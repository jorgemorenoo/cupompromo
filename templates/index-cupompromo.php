<?php
/**
 * Template da homepage do portal de cupons
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

<div class="cupompromo-homepage">
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <?php _e('Encontre os Melhores Cupons de Desconto', 'cupompromo'); ?>
                </h1>
                <p class="hero-subtitle">
                    <?php _e('Economize em suas compras com cupons verificados das principais lojas online', 'cupompromo'); ?>
                </p>
                
                <!-- Formulário de Busca Principal -->
                <div class="hero-search">
                    <?php
                    $search_config = [
                        'show_advanced_filters' => false,
                        'compact_mode' => true,
                        'placeholder' => __('Buscar cupons, lojas ou categorias...', 'cupompromo'),
                        'live_search' => true
                    ];
                    
                    include get_template_directory() . '/templates/parts/search-form.php';
                    ?>
                </div>
                
                <!-- Estatísticas Rápidas -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php 
                            $total_coupons = wp_count_posts('cupompromo_coupon')->publish;
                            echo number_format($total_coupons, 0, ',', '.');
                            ?>
                        </span>
                        <span class="stat-label"><?php _e('Cupons Ativos', 'cupompromo'); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php 
                            $total_stores = wp_count_posts('cupompromo_store')->publish;
                            echo number_format($total_stores, 0, ',', '.');
                            ?>
                        </span>
                        <span class="stat-label"><?php _e('Lojas Parceiras', 'cupompromo'); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php 
                            $total_categories = wp_count_terms('cupompromo_category');
                            echo number_format($total_categories, 0, ',', '.');
                            ?>
                        </span>
                        <span class="stat-label"><?php _e('Categorias', 'cupompromo'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="hero-image">
                    <img src="<?php echo esc_url(CUPOMPROMO_PLUGIN_URL . 'assets/images/hero-coupons.svg'); ?>" 
                         alt="<?php esc_attr_e('Cupons de desconto', 'cupompromo'); ?>"
                         loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias Populares -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php _e('Categorias Populares', 'cupompromo'); ?></h2>
                <p class="section-subtitle"><?php _e('Explore cupons por categoria', 'cupompromo'); ?></p>
            </div>
            
            <div class="categories-grid">
                <?php
                $popular_categories = get_terms([
                    'taxonomy' => 'cupompromo_category',
                    'number' => 8,
                    'orderby' => 'count',
                    'order' => 'DESC',
                    'hide_empty' => true
                ]);
                
                foreach ($popular_categories as $category):
                    $category_icon = get_term_meta($category->term_id, '_category_icon', true);
                    $category_color = get_term_meta($category->term_id, '_category_color', true) ?: '#622599';
                    ?>
                    <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-card">
                        <div class="category-icon" style="background-color: <?php echo esc_attr($category_color); ?>">
                            <?php if ($category_icon): ?>
                                <img src="<?php echo esc_url($category_icon); ?>" alt="<?php echo esc_attr($category->name); ?>">
                            <?php else: ?>
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <h3 class="category-name"><?php echo esc_html($category->name); ?></h3>
                            <span class="category-count">
                                <?php 
                                printf(
                                    _n(
                                        '%d cupom',
                                        '%d cupons',
                                        $category->count,
                                        'cupompromo'
                                    ),
                                    $category->count
                                );
                                ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="<?php echo esc_url(home_url('/categorias/')); ?>" class="btn btn-outline">
                    <?php _e('Ver Todas as Categorias', 'cupompromo'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Lojas em Destaque -->
    <section class="featured-stores-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php _e('Lojas em Destaque', 'cupompromo'); ?></h2>
                <p class="section-subtitle"><?php _e('As lojas mais populares com os melhores descontos', 'cupompromo'); ?></p>
            </div>
            
            <div class="stores-grid">
                <?php
                $featured_stores = get_posts([
                    'post_type' => 'cupompromo_store',
                    'posts_per_page' => 6,
                    'meta_query' => [
                        [
                            'key' => '_featured_store',
                            'value' => '1',
                            'compare' => '='
                        ],
                        [
                            'key' => '_store_status',
                            'value' => 'active',
                            'compare' => '='
                        ]
                    ],
                    'orderby' => 'meta_value_num',
                    'meta_key' => '_coupons_count',
                    'order' => 'DESC'
                ]);
                
                foreach ($featured_stores as $store):
                    $store_logo = get_post_meta($store->ID, '_store_logo', true);
                    $store_description = get_post_meta($store->ID, '_store_description', true);
                    $coupons_count = get_post_meta($store->ID, '_coupons_count', true) ?: 0;
                    $store_website = get_post_meta($store->ID, '_store_website', true);
                    ?>
                    <div class="store-card featured">
                        <div class="store-header">
                            <?php if ($store_logo): ?>
                                <img src="<?php echo esc_url($store_logo); ?>" 
                                     alt="<?php echo esc_attr($store->post_title); ?>"
                                     class="store-logo"
                                     loading="lazy">
                            <?php endif; ?>
                            <div class="store-info">
                                <h3 class="store-name"><?php echo esc_html($store->post_title); ?></h3>
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
                            </div>
                        </div>
                        
                        <?php if ($store_description): ?>
                            <p class="store-description">
                                <?php echo wp_trim_words($store_description, 15, '...'); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="store-actions">
                            <a href="<?php echo esc_url(get_permalink($store->ID)); ?>" class="btn btn-primary">
                                <?php _e('Ver Cupons', 'cupompromo'); ?>
                            </a>
                            <?php if ($store_website): ?>
                                <a href="<?php echo esc_url($store_website); ?>" 
                                   class="btn btn-outline"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    <?php _e('Visitar Loja', 'cupompromo'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="<?php echo esc_url(home_url('/lojas/')); ?>" class="btn btn-outline">
                    <?php _e('Ver Todas as Lojas', 'cupompromo'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Cupons em Destaque -->
    <section class="featured-coupons-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php _e('Cupons em Destaque', 'cupompromo'); ?></h2>
                <p class="section-subtitle"><?php _e('Os cupons mais populares e verificados', 'cupompromo'); ?></p>
            </div>
            
            <div class="coupons-grid featured">
                <?php
                $featured_coupons = get_posts([
                    'post_type' => 'cupompromo_coupon',
                    'posts_per_page' => 8,
                    'meta_query' => [
                        [
                            'key' => '_featured_coupon',
                            'value' => '1',
                            'compare' => '='
                        ],
                        [
                            'key' => '_coupon_status',
                            'value' => 'active',
                            'compare' => '='
                        ]
                    ],
                    'orderby' => 'meta_value_num',
                    'meta_key' => '_click_count',
                    'order' => 'DESC'
                ]);
                
                foreach ($featured_coupons as $coupon):
                    $coupon_data = cupompromo_get_coupon_data($coupon->ID);
                    $config = [
                        'show_store_info' => true,
                        'show_categories' => false,
                        'show_meta' => true,
                        'show_actions' => true,
                        'card_style' => 'featured',
                        'show_verification' => true,
                        'show_usage_count' => true,
                        'show_date' => false,
                        'truncate_description' => true,
                        'description_length' => 12
                    ];
                    
                    include get_template_directory() . '/templates/parts/coupon-card.php';
                endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="<?php echo esc_url(home_url('/cupons/')); ?>" class="btn btn-outline">
                    <?php _e('Ver Todos os Cupons', 'cupompromo'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Seção de Benefícios -->
    <section class="benefits-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php _e('Por que usar o Cupompromo?', 'cupompromo'); ?></h2>
                <p class="section-subtitle"><?php _e('Economize tempo e dinheiro com nossos cupons verificados', 'cupompromo'); ?></p>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </div>
                    <h3 class="benefit-title"><?php _e('Cupons Verificados', 'cupompromo'); ?></h3>
                    <p class="benefit-description">
                        <?php _e('Todos os nossos cupons são verificados regularmente para garantir que funcionem.', 'cupompromo'); ?>
                    </p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 4.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <h3 class="benefit-title"><?php _e('Melhores Descontos', 'cupompromo'); ?></h3>
                    <p class="benefit-description">
                        <?php _e('Selecionamos os cupons com os maiores descontos das principais lojas.', 'cupompromo'); ?>
                    </p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                        </svg>
                    </div>
                    <h3 class="benefit-title"><?php _e('Economia Garantida', 'cupompromo'); ?></h3>
                    <p class="benefit-description">
                        <?php _e('Economize em suas compras com cupons exclusivos e ofertas especiais.', 'cupompromo'); ?>
                    </p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3 class="benefit-title"><?php _e('Segurança Total', 'cupompromo'); ?></h3>
                    <p class="benefit-description">
                        <?php _e('Todos os links são seguros e direcionam para as lojas oficiais.', 'cupompromo'); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2 class="newsletter-title"><?php _e('Fique por dentro das novidades!', 'cupompromo'); ?></h2>
                    <p class="newsletter-subtitle">
                        <?php _e('Receba os melhores cupons e ofertas diretamente no seu email.', 'cupompromo'); ?>
                    </p>
                </div>
                
                <div class="newsletter-form">
                    <form class="newsletter-signup" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                        <input type="hidden" name="action" value="cupompromo_newsletter_signup">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('cupompromo_newsletter'); ?>">
                        
                        <div class="form-group">
                            <input 
                                type="email" 
                                name="email" 
                                placeholder="<?php esc_attr_e('Seu melhor email', 'cupompromo'); ?>"
                                required
                                class="newsletter-input"
                            >
                            <button type="submit" class="btn btn-primary newsletter-submit">
                                <?php _e('Inscrever-se', 'cupompromo'); ?>
                            </button>
                        </div>
                        
                        <div class="form-footer">
                            <label class="checkbox-label">
                                <input type="checkbox" name="privacy" required>
                                <span class="checkmark"></span>
                                <?php 
                                printf(
                                    __('Concordo com a <a href="%s" target="_blank">Política de Privacidade</a>', 'cupompromo'),
                                    esc_url(home_url('/politica-privacidade/'))
                                );
                                ?>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer('cupompromo'); ?> 