<?php
/**
 * Sistema de Cache Inteligente
 *
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal do sistema de cache
 */
class Cupompromo_Cache {

    /**
     * Prefixo para chaves de cache
     */
    const CACHE_PREFIX = 'cupompromo_';

    /**
     * Tempos de expiração padrão (em segundos)
     */
    const EXPIRATION_TIMES = [
        'coupons' => 3600,        // 1 hora
        'stores' => 7200,         // 2 horas
        'categories' => 86400,    // 24 horas
        'search' => 1800,         // 30 minutos
        'analytics' => 300,       // 5 minutos
        'user_data' => 1800,      // 30 minutos
        'api_responses' => 3600,  // 1 hora
    ];

    /**
     * Instância singleton
     */
    private static $instance = null;

    /**
     * Cache de objetos em memória
     */
    private $memory_cache = [];

    /**
     * Estatísticas de cache
     */
    private $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
    ];

    /**
     * Construtor privado
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Obtém instância singleton
     */
    public static function get_instance(): Cupompromo_Cache {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializa hooks
     */
    private function init_hooks(): void {
        // Limpa cache quando cupons são atualizados
        add_action('save_post_cupompromo_coupon', [$this, 'clear_coupons_cache']);
        add_action('save_post_cupompromo_store', [$this, 'clear_stores_cache']);
        add_action('edited_cupompromo_category', [$this, 'clear_categories_cache']);
        
        // Limpa cache quando opções são atualizadas
        add_action('update_option_cupompromo_settings', [$this, 'clear_all_cache']);
        
        // Limpa cache periodicamente
        add_action('cupompromo_clear_expired_cache', [$this, 'clear_expired_cache']);
        
        // Agenda limpeza automática
        if (!wp_next_scheduled('cupompromo_clear_expired_cache')) {
            wp_schedule_event(time(), 'hourly', 'cupompromo_clear_expired_cache');
        }
    }

    /**
     * Define um valor no cache
     */
    public function set(string $key, $value, string $type = 'general', int $expiration = null): bool {
        $cache_key = $this->get_cache_key($key, $type);
        $expiration = $expiration ?? $this->get_expiration_time($type);
        
        // Cache em memória para acesso rápido
        $this->memory_cache[$cache_key] = [
            'value' => $value,
            'expires' => time() + $expiration,
            'type' => $type
        ];
        
        // Cache em transients para persistência
        $result = set_transient($cache_key, $value, $expiration);
        
        if ($result) {
            $this->stats['sets']++;
            $this->log_cache_action('set', $cache_key, $type);
        }
        
        return $result;
    }

    /**
     * Obtém um valor do cache
     */
    public function get(string $key, string $type = 'general') {
        $cache_key = $this->get_cache_key($key, $type);
        
        // Verifica cache em memória primeiro
        if (isset($this->memory_cache[$cache_key])) {
            $cached = $this->memory_cache[$cache_key];
            if ($cached['expires'] > time()) {
                $this->stats['hits']++;
                return $cached['value'];
            } else {
                unset($this->memory_cache[$cache_key]);
            }
        }
        
        // Verifica cache em transients
        $value = get_transient($cache_key);
        
        if (false !== $value) {
            $this->stats['hits']++;
            // Atualiza cache em memória
            $this->memory_cache[$cache_key] = [
                'value' => $value,
                'expires' => time() + $this->get_expiration_time($type),
                'type' => $type
            ];
            return $value;
        }
        
        $this->stats['misses']++;
        return false;
    }

    /**
     * Remove um valor do cache
     */
    public function delete(string $key, string $type = 'general'): bool {
        $cache_key = $this->get_cache_key($key, $type);
        
        // Remove da memória
        unset($this->memory_cache[$cache_key]);
        
        // Remove do transient
        $result = delete_transient($cache_key);
        
        if ($result) {
            $this->stats['deletes']++;
            $this->log_cache_action('delete', $cache_key, $type);
        }
        
        return $result;
    }

    /**
     * Verifica se uma chave existe no cache
     */
    public function exists(string $key, string $type = 'general'): bool {
        return false !== $this->get($key, $type);
    }

    /**
     * Obtém ou define um valor (get or set)
     */
    public function remember(string $key, callable $callback, string $type = 'general', int $expiration = null) {
        $value = $this->get($key, $type);
        
        if (false !== $value) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $type, $expiration);
        
        return $value;
    }

    /**
     * Limpa cache por tipo
     */
    public function clear_type(string $type): int {
        global $wpdb;
        
        $deleted = 0;
        $pattern = self::CACHE_PREFIX . $type . '_%';
        
        // Remove da memória
        foreach ($this->memory_cache as $key => $data) {
            if ($data['type'] === $type) {
                unset($this->memory_cache[$key]);
                $deleted++;
            }
        }
        
        // Remove transients
        $transients = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE %s AND option_name LIKE '_transient_%'",
            $pattern
        ));
        
        foreach ($transients as $transient) {
            $key = str_replace('_transient_', '', $transient);
            if (delete_transient($key)) {
                $deleted++;
            }
        }
        
        $this->log_cache_action('clear_type', $type, $deleted);
        return $deleted;
    }

    /**
     * Limpa todo o cache
     */
    public function clear_all(): int {
        global $wpdb;
        
        $deleted = 0;
        
        // Limpa memória
        $deleted += count($this->memory_cache);
        $this->memory_cache = [];
        
        // Limpa transients
        $transients = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE %s AND option_name LIKE '_transient_%'",
            self::CACHE_PREFIX . '%'
        ));
        
        foreach ($transients as $transient) {
            $key = str_replace('_transient_', '', $transient);
            if (delete_transient($key)) {
                $deleted++;
            }
        }
        
        $this->log_cache_action('clear_all', 'all', $deleted);
        return $deleted;
    }

    /**
     * Limpa cache expirado
     */
    public function clear_expired_cache(): int {
        $deleted = 0;
        
        // Limpa memória
        foreach ($this->memory_cache as $key => $data) {
            if ($data['expires'] <= time()) {
                unset($this->memory_cache[$key]);
                $deleted++;
            }
        }
        
        // WordPress limpa transients expirados automaticamente
        $this->log_cache_action('clear_expired', 'expired', $deleted);
        return $deleted;
    }

    /**
     * Obtém estatísticas do cache
     */
    public function get_stats(): array {
        global $wpdb;
        
        $transient_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->options} 
             WHERE option_name LIKE %s AND option_name LIKE '_transient_%'",
            self::CACHE_PREFIX . '%'
        ));
        
        return [
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'sets' => $this->stats['sets'],
            'deletes' => $this->stats['deletes'],
            'hit_rate' => $this->stats['hits'] + $this->stats['misses'] > 0 
                ? round(($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses'])) * 100, 2)
                : 0,
            'memory_items' => count($this->memory_cache),
            'transient_items' => (int) $transient_count,
        ];
    }

    /**
     * Gera chave de cache
     */
    private function get_cache_key(string $key, string $type): string {
        return self::CACHE_PREFIX . $type . '_' . md5($key);
    }

    /**
     * Obtém tempo de expiração para tipo
     */
    private function get_expiration_time(string $type): int {
        return self::EXPIRATION_TIMES[$type] ?? 3600;
    }

    /**
     * Log de ações do cache
     */
    private function log_cache_action(string $action, string $key, $data = null): void {
        if (defined('CUPOMPROMO_DEBUG') && CUPOMPROMO_DEBUG) {
            error_log(sprintf(
                'Cupompromo Cache: %s - %s - %s',
                $action,
                $key,
                is_scalar($data) ? $data : json_encode($data)
            ));
        }
    }

    /**
     * Hooks para limpeza automática
     */
    public function clear_coupons_cache(): void {
        $this->clear_type('coupons');
        $this->clear_type('search');
    }

    public function clear_stores_cache(): void {
        $this->clear_type('stores');
    }

    public function clear_categories_cache(): void {
        $this->clear_type('categories');
    }

    /**
     * Cache de cupons com filtros
     */
    public function cache_coupons(array $filters, array $coupons, int $expiration = null): bool {
        $key = 'coupons_' . md5(serialize($filters));
        return $this->set($key, $coupons, 'coupons', $expiration);
    }

    /**
     * Cache de lojas
     */
    public function cache_stores(array $stores, int $expiration = null): bool {
        return $this->set('all_stores', $stores, 'stores', $expiration);
    }

    /**
     * Cache de categorias
     */
    public function cache_categories(array $categories, int $expiration = null): bool {
        return $this->set('all_categories', $categories, 'categories', $expiration);
    }

    /**
     * Cache de resultados de busca
     */
    public function cache_search_results(string $query, array $filters, array $results, int $expiration = null): bool {
        $key = 'search_' . md5($query . serialize($filters));
        return $this->set($key, $results, 'search', $expiration);
    }

    /**
     * Cache de dados de usuário
     */
    public function cache_user_data(int $user_id, array $data, int $expiration = null): bool {
        $key = 'user_' . $user_id;
        return $this->set($key, $data, 'user_data', $expiration);
    }

    /**
     * Cache de respostas de API
     */
    public function cache_api_response(string $endpoint, array $params, $response, int $expiration = null): bool {
        $key = 'api_' . md5($endpoint . serialize($params));
        return $this->set($key, $response, 'api_responses', $expiration);
    }

    /**
     * Invalida cache específico
     */
    public function invalidate_cache(string $type, string $identifier = ''): bool {
        if (empty($identifier)) {
            return $this->clear_type($type) > 0;
        }
        
        return $this->delete($identifier, $type);
    }

    /**
     * Warm up cache (pré-carrega dados importantes)
     */
    public function warm_up_cache(): void {
        // Carrega cupons populares
        $popular_coupons = get_posts([
            'post_type' => 'cupompromo_coupon',
            'posts_per_page' => 20,
            'meta_key' => '_click_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ]);
        
        $this->cache_coupons(['popular' => true], $popular_coupons);
        
        // Carrega lojas em destaque
        $featured_stores = get_posts([
            'post_type' => 'cupompromo_store',
            'posts_per_page' => 10,
            'meta_query' => [
                [
                    'key' => '_featured_store',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ]);
        
        $this->cache_stores($featured_stores);
        
        // Carrega categorias
        $categories = get_terms([
            'taxonomy' => 'cupompromo_category',
            'hide_empty' => false
        ]);
        
        $this->cache_categories($categories);
    }
} 