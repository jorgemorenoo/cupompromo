/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    ToggleControl, 
    TextareaControl,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
    const {
        placeholder,
        show_filters,
        live_search,
        show_categories,
        show_stores,
        show_types,
        button_text,
        search_page_url
    } = attributes;

    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Configurações da Busca', 'cupompromo')} initialOpen={true}>
                    <TextControl
                        label={__('Placeholder', 'cupompromo')}
                        value={placeholder}
                        onChange={(value) => setAttributes({ placeholder: value })}
                        help={__('Texto que aparece no campo de busca quando vazio', 'cupompromo')}
                    />
                    
                    <TextControl
                        label={__('Texto do botão', 'cupompromo')}
                        value={button_text}
                        onChange={(value) => setAttributes({ button_text: value })}
                    />
                    
                    <TextControl
                        label={__('URL da página de busca', 'cupompromo')}
                        value={search_page_url}
                        onChange={(value) => setAttributes({ search_page_url: value })}
                        help={__('Deixe vazio para usar a página padrão', 'cupompromo')}
                    />
                </PanelBody>

                <PanelBody title={__('Funcionalidades', 'cupompromo')} initialOpen={false}>
                    <ToggleControl
                        label={__('Busca em tempo real', 'cupompromo')}
                        checked={live_search}
                        onChange={(value) => setAttributes({ live_search: value })}
                        help={__('Mostra resultados enquanto o usuário digita', 'cupompromo')}
                    />
                    
                    <ToggleControl
                        label={__('Mostrar filtros avançados', 'cupompromo')}
                        checked={show_filters}
                        onChange={(value) => setAttributes({ show_filters: value })}
                        help={__('Exibe opções de filtro na barra de busca', 'cupompromo')}
                    />
                </PanelBody>

                <PanelBody title={__('Filtros Disponíveis', 'cupompromo')} initialOpen={false}>
                    <ToggleControl
                        label={__('Filtro por categorias', 'cupompromo')}
                        checked={show_categories}
                        onChange={(value) => setAttributes({ show_categories: value })}
                    />
                    
                    <ToggleControl
                        label={__('Filtro por lojas', 'cupompromo')}
                        checked={show_stores}
                        onChange={(value) => setAttributes({ show_stores: value })}
                    />
                    
                    <ToggleControl
                        label={__('Filtro por tipo de cupom', 'cupompromo')}
                        checked={show_types}
                        onChange={(value) => setAttributes({ show_types: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <div className="cupompromo-search-bar-editor">
                    <div className="editor-header">
                        <h3 className="editor-title">
                            {__('Barra de Busca', 'cupompromo')}
                        </h3>
                        <div className="editor-meta">
                            {live_search && (
                                <span className="meta-item live-search">
                                    {__('Busca em tempo real', 'cupompromo')}
                                </span>
                            )}
                            {show_filters && (
                                <span className="meta-item filters">
                                    {__('Com filtros', 'cupompromo')}
                                </span>
                            )}
                        </div>
                    </div>

                    <div className="search-bar-preview">
                        <div className="search-input-group">
                            <input 
                                type="search" 
                                placeholder={placeholder}
                                className="search-input"
                                disabled
                            />
                            <button type="button" className="search-submit" disabled>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <span className="button-text">{button_text}</span>
                            </button>
                        </div>
                        
                        {show_filters && (
                            <div className="filters-preview">
                                <div className="filter-chips">
                                    {show_categories && (
                                        <span className="filter-chip">
                                            {__('Categoria', 'cupompromo')}
                                        </span>
                                    )}
                                    {show_stores && (
                                        <span className="filter-chip">
                                            {__('Loja', 'cupompromo')}
                                        </span>
                                    )}
                                    {show_types && (
                                        <span className="filter-chip">
                                            {__('Tipo', 'cupompromo')}
                                        </span>
                                    )}
                                </div>
                                
                                <button type="button" className="filter-toggle" disabled>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                                    </svg>
                                    {__('Filtros Avançados', 'cupompromo')}
                                </button>
                            </div>
                        )}
                        
                        {live_search && (
                            <div className="live-search-preview">
                                <div className="live-search-results">
                                    <div className="result-item">
                                        <div className="result-icon">🎫</div>
                                        <div className="result-content">
                                            <div className="result-title">{__('Cupom de Desconto', 'cupompromo')}</div>
                                            <div className="result-meta">{__('Loja Exemplo', 'cupompromo')}</div>
                                        </div>
                                    </div>
                                    <div className="result-item">
                                        <div className="result-icon">🏪</div>
                                        <div className="result-content">
                                            <div className="result-title">{__('Loja Online', 'cupompromo')}</div>
                                            <div className="result-meta">{__('15 cupons ativos', 'cupompromo')}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
} 