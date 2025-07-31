<?php
/**
 * Classe de shortcodes do plugin Cupompromo
 *
 * @package Cupompromo
 * @since 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Cupompromo_Shortcodes
 */
class Cupompromo_Shortcodes {
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        add_shortcode('cupompromo_search', array($this, 'search_shortcode'));
        add_shortcode('cupompromo_stores_grid', array($this, 'stores_grid_shortcode'));
        add_shortcode('cupompromo_popular_coupons', array($this, 'popular_coupons_shortcode'));
        add_shortcode('cupompromo_coupons_by_category', array($this, 'coupons_by_category_shortcode'));
        add_shortcode('cupompromo_featured_stores', array($this, 'featured_stores_shortcode'));
        add_shortcode('cupompromo_coupon_form', array($this, 'coupon_form_shortcode'));
    }
    
    /**
     * Shortcode de busca
     */
    public function search_shortcode($atts): string {
        $atts = shortcode_atts(array(
            'placeholder' => __('Buscar cupons...', 'cupompromo'),
            'show_filters' => 'true'
        ), $atts, 'cupompromo_search');
        
        ob_start();
        ?>
        <div class="cupompromo-search-shortcode">
            <form id="cupompromo-search-form" class="cupompromo-search-form">
                <div class="search-input-group">
                    <input type="text" 
                           name="search_query" 
                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                           class="search-input">
                    <button type="submit" class="search-submit">
                        <span class="search-icon">🔍</span>
                    </button>
                </div>
                <?php if ($atts['show_filters'] === 'true'): ?>
                    <div class="search-filters">
                        <select name="store_filter" class="filter-select">
                            <option value=""><?php _e('Todas as Lojas', 'cupompromo'); ?></option>
                            <?php
                            global $wpdb;
                            $table_stores = $wpdb->prefix . 'cupompromo_stores';
                            $stores = $wpdb->get_results("SELECT id, name FROM $table_stores WHERE status = 'active' ORDER BY name");
                            
                            foreach ($stores as $store) {
                                echo '<option value="' . esc_attr($store->id) . '">' . esc_html($store->name) . '</option>';
                            }
                            ?>
                        </select>
                        <select name="type_filter" class="filter-select">
                            <option value=""><?php _e('Todos os Tipos', 'cupompromo'); ?></option>
                            <option value="code"><?php _e('Códigos', 'cupompromo'); ?></option>
                            <option value="offer"><?php _e('Ofertas', 'cupompromo'); ?></option>
                        </select>
                    </div>
                <?php endif; ?>
            </form>
            <div id="search-results" class="search-results"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode de grid de lojas
     */
    public function stores_grid_shortcode($atts): string {
        $atts = shortcode_atts(array(
            'limit' => 12,
            'featured_only' => 'false',
            'columns' => 3,
            'card_style' => 'default'
        ), $atts, 'cupompromo_stores_grid');
        
        global $wpdb;
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $where_conditions = array('status = "active"');
        $where_values = array();
        
        if ($atts['featured_only'] === 'true') {
            $where_conditions[] = 'featured_store = 1';
        }
        
        $sql = "SELECT * FROM $table_stores WHERE " . implode(' AND ', $where_conditions) . " ORDER BY created_at DESC LIMIT %d";
        $where_values[] = intval($atts['limit']);
        
        $stores = $wpdb->get_results($wpdb->prepare($sql, ...$where_values));
        
        ob_start();
        ?>
        <div class="cupompromo-stores-grid" style="--columns: <?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($stores as $store): ?>
                <?php
                $store_card = new Cupompromo_Store_Card($store, array(
                    'card_style' => $atts['card_style']
                ));
                echo $store_card->render();
                ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode de cupons populares
     */
    public function popular_coupons_shortcode($atts): string {
        $atts = shortcode_atts(array(
            'limit' => 6,
            'store_id' => '',
            'coupon_type' => ''
        ), $atts, 'cupompromo_popular_coupons');
        
        global $wpdb;
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $where_conditions = array('c.status = "active"');
        $where_values = array();
        
        if (!empty($atts['store_id'])) {
            $where_conditions[] = 'c.store_id = %d';
            $where_values[] = intval($atts['store_id']);
        }
        
        if (!empty($atts['coupon_type'])) {
            $where_conditions[] = 'c.coupon_type = %s';
            $where_values[] = $atts['coupon_type'];
        }
        
        $sql = "SELECT c.*, s.name as store_name, s.logo_url as store_logo 
                FROM $table_coupons c 
                LEFT JOIN $table_stores s ON c.store_id = s.id 
                WHERE " . implode(' AND ', $where_conditions) . " 
                ORDER BY c.click_count DESC 
                LIMIT %d";
        $where_values[] = intval($atts['limit']);
        
        $coupons = $wpdb->get_results($wpdb->prepare($sql, ...$where_values));
        
        ob_start();
        ?>
        <div class="cupompromo-popular-coupons">
            <?php foreach ($coupons as $coupon): ?>
                <div class="coupon-card">
                    <div class="coupon-header">
                        <div class="store-info">
                            <?php if (!empty($coupon->store_logo)): ?>
                                <img src="<?php echo esc_url($coupon->store_logo); ?>" alt="<?php echo esc_attr($coupon->store_name); ?>" class="store-logo">
                            <?php endif; ?>
                            <span class="store-name"><?php echo esc_html($coupon->store_name); ?></span>
                        </div>
                        <div class="coupon-type">
                            <span class="badge badge-<?php echo esc_attr($coupon->coupon_type); ?>">
                                <?php echo $coupon->coupon_type === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="coupon-content">
                        <h3 class="coupon-title"><?php echo esc_html($coupon->title); ?></h3>
                        <div class="coupon-discount">
                            <span class="discount-value"><?php echo esc_html($coupon->discount_value); ?></span>
                            <?php if ($coupon->discount_type === 'percentage'): ?>
                                <span class="discount-type">% OFF</span>
                            <?php else: ?>
                                <span class="discount-type">OFF</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($coupon->coupon_code)): ?>
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
                            <span class="clicks-count"><?php echo $coupon->click_count; ?> cliques</span>
                        </div>
                        <div class="coupon-actions">
                            <?php if (!empty($coupon->affiliate_url)): ?>
                                <a href="<?php echo esc_url($coupon->affiliate_url); ?>" class="btn-get-coupon" target="_blank" rel="nofollow">
                                    <?php echo $coupon->coupon_type === 'code' ? __('Ver Cupom', 'cupompromo') : __('Ativar Oferta', 'cupompromo'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode de cupons por categoria
     */
    public function coupons_by_category_shortcode($atts): string {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => 10
        ), $atts, 'cupompromo_coupons_by_category');
        
        if (empty($atts['category'])) {
            return '<p>' . __('Categoria não especificada.', 'cupompromo') . '</p>';
        }
        
        global $wpdb;
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        $table_categories = $wpdb->prefix . 'cupompromo_categories';
        $table_coupon_categories = $wpdb->prefix . 'cupompromo_coupon_categories';
        
        $sql = "SELECT c.*, s.name as store_name, s.logo_url as store_logo 
                FROM $table_coupons c 
                LEFT JOIN $table_stores s ON c.store_id = s.id 
                LEFT JOIN $table_coupon_categories cc ON c.id = cc.coupon_id 
                LEFT JOIN $table_categories cat ON cc.category_id = cat.id 
                WHERE c.status = 'active' AND cat.slug = %s 
                ORDER BY c.click_count DESC 
                LIMIT %d";
        
        $coupons = $wpdb->get_results($wpdb->prepare($sql, $atts['category'], intval($atts['limit'])));
        
        if (empty($coupons)) {
            return '<p>' . __('Nenhum cupom encontrado para esta categoria.', 'cupompromo') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="cupompromo-category-coupons">
            <h3><?php echo sprintf(__('Cupons da categoria: %s', 'cupompromo'), esc_html($atts['category'])); ?></h3>
            <?php foreach ($coupons as $coupon): ?>
                <div class="coupon-card">
                    <div class="coupon-header">
                        <div class="store-info">
                            <?php if (!empty($coupon->store_logo)): ?>
                                <img src="<?php echo esc_url($coupon->store_logo); ?>" alt="<?php echo esc_attr($coupon->store_name); ?>" class="store-logo">
                            <?php endif; ?>
                            <span class="store-name"><?php echo esc_html($coupon->store_name); ?></span>
                        </div>
                        <div class="coupon-type">
                            <span class="badge badge-<?php echo esc_attr($coupon->coupon_type); ?>">
                                <?php echo $coupon->coupon_type === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="coupon-content">
                        <h3 class="coupon-title"><?php echo esc_html($coupon->title); ?></h3>
                        <div class="coupon-discount">
                            <span class="discount-value"><?php echo esc_html($coupon->discount_value); ?></span>
                            <?php if ($coupon->discount_type === 'percentage'): ?>
                                <span class="discount-type">% OFF</span>
                            <?php else: ?>
                                <span class="discount-type">OFF</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($coupon->coupon_code)): ?>
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
                            <span class="clicks-count"><?php echo $coupon->click_count; ?> cliques</span>
                        </div>
                        <div class="coupon-actions">
                            <?php if (!empty($coupon->affiliate_url)): ?>
                                <a href="<?php echo esc_url($coupon->affiliate_url); ?>" class="btn-get-coupon" target="_blank" rel="nofollow">
                                    <?php echo $coupon->coupon_type === 'code' ? __('Ver Cupom', 'cupompromo') : __('Ativar Oferta', 'cupompromo'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode de lojas em destaque
     */
    public function featured_stores_shortcode($atts): string {
        $atts = shortcode_atts(array(
            'limit' => 6,
            'columns' => 2
        ), $atts, 'cupompromo_featured_stores');
        
        global $wpdb;
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $stores = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_stores WHERE featured_store = 1 AND status = 'active' ORDER BY created_at DESC LIMIT %d",
            intval($atts['limit'])
        ));
        
        ob_start();
        ?>
        <div class="cupompromo-featured-stores" style="--columns: <?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($stores as $store): ?>
                <div class="featured-store-card">
                    <div class="store-logo">
                        <?php if (!empty($store->logo_url)): ?>
                            <img src="<?php echo esc_url($store->logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>">
                        <?php else: ?>
                            <div class="store-placeholder"><?php echo esc_html(substr($store->name, 0, 1)); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="store-info">
                        <h3 class="store-name"><?php echo esc_html($store->name); ?></h3>
                        <?php if (!empty($store->store_description)): ?>
                            <p class="store-description"><?php echo esc_html($store->store_description); ?></p>
                        <?php endif; ?>
                        <div class="store-stats">
                            <span class="coupons-count"><?php echo $this->get_store_coupons_count($store->id); ?> cupons</span>
                            <span class="featured-badge"><?php _e('Destaque', 'cupompromo'); ?></span>
                        </div>
                    </div>
                    <div class="store-actions">
                        <a href="<?php echo esc_url(home_url('/loja/' . $store->slug)); ?>" class="btn-view-coupons">
                            <?php _e('Ver Cupons', 'cupompromo'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode de formulário de cupom
     */
    public function coupon_form_shortcode($atts): string {
        $atts = shortcode_atts(array(
            'placeholder' => __('Digite o código do cupom', 'cupompromo'),
            'button_text' => __('Validar Cupom', 'cupompromo')
        ), $atts, 'cupompromo_coupon_form');
        
        ob_start();
        ?>
        <div class="cupompromo-coupon-form">
            <form id="cupompromo-validate-form" class="validate-form">
                <div class="form-group">
                    <input type="text" 
                           id="coupon-code" 
                           name="coupon_code" 
                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                           class="coupon-input" 
                           required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-validate-coupon">
                        <?php echo esc_html($atts['button_text']); ?>
                    </button>
                </div>
            </form>
            <div id="validation-result" class="validation-result"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Obtém contagem de cupons de uma loja
     */
    private function get_store_coupons_count(int $store_id): int {
        global $wpdb;
        
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_coupons WHERE store_id = %d AND status = 'active'",
            $store_id
        ));
    }
} 