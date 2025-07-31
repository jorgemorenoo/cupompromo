/**
 * Bloco Stores Grid
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    RangeControl, 
    ToggleControl, 
    SelectControl,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';

import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
function Edit({ attributes, setAttributes }) {
    const {
        columns,
        featured_only,
        limit,
        card_style,
        show_description,
        show_coupons_count,
        show_website_link,
        orderby,
        order
    } = attributes;

    const blockProps = useBlockProps();

    // Buscar lojas para preview
    const stores = useSelect((select) => {
        const { getEntityRecords } = select(coreDataStore);
        const query = {
            post_type: 'cupompromo_store',
            per_page: limit,
            orderby: orderby,
            order: order,
            _embed: true
        };

        if (featured_only) {
            query.meta = {
                _featured_store: '1'
            };
        }

        return getEntityRecords('postType', 'cupompromo_store', query);
    }, [limit, featured_only, orderby, order]);

    const cardStyles = [
        { label: __('Padrão', 'cupompromo'), value: 'default' },
        { label: __('Minimalista', 'cupompromo'), value: 'minimal' },
        { label: __('Destaque', 'cupompromo'), value: 'featured' },
        { label: __('Compacto', 'cupompromo'), value: 'compact' },
        { label: __('Horizontal', 'cupompromo'), value: 'horizontal' }
    ];

    const orderbyOptions = [
        { label: __('Nome', 'cupompromo'), value: 'title' },
        { label: __('Data', 'cupompromo'), value: 'date' },
        { label: __('Mais Cupons', 'cupompromo'), value: 'meta_value_num' },
        { label: __('Aleatório', 'cupompromo'), value: 'rand' }
    ];

    const orderOptions = [
        { label: __('Crescente', 'cupompromo'), value: 'ASC' },
        { label: __('Decrescente', 'cupompromo'), value: 'DESC' }
    ];

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Configurações do Grid', 'cupompromo')} initialOpen={true}>
                    <RangeControl
                        label={__('Número de colunas', 'cupompromo')}
                        value={columns}
                        onChange={(value) => setAttributes({ columns: value })}
                        min={1}
                        max={6}
                        step={1}
                    />
                    
                    <NumberControl
                        label={__('Número de lojas', 'cupompromo')}
                        value={limit}
                        onChange={(value) => setAttributes({ limit: parseInt(value) || 6 })}
                        min={1}
                        max={50}
                        step={1}
                    />
                    
                    <ToggleControl
                        label={__('Apenas lojas em destaque', 'cupompromo')}
                        checked={featured_only}
                        onChange={(value) => setAttributes({ featured_only: value })}
                    />
                </PanelBody>

                <PanelBody title={__('Configurações de Exibição', 'cupompromo')} initialOpen={false}>
                    <SelectControl
                        label={__('Estilo do card', 'cupompromo')}
                        value={card_style}
                        options={cardStyles}
                        onChange={(value) => setAttributes({ card_style: value })}
                    />
                    
                    <ToggleControl
                        label={__('Mostrar descrição', 'cupompromo')}
                        checked={show_description}
                        onChange={(value) => setAttributes({ show_description: value })}
                    />
                    
                    <ToggleControl
                        label={__('Mostrar contador de cupons', 'cupompromo')}
                        checked={show_coupons_count}
                        onChange={(value) => setAttributes({ show_coupons_count: value })}
                    />
                    
                    <ToggleControl
                        label={__('Mostrar link do site', 'cupompromo')}
                        checked={show_website_link}
                        onChange={(value) => setAttributes({ show_website_link: value })}
                    />
                </PanelBody>

                <PanelBody title={__('Ordenação', 'cupompromo')} initialOpen={false}>
                    <SelectControl
                        label={__('Ordenar por', 'cupompromo')}
                        value={orderby}
                        options={orderbyOptions}
                        onChange={(value) => setAttributes({ orderby: value })}
                    />
                    
                    <SelectControl
                        label={__('Ordem', 'cupompromo')}
                        value={order}
                        options={orderOptions}
                        onChange={(value) => setAttributes({ order: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <div className="cupompromo-stores-grid-editor">
                    <div className="editor-header">
                        <h3 className="editor-title">
                            {__('Grid de Lojas', 'cupompromo')}
                        </h3>
                        <div className="editor-meta">
                            <span className="meta-item">
                                {__('Colunas:', 'cupompromo')} {columns}
                            </span>
                            <span className="meta-item">
                                {__('Limite:', 'cupompromo')} {limit}
                            </span>
                            {featured_only && (
                                <span className="meta-item featured">
                                    {__('Apenas destaque', 'cupompromo')}
                                </span>
                            )}
                        </div>
                    </div>

                    <div 
                        className="stores-grid-preview"
                        style={{ 
                            '--grid-columns': columns,
                            '--card-style': card_style
                        }}
                    >
                        {stores && stores.length > 0 ? (
                            stores.map((store) => (
                                <div key={store.id} className="store-card-preview">
                                    <div className="store-header">
                                        {store._embedded?.['wp:featuredmedia']?.[0]?.source_url && (
                                            <img 
                                                src={store._embedded['wp:featuredmedia'][0].source_url}
                                                alt={store.title.rendered}
                                                className="store-logo"
                                            />
                                        )}
                                        <h4 className="store-name">{store.title.rendered}</h4>
                                    </div>
                                    
                                    {show_description && store.excerpt?.rendered && (
                                        <div 
                                            className="store-description"
                                            dangerouslySetInnerHTML={{ __html: store.excerpt.rendered }}
                                        />
                                    )}
                                    
                                    {show_coupons_count && (
                                        <div className="store-coupons-count">
                                            {__('Cupons disponíveis', 'cupompromo')}
                                        </div>
                                    )}
                                    
                                    <div className="store-actions">
                                        <button className="btn btn-primary">
                                            {__('Ver Cupons', 'cupompromo')}
                                        </button>
                                        {show_website_link && (
                                            <button className="btn btn-secondary">
                                                {__('Visitar Loja', 'cupompromo')}
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="no-stores-message">
                                <p>{__('Nenhuma loja encontrada com os critérios selecionados.', 'cupompromo')}</p>
                                <p>{__('Tente ajustar os filtros ou adicionar mais lojas.', 'cupompromo')}</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
function save() {
    return null; // Server-side rendering
}

// Registra o bloco
registerBlockType('cupompromo/stores-grid', {
    apiVersion: 3,
    title: __('Grid de Lojas', 'cupompromo'),
    description: __('Exibe um grid de lojas parceiras com cupons de desconto.', 'cupompromo'),
    category: 'cupompromo',
    icon: 'grid-view',
    supports: {
        html: false,
        align: ['wide', 'full'],
        spacing: {
            margin: true,
            padding: true
        },
        color: {
            background: true,
            text: true
        }
    },
    attributes: {
        columns: {
            type: 'number',
            default: 3
        },
        featured_only: {
            type: 'boolean',
            default: false
        },
        limit: {
            type: 'number',
            default: 6
        },
        card_style: {
            type: 'string',
            default: 'default'
        },
        show_description: {
            type: 'boolean',
            default: true
        },
        show_coupons_count: {
            type: 'boolean',
            default: true
        },
        show_website_link: {
            type: 'boolean',
            default: true
        },
        orderby: {
            type: 'string',
            default: 'title'
        },
        order: {
            type: 'string',
            default: 'ASC'
        }
    },
    edit: Edit,
    save: save
}); 