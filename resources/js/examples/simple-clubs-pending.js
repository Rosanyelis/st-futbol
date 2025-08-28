/**
 * Ejemplo simple para obtener clubs con cuentas por pagar pendientes
 * Solo muestra nombre del club y monto pendiente
 */

/**
 * Obtener clubs con montos pendientes de forma simple
 */
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

/**
 * Renderizar lista simple de clubs con montos pendientes
 */
function renderSimpleClubsList(clubs, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = `
        <div class="clubs-pending-list">
            <h4>Clubs con Cuentas Pendientes</h4>
            ${clubs.map(club => `
                <div class="club-pending-item">
                    <div class="club-name">${club.name}</div>
                    <div class="club-amount">
                        ${formatCurrency(club.pending_amount)} ${club.currency.symbol}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

/**
 * Renderizar resumen de montos pendientes
 */
function renderPendingSummary(data, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = `
        <div class="pending-summary">
            <div class="summary-header">
                <h3>Resumen de Cuentas Pendientes</h3>
                <p>Evento: ${data.event.name} (${data.event.year})</p>
            </div>
            <div class="summary-stats">
                <div class="stat-item">
                    <span class="stat-label">Total Pendiente:</span>
                    <span class="stat-value">${formatCurrency(data.total_pending_amount)}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Clubs con Deuda:</span>
                    <span class="stat-value">${data.total_clubs}</span>
                </div>
            </div>
        </div>
    `;
}

/**
 * Función principal para cargar y mostrar clubs con montos pendientes
 */
async function loadClubsPendingAmounts(eventId) {
    try {
        const data = await getClubsPendingAmounts(eventId);
        
        // Renderizar resumen
        renderPendingSummary(data, 'pending-summary-container');
        
        // Renderizar lista de clubs
        renderSimpleClubsList(data.clubs, 'clubs-list-container');
        
        // Mostrar información en consola para debugging
        console.log('Clubs con montos pendientes:', data);
        
    } catch (error) {
        showError('Error cargando clubs con montos pendientes: ' + error.message);
    }
}

/**
 * Función para filtrar clubs por monto mínimo
 */
function filterClubsByMinAmount(clubs, minAmount) {
    return clubs.filter(club => club.pending_amount >= minAmount);
}

/**
 * Función para ordenar clubs por monto pendiente
 */
function sortClubsByAmount(clubs, order = 'desc') {
    return clubs.sort((a, b) => {
        if (order === 'desc') {
            return b.pending_amount - a.pending_amount;
        } else {
            return a.pending_amount - b.pending_amount;
        }
    });
}

/**
 * Función para exportar datos a CSV
 */
function exportToCSV(data) {
    const csvContent = [
        ['Club', 'Monto Pendiente', 'Moneda'],
        ...data.clubs.map(club => [
            club.name,
            club.pending_amount,
            club.currency.symbol
        ])
    ].map(row => row.join(',')).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `clubs_pending_${data.event.name}_${data.event.year}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Función para mostrar error
 */
function showError(message) {
    console.error(message);
    
    // Crear elemento de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.textContent = message;
    
    // Insertar al inicio del contenedor principal
    const container = document.querySelector('.main-container') || document.body;
    container.insertBefore(errorDiv, container.firstChild);
    
    // Remover después de 5 segundos
    setTimeout(() => errorDiv.remove(), 5000);
}

/**
 * Función para formatear moneda
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

/**
 * Inicialización cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el ID del evento del atributo data del body
    const eventId = document.body.dataset.eventId;
    
    if (eventId) {
        // Cargar datos automáticamente
        loadClubsPendingAmounts(eventId);
        
        // Configurar botón de exportar si existe
        const exportButton = document.getElementById('export-csv-button');
        if (exportButton) {
            exportButton.addEventListener('click', async function() {
                try {
                    const data = await getClubsPendingAmounts(eventId);
                    exportToCSV(data);
                } catch (error) {
                    showError('Error exportando datos: ' + error.message);
                }
            });
        }
        
        // Configurar filtro por monto mínimo si existe
        const minAmountInput = document.getElementById('min-amount-filter');
        if (minAmountInput) {
            minAmountInput.addEventListener('change', async function() {
                try {
                    const data = await getClubsPendingAmounts(eventId);
                    const minAmount = parseFloat(this.value) || 0;
                    
                    if (minAmount > 0) {
                        const filteredClubs = filterClubsByMinAmount(data.clubs, minAmount);
                        renderSimpleClubsList(filteredClubs, 'clubs-list-container');
                    } else {
                        renderSimpleClubsList(data.clubs, 'clubs-list-container');
                    }
                } catch (error) {
                    showError('Error aplicando filtro: ' + error.message);
                }
            });
        }
    }
});

// Ejemplo de uso directo
// loadClubsPendingAmounts(1); // Para evento con ID 1

// Exportar funciones para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        getClubsPendingAmounts,
        renderSimpleClubsList,
        renderPendingSummary,
        filterClubsByMinAmount,
        sortClubsByAmount,
        exportToCSV
    };
}
