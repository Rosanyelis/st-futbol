/**
 * Ejemplos de uso de la API para Clubs con Cuentas por Pagar Pendientes
 * Este archivo muestra cómo implementar los diferentes endpoints en el frontend
 */

class ClubsPendingAccountsAPI {
    constructor(baseUrl = '/eventos') {
        this.baseUrl = baseUrl;
    }

    /**
     * Obtener clubs con cuentas pendientes completas
     */
    async getClubsWithPendingAccounts(eventId) {
        try {
            const response = await fetch(`${this.baseUrl}/${eventId}/clubs-with-pending-accounts`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Error desconocido');
            }
            
            return data;
        } catch (error) {
            console.error('Error obteniendo clubs con cuentas pendientes:', error);
            throw error;
        }
    }

    /**
     * Obtener resumen de clubs con cuentas pendientes
     */
    async getClubsPendingAccountsSummary(eventId) {
        try {
            const response = await fetch(`${this.baseUrl}/${eventId}/clubs-pending-accounts-summary`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Error desconocido');
            }
            
            return data;
        } catch (error) {
            console.error('Error obteniendo resumen de cuentas pendientes:', error);
            throw error;
        }
    }

    /**
     * Obtener clubs filtrados con cuentas pendientes
     */
    async getClubsPendingAccountsFiltered(eventId, filters = {}) {
        try {
            const params = new URLSearchParams();
            
            // Agregar filtros si están definidos
            if (filters.minAmount) params.append('min_amount', filters.minAmount);
            if (filters.maxAmount) params.append('max_amount', filters.maxAmount);
            if (filters.currencyId) params.append('currency_id', filters.currencyId);
            if (filters.overdueOnly) params.append('overdue_only', filters.overdueOnly);
            if (filters.status) params.append('status', filters.status);
            if (filters.sortBy) params.append('sort_by', filters.sortBy);
            if (filters.sortOrder) params.append('sort_order', filters.sortOrder);

            const queryString = params.toString();
            const url = `${this.baseUrl}/${eventId}/clubs-pending-accounts-filtered${queryString ? '?' + queryString : ''}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Error desconocido');
            }
            
            return data;
        } catch (error) {
            console.error('Error obteniendo clubs filtrados:', error);
            throw error;
        }
    }
}

/**
 * Ejemplos de implementación
 */

// 1. Dashboard principal con resumen
async function loadDashboardSummary(eventId) {
    const api = new ClubsPendingAccountsAPI();
    
    try {
        const data = await api.getClubsPendingAccountsSummary(eventId);
        
        // Actualizar elementos del dashboard
        document.getElementById('total-clubs').textContent = data.summary.total_clubs;
        document.getElementById('total-pending').textContent = formatCurrency(data.summary.total_pending_amount);
        document.getElementById('overdue-accounts').textContent = data.summary.overdue_accounts;
        document.getElementById('payment-percentage').textContent = data.summary.payment_percentage + '%';
        
        // Renderizar lista de clubs
        renderClubsList(data.clubs, 'dashboard-clubs-list');
        
    } catch (error) {
        showError('Error cargando resumen del dashboard: ' + error.message);
    }
}

// 2. Lista detallada con filtros
async function loadFilteredClubs(eventId, filters = {}) {
    const api = new ClubsPendingAccountsAPI();
    
    try {
        const data = await api.getClubsPendingAccountsFiltered(eventId, filters);
        
        // Actualizar contadores
        updateFilteredSummary(data.summary);
        
        // Renderizar lista filtrada
        renderDetailedClubsList(data.clubs, 'filtered-clubs-list');
        
        // Mostrar filtros aplicados
        showAppliedFilters(data.filters);
        
    } catch (error) {
        showError('Error cargando clubs filtrados: ' + error.message);
    }
}

// 3. Monitoreo de cuentas vencidas
async function loadOverdueAccounts(eventId) {
    const filters = {
        overdueOnly: true,
        sortBy: 'total_pending',
        sortOrder: 'desc'
    };
    
    await loadFilteredClubs(eventId, filters);
}

// 4. Búsqueda por monto mínimo
async function searchByAmount(eventId, minAmount) {
    const filters = {
        minAmount: minAmount,
        sortBy: 'total_pending',
        sortOrder: 'desc'
    };
    
    await loadFilteredClubs(eventId, filters);
}

// 5. Filtro por moneda
async function filterByCurrency(eventId, currencyId) {
    const filters = {
        currencyId: currencyId
    };
    
    await loadFilteredClubs(eventId, filters);
}

/**
 * Funciones de renderizado
 */

function renderClubsList(clubs, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = clubs.map(club => `
        <div class="club-card">
            <div class="club-header">
                <img src="${club.logo || '/images/default-club.png'}" alt="${club.name}" class="club-logo">
                <h4>${club.name}</h4>
            </div>
            <div class="club-summary">
                <div class="amount-info">
                    <span class="label">Pendiente:</span>
                    <span class="amount">${formatCurrency(club.total_pending)}</span>
                </div>
                <div class="percentage-info">
                    <span class="label">Pagado:</span>
                    <span class="percentage">${club.payment_percentage}%</span>
                </div>
            </div>
        </div>
    `).join('');
}

function renderDetailedClubsList(clubs, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = clubs.map(club => `
        <div class="club-detailed-card">
            <div class="club-header">
                <img src="${club.logo || '/images/default-club.png'}" alt="${club.name}" class="club-logo">
                <div class="club-info">
                    <h4>${club.name}</h4>
                    <p class="club-details">
                        ${club.cuit ? `CUIT: ${club.cuit}` : ''}
                        ${club.responsible ? `Responsable: ${club.responsible}` : ''}
                    </p>
                </div>
            </div>
            <div class="accounts-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="label">Total Pendiente:</span>
                        <span class="amount">${formatCurrency(club.accounts_summary.total_pending)}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Total Facturado:</span>
                        <span class="amount">${formatCurrency(club.accounts_summary.total_receivable)}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Porcentaje Pagado:</span>
                        <span class="percentage">${club.accounts_summary.payment_percentage}%</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Cuentas Pendientes:</span>
                        <span class="count">${club.accounts_summary.accounts_count}</span>
                    </div>
                </div>
                ${club.accounts_summary.overdue_accounts > 0 ? 
                    `<div class="overdue-warning">
                        ⚠️ ${club.accounts_summary.overdue_accounts} cuenta(s) vencida(s)
                    </div>` : ''
                }
            </div>
            <div class="accounts-list">
                <h5>Cuentas Pendientes:</h5>
                ${club.accounts_summary.accounts.map(account => `
                    <div class="account-item ${account.is_overdue ? 'overdue' : ''}">
                        <div class="account-details">
                            <span class="account-id">#${account.id}</span>
                            <span class="account-amount">${formatCurrency(account.pending_amount)}</span>
                            <span class="account-status ${account.status.toLowerCase()}">${account.status}</span>
                        </div>
                        <div class="account-due-date">
                            Vence: ${formatDate(account.due_date)}
                            ${account.is_overdue ? '<span class="overdue-badge">VENCIDO</span>' : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `).join('');
}

function updateFilteredSummary(summary) {
    const elements = {
        'filtered-total-clubs': summary.total_clubs,
        'filtered-total-pending': formatCurrency(summary.total_pending_amount),
        'filtered-overdue': summary.overdue_accounts,
        'filtered-payment-percentage': summary.payment_percentage + '%'
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });
}

function showAppliedFilters(filters) {
    const container = document.getElementById('applied-filters');
    if (!container) return;
    
    const activeFilters = Object.entries(filters)
        .filter(([key, value]) => value !== null && value !== false && value !== '')
        .map(([key, value]) => {
            const label = getFilterLabel(key);
            return `<span class="filter-tag">${label}: ${value}</span>`;
        });
    
    container.innerHTML = activeFilters.length > 0 ? 
        `<h6>Filtros Aplicados:</h6>${activeFilters.join('')}` : 
        '<p>Sin filtros aplicados</p>';
}

function getFilterLabel(key) {
    const labels = {
        'min_amount': 'Monto Mínimo',
        'max_amount': 'Monto Máximo',
        'currency_id': 'Moneda',
        'overdue_only': 'Solo Vencidas',
        'status': 'Estado',
        'sort_by': 'Ordenar Por',
        'sort_order': 'Orden'
    };
    
    return labels[key] || key;
}

/**
 * Funciones utilitarias
 */

function formatCurrency(amount) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('es-AR');
}

function showError(message) {
    // Implementar según el sistema de notificaciones del proyecto
    console.error(message);
    
    // Ejemplo básico
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.textContent = message;
    
    const container = document.querySelector('.main-container');
    if (container) {
        container.insertBefore(errorDiv, container.firstChild);
        
        // Remover después de 5 segundos
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

/**
 * Inicialización y eventos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Ejemplo de uso en dashboard
    const eventId = document.body.dataset.eventId;
    if (eventId) {
        loadDashboardSummary(eventId);
    }
    
    // Configurar eventos de filtros
    setupFilterEvents();
});

function setupFilterEvents() {
    // Filtro por monto mínimo
    const minAmountInput = document.getElementById('min-amount-filter');
    if (minAmountInput) {
        minAmountInput.addEventListener('change', function() {
            const eventId = document.body.dataset.eventId;
            const minAmount = parseFloat(this.value);
            
            if (minAmount > 0) {
                searchByAmount(eventId, minAmount);
            }
        });
    }
    
    // Filtro por moneda
    const currencySelect = document.getElementById('currency-filter');
    if (currencySelect) {
        currencySelect.addEventListener('change', function() {
            const eventId = document.body.dataset.eventId;
            const currencyId = this.value;
            
            if (currencyId) {
                filterByCurrency(eventId, currencyId);
            }
        });
    }
    
    // Botón de cuentas vencidas
    const overdueButton = document.getElementById('overdue-only-filter');
    if (overdueButton) {
        overdueButton.addEventListener('click', function() {
            const eventId = document.body.dataset.eventId;
            loadOverdueAccounts(eventId);
        });
    }
}

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ClubsPendingAccountsAPI;
}
