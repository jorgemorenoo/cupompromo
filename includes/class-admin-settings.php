<?php
/**
 * Classe Cupompromo_Admin_Settings
 * 
 * Responsável pelo painel administrativo do plugin Cupompromo,
 * incluindo menus, configurações, dashboard e gerenciamento de cupons.
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
 * Classe Cupompromo_Admin_Settings
 */
class Cupompromo_Admin_Settings {
    
    /**
     * Instância única da classe
     */
    private static $instance = null;
    
    /**
     * Páginas do admin
     */
    private $admin_pages = array();
    
    /**
     * Construtor da classe
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Retorna a instância única da classe
     */
    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializa hooks
     */
    private function init_hooks(): void {
        add_action('admin_menu', array($this, 'cupompromo_add_admin_menu'));
        add_action('admin_init', array($this, 'cupompromo_init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'cupompromo_enqueue_admin_scripts'));
        add_action('wp_ajax_cupompromo_sync_awin', array($this, 'cupompromo_ajax_sync_awin'));
        add_action('wp_ajax_cupompromo_get_stats', array($this, 'cupompromo_ajax_get_stats'));
        add_action('admin_notices', array($this, 'cupompromo_admin_notices'));
    }
    
    /**
     * Adiciona menu administrativo
     */
    public function cupompromo_add_admin_menu(): void {
        // Menu principal
        add_menu_page(
            __('Cupompromo', 'cupompromo'),
            __('Cupompromo', 'cupompromo'),
            'manage_options',
            'cupompromo-dashboard',
            array($this, 'cupompromo_dashboard_page'),
            'dashicons-tickets-alt',
            30
        );
        
        // Submenus
        add_submenu_page(
            'cupompromo-dashboard',
            __('Dashboard', 'cupompromo'),
            __('Dashboard', 'cupompromo'),
            'manage_options',
            'cupompromo-dashboard',
            array($this, 'cupompromo_dashboard_page')
        );
        
        add_submenu_page(
            'cupompromo-dashboard',
            __('Cupons', 'cupompromo'),
            __('Cupons', 'cupompromo'),
            'manage_options',
            'cupompromo-coupons',
            array($this, 'cupompromo_coupons_page')
        );
        
        add_submenu_page(
            'cupompromo-dashboard',
            __('Lojas', 'cupompromo'),
            __('Lojas', 'cupompromo'),
            'manage_options',
            'cupompromo-stores',
            array($this, 'cupompromo_stores_page')
        );
        
        add_submenu_page(
            'cupompromo-dashboard',
            __('Configurações', 'cupompromo'),
            __('Configurações', 'cupompromo'),
            'manage_options',
            'cupompromo-settings',
            array($this, 'cupompromo_settings_page')
        );
        
        add_submenu_page(
            'cupompromo-dashboard',
            __('Analytics', 'cupompromo'),
            __('Analytics', 'cupompromo'),
            'manage_options',
            'cupompromo-analytics',
            array($this, 'cupompromo_analytics_page')
        );
    }
    
    /**
     * Inicializa configurações
     */
    public function cupompromo_init_settings(): void {
        // Registra configurações
        register_setting('cupompromo_settings', 'cupompromo_awin_api_key');
        register_setting('cupompromo_settings', 'cupompromo_awin_publisher_id');
        register_setting('cupompromo_settings', 'cupompromo_awin_region');
        register_setting('cupompromo_settings', 'cupompromo_currency');
        register_setting('cupompromo_settings', 'cupompromo_primary_color');
        register_setting('cupompromo_settings', 'cupompromo_secondary_color');
        register_setting('cupompromo_settings', 'cupompromo_enable_notifications');
        register_setting('cupompromo_settings', 'cupompromo_auto_sync_interval');
        
        // Seções de configuração
        add_settings_section(
            'cupompromo_api_section',
            __('Configurações da API Awin', 'cupompromo'),
            array($this, 'cupompromo_api_section_callback'),
            'cupompromo_settings'
        );
        
        add_settings_section(
            'cupompromo_general_section',
            __('Configurações Gerais', 'cupompromo'),
            array($this, 'cupompromo_general_section_callback'),
            'cupompromo_settings'
        );
        
        add_settings_section(
            'cupompromo_visual_section',
            __('Configurações Visuais', 'cupompromo'),
            array($this, 'cupompromo_visual_section_callback'),
            'cupompromo_settings'
        );
        
        // Campos de configuração
        add_settings_field(
            'cupompromo_awin_api_key',
            __('API Key', 'cupompromo'),
            array($this, 'cupompromo_api_key_field'),
            'cupompromo_settings',
            'cupompromo_api_section'
        );
        
        add_settings_field(
            'cupompromo_awin_publisher_id',
            __('Publisher ID', 'cupompromo'),
            array($this, 'cupompromo_publisher_id_field'),
            'cupompromo_settings',
            'cupompromo_api_section'
        );
        
        add_settings_field(
            'cupompromo_awin_region',
            __('Região', 'cupompromo'),
            array($this, 'cupompromo_region_field'),
            'cupompromo_settings',
            'cupompromo_api_section'
        );
        
        add_settings_field(
            'cupompromo_currency',
            __('Moeda', 'cupompromo'),
            array($this, 'cupompromo_currency_field'),
            'cupompromo_settings',
            'cupompromo_general_section'
        );
        
        add_settings_field(
            'cupompromo_primary_color',
            __('Cor Primária', 'cupompromo'),
            array($this, 'cupompromo_primary_color_field'),
            'cupompromo_settings',
            'cupompromo_visual_section'
        );
        
        add_settings_field(
            'cupompromo_secondary_color',
            __('Cor Secundária', 'cupompromo'),
            array($this, 'cupompromo_secondary_color_field'),
            'cupompromo_settings',
            'cupompromo_visual_section'
        );
        
        add_settings_field(
            'cupompromo_enable_notifications',
            __('Notificações', 'cupompromo'),
            array($this, 'cupompromo_notifications_field'),
            'cupompromo_settings',
            'cupompromo_general_section'
        );
        
        add_settings_field(
            'cupompromo_auto_sync_interval',
            __('Intervalo de Sincronização', 'cupompromo'),
            array($this, 'cupompromo_sync_interval_field'),
            'cupompromo_settings',
            'cupompromo_general_section'
        );
    }
    
    /**
     * Carrega scripts do admin
     */
    public function cupompromo_enqueue_admin_scripts(string $hook): void {
        if (strpos($hook, 'cupompromo') === false) {
            return;
        }
        
        wp_enqueue_script(
            'cupompromo-admin',
            plugin_dir_url(__FILE__) . '../assets/js/admin.js',
            array('jquery', 'wp-util'),
            CUPOMPROMO_VERSION,
            true
        );
        
        wp_enqueue_style(
            'cupompromo-admin',
            plugin_dir_url(__FILE__) . '../assets/css/admin.css',
            array(),
            CUPOMPROMO_VERSION
        );
        
        wp_localize_script('cupompromo-admin', 'cupompromo_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cupompromo_admin_nonce'),
            'strings' => array(
                'syncing' => __('Sincronizando...', 'cupompromo'),
                'sync_complete' => __('Sincronização concluída!', 'cupompromo'),
                'sync_error' => __('Erro na sincronização', 'cupompromo'),
                'confirm_delete' => __('Tem certeza que deseja excluir?', 'cupompromo')
            )
        ));
    }
    
    /**
     * Página do Dashboard
     */
    public function cupompromo_dashboard_page(): void {
        $coupon_manager = Cupompromo_Coupon_Manager::get_instance();
        $stats = $coupon_manager->cupompromo_get_coupon_stats();
        ?>
        <div class="wrap">
            <h1><?php _e('Dashboard Cupompromo', 'cupompromo'); ?></h1>
            
            <!-- Cards de Estatísticas -->
            <div class="cupompromo-stats-grid">
                <div class="cupompromo-stat-card">
                    <div class="stat-icon">🎫</div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_coupons']); ?></h3>
                        <p><?php _e('Total de Cupons', 'cupompromo'); ?></p>
                    </div>
                </div>
                
                <div class="cupompromo-stat-card">
                    <div class="stat-icon">🏪</div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_stores']); ?></h3>
                        <p><?php _e('Lojas Cadastradas', 'cupompromo'); ?></p>
                    </div>
                </div>
                
                <div class="cupompromo-stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_clicks']); ?></h3>
                        <p><?php _e('Total de Cliques', 'cupompromo'); ?></p>
                    </div>
                </div>
                
                <div class="cupompromo-stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_usage']); ?></h3>
                        <p><?php _e('Códigos Copiados', 'cupompromo'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Ações Rápidas -->
            <div class="cupompromo-quick-actions">
                <h2><?php _e('Ações Rápidas', 'cupompromo'); ?></h2>
                <div class="action-buttons">
                    <button class="button button-primary" id="cupompromo-sync-awin">
                        <?php _e('Sincronizar com Awin', 'cupompromo'); ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=cupompromo-coupons'); ?>" class="button">
                        <?php _e('Gerenciar Cupons', 'cupompromo'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=cupompromo-settings'); ?>" class="button">
                        <?php _e('Configurações', 'cupompromo'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Status da API -->
            <div class="cupompromo-api-status">
                <h2><?php _e('Status da API Awin', 'cupompromo'); ?></h2>
                <div class="status-info">
                    <p><strong><?php _e('Configurada:', 'cupompromo'); ?></strong> 
                        <?php echo $stats['awin_configured'] ? __('Sim', 'cupompromo') : __('Não', 'cupompromo'); ?>
                    </p>
                    <p><strong><?php _e('Última Sincronização:', 'cupompromo'); ?></strong> 
                        <?php echo $stats['last_sync'] ? date('d/m/Y H:i', strtotime($stats['last_sync'])) : __('Nunca', 'cupompromo'); ?>
                    </p>
                </div>
            </div>
            
            <!-- Cupons Recentes -->
            <div class="cupompromo-recent-coupons">
                <h2><?php _e('Cupons Recentes', 'cupompromo'); ?></h2>
                <?php $this->cupompromo_display_recent_coupons(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Página de Cupons
     */
    public function cupompromo_coupons_page(): void {
        $coupon_manager = Cupompromo_Coupon_Manager::get_instance();
        
        // Filtros
        $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
        $coupon_type = isset($_GET['coupon_type']) ? sanitize_text_field($_GET['coupon_type']) : '';
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'publish';
        
        $coupons = $coupon_manager->cupompromo_get_coupons(array(
            'store_id' => $store_id,
            'coupon_type' => $coupon_type,
            'status' => $status,
            'limit' => 50
        ));
        ?>
        <div class="wrap">
            <h1><?php _e('Gerenciar Cupons', 'cupompromo'); ?></h1>
            
            <!-- Filtros -->
            <div class="cupompromo-filters">
                <form method="get">
                    <input type="hidden" name="page" value="cupompromo-coupons">
                    
                    <select name="store_id">
                        <option value=""><?php _e('Todas as Lojas', 'cupompromo'); ?></option>
                        <?php
                        $stores = get_posts(array(
                            'post_type' => 'cupompromo_store',
                            'posts_per_page' => -1,
                            'post_status' => 'publish'
                        ));
                        foreach ($stores as $store) {
                            $selected = $store_id == $store->ID ? 'selected' : '';
                            echo '<option value="' . $store->ID . '" ' . $selected . '>' . esc_html($store->post_title) . '</option>';
                        }
                        ?>
                    </select>
                    
                    <select name="coupon_type">
                        <option value=""><?php _e('Todos os Tipos', 'cupompromo'); ?></option>
                        <option value="code" <?php selected($coupon_type, 'code'); ?>><?php _e('Códigos', 'cupompromo'); ?></option>
                        <option value="offer" <?php selected($coupon_type, 'offer'); ?>><?php _e('Ofertas', 'cupompromo'); ?></option>
                    </select>
                    
                    <select name="status">
                        <option value="publish" <?php selected($status, 'publish'); ?>><?php _e('Ativos', 'cupompromo'); ?></option>
                        <option value="expired" <?php selected($status, 'expired'); ?>><?php _e('Expirados', 'cupompromo'); ?></option>
                        <option value="draft" <?php selected($status, 'draft'); ?>><?php _e('Rascunhos', 'cupompromo'); ?></option>
                    </select>
                    
                    <button type="submit" class="button"><?php _e('Filtrar', 'cupompromo'); ?></button>
                </form>
            </div>
            
            <!-- Lista de Cupons -->
            <div class="cupompromo-coupons-list">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Cupom', 'cupompromo'); ?></th>
                            <th><?php _e('Loja', 'cupompromo'); ?></th>
                            <th><?php _e('Tipo', 'cupompromo'); ?></th>
                            <th><?php _e('Desconto', 'cupompromo'); ?></th>
                            <th><?php _e('Cliques', 'cupompromo'); ?></th>
                            <th><?php _e('Status', 'cupompromo'); ?></th>
                            <th><?php _e('Ações', 'cupompromo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($coupon->title); ?></strong>
                                <?php if (!empty($coupon->coupon_code)): ?>
                                    <br><code><?php echo esc_html($coupon->coupon_code); ?></code>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($coupon->store_name ?? ''); ?></td>
                            <td>
                                <span class="badge badge-<?php echo esc_attr($coupon->coupon_type); ?>">
                                    <?php echo $coupon->coupon_type === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo'); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($coupon->formatted_discount); ?></td>
                            <td><?php echo number_format($coupon->click_count); ?></td>
                            <td>
                                <span class="status-<?php echo esc_attr($coupon->status); ?>">
                                    <?php echo esc_html($coupon->status); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo get_edit_post_link($coupon->id); ?>" class="button button-small">
                                    <?php _e('Editar', 'cupompromo'); ?>
                                </a>
                                <a href="<?php echo get_permalink($coupon->id); ?>" class="button button-small" target="_blank">
                                    <?php _e('Ver', 'cupompromo'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Página de Lojas
     */
    public function cupompromo_stores_page(): void {
        $stores = get_posts(array(
            'post_type' => 'cupompromo_store',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        ?>
        <div class="wrap">
            <h1><?php _e('Gerenciar Lojas', 'cupompromo'); ?></h1>
            
            <div class="cupompromo-stores-grid">
                <?php foreach ($stores as $store): ?>
                <div class="cupompromo-store-card">
                    <div class="store-header">
                        <?php
                        $logo_url = get_post_meta($store->ID, '_store_logo', true);
                        if ($logo_url) {
                            echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($store->post_title) . '" class="store-logo">';
                        }
                        ?>
                        <h3><?php echo esc_html($store->post_title); ?></h3>
                    </div>
                    
                    <div class="store-content">
                        <p><?php echo wp_trim_words($store->post_content, 20); ?></p>
                        
                        <?php
                        $website = get_post_meta($store->ID, '_store_website', true);
                        if ($website) {
                            echo '<p><a href="' . esc_url($website) . '" target="_blank">' . __('Visitar Site', 'cupompromo') . '</a></p>';
                        }
                        ?>
                    </div>
                    
                    <div class="store-actions">
                        <a href="<?php echo get_edit_post_link($store->ID); ?>" class="button button-small">
                            <?php _e('Editar', 'cupompromo'); ?>
                        </a>
                        <a href="<?php echo get_permalink($store->ID); ?>" class="button button-small" target="_blank">
                            <?php _e('Ver', 'cupompromo'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Página de Configurações
     */
    public function cupompromo_settings_page(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Configurações Cupompromo', 'cupompromo'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('cupompromo_settings');
                do_settings_sections('cupompromo_settings');
                submit_button();
                ?>
            </form>
            
            <!-- Teste de Conexão -->
            <div class="cupompromo-api-test">
                <h2><?php _e('Teste de Conexão Awin', 'cupompromo'); ?></h2>
                <button type="button" class="button" id="cupompromo-test-api">
                    <?php _e('Testar Conexão', 'cupompromo'); ?>
                </button>
                <div id="cupompromo-test-result"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Página de Analytics
     */
    public function cupompromo_analytics_page(): void {
        $coupon_manager = Cupompromo_Coupon_Manager::get_instance();
        $stats = $coupon_manager->cupompromo_get_coupon_stats();
        ?>
        <div class="wrap">
            <h1><?php _e('Analytics Cupompromo', 'cupompromo'); ?></h1>
            
            <!-- Gráficos -->
            <div class="cupompromo-analytics-charts">
                <div class="chart-container">
                    <h3><?php _e('Cupons por Loja', 'cupompromo'); ?></h3>
                    <canvas id="coupons-by-store-chart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3><?php _e('Cliques por Mês', 'cupompromo'); ?></h3>
                    <canvas id="clicks-by-month-chart"></canvas>
                </div>
            </div>
            
            <!-- Relatórios -->
            <div class="cupompromo-reports">
                <h2><?php _e('Relatórios', 'cupompromo'); ?></h2>
                <div class="report-buttons">
                    <button class="button" id="cupompromo-export-coupons">
                        <?php _e('Exportar Cupons', 'cupompromo'); ?>
                    </button>
                    <button class="button" id="cupompromo-export-stats">
                        <?php _e('Exportar Estatísticas', 'cupompromo'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Callbacks dos campos de configuração
     */
    public function cupompromo_api_section_callback(): void {
        echo '<p>' . __('Configure suas credenciais da API Awin para sincronização automática de cupons.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_general_section_callback(): void {
        echo '<p>' . __('Configurações gerais do plugin.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_visual_section_callback(): void {
        echo '<p>' . __('Personalize a aparência do plugin.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_api_key_field(): void {
        $value = get_option('cupompromo_awin_api_key', '');
        echo '<input type="text" name="cupompromo_awin_api_key" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Sua chave de API da Awin.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_publisher_id_field(): void {
        $value = get_option('cupompromo_awin_publisher_id', '');
        echo '<input type="text" name="cupompromo_awin_publisher_id" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Seu Publisher ID da Awin.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_region_field(): void {
        $value = get_option('cupompromo_awin_region', 'BR');
        $regions = array(
            'BR' => __('Brasil', 'cupompromo'),
            'US' => __('Estados Unidos', 'cupompromo'),
            'UK' => __('Reino Unido', 'cupompromo'),
            'DE' => __('Alemanha', 'cupompromo')
        );
        
        echo '<select name="cupompromo_awin_region">';
        foreach ($regions as $code => $name) {
            echo '<option value="' . $code . '" ' . selected($value, $code, false) . '>' . $name . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Região da sua conta Awin.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_currency_field(): void {
        $value = get_option('cupompromo_currency', 'BRL');
        $currencies = array(
            'BRL' => 'Real (R$)',
            'USD' => 'Dólar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'Libra (£)'
        );
        
        echo '<select name="cupompromo_currency">';
        foreach ($currencies as $code => $name) {
            echo '<option value="' . $code . '" ' . selected($value, $code, false) . '>' . $name . '</option>';
        }
        echo '</select>';
    }
    
    public function cupompromo_primary_color_field(): void {
        $value = get_option('cupompromo_primary_color', '#622599');
        echo '<input type="color" name="cupompromo_primary_color" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor principal do plugin.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_secondary_color_field(): void {
        $value = get_option('cupompromo_secondary_color', '#8BC53F');
        echo '<input type="color" name="cupompromo_secondary_color" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor secundária do plugin.', 'cupompromo') . '</p>';
    }
    
    public function cupompromo_notifications_field(): void {
        $value = get_option('cupompromo_enable_notifications', '1');
        echo '<label><input type="checkbox" name="cupompromo_enable_notifications" value="1" ' . checked($value, '1', false) . ' />';
        echo __('Habilitar notificações administrativas', 'cupompromo') . '</label>';
    }
    
    public function cupompromo_sync_interval_field(): void {
        $value = get_option('cupompromo_auto_sync_interval', 'hourly');
        $intervals = array(
            'hourly' => __('A cada hora', 'cupompromo'),
            'twicedaily' => __('Duas vezes por dia', 'cupompromo'),
            'daily' => __('Diariamente', 'cupompromo')
        );
        
        echo '<select name="cupompromo_auto_sync_interval">';
        foreach ($intervals as $interval => $label) {
            echo '<option value="' . $interval . '" ' . selected($value, $interval, false) . '>' . $label . '</option>';
        }
        echo '</select>';
    }
    
    /**
     * AJAX: Sincronizar com Awin
     */
    public function cupompromo_ajax_sync_awin(): void {
        check_ajax_referer('cupompromo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissão negada', 'cupompromo'));
        }
        
        $coupon_manager = Cupompromo_Coupon_Manager::get_instance();
        $result = $coupon_manager->cupompromo_force_sync();
        
        wp_send_json($result);
    }
    
    /**
     * AJAX: Obter estatísticas
     */
    public function cupompromo_ajax_get_stats(): void {
        check_ajax_referer('cupompromo_admin_nonce', 'nonce');
        
        $coupon_manager = Cupompromo_Coupon_Manager::get_instance();
        $stats = $coupon_manager->cupompromo_get_coupon_stats();
        
        wp_send_json_success($stats);
    }
    
    /**
     * Notificações administrativas
     */
    public function cupompromo_admin_notices(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Verifica se a API está configurada
        $api_key = get_option('cupompromo_awin_api_key');
        $publisher_id = get_option('cupompromo_awin_publisher_id');
        
        if (empty($api_key) || empty($publisher_id)) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . __('<strong>Cupompromo:</strong> Configure sua API Awin para começar a sincronizar cupons.', 'cupompromo') . '</p>';
            echo '<p><a href="' . admin_url('admin.php?page=cupompromo-settings') . '" class="button button-primary">' . __('Configurar Agora', 'cupompromo') . '</a></p>';
            echo '</div>';
        }
        
        // Verifica última sincronização
        $last_sync = get_option('cupompromo_last_awin_sync');
        if ($last_sync) {
            $sync_time = strtotime($last_sync);
            $hours_ago = (time() - $sync_time) / 3600;
            
            if ($hours_ago > 24) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p>' . __('<strong>Cupompromo:</strong> A última sincronização foi há mais de 24 horas. Considere sincronizar manualmente.', 'cupompromo') . '</p>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Exibe cupons recentes no dashboard
     */
    private function cupompromo_display_recent_coupons(): void {
        $coupon_manager = Cupompromo_Coupon_Manager::get_instance();
        $recent_coupons = $coupon_manager->cupompromo_get_coupons(array(
            'limit' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if (empty($recent_coupons)) {
            echo '<p>' . __('Nenhum cupom encontrado.', 'cupompromo') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Cupom', 'cupompromo') . '</th>';
        echo '<th>' . __('Loja', 'cupompromo') . '</th>';
        echo '<th>' . __('Desconto', 'cupompromo') . '</th>';
        echo '<th>' . __('Status', 'cupompromo') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($recent_coupons as $coupon) {
            echo '<tr>';
            echo '<td><strong>' . esc_html($coupon->title) . '</strong></td>';
            echo '<td>' . esc_html($coupon->store_name ?? '') . '</td>';
            echo '<td>' . esc_html($coupon->formatted_discount) . '</td>';
            echo '<td><span class="status-' . esc_attr($coupon->status) . '">' . esc_html($coupon->status) . '</span></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
} 