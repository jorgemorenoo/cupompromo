<?php
/**
 * Template para exibir resultados de busca de cupons
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

get_header('cupompromo'); ?>

<div class="cupompromo-container">
    <div class="cupompromo-content">
        
        <!-- Header da Busca -->
        <header class="cupompromo-search-header">
            <div class="search-info">
                <h1 class="search-title">
                    <?php 
                    if (get_search_query()) {
                        printf(
                            __('Resultados para: "%s"', 'cupompromo'),
                            '<span class="search-term">' . esc_html(get_search_query()) . '</span>'
                        );
                    } else {
                        _e('Buscar Cupons', 'cupompromo');
                    }
                    ?>
                </h1>
                
                <div class="search-stats">
                    <?php if (have_posts()): ?>
                        <span class="results-count">
                            <?php 
                            global $wp_query;
                            printf(
                                _n(
                                    '%d resultado encontrado',
                                    '%d resultados encontrados',
                                    $wp_query->found_posts,
                                    'cupompromo'
                                ),
                                $wp_query->found_posts
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Breadcrumb -->
            <nav class="cupompromo-breadcrumb" aria-label="<?php esc_attr_e('Navegação', 'cupompromo'); ?>">
                <ol>
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Início', 'cupompromo'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/cupons/')); ?>"><?php _e('Cupons', 'cupompromo'); ?></a></li>
                    <li aria-current="page"><?php _e('Busca', 'cupompromo'); ?></li>
                </ol>
            </nav>
        </header>

        <!-- Formulário de Busca Avançada -->
        <div class="cupompromo-search-form-section">
            <form role="search" method="get" class="cupompromo-advanced-search" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="hidden" name="post_type" value="cupompromo_coupon">
                
                <div class="search-form-grid">
                    <div class="search-input-group">
                        <label for="search-query" class="sr-only"><?php _e('Buscar cupons', 'cupompromo'); ?></label>
                        <input 
                            type="search" 
                            id="search-query"
                            name="s" 
                            placeholder="<?php esc_attr_e('Digite o que você está procurando...', 'cupompromo'); ?>"
                            value="<?php echo esc_attr(get_search_query()); ?>"
                            class="search-input-large"
                            required
                        >
                    </div>
                    
                    <div class="search-filters-row">
                        <div class="filter-group">
                            <label for="search-store"><?php _e('Loja:', 'cupompromo'); ?></label>
                            <select id="search-store" name="store" class="filter-select">
                                <option value=""><?php _e('Todas as lojas', 'cupompromo'); ?></option>
                                <?php
                                $stores = get_posts([
                                    'post_type' => 'cupompromo_store',
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ]);
                                
                                foreach ($stores as $store) {
                                    $selected = (isset($_GET['store']) && $_GET['store'] == $store->ID) ? 'selected' : '';
                                    echo sprintf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($store->ID),
                                        $selected,
                                        esc_html($store->post_title)
                                    );
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="search-category"><?php _e('Categoria:', 'cupompromo'); ?></label>
                            <select id="search-category" name="category" class="filter-select">
                                <option value=""><?php _e('Todas as categorias', 'cupompromo'); ?></option>
                                <?php
                                $categories = get_terms([
                                    'taxonomy' => 'cupompromo_category',
                                    'hide_empty' => true,
                                    'orderby' => 'name',
                                    'order' => 'ASC'
                                ]);
                                
                                foreach ($categories as $category) {
                                    $selected = (isset($_GET['category']) && $_GET['category'] == $category->slug) ? 'selected' : '';
                                    echo sprintf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($category->slug),
                                        $selected,
                                        esc_html($category->name)
                                    );
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="search-type"><?php _e('Tipo:', 'cupompromo'); ?></label>
                            <select id="search-type" name="type" class="filter-select">
                                <option value=""><?php _e('Todos os tipos', 'cupompromo'); ?></option>
                                <option value="code" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'code'); ?>>
                                    <?php _e('Códigos', 'cupompromo'); ?>
                                </option>
                                <option value="offer" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'offer'); ?>>
                                    <?php _e('Ofertas', 'cupompromo'); ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="search-sort"><?php _e('Ordenar por:', 'cupompromo'); ?></label>
                            <select id="search-sort" name="sort" class="filter-select">
                                <option value="relevance" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'relevance'); ?>>
                                    <?php _e('Relevância', 'cupompromo'); ?>
                                </option>
                                <option value="date" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'date'); ?>>
                                    <?php _e('Mais recentes', 'cupompromo'); ?>
                                </option>
                                <option value="popular" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'popular'); ?>>
                                    <?php _e('Mais populares', 'cupompromo'); ?>
                                </option>
                                <option value="discount" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'discount'); ?>>
                                    <?php _e('Maior desconto', 'cupompromo'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="search-actions">
                        <button type="submit" class="btn btn-primary search-submit">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <?php _e('Buscar', 'cupompromo'); ?>
                        </button>
                        
                        <?php if (get_search_query() || isset($_GET['store']) || isset($_GET['category']) || isset($_GET['type'])): ?>
                            <a href="<?php echo esc_url(home_url('/cupons/')); ?>" class="btn btn-secondary clear-search">
                                <?php _e('Limpar Busca', 'cupompromo'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Resultados da Busca -->
        <main class="cupompromo-main-content">
            <?php if (have_posts()): ?>
                <div class="search-results-header">
                    <div class="results-info">
                        <p class="results-summary">
                            <?php 
                            printf(
                                __('Mostrando %1$d-%2$d de %3$d resultados', 'cupompromo'),
                                ($wp_query->query_vars['paged'] > 0 ? ($wp_query->query_vars['paged'] - 1) * $wp_query->query_vars['posts_per_page'] + 1 : 1),
                                min($wp_query->query_vars['paged'] * $wp_query->query_vars['posts_per_page'], $wp_query->found_posts),
                                $wp_query->found_posts
                            );
                            ?>
                        </p>
                    </div>
                    
                    <div class="view-options">
                        <button class="view-toggle grid-view active" data-view="grid" aria-label="<?php esc_attr_e('Visualização em grade', 'cupompromo'); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </button>
                        <button class="view-toggle list-view" data-view="list" aria-label="<?php esc_attr_e('Visualização em lista', 'cupompromo'); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="cupons-grid search-results" id="search-results">
                    <?php while (have_posts()): the_post(); ?>
                        <?php
                        // Obtém dados do cupom
                        $coupon_data = cupompromo_get_coupon_data(get_the_ID());
                        ?>
                        
                        <article class="cupom-coupon-card search-result" data-coupon-id="<?php echo esc_attr(get_the_ID()); ?>">
                            <div class="coupon-header">
                                <div class="store-info">
                                    <?php if (!empty($coupon_data['store_logo'])): ?>
                                        <img 
                                            src="<?php echo esc_url($coupon_data['store_logo']); ?>" 
                                            alt="<?php echo esc_attr($coupon_data['store_name']); ?>"
                                            class="store-logo"
                                            loading="lazy"
                                        >
                                    <?php endif; ?>
                                    <span class="store-name"><?php echo esc_html($coupon_data['store_name']); ?></span>
                                </div>
                                
                                <div class="coupon-type">
                                    <span class="badge badge-<?php echo esc_attr($coupon_data['coupon_type']); ?>">
                                        <?php echo $coupon_data['coupon_type'] === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo'); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="coupon-content">
                                <h3 class="coupon-title">
                                    <a href="<?php the_permalink(); ?>" rel="bookmark">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                
                                <div class="coupon-description">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </div>
                                
                                <div class="coupon-discount">
                                    <span class="discount-value">
                                        <?php echo esc_html($coupon_data['discount_value']); ?>
                                    </span>
                                    <?php if (!empty($coupon_data['discount_type'])): ?>
                                        <span class="discount-type">
                                            <?php echo $coupon_data['discount_type'] === 'percentage' ? '%' : __('OFF', 'cupompromo'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($coupon_data['coupon_code'])): ?>
                                    <div class="coupon-code">
                                        <code><?php echo esc_html($coupon_data['coupon_code']); ?></code>
                                        <button class="copy-code" data-code="<?php echo esc_attr($coupon_data['coupon_code']); ?>" aria-label="<?php esc_attr_e('Copiar código', 'cupompromo'); ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                            </svg>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Categorias do cupom -->
                                <?php
                                $categories = get_the_terms(get_the_ID(), 'cupompromo_category');
                                if ($categories && !is_wp_error($categories)): ?>
                                    <div class="coupon-categories">
                                        <?php foreach ($categories as $category): ?>
                                            <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-tag">
                                                <?php echo esc_html($category->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="coupon-footer">
                                <div class="coupon-meta">
                                    <?php if (!empty($coupon_data['verified_date'])): ?>
                                        <span class="verification-status verified">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 6L9 17l-5-5"></path>
                                            </svg>
                                            <?php 
                                            printf(
                                                __('Verificado %s', 'cupompromo'),
                                                human_time_diff(strtotime($coupon_data['verified_date']), current_time('timestamp'))
                                            );
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($coupon_data['click_count'])): ?>
                                        <span class="usage-count">
                                            <?php 
                                            printf(
                                                _n(
                                                    'Usado %d vez',
                                                    'Usado %d vezes',
                                                    $coupon_data['click_count'],
                                                    'cupompromo'
                                                ),
                                                $coupon_data['click_count']
                                            );
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="post-date">
                                        <?php 
                                        printf(
                                            __('Publicado %s', 'cupompromo'),
                                            human_time_diff(get_the_time('U'), current_time('timestamp'))
                                        );
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="coupon-actions">
                                    <a href="<?php echo esc_url($coupon_data['affiliate_url']); ?>" 
                                       class="btn btn-primary get-coupon"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       data-coupon-id="<?php echo esc_attr(get_the_ID()); ?>"
                                       data-store-id="<?php echo esc_attr($coupon_data['store_id']); ?>">
                                        <?php echo $coupon_data['coupon_type'] === 'code' ? __('Ver Cupom', 'cupompromo') : __('Ativar Oferta', 'cupompromo'); ?>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <!-- Paginação -->
                <?php if ($wp_query->max_num_pages > 1): ?>
                    <nav class="cupompromo-pagination" aria-label="<?php esc_attr_e('Navegação de páginas', 'cupompromo'); ?>">
                        <?php
                        echo paginate_links([
                            'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,18 9,12 15,6"></polyline></svg> ' . __('Anterior', 'cupompromo'),
                            'next_text' => __('Próxima', 'cupompromo') . ' <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,18 15,12 9,6"></polyline></svg>',
                            'class' => 'pagination-links',
                            'current_class' => 'current-page',
                            'prev_class' => 'prev-page',
                            'next_class' => 'next-page'
                        ]);
                        ?>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-results-found">
                    <div class="no-results-content">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <h2><?php _e('Nenhum resultado encontrado', 'cupompromo'); ?></h2>
                        <p><?php _e('Não encontramos cupons que correspondam à sua busca. Tente ajustar os termos de pesquisa ou filtros.', 'cupompromo'); ?></p>
                        
                        <div class="suggestions">
                            <h3><?php _e('Sugestões:', 'cupompromo'); ?></h3>
                            <ul>
                                <li><?php _e('Verifique se digitou corretamente', 'cupompromo'); ?></li>
                                <li><?php _e('Tente termos mais gerais', 'cupompromo'); ?></li>
                                <li><?php _e('Use menos filtros', 'cupompromo'); ?></li>
                                <li><?php _e('Explore as categorias populares', 'cupompromo'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/cupons/')); ?>" class="btn btn-primary">
                                <?php _e('Ver Todos os Cupons', 'cupompromo'); ?>
                            </a>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('search-query').focus();">
                                <?php _e('Nova Busca', 'cupompromo'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
        
        <!-- Sidebar com Sugestões -->
        <aside class="cupompromo-sidebar">
            <?php if (get_search_query()): ?>
                <div class="sidebar-widget suggestions-widget">
                    <h3 class="widget-title"><?php _e('Busquedas Relacionadas', 'cupompromo'); ?></h3>
                    <div class="related-searches">
                        <?php
                        // Busca termos relacionados
                        $related_terms = cupompromo_get_related_search_terms(get_search_query());
                        if ($related_terms): ?>
                            <ul class="related-terms">
                                <?php foreach ($related_terms as $term): ?>
                                    <li>
                                        <a href="<?php echo esc_url(home_url('/?s=' . urlencode($term) . '&post_type=cupompromo_coupon')); ?>">
                                            <?php echo esc_html($term); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p><?php _e('Nenhuma sugestão disponível.', 'cupompromo'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="sidebar-widget popular-categories-widget">
                <h3 class="widget-title"><?php _e('Categorias Populares', 'cupompromo'); ?></h3>
                <div class="popular-categories">
                    <?php
                    $popular_categories = get_terms([
                        'taxonomy' => 'cupompromo_category',
                        'number' => 10,
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'hide_empty' => true
                    ]);
                    
                    foreach ($popular_categories as $category): ?>
                        <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-item">
                            <span class="category-name"><?php echo esc_html($category->name); ?></span>
                            <span class="category-count">(<?php echo $category->count; ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php get_footer('cupompromo'); ?> 