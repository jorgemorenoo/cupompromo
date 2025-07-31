<?php
/**
 * Classe Cupompromo_Awin_API
 * 
 * Responsável pela integração com a API da Awin para sincronização
 * de cupons e ofertas de afiliados.
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
 * Classe Cupompromo_Awin_API
 */
class Cupompromo_Awin_API {
    
    /**
     * URL base da API da Awin
     */
    private const API_BASE_URL = 'https://api.awin.com';
    
    /**
     * Configurações da API
     */
    private $config;
    
    /**
     * Cache de requisições
     */
    private $cache;
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        $this->config = $this->get_api_config();
        $this->cache = array();
    }
    
    /**
     * Obtém configurações da API
     */
    private function get_api_config(): array {
        return array(
            'api_key' => get_option('cupompromo_awin_api_key', ''),
            'publisher_id' => get_option('cupompromo_awin_publisher_id', ''),
            'region' => get_option('cupompromo_awin_region', 'BR'),
            'timeout' => 30,
            'cache_duration' => 3600 // 1 hora
        );
    }
    
    /**
     * Verifica se a API está configurada
     */
    public function is_configured(): bool {
        return !empty($this->config['api_key']) && !empty($this->config['publisher_id']);
    }
    
    /**
     * Obtém cupons da Awin
     */
    public function get_coupons(array $params = array()): array {
        if (!$this->is_configured()) {
            return array();
        }
        
        $cache_key = 'awin_coupons_' . md5(serialize($params));
        
        // Verifica cache
        $cached = wp_cache_get($cache_key, 'cupompromo_awin');
        if ($cached !== false) {
            return $cached;
        }
        
        $default_params = array(
            'timezone' => 'America/Sao_Paulo',
            'format' => 'json',
            'limit' => 100,
            'offset' => 0,
            'relationship' => 'substring',
            'hasData' => 'true'
        );
        
        $params = wp_parse_args($params, $default_params);
        
        $url = add_query_arg($params, self::API_BASE_URL . '/v1/publishers/' . $this->config['publisher_id'] . '/programmes');
        
        $response = $this->make_request($url);
        
        if (is_wp_error($response)) {
            error_log('Cupompromo Awin API Error: ' . $response->get_error_message());
            return array();
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($data) || !isset($data['data'])) {
            return array();
        }
        
        $coupons = $this->parse_coupons($data['data']);
        
        // Salva no cache
        wp_cache_set($cache_key, $coupons, 'cupompromo_awin', $this->config['cache_duration']);
        
        return $coupons;
    }
    
    /**
     * Obtém lojas/merchants da Awin
     */
    public function get_merchants(array $params = array()): array {
        if (!$this->is_configured()) {
            return array();
        }
        
        $cache_key = 'awin_merchants_' . md5(serialize($params));
        
        // Verifica cache
        $cached = wp_cache_get($cache_key, 'cupompromo_awin');
        if ($cached !== false) {
            return $cached;
        }
        
        $default_params = array(
            'timezone' => 'America/Sao_Paulo',
            'format' => 'json',
            'limit' => 100,
            'offset' => 0
        );
        
        $params = wp_parse_args($params, $default_params);
        
        $url = add_query_arg($params, self::API_BASE_URL . '/v1/publishers/' . $this->config['publisher_id'] . '/programmes');
        
        $response = $this->make_request($url);
        
        if (is_wp_error($response)) {
            error_log('Cupompromo Awin API Error: ' . $response->get_error_message());
            return array();
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($data) || !isset($data['data'])) {
            return array();
        }
        
        $merchants = $this->parse_merchants($data['data']);
        
        // Salva no cache
        wp_cache_set($cache_key, $merchants, 'cupompromo_awin', $this->config['cache_duration']);
        
        return $merchants;
    }
    
    /**
     * Sincroniza cupons da Awin
     */
    public function sync_coupons(): array {
        $results = array(
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0
        );
        
        $coupons = $this->get_coupons(array('limit' => 500));
        
        foreach ($coupons as $coupon_data) {
            try {
                $result = $this->sync_single_coupon($coupon_data);
                $results['total']++;
                
                if ($result === 'created') {
                    $results['created']++;
                } elseif ($result === 'updated') {
                    $results['updated']++;
                } else {
                    $results['errors']++;
                }
            } catch (Exception $e) {
                $results['errors']++;
                error_log('Cupompromo Awin Sync Error: ' . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Sincroniza um cupom individual
     */
    private function sync_single_coupon(array $coupon_data): string {
        global $wpdb;
        
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        // Verifica se a loja existe
        $store_id = $this->get_or_create_store($coupon_data);
        
        if (!$store_id) {
            return 'error';
        }
        
        // Dados do cupom
        $coupon_data_db = array(
            'title' => sanitize_text_field($coupon_data['description'] ?? ''),
            'coupon_code' => sanitize_text_field($coupon_data['code'] ?? ''),
            'affiliate_url' => esc_url_raw($coupon_data['url'] ?? ''),
            'discount_value' => sanitize_text_field($coupon_data['discount'] ?? ''),
            'discount_type' => $this->determine_discount_type($coupon_data['discount'] ?? ''),
            'store_id' => $store_id,
            'coupon_type' => $this->determine_coupon_type($coupon_data),
            'status' => 'active',
            'awin_id' => intval($coupon_data['id'] ?? 0),
            'awin_data' => json_encode($coupon_data),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        // Verifica se o cupom já existe
        $existing_coupon = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_coupons WHERE awin_id = %d",
            $coupon_data_db['awin_id']
        ));
        
        if ($existing_coupon) {
            // Atualiza cupom existente
            $wpdb->update(
                $table_coupons,
                $coupon_data_db,
                array('awin_id' => $coupon_data_db['awin_id'])
            );
            return 'updated';
        } else {
            // Cria novo cupom
            $wpdb->insert($table_coupons, $coupon_data_db);
            return 'created';
        }
    }
    
    /**
     * Obtém ou cria uma loja
     */
    private function get_or_create_store(array $coupon_data): int {
        global $wpdb;
        
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $merchant_name = sanitize_text_field($coupon_data['merchant_name'] ?? '');
        $merchant_id = intval($coupon_data['merchant_id'] ?? 0);
        
        if (empty($merchant_name)) {
            return 0;
        }
        
        // Verifica se a loja já existe
        $existing_store = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_stores WHERE awin_id = %d OR name = %s",
            $merchant_id,
            $merchant_name
        ));
        
        if ($existing_store) {
            return $existing_store->id;
        }
        
        // Cria nova loja
        $store_data = array(
            'name' => $merchant_name,
            'slug' => sanitize_title($merchant_name),
            'store_description' => '',
            'store_website' => esc_url_raw($coupon_data['merchant_url'] ?? ''),
            'logo_url' => esc_url_raw($coupon_data['merchant_logo'] ?? ''),
            'featured_store' => 0,
            'default_commission' => floatval($coupon_data['commission'] ?? 0),
            'status' => 'active',
            'awin_id' => $merchant_id,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $wpdb->insert($table_stores, $store_data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Determina o tipo de desconto
     */
    private function determine_discount_type(string $discount): string {
        if (strpos($discount, '%') !== false) {
            return 'percentage';
        }
        return 'fixed';
    }
    
    /**
     * Determina o tipo de cupom
     */
    private function determine_coupon_type(array $coupon_data): string {
        if (!empty($coupon_data['code'])) {
            return 'code';
        }
        return 'offer';
    }
    
    /**
     * Faz requisição para a API
     */
    private function make_request(string $url): WP_Error|array {
        $args = array(
            'timeout' => $this->config['timeout'],
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            )
        );
        
        return wp_remote_get($url, $args);
    }
    
    /**
     * Processa dados dos cupons
     */
    private function parse_coupons(array $data): array {
        $coupons = array();
        
        foreach ($data as $item) {
            if (isset($item['vouchers']) && is_array($item['vouchers'])) {
                foreach ($item['vouchers'] as $voucher) {
                    $coupons[] = array_merge($item, array('voucher' => $voucher));
                }
            }
        }
        
        return $coupons;
    }
    
    /**
     * Processa dados dos merchants
     */
    private function parse_merchants(array $data): array {
        $merchants = array();
        
        foreach ($data as $item) {
            $merchants[] = array(
                'id' => $item['id'] ?? 0,
                'name' => $item['name'] ?? '',
                'url' => $item['url'] ?? '',
                'logo' => $item['logo'] ?? '',
                'commission' => $item['commission'] ?? 0,
                'status' => $item['status'] ?? 'inactive'
            );
        }
        
        return $merchants;
    }
    
    /**
     * Testa a conexão com a API
     */
    public function test_connection(): array {
        if (!$this->is_configured()) {
            return array(
                'success' => false,
                'message' => __('API não configurada', 'cupompromo')
            );
        }
        
        $url = self::API_BASE_URL . '/v1/publishers/' . $this->config['publisher_id'] . '/programmes';
        $response = $this->make_request($url);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            return array(
                'success' => true,
                'message' => __('Conexão com API estabelecida com sucesso', 'cupompromo')
            );
        }
        
        return array(
            'success' => false,
            'message' => sprintf(__('Erro na API: %d', 'cupompromo'), $status_code)
        );
    }
    
    /**
     * Limpa cache da API
     */
    public function clear_cache(): void {
        wp_cache_flush_group('cupompromo_awin');
        $this->cache = array();
    }
    
    /**
     * Obtém estatísticas da API
     */
    public function get_stats(): array {
        return array(
            'configured' => $this->is_configured(),
            'last_sync' => get_option('cupompromo_awin_last_sync', ''),
            'total_coupons' => $this->get_total_coupons(),
            'total_stores' => $this->get_total_stores()
        );
    }
    
    /**
     * Obtém total de cupons sincronizados
     */
    private function get_total_coupons(): int {
        global $wpdb;
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_coupons WHERE awin_id > 0");
    }
    
    /**
     * Obtém total de lojas sincronizadas
     */
    private function get_total_stores(): int {
        global $wpdb;
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_stores WHERE awin_id > 0");
    }
} 