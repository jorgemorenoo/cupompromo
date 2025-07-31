/**
 * Componente CouponGrid - Grid de cupons interativo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import CouponCard from './CouponCard.js';

/**
 * Componente de grid de cupons com filtros e paginação
 */
export default function CouponGrid({ 
    initialCoupons = [],
    storeId = null,
    categoryId = null,
    limit = 12,
    showFilters = true,
    showPagination = true,
    className = ''
}) {
    const [coupons, setCoupons] = useState(initialCoupons);
    const [loading, setLoading] = useState(false);
    const [filters, setFilters] = useState({
        category: categoryId || '',
        store: storeId || '',
        type: '',
        sort: 'date',
        verified: false
    });
    const [pagination, setPagination] = useState({
        page: 1,
        total: 0,
        perPage: limit,
        totalPages: 0
    });
    const [viewMode, setViewMode] = useState('grid'); // grid, list, compact

    // Carregar cupons iniciais
    useEffect(() => {
        if (initialCoupons.length === 0) {
            loadCoupons();
        }
    }, []);

    // Recarregar quando filtros mudam
    useEffect(() => {
        if (initialCoupons.length === 0) {
            setPagination(prev => ({ ...prev, page: 1 }));
            loadCoupons();
        }
    }, [filters]);

    /**
     * Carrega cupons via AJAX
     */
    const loadCoupons = async (page = 1) => {
        setLoading(true);
        
        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'cupompromo_get_coupons',
                    page: page,
                    limit: pagination.perPage,
                    filters: JSON.stringify(filters),
                    nonce: cupompromo_ajax.nonce
                })
            });

            const data = await response.json();
            
            if (data.success) {
                setCoupons(data.data.coupons);
                setPagination({
                    page: data.data.pagination.page,
                    total: data.data.pagination.total,
                    perPage: data.data.pagination.perPage,
                    totalPages: data.data.pagination.totalPages
                });
            }
        } catch (error) {
            console.error('Erro ao carregar cupons:', error);
        } finally {
            setLoading(false);
        }
    };

    /**
     * Atualiza filtros
     */
    const updateFilter = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    /**
     * Limpa todos os filtros
     */
    const clearFilters = () => {
        setFilters({
            category: categoryId || '',
            store: storeId || '',
            type: '',
            sort: 'date',
            verified: false
        });
    };

    /**
     * Navega para página específica
     */
    const goToPage = (page) => {
        if (page >= 1 && page <= pagination.totalPages) {
            loadCoupons(page);
        }
    };

    /**
     * Copia código do cupom
     */
    const copyCouponCode = async (code) => {
        try {
            await navigator.clipboard.writeText(code);
            
            // Mostrar notificação
            showNotification(__('Código copiado!', 'cupompromo'), 'success');
            
            // Registrar analytics
            trackCouponAction('copy', code);
        } catch (error) {
            console.error('Erro ao copiar código:', error);
            showNotification(__('Erro ao copiar código', 'cupompromo'), 'error');
        }
    };

    /**
     * Registra ação do usuário
     */
    const trackCouponAction = (action, couponCode) => {
        // TODO: Implementar tracking de analytics
        console.log('Coupon action:', action, couponCode);
    };

    /**
     * Mostra notificação
     */
    const showNotification = (message, type = 'info') => {
        // TODO: Implementar sistema de notificações
        console.log('Notification:', message, type);
    };

    return (
        <div className={`cupompromo-coupon-grid ${className}`}>
            {/* Filtros */}
            {showFilters && (
                <div className="coupon-filters">
                    <div className="filters-header">
                        <h3>{__('Filtros', 'cupompromo')}</h3>
                        <button 
                            type="button" 
                            className="clear-filters"
                            onClick={clearFilters}
                        >
                            {__('Limpar Filtros', 'cupompromo')}
                        </button>
                    </div>

                    <div className="filters-content">
                        <div className="filter-group">
                            <label htmlFor="coupon-category">{__('Categoria:', 'cupompromo')}</label>
                            <select
                                id="coupon-category"
                                value={filters.category}
                                onChange={(e) => updateFilter('category', e.target.value)}
                            >
                                <option value="">{__('Todas as categorias', 'cupompromo')}</option>
                                {cupompromo_ajax.categories?.map(category => (
                                    <option key={category.term_id} value={category.term_id}>
                                        {category.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="filter-group">
                            <label htmlFor="coupon-type">{__('Tipo:', 'cupompromo')}</label>
                            <select
                                id="coupon-type"
                                value={filters.type}
                                onChange={(e) => updateFilter('type', e.target.value)}
                            >
                                <option value="">{__('Todos os tipos', 'cupompromo')}</option>
                                <option value="code">{__('Códigos', 'cupompromo')}</option>
                                <option value="offer">{__('Ofertas', 'cupompromo')}</option>
                            </select>
                        </div>

                        <div className="filter-group">
                            <label htmlFor="coupon-sort">{__('Ordenar por:', 'cupompromo')}</label>
                            <select
                                id="coupon-sort"
                                value={filters.sort}
                                onChange={(e) => updateFilter('sort', e.target.value)}
                            >
                                <option value="date">{__('Mais Recentes', 'cupompromo')}</option>
                                <option value="popular">{__('Mais Populares', 'cupompromo')}</option>
                                <option value="discount">{__('Maior Desconto', 'cupompromo')}</option>
                                <option value="verified">{__('Verificados', 'cupompromo')}</option>
                            </select>
                        </div>

                        <div className="filter-group">
                            <label className="checkbox-label">
                                <input
                                    type="checkbox"
                                    checked={filters.verified}
                                    onChange={(e) => updateFilter('verified', e.target.checked)}
                                />
                                <span className="checkmark"></span>
                                {__('Apenas verificados', 'cupompromo')}
                            </label>
                        </div>
                    </div>
                </div>
            )}

            {/* Controles de visualização */}
            <div className="grid-controls">
                <div className="view-modes">
                    <button
                        type="button"
                        className={`view-mode ${viewMode === 'grid' ? 'active' : ''}`}
                        onClick={() => setViewMode('grid')}
                        aria-label={__('Visualização em grade', 'cupompromo')}
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </button>
                    <button
                        type="button"
                        className={`view-mode ${viewMode === 'list' ? 'active' : ''}`}
                        onClick={() => setViewMode('list')}
                        aria-label={__('Visualização em lista', 'cupompromo')}
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </button>
                    <button
                        type="button"
                        className={`view-mode ${viewMode === 'compact' ? 'active' : ''}`}
                        onClick={() => setViewMode('compact')}
                        aria-label={__('Visualização compacta', 'cupompromo')}
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="9" y1="9" x2="15" y2="9"></line>
                            <line x1="9" y1="12" x2="15" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                    </button>
                </div>

                <div className="results-info">
                    {pagination.total > 0 && (
                        <span className="results-count">
                            {__('Mostrando', 'cupompromo')} {((pagination.page - 1) * pagination.perPage) + 1}-{Math.min(pagination.page * pagination.perPage, pagination.total)} {__('de', 'cupompromo')} {pagination.total} {__('cupons', 'cupompromo')}
                        </span>
                    )}
                </div>
            </div>

            {/* Loading state */}
            {loading && (
                <div className="loading-state">
                    <div className="spinner"></div>
                    <p>{__('Carregando cupons...', 'cupompromo')}</p>
                </div>
            )}

            {/* Grid de cupons */}
            {!loading && (
                <div className={`coupons-grid view-${viewMode}`}>
                    {coupons.length > 0 ? (
                        coupons.map((coupon) => (
                            <CouponCard
                                key={coupon.id}
                                coupon={coupon}
                                viewMode={viewMode}
                                onCopyCode={copyCouponCode}
                                onTrackAction={trackCouponAction}
                            />
                        ))
                    ) : (
                        <div className="no-coupons">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <h3>{__('Nenhum cupom encontrado', 'cupompromo')}</h3>
                            <p>{__('Tente ajustar os filtros ou buscar por outro termo.', 'cupompromo')}</p>
                        </div>
                    )}
                </div>
            )}

            {/* Paginação */}
            {showPagination && pagination.totalPages > 1 && (
                <div className="pagination">
                    <button
                        type="button"
                        className="pagination-btn prev"
                        onClick={() => goToPage(pagination.page - 1)}
                        disabled={pagination.page <= 1}
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                        {__('Anterior', 'cupompromo')}
                    </button>

                    <div className="pagination-numbers">
                        {Array.from({ length: pagination.totalPages }, (_, i) => i + 1).map(page => (
                            <button
                                key={page}
                                type="button"
                                className={`pagination-btn ${page === pagination.page ? 'active' : ''}`}
                                onClick={() => goToPage(page)}
                            >
                                {page}
                            </button>
                        ))}
                    </div>

                    <button
                        type="button"
                        className="pagination-btn next"
                        onClick={() => goToPage(pagination.page + 1)}
                        disabled={pagination.page >= pagination.totalPages}
                    >
                        {__('Próxima', 'cupompromo')}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>
            )}
        </div>
    );
} 