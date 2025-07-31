<?php
/**
 * Bootstrap para testes do Cupompromo
 *
 * @package Cupompromo
 * @since 1.0.0
 */

// Define constantes necessárias
if (!defined('CUPOMPROMO_PLUGIN_PATH')) {
    define('CUPOMPROMO_PLUGIN_PATH', dirname(__DIR__));
}

if (!defined('CUPOMPROMO_VERSION')) {
    define('CUPOMPROMO_VERSION', '1.0.0');
}

// Carrega o autoloader do Composer
require_once CUPOMPROMO_PLUGIN_PATH . '/vendor/autoload.php';

// Verifica se o WordPress Test Suite está disponível
if (!getenv('WP_TESTS_DIR')) {
    putenv('WP_TESTS_DIR=/tmp/wordpress-tests-lib');
}

if (!getenv('WP_CORE_DIR')) {
    putenv('WP_CORE_DIR=/tmp/wordpress/');
}

// Carrega o WordPress Test Suite
require_once getenv('WP_TESTS_DIR') . '/includes/functions.php';
require_once getenv('WP_TESTS_DIR') . '/includes/bootstrap.php';

// Carrega o plugin principal
require_once CUPOMPROMO_PLUGIN_PATH . '/cupompromo.php';

// Função helper para criar dados de teste
function cupompromo_create_test_data() {
    // Cria lojas de teste
    $store_data = array(
        'post_title' => 'Loja Teste',
        'post_content' => 'Descrição da loja teste',
        'post_status' => 'publish',
        'post_type' => 'cupompromo_store',
        'meta_input' => array(
            '_store_website' => 'https://lojatest.com',
            '_store_description' => 'Loja para testes',
            '_featured_store' => 0,
            '_default_commission' => 5.0
        )
    );
    
    $store_id = wp_insert_post($store_data);
    
    // Cria cupons de teste
    $coupon_data = array(
        'post_title' => 'Cupom Teste',
        'post_content' => 'Descrição do cupom teste',
        'post_status' => 'publish',
        'post_type' => 'cupompromo_coupon',
        'meta_input' => array(
            '_coupon_type' => 'code',
            '_coupon_code' => 'TESTE10',
            '_affiliate_url' => 'https://lojatest.com/cupom',
            '_discount_value' => '10%',
            '_discount_type' => 'percentage',
            '_store_id' => $store_id,
            '_click_count' => 0,
            '_usage_count' => 0,
            '_verified_date' => current_time('mysql')
        )
    );
    
    $coupon_id = wp_insert_post($coupon_data);
    
    return array(
        'store_id' => $store_id,
        'coupon_id' => $coupon_id
    );
}

// Função helper para limpar dados de teste
function cupompromo_cleanup_test_data() {
    // Remove posts de teste
    $test_posts = get_posts(array(
        'post_type' => array('cupompromo_store', 'cupompromo_coupon'),
        'post_status' => 'any',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_test_data',
                'value' => '1'
            )
        )
    ));
    
    foreach ($test_posts as $post) {
        wp_delete_post($post->ID, true);
    }
}

// Função helper para mock de dados
function cupompromo_mock_awin_data() {
    return array(
        'id' => 12345,
        'merchant' => array(
            'id' => 678,
            'name' => 'Loja Teste Awin',
            'url' => 'https://lojatest.com',
            'description' => 'Loja para testes da Awin',
            'commission' => 5.0
        ),
        'voucher' => array(
            'code' => 'AWIN10',
            'description' => '10% OFF em produtos',
            'url' => 'https://awin.com/click/123',
            'endDate' => '2024-12-31T23:59:59Z',
            'active' => true
        )
    );
}

// Configurações globais para testes
if (!function_exists('cupompromo_test_setup')) {
    function cupompromo_test_setup() {
        // Configurações específicas para testes
        update_option('cupompromo_awin_api_key', 'test_api_key');
        update_option('cupompromo_awin_publisher_id', 'test_publisher');
        update_option('cupompromo_currency', 'BRL');
        update_option('cupompromo_primary_color', '#622599');
        update_option('cupompromo_secondary_color', '#8BC53F');
    }
}

if (!function_exists('cupompromo_test_teardown')) {
    function cupompromo_test_teardown() {
        // Limpeza após testes
        delete_option('cupompromo_awin_api_key');
        delete_option('cupompromo_awin_publisher_id');
        delete_option('cupompromo_currency');
        delete_option('cupompromo_primary_color');
        delete_option('cupompromo_secondary_color');
        
        // Limpa cache
        wp_cache_flush();
    }
}

// Hooks para setup/teardown automático
add_action('setUp', 'cupompromo_test_setup');
add_action('tearDown', 'cupompromo_test_teardown'); 