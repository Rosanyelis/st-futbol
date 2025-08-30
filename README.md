# ST Fútbol - Sistema de Gestión Deportiva

## 📋 Descripción General

ST Fútbol es un sistema integral de gestión deportiva desarrollado en Laravel que permite administrar eventos deportivos, clubes, proveedores, movimientos financieros y reportes. El sistema está diseñado para organizaciones deportivas que necesitan gestionar múltiples eventos, clubes participantes, proveedores de servicios y el control financiero completo de sus operaciones.

## 🏗️ Arquitectura del Sistema

### Backend
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Patrón**: MVC (Model-View-Controller)
- **Base de Datos**: MySQL/PostgreSQL
- **Autenticación**: Laravel Breeze
- **Validación**: Form Request Validation
- **Relaciones**: Eloquent ORM con relaciones complejas

### Frontend
- **Framework CSS**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js 3.x
- **Build Tool**: Vite 6.x
- **Componentes**: Blade Templates con componentes reutilizables
- **Responsive**: Diseño mobile-first

## 🎯 Módulos Principales

### 1. **Gestión de Eventos** (`/eventos`)
- **Funcionalidades**:
  - Crear, editar, eliminar eventos deportivos
  - Asignar clubes y proveedores a eventos
  - Gestión de fechas de inicio y fin
  - Control de movimientos financieros por evento
  - Historial de transacciones
  - Gestión de imágenes de eventos

### 2. **Gestión de Clubes** (`/clubs`)
- **Funcionalidades**:
  - Registro completo de clubes deportivos
  - Información de contacto y ubicación
  - Asignación a eventos específicos
  - Gestión de pagos y cuentas por cobrar
  - Relación con categorías de ingresos
  - Historial de movimientos por club

### 3. **Gestión de Proveedores** (`/proveedores`)
- **Funcionalidades**:
  - Registro de proveedores de servicios
  - Categorización por tipo de servicio
  - Subcategorías específicas
  - Asignación a eventos
  - Control de cuentas por pagar
  - Historial de transacciones

### 4. **Gestión Financiera**

#### 4.1 **Cuentas por Cobrar** (`/cuenta-por-cobrar`)
- Registro de deudas de clubes
- Procesamiento de pagos
- Generación de recibos
- Estados de cuenta detallados
- Reportes de cobranza

#### 4.2 **Cuentas por Pagar** (`/cuenta-por-pagar`)
- Registro de deudas con proveedores
- Procesamiento de pagos
- Control de vencimientos
- Estados de cuenta

#### 4.3 **Movimientos de Negocio** (`/negocio`)
- Transacciones generales del negocio
- Control de ingresos y egresos
- Categorización de movimientos
- Historial completo

### 5. **Gestión de Gastos** (`/gastos`)
- Registro de gastos operativos
- Categorización por tipo
- Asociación con eventos
- Control de presupuestos

### 6. **Gestión de Categorías**

#### 6.1 **Categorías de Ingresos** (`/categorias-ingresos`)
- Clasificación de fuentes de ingresos
- Relación con clubes y eventos

#### 6.2 **Categorías de Gastos** (`/categorias-gastos`)
- Clasificación de tipos de gastos
- Control presupuestario

#### 6.3 **Categorías de Proveedores** (`/categorias-proveedores`)
- Clasificación de proveedores por servicio
- Subcategorías específicas

### 7. **Gestión de Métodos de Pago** (`/metodos-pago`)
- Configuración de métodos de pago
- Categorización por tipo
- Asociación con monedas
- Control de transacciones

### 8. **Gestión de Monedas** (`/monedas`)
- Configuración de monedas soportadas
- Historial de cambios de moneda
- Conversiones automáticas

### 9. **Gestión Geográfica**
- **Países** (`/paises`)
- **Provincias** (`/provincias`)
- **Ciudades** (`/ciudades`)

### 10. **Sistema de Reportes** (`/reportes`)
- **Reportes de Eventos**: Lista completa de eventos
- **Estado de Ingresos**: Análisis de ingresos por categoría
- **Estado de Egresos**: Análisis de gastos por categoría
- **Cuentas por Cobrar**: Estado de cobranza
- **Estado por Evento y Moneda**: Resultados financieros por evento
- **Estado General**: Resultados consolidados
- **Estado de Cuentas**: Balance general
- **Estado de Movimientos**: Análisis de transacciones
- **Exportación**: PDF y Excel

## 🛠️ Tecnologías y Paquetes

### Backend (PHP/Laravel)
```json
{
  "laravel/framework": "^12.0",
  "barryvdh/laravel-dompdf": "^3.1",
  "maatwebsite/excel": "^3.1",
  "yajra/laravel-datatables-oracle": "^12.0",
  "laravel/breeze": "^2.3",
  "laravel-shift/blueprint": "^2.12"
}
```

### Frontend (JavaScript/CSS)
```json
{
  "tailwindcss": "^3.1.0",
  "@tailwindcss/forms": "^0.5.2",
  "alpinejs": "^3.4.2",
  "vite": "^6.2.4",
  "axios": "^1.8.2"
}
```

### Herramientas de Desarrollo
- **Laravel Pint**: Formateo de código PHP
- **Laravel Sail**: Entorno de desarrollo Docker
- **Pest**: Framework de testing
- **Laravel Pail**: Log viewer

## 📊 Estructura de Base de Datos

### Tablas Principales
- `events` - Eventos deportivos
- `clubs` - Clubes participantes
- `suppliers` - Proveedores de servicios
- `bussines_movements` - Movimientos financieros generales
- `event_movements` - Movimientos específicos por evento
- `account_receivables` - Cuentas por cobrar
- `account_payables` - Cuentas por pagar
- `expenses` - Gastos operativos
- `currencies` - Monedas soportadas
- `method_payments` - Métodos de pago

### Tablas de Relación
- `event_clubs` - Relación eventos-clubes
- `event_suppliers` - Relación eventos-proveedores
- `account_receivable_payments` - Pagos de cuentas por cobrar
- `account_payable_payments` - Pagos de cuentas por pagar

### Tablas de Configuración
- `category_incomes` - Categorías de ingresos
- `category_expenses` - Categorías de gastos
- `category_suppliers` - Categorías de proveedores
- `category_method_payments` - Categorías de métodos de pago
- `countries`, `provinces`, `cities` - Ubicaciones geográficas

## 🚀 Instalación y Configuración

### Requisitos Previos
- PHP 8.2 o superior
- Composer
- Node.js y npm
- Base de datos MySQL/PostgreSQL

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone [url-del-repositorio]
cd st-futbol
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias JavaScript**
```bash
npm install
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos en `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=st_futbol
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

6. **Ejecutar migraciones**
```bash
php artisan migrate
```

7. **Compilar assets**
```bash
npm run build
```

8. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

### Comandos Útiles

```bash
# Desarrollo con hot reload
composer run dev

# Ejecutar tests
composer run test

# Formatear código PHP
./vendor/bin/pint

# Ver logs en tiempo real
php artisan pail
```

## 🔐 Autenticación y Autorización

El sistema utiliza Laravel Breeze para la autenticación con las siguientes características:
- Login/Logout tradicional
- Registro de usuarios
- Recuperación de contraseñas
- Verificación de email
- Gestión de perfiles de usuario

## 📱 Características de la Interfaz

### Diseño Responsive
- Mobile-first design
- Adaptable a tablets y desktop
- Componentes reutilizables
- Navegación intuitiva

### Componentes UI
- Formularios con validación en tiempo real
- Tablas con DataTables
- Modales para acciones rápidas
- Notificaciones toast
- Gráficos y reportes visuales

### Funcionalidades Avanzadas
- Búsqueda y filtrado avanzado
- Exportación a PDF y Excel
- Carga de imágenes
- Validación de formularios
- AJAX para operaciones asíncronas

## 📈 Reportes y Analytics

### Tipos de Reportes
1. **Reportes Financieros**
   - Estado de ingresos y egresos
   - Balance general
   - Flujo de caja

2. **Reportes Operativos**
   - Lista de eventos
   - Participación de clubes
   - Proveedores por evento

3. **Reportes de Cobranza**
   - Estado de cuentas por cobrar
   - Vencimientos
   - Historial de pagos

4. **Reportes de Pagos**
   - Estado de cuentas por pagar
   - Proveedores pendientes
   - Flujo de pagos

### Exportación
- **PDF**: Reportes formateados para impresión
- **Excel**: Datos estructurados para análisis
- **CSV**: Datos para importación en otros sistemas

## 🔧 Mantenimiento y Soporte

### Logs y Monitoreo
- Logs de Laravel en `storage/logs/`
- Logs de errores y transacciones
- Monitoreo de performance

### Backup y Recuperación
- Backup automático de base de datos
- Versionado de código con Git
- Documentación de cambios

### Actualizaciones
- Composer para dependencias PHP
- npm para dependencias JavaScript
- Migraciones de base de datos

## 🤝 Contribución

### Estándares de Código
- PSR-12 para PHP
- ESLint para JavaScript
- Prettier para formateo
- Conventional Commits

### Testing
- Tests unitarios con Pest
- Tests de integración
- Tests de funcionalidad

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o consultas sobre el sistema, contactar al equipo de desarrollo.

correo de desarrolladora: rossdigital2@gmail.com

---

**Desarrollado con ❤️ para la gestión deportiva**
