# API Simple para Clubs con Cuentas por Pagar Pendientes

## Descripción

Este endpoint proporciona una consulta simple y optimizada para obtener solo la información esencial: **nombre del club** y **monto pendiente total** de las cuentas por cobrar en un evento específico.

## Endpoint

**URL:** `GET /eventos/{eventId}/clubs-pending-amounts`

**Descripción:** Obtiene todos los clubs asignados a un evento que tienen cuentas por cobrar pendientes, mostrando solo el nombre del club y el monto total pendiente.

## Parámetros

- `eventId` (requerido): ID del evento en la URL

## Respuesta

### Estructura de Respuesta
```json
{
  "success": true,
  "event": {
    "id": 1,
    "name": "Torneo 2024",
    "year": "2024"
  },
  "total_pending_amount": 15000.00,
  "total_clubs": 5,
  "clubs": [
    {
      "id": 1,
      "name": "Club Deportivo A",
      "currency": {
        "id": 1,
        "name": "Peso Argentino",
        "symbol": "$"
      },
      "pending_amount": 5000.00
    },
    {
      "id": 2,
      "name": "Club Deportivo B",
      "currency": {
        "id": 1,
        "name": "Peso Argentino",
        "symbol": "$"
      },
      "pending_amount": 3000.00
    }
  ]
}
```

### Campos de Respuesta

#### Evento
- `id`: ID del evento
- `name`: Nombre del evento
- `year`: Año del evento

#### Resumen General
- `total_pending_amount`: Monto total pendiente de todos los clubs
- `total_clubs`: Cantidad total de clubs con cuentas pendientes

#### Lista de Clubs
- `id`: ID del club
- `name`: Nombre del club
- `currency`: Información de la moneda (id, name, symbol)
- `pending_amount`: Monto total pendiente del club

## Características

### ✅ **Optimizaciones Implementadas**
- **Consulta única**: Solo una consulta SQL optimizada
- **Joins eficientes**: Uso de JOIN para evitar consultas N+1
- **Selección selectiva**: Solo se cargan los campos necesarios
- **Filtrado automático**: Solo incluye clubs con monto pendiente > 0
- **Ordenamiento**: Los clubs se ordenan por monto pendiente (descendente)

### ✅ **Filtros Automáticos**
- Solo clubs asignados al evento
- Solo cuentas con estado diferente a "Pagado"
- Solo cuentas con monto pendiente > 0
- Solo clubs que efectivamente tienen deuda

## Ejemplos de Uso

### 1. Obtener Clubs con Cuentas Pendientes
```bash
GET /eventos/1/clubs-pending-amounts
```

### 2. Uso con JavaScript
```javascript
// Función simple para obtener clubs con montos pendientes
async function getClubsPendingAmounts(eventId) {
    try {
        const response = await fetch(`/eventos/${eventId}/clubs-pending-amounts`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido');
        }
        
        return data;
    } catch (error) {
        console.error('Error obteniendo clubs con montos pendientes:', error);
        throw error;
    }
}

// Uso
const eventId = 1;
getClubsPendingAmounts(eventId).then(data => {
    console.log(`Total pendiente: $${data.total_pending_amount}`);
    console.log(`Clubs con deuda: ${data.total_clubs}`);
    
    data.clubs.forEach(club => {
        console.log(`${club.name}: $${club.pending_amount} ${club.currency.symbol}`);
    });
});
```

### 3. Uso con jQuery
```javascript
$.get(`/eventos/${eventId}/clubs-pending-amounts`)
    .done(function(data) {
        if (data.success) {
            // Mostrar resumen
            $('#total-pending').text(data.total_pending_amount);
            $('#total-clubs').text(data.total_clubs);
            
            // Renderizar lista
            const clubsList = data.clubs.map(club => 
                `<div class="club-item">
                    <span class="club-name">${club.name}</span>
                    <span class="club-amount">$${club.pending_amount}</span>
                </div>`
            ).join('');
            
            $('#clubs-list').html(clubsList);
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Error:', error);
    });
```

### 4. Uso con Axios
```javascript
import axios from 'axios';

const getClubsPendingAmounts = async (eventId) => {
    try {
        const response = await axios.get(`/eventos/${eventId}/clubs-pending-amounts`);
        return response.data;
    } catch (error) {
        console.error('Error:', error.response?.data?.error || error.message);
        throw error;
    }
};

// Uso
getClubsPendingAmounts(1).then(data => {
    console.log('Clubs con cuentas pendientes:', data.clubs);
});
```

## Casos de Uso

### 📊 **Dashboard Simple**
- Mostrar total de deuda pendiente
- Lista de clubs con montos pendientes
- Contador de clubs con deuda

### 📋 **Reportes Básicos**
- Lista para cobranza
- Resumen ejecutivo
- Exportación a CSV/Excel

### 🔍 **Búsquedas Rápidas**
- Verificar estado de cuentas por cobrar
- Identificar clubs con mayor deuda
- Monitoreo de cobranza

## Ventajas del Método Simple

### 🚀 **Rendimiento**
- **Consulta única**: Una sola consulta SQL
- **Sin N+1**: No hay consultas adicionales por club
- **Datos mínimos**: Solo campos esenciales
- **Ordenamiento automático**: Por monto pendiente

### 💡 **Simplicidad**
- **Respuesta clara**: Solo lo que necesitas
- **Fácil de usar**: Un solo endpoint
- **Sin filtros complejos**: Lógica simple y directa
- **Datos limpios**: Solo clubs con deuda real

### 🔧 **Mantenimiento**
- **Código simple**: Fácil de entender y modificar
- **Sin dependencias**: No requiere parámetros complejos
- **Consistente**: Respuesta siempre en el mismo formato
- **Escalable**: Funciona bien con muchos clubs

## Comparación con Otros Métodos

| Método | Complejidad | Datos | Rendimiento | Caso de Uso |
|--------|-------------|-------|-------------|--------------|
| `getClubsWithPendingAmounts` | **Simple** | **Básico** | **Excelente** | **Dashboard, Reportes** |
| `getClubsWithPendingAccounts` | Complejo | Detallado | Bueno | Análisis detallado |
| `getClubsPendingAccountsFiltered` | Muy complejo | Completo | Regular | Filtros avanzados |

## Manejo de Errores

### Respuesta de Error
```json
{
  "success": false,
  "error": "Descripción del error"
}
```

### Errores Comunes
- **Evento no encontrado**: El ID del evento no existe
- **Error de base de datos**: Problemas de conexión o consulta
- **Permisos insuficientes**: Usuario sin acceso al evento

## Consideraciones de Seguridad

- ✅ Validación del ID del evento
- ✅ Verificación de permisos del usuario
- ✅ Sanitización de parámetros
- ✅ Manejo seguro de errores

## Monitoreo y Rendimiento

### Métricas Recomendadas
- **Tiempo de respuesta**: < 200ms para eventos normales
- **Uso de memoria**: < 10MB para 1000 clubs
- **Consultas SQL**: 1 consulta por request

### Optimizaciones Futuras
- **Caché**: Para eventos con muchos clubs
- **Paginación**: Si se superan los 1000 clubs
- **Índices**: Optimizar consultas de cuentas pendientes

## Ejemplo de Implementación Completa

```html
<!DOCTYPE html>
<html>
<head>
    <title>Clubs con Cuentas Pendientes</title>
</head>
<body data-event-id="1">
    <div id="pending-summary-container"></div>
    <div id="clubs-list-container"></div>
    
    <script src="simple-clubs-pending.js"></script>
</body>
</html>
```

Este endpoint es perfecto para cuando solo necesitas una lista simple y rápida de clubs con sus montos pendientes, sin información adicional compleja.
