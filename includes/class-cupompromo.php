<?php
/**
 * Classe principal do plugin Cupompromo
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
 * Classe Cupompromo
 */
class Cupompromo {
    
    /**
     * Instância única da classe
     */
    private static $instance = null;
    
    /**
     * Construtor da classe
     */
    private function __construct() {
        // Construtor privado para singleton
    }
    
    /**
     * Retorna a instância única da classe
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtém a versão do plugin
     */
    public function get_version(): string {
        return CUPOMPROMO_VERSION;
    }
    
    /**
     * Obtém a URL do plugin
     */
    public function get_plugin_url(): string {
        return CUPOMPROMO_PLUGIN_URL;
    }
    
    /**
     * Obtém o caminho do plugin
     */
    public function get_plugin_path(): string {
        return CUPOMPROMO_PLUGIN_PATH;
    }
    
    /**
     * Obtém o basename do plugin
     */
    public function get_plugin_basename(): string {
        return CUPOMPROMO_PLUGIN_BASENAME;
    }
    
    /**
     * Verifica se o plugin está ativo
     */
    public function is_active(): bool {
        return is_plugin_active(CUPOMPROMO_PLUGIN_BASENAME);
    }
    
    /**
     * Obtém configurações do plugin
     */
    public function get_settings(): array {
        return get_option('cupompromo_settings', array());
    }
    
    /**
     * Salva configurações do plugin
     */
    public function save_settings(array $settings): bool {
        return update_option('cupompromo_settings', $settings);
    }
    
    /**
     * Obtém uma configuração específica
     */
    public function get_setting(string $key, $default = null) {
        $settings = $this->get_settings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    /**
     * Salva uma configuração específica
     */
    public function save_setting(string $key, $value): bool {
        $settings = $this->get_settings();
        $settings[$key] = $value;
        return $this->save_settings($settings);
    }
    
    /**
     * Obtém estatísticas do plugin
     */
    public function get_stats(): array {
        global $wpdb;
        
        $stats = array();
        
        // Total de lojas
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        $stats['total_stores'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_stores WHERE status = 'active'");
        
        // Total de cupons
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $stats['total_coupons'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_coupons WHERE status = 'active'");
        
        // Total de cliques
        $table_analytics = $wpdb->prefix . 'cupompromo_analytics';
        $stats['total_clicks'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_analytics WHERE action_type = 'click'");
        
        // Total de conversões
        $stats['total_conversions'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_analytics WHERE action_type = 'conversion'");
        
        return $stats;
    }
    
    /**
     * Registra uma ação de analytics
     */
    public function log_analytics(int $coupon_id, string $action_type, array $data = array()): bool {
        global $wpdb;
        
        $table_analytics = $wpdb->prefix . 'cupompromo_analytics';
        
        $insert_data = array(
            'coupon_id' => $coupon_id,
            'user_id' => get_current_user_id(),
            'action_type' => $action_type,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'created_at' => current_time('mysql')
        );
        
        // Adiciona dados extras se fornecidos
        if (!empty($data)) {
            $insert_data = array_merge($insert_data, $data);
        }
        
        $result = $wpdb->insert($table_analytics, $insert_data);
        
        if ($result && $action_type === 'click') {
            // Atualiza contador de cliques do cupom
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}cupompromo_coupons SET click_count = click_count + 1 WHERE id = %d",
                $coupon_id
            ));
        }
        
        return $result !== false;
    }
    
    /**
     * Obtém o IP do cliente
     */
    private function get_client_ip(): string {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Gera um código único
     */
    public function generate_unique_code(string $prefix = 'CUPOM', int $length = 8): string {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = $prefix . '_';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
    
    /**
     * Valida um código de cupom
     */
    public function validate_coupon_code(string $code): array {
        global $wpdb;
        
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        
        $coupon = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_coupons WHERE coupon_code = %s AND status = 'active'",
            $code
        ));
        
        if (!$coupon) {
            return array(
                'valid' => false,
                'message' => __('Cupom não encontrado.', 'cupompromo')
            );
        }
        
        // Verifica se expirou
        if ($coupon->expiry_date && current_time('mysql') > $coupon->expiry_date) {
            return array(
                'valid' => false,
                'message' => __('Cupom expirado.', 'cupompromo')
            );
        }
        
        return array(
            'valid' => true,
            'message' => __('Cupom válido!', 'cupompromo'),
            'coupon' => $coupon
        );
    }
    
    /**
     * Formata valor monetário
     */
    public function format_currency(float $amount, string $currency = 'BRL'): string {
        $symbols = array(
            'BRL' => 'R$',
            'USD' => '$',
            'EUR' => '€'
        );
        
        $symbol = $symbols[$currency] ?? $currency;
        
        return $symbol . ' ' . number_format($amount, 2, ',', '.');
    }
    
    /**
     * Obtém lojas em destaque
     */
    public function get_featured_stores(int $limit = 10): array {
        global $wpdb;
        
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_stores WHERE featured_store = 1 AND status = 'active' ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
    }
    
    /**
     * Obtém cupons populares
     */
    public function get_popular_coupons(int $limit = 10): array {
        global $wpdb;
        
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, s.name as store_name, s.logo_url as store_logo 
             FROM $table_coupons c 
             LEFT JOIN $table_stores s ON c.store_id = s.id 
             WHERE c.status = 'active' 
             ORDER BY c.click_count DESC 
             LIMIT %d",
            $limit
        ));
    }
    
    /**
     * Busca cupons
     */
    public function search_coupons(string $query, array $filters = array()): array {
        global $wpdb;
        
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $sql = "SELECT c.*, s.name as store_name, s.logo_url as store_logo 
                FROM $table_coupons c 
                LEFT JOIN $table_stores s ON c.store_id = s.id 
                WHERE c.status = 'active'";
        
        $where_conditions = array();
        $where_values = array();
        
        // Busca por texto
        if (!empty($query)) {
            $where_conditions[] = "(c.title LIKE %s OR s.name LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($query) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        // Filtros adicionais
        if (!empty($filters['store_id'])) {
            $where_conditions[] = "c.store_id = %d";
            $where_values[] = $filters['store_id'];
        }
        
        if (!empty($filters['coupon_type'])) {
            $where_conditions[] = "c.coupon_type = %s";
            $where_values[] = $filters['coupon_type'];
        }
        
        if (!empty($filters['discount_type'])) {
            $where_conditions[] = "c.discount_type = %s";
            $where_values[] = $filters['discount_type'];
        }
        
        if (!empty($where_conditions)) {
            $sql .= " AND " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY c.click_count DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, ...$where_values);
        }
        
        return $wpdb->get_results($sql);
    }
} 