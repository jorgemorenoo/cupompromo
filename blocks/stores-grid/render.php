<?php
/**
 * Server-side rendering of the `cupompromo/stores-grid` block.
 *
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the `cupompromo/stores-grid` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string Returns the markup for the block.
 */
function cupompromo_render_stores_grid_block($attributes, $content, $block) {
    // Extrai atributos
    $columns = $attributes['columns'] ?? 3;
    $featured_only = $attributes['featured_only'] ?? false;
    $limit = $attributes['limit'] ?? 6;
    $card_style = $attributes['card_style'] ?? 'default';
    $show_description = $attributes['show_description'] ?? true;
    $show_coupons_count = $attributes['show_coupons_count'] ?? true;
    $show_website_link = $attributes['show_website_link'] ?? true;
    $orderby = $attributes['orderby'] ?? 'title';
    $order = $attributes['order'] ?? 'ASC';
    $align = $attributes['align'] ?? '';
    $backgroundColor = $attributes['backgroundColor'] ?? '';
    $textColor = $attributes['textColor'] ?? '';
    $gradient = $attributes['gradient'] ?? '';
    $style = $attributes['style'] ?? [];

    // Classes CSS
    $wrapper_classes = [
        'wp-block-cupompromo-stores-grid',
        'cupompromo-stores-grid',
        'stores-grid-' . $card_style
    ];

    if ($align) {
        $wrapper_classes[] = 'align' . $align;
    }

    if ($backgroundColor) {
        $wrapper_classes[] = 'has-background';
        $wrapper_classes[] = 'has-' . $backgroundColor . '-background-color';
    }

    if ($textColor) {
        $wrapper_classes[] = 'has-text-color';
        $wrapper_classes[] = 'has-' . $textColor . '-color';
    }

    if ($gradient) {
        $wrapper_classes[] = 'has-background';
        $wrapper_classes[] = 'has-' . $gradient . '-gradient-background';
    }

    $wrapper_class = implode(' ', array_filter($wrapper_classes));

    // Estilos inline
    $wrapper_styles = [];
    if (!empty($style['spacing']['margin'])) {
        $wrapper_styles[] = 'margin: ' . $style['spacing']['margin'];
    }
    if (!empty($style['spacing']['padding'])) {
        $wrapper_styles[] = 'padding: ' . $style['spacing']['padding'];
    }

    $wrapper_style = !empty($wrapper_styles) ? ' style="' . esc_attr(implode('; ', $wrapper_styles)) . '"' : '';

    // Query para buscar lojas
    $query_args = [
        'post_type' => 'cupompromo_store',
        'posts_per_page' => $limit,
        'orderby' => $orderby,
        'order' => $order,
        'post_status' => 'publish'
    ];

    // Meta query para lojas em destaque
    if ($featured_only) {
        $query_args['meta_query'] = [
            [
                'key' => '_featured_store',
                'value' => '1',
                'compare' => '='
            ]
        ];
    }

    // Ordenação por meta value (número de cupons)
    if ($orderby === 'meta_value_num') {
        $query_args['meta_key'] = '_coupons_count';
        $query_args['orderby'] = 'meta_value_num';
    }

    $stores_query = new WP_Query($query_args);

    ob_start();
    ?>
    <div class="<?php echo esc_attr($wrapper_class); ?>"<?php echo $wrapper_style; ?>>
        <?php if ($stores_query->have_posts()): ?>
            <div class="stores-grid" style="--grid-columns: <?php echo esc_attr($columns); ?>;">
                <?php while ($stores_query->have_posts()): $stores_query->the_post(); ?>
                    <?php
                    $store_id = get_the_ID();
                    $store_logo = get_post_meta($store_id, '_store_logo', true);
                    $store_description = get_post_meta($store_id, '_store_description', true);
                    $store_website = get_post_meta($store_id, '_store_website', true);
                    $coupons_count = get_post_meta($store_id, '_coupons_count', true) ?: 0;
                    $is_featured = get_post_meta($store_id, '_featured_store', true);
                    
                    // Classes do card
                    $card_classes = [
                        'store-card',
                        'card-style-' . $card_style
                    ];
                    
                    if ($is_featured) {
                        $card_classes[] = 'featured-store';
                    }
                    
                    $card_class = implode(' ', array_filter($card_classes));
                    ?>
                    
                    <article class="<?php echo esc_attr($card_class); ?>" data-store-id="<?php echo esc_attr($store_id); ?>">
                        <div class="store-header">
                            <?php if ($store_logo): ?>
                                <img 
                                    src="<?php echo esc_url($store_logo); ?>" 
                                    alt="<?php echo esc_attr(get_the_title()); ?>"
                                    class="store-logo"
                                    loading="lazy"
                                    width="60"
                                    height="60"
                                >
                            <?php endif; ?>
                            
                            <div class="store-info">
                                <h3 class="store-name">
                                    <a href="<?php the_permalink(); ?>" rel="bookmark">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                
                                <?php if ($show_coupons_count): ?>
                                    <span class="store-coupons-count">
                                        <?php 
                                        printf(
                                            _n(
                                                '%d cupom ativo',
                                                '%d cupons ativos',
                                                $coupons_count,
                                                'cupompromo'
                                            ),
                                            $coupons_count
                                        );
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($show_description && $store_description): ?>
                            <div class="store-description">
                                <?php echo wp_trim_words($store_description, 15, '...'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="store-actions">
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                <?php _e('Ver Cupons', 'cupompromo'); ?>
                            </a>
                            
                            <?php if ($show_website_link && $store_website): ?>
                                <a 
                                    href="<?php echo esc_url($store_website); ?>" 
                                    class="btn btn-secondary"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?php _e('Visitar Loja', 'cupompromo'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($is_featured): ?>
                            <div class="featured-badge">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                                </svg>
                                <?php _e('Destaque', 'cupompromo'); ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-stores-found">
                <div class="no-stores-content">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    <h3><?php _e('Nenhuma loja encontrada', 'cupompromo'); ?></h3>
                    <p><?php _e('Não encontramos lojas com os critérios selecionados.', 'cupompromo'); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    </div>
    <?php
    
    return ob_get_clean();
}

// Registra a função de renderização
register_block_type('cupompromo/stores-grid', [
    'render_callback' => 'cupompromo_render_stores_grid_block'
]); 