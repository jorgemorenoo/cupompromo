<?php
/**
 * Classe da API REST do plugin Cupompromo
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
 * Classe Cupompromo_API
 */
class Cupompromo_API {
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Registra as rotas da API
     */
    public function register_routes(): void {
        // Endpoint para cupons
        register_rest_route('cupompromo/v1', '/coupons', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_coupons'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'store_id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                    'coupon_type' => array(
                        'validate_callback' => function($param) {
                            return in_array($param, array('code', 'offer'));
                        }
                    ),
                    'discount_type' => array(
                        'validate_callback' => function($param) {
                            return in_array($param, array('percentage', 'fixed'));
                        }
                    ),
                    'search' => array(
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'per_page' => array(
                        'default' => 10,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0 && $param <= 100;
                        }
                    ),
                    'page' => array(
                        'default' => 1,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        }
                    )
                )
            )
        ));
        
        // Endpoint para lojas
        register_rest_route('cupompromo/v1', '/stores', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_stores'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'featured' => array(
                        'validate_callback' => function($param) {
                            return in_array($param, array('true', 'false', '1', '0'));
                        }
                    ),
                    'per_page' => array(
                        'default' => 10,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0 && $param <= 100;
                        }
                    ),
                    'page' => array(
                        'default' => 1,
                        'validate_callback' => function($param) {
                            return is_numeric($param) && $param > 0;
                        }
                    )
                )
            )
        ));
        
        // Endpoint para validar cupom
        register_rest_route('cupompromo/v1', '/validate-coupon', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'validate_coupon'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'coupon_code' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field'
                    )
                )
            )
        ));
        
        // Endpoint para analytics
        register_rest_route('cupompromo/v1', '/analytics', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'log_analytics'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'coupon_id' => array(
                        'required' => true,
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                    'action_type' => array(
                        'required' => true,
                        'validate_callback' => function($param) {
                            return in_array($param, array('click', 'conversion'));
                        }
                    )
                )
            )
        ));
        
        // Endpoint para estatísticas
        register_rest_route('cupompromo/v1', '/stats', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_stats'),
                'permission_callback' => '__return_true'
            )
        ));
    }
    
    /**
     * Obtém cupons
     */
    public function get_coupons(WP_REST_Request $request): WP_REST_Response {
        $cupompromo = Cupompromo::get_instance();
        
        $search = $request->get_param('search');
        $filters = array();
        
        if ($request->get_param('store_id')) {
            $filters['store_id'] = intval($request->get_param('store_id'));
        }
        
        if ($request->get_param('coupon_type')) {
            $filters['coupon_type'] = $request->get_param('coupon_type');
        }
        
        if ($request->get_param('discount_type')) {
            $filters['discount_type'] = $request->get_param('discount_type');
        }
        
        $per_page = intval($request->get_param('per_page'));
        $page = intval($request->get_param('page'));
        
        $results = $cupompromo->search_coupons($search, $filters);
        
        // Paginação
        $total = count($results);
        $total_pages = ceil($total / $per_page);
        $offset = ($page - 1) * $per_page;
        $results = array_slice($results, $offset, $per_page);
        
        $response = array(
            'coupons' => $results,
            'pagination' => array(
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $total_pages
            )
        );
        
        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Obtém lojas
     */
    public function get_stores(WP_REST_Request $request): WP_REST_Response {
        global $wpdb;
        
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $where_conditions = array('status = "active"');
        $where_values = array();
        
        if ($request->get_param('featured')) {
            $featured = $request->get_param('featured') === 'true' || $request->get_param('featured') === '1';
            $where_conditions[] = 'featured_store = %d';
            $where_values[] = $featured ? 1 : 0;
        }
        
        $per_page = intval($request->get_param('per_page'));
        $page = intval($request->get_param('page'));
        $offset = ($page - 1) * $per_page;
        
        $sql = "SELECT * FROM $table_stores WHERE " . implode(' AND ', $where_conditions) . " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $stores = $wpdb->get_results($wpdb->prepare($sql, ...$where_values));
        
        // Contar total
        $count_sql = "SELECT COUNT(*) FROM $table_stores WHERE " . implode(' AND ', $where_conditions);
        $total = $wpdb->get_var($wpdb->prepare($count_sql, ...array_slice($where_values, 0, -2)));
        
        $total_pages = ceil($total / $per_page);
        
        $response = array(
            'stores' => $stores,
            'pagination' => array(
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $total_pages
            )
        );
        
        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Valida um cupom
     */
    public function validate_coupon(WP_REST_Request $request): WP_REST_Response {
        $coupon_code = $request->get_param('coupon_code');
        
        if (empty($coupon_code)) {
            return new WP_REST_Response(array(
                'valid' => false,
                'message' => __('Código do cupom é obrigatório.', 'cupompromo')
            ), 400);
        }
        
        $cupompromo = Cupompromo::get_instance();
        $validation = $cupompromo->validate_coupon_code($coupon_code);
        
        if ($validation['valid']) {
            return new WP_REST_Response($validation, 200);
        } else {
            return new WP_REST_Response($validation, 400);
        }
    }
    
    /**
     * Registra analytics
     */
    public function log_analytics(WP_REST_Request $request): WP_REST_Response {
        $coupon_id = intval($request->get_param('coupon_id'));
        $action_type = $request->get_param('action_type');
        
        $cupompromo = Cupompromo::get_instance();
        $result = $cupompromo->log_analytics($coupon_id, $action_type);
        
        if ($result) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Analytics registrado com sucesso.', 'cupompromo')
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Erro ao registrar analytics.', 'cupompromo')
            ), 500);
        }
    }
    
    /**
     * Obtém estatísticas
     */
    public function get_stats(WP_REST_Request $request): WP_REST_Response {
        $cupompromo = Cupompromo::get_instance();
        $stats = $cupompromo->get_stats();
        
        return new WP_REST_Response($stats, 200);
    }
} 