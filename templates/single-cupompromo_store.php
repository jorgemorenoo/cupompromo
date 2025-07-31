<?php
/**
 * Template para página individual de loja
 * 
 * @package Cupompromo
 * @since 1.0.0
 */

get_header(); ?>

<div class="cupompromo-store-page">
    <div class="container">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            
            <?php
            // Obtém dados da loja
            $store_id = get_the_ID();
            $store_data = (object) array(
                'id' => $store_id,
                'name' => get_the_title(),
                'slug' => get_post_field('post_name'),
                'logo_url' => get_post_meta($store_id, '_store_logo', true),
                'store_description' => get_the_content(),
                'store_website' => get_post_meta($store_id, '_store_website', true),
                'featured_store' => get_post_meta($store_id, '_featured_store', true),
                'default_commission' => get_post_meta($store_id, '_default_commission', true),
                'status' => get_post_status()
            );
            
            // Cria card da loja
            $store_card = new Cupompromo_Store_Card($store_data, array(
                'card_style' => 'featured',
                'show_description' => true,
                'show_stats' => true
            ));
            ?>
            
            <!-- Header da Loja -->
            <div class="store-page-header">
                <?php echo $store_card->render(); ?>
            </div>
            
            <!-- Filtros de Cupons -->
            <div class="coupon-filters">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">
                        <?php _e('Todos os Cupons', 'cupompromo'); ?>
                    </button>
                    <button class="filter-tab" data-filter="code">
                        <?php _e('Códigos', 'cupompromo'); ?>
                    </button>
                    <button class="filter-tab" data-filter="offer">
                        <?php _e('Ofertas Diretas', 'cupompromo'); ?>
                    </button>
                </div>
                
                <div class="filter-options">
                    <select class="sort-select" id="sort-coupons">
                        <option value="created_at DESC"><?php _e('Mais Recentes', 'cupompromo'); ?></option>
                        <option value="click_count DESC"><?php _e('Mais Populares', 'cupompromo'); ?></option>
                        <option value="discount_value DESC"><?php _e('Maior Desconto', 'cupompromo'); ?></option>
                        <option value="usage_count DESC"><?php _e('Mais Usados', 'cupompromo'); ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Grid de Cupons -->
            <div class="coupons-container">
                <div class="cupons-grid" id="coupons-grid">
                    <?php
                    // Obtém cupons da loja
                    $coupon_manager = Cupompromo_Coupon_Manager::get_instance();
                    $coupons = $coupon_manager->get_coupons(array(
                        'store_id' => $store_id,
                        'limit' => 20,
                        'orderby' => 'created_at',
                        'order' => 'DESC'
                    ));
                    
                    if (!empty($coupons)) :
                        foreach ($coupons as $coupon) :
                    ?>
                        <div class="coupon-card" data-coupon-type="<?php echo esc_attr($coupon->coupon_type); ?>">
                            <div class="coupon-header">
                                <div class="coupon-type">
                                    <span class="badge badge-<?php echo esc_attr($coupon->coupon_type); ?>">
                                        <?php echo $coupon->coupon_type === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo'); ?>
                                    </span>
                                </div>
                                <?php if ($coupon->is_verified) : ?>
                                    <div class="verification-badge">
                                        <span class="badge-icon">✓</span>
                                        <span class="badge-text"><?php _e('Verificado', 'cupompromo'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="coupon-content">
                                <h3 class="coupon-title"><?php echo esc_html($coupon->title); ?></h3>
                                
                                <?php if (!empty($coupon->description)) : ?>
                                    <div class="coupon-description">
                                        <?php echo wp_kses_post($coupon->description); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="coupon-discount">
                                    <span class="discount-value"><?php echo esc_html($coupon->discount_value); ?></span>
                                    <?php if ($coupon->discount_type === 'percentage') : ?>
                                        <span class="discount-type">% OFF</span>
                                    <?php else : ?>
                                        <span class="discount-type">OFF</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($coupon->coupon_code)) : ?>
                                    <div class="coupon-code">
                                        <code><?php echo esc_html($coupon->coupon_code); ?></code>
                                        <button class="btn-copy-code" data-code="<?php echo esc_attr($coupon->coupon_code); ?>">
                                            <?php _e('Copiar', 'cupompromo'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="coupon-footer">
                                <div class="coupon-stats">
                                    <span class="stat-item">
                                        <span class="stat-icon">👁️</span>
                                        <span class="stat-value"><?php echo number_format($coupon->click_count); ?></span>
                                    </span>
                                    <span class="stat-item">
                                        <span class="stat-icon">📅</span>
                                        <span class="stat-value">
                                            <?php 
                                            if ($coupon->days_until_expiry === -1) {
                                                _e('Sem expiração', 'cupompromo');
                                            } elseif ($coupon->days_until_expiry === 0) {
                                                _e('Expira hoje', 'cupompromo');
                                            } else {
                                                printf(_n('Expira em %d dia', 'Expira em %d dias', $coupon->days_until_expiry, 'cupompromo'), $coupon->days_until_expiry);
                                            }
                                            ?>
                                        </span>
                                    </span>
                                </div>
                                
                                <div class="coupon-actions">
                                    <a href="<?php echo esc_url($coupon->affiliate_url); ?>" 
                                       class="btn-view-coupon"
                                       target="_blank"
                                       rel="nofollow"
                                       data-coupon-id="<?php echo esc_attr($coupon->id); ?>">
                                        <?php _e('Ver Cupom', 'cupompromo'); ?>
                                    </a>
                                    
                                    <?php if (!empty($coupon->store_website)) : ?>
                                        <a href="<?php echo esc_url($coupon->store_website); ?>" 
                                           class="btn-visit-store"
                                           target="_blank"
                                           rel="nofollow">
                                            <?php _e('Visitar Loja', 'cupompromo'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    else :
                    ?>
                        <div class="no-coupons">
                            <div class="no-coupons-icon">🎫</div>
                            <h3><?php _e('Nenhum cupom disponível', 'cupompromo'); ?></h3>
                            <p><?php _e('Esta loja ainda não possui cupons cadastrados.', 'cupompromo'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Paginação -->
                <?php if (count($coupons) >= 20) : ?>
                    <div class="pagination">
                        <button class="pagination-btn prev" disabled>
                            <?php _e('Anterior', 'cupompromo'); ?>
                        </button>
                        <span class="pagination-info">
                            <?php _e('Página 1 de 1', 'cupompromo'); ?>
                        </span>
                        <button class="pagination-btn next" disabled>
                            <?php _e('Próxima', 'cupompromo'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Cupons Expirados (Colapsado) -->
            <div class="expired-coupons-section">
                <button class="expired-toggle" type="button">
                    <span class="toggle-icon">▼</span>
                    <span class="toggle-text"><?php _e('Ver Cupons Expirados', 'cupompromo'); ?></span>
                </button>
                
                <div class="expired-coupons" style="display: none;">
                    <?php
                    $expired_coupons = $coupon_manager->get_coupons(array(
                        'store_id' => $store_id,
                        'status' => 'expired',
                        'limit' => 10
                    ));
                    
                    if (!empty($expired_coupons)) :
                        foreach ($expired_coupons as $coupon) :
                    ?>
                        <div class="coupon-card expired">
                            <div class="coupon-header">
                                <div class="coupon-type">
                                    <span class="badge badge-expired">
                                        <?php _e('Expirado', 'cupompromo'); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="coupon-content">
                                <h3 class="coupon-title"><?php echo esc_html($coupon->title); ?></h3>
                                <div class="coupon-discount">
                                    <span class="discount-value"><?php echo esc_html($coupon->discount_value); ?></span>
                                    <?php if ($coupon->discount_type === 'percentage') : ?>
                                        <span class="discount-type">% OFF</span>
                                    <?php else : ?>
                                        <span class="discount-type">OFF</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    else :
                    ?>
                        <p class="no-expired-coupons">
                            <?php _e('Nenhum cupom expirado encontrado.', 'cupompromo'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php endwhile; endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtros de cupons
    const filterTabs = document.querySelectorAll('.filter-tab');
    const couponCards = document.querySelectorAll('.coupon-card');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Remove active class de todos os tabs
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filtra cupons
            couponCards.forEach(card => {
                if (filter === 'all' || card.dataset.couponType === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // Ordenação
    const sortSelect = document.getElementById('sort-coupons');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            // Implementar ordenação via AJAX
            console.log('Ordenar por:', this.value);
        });
    }
    
    // Cupons expirados
    const expiredToggle = document.querySelector('.expired-toggle');
    const expiredCoupons = document.querySelector('.expired-coupons');
    
    if (expiredToggle && expiredCoupons) {
        expiredToggle.addEventListener('click', function() {
            const isVisible = expiredCoupons.style.display !== 'none';
            expiredCoupons.style.display = isVisible ? 'none' : 'block';
            
            const toggleIcon = this.querySelector('.toggle-icon');
            toggleIcon.textContent = isVisible ? '▼' : '▲';
        });
    }
    
    // Tracking de cliques
    const viewButtons = document.querySelectorAll('.btn-view-coupon');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const couponId = this.dataset.couponId;
            if (couponId) {
                // Envia tracking via AJAX
                fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'cupompromo_track_click',
                        coupon_id: couponId,
                        nonce: '<?php echo wp_create_nonce('cupompromo_track_click'); ?>'
                    })
                });
            }
        });
    });
    
    // Copiar código
    const copyButtons = document.querySelectorAll('.btn-copy-code');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const code = this.dataset.code;
            navigator.clipboard.writeText(code).then(() => {
                this.textContent = '<?php _e('Copiado!', 'cupompromo'); ?>';
                setTimeout(() => {
                    this.textContent = '<?php _e('Copiar', 'cupompromo'); ?>';
                }, 2000);
            });
        });
    });
});
</script>

<?php get_footer(); ?> 