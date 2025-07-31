<?php
/**
 * Classe administrativa do plugin Cupompromo
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
 * Classe Cupompromo_Admin
 */
class Cupompromo_Admin {
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_cupompromo_save_store', array($this, 'save_store'));
        add_action('wp_ajax_cupompromo_save_coupon', array($this, 'save_coupon'));
        add_action('wp_ajax_cupompromo_delete_store', array($this, 'delete_store'));
        add_action('wp_ajax_cupompromo_delete_coupon', array($this, 'delete_coupon'));
        add_action('admin_init', array($this, 'init_settings'));
    }
    
    /**
     * Adiciona o menu administrativo
     */
    public function add_admin_menu(): void {
        add_menu_page(
            __('Cupompromo', 'cupompromo'),
            __('Cupompromo', 'cupompromo'),
            'manage_options',
            'cupompromo',
            array($this, 'dashboard_page'),
            'dashicons-tickets-alt',
            30
        );
        
        add_submenu_page(
            'cupompromo',
            __('Dashboard', 'cupompromo'),
            __('Dashboard', 'cupompromo'),
            'manage_options',
            'cupompromo',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'cupompromo',
            __('Lojas', 'cupompromo'),
            __('Lojas', 'cupompromo'),
            'manage_options',
            'cupompromo-stores',
            array($this, 'stores_page')
        );
        
        add_submenu_page(
            'cupompromo',
            __('Cupons', 'cupompromo'),
            __('Cupons', 'cupompromo'),
            'manage_options',
            'cupompromo-coupons',
            array($this, 'coupons_page')
        );
        
        add_submenu_page(
            'cupompromo',
            __('Relatórios', 'cupompromo'),
            __('Relatórios', 'cupompromo'),
            'manage_options',
            'cupompromo-reports',
            array($this, 'reports_page')
        );
        
        add_submenu_page(
            'cupompromo',
            __('Configurações', 'cupompromo'),
            __('Configurações', 'cupompromo'),
            'manage_options',
            'cupompromo-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Carrega scripts e estilos do admin
     */
    public function enqueue_admin_scripts(string $hook): void {
        if (strpos($hook, 'cupompromo') === false) {
            return;
        }
        
        wp_enqueue_style(
            'cupompromo-admin',
            CUPOMPROMO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CUPOMPROMO_VERSION
        );
        
        wp_enqueue_script(
            'cupompromo-admin',
            CUPOMPROMO_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            CUPOMPROMO_VERSION,
            true
        );
        
        wp_localize_script('cupompromo-admin', 'cupompromoAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cupompromo_nonce'),
            'strings' => array(
                'confirmDelete' => __('Tem certeza que deseja excluir este item?', 'cupompromo'),
                'saving' => __('Salvando...', 'cupompromo'),
                'saved' => __('Salvo com sucesso!', 'cupompromo'),
                'error' => __('Erro ao salvar.', 'cupompromo')
            )
        ));
    }
    
    /**
     * Inicializa configurações
     */
    public function init_settings(): void {
        register_setting('cupompromo_settings', 'cupompromo_settings');
        
        add_settings_section(
            'cupompromo_general',
            __('Configurações Gerais', 'cupompromo'),
            array($this, 'settings_section_callback'),
            'cupompromo_settings'
        );
        
        add_settings_field(
            'currency_symbol',
            __('Símbolo da Moeda', 'cupompromo'),
            array($this, 'currency_symbol_callback'),
            'cupompromo_settings',
            'cupompromo_general'
        );
        
        add_settings_field(
            'default_commission',
            __('Comissão Padrão (%)', 'cupompromo'),
            array($this, 'default_commission_callback'),
            'cupompromo_settings',
            'cupompromo_general'
        );
    }
    
    /**
     * Página do dashboard
     */
    public function dashboard_page(): void {
        $cupompromo = Cupompromo::get_instance();
        $stats = $cupompromo->get_stats();
        
        include CUPOMPROMO_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    /**
     * Página de lojas
     */
    public function stores_page(): void {
        include CUPOMPROMO_PLUGIN_PATH . 'admin/views/stores.php';
    }
    
    /**
     * Página de cupons
     */
    public function coupons_page(): void {
        include CUPOMPROMO_PLUGIN_PATH . 'admin/views/coupons.php';
    }
    
    /**
     * Página de relatórios
     */
    public function reports_page(): void {
        include CUPOMPROMO_PLUGIN_PATH . 'admin/views/reports.php';
    }
    
    /**
     * Página de configurações
     */
    public function settings_page(): void {
        include CUPOMPROMO_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    /**
     * Callback da seção de configurações
     */
    public function settings_section_callback(): void {
        echo '<p>' . __('Configure as opções gerais do plugin Cupompromo.', 'cupompromo') . '</p>';
    }
    
    /**
     * Callback do campo símbolo da moeda
     */
    public function currency_symbol_callback(): void {
        $settings = get_option('cupompromo_settings', array());
        $value = isset($settings['currency_symbol']) ? $settings['currency_symbol'] : 'R$';
        
        echo '<input type="text" name="cupompromo_settings[currency_symbol]" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Símbolo da moeda para exibição (ex: R$, $, €)', 'cupompromo') . '</p>';
    }
    
    /**
     * Callback do campo comissão padrão
     */
    public function default_commission_callback(): void {
        $settings = get_option('cupompromo_settings', array());
        $value = isset($settings['default_commission']) ? $settings['default_commission'] : '5.00';
        
        echo '<input type="number" name="cupompromo_settings[default_commission]" value="' . esc_attr($value) . '" class="small-text" min="0" max="100" step="0.01"> %';
        echo '<p class="description">' . __('Comissão padrão para cupons', 'cupompromo') . '</p>';
    }
    
    /**
     * Salva uma loja via AJAX
     */
    public function save_store(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_nonce')) {
            wp_die(__('Erro de segurança.', 'cupompromo'));
        }
        
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissão negada.', 'cupompromo'));
        }
        
        $store_data = array(
            'name' => sanitize_text_field($_POST['name']),
            'slug' => sanitize_title($_POST['name']),
            'logo_url' => esc_url_raw($_POST['logo_url']),
            'affiliate_base_url' => esc_url_raw($_POST['affiliate_base_url']),
            'default_commission' => floatval($_POST['default_commission']),
            'store_description' => sanitize_textarea_field($_POST['store_description']),
            'store_website' => esc_url_raw($_POST['store_website']),
            'featured_store' => isset($_POST['featured_store']) ? 1 : 0,
            'status' => sanitize_text_field($_POST['status'])
        );
        
        global $wpdb;
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        if (isset($_POST['store_id']) && !empty($_POST['store_id'])) {
            // Atualizar loja existente
            $result = $wpdb->update(
                $table_stores,
                $store_data,
                array('id' => intval($_POST['store_id'])),
                array('%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s'),
                array('%d')
            );
        } else {
            // Inserir nova loja
            $result = $wpdb->insert(
                $table_stores,
                $store_data,
                array('%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s')
            );
        }
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Loja salva com sucesso!', 'cupompromo')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Erro ao salvar loja.', 'cupompromo')
            ));
        }
    }
    
    /**
     * Salva um cupom via AJAX
     */
    public function save_coupon(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_nonce')) {
            wp_die(__('Erro de segurança.', 'cupompromo'));
        }
        
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissão negada.', 'cupompromo'));
        }
        
        $coupon_data = array(
            'store_id' => intval($_POST['store_id']),
            'title' => sanitize_text_field($_POST['title']),
            'coupon_type' => sanitize_text_field($_POST['coupon_type']),
            'coupon_code' => sanitize_text_field($_POST['coupon_code']),
            'affiliate_url' => esc_url_raw($_POST['affiliate_url']),
            'discount_value' => sanitize_text_field($_POST['discount_value']),
            'discount_type' => sanitize_text_field($_POST['discount_type']),
            'expiry_date' => sanitize_text_field($_POST['expiry_date']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        global $wpdb;
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        
        if (isset($_POST['coupon_id']) && !empty($_POST['coupon_id'])) {
            // Atualizar cupom existente
            $result = $wpdb->update(
                $table_coupons,
                $coupon_data,
                array('id' => intval($_POST['coupon_id'])),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Inserir novo cupom
            $result = $wpdb->insert(
                $table_coupons,
                $coupon_data,
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Cupom salvo com sucesso!', 'cupompromo')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Erro ao salvar cupom.', 'cupompromo')
            ));
        }
    }
    
    /**
     * Exclui uma loja via AJAX
     */
    public function delete_store(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_nonce')) {
            wp_die(__('Erro de segurança.', 'cupompromo'));
        }
        
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissão negada.', 'cupompromo'));
        }
        
        $store_id = intval($_POST['store_id']);
        
        global $wpdb;
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $result = $wpdb->delete(
            $table_stores,
            array('id' => $store_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Loja excluída com sucesso!', 'cupompromo')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Erro ao excluir loja.', 'cupompromo')
            ));
        }
    }
    
    /**
     * Exclui um cupom via AJAX
     */
    public function delete_coupon(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_nonce')) {
            wp_die(__('Erro de segurança.', 'cupompromo'));
        }
        
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissão negada.', 'cupompromo'));
        }
        
        $coupon_id = intval($_POST['coupon_id']);
        
        global $wpdb;
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        
        $result = $wpdb->delete(
            $table_coupons,
            array('id' => $coupon_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Cupom excluído com sucesso!', 'cupompromo')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Erro ao excluir cupom.', 'cupompromo')
            ));
        }
    }
} 