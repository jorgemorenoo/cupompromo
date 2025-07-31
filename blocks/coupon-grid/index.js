/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    RangeControl, 
    SelectControl, 
    ToggleControl,
    TextControl,
    ButtonGroup,
    Button
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './editor.scss';

/**
 * Edit component for the coupon grid block
 */
export default function Edit({ attributes, setAttributes }) {
    const {
        columns = 3,
        limit = 12,
        store_id = 0,
        category_id = 0,
        coupon_type = '',
        orderby = 'created_at',
        order = 'DESC',
        show_filters = true,
        show_pagination = true,
        featured_only = false,
        card_style = 'default'
    } = attributes;

    const [coupons, setCoupons] = useState([]);
    const [loading, setLoading] = useState(true);

    // Fetch coupons for preview
    useEffect(() => {
        const fetchCoupons = async () => {
            setLoading(true);
            try {
                const response = await fetch('/wp-json/cupompromo/v1/coupons?' + new URLSearchParams({
                    limit: limit,
                    store_id: store_id,
                    category_id: category_id,
                    coupon_type: coupon_type,
                    orderby: orderby,
                    order: order,
                    featured_only: featured_only
                }));
                
                if (response.ok) {
                    const data = await response.json();
                    setCoupons(data);
                }
            } catch (error) {
                console.error('Error fetching coupons:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchCoupons();
    }, [limit, store_id, category_id, coupon_type, orderby, order, featured_only]);

    // Get stores for dropdown
    const stores = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'cupompromo_store', {
            per_page: -1,
            status: 'publish'
        });
    }, []);

    const blockProps = useBlockProps({
        className: 'cupompromo-coupon-grid-block'
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Configurações do Grid', 'cupompromo')} initialOpen={true}>
                    <RangeControl
                        label={__('Número de Colunas', 'cupompromo')}
                        value={columns}
                        onChange={(value) => setAttributes({ columns: value })}
                        min={1}
                        max={6}
                    />
                    
                    <RangeControl
                        label={__('Limite de Cupons', 'cupompromo')}
                        value={limit}
                        onChange={(value) => setAttributes({ limit: value })}
                        min={1}
                        max={50}
                    />
                    
                    <SelectControl
                        label={__('Loja Específica', 'cupompromo')}
                        value={store_id}
                        options={[
                            { label: __('Todas as Lojas', 'cupompromo'), value: 0 },
                            ...(stores || []).map(store => ({
                                label: store?.title?.rendered || store?.name || '',
                                value: store?.id || 0
                            }))
                        ]}
                        onChange={(value) => setAttributes({ store_id: parseInt(value) })}
                    />
                    
                    <SelectControl
                        label={__('Tipo de Cupom', 'cupompromo')}
                        value={coupon_type}
                        options={[
                            { label: __('Todos os Tipos', 'cupompromo'), value: '' },
                            { label: __('Códigos', 'cupompromo'), value: 'code' },
                            { label: __('Ofertas', 'cupompromo'), value: 'offer' }
                        ]}
                        onChange={(value) => setAttributes({ coupon_type: value })}
                    />
                    
                    <SelectControl
                        label={__('Ordenar Por', 'cupompromo')}
                        value={orderby}
                        options={[
                            { label: __('Data de Criação', 'cupompromo'), value: 'created_at' },
                            { label: __('Mais Populares', 'cupompromo'), value: 'click_count' },
                            { label: __('Mais Usados', 'cupompromo'), value: 'usage_count' },
                            { label: __('Data de Atualização', 'cupompromo'), value: 'updated_at' }
                        ]}
                        onChange={(value) => setAttributes({ orderby: value })}
                    />
                    
                    <SelectControl
                        label={__('Ordem', 'cupompromo')}
                        value={order}
                        options={[
                            { label: __('Decrescente', 'cupompromo'), value: 'DESC' },
                            { label: __('Crescente', 'cupompromo'), value: 'ASC' }
                        ]}
                        onChange={(value) => setAttributes({ order: value })}
                    />
                    
                    <SelectControl
                        label={__('Estilo do Card', 'cupompromo')}
                        value={card_style}
                        options={[
                            { label: __('Padrão', 'cupompromo'), value: 'default' },
                            { label: __('Minimalista', 'cupompromo'), value: 'minimal' },
                            { label: __('Destaque', 'cupompromo'), value: 'featured' },
                            { label: __('Compacto', 'cupompromo'), value: 'compact' }
                        ]}
                        onChange={(value) => setAttributes({ card_style: value })}
                    />
                </PanelBody>
                
                <PanelBody title={__('Opções de Exibição', 'cupompromo')} initialOpen={false}>
                    <ToggleControl
                        label={__('Mostrar Filtros', 'cupompromo')}
                        checked={show_filters}
                        onChange={(value) => setAttributes({ show_filters: value })}
                        help={__('Exibe filtros de loja, categoria e tipo de cupom', 'cupompromo')}
                    />
                    
                    <ToggleControl
                        label={__('Mostrar Paginação', 'cupompromo')}
                        checked={show_pagination}
                        onChange={(value) => setAttributes({ show_pagination: value })}
                        help={__('Exibe navegação entre páginas', 'cupompromo')}
                    />
                    
                    <ToggleControl
                        label={__('Apenas Cupons em Destaque', 'cupompromo')}
                        checked={featured_only}
                        onChange={(value) => setAttributes({ featured_only: value })}
                        help={__('Exibe apenas cupons marcados como destaque', 'cupompromo')}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <div className="cupompromo-coupon-grid-editor">
                    <div className="cupompromo-coupon-grid-header">
                        <h3>{__('Grid de Cupons', 'cupompromo')}</h3>
                        <div className="cupompromo-coupon-grid-info">
                            <span>{__('Colunas:', 'cupompromo')} {columns}</span>
                            <span>{__('Limite:', 'cupompromo')} {limit}</span>
                            {store_id > 0 && <span>{__('Loja específica', 'cupompromo')}</span>}
                            {featured_only && <span>{__('Apenas destaque', 'cupompromo')}</span>}
                        </div>
                    </div>

                    {loading ? (
                        <div className="cupompromo-coupon-grid-loading">
                            <div className="cupompromo-spinner"></div>
                            <p>{__('Carregando cupons...', 'cupompromo')}</p>
                        </div>
                    ) : (
                        <div 
                            className="cupompromo-coupon-grid-preview"
                            style={{ '--columns': columns }}
                        >
                            {coupons.length > 0 ? (
                                coupons.slice(0, Math.min(limit, 6)).map((coupon, index) => (
                                    <div key={index} className="cupompromo-coupon-card-preview">
                                        <div className="coupon-card-header">
                                            <div className="store-info">
                                                {coupon.store_logo && (
                                                    <img 
                                                        src={coupon.store_logo} 
                                                        alt={coupon.store_name}
                                                        className="store-logo"
                                                    />
                                                )}
                                                <span className="store-name">{coupon.store_name}</span>
                                            </div>
                                            <div className="coupon-type">
                                                <span className={`badge badge-${coupon.coupon_type}`}>
                                                    {coupon.coupon_type === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo')}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="coupon-card-content">
                                            <h4 className="coupon-title">{coupon.title}</h4>
                                            <div className="coupon-discount">
                                                <span className="discount-value">{coupon.discount_value}</span>
                                                {coupon.discount_type === 'percentage' && (
                                                    <span className="discount-type">% OFF</span>
                                                )}
                                            </div>
                                            {coupon.coupon_code && (
                                                <div className="coupon-code">
                                                    <code>{coupon.coupon_code}</code>
                                                </div>
                                            )}
                                        </div>
                                        <div className="coupon-card-footer">
                                            <button className="btn-view-coupon">
                                                {__('Ver Cupom', 'cupompromo')}
                                            </button>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="cupompromo-coupon-grid-empty">
                                    <p>{__('Nenhum cupom encontrado com os filtros atuais.', 'cupompromo')}</p>
                                </div>
                            )}
                        </div>
                    )}

                    {show_filters && (
                        <div className="cupompromo-coupon-filters-preview">
                            <h4>{__('Filtros Disponíveis:', 'cupompromo')}</h4>
                            <div className="filter-buttons">
                                <button className="filter-btn active">{__('Todas as Lojas', 'cupompromo')}</button>
                                <button className="filter-btn">{__('Códigos', 'cupompromo')}</button>
                                <button className="filter-btn">{__('Ofertas', 'cupompromo')}</button>
                            </div>
                        </div>
                    )}

                    {show_pagination && coupons.length > limit && (
                        <div className="cupompromo-coupon-pagination-preview">
                            <button className="pagination-btn prev">{__('Anterior', 'cupompromo')}</button>
                            <span className="pagination-info">{__('Página 1 de 3', 'cupompromo')}</span>
                            <button className="pagination-btn next">{__('Próxima', 'cupompromo')}</button>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
} 