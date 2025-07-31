/**
 * Componente CouponCard - Card de cupom interativo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Componente de card de cupom com interações
 */
export default function CouponCard({ 
    coupon, 
    viewMode = 'grid',
    onCopyCode,
    onTrackAction,
    className = ''
}) {
    const [isCopied, setIsCopied] = useState(false);
    const [isExpanded, setIsExpanded] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    /**
     * Copia código do cupom
     */
    const handleCopyCode = async () => {
        if (!coupon.coupon_code) return;

        setIsLoading(true);
        
        try {
            await onCopyCode(coupon.coupon_code);
            setIsCopied(true);
            
            // Reset após 2 segundos
            setTimeout(() => {
                setIsCopied(false);
            }, 2000);
            
            // Registrar ação
            onTrackAction('copy', coupon.coupon_code);
        } catch (error) {
            console.error('Erro ao copiar código:', error);
        } finally {
            setIsLoading(false);
        }
    };

    /**
     * Abre link do cupom
     */
    const handleOpenCoupon = () => {
        // Registrar ação
        onTrackAction('click', coupon.coupon_code);
        
        // Abrir em nova aba
        window.open(coupon.affiliate_url, '_blank', 'noopener,noreferrer');
    };

    /**
     * Formata data
     */
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    /**
     * Calcula tempo restante
     */
    const getTimeRemaining = (expiryDate) => {
        const now = new Date();
        const expiry = new Date(expiryDate);
        const diff = expiry - now;
        
        if (diff <= 0) return null;
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        
        if (days > 0) {
            return `${days} ${days === 1 ? __('dia', 'cupompromo') : __('dias', 'cupompromo')}`;
        } else if (hours > 0) {
            return `${hours} ${hours === 1 ? __('hora', 'cupompromo') : __('horas', 'cupompromo')}`;
        } else {
            return __('Menos de 1 hora', 'cupompromo');
        }
    };

    /**
     * Verifica se o cupom está expirado
     */
    const isExpired = coupon.expiry_date && new Date(coupon.expiry_date) < new Date();

    /**
     * Classes CSS dinâmicas
     */
    const cardClasses = [
        'coupon-card',
        `view-${viewMode}`,
        className,
        isExpired ? 'expired' : '',
        coupon.featured ? 'featured' : '',
        isCopied ? 'copied' : ''
    ].filter(Boolean).join(' ');

    return (
        <article className={cardClasses} data-coupon-id={coupon.id}>
            {/* Badge de destaque */}
            {coupon.featured && (
                <div className="featured-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                    </svg>
                    {__('Destaque', 'cupompromo')}
                </div>
            )}

            {/* Header do card */}
            <div className="card-header">
                <div className="store-info">
                    {coupon.store_logo && (
                        <img 
                            src={coupon.store_logo} 
                            alt={coupon.store_name}
                            className="store-logo"
                            loading="lazy"
                        />
                    )}
                    <div className="store-details">
                        <h3 className="store-name">{coupon.store_name}</h3>
                        {coupon.store_description && (
                            <p className="store-description">{coupon.store_description}</p>
                        )}
                    </div>
                </div>

                <div className="coupon-type">
                    <span className={`badge badge-${coupon.coupon_type}`}>
                        {coupon.coupon_type === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo')}
                    </span>
                </div>
            </div>

            {/* Conteúdo do card */}
            <div className="card-content">
                <h4 className="coupon-title">
                    <a href={coupon.permalink} onClick={(e) => e.preventDefault()}>
                        {coupon.title}
                    </a>
                </h4>

                {coupon.description && (
                    <p className="coupon-description">
                        {isExpanded ? coupon.description : coupon.description.substring(0, 100)}
                        {coupon.description.length > 100 && (
                            <button 
                                type="button"
                                className="expand-toggle"
                                onClick={() => setIsExpanded(!isExpanded)}
                            >
                                {isExpanded ? __('Ver menos', 'cupompromo') : __('Ver mais', 'cupompromo')}
                            </button>
                        )}
                    </p>
                )}

                {/* Valor do desconto */}
                <div className="discount-info">
                    <span className="discount-value">{coupon.discount_value}</span>
                    {coupon.discount_type && (
                        <span className="discount-type">
                            {coupon.discount_type === 'percentage' ? '%' : __('OFF', 'cupompromo')}
                        </span>
                    )}
                </div>

                {/* Código do cupom */}
                {coupon.coupon_code && (
                    <div className="coupon-code-section">
                        <div className="code-display">
                            <code className="coupon-code">{coupon.coupon_code}</code>
                            <button
                                type="button"
                                className={`copy-button ${isCopied ? 'copied' : ''} ${isLoading ? 'loading' : ''}`}
                                onClick={handleCopyCode}
                                disabled={isLoading}
                                aria-label={__('Copiar código', 'cupompromo')}
                            >
                                {isLoading ? (
                                    <div className="spinner"></div>
                                ) : isCopied ? (
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M20 6L9 17l-5-5"></path>
                                    </svg>
                                ) : (
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                )}
                            </button>
                        </div>
                        {isCopied && (
                            <span className="copy-feedback">{__('Código copiado!', 'cupompromo')}</span>
                        )}
                    </div>
                )}

                {/* Categorias */}
                {coupon.categories && coupon.categories.length > 0 && (
                    <div className="coupon-categories">
                        {coupon.categories.map(category => (
                            <a 
                                key={category.term_id} 
                                href={category.url}
                                className="category-tag"
                            >
                                {category.name}
                            </a>
                        ))}
                    </div>
                )}
            </div>

            {/* Footer do card */}
            <div className="card-footer">
                <div className="coupon-meta">
                    {/* Status de verificação */}
                    {coupon.verified_date && (
                        <span className="verification-status verified" title={__('Cupom verificado', 'cupompromo')}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M20 6L9 17l-5-5"></path>
                            </svg>
                            {__('Verificado', 'cupompromo')}
                        </span>
                    )}

                    {/* Contador de uso */}
                    {coupon.click_count > 0 && (
                        <span className="usage-count">
                            {coupon.click_count} {coupon.click_count === 1 ? __('uso', 'cupompromo') : __('usos', 'cupompromo')}
                        </span>
                    )}

                    {/* Data de publicação */}
                    {coupon.post_date && (
                        <span className="post-date">
                            {formatDate(coupon.post_date)}
                        </span>
                    )}

                    {/* Tempo restante */}
                    {coupon.expiry_date && !isExpired && (
                        <span className="time-remaining">
                            {getTimeRemaining(coupon.expiry_date)}
                        </span>
                    )}
                </div>

                {/* Ações do cupom */}
                <div className="coupon-actions">
                    <button
                        type="button"
                        className="btn btn-primary get-coupon"
                        onClick={handleOpenCoupon}
                        disabled={isExpired}
                    >
                        {coupon.coupon_type === 'code' ? __('Ver Cupom', 'cupompromo') : __('Ativar Oferta', 'cupompromo')}
                    </button>

                    {coupon.store_website && (
                        <a 
                            href={coupon.store_website}
                            className="btn btn-secondary visit-store"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {__('Visitar Loja', 'cupompromo')}
                        </a>
                    )}
                </div>
            </div>

            {/* Estado expirado */}
            {isExpired && (
                <div className="expired-overlay">
                    <span className="expired-text">{__('Cupom Expirado', 'cupompromo')}</span>
                </div>
            )}
        </article>
    );
} 