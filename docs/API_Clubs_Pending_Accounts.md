# API para Clubs con Cuentas por Pagar Pendientes

## Descripción

Esta API proporciona métodos optimizados para obtener clubs que tienen cuentas por pagar pendientes en eventos específicos. Los métodos están diseñados para ser eficientes y permitir diferentes tipos de consultas según las necesidades.

## Endpoints Disponibles

### 1. Obtener Clubs con Cuentas Pendientes (Completo)

**Endpoint:** `GET /eventos/{eventId}/clubs-with-pending-accounts`

**Descripción:** Obtiene todos los clubs asignados a un evento que tienen cuentas por cobrar pendientes, incluyendo información detallada de cada cuenta.

**Respuesta:**
```json
{
  "success": true,
  "event": {
    "id": 1,
    "name": "Torneo 2024",
    "year": "2024"
  },
  "clubs": [
    {
      "id": 1,
      "name": "Club Deportivo A",
      "logo": "logo.png",
      "cuit": "20-12345678-9",
      "responsible": "Juan Pérez",
      "phone": "+54 11 1234-5678",
      "email": "juan@club.com",
      "total_amount": 5000.00,
      "currency": {
        "id": 1,
        "name": "Peso Argentino",
        "symbol": "$"
      },
      "summary": {
        "total_pending": 3000.00,
        "total_receivable": 5000.00,
        "total_paid": 2000.00,
        "payment_percentage": 40.00,
        "accounts_count": 2
      }
    }
  ],
  "total_clubs": 1,
  "total_pending_amount": 3000.00
}
```

### 2. Resumen de Clubs con Cuentas Pendientes (Ligero)

**Endpoint:** `GET /eventos/{eventId}/clubs-pending-accounts-summary`

**Descripción:** Obtiene un resumen optimizado de clubs con cuentas pendientes, ideal para dashboards y listas.

**Respuesta:**
```json
{
  "success": true,
  "summary": {
    "total_clubs": 5,
    "total_pending_amount": 15000.00,
    "total_receivable_amount": 25000.00,
    "overdue_accounts": 2,
    "payment_percentage": 40.00
  },
  "clubs": [
    {
      "id": 1,
      "name": "Club Deportivo A",
      "logo": "logo.png",
      "currency": {
        "id": 1,
        "name": "Peso Argentino",
        "symbol": "$"
      },
      "total_pending": 3000.00,
      "total_receivable": 5000.00,
      "payment_percentage": 40.00,
      "accounts_count": 2,
      "overdue_accounts": 1
    }
  ]
}
```

### 3. Clubs con Cuentas Pendientes Filtradas

**Endpoint:** `GET /eventos/{eventId}/clubs-pending-accounts-filtered`

**Descripción:** Permite filtrar clubs con cuentas pendientes por diferentes criterios.

**Parámetros de Query:**
- `min_amount`: Monto mínimo pendiente (opcional)
- `max_amount`: Monto máximo pendiente (opcional)
- `currency_id`: ID de la moneda (opcional)
- `overdue_only`: Solo cuentas vencidas (true/false, opcional)
- `status`: Estado específico de las cuentas (opcional)
- `sort_by`: Campo para ordenar (total_pending, name, opcional)
- `sort_order`: Orden (asc, desc, opcional)

**Ejemplo de uso:**
```
GET /eventos/1/clubs-pending-accounts-filtered?min_amount=1000&overdue_only=true&sort_by=total_pending&sort_order=desc
```

**Respuesta:**
```json
{
  "success": true,
  "filters": {
    "min_amount": 1000,
    "max_amount": null,
    "currency_id": null,
    "overdue_only": true,
    "status": null,
    "sort_by": "total_pending",
    "sort_order": "desc"
  },
  "summary": {
    "total_clubs": 3,
    "total_pending_amount": 12000.00,
    "total_receivable_amount": 20000.00,
    "overdue_accounts": 3,
    "payment_percentage": 40.00
  },
  "clubs": [
    {
      "id": 1,
      "name": "Club Deportivo A",
      "logo": "logo.png",
      "currency": {
        "id": 1,
        "name": "Peso Argentino",
        "symbol": "$"
      },
      "accounts_summary": {
        "total_pending": 5000.00,
        "total_receivable": 8000.00,
        "total_paid": 3000.00,
        "payment_percentage": 37.50,
        "accounts_count": 2,
        "overdue_accounts": 2,
        "accounts": [
          {
            "id": 1,
            "pending_amount": 3000.00,
            "total_amount": 5000.00,
            "status": "Parcial",
            "due_date": "2024-01-15",
            "is_overdue": true
          }
        ]
      }
    }
  ]
}
```

## Optimizaciones Implementadas

### 1. Eager Loading Selectivo
- Solo se cargan los campos necesarios de cada modelo
- Se evita el problema N+1 con relaciones optimizadas

### 2. Joins Eficientes
- Uso de `JOIN` en lugar de consultas separadas
- Filtros aplicados directamente en los joins

### 3. Consultas Raw para Filtros Complejos
- Uso de subconsultas para filtros de monto
- Ordenamiento por campos calculados

### 4. Paginación y Límites
- Los métodos están preparados para implementar paginación
- Se pueden agregar límites según sea necesario

## Casos de Uso

### Dashboard Principal
```javascript
// Obtener resumen para dashboard
fetch('/eventos/1/clubs-pending-accounts-summary')
  .then(response => response.json())
  .then(data => {
    console.log(`Total pendiente: $${data.summary.total_pending_amount}`);
    console.log(`Clubs con cuentas pendientes: ${data.summary.total_clubs}`);
  });
```

### Lista Detallada
```javascript
// Obtener lista completa con filtros
const params = new URLSearchParams({
  min_amount: 1000,
  overdue_only: true,
  sort_by: 'total_pending',
  sort_order: 'desc'
});

fetch(`/eventos/1/clubs-pending-accounts-filtered?${params}`)
  .then(response => response.json())
  .then(data => {
    data.clubs.forEach(club => {
      console.log(`${club.name}: $${club.accounts_summary.total_pending} pendiente`);
    });
  });
```

### Monitoreo de Vencimientos
```javascript
// Obtener solo cuentas vencidas
fetch('/eventos/1/clubs-pending-accounts-filtered?overdue_only=true')
  .then(response => response.json())
  .then(data => {
    console.log(`Cuentas vencidas: ${data.summary.overdue_accounts}`);
  });
```

## Consideraciones de Rendimiento

1. **Índices Recomendados:**
   - `event_clubs(event_id, club_id)`
   - `account_receivables(club_id, event_id, status, pending_amount)`
   - `clubs(currency_id)`

2. **Monitoreo:**
   - Revisar tiempos de respuesta para eventos con muchos clubs
   - Considerar implementar caché para consultas frecuentes

3. **Escalabilidad:**
   - Los métodos están preparados para implementar paginación
   - Se pueden agregar límites de resultados según sea necesario

## Manejo de Errores

Todos los endpoints devuelven respuestas consistentes:

**Éxito:**
```json
{
  "success": true,
  "data": {...}
}
```

**Error:**
```json
{
  "success": false,
  "error": "Descripción del error"
}
```

## Notas de Implementación

- Los métodos validan la existencia del evento antes de procesar
- Se incluye manejo de excepciones con try-catch
- Las consultas están optimizadas para evitar problemas de rendimiento
- Se mantiene compatibilidad con el código existente
