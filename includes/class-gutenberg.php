<?php
/**
 * Classe para blocos Gutenberg do plugin Cupompromo
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
 * Classe Cupompromo_Gutenberg
 */
class Cupompromo_Gutenberg {
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_assets'));
    }
    
    /**
     * Registra os blocos Gutenberg
     */
    public function register_blocks(): void {
        // Registra blocos usando block.json
        $blocks_dir = CUPOMPROMO_PLUGIN_PATH . 'blocks/';
        
        // Bloco: Grid de Lojas
        if (file_exists($blocks_dir . 'stores-grid/block.json')) {
            register_block_type($blocks_dir . 'stores-grid');
        }
        
        // Bloco: Barra de Busca
        if (file_exists($blocks_dir . 'search-bar/block.json')) {
            register_block_type($blocks_dir . 'search-bar');
        }
        
        // Bloco: Lista de Cupons (mantém compatibilidade)
        register_block_type('cupompromo/coupons-list', array(
            'editor_script' => 'cupompromo-blocks',
            'editor_style' => 'cupompromo-blocks-editor',
            'style' => 'cupompromo-blocks',
            'render_callback' => array($this, 'render_coupons_list_block'),
            'attributes' => array(
                'limit' => array(
                    'type' => 'number',
                    'default' => 10
                ),
                'store_id' => array(
                    'type' => 'number',
                    'default' => 0
                ),
                'coupon_type' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'show_filters' => array(
                    'type' => 'boolean',
                    'default' => true
                )
            )
        ));
        
        // Bloco: Carrossel de Destaques
        register_block_type('cupompromo/featured-carousel', array(
            'editor_script' => 'cupompromo-blocks',
            'editor_style' => 'cupompromo-blocks-editor',
            'style' => 'cupompromo-blocks',
            'render_callback' => array($this, 'render_featured_carousel_block'),
            'attributes' => array(
                'limit' => array(
                    'type' => 'number',
                    'default' => 5
                ),
                'autoplay' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'interval' => array(
                    'type' => 'number',
                    'default' => 5000
                )
            )
        ));
    }
    
    /**
     * Carrega assets dos blocos
     */
    public function enqueue_block_assets(): void {
        wp_enqueue_script(
            'cupompromo-blocks',
            CUPOMPROMO_PLUGIN_URL . 'assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            CUPOMPROMO_VERSION
        );
        
        wp_enqueue_style(
            'cupompromo-blocks-editor',
            CUPOMPROMO_PLUGIN_URL . 'assets/css/blocks-editor.css',
            array('wp-edit-blocks'),
            CUPOMPROMO_VERSION
        );
        
        wp_localize_script('cupompromo-blocks', 'cupompromoBlocks', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cupompromo_blocks_nonce'),
            'strings' => array(
                'storesGrid' => __('Grid de Lojas', 'cupompromo'),
                'couponsList' => __('Lista de Cupons', 'cupompromo'),
                'searchBar' => __('Barra de Busca', 'cupompromo'),
                'featuredCarousel' => __('Carrossel de Destaques', 'cupompromo'),
                'settings' => __('Configurações', 'cupompromo'),
                'limit' => __('Limite', 'cupompromo'),
                'columns' => __('Colunas', 'cupompromo'),
                'placeholder' => __('Placeholder', 'cupompromo'),
                'showFilters' => __('Mostrar Filtros', 'cupompromo'),
                'featuredOnly' => __('Apenas Destaques', 'cupompromo'),
                'store' => __('Loja', 'cupompromo'),
                'couponType' => __('Tipo de Cupom', 'cupompromo'),
                'autoplay' => __('Autoplay', 'cupompromo'),
                'interval' => __('Intervalo (ms)', 'cupompromo')
            )
        ));
    }
    
    /**
     * Renderiza bloco de grid de lojas
     */
    public function render_stores_grid_block($attributes): string {
        $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 12;
        $featured_only = isset($attributes['featured_only']) ? $attributes['featured_only'] : false;
        $columns = isset($attributes['columns']) ? intval($attributes['columns']) : 3;
        
        global $wpdb;
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $where_conditions = array('status = "active"');
        $where_values = array();
        
        if ($featured_only) {
            $where_conditions[] = 'featured_store = 1';
        }
        
        $sql = "SELECT * FROM $table_stores WHERE " . implode(' AND ', $where_conditions) . " ORDER BY created_at DESC LIMIT %d";
        $where_values[] = $limit;
        
        $stores = $wpdb->get_results($wpdb->prepare($sql, ...$where_values));
        
        ob_start();
        ?>
        <div class="cupompromo-stores-grid-block" style="--columns: <?php echo esc_attr($columns); ?>">
            <?php foreach ($stores as $store): ?>
                <div class="store-card">
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
                            <?php if ($store->featured_store): ?>
                                <span class="featured-badge"><?php _e('Destaque', 'cupompromo'); ?></span>
                            <?php endif; ?>
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
     * Renderiza bloco de lista de cupons
     */
    public function render_coupons_list_block($attributes): string {
        $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 10;
        $store_id = isset($attributes['store_id']) ? intval($attributes['store_id']) : 0;
        $coupon_type = isset($attributes['coupon_type']) ? $attributes['coupon_type'] : '';
        $show_filters = isset($attributes['show_filters']) ? $attributes['show_filters'] : true;
        
        global $wpdb;
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $where_conditions = array('c.status = "active"');
        $where_values = array();
        
        if ($store_id > 0) {
            $where_conditions[] = 'c.store_id = %d';
            $where_values[] = $store_id;
        }
        
        if (!empty($coupon_type)) {
            $where_conditions[] = 'c.coupon_type = %s';
            $where_values[] = $coupon_type;
        }
        
        $sql = "SELECT c.*, s.name as store_name, s.logo_url as store_logo 
                FROM $table_coupons c 
                LEFT JOIN $table_stores s ON c.store_id = s.id 
                WHERE " . implode(' AND ', $where_conditions) . " 
                ORDER BY c.click_count DESC 
                LIMIT %d";
        $where_values[] = $limit;
        
        $coupons = $wpdb->get_results($wpdb->prepare($sql, ...$where_values));
        
        ob_start();
        ?>
        <div class="cupompromo-coupons-list-block">
            <?php if ($show_filters): ?>
                <div class="coupons-filters">
                    <select class="filter-store">
                        <option value=""><?php _e('Todas as Lojas', 'cupompromo'); ?></option>
                        <?php
                        $stores = $wpdb->get_results("SELECT id, name FROM $table_stores WHERE status = 'active' ORDER BY name");
                        foreach ($stores as $store) {
                            $selected = ($store->id == $store_id) ? 'selected' : '';
                            echo '<option value="' . esc_attr($store->id) . '" ' . $selected . '>' . esc_html($store->name) . '</option>';
                        }
                        ?>
                    </select>
                    <select class="filter-type">
                        <option value=""><?php _e('Todos os Tipos', 'cupompromo'); ?></option>
                        <option value="code" <?php selected($coupon_type, 'code'); ?>><?php _e('Códigos', 'cupompromo'); ?></option>
                        <option value="offer" <?php selected($coupon_type, 'offer'); ?>><?php _e('Ofertas', 'cupompromo'); ?></option>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="coupons-grid">
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
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderiza bloco de barra de busca
     */
    public function render_search_bar_block($attributes): string {
        $placeholder = isset($attributes['placeholder']) ? $attributes['placeholder'] : __('Buscar cupons...', 'cupompromo');
        $show_filters = isset($attributes['show_filters']) ? $attributes['show_filters'] : true;
        
        ob_start();
        ?>
        <div class="cupompromo-search-bar-block">
            <form class="search-form">
                <div class="search-input-group">
                    <input type="text" 
                           name="search_query" 
                           placeholder="<?php echo esc_attr($placeholder); ?>" 
                           class="search-input">
                    <button type="submit" class="search-submit">
                        <span class="search-icon">🔍</span>
                    </button>
                </div>
                <?php if ($show_filters): ?>
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
            <div class="search-results"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderiza bloco de carrossel de destaques
     */
    public function render_featured_carousel_block($attributes): string {
        $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 5;
        $autoplay = isset($attributes['autoplay']) ? $attributes['autoplay'] : true;
        $interval = isset($attributes['interval']) ? intval($attributes['interval']) : 5000;
        
        global $wpdb;
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $stores = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_stores WHERE featured_store = 1 AND status = 'active' ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
        
        ob_start();
        ?>
        <div class="cupompromo-featured-carousel-block" 
             data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
             data-interval="<?php echo esc_attr($interval); ?>">
            <div class="carousel-container">
                <?php foreach ($stores as $store): ?>
                    <div class="carousel-item">
                        <div class="store-card">
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
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="carousel-controls">
                <button class="carousel-prev">‹</button>
                <button class="carousel-next">›</button>
            </div>
            <div class="carousel-indicators">
                <?php for ($i = 0; $i < count($stores); $i++): ?>
                    <button class="carousel-indicator <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></button>
                <?php endfor; ?>
            </div>
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