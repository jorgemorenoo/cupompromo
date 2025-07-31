/**
 * Componente React para Modal de Cupons
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const CouponModal = ({ coupon, isOpen, onClose, onCopy }) => {
    const [copied, setCopied] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }

        return () => {
            document.body.style.overflow = 'unset';
        };
    }, [isOpen]);

    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                onClose();
            }
        };

        if (isOpen) {
            document.addEventListener('keydown', handleEscape);
        }

        return () => {
            document.removeEventListener('keydown', handleEscape);
        };
    }, [isOpen, onClose]);

    const handleCopyCode = async () => {
        if (coupon.coupon_code) {
            try {
                await navigator.clipboard.writeText(coupon.coupon_code);
                setCopied(true);
                
                if (onCopy) {
                    onCopy(coupon.coupon_code);
                }

                setTimeout(() => setCopied(false), 2000);
            } catch (err) {
                console.error('Erro ao copiar código:', err);
            }
        }
    };

    const handleRedirect = () => {
        if (coupon.affiliate_url) {
            setLoading(true);
            
            // Registrar redirecionamento
            fetch(cupompromoFrontend.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'cupompromo_track_redirect',
                    nonce: cupompromoFrontend.nonce,
                    coupon_id: coupon.id
                })
            }).finally(() => {
                window.open(coupon.affiliate_url, '_blank');
                setLoading(false);
                onClose();
            });
        }
    };

    if (!isOpen || !coupon) {
        return null;
    }

    return (
        <div className="cupompromo-modal cupompromo-modal--active">
            <div className="cupompromo-modal__content">
                <div className="cupompromo-modal__header">
                    <h3 className="cupompromo-modal__title">
                        {__('Cupom de Desconto', 'cupompromo')}
                    </h3>
                    <button 
                        className="cupompromo-modal__close"
                        onClick={onClose}
                        aria-label={__('Fechar modal', 'cupompromo')}
                    >
                        ×
                    </button>
                </div>

                <div className="cupompromo-coupon-modal">
                    {/* Informações da Loja */}
                    <div className="cupompromo-coupon-modal__store">
                        <img 
                            src={coupon.store_logo} 
                            alt={coupon.store_name}
                            className="cupompromo-coupon-modal__store-logo"
                        />
                        <div className="cupompromo-coupon-modal__store-info">
                            <h4 className="cupompromo-coupon-modal__store-name">
                                {coupon.store_name}
                            </h4>
                            <p className="cupompromo-coupon-modal__store-description">
                                {coupon.store_description}
                            </p>
                        </div>
                    </div>

                    {/* Detalhes do Cupom */}
                    <div className="cupompromo-coupon-modal__details">
                        <h4 className="cupompromo-coupon-modal__title">
                            {coupon.title}
                        </h4>
                        
                        <div className="cupompromo-coupon-modal__discount">
                            <span className="cupompromo-coupon-modal__discount-value">
                                {coupon.discount_value}
                            </span>
                            <span className="cupompromo-coupon-modal__discount-type">
                                {coupon.discount_type === 'percentage' ? '% OFF' : 'R$ OFF'}
                            </span>
                        </div>

                        {coupon.description && (
                            <p className="cupompromo-coupon-modal__description">
                                {coupon.description}
                            </p>
                        )}

                        {/* Código do Cupom */}
                        {coupon.coupon_code && (
                            <div className="cupompromo-coupon-modal__code-section">
                                <label className="cupompromo-coupon-modal__code-label">
                                    {__('Código do Cupom:', 'cupompromo')}
                                </label>
                                <div className="cupompromo-coupon-modal__code-container">
                                    <input
                                        type="text"
                                        value={coupon.coupon_code}
                                        readOnly
                                        className="cupompromo-coupon-modal__code-input"
                                    />
                                    <button
                                        className={`cupompromo-btn cupompromo-btn--${copied ? 'secondary' : 'primary'}`}
                                        onClick={handleCopyCode}
                                        disabled={copied}
                                    >
                                        {copied ? __('Copiado!', 'cupompromo') : __('Copiar', 'cupompromo')}
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* Instruções */}
                        <div className="cupompromo-coupon-modal__instructions">
                            <h5>{__('Como usar:', 'cupompromo')}</h5>
                            <ol>
                                <li>{__('Clique no botão "Ir para a Loja" abaixo', 'cupompromo')}</li>
                                {coupon.coupon_code && (
                                    <li>{__('Cole o código do cupom no carrinho de compras', 'cupompromo')}</li>
                                )}
                                <li>{__('Aproveite seu desconto!', 'cupompromo')}</li>
                            </ol>
                        </div>

                        {/* Informações Adicionais */}
                        <div className="cupompromo-coupon-modal__info">
                            {coupon.expiry_date && (
                                <p className="cupompromo-coupon-modal__expiry">
                                    <strong>{__('Válido até:', 'cupompromo')}</strong> {coupon.expiry_date}
                                </p>
                            )}
                            
                            {coupon.verified_date && (
                                <p className="cupompromo-coupon-modal__verified">
                                    <strong>{__('Verificado em:', 'cupompromo')}</strong> {coupon.verified_date}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Botões de Ação */}
                    <div className="cupompromo-coupon-modal__actions">
                        <button
                            className="cupompromo-btn cupompromo-btn--primary cupompromo-btn--large cupompromo-btn--full"
                            onClick={handleRedirect}
                            disabled={loading}
                        >
                            {loading ? __('Redirecionando...', 'cupompromo') : __('Ir para a Loja', 'cupompromo')}
                        </button>
                        
                        <button
                            className="cupompromo-btn cupompromo-btn--outline cupompromo-btn--full"
                            onClick={onClose}
                        >
                            {__('Fechar', 'cupompromo')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CouponModal; 