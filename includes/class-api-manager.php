<?php
/**
 * Gerenciador de APIs de Afiliados - Cupompromo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

class Cupompromo_API_Manager {
    
    private $apis = [];
    private $rest_client;
    
    public function __construct() {
        $this->init_apis();
        $this->rest_client = new Cupompromo_Rest_Client();
    }
    
    /**
     * Inicializar APIs disponíveis
     */
    private function init_apis() {
        $this->apis = [
            'awin' => new Cupompromo_Awin_API(),
            'admitad' => new Cupompromo_Admitad_API(),
            'afilio' => new Cupompromo_Afilio_API(),
            'socialsoul' => new Cupompromo_SocialSoul_API()
        ];
    }
    
    /**
     * Testar conexão com API
     */
    public function test_connection($api_name) {
        if (!isset($this->apis[$api_name])) {
            return [
                'success' => false,
                'error' => sprintf(__('API %s não encontrada.', 'cupompromo'), $api_name)
            ];
        }
        
        try {
            $api = $this->apis[$api_name];
            $result = $api->test_connection();
            
            // Atualizar status da API
            $status = $result ? 'active' : 'error';
            update_option("cupompromo_{$api_name}_status", $status);
            
            return [
                'success' => $result,
                'data' => $result ? __('Conexão bem-sucedida', 'cupompromo') : __('Falha na conexão', 'cupompromo'),
                'error' => $result ? null : __('Erro na comunicação com a API', 'cupompromo')
            ];
        } catch (Exception $e) {
            update_option("cupompromo_{$api_name}_status", 'error');
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sincronizar advertisers de uma API
     */
    public function sync_advertisers($api_name) {
        if (!isset($this->apis[$api_name])) {
            return [
                'success' => false,
                'error' => sprintf(__('API %s não encontrada.', 'cupompromo'), $api_name)
            ];
        }
        
        try {
            $api = $this->apis[$api_name];
            $advertisers = $api->get_advertisers();
            
            if (!$advertisers) {
                return [
                    'success' => false,
                    'error' => __('Nenhum advertiser encontrado', 'cupompromo')
                ];
            }
            
            $imported_count = 0;
            
            foreach ($advertisers as $advertiser) {
                $store_id = $this->import_advertiser($advertiser, $api_name);
                if ($store_id) {
                    $imported_count++;
                }
            }
            
            // Atualizar última sincronização
            update_option("cupompromo_{$api_name}_last_sync", current_time('mysql'));
            
            return [
                'success' => true,
                'count' => $imported_count,
                'data' => sprintf(__('%d lojas importadas com sucesso', 'cupompromo'), $imported_count)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Importar advertiser como loja
     */
    private function import_advertiser($advertiser_data, $api_name) {
        // Verificar se a loja já existe
        $existing_store = $this->find_existing_store($advertiser_data, $api_name);
        
        if ($existing_store) {
            // Atualizar loja existente
            return $this->update_store($existing_store, $advertiser_data, $api_name);
        } else {
            // Criar nova loja
            return $this->create_store($advertiser_data, $api_name);
        }
    }
    
    /**
     * Encontrar loja existente
     */
    private function find_existing_store($advertiser_data, $api_name) {
        global $wpdb;
        
        $api_id = $advertiser_data['id'] ?? $advertiser_data['advertiser_id'] ?? null;
        if (!$api_id) {
            return false;
        }
        
        $store_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_api_id' 
             AND meta_value = %s",
            $api_id
        ));
        
        return $store_id ? get_post($store_id) : false;
    }
    
    /**
     * Criar nova loja
     */
    private function create_store($advertiser_data, $api_name) {
        $store_data = $this->prepare_store_data($advertiser_data, $api_name);
        
        $store_id = wp_insert_post([
            'post_title' => $store_data['title'],
            'post_content' => $store_data['description'],
            'post_status' => 'publish',
            'post_type' => 'cupompromo_store'
        ]);
        
        if (is_wp_error($store_id)) {
            return false;
        }
        
        // Adicionar meta dados
        $this->add_store_meta($store_id, $store_data, $api_name);
        
        return $store_id;
    }
    
    /**
     * Atualizar loja existente
     */
    private function update_store($store, $advertiser_data, $api_name) {
        $store_data = $this->prepare_store_data($advertiser_data, $api_name);
        
        $updated = wp_update_post([
            'ID' => $store->ID,
            'post_title' => $store_data['title'],
            'post_content' => $store_data['description']
        ]);
        
        if (is_wp_error($updated)) {
            return false;
        }
        
        // Atualizar meta dados
        $this->add_store_meta($store->ID, $store_data, $api_name);
        
        return $store->ID;
    }
    
    /**
     * Preparar dados da loja
     */
    private function prepare_store_data($advertiser_data, $api_name) {
        $data = [
            'title' => $advertiser_data['name'] ?? $advertiser_data['title'] ?? 'Loja sem nome',
            'description' => $advertiser_data['description'] ?? '',
            'website' => $advertiser_data['url'] ?? $advertiser_data['website'] ?? '',
            'logo' => $advertiser_data['logo'] ?? $advertiser_data['image'] ?? '',
            'api_id' => $advertiser_data['id'] ?? $advertiser_data['advertiser_id'] ?? '',
            'commission' => $advertiser_data['commission'] ?? 0,
            'status' => $advertiser_data['status'] ?? 'active'
        ];
        
        return $data;
    }
    
    /**
     * Adicionar meta dados da loja
     */
    private function add_store_meta($store_id, $store_data, $api_name) {
        update_post_meta($store_id, '_api_source', $api_name);
        update_post_meta($store_id, '_api_id', $store_data['api_id']);
        update_post_meta($store_id, '_store_website', $store_data['website']);
        update_post_meta($store_id, '_store_logo', $store_data['logo']);
        update_post_meta($store_id, '_default_commission', $store_data['commission']);
        update_post_meta($store_id, '_store_status', $store_data['status']);
        
        // Adicionar categorias se disponíveis
        if (!empty($store_data['categories'])) {
            wp_set_object_terms($store_id, $store_data['categories'], 'cupompromo_category');
        }
    }
    
    /**
     * Sincronizar cupons de uma API
     */
    public function sync_coupons($api_name, $advertiser_id = null) {
        if (!isset($this->apis[$api_name])) {
            return [
                'success' => false,
                'error' => sprintf(__('API %s não encontrada.', 'cupompromo'), $api_name)
            ];
        }
        
        try {
            $api = $this->apis[$api_name];
            $coupons = $api->get_coupons($advertiser_id);
            
            if (!$coupons) {
                return [
                    'success' => false,
                    'error' => __('Nenhum cupom encontrado', 'cupompromo')
                ];
            }
            
            $imported_count = 0;
            
            foreach ($coupons as $coupon_data) {
                $coupon_id = $this->import_coupon($coupon_data, $api_name);
                if ($coupon_id) {
                    $imported_count++;
                }
            }
            
            return [
                'success' => true,
                'count' => $imported_count,
                'data' => sprintf(__('%d cupons importados com sucesso', 'cupompromo'), $imported_count)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Importar cupom
     */
    private function import_coupon($coupon_data, $api_name) {
        // Verificar se o cupom já existe
        $existing_coupon = $this->find_existing_coupon($coupon_data, $api_name);
        
        if ($existing_coupon) {
            // Atualizar cupom existente
            return $this->update_coupon($existing_coupon, $coupon_data, $api_name);
        } else {
            // Criar novo cupom
            return $this->create_coupon($coupon_data, $api_name);
        }
    }
    
    /**
     * Encontrar cupom existente
     */
    private function find_existing_coupon($coupon_data, $api_name) {
        global $wpdb;
        
        $api_id = $coupon_data['id'] ?? $coupon_data['voucher_id'] ?? null;
        if (!$api_id) {
            return false;
        }
        
        $coupon_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_api_coupon_id' 
             AND meta_value = %s",
            $api_id
        ));
        
        return $coupon_id ? get_post($coupon_id) : false;
    }
    
    /**
     * Criar novo cupom
     */
    private function create_coupon($coupon_data, $api_name) {
        $coupon_prepared = $this->prepare_coupon_data($coupon_data, $api_name);
        
        $coupon_id = wp_insert_post([
            'post_title' => $coupon_prepared['title'],
            'post_content' => $coupon_prepared['description'],
            'post_status' => 'publish',
            'post_type' => 'cupompromo_coupon'
        ]);
        
        if (is_wp_error($coupon_id)) {
            return false;
        }
        
        // Adicionar meta dados
        $this->add_coupon_meta($coupon_id, $coupon_prepared, $api_name);
        
        return $coupon_id;
    }
    
    /**
     * Atualizar cupom existente
     */
    private function update_coupon($coupon, $coupon_data, $api_name) {
        $coupon_prepared = $this->prepare_coupon_data($coupon_data, $api_name);
        
        $updated = wp_update_post([
            'ID' => $coupon->ID,
            'post_title' => $coupon_prepared['title'],
            'post_content' => $coupon_prepared['description']
        ]);
        
        if (is_wp_error($updated)) {
            return false;
        }
        
        // Atualizar meta dados
        $this->add_coupon_meta($coupon->ID, $coupon_prepared, $api_name);
        
        return $coupon->ID;
    }
    
    /**
     * Preparar dados do cupom
     */
    private function prepare_coupon_data($coupon_data, $api_name) {
        $data = [
            'title' => $coupon_data['title'] ?? $coupon_data['name'] ?? 'Cupom sem título',
            'description' => $coupon_data['description'] ?? $coupon_data['short_description'] ?? '',
            'code' => $coupon_data['code'] ?? $coupon_data['voucher_code'] ?? '',
            'discount' => $coupon_data['discount'] ?? $coupon_data['value'] ?? '',
            'type' => $coupon_data['type'] ?? 'percentage',
            'expiry' => $coupon_data['expiry_date'] ?? $coupon_data['valid_until'] ?? '',
            'url' => $coupon_data['url'] ?? $coupon_data['affiliate_url'] ?? '',
            'api_id' => $coupon_data['id'] ?? $coupon_data['voucher_id'] ?? '',
            'store_id' => $coupon_data['advertiser_id'] ?? $coupon_data['store_id'] ?? '',
            'status' => $coupon_data['status'] ?? 'active'
        ];
        
        return $data;
    }
    
    /**
     * Adicionar meta dados do cupom
     */
    private function add_coupon_meta($coupon_id, $coupon_data, $api_name) {
        update_post_meta($coupon_id, '_api_source', $api_name);
        update_post_meta($coupon_id, '_api_coupon_id', $coupon_data['api_id']);
        update_post_meta($coupon_id, '_coupon_code', $coupon_data['code']);
        update_post_meta($coupon_id, '_discount_value', $coupon_data['discount']);
        update_post_meta($coupon_id, '_discount_type', $coupon_data['type']);
        update_post_meta($coupon_id, '_affiliate_url', $coupon_data['url']);
        update_post_meta($coupon_id, '_expiry_date', $coupon_data['expiry']);
        update_post_meta($coupon_id, '_store_id', $coupon_data['store_id']);
        update_post_meta($coupon_id, '_coupon_status', $coupon_data['status']);
        
        // Definir tipo de cupom
        $coupon_type = !empty($coupon_data['code']) ? 'code' : 'offer';
        update_post_meta($coupon_id, '_coupon_type', $coupon_type);
    }
    
    /**
     * Obter estatísticas de uma API
     */
    public function get_api_statistics($api_name) {
        if (!isset($this->apis[$api_name])) {
            return false;
        }
        
        try {
            $api = $this->apis[$api_name];
            return $api->get_statistics();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obter logs de uma API
     */
    public function get_api_logs($api_name, $limit = 50) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cupompromo_api_logs';
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE api_name = %s 
             ORDER BY created_at DESC 
             LIMIT %d",
            $api_name,
            $limit
        ));
        
        return $logs;
    }
    
    /**
     * Registrar log de API
     */
    public function log_api_action($api_name, $action, $status, $message = '', $data = []) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cupompromo_api_logs';
        
        $wpdb->insert($table, [
            'api_name' => $api_name,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'data' => json_encode($data),
            'created_at' => current_time('mysql')
        ]);
    }
} 