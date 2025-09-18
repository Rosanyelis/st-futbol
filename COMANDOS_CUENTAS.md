# Comandos de Gestión de Cuentas

Este documento describe los comandos disponibles para gestionar los saldos de las cuentas/métodos de pago del sistema.

## 📋 Comandos Disponibles

### 1. Actualizar Saldos de Cuentas
**Comando:** `php artisan accounts:update-balances`

**Descripción:** Actualiza los saldos de todas las cuentas/métodos de pago basándose en los movimientos de BussinesMovement y EventMovement.

**Uso:**
```bash
# Actualizar con confirmación
php artisan accounts:update-balances

# Actualizar sin confirmación (forzar)
php artisan accounts:update-balances --force

# Actualizar con información detallada
php artisan accounts:update-balances --verbose
```

**Funcionalidad:**
- Calcula ingresos y egresos de BussinesMovement
- Calcula ingresos y egresos de EventMovement
- Suma todos los movimientos por método de pago
- Actualiza el campo `current_balance` en MethodPayment
- Utiliza transacciones de base de datos para garantizar consistencia

### 2. Mostrar Resumen de Saldos
**Comando:** `php artisan accounts:show-balances`

**Descripción:** Muestra un resumen detallado de todos los saldos actuales de las cuentas.

**Uso:**
```bash
# Mostrar todos los saldos
php artisan accounts:show-balances

# Filtrar por moneda específica
php artisan accounts:show-balances --currency=1

# Filtrar por entidad específica
php artisan accounts:show-balances --entity=2

# Combinar filtros
php artisan accounts:show-balances --currency=1 --entity=2
```

**Información mostrada:**
- Tabla con ID, Titular, Tipo, Entidad, Moneda, Saldo Actual, Saldo Inicial
- Saldo total general de todas las cuentas
- Estadísticas (cuentas positivas, negativas, cero)
- Opción de ver movimientos recientes

## 🔧 Cómo Funciona la Actualización

### Cálculo de Saldos
Para cada método de pago, el sistema:

1. **Suma Ingresos de Negocio:**
   ```sql
   SELECT SUM(amount) FROM bussines_movements 
   WHERE method_payment_id = ? AND type = 'Ingreso' AND status = 'Activo'
   ```

2. **Suma Egresos de Negocio:**
   ```sql
   SELECT SUM(amount) FROM bussines_movements 
   WHERE method_payment_id = ? AND type = 'Egreso' AND status = 'Activo'
   ```

3. **Suma Ingresos de Eventos:**
   ```sql
   SELECT SUM(amount) FROM event_movements 
   WHERE method_payment_id = ? AND type = 'Ingreso' AND status = 'Activo'
   ```

4. **Suma Egresos de Eventos:**
   ```sql
   SELECT SUM(amount) FROM event_movements 
   WHERE method_payment_id = ? AND type = 'Egreso' AND status = 'Activo'
   ```

5. **Calcula Saldo Final:**
   ```
   Saldo = (Ingresos Negocio + Ingresos Eventos) - (Egresos Negocio + Egresos Eventos)
   ```

### Seguridad
- **Transacciones:** Todos los cambios se realizan dentro de una transacción
- **Rollback:** Si ocurre un error, se revierten todos los cambios
- **Confirmación:** Por defecto solicita confirmación antes de ejecutar
- **Logging:** Registra errores detallados en caso de fallos

## 📊 Ejemplo de Uso

### Actualizar Saldos
```bash
$ php artisan accounts:update-balances

🔄 Iniciando actualización de saldos de cuentas...
¿Estás seguro de que quieres actualizar todos los saldos de las cuentas? (yes/no) [no]:
> yes

📊 Procesando 11 métodos de pago...
████████████████████████████████████████ 100%

✅ Actualización completada exitosamente!
📈 Se actualizaron 11 métodos de pago
```

### Ver Resumen
```bash
$ php artisan accounts:show-balances

💰 Resumen de Saldos de Cuentas
================================

📊 Total de cuentas: 11

+----+------------------------+----------+-------------------------+---------+---------------+---------------+
| ID | Titular                | Tipo     | Entidad                 | Moneda  | Saldo Actual  | Saldo Inicial |
+----+------------------------+----------+-------------------------+---------+---------------+---------------+
| 1  | Sebastian Alexis Bauso | Propia   | Banco Frances Sebastian | Pesos   | 2.467.670,00  | 0,00          |
| 2  | Sebastian Alexis Bauso | Propia   | UALA                    | Pesos   | 640.000,00    | 0,00          |
| 3  | Efectivo               | Propia   | Pesos                   | Pesos   | 41.830.000,00 | 40.000.000,00 |
+----+------------------------+----------+-------------------------+---------+---------------+---------------+

💵 Saldo Total General: 72.098.841,07

📈 Estadísticas:
  • Cuentas con saldo positivo: 11
  • Cuentas con saldo negativo: 0
  • Cuentas con saldo cero: 0
```

## ⚠️ Consideraciones Importantes

1. **Frecuencia:** Se recomienda ejecutar la actualización regularmente, especialmente después de importar datos o realizar cambios masivos.

2. **Backup:** Siempre hacer backup de la base de datos antes de ejecutar actualizaciones masivas.

3. **Horarios:** Ejecutar durante horarios de bajo tráfico para evitar conflictos.

4. **Monitoreo:** Verificar los resultados después de la actualización usando el comando de resumen.

5. **Movimientos Cancelados:** Solo se consideran movimientos con status 'Activo', los cancelados se ignoran.

## 🚀 Automatización

Para automatizar la actualización, puedes agregar el comando a tu cron:

```bash
# Actualizar saldos cada día a las 2:00 AM
0 2 * * * cd /path/to/project && php artisan accounts:update-balances --force
```

O crear un job programado en Laravel:

```php
// En app/Console/Kernel.php
$schedule->command('accounts:update-balances --force')
         ->dailyAt('02:00')
         ->withoutOverlapping();
```
