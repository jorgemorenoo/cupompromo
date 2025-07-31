/**
 * Hook customizado para gerenciar cupons
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

import { useState, useEffect, useCallback } from '@wordpress/element';

/**
 * Hook para gerenciar cupons com cache e paginação
 */
export function useCoupons(options = {}) {
    const {
        storeId = null,
        categoryId = null,
        limit = 12,
        initialFilters = {}
    } = options;

    const [coupons, setCoupons] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [filters, setFilters] = useState(initialFilters);
    const [pagination, setPagination] = useState({
        page: 1,
        total: 0,
        perPage: limit,
        totalPages: 0
    });

    /**
     * Carrega cupons
     */
    const loadCoupons = useCallback(async (page = 1, newFilters = filters) => {
        setLoading(true);
        setError(null);

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
                    store_id: storeId,
                    category_id: categoryId,
                    filters: JSON.stringify(newFilters),
                    nonce: cupompromo_ajax.nonce
                })
            });

            const data = await response.json();
            
            if (data.success) {
                setCoupons(data.data.coupons);
                setPagination(data.data.pagination);
            } else {
                setError(data.data.message || 'Erro ao carregar cupons');
            }
        } catch (err) {
            setError('Erro de conexão');
            console.error('Erro ao carregar cupons:', err);
        } finally {
            setLoading(false);
        }
    }, [storeId, categoryId, pagination.perPage, filters]);

    /**
     * Atualiza filtros
     */
    const updateFilters = useCallback((newFilters) => {
        setFilters(prev => ({ ...prev, ...newFilters }));
        setPagination(prev => ({ ...prev, page: 1 }));
    }, []);

    /**
     * Limpa filtros
     */
    const clearFilters = useCallback(() => {
        setFilters(initialFilters);
        setPagination(prev => ({ ...prev, page: 1 }));
    }, [initialFilters]);

    /**
     * Navega para página
     */
    const goToPage = useCallback((page) => {
        if (page >= 1 && page <= pagination.totalPages) {
            loadCoupons(page);
        }
    }, [loadCoupons, pagination.totalPages]);

    /**
     * Recarrega cupons
     */
    const refresh = useCallback(() => {
        loadCoupons(1);
    }, [loadCoupons]);

    // Carregar cupons iniciais
    useEffect(() => {
        loadCoupons(1);
    }, [loadCoupons]);

    return {
        coupons,
        loading,
        error,
        filters,
        pagination,
        updateFilters,
        clearFilters,
        goToPage,
        refresh,
        loadCoupons
    };
}

/**
 * Hook para cupom individual
 */
export function useCoupon(couponId) {
    const [coupon, setCoupon] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const loadCoupon = useCallback(async () => {
        if (!couponId) return;

        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'cupompromo_get_coupon',
                    coupon_id: couponId,
                    nonce: cupompromo_ajax.nonce
                })
            });

            const data = await response.json();
            
            if (data.success) {
                setCoupon(data.data.coupon);
            } else {
                setError(data.data.message || 'Erro ao carregar cupom');
            }
        } catch (err) {
            setError('Erro de conexão');
            console.error('Erro ao carregar cupom:', err);
        } finally {
            setLoading(false);
        }
    }, [couponId]);

    useEffect(() => {
        loadCoupon();
    }, [loadCoupon]);

    return {
        coupon,
        loading,
        error,
        refresh: loadCoupon
    };
} 