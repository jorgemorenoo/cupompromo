/**
 * Componente SearchBar - Busca em tempo real
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Componente de busca com funcionalidades avançadas
 */
export default function SearchBar({ 
    placeholder = __('Buscar cupons de desconto...', 'cupompromo'),
    showFilters = true,
    liveSearch = true,
    onSearch,
    className = ''
}) {
    const [query, setQuery] = useState('');
    const [isSearching, setIsSearching] = useState(false);
    const [showFiltersPanel, setShowFiltersPanel] = useState(false);
    const [filters, setFilters] = useState({
        category: '',
        store: '',
        type: '',
        sort: 'relevance'
    });
    const [suggestions, setSuggestions] = useState([]);
    const [showSuggestions, setShowSuggestions] = useState(false);
    
    const searchTimeout = useRef(null);
    const searchInputRef = useRef(null);
    const suggestionsRef = useRef(null);

    // Debounced search
    useEffect(() => {
        if (searchTimeout.current) {
            clearTimeout(searchTimeout.current);
        }

        if (query.length >= 2 && liveSearch) {
            setIsSearching(true);
            searchTimeout.current = setTimeout(() => {
                performSearch(query);
            }, 300);
        } else {
            setSuggestions([]);
            setShowSuggestions(false);
        }

        return () => {
            if (searchTimeout.current) {
                clearTimeout(searchTimeout.current);
            }
        };
    }, [query, liveSearch]);

    // Click outside to close suggestions
    useEffect(() => {
        function handleClickOutside(event) {
            if (suggestionsRef.current && !suggestionsRef.current.contains(event.target) &&
                searchInputRef.current && !searchInputRef.current.contains(event.target)) {
                setShowSuggestions(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    /**
     * Realiza busca via AJAX
     */
    const performSearch = async (searchQuery) => {
        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'cupompromo_live_search',
                    query: searchQuery,
                    filters: JSON.stringify(filters),
                    nonce: cupompromo_ajax.nonce
                })
            });

            const data = await response.json();
            
            if (data.success) {
                setSuggestions(data.data.results);
                setShowSuggestions(true);
            }
        } catch (error) {
            console.error('Erro na busca:', error);
        } finally {
            setIsSearching(false);
        }
    };

    /**
     * Submete o formulário de busca
     */
    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (onSearch) {
            onSearch({ query, filters });
        } else {
            // Redireciona para página de busca
            const searchUrl = new URL(window.location.origin + '/');
            searchUrl.searchParams.set('s', query);
            searchUrl.searchParams.set('post_type', 'cupompromo_coupon');
            
            if (filters.category) searchUrl.searchParams.set('category', filters.category);
            if (filters.store) searchUrl.searchParams.set('store', filters.store);
            if (filters.type) searchUrl.searchParams.set('type', filters.type);
            if (filters.sort) searchUrl.searchParams.set('sort', filters.sort);
            
            window.location.href = searchUrl.toString();
        }
    };

    /**
     * Seleciona uma sugestão
     */
    const selectSuggestion = (suggestion) => {
        setQuery(suggestion.title);
        setShowSuggestions(false);
        
        if (suggestion.type === 'coupon') {
            window.location.href = suggestion.url;
        } else if (suggestion.type === 'store') {
            window.location.href = suggestion.url;
        }
    };

    /**
     * Atualiza filtros
     */
    const updateFilter = (key, value) => {
        const newFilters = { ...filters, [key]: value };
        setFilters(newFilters);
        
        // Re-busca se há query ativa
        if (query.length >= 2) {
            performSearch(query);
        }
    };

    return (
        <div className={`cupompromo-search-bar ${className}`}>
            <form onSubmit={handleSubmit} className="search-form">
                <div className="search-input-wrapper">
                    <input
                        ref={searchInputRef}
                        type="search"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder={placeholder}
                        className="search-input"
                        autoComplete="off"
                        aria-label={__('Buscar cupons', 'cupompromo')}
                    />
                    
                    <button 
                        type="submit" 
                        className="search-submit"
                        disabled={isSearching}
                        aria-label={__('Buscar', 'cupompromo')}
                    >
                        {isSearching ? (
                            <div className="spinner"></div>
                        ) : (
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        )}
                    </button>
                </div>

                {showFilters && (
                    <div className="search-filters">
                        <button
                            type="button"
                            className="filter-toggle"
                            onClick={() => setShowFiltersPanel(!showFiltersPanel)}
                            aria-expanded={showFiltersPanel}
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                            </svg>
                            {__('Filtros', 'cupompromo')}
                        </button>

                        {showFiltersPanel && (
                            <div className="filter-panel">
                                <div className="filter-group">
                                    <label htmlFor="category-filter">{__('Categoria:', 'cupompromo')}</label>
                                    <select
                                        id="category-filter"
                                        value={filters.category}
                                        onChange={(e) => updateFilter('category', e.target.value)}
                                    >
                                        <option value="">{__('Todas as categorias', 'cupompromo')}</option>
                                        {cupompromo_ajax.categories?.map(category => (
                                            <option key={category.slug} value={category.slug}>
                                                {category.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="filter-group">
                                    <label htmlFor="store-filter">{__('Loja:', 'cupompromo')}</label>
                                    <select
                                        id="store-filter"
                                        value={filters.store}
                                        onChange={(e) => updateFilter('store', e.target.value)}
                                    >
                                        <option value="">{__('Todas as lojas', 'cupompromo')}</option>
                                        {cupompromo_ajax.stores?.map(store => (
                                            <option key={store.id} value={store.id}>
                                                {store.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="filter-group">
                                    <label htmlFor="type-filter">{__('Tipo:', 'cupompromo')}</label>
                                    <select
                                        id="type-filter"
                                        value={filters.type}
                                        onChange={(e) => updateFilter('type', e.target.value)}
                                    >
                                        <option value="">{__('Todos os tipos', 'cupompromo')}</option>
                                        <option value="code">{__('Códigos', 'cupompromo')}</option>
                                        <option value="offer">{__('Ofertas', 'cupompromo')}</option>
                                    </select>
                                </div>

                                <div className="filter-group">
                                    <label htmlFor="sort-filter">{__('Ordenar por:', 'cupompromo')}</label>
                                    <select
                                        id="sort-filter"
                                        value={filters.sort}
                                        onChange={(e) => updateFilter('sort', e.target.value)}
                                    >
                                        <option value="relevance">{__('Relevância', 'cupompromo')}</option>
                                        <option value="date">{__('Mais Recentes', 'cupompromo')}</option>
                                        <option value="popular">{__('Mais Populares', 'cupompromo')}</option>
                                        <option value="discount">{__('Maior Desconto', 'cupompromo')}</option>
                                    </select>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </form>

            {/* Sugestões de busca */}
            {showSuggestions && suggestions.length > 0 && (
                <div ref={suggestionsRef} className="search-suggestions">
                    {suggestions.map((suggestion, index) => (
                        <div
                            key={index}
                            className="suggestion-item"
                            onClick={() => selectSuggestion(suggestion)}
                        >
                            <div className="suggestion-icon">
                                {suggestion.type === 'coupon' ? '🎫' : '🏪'}
                            </div>
                            <div className="suggestion-content">
                                <div className="suggestion-title">{suggestion.title}</div>
                                <div className="suggestion-meta">{suggestion.meta}</div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Estado vazio */}
            {showSuggestions && suggestions.length === 0 && query.length >= 2 && !isSearching && (
                <div className="search-suggestions empty">
                    <div className="no-results">
                        {__('Nenhum resultado encontrado', 'cupompromo')}
                    </div>
                </div>
            )}
        </div>
    );
} 