/**
 * JavaScript para o frontend do plugin Cupom Promo
 *
 * @package CupomPromo
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    
    // Formulário de cupom
    var couponForm = $('#cupom-promo-form');
    var couponInput = $('#cupom-code');
    var resultDiv = $('#cupom-result');
    var submitBtn = couponForm.find('.cupom-promo-btn');
    
    // Submeter formulário
    couponForm.on('submit', function(e) {
        e.preventDefault();
        validateCoupon();
    });
    
    // Validar cupom ao pressionar Enter
    couponInput.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            validateCoupon();
        }
    });
    
    // Limpar resultado ao digitar
    couponInput.on('input', function() {
        clearResult();
    });
    
    /**
     * Validar cupom
     */
    function validateCoupon() {
        var couponCode = couponInput.val().trim();
        
        if (!couponCode) {
            showResult('Por favor, insira um código de cupom.', 'error');
            return;
        }
        
        // Adicionar estado de loading
        setLoadingState(true);
        
        $.ajax({
            url: cupomPromoFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'cupom_promo_validate_coupon',
                coupon_code: couponCode,
                nonce: cupomPromoFrontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    showResult(response.data.message, 'success');
                    
                    // Se houver desconto, mostrar detalhes
                    if (response.data.discount) {
                        showDiscountDetails(response.data);
                    }
                    
                    // Limpar campo após sucesso
                    couponInput.val('');
                } else {
                    showResult(response.data.message, 'error');
                }
            },
            error: function() {
                showResult('Erro ao validar cupom. Tente novamente.', 'error');
            },
            complete: function() {
                setLoadingState(false);
            }
        });
    }
    
    /**
     * Mostrar resultado da validação
     */
    function showResult(message, type) {
        resultDiv.removeClass('success error info')
                .addClass(type)
                .text(message)
                .show();
        
        // Scroll suave para o resultado
        $('html, body').animate({
            scrollTop: resultDiv.offset().top - 50
        }, 300);
        
        // Auto-remover após 10 segundos para sucesso
        if (type === 'success') {
            setTimeout(function() {
                resultDiv.fadeOut();
            }, 10000);
        }
    }
    
    /**
     * Limpar resultado
     */
    function clearResult() {
        resultDiv.removeClass('success error info').hide();
    }
    
    /**
     * Definir estado de loading
     */
    function setLoadingState(loading) {
        if (loading) {
            couponForm.addClass('loading');
            submitBtn.prop('disabled', true);
        } else {
            couponForm.removeClass('loading');
            submitBtn.prop('disabled', false);
        }
    }
    
    /**
     * Mostrar detalhes do desconto
     */
    function showDiscountDetails(data) {
        var discountText = '';
        
        if (data.discount_type === 'percentage') {
            discountText = data.discount + '% de desconto';
        } else {
            discountText = 'R$ ' + parseFloat(data.discount).toFixed(2).replace('.', ',') + ' de desconto';
        }
        
        // Criar elemento para detalhes do desconto
        var discountDetails = $('<div class="discount-details">' +
            '<h4>Detalhes do Desconto:</h4>' +
            '<p><strong>Valor:</strong> ' + discountText + '</p>' +
            '<p><strong>Status:</strong> Válido</p>' +
            '</div>');
        
        resultDiv.after(discountDetails);
        
        // Remover detalhes após 15 segundos
        setTimeout(function() {
            discountDetails.fadeOut();
        }, 15000);
    }
    
    /**
     * Aplicar cupom automaticamente (se configurado)
     */
    function applyCouponToCart(couponCode) {
        $.ajax({
            url: cupomPromoFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'cupom_promo_apply_to_cart',
                coupon_code: couponCode,
                nonce: cupomPromoFrontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    showResult('Cupom aplicado com sucesso ao carrinho!', 'success');
                    
                    // Atualizar carrinho se WooCommerce estiver ativo
                    if (typeof wc_add_to_cart_params !== 'undefined') {
                        $(document.body).trigger('wc_fragment_refresh');
                    }
                } else {
                    showResult(response.data.message, 'error');
                }
            },
            error: function() {
                showResult('Erro ao aplicar cupom ao carrinho.', 'error');
            }
        });
    }
    
    /**
     * Copiar código do cupom para área de transferência
     */
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showResult('Código copiado para área de transferência!', 'info');
            }).catch(function() {
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    }
    
    /**
     * Fallback para copiar para área de transferência
     */
    function fallbackCopyToClipboard(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showResult('Código copiado para área de transferência!', 'info');
        } catch (err) {
            showResult('Erro ao copiar código.', 'error');
        }
        
        document.body.removeChild(textArea);
    }
    
    /**
     * Inicializar tooltips
     */
    function initTooltips() {
        $('[data-tooltip]').each(function() {
            var $this = $(this);
            var tooltipText = $this.data('tooltip');
            
            $this.on('mouseenter', function() {
                var tooltip = $('<div class="cupom-tooltip">' + tooltipText + '</div>');
                $('body').append(tooltip);
                
                var offset = $this.offset();
                tooltip.css({
                    position: 'absolute',
                    top: offset.top - tooltip.outerHeight() - 10,
                    left: offset.left + ($this.outerWidth() / 2) - (tooltip.outerWidth() / 2),
                    zIndex: 9999
                });
            });
            
            $this.on('mouseleave', function() {
                $('.cupom-tooltip').remove();
            });
        });
    }
    
    /**
     * Inicializar formulários de cupom dinâmicos
     */
    function initDynamicForms() {
        // Adicionar botão de copiar código
        $('.copy-coupon-code').on('click', function(e) {
            e.preventDefault();
            var code = $(this).data('code');
            copyToClipboard(code);
        });
        
        // Adicionar botão de aplicar automaticamente
        $('.apply-coupon-auto').on('click', function(e) {
            e.preventDefault();
            var code = $(this).data('code');
            applyCouponToCart(code);
        });
    }
    
    /**
     * Animações suaves
     */
    function initAnimations() {
        // Animação de entrada para formulários
        $('.cupom-promo-form').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            }).delay(index * 100).animate({
                'opacity': '1'
            }, 500).css('transform', 'translateY(0)');
        });
        
        // Animação para resultados
        resultDiv.on('show', function() {
            $(this).css({
                'opacity': '0',
                'transform': 'scale(0.9)'
            }).animate({
                'opacity': '1'
            }, 300).css('transform', 'scale(1)');
        });
    }
    
    /**
     * Validação em tempo real
     */
    function initRealTimeValidation() {
        var validationTimer;
        
        couponInput.on('input', function() {
            clearTimeout(validationTimer);
            var value = $(this).val();
            
            if (value.length >= 3) {
                validationTimer = setTimeout(function() {
                    // Aqui você pode implementar validação em tempo real
                    // Por exemplo, verificar se o formato está correto
                    if (!/^[A-Z0-9_-]+$/i.test(value)) {
                        showResult('Código deve conter apenas letras, números, hífens e underscores.', 'error');
                    }
                }, 500);
            }
        });
    }
    
    /**
     * Inicializar funcionalidades
     */
    function init() {
        initTooltips();
        initDynamicForms();
        initAnimations();
        initRealTimeValidation();
        
        // Adicionar suporte para teclas de atalho
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + K para focar no campo de cupom
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
                e.preventDefault();
                couponInput.focus();
            }
        });
    }
    
    // Inicializar quando o documento estiver pronto
    init();
    
    // Re-inicializar para elementos carregados via AJAX
    $(document).on('cupom_promo_ready', function() {
        init();
    });
    
    // Expor funções para uso global
    window.CupomPromo = {
        validateCoupon: validateCoupon,
        applyCouponToCart: applyCouponToCart,
        copyToClipboard: copyToClipboard,
        showResult: showResult
    };
}); 