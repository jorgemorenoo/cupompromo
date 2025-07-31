<?php
/**
 * Classe do frontend do plugin Cupompromo
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
 * Classe Cupompromo_Frontend
 */
class Cupompromo_Frontend {
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_cupompromo_validate_coupon', array($this, 'validate_coupon'));
        add_action('wp_ajax_nopriv_cupompromo_validate_coupon', array($this, 'validate_coupon'));
        add_action('wp_ajax_cupompromo_search_coupons', array($this, 'search_coupons'));
        add_action('wp_ajax_nopriv_cupompromo_search_coupons', array($this, 'search_coupons'));
        add_action('wp_ajax_cupompromo_track_click', array($this, 'track_click'));
        add_action('wp_ajax_nopriv_cupompromo_track_click', array($this, 'track_click'));
        add_filter('template_include', array($this, 'load_templates'));
        add_action('wp_head', array($this, 'add_meta_tags'));
    }
    
    /**
     * Carrega scripts e estilos do frontend
     */
    public function enqueue_scripts(): void {
        wp_enqueue_style(
            'cupompromo-frontend',
            CUPOMPROMO_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            CUPOMPROMO_VERSION
        );
        
        wp_enqueue_script(
            'cupompromo-frontend',
            CUPOMPROMO_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            CUPOMPROMO_VERSION,
            true
        );
        
        wp_localize_script('cupompromo-frontend', 'cupompromoFrontend', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cupompromo_frontend_nonce'),
            'strings' => array(
                'validating' => __('Validando...', 'cupompromo'),
                'valid' => __('Cupom válido!', 'cupompromo'),
                'invalid' => __('Cupom inválido.', 'cupompromo'),
                'expired' => __('Cupom expirado.', 'cupompromo'),
                'used' => __('Cupom já foi utilizado.', 'cupompromo'),
                'searching' => __('Buscando...', 'cupompromo'),
                'noResults' => __('Nenhum resultado encontrado.', 'cupompromo')
            )
        ));
    }
    
    /**
     * Valida um cupom via AJAX
     */
    public function validate_coupon(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_frontend_nonce')) {
            wp_send_json_error(array(
                'message' => __('Erro de segurança.', 'cupompromo')
            ));
        }
        
        $coupon_code = sanitize_text_field($_POST['coupon_code']);
        
        if (empty($coupon_code)) {
            wp_send_json_error(array(
                'message' => __('Por favor, insira um código de cupom.', 'cupompromo')
            ));
        }
        
        $cupompromo = Cupompromo::get_instance();
        $validation = $cupompromo->validate_coupon_code($coupon_code);
        
        if ($validation['valid']) {
            wp_send_json_success(array(
                'message' => $validation['message'],
                'coupon' => $validation['coupon']
            ));
        } else {
            wp_send_json_error(array(
                'message' => $validation['message']
            ));
        }
    }
    
    /**
     * Busca cupons via AJAX
     */
    public function search_coupons(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_frontend_nonce')) {
            wp_send_json_error(array(
                'message' => __('Erro de segurança.', 'cupompromo')
            ));
        }
        
        $query = sanitize_text_field($_POST['query']);
        $filters = array();
        
        if (isset($_POST['store_id'])) {
            $filters['store_id'] = intval($_POST['store_id']);
        }
        
        if (isset($_POST['coupon_type'])) {
            $filters['coupon_type'] = sanitize_text_field($_POST['coupon_type']);
        }
        
        if (isset($_POST['discount_type'])) {
            $filters['discount_type'] = sanitize_text_field($_POST['discount_type']);
        }
        
        $cupompromo = Cupompromo::get_instance();
        $results = $cupompromo->search_coupons($query, $filters);
        
        wp_send_json_success(array(
            'coupons' => $results
        ));
    }
    
    /**
     * Registra clique em cupom
     */
    public function track_click(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_frontend_nonce')) {
            wp_send_json_error(array(
                'message' => __('Erro de segurança.', 'cupompromo')
            ));
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        
        $cupompromo = Cupompromo::get_instance();
        $result = $cupompromo->log_analytics($coupon_id, 'click');
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Clique registrado com sucesso!', 'cupompromo')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Erro ao registrar clique.', 'cupompromo')
            ));
        }
    }
    
    /**
     * Carrega templates personalizados
     */
    public function load_templates(string $template): string {
        if (is_post_type_archive('cupompromo_store')) {
            $archive_template = CUPOMPROMO_PLUGIN_PATH . 'templates/archive-stores.php';
            if (file_exists($archive_template)) {
                return $archive_template;
            }
        }
        
        if (is_singular('cupompromo_store')) {
            $single_template = CUPOMPROMO_PLUGIN_PATH . 'templates/single-store.php';
            if (file_exists($single_template)) {
                return $single_template;
            }
        }
        
        if (is_post_type_archive('cupompromo_coupon')) {
            $archive_template = CUPOMPROMO_PLUGIN_PATH . 'templates/archive-coupons.php';
            if (file_exists($archive_template)) {
                return $archive_template;
            }
        }
        
        if (is_singular('cupompromo_coupon')) {
            $single_template = CUPOMPROMO_PLUGIN_PATH . 'templates/single-coupon.php';
            if (file_exists($single_template)) {
                return $single_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Adiciona meta tags para SEO
     */
    public function add_meta_tags(): void {
        if (is_post_type_archive('cupompromo_store') || is_post_type_archive('cupompromo_coupon')) {
            echo '<meta name="description" content="' . esc_attr__('Encontre os melhores cupons de desconto e ofertas das principais lojas online.', 'cupompromo') . '">';
            echo '<meta property="og:title" content="' . esc_attr(get_bloginfo('name')) . ' - ' . esc_attr__('Cupons de Desconto', 'cupompromo') . '">';
            echo '<meta property="og:description" content="' . esc_attr__('Encontre os melhores cupons de desconto e ofertas das principais lojas online.', 'cupompromo') . '">';
            echo '<meta property="og:type" content="website">';
        }
    }
    
    /**
     * Renderiza card de loja
     */
    public function render_store_card(object $store): string {
        ob_start();
        ?>
        <div class="cupompromo-store-card" data-store-id="<?php echo esc_attr($store->id); ?>">
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
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderiza card de cupom
     */
    public function render_coupon_card(object $coupon): string {
        ob_start();
        ?>
        <div class="cupompromo-coupon-card" data-coupon-id="<?php echo esc_attr($coupon->id); ?>">
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
                    <?php if ($coupon->verified_date): ?>
                        <span class="verified-date"><?php echo __('Verificado em ', 'cupompromo') . date('d/m/Y', strtotime($coupon->verified_date)); ?></span>
                    <?php endif; ?>
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
    
    /**
     * Renderiza formulário de busca
     */
    public function render_search_form(): string {
        ob_start();
        ?>
        <div class="cupompromo-search-form">
            <form id="cupompromo-search" class="search-form">
                <div class="search-input-group">
                    <input type="text" 
                           id="search-query" 
                           name="query" 
                           placeholder="<?php _e('Buscar cupons...', 'cupompromo'); ?>" 
                           class="search-input">
                    <button type="submit" class="search-submit">
                        <span class="search-icon">🔍</span>
                    </button>
                </div>
                <div class="search-filters">
                    <select name="store_id" class="filter-select">
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
                    <select name="coupon_type" class="filter-select">
                        <option value=""><?php _e('Todos os Tipos', 'cupompromo'); ?></option>
                        <option value="code"><?php _e('Códigos', 'cupompromo'); ?></option>
                        <option value="offer"><?php _e('Ofertas', 'cupompromo'); ?></option>
                    </select>
                </div>
            </form>
            <div id="search-results" class="search-results"></div>
        </div>
        <?php
        return ob_get_clean();
    }
} 