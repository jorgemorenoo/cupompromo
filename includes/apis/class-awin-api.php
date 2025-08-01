<?php
/**
 * Awin API - Cupompromo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

class Cupompromo_Awin_API extends Cupompromo_Rest_Client {
    protected $access_token;
    protected $publisher_id;
    protected $type = 'publisher';
    protected $base_api = 'https://api.awin.com';
    
    public function __construct() {
        parent::set_uri($this->base_api);
        
        // Obter credenciais das configurações
        $this->access_token = get_option('cupompromo_awin_token', '090f5e71-1b20-47cb-9aa6-e1cde8605ba6');
        $this->publisher_id = get_option('cupompromo_awin_publisher', '627035');
        
        // Configurar headers padrão
        parent::set_headers([
            'Authorization' => 'Bearer ' . $this->access_token,
            'Content-Type' => 'application/json'
        ]);
    }
    
    /**
     * Testar conexão com a API
     */
    public function test_connection(): bool {
        try {
            $request = parent::get('/accounts/', [
                'type' => $this->type
            ]);
            return $request ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Buscar informações da conta
     */
    public function get_account(): ?array {
        try {
            return parent::get('/accounts/', [
                'type' => $this->type
            ]);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Buscar advertiser específico
     */
    public function get_advertiser(int $advertiser_id, bool $any = false): ?array {
        try {
            parent::set_timeout(120);
            
            $options = ['advertiserId' => $advertiser_id];
            if ($any) {
                $options['relationship'] = 'any';
            }
            
            return parent::get('/publishers/' . urlencode($this->publisher_id) . '/programmedetails', $options);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Listar todos os advertisers
     */
    public function get_advertisers(array $options = []): array {
        try {
            parent::set_timeout(120);
            
            $default_options = [
                'relationship' => 'joined',
                'limit' => 100
            ];
            
            $options = array_merge($default_options, $options);
            
            $response = parent::get('/publishers/' . urlencode($this->publisher_id) . '/programmes', $options);
            
            if (!$response || !isset($response['data'])) {
                return [];
            }
            
            return $response['data'];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Buscar transação específica
     */
    public function get_transaction($transaction_id): ?array {
        try {
            parent::set_timeout(120);
            
            $transaction_id = is_array($transaction_id) ? implode(',', $transaction_id) : $transaction_id;
            $options = ['ids' => $transaction_id];
            
            $request = parent::get('/publishers/' . urlencode($this->publisher_id) . '/transactions/', $options);
            
            if (!$request || !is_array($request) || empty($request)) {
                return null;
            }
            
            return $request[0];
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Listar transações
     */
    public function get_transactions(array $options = []): array {
        try {
            parent::set_timeout(120);
            
            $default_options = [
                'startDate' => date('Y-m-d', strtotime('-30 days')),
                'endDate' => date('Y-m-d'),
                'timezone' => 'Europe/London',
                'dateFormat' => 'iso8601',
                'limit' => 100
            ];
            
            $options = array_merge($default_options, $options);
            
            $response = parent::get('/publishers/' . urlencode($this->publisher_id) . '/transactions/', $options);
            
            if (!$response || !isset($response['data'])) {
                return [];
            }
            
            return $response['data'];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Buscar ofertas/cupons
     */
    public function get_coupons(array $options = []): array {
        try {
            parent::set_timeout(120);
            
            $default_options = [
                'limit' => 100,
                'voucherType' => 'discount'
            ];
            
            $options = array_merge($default_options, $options);
            
            $response = parent::get('/publishers/' . urlencode($this->publisher_id) . '/offers/', $options);
            
            if (!$response || !isset($response['data'])) {
                return [];
            }
            
            return $response['data'];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Buscar cupons de um advertiser específico
     */
    public function get_advertiser_coupons(int $advertiser_id, array $options = []): array {
        try {
            parent::set_timeout(120);
            
            $default_options = [
                'advertiserId' => $advertiser_id,
                'limit' => 100,
                'voucherType' => 'discount'
            ];
            
            $options = array_merge($default_options, $options);
            
            $response = parent::get('/publishers/' . urlencode($this->publisher_id) . '/offers/', $options);
            
            if (!$response || !isset($response['data'])) {
                return [];
            }
            
            return $response['data'];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obter estatísticas da API
     */
    public function get_statistics(): array {
        try {
            $account = $this->get_account();
            $advertisers = $this->get_advertisers(['limit' => 10]);
            $transactions = $this->get_transactions(['limit' => 10]);
            
            return [
                'account' => $account,
                'advertisers_count' => count($advertisers),
                'recent_transactions' => count($transactions),
                'last_sync' => get_option('cupompromo_awin_last_sync'),
                'status' => get_option('cupompromo_awin_status', 'unknown')
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }
    
    /**
     * Sincronizar advertisers (alias para get_advertisers)
     */
    public function sync_advertisers(): array {
        return $this->get_advertisers();
    }
    
    /**
     * Sincronizar cupons (alias para get_coupons)
     */
    public function sync_coupons(int $advertiser_id = null): array {
        if ($advertiser_id) {
            return $this->get_advertiser_coupons($advertiser_id);
        }
        
        return $this->get_coupons();
    }
    
    /**
     * Obter relatório de performance
     */
    public function get_performance_report(array $options = []): array {
        try {
            parent::set_timeout(120);
            
            $default_options = [
                'startDate' => date('Y-m-d', strtotime('-30 days')),
                'endDate' => date('Y-m-d'),
                'timezone' => 'Europe/London',
                'dateFormat' => 'iso8601',
                'groupBy' => 'advertiser'
            ];
            
            $options = array_merge($default_options, $options);
            
            $response = parent::get('/publishers/' . urlencode($this->publisher_id) . '/transactions/', $options);
            
            if (!$response || !isset($response['data'])) {
                return [];
            }
            
            return $response['data'];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obter links de afiliado
     */
    public function get_affiliate_links(int $advertiser_id): array {
        try {
            parent::set_timeout(60);
            
            $response = parent::get('/publishers/' . urlencode($this->publisher_id) . '/programmes/' . $advertiser_id . '/links');
            
            if (!$response || !isset($response['data'])) {
                return [];
            }
            
            return $response['data'];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Criar link de afiliado
     */
    public function create_affiliate_link(int $advertiser_id, string $url, string $description = ''): ?array {
        try {
            parent::set_timeout(60);
            
            $data = [
                'url' => $url,
                'description' => $description
            ];
            
            return parent::post('/publishers/' . urlencode($this->publisher_id) . '/programmes/' . $advertiser_id . '/links', $data);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Obter configurações da API
     */
    public function get_config(): array {
        return [
            'access_token' => $this->access_token ? '***' . substr($this->access_token, -4) : 'Não configurado',
            'publisher_id' => $this->publisher_id,
            'base_api' => $this->base_api,
            'type' => $this->type
        ];
    }
    
    /**
     * Validar credenciais
     */
    public function validate_credentials(): bool {
        if (empty($this->access_token) || empty($this->publisher_id)) {
            return false;
        }
        
        return $this->test_connection();
    }
} 