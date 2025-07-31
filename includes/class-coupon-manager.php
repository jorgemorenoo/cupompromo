<?php
/**
 * Classe Cupompromo_Coupon_Manager
 * 
 * Responsável pelo gerenciamento centralizado de cupons,
 * incluindo sincronização com API Awin, CRUD, cache e analytics.
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
 * Classe Cupompromo_Coupon_Manager
 */
class Cupompromo_Coupon_Manager {
    
    /**
     * Instância única da classe
     */
    private static $instance = null;
    
    /**
     * Instância da API Awin
     */
    private $awin_api;
    
    /**
     * Cache de cupons
     */
    private $cache = array();
    
    /**
     * Rate limiting
     */
    private $rate_limits = array();
    
    /**
     * Construtor da classe
     */
    private function __construct() {
        $this->awin_api = new Cupompromo_Awin_API();
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
        add_action('wp_ajax_cupompromo_track_click', array($this, 'track_coupon_click'));
        add_action('wp_ajax_nopriv_cupompromo_track_click', array($this, 'track_coupon_click'));
        add_action('wp_ajax_cupompromo_copy_code', array($this, 'copy_coupon_code'));
        add_action('wp_ajax_nopriv_cupompromo_copy_code', array($this, 'copy_coupon_code'));
        add_action('init', array($this, 'schedule_cleanup'));
        add_action('cupompromo_cleanup_expired_coupons', array($this, 'cleanup_expired_coupons'));
        add_action('cupompromo_sync_awin_coupons', array($this, 'sync_awin_coupons'));
        
        // Agenda sincronização automática
        if (!wp_next_scheduled('cupompromo_sync_awin_coupons')) {
            wp_schedule_event(time(), 'hourly', 'cupompromo_sync_awin_coupons');
        }
    }
    
    /**
     * Sincroniza cupons da API Awin
     */
    public function sync_awin_coupons(): array {
        if (!$this->awin_api->is_configured()) {
            return array(
                'success' => false,
                'message' => __('API Awin não configurada', 'cupompromo'),
                'stats' => array()
            );
        }
        
        // Verifica rate limiting
        if ($this->is_rate_limited('awin_sync')) {
            return array(
                'success' => false,
                'message' => __('Rate limit atingido. Tente novamente em alguns minutos.', 'cupompromo'),
                'stats' => array()
            );
        }
        
        $results = array(
            'total_processed' => 0,
            'posts_created' => 0,
            'posts_updated' => 0,
            'posts_skipped' => 0,
            'errors' => 0,
            'start_time' => microtime(true)
        );
        
        try {
            // Obtém cupons da API Awin
            $awin_coupons = $this->awin_api->get_coupons(array(
                'limit' => 500,
                'hasData' => 'true'
            ));
            
            if (empty($awin_coupons)) {
                return array(
                    'success' => true,
                    'message' => __('Nenhum cupom encontrado na API', 'cupompromo'),
                    'stats' => $results
                );
            }
            
            foreach ($awin_coupons as $awin_coupon) {
                $results['total_processed']++;
                
                try {
                    $sync_result = $this->sync_single_awin_coupon($awin_coupon);
                    
                    switch ($sync_result) {
                        case 'created':
                            $results['posts_created']++;
                            break;
                        case 'updated':
                            $results['posts_updated']++;
                            break;
                        case 'skipped':
                            $results['posts_skipped']++;
                            break;
                        default:
                            $results['errors']++;
                    }
                } catch (Exception $e) {
                    $results['errors']++;
                    error_log('Cupompromo Awin Sync Error: ' . $e->getMessage());
                }
            }
            
            // Atualiza timestamp da última sincronização
            update_option('cupompromo_last_awin_sync', current_time('mysql'));
            
            // Define rate limiting
            $this->set_rate_limit('awin_sync', 3600); // 1 hora
            
            $results['end_time'] = microtime(true);
            $results['duration'] = $results['end_time'] - $results['start_time'];
            
            return array(
                'success' => true,
                'message' => sprintf(
                    __('Sincronização concluída: %d processados, %d criados, %d atualizados', 'cupompromo'),
                    $results['total_processed'],
                    $results['posts_created'],
                    $results['posts_updated']
                ),
                'stats' => $results
            );
            
        } catch (Exception $e) {
            error_log('Cupompromo Awin Sync Fatal Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'stats' => $results
            );
        }
    }
    
    /**
     * Sincroniza um cupom individual da Awin
     */
    private function sync_single_awin_coupon(array $awin_coupon): string {
        // Processa dados da Awin
        $coupon_data = $this->process_awin_coupon_data($awin_coupon);
        
        if (empty($coupon_data)) {
            return 'skipped';
        }
        
        // Verifica se o cupom já existe
        $existing_post_id = $this->get_coupon_by_awin_id($coupon_data['awin_id']);
        
        if ($existing_post_id) {
            // Atualiza post existente
            $this->update_coupon_post($existing_post_id, $coupon_data);
            return 'updated';
        } else {
            // Cria novo post
            $post_id = $this->create_coupon_post($coupon_data);
            return $post_id ? 'created' : 'skipped';
        }
    }
    
    /**
     * Processa dados de cupom da Awin para formato WordPress
     */
    private function process_awin_coupon_data(array $awin_coupon): array {
        // Extrai dados básicos
        $merchant_data = $awin_coupon['merchant'] ?? array();
        $voucher_data = $awin_coupon['voucher'] ?? array();
        
        // Determina tipo de cupom
        $coupon_type = !empty($voucher_data['code']) ? 'code' : 'offer';
        
        // Processa desconto
        $discount_value = $this->extract_discount_value($voucher_data);
        $discount_type = $this->determine_discount_type($discount_value);
        
        // Processa data de expiração
        $expiry_date = $this->process_expiry_date($voucher_data);
        
        // Verifica se o cupom está ativo
        $status = $this->determine_coupon_status($voucher_data, $expiry_date);
        
        return array(
            'post_title' => sanitize_text_field($voucher_data['description'] ?? $merchant_data['name'] ?? ''),
            'post_content' => wp_kses_post($voucher_data['description'] ?? ''),
            'post_status' => $status,
            'post_type' => 'cupompromo_coupon',
            'meta_input' => array(
                '_awin_id' => intval($awin_coupon['id'] ?? 0),
                '_awin_merchant_id' => intval($merchant_data['id'] ?? 0),
                '_coupon_type' => $coupon_type,
                '_coupon_code' => sanitize_text_field($voucher_data['code'] ?? ''),
                '_affiliate_url' => esc_url_raw($voucher_data['url'] ?? $merchant_data['url'] ?? ''),
                '_discount_value' => sanitize_text_field($discount_value),
                '_discount_type' => $discount_type,
                '_expiry_date' => $expiry_date,
                '_store_id' => $this->get_or_create_store_from_awin($merchant_data),
                '_awin_data' => json_encode($awin_coupon),
                '_click_count' => 0,
                '_usage_count' => 0,
                '_verified_date' => current_time('mysql'),
                '_last_sync' => current_time('mysql')
            )
        );
    }
    
    /**
     * Extrai valor de desconto dos dados da Awin
     */
    private function extract_discount_value(array $voucher_data): string {
        $discount_text = $voucher_data['description'] ?? '';
        
        // Padrões comuns de desconto
        $patterns = array(
            '/(\d+)%\s*off/i',
            '/(\d+)\s*%\s*desconto/i',
            '/(\d+)\s*%\s*off/i',
            '/R?\$?\s*(\d+)\s*off/i',
            '/R?\$?\s*(\d+)\s*desconto/i'
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $discount_text, $matches)) {
                return $matches[1] . '%';
            }
        }
        
        return '';
    }
    
    /**
     * Determina tipo de desconto
     */
    private function determine_discount_type(string $discount_value): string {
        if (strpos($discount_value, '%') !== false) {
            return 'percentage';
        }
        return 'fixed';
    }
    
    /**
     * Processa data de expiração
     */
    private function process_expiry_date(array $voucher_data): ?string {
        if (empty($voucher_data['endDate'])) {
            return null;
        }
        
        $expiry_timestamp = strtotime($voucher_data['endDate']);
        
        if ($expiry_timestamp === false) {
            return null;
        }
        
        return date('Y-m-d H:i:s', $expiry_timestamp);
    }
    
    /**
     * Determina status do cupom
     */
    private function determine_coupon_status(array $voucher_data, ?string $expiry_date): string {
        // Verifica se está ativo na Awin
        if (isset($voucher_data['active']) && !$voucher_data['active']) {
            return 'inactive';
        }
        
        // Verifica expiração
        if ($expiry_date && strtotime($expiry_date) < time()) {
            return 'expired';
        }
        
        return 'publish';
    }
    
    /**
     * Obtém ou cria loja baseada nos dados da Awin
     */
    private function get_or_create_store_from_awin(array $merchant_data): int {
        $merchant_id = intval($merchant_data['id'] ?? 0);
        $merchant_name = sanitize_text_field($merchant_data['name'] ?? '');
        
        if (empty($merchant_name)) {
            return 0;
        }
        
        // Verifica se a loja já existe
        $existing_store = get_posts(array(
            'post_type' => 'cupompromo_store',
            'meta_query' => array(
                array(
                    'key' => '_awin_id',
                    'value' => $merchant_id
                )
            ),
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ));
        
        if (!empty($existing_store)) {
            return $existing_store[0]->ID;
        }
        
        // Cria nova loja
        $store_data = array(
            'post_title' => $merchant_name,
            'post_content' => sanitize_textarea_field($merchant_data['description'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'cupompromo_store',
            'meta_input' => array(
                '_awin_id' => $merchant_id,
                '_store_website' => esc_url_raw($merchant_data['url'] ?? ''),
                '_store_description' => sanitize_textarea_field($merchant_data['description'] ?? ''),
                '_featured_store' => 0,
                '_default_commission' => floatval($merchant_data['commission'] ?? 0),
                '_awin_data' => json_encode($merchant_data)
            )
        );
        
        $store_id = wp_insert_post($store_data);
        
        return $store_id ? $store_id : 0;
    }
    
    /**
     * Obtém cupom por ID da Awin
     */
    private function get_coupon_by_awin_id(int $awin_id): ?int {
        $posts = get_posts(array(
            'post_type' => 'cupompromo_coupon',
            'meta_query' => array(
                array(
                    'key' => '_awin_id',
                    'value' => $awin_id
                )
            ),
            'posts_per_page' => 1,
            'post_status' => array('publish', 'draft', 'private')
        ));
        
        return !empty($posts) ? $posts[0]->ID : null;
    }
    
    /**
     * Cria post de cupom
     */
    private function create_coupon_post(array $coupon_data): ?int {
        $post_id = wp_insert_post($coupon_data);
        
        if ($post_id) {
            // Hook após criação
            do_action('cupompromo_coupon_created', $post_id, $coupon_data);
        }
        
        return $post_id ? $post_id : null;
    }
    
    /**
     * Atualiza post de cupom
     */
    private function update_coupon_post(int $post_id, array $coupon_data): bool {
        $coupon_data['ID'] = $post_id;
        
        $result = wp_update_post($coupon_data);
        
        if ($result) {
            // Hook após atualização
            do_action('cupompromo_coupon_updated', $post_id, $coupon_data);
        }
        
        return $result !== 0;
    }
    
    /**
     * Obtém cupons com filtros
     */
    public function cupompromo_get_coupons(array $args = array()): array {
        $defaults = array(
            'store_id' => 0,
            'category_id' => 0,
            'coupon_type' => '',
            'status' => 'publish',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'date',
            'order' => 'DESC',
            'featured_only' => false,
            'expired' => false
        );
        
        $args = wp_parse_args($args, $defaults);
        $cache_key = 'coupons_' . md5(serialize($args));
        
        // Verifica cache
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        $query_args = array(
            'post_type' => 'cupompromo_coupon',
            'posts_per_page' => $args['limit'],
            'offset' => $args['offset'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'post_status' => $args['status']
        );
        
        // Filtros
        if (!empty($args['store_id'])) {
            $query_args['meta_query'][] = array(
                'key' => '_store_id',
                'value' => $args['store_id']
            );
        }
        
        if (!empty($args['coupon_type'])) {
            $query_args['meta_query'][] = array(
                'key' => '_coupon_type',
                'value' => $args['coupon_type']
            );
        }
        
        if ($args['featured_only']) {
            $query_args['meta_query'][] = array(
                'key' => '_featured_coupon',
                'value' => '1'
            );
        }
        
        if ($args['expired']) {
            $query_args['meta_query'][] = array(
                'key' => '_expiry_date',
                'value' => current_time('mysql'),
                'compare' => '<',
                'type' => 'DATETIME'
            );
        } else {
            $query_args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => '_expiry_date',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => '_expiry_date',
                    'value' => current_time('mysql'),
                    'compare' => '>=',
                    'type' => 'DATETIME'
                )
            );
        }
        
        $query = new WP_Query($query_args);
        $coupons = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $coupons[] = $this->cupompromo_process_coupon_data(get_post());
            }
        }
        
        wp_reset_postdata();
        
        // Salva no cache
        $this->cache[$cache_key] = $coupons;
        
        return $coupons;
    }
    
    /**
     * Processa dados do cupom
     */
    private function cupompromo_process_coupon_data(WP_Post $post): object {
        $coupon = (object) array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'status' => $post->post_status,
            'created_at' => $post->post_date,
            'updated_at' => $post->post_modified
        );
        
        // Meta fields
        $meta_fields = array(
            'coupon_type', 'coupon_code', 'affiliate_url', 'discount_value',
            'discount_type', 'expiry_date', 'store_id', 'click_count',
            'usage_count', 'verified_date', 'awin_id'
        );
        
        foreach ($meta_fields as $field) {
            $coupon->$field = get_post_meta($post->ID, '_' . $field, true);
        }
        
        // Dados da loja
        if (!empty($coupon->store_id)) {
            $store = get_post($coupon->store_id);
            if ($store) {
                $coupon->store_name = $store->post_title;
                $coupon->store_slug = $store->post_name;
                $coupon->store_logo = get_post_meta($store->ID, '_store_logo', true);
                $coupon->store_website = get_post_meta($store->ID, '_store_website', true);
            }
        }
        
        // Propriedades calculadas
        $coupon->is_expired = $this->cupompromo_is_coupon_expired($coupon);
        $coupon->is_verified = !empty($coupon->verified_date);
        $coupon->days_until_expiry = $this->cupompromo_get_days_until_expiry($coupon);
        $coupon->formatted_discount = $this->cupompromo_format_discount($coupon);
        
        return $coupon;
    }
    
    /**
     * Verifica se cupom está expirado
     */
    private function cupompromo_is_coupon_expired(object $coupon): bool {
        if (empty($coupon->expiry_date)) {
            return false;
        }
        
        return strtotime($coupon->expiry_date) < time();
    }
    
    /**
     * Obtém dias até expiração
     */
    private function cupompromo_get_days_until_expiry(object $coupon): int {
        if (empty($coupon->expiry_date)) {
            return -1;
        }
        
        $expiry_timestamp = strtotime($coupon->expiry_date);
        $current_timestamp = time();
        
        return max(0, ceil(($expiry_timestamp - $current_timestamp) / DAY_IN_SECONDS));
    }
    
    /**
     * Formata desconto para exibição
     */
    private function cupompromo_format_discount(object $coupon): string {
        if (empty($coupon->discount_value)) {
            return '';
        }
        
        if ($coupon->discount_type === 'percentage') {
            return $coupon->discount_value . '% OFF';
        }
        
        return 'R$ ' . $coupon->discount_value . ' OFF';
    }
    
    /**
     * Registra clique no cupom
     */
    public function track_coupon_click(): void {
        check_ajax_referer('cupompromo_track_click', 'nonce');
        
        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        
        if (!$coupon_id) {
            wp_die('Invalid coupon ID');
        }
        
        // Incrementa contador de cliques
        $current_clicks = get_post_meta($coupon_id, '_click_count', true);
        $new_clicks = intval($current_clicks) + 1;
        
        update_post_meta($coupon_id, '_click_count', $new_clicks);
        
        // Hook após clique
        do_action('cupompromo_coupon_clicked', $coupon_id, $new_clicks);
        
        wp_send_json_success(array(
            'message' => __('Clique registrado com sucesso', 'cupompromo'),
            'clicks' => $new_clicks
        ));
    }
    
    /**
     * Copia código do cupom
     */
    public function copy_coupon_code(): void {
        check_ajax_referer('cupompromo_copy_code', 'nonce');
        
        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        
        if (!$coupon_id) {
            wp_die('Invalid coupon ID');
        }
        
        $coupon_code = get_post_meta($coupon_id, '_coupon_code', true);
        
        if (empty($coupon_code)) {
            wp_send_json_error(array(
                'message' => __('Código não disponível', 'cupompromo')
            ));
        }
        
        // Incrementa contador de uso
        $current_usage = get_post_meta($coupon_id, '_usage_count', true);
        $new_usage = intval($current_usage) + 1;
        
        update_post_meta($coupon_id, '_usage_count', $new_usage);
        
        // Hook após cópia
        do_action('cupompromo_coupon_code_copied', $coupon_id, $new_usage);
        
        wp_send_json_success(array(
            'code' => $coupon_code,
            'message' => __('Código copiado para a área de transferência', 'cupompromo'),
            'usage' => $new_usage
        ));
    }
    
    /**
     * Agenda limpeza de cupons expirados
     */
    public function schedule_cleanup(): void {
        if (!wp_next_scheduled('cupompromo_cleanup_expired_coupons')) {
            wp_schedule_event(time(), 'daily', 'cupompromo_cleanup_expired_coupons');
        }
    }
    
    /**
     * Limpa cupons expirados
     */
    public function cleanup_expired_coupons(): void {
        $expired_coupons = get_posts(array(
            'post_type' => 'cupompromo_coupon',
            'meta_query' => array(
                array(
                    'key' => '_expiry_date',
                    'value' => current_time('mysql'),
                    'compare' => '<',
                    'type' => 'DATETIME'
                )
            ),
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $updated_count = 0;
        
        foreach ($expired_coupons as $coupon) {
            wp_update_post(array(
                'ID' => $coupon->ID,
                'post_status' => 'expired'
            ));
            $updated_count++;
        }
        
        if ($updated_count > 0) {
            do_action('cupompromo_coupons_expired', $updated_count);
        }
    }
    
    /**
     * Verifica rate limiting
     */
    private function is_rate_limited(string $action): bool {
        $limit_key = 'cupompromo_rate_limit_' . $action;
        $limit_data = get_transient($limit_key);
        
        if ($limit_data === false) {
            return false;
        }
        
        return $limit_data['count'] >= $limit_data['limit'];
    }
    
    /**
     * Define rate limiting
     */
    private function set_rate_limit(string $action, int $duration): void {
        $limit_key = 'cupompromo_rate_limit_' . $action;
        $limit_data = get_transient($limit_key);
        
        if ($limit_data === false) {
            $limit_data = array(
                'count' => 1,
                'limit' => 10,
                'reset_time' => time() + $duration
            );
        } else {
            $limit_data['count']++;
        }
        
        set_transient($limit_key, $limit_data, $duration);
    }
    
    /**
     * Obtém estatísticas dos cupons
     */
    public function cupompromo_get_coupon_stats(): array {
        $stats = array(
            'total_coupons' => wp_count_posts('cupompromo_coupon')->publish,
            'expired_coupons' => wp_count_posts('cupompromo_coupon')->expired,
            'total_stores' => wp_count_posts('cupompromo_store')->publish,
            'total_clicks' => $this->cupompromo_get_total_clicks(),
            'total_usage' => $this->cupompromo_get_total_usage(),
            'verified_coupons' => $this->cupompromo_get_verified_count(),
            'last_sync' => get_option('cupompromo_last_awin_sync', ''),
            'awin_configured' => $this->awin_api->is_configured()
        );
        
        return $stats;
    }
    
    /**
     * Obtém total de cliques
     */
    private function cupompromo_get_total_clicks(): int {
        global $wpdb;
        
        $result = $wpdb->get_var(
            "SELECT SUM(meta_value) FROM $wpdb->postmeta 
             WHERE meta_key = '_click_count' AND meta_value != ''"
        );
        
        return intval($result);
    }
    
    /**
     * Obtém total de usos
     */
    private function cupompromo_get_total_usage(): int {
        global $wpdb;
        
        $result = $wpdb->get_var(
            "SELECT SUM(meta_value) FROM $wpdb->postmeta 
             WHERE meta_key = '_usage_count' AND meta_value != ''"
        );
        
        return intval($result);
    }
    
    /**
     * Obtém contagem de cupons verificados
     */
    private function cupompromo_get_verified_count(): int {
        global $wpdb;
        
        $result = $wpdb->get_var(
            "SELECT COUNT(*) FROM $wpdb->postmeta 
             WHERE meta_key = '_verified_date' AND meta_value != ''"
        );
        
        return intval($result);
    }
    
    /**
     * Limpa cache
     */
    public function cupompromo_clear_cache(): void {
        $this->cache = array();
    }
    
    /**
     * Força sincronização manual
     */
    public function cupompromo_force_sync(): array {
        // Remove rate limiting para sincronização manual
        delete_transient('cupompromo_rate_limit_awin_sync');
        
        return $this->sync_awin_coupons();
    }
} 