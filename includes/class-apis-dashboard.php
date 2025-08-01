<?php
/**
 * Dashboard de APIs de Afiliados - Cupompromo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

class Cupompromo_APIs_Dashboard {
    
    private $apis = [
        'awin' => 'Awin',
        'admitad' => 'Admitad', 
        'afilio' => 'Afilio',
        'socialsoul' => 'SocialSoul'
    ];
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cupompromo_test_api', [$this, 'test_api_connection']);
        add_action('wp_ajax_cupompromo_sync_advertisers', [$this, 'sync_advertisers']);
        add_action('wp_ajax_cupompromo_get_api_stats', [$this, 'get_api_statistics']);
    }
    
    /**
     * Adicionar menu administrativo
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cupompromo-dashboard',
            __('APIs de Afiliados', 'cupompromo'),
            __('APIs de Afiliados', 'cupompromo'),
            'manage_options',
            'cupompromo-apis',
            [$this, 'render_dashboard_page']
        );
    }
    
    /**
     * Carregar scripts e estilos
     */
    public function enqueue_scripts($hook) {
        if ('cupompromo_page_cupompromo-apis' !== $hook) {
            return;
        }
        
        wp_enqueue_script(
            'cupompromo-apis-dashboard',
            plugin_dir_url(__FILE__) . '../assets/js/admin/apis-dashboard.js',
            ['jquery', 'wp-util'],
            CUPOMPROMO_VERSION,
            true
        );
        
        wp_enqueue_style(
            'cupompromo-apis-dashboard',
            plugin_dir_url(__FILE__) . '../assets/css/admin/apis-dashboard.css',
            [],
            CUPOMPROMO_VERSION
        );
        
        wp_localize_script('cupompromo-apis-dashboard', 'cupompromoAPIs', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cupompromo_apis_nonce'),
            'strings' => [
                'testing' => __('Testando conexão...', 'cupompromo'),
                'success' => __('Conexão bem-sucedida!', 'cupompromo'),
                'error' => __('Erro na conexão', 'cupompromo'),
                'syncing' => __('Sincronizando...', 'cupompromo'),
                'synced' => __('Sincronização concluída!', 'cupompromo')
            ]
        ]);
    }
    
    /**
     * Renderizar página do dashboard
     */
    public function render_dashboard_page() {
        ?>
        <div class="wrap cupompromo-apis-dashboard">
            <h1><?php _e('Dashboard de APIs de Afiliados', 'cupompromo'); ?></h1>
            
            <!-- Status Geral das APIs -->
            <div class="cupompromo-apis-overview">
                <h2><?php _e('Visão Geral', 'cupompromo'); ?></h2>
                <div class="cupompromo-apis-grid">
                    <?php foreach ($this->apis as $api_key => $api_name): ?>
                        <div class="cupompromo-api-card" data-api="<?php echo esc_attr($api_key); ?>">
                            <div class="api-header">
                                <h3><?php echo esc_html($api_name); ?></h3>
                                <span class="api-status" id="status-<?php echo esc_attr($api_key); ?>">
                                    <span class="status-indicator"></span>
                                    <?php _e('Verificando...', 'cupompromo'); ?>
                                </span>
                            </div>
                            <div class="api-stats">
                                <div class="stat-item">
                                    <span class="stat-label"><?php _e('Lojas', 'cupompromo'); ?></span>
                                    <span class="stat-value" id="stores-<?php echo esc_attr($api_key); ?>">-</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label"><?php _e('Cupons', 'cupompromo'); ?></span>
                                    <span class="stat-value" id="coupons-<?php echo esc_attr($api_key); ?>">-</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label"><?php _e('Última Sinc.', 'cupompromo'); ?></span>
                                    <span class="stat-value" id="last-sync-<?php echo esc_attr($api_key); ?>">-</span>
                                </div>
                            </div>
                            <div class="api-actions">
                                <button class="button button-primary test-api" data-api="<?php echo esc_attr($api_key); ?>">
                                    <?php _e('Testar Conexão', 'cupompromo'); ?>
                                </button>
                                <button class="button button-secondary sync-api" data-api="<?php echo esc_attr($api_key); ?>">
                                    <?php _e('Sincronizar', 'cupompromo'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Configurações das APIs -->
            <div class="cupompromo-apis-settings">
                <h2><?php _e('Configurações', 'cupompromo'); ?></h2>
                <form method="post" action="options.php">
                    <?php settings_fields('cupompromo_apis_settings'); ?>
                    
                    <!-- Awin API -->
                    <div class="api-settings-section">
                        <h3><?php _e('Awin API', 'cupompromo'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Access Token', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_awin_token" 
                                           value="<?php echo esc_attr(get_option('cupompromo_awin_token')); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Token de acesso da API Awin', 'cupompromo'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Publisher ID', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_awin_publisher" 
                                           value="<?php echo esc_attr(get_option('cupompromo_awin_publisher')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Admitad API -->
                    <div class="api-settings-section">
                        <h3><?php _e('Admitad API', 'cupompromo'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Client ID', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_admitad_client_id" 
                                           value="<?php echo esc_attr(get_option('cupompromo_admitad_client_id')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Client Secret', 'cupompromo'); ?></th>
                                <td>
                                    <input type="password" name="cupompromo_admitad_client_secret" 
                                           value="<?php echo esc_attr(get_option('cupompromo_admitad_client_secret')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Website ID', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_admitad_website_id" 
                                           value="<?php echo esc_attr(get_option('cupompromo_admitad_website_id')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Afilio API -->
                    <div class="api-settings-section">
                        <h3><?php _e('Afilio API', 'cupompromo'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Affiliate ID', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_afilio_affid" 
                                           value="<?php echo esc_attr(get_option('cupompromo_afilio_affid')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Token', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_afilio_token" 
                                           value="<?php echo esc_attr(get_option('cupompromo_afilio_token')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- SocialSoul API -->
                    <div class="api-settings-section">
                        <h3><?php _e('SocialSoul API', 'cupompromo'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Publisher ID', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_socialsoul_publisher_id" 
                                           value="<?php echo esc_attr(get_option('cupompromo_socialsoul_publisher_id')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('App Token', 'cupompromo'); ?></th>
                                <td>
                                    <input type="text" name="cupompromo_socialsoul_app_token" 
                                           value="<?php echo esc_attr(get_option('cupompromo_socialsoul_app_token')); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php submit_button(__('Salvar Configurações', 'cupompromo')); ?>
                </form>
            </div>
            
            <!-- Logs e Monitoramento -->
            <div class="cupompromo-apis-logs">
                <h2><?php _e('Logs e Monitoramento', 'cupompromo'); ?></h2>
                <div class="log-container">
                    <div class="log-filters">
                        <select id="log-api-filter">
                            <option value=""><?php _e('Todas as APIs', 'cupompromo'); ?></option>
                            <?php foreach ($this->apis as $api_key => $api_name): ?>
                                <option value="<?php echo esc_attr($api_key); ?>"><?php echo esc_html($api_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="log-level-filter">
                            <option value=""><?php _e('Todos os níveis', 'cupompromo'); ?></option>
                            <option value="error"><?php _e('Erro', 'cupompromo'); ?></option>
                            <option value="warning"><?php _e('Aviso', 'cupompromo'); ?></option>
                            <option value="info"><?php _e('Info', 'cupompromo'); ?></option>
                        </select>
                        <button class="button" id="refresh-logs"><?php _e('Atualizar', 'cupompromo'); ?></button>
                    </div>
                    <div class="log-content" id="api-logs">
                        <!-- Logs serão carregados via AJAX -->
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Testar conexão com API
     */
    public function test_api_connection() {
        check_ajax_referer('cupompromo_apis_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada.', 'cupompromo')]);
        }
        
        $api_name = sanitize_text_field($_POST['api']);
        
        try {
            $api_manager = new Cupompromo_API_Manager();
            $result = $api_manager->test_connection($api_name);
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => sprintf(__('Conexão com %s bem-sucedida!', 'cupompromo'), $this->apis[$api_name]),
                    'data' => $result['data']
                ]);
            } else {
                wp_send_json_error([
                    'message' => sprintf(__('Erro na conexão com %s: %s', 'cupompromo'), $this->apis[$api_name], $result['error'])
                ]);
            }
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => sprintf(__('Exceção na conexão com %s: %s', 'cupompromo'), $this->apis[$api_name], $e->getMessage())
            ]);
        }
    }
    
    /**
     * Sincronizar advertisers
     */
    public function sync_advertisers() {
        check_ajax_referer('cupompromo_apis_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada.', 'cupompromo')]);
        }
        
        $api_name = sanitize_text_field($_POST['api']);
        
        try {
            $api_manager = new Cupompromo_API_Manager();
            $result = $api_manager->sync_advertisers($api_name);
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => sprintf(__('Sincronização com %s concluída! %d lojas importadas.', 'cupompromo'), 
                        $this->apis[$api_name], $result['count']),
                    'count' => $result['count']
                ]);
            } else {
                wp_send_json_error([
                    'message' => sprintf(__('Erro na sincronização com %s: %s', 'cupompromo'), 
                        $this->apis[$api_name], $result['error'])
                ]);
            }
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => sprintf(__('Exceção na sincronização com %s: %s', 'cupompromo'), 
                    $this->apis[$api_name], $e->getMessage())
            ]);
        }
    }
    
    /**
     * Obter estatísticas das APIs
     */
    public function get_api_statistics() {
        check_ajax_referer('cupompromo_apis_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada.', 'cupompromo')]);
        }
        
        $stats = [];
        
        foreach ($this->apis as $api_key => $api_name) {
            $stats[$api_key] = [
                'stores' => $this->get_store_count($api_key),
                'coupons' => $this->get_coupon_count($api_key),
                'last_sync' => $this->get_last_sync($api_key),
                'status' => $this->get_api_status($api_key)
            ];
        }
        
        wp_send_json_success($stats);
    }
    
    /**
     * Obter contagem de lojas
     */
    private function get_store_count($api_name) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'cupompromo_store' 
             AND post_status = 'publish'
             AND meta_key = '_api_source' 
             AND meta_value = %s",
            $api_name
        ));
        
        return intval($count);
    }
    
    /**
     * Obter contagem de cupons
     */
    private function get_coupon_count($api_name) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'cupompromo_coupon' 
             AND post_status = 'publish'
             AND meta_key = '_api_source' 
             AND meta_value = %s",
            $api_name
        ));
        
        return intval($count);
    }
    
    /**
     * Obter última sincronização
     */
    private function get_last_sync($api_name) {
        $last_sync = get_option("cupompromo_{$api_name}_last_sync");
        return $last_sync ? date('d/m/Y H:i', strtotime($last_sync)) : '-';
    }
    
    /**
     * Obter status da API
     */
    private function get_api_status($api_name) {
        $status = get_option("cupompromo_{$api_name}_status", 'unknown');
        return $status;
    }
} 