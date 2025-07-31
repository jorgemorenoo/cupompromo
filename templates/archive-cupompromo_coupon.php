<?php
/**
 * Template de arquivo para cupons
 *
 * @package Cupompromo
 * @since 1.0.0
 */

get_header(); ?>

<div class="cupompromo-container">
    <header class="cupompromo-archive-header">
        <h1 class="cupompromo-archive-title">
            <?php _e('Cupons de Desconto', 'cupompromo'); ?>
        </h1>
        
        <?php if (get_query_var('paged') > 1) : ?>
            <p class="cupompromo-archive-description">
                <?php printf(
                    __('Página %d de %d', 'cupompromo'),
                    get_query_var('paged'),
                    $wp_query->max_num_pages
                ); ?>
            </p>
        <?php endif; ?>
    </header>

    <!-- Barra de Busca -->
    <div class="cupompromo-search">
        <form class="cupompromo-search__form" method="get">
            <input 
                type="text" 
                name="s" 
                class="cupompromo-search__input cupompromo-search__input--search"
                placeholder="<?php esc_attr_e('Buscar cupons...', 'cupompromo'); ?>"
                value="<?php echo esc_attr(get_search_query()); ?>"
            >
            <button type="submit" class="cupompromo-search__button">
                <?php _e('Buscar', 'cupompromo'); ?>
            </button>
        </form>
    </div>

    <!-- Filtros -->
    <div class="cupompromo-filters">
        <div class="cupompromo-filters__row">
            <!-- Filtro por Loja -->
            <div class="cupompromo-filter-group">
                <label for="filter-store" class="cupompromo-filter-label">
                    <?php _e('Loja:', 'cupompromo'); ?>
                </label>
                <select id="filter-store" class="cupompromo-filter" data-filter-type="store">
                    <option value=""><?php _e('Todas as lojas', 'cupompromo'); ?></option>
                    <?php
                    $stores = get_posts(array(
                        'post_type' => 'cupompromo_store',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ));
                    
                    foreach ($stores as $store) {
                        $selected = (isset($_GET['store']) && $_GET['store'] == $store->ID) ? 'selected' : '';
                        echo sprintf(
                            '<option value="%d" %s>%s</option>',
                            $store->ID,
                            $selected,
                            esc_html($store->post_title)
                        );
                    }
                    ?>
                </select>
            </div>

            <!-- Filtro por Tipo -->
            <div class="cupompromo-filter-group">
                <label for="filter-type" class="cupompromo-filter-label">
                    <?php _e('Tipo:', 'cupompromo'); ?>
                </label>
                <select id="filter-type" class="cupompromo-filter" data-filter-type="type">
                    <option value=""><?php _e('Todos os tipos', 'cupompromo'); ?></option>
                    <option value="code" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'code'); ?>>
                        <?php _e('Códigos', 'cupompromo'); ?>
                    </option>
                    <option value="offer" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'offer'); ?>>
                        <?php _e('Ofertas Diretas', 'cupompromo'); ?>
                    </option>
                </select>
            </div>

            <!-- Filtro por Desconto -->
            <div class="cupompromo-filter-group">
                <label for="filter-discount" class="cupompromo-filter-label">
                    <?php _e('Desconto:', 'cupompromo'); ?>
                </label>
                <select id="filter-discount" class="cupompromo-filter" data-filter-type="discount">
                    <option value=""><?php _e('Qualquer desconto', 'cupompromo'); ?></option>
                    <option value="percentage" <?php selected(isset($_GET['discount']) ? $_GET['discount'] : '', 'percentage'); ?>>
                        <?php _e('Porcentagem', 'cupompromo'); ?>
                    </option>
                    <option value="fixed" <?php selected(isset($_GET['discount']) ? $_GET['discount'] : '', 'fixed'); ?>>
                        <?php _e('Valor Fixo', 'cupompromo'); ?>
                    </option>
                </select>
            </div>

            <!-- Ordenação -->
            <div class="cupompromo-filter-group">
                <label for="filter-order" class="cupompromo-filter-label">
                    <?php _e('Ordenar por:', 'cupompromo'); ?>
                </label>
                <select id="filter-order" class="cupompromo-filter" data-filter-type="order">
                    <option value="date" <?php selected(isset($_GET['order']) ? $_GET['order'] : '', 'date'); ?>>
                        <?php _e('Mais Recentes', 'cupompromo'); ?>
                    </option>
                    <option value="popular" <?php selected(isset($_GET['order']) ? $_GET['order'] : '', 'popular'); ?>>
                        <?php _e('Mais Populares', 'cupompromo'); ?>
                    </option>
                    <option value="discount" <?php selected(isset($_GET['order']) ? $_GET['order'] : '', 'discount'); ?>>
                        <?php _e('Maior Desconto', 'cupompromo'); ?>
                    </option>
                </select>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="cupompromo-results" data-page="<?php echo get_query_var('paged', 1); ?>">
        <?php if (have_posts()) : ?>
            <div class="cupompromo-grid cupompromo-grid--coupons">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $coupon_id = get_the_ID();
                    $store_id = get_post_meta($coupon_id, '_store_id', true);
                    $store = get_post($store_id);
                    $coupon_type = get_post_meta($coupon_id, '_coupon_type', true);
                    $coupon_code = get_post_meta($coupon_id, '_coupon_code', true);
                    $discount_value = get_post_meta($coupon_id, '_discount_value', true);
                    $discount_type = get_post_meta($coupon_id, '_discount_type', true);
                    $expiry_date = get_post_meta($coupon_id, '_expiry_date', true);
                    $click_count = get_post_meta($coupon_id, '_click_count', true);
                    $verified_date = get_post_meta($coupon_id, '_verified_date', true);
                    ?>
                    
                    <article class="cupompromo-coupon-card" data-coupon-id="<?php echo esc_attr($coupon_id); ?>">
                        <div class="cupompromo-coupon-card__header">
                            <div class="cupompromo-coupon-card__store">
                                <?php if ($store && has_post_thumbnail($store_id)) : ?>
                                    <img 
                                        src="<?php echo get_the_post_thumbnail_url($store_id, 'thumbnail'); ?>"
                                        alt="<?php echo esc_attr($store->post_title); ?>"
                                        class="cupompromo-coupon-card__store-logo"
                                        loading="lazy"
                                    >
                                <?php endif; ?>
                                <span class="cupompromo-coupon-card__store-name">
                                    <?php echo esc_html($store ? $store->post_title : __('Loja não encontrada', 'cupompromo')); ?>
                                </span>
                            </div>
                            
                            <div class="cupompromo-coupon-card__discount">
                                <?php echo esc_html($discount_value); ?>
                                <?php echo $discount_type === 'percentage' ? '% OFF' : 'R$ OFF'; ?>
                            </div>
                        </div>

                        <h3 class="cupompromo-coupon-card__title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <div class="cupompromo-coupon-card__description">
                            <?php the_excerpt(); ?>
                        </div>

                        <div class="cupompromo-coupon-card__footer">
                            <div class="cupompromo-coupon-card__status">
                                <?php if ($verified_date) : ?>
                                    <span class="cupompromo-status cupompromo-status--verified">
                                        <?php printf(__('Verificado em %s', 'cupompromo'), date_i18n(get_option('date_format'), strtotime($verified_date))); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($click_count > 0) : ?>
                                    <span class="cupompromo-status cupompromo-status--popular">
                                        <?php printf(_n('%d pessoa usou', '%d pessoas usaram', $click_count, 'cupompromo'), $click_count); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <button 
                                class="cupompromo-coupon-card__button"
                                data-coupon-id="<?php echo esc_attr($coupon_id); ?>"
                                data-coupon-type="<?php echo esc_attr($coupon_type); ?>"
                            >
                                <?php if ($coupon_type === 'code') : ?>
                                    <?php _e('Ver Cupom', 'cupompromo'); ?>
                                <?php else : ?>
                                    <?php _e('Ativar Oferta', 'cupompromo'); ?>
                                <?php endif; ?>
                            </button>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Paginação -->
            <?php if ($wp_query->max_num_pages > 1) : ?>
                <nav class="cupompromo-pagination">
                    <?php
                    echo paginate_links(array(
                        'prev_text' => __('&laquo; Anterior', 'cupompromo'),
                        'next_text' => __('Próximo &raquo;', 'cupompromo'),
                        'type' => 'list',
                        'class' => 'cupompromo-pagination__list'
                    ));
                    ?>
                </nav>
            <?php endif; ?>

        <?php else : ?>
            <div class="cupompromo-no-results">
                <div class="cupompromo-no-results__icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </div>
                <h2 class="cupompromo-no-results__title">
                    <?php _e('Nenhum cupom encontrado', 'cupompromo'); ?>
                </h2>
                <p class="cupompromo-no-results__description">
                    <?php _e('Tente ajustar os filtros ou fazer uma nova busca.', 'cupompromo'); ?>
                </p>
                <a href="<?php echo esc_url(get_post_type_archive_link('cupompromo_coupon')); ?>" class="cupompromo-btn cupompromo-btn--primary">
                    <?php _e('Ver Todos os Cupons', 'cupompromo'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?> 