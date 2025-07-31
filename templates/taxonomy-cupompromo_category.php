<?php
/**
 * Template para exibir cupons por categoria
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
        
        <!-- Header da Categoria -->
        <header class="cupompromo-category-header">
            <div class="category-info">
                <h1 class="category-title">
                    <?php single_term_title(); ?>
                </h1>
                
                <?php if (term_description()): ?>
                    <div class="category-description">
                        <?php echo term_description(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="category-stats">
                    <span class="coupons-count">
                        <?php 
                        $coupons_count = $wp_query->found_posts;
                        printf(
                            _n(
                                '%d cupom encontrado',
                                '%d cupons encontrados',
                                $coupons_count,
                                'cupompromo'
                            ),
                            $coupons_count
                        );
                        ?>
                    </span>
                    
                    <?php if (function_exists('cupompromo_get_category_stats')): ?>
                        <span class="stores-count">
                            <?php 
                            $stores_count = cupompromo_get_category_stats(get_queried_object_id(), 'stores');
                            printf(
                                _n(
                                    'em %d loja',
                                    'em %d lojas',
                                    $stores_count,
                                    'cupompromo'
                                ),
                                $stores_count
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
                    <li aria-current="page"><?php single_term_title(); ?></li>
                </ol>
            </nav>
        </header>

        <!-- Filtros e Busca -->
        <div class="cupompromo-filters-section">
            <div class="filters-container">
                <!-- Barra de Busca -->
                <div class="search-container">
                    <form role="search" method="get" class="cupompromo-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="hidden" name="post_type" value="cupompromo_coupon">
                        <input type="hidden" name="cupompromo_category" value="<?php echo esc_attr(get_queried_object()->slug); ?>">
                        
                        <div class="search-input-wrapper">
                            <input 
                                type="search" 
                                name="s" 
                                placeholder="<?php esc_attr_e('Buscar cupons nesta categoria...', 'cupompromo'); ?>"
                                value="<?php echo esc_attr(get_search_query()); ?>"
                                class="search-input"
                                aria-label="<?php esc_attr_e('Buscar cupons', 'cupompromo'); ?>"
                            >
                            <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Buscar', 'cupompromo'); ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Filtros Avançados -->
                <div class="advanced-filters">
                    <button class="filter-toggle" aria-expanded="false" aria-controls="filter-panel">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                        </svg>
                        <?php _e('Filtros', 'cupompromo'); ?>
                    </button>
                    
                    <div id="filter-panel" class="filter-panel" hidden>
                        <div class="filter-group">
                            <label for="store-filter"><?php _e('Loja:', 'cupompromo'); ?></label>
                            <select id="store-filter" name="store" class="filter-select">
                                <option value=""><?php _e('Todas as lojas', 'cupompromo'); ?></option>
                                <?php
                                $stores = get_posts([
                                    'post_type' => 'cupompromo_store',
                                    'posts_per_page' => -1,
                                    'meta_query' => [
                                        [
                                            'key' => '_cupompromo_category',
                                            'value' => get_queried_object_id(),
                                            'compare' => 'LIKE'
                                        ]
                                    ]
                                ]);
                                
                                foreach ($stores as $store) {
                                    echo sprintf(
                                        '<option value="%s">%s</option>',
                                        esc_attr($store->ID),
                                        esc_html($store->post_title)
                                    );
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="type-filter"><?php _e('Tipo:', 'cupompromo'); ?></label>
                            <select id="type-filter" name="type" class="filter-select">
                                <option value=""><?php _e('Todos os tipos', 'cupompromo'); ?></option>
                                <option value="code"><?php _e('Códigos', 'cupompromo'); ?></option>
                                <option value="offer"><?php _e('Ofertas', 'cupompromo'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="sort-filter"><?php _e('Ordenar por:', 'cupompromo'); ?></label>
                            <select id="sort-filter" name="sort" class="filter-select">
                                <option value="date"><?php _e('Mais recentes', 'cupompromo'); ?></option>
                                <option value="popular"><?php _e('Mais populares', 'cupompromo'); ?></option>
                                <option value="discount"><?php _e('Maior desconto', 'cupompromo'); ?></option>
                                <option value="verified"><?php _e('Verificados', 'cupompromo'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="button" class="btn btn-primary apply-filters">
                                <?php _e('Aplicar Filtros', 'cupompromo'); ?>
                            </button>
                            <button type="button" class="btn btn-secondary clear-filters">
                                <?php _e('Limpar', 'cupompromo'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de Cupons -->
        <main class="cupompromo-main-content">
            <?php if (have_posts()): ?>
                <div class="cupons-grid" id="cupons-grid">
                    <?php while (have_posts()): the_post(); ?>
                        <?php
                        // Obtém dados do cupom
                        $coupon_data = cupompromo_get_coupon_data(get_the_ID());
                        ?>
                        
                        <article class="cupom-coupon-card" data-coupon-id="<?php echo esc_attr(get_the_ID()); ?>">
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
                                    <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
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
                <div class="no-coupons-found">
                    <div class="no-results-content">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <h2><?php _e('Nenhum cupom encontrado', 'cupompromo'); ?></h2>
                        <p><?php _e('Não encontramos cupons nesta categoria. Tente ajustar os filtros ou buscar por outro termo.', 'cupompromo'); ?></p>
                        <a href="<?php echo esc_url(home_url('/cupons/')); ?>" class="btn btn-primary">
                            <?php _e('Ver Todos os Cupons', 'cupompromo'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
        
        <!-- Sidebar com Lojas da Categoria -->
        <aside class="cupompromo-sidebar">
            <div class="sidebar-widget stores-widget">
                <h3 class="widget-title"><?php _e('Lojas desta Categoria', 'cupompromo'); ?></h3>
                <div class="stores-list">
                    <?php
                    $category_stores = get_posts([
                        'post_type' => 'cupompromo_store',
                        'posts_per_page' => 10,
                        'meta_query' => [
                            [
                                'key' => '_cupompromo_category',
                                'value' => get_queried_object_id(),
                                'compare' => 'LIKE'
                            ]
                        ]
                    ]);
                    
                    foreach ($category_stores as $store) {
                        $store_logo = get_post_meta($store->ID, '_store_logo', true);
                        $store_url = get_permalink($store->ID);
                        ?>
                        <a href="<?php echo esc_url($store_url); ?>" class="store-item">
                            <?php if (!empty($store_logo)): ?>
                                <img src="<?php echo esc_url($store_logo); ?>" alt="<?php echo esc_attr($store->post_title); ?>" class="store-logo-small" loading="lazy">
                            <?php endif; ?>
                            <span class="store-name"><?php echo esc_html($store->post_title); ?></span>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <a href="<?php echo esc_url(home_url('/lojas/')); ?>" class="view-all-stores">
                    <?php _e('Ver Todas as Lojas', 'cupompromo'); ?>
                </a>
            </div>
        </aside>
    </div>
</div>

<?php get_footer('cupompromo'); ?> 