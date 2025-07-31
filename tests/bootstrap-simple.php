<?php
/**
 * Bootstrap simples para testes do Cupompromo
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

// Função helper para criar dados de teste
function cupompromo_create_test_data() {
    return array(
        'store_id' => 1,
        'coupon_id' => 2
    );
}

// Função helper para limpar dados de teste
function cupompromo_cleanup_test_data() {
    // Função vazia para testes simples
    return true;
} 