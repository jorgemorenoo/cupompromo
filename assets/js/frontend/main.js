/**
 * Cupompromo Frontend JavaScript
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Configurações globais
    const Cupompromo = {
        // Configurações
        config: {
            ajaxUrl: cupompromoFrontend.ajaxurl,
            nonce: cupompromoFrontend.nonce,
            strings: cupompromoFrontend.strings
        },

        // Inicialização
        init: function() {
            this.bindEvents();
            this.initComponents();
        },

        // Bind de eventos
        bindEvents: function() {
            // Busca de cupons
            $(document).on('submit', '.cupompromo-search__form', this.handleSearch);
            
            // Validação de cupons
            $(document).on('click', '.cupompromo-coupon-card__button', this.handleCouponClick);
            
            // Modal
            $(document).on('click', '[data-cupompromo-modal]', this.openModal);
            $(document).on('click', '.cupompromo-modal__close, .cupompromo-modal', this.closeModal);
            $(document).on('keydown', this.handleModalKeydown);
            
            // Filtros
            $(document).on('change', '.cupompromo-filter', this.handleFilterChange);
            
            // Lazy loading
            $(document).on('scroll', this.handleScroll);
        },

        // Inicializar componentes
        initComponents: function() {
            this.initSearchAutocomplete();
            this.initLazyLoading();
            this.initTooltips();
        },

        // Busca de cupons
        handleSearch: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $input = $form.find('.cupompromo-search__input');
            const $button = $form.find('.cupompromo-search__button');
            const query = $input.val().trim();
            
            if (!query) {
                Cupompromo.showMessage('Por favor, insira um termo de busca.', 'warning');
                return;
            }
            
            // Mostrar loading
            $button.addClass('cupompromo-loading').prop('disabled', true);
            
            // Fazer requisição AJAX
            $.ajax({
                url: Cupompromo.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cupompromo_search_coupons',
                    nonce: Cupompromo.config.nonce,
                    query: query
                },
                success: function(response) {
                    if (response.success) {
                        Cupompromo.updateResults(response.data);
                    } else {
                        Cupompromo.showMessage(response.data.message || 'Erro na busca.', 'error');
                    }
                },
                error: function() {
                    Cupompromo.showMessage('Erro de conexão. Tente novamente.', 'error');
                },
                complete: function() {
                    $button.removeClass('cupompromo-loading').prop('disabled', false);
                }
            });
        },

        // Clique em cupom
        handleCouponClick: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const couponId = $button.data('coupon-id');
            
            if (!$button.hasClass('cupompromo-loading')) {
                $button.addClass('cupompromo-loading').prop('disabled', true);
                
                // Registrar clique
                $.ajax({
                    url: Cupompromo.config.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cupompromo_track_click',
                        nonce: Cupompromo.config.nonce,
                        coupon_id: couponId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Abrir modal com detalhes do cupom
                            Cupompromo.showCouponModal(couponId);
                        }
                    },
                    complete: function() {
                        $button.removeClass('cupompromo-loading').prop('disabled', false);
                    }
                });
            }
        },

        // Abrir modal
        openModal: function(e) {
            e.preventDefault();
            
            const modalId = $(this).data('cupompromo-modal');
            const $modal = $('#' + modalId);
            
            if ($modal.length) {
                $modal.addClass('cupompromo-modal--active');
                $('body').addClass('cupompromo-modal-open');
                
                // Focus no primeiro elemento interativo
                $modal.find('input, button, a').first().focus();
            }
        },

        // Fechar modal
        closeModal: function(e) {
            const $modal = $(this).closest('.cupompromo-modal');
            
            if ($(this).hasClass('cupompromo-modal') || $(this).hasClass('cupompromo-modal__close')) {
                $modal.removeClass('cupompromo-modal--active');
                $('body').removeClass('cupompromo-modal-open');
            }
        },

        // Teclas do modal
        handleModalKeydown: function(e) {
            if (e.key === 'Escape') {
                const $activeModal = $('.cupompromo-modal--active');
                if ($activeModal.length) {
                    $activeModal.removeClass('cupompromo-modal--active');
                    $('body').removeClass('cupompromo-modal-open');
                }
            }
        },

        // Filtros
        handleFilterChange: function() {
            const $filter = $(this);
            const filterType = $filter.data('filter-type');
            const filterValue = $filter.val();
            
            // Atualizar URL sem recarregar a página
            const url = new URL(window.location);
            url.searchParams.set(filterType, filterValue);
            window.history.pushState({}, '', url);
            
            // Aplicar filtro via AJAX
            Cupompromo.applyFilters();
        },

        // Aplicar filtros
        applyFilters: function() {
            const filters = {};
            
            $('.cupompromo-filter').each(function() {
                const $filter = $(this);
                const filterType = $filter.data('filter-type');
                const filterValue = $filter.val();
                
                if (filterValue) {
                    filters[filterType] = filterValue;
                }
            });
            
            // Fazer requisição AJAX com filtros
            $.ajax({
                url: Cupompromo.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cupompromo_filter_coupons',
                    nonce: Cupompromo.config.nonce,
                    filters: filters
                },
                success: function(response) {
                    if (response.success) {
                        Cupompromo.updateResults(response.data);
                    }
                }
            });
        },

        // Atualizar resultados
        updateResults: function(data) {
            const $container = $('.cupompromo-results');
            
            if ($container.length) {
                $container.html(data.html);
                
                // Scroll para o topo dos resultados
                $('html, body').animate({
                    scrollTop: $container.offset().top - 100
                }, 300);
            }
        },

        // Mostrar modal de cupom
        showCouponModal: function(couponId) {
            $.ajax({
                url: Cupompromo.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cupompromo_get_coupon_modal',
                    nonce: Cupompromo.config.nonce,
                    coupon_id: couponId
                },
                success: function(response) {
                    if (response.success) {
                        // Criar modal dinamicamente
                        const $modal = $(response.data.html);
                        $('body').append($modal);
                        
                        // Abrir modal
                        $modal.addClass('cupompromo-modal--active');
                        $('body').addClass('cupompromo-modal-open');
                    }
                }
            });
        },

        // Autocomplete na busca
        initSearchAutocomplete: function() {
            const $searchInput = $('.cupompromo-search__input');
            
            if ($searchInput.length) {
                let searchTimeout;
                
                $searchInput.on('input', function() {
                    const query = $(this).val().trim();
                    
                    clearTimeout(searchTimeout);
                    
                    if (query.length >= 2) {
                        searchTimeout = setTimeout(function() {
                            Cupompromo.getSearchSuggestions(query);
                        }, 300);
                    } else {
                        Cupompromo.hideSearchSuggestions();
                    }
                });
            }
        },

        // Buscar sugestões
        getSearchSuggestions: function(query) {
            $.ajax({
                url: Cupompromo.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cupompromo_search_suggestions',
                    nonce: Cupompromo.config.nonce,
                    query: query
                },
                success: function(response) {
                    if (response.success) {
                        Cupompromo.showSearchSuggestions(response.data);
                    }
                }
            });
        },

        // Mostrar sugestões
        showSearchSuggestions: function(suggestions) {
            let $suggestions = $('.cupompromo-search-suggestions');
            
            if (!$suggestions.length) {
                $suggestions = $('<div class="cupompromo-search-suggestions"></div>');
                $('.cupompromo-search__input').after($suggestions);
            }
            
            if (suggestions.length > 0) {
                const html = suggestions.map(function(suggestion) {
                    return `<div class="cupompromo-search-suggestion" data-value="${suggestion.value}">${suggestion.label}</div>`;
                }).join('');
                
                $suggestions.html(html).show();
                
                // Bind de eventos nas sugestões
                $suggestions.find('.cupompromo-search-suggestion').on('click', function() {
                    const value = $(this).data('value');
                    $('.cupompromo-search__input').val(value);
                    $suggestions.hide();
                    $('.cupompromo-search__form').submit();
                });
            } else {
                $suggestions.hide();
            }
        },

        // Esconder sugestões
        hideSearchSuggestions: function() {
            $('.cupompromo-search-suggestions').hide();
        },

        // Lazy loading
        initLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('cupompromo-lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(function(img) {
                    imageObserver.observe(img);
                });
            }
        },

        // Scroll infinito
        handleScroll: function() {
            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();
            
            if (scrollTop + windowHeight >= documentHeight - 100) {
                Cupompromo.loadMoreContent();
            }
        },

        // Carregar mais conteúdo
        loadMoreContent: function() {
            if (!Cupompromo.isLoadingMore) {
                Cupompromo.isLoadingMore = true;
                
                const page = parseInt($('.cupompromo-results').data('page') || 1) + 1;
                
                $.ajax({
                    url: Cupompromo.config.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cupompromo_load_more',
                        nonce: Cupompromo.config.nonce,
                        page: page
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.cupompromo-results').append(response.data.html);
                            $('.cupompromo-results').data('page', page);
                        }
                    },
                    complete: function() {
                        Cupompromo.isLoadingMore = false;
                    }
                });
            }
        },

        // Tooltips
        initTooltips: function() {
            $('[data-tooltip]').each(function() {
                const $element = $(this);
                const tooltipText = $element.data('tooltip');
                
                $element.on('mouseenter', function() {
                    Cupompromo.showTooltip($element, tooltipText);
                }).on('mouseleave', function() {
                    Cupompromo.hideTooltip();
                });
            });
        },

        // Mostrar tooltip
        showTooltip: function($element, text) {
            const $tooltip = $('<div class="cupompromo-tooltip">' + text + '</div>');
            $('body').append($tooltip);
            
            const elementRect = $element[0].getBoundingClientRect();
            const tooltipRect = $tooltip[0].getBoundingClientRect();
            
            $tooltip.css({
                position: 'fixed',
                top: elementRect.top - tooltipRect.height - 10,
                left: elementRect.left + (elementRect.width / 2) - (tooltipRect.width / 2),
                zIndex: 9999
            });
            
            $tooltip.addClass('cupompromo-tooltip--visible');
        },

        // Esconder tooltip
        hideTooltip: function() {
            $('.cupompromo-tooltip').remove();
        },

        // Mostrar mensagem
        showMessage: function(message, type = 'info') {
            const $message = $('<div class="cupompromo-message cupompromo-message--' + type + '">' + message + '</div>');
            $('body').append($message);
            
            $message.addClass('cupompromo-message--visible');
            
            setTimeout(function() {
                $message.removeClass('cupompromo-message--visible');
                setTimeout(function() {
                    $message.remove();
                }, 300);
            }, 3000);
        },

        // Utilitários
        utils: {
            // Debounce
            debounce: function(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            },

            // Throttle
            throttle: function(func, limit) {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            },

            // Format currency
            formatCurrency: function(amount, currency = 'BRL') {
                return new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: currency
                }).format(amount);
            },

            // Format date
            formatDate: function(date) {
                return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
            }
        }
    };

    // Inicializar quando DOM estiver pronto
    $(document).ready(function() {
        Cupompromo.init();
    });

    // Expor para uso global
    window.Cupompromo = Cupompromo;

})(jQuery); 