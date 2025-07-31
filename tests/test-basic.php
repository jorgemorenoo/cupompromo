<?php
/**
 * Teste básico para verificar se o PHPUnit está funcionando
 *
 * @package Cupompromo
 * @since 1.0.0
 */

/**
 * Classe de teste básico
 */
class Cupompromo_Basic_Test extends WP_UnitTestCase {
    
    /**
     * Setup do teste
     */
    public function setUp(): void {
        parent::setUp();
        
        // Configurações básicas para teste
        update_option('cupompromo_awin_api_key', 'test_key');
        update_option('cupompromo_currency', 'BRL');
    }
    
    /**
     * Teardown do teste
     */
    public function tearDown(): void {
        // Limpa configurações de teste
        delete_option('cupompromo_awin_api_key');
        delete_option('cupompromo_currency');
        
        parent::tearDown();
    }
    
    /**
     * Teste básico de funcionamento
     */
    public function test_basic_functionality() {
        $this->assertTrue(true, 'Teste básico deve passar');
    }
    
    /**
     * Teste de configurações do plugin
     */
    public function test_plugin_configuration() {
        $api_key = get_option('cupompromo_awin_api_key');
        $currency = get_option('cupompromo_currency');
        
        $this->assertEquals('test_key', $api_key, 'API key deve ser configurada');
        $this->assertEquals('BRL', $currency, 'Moeda deve ser BRL');
    }
    
    /**
     * Teste de constantes do plugin
     */
    public function test_plugin_constants() {
        $this->assertTrue(defined('CUPOMPROMO_PLUGIN_PATH'), 'CUPOMPROMO_PLUGIN_PATH deve estar definida');
        $this->assertTrue(defined('CUPOMPROMO_VERSION'), 'CUPOMPROMO_VERSION deve estar definida');
        $this->assertNotEmpty(CUPOMPROMO_VERSION, 'CUPOMPROMO_VERSION não deve estar vazia');
    }
    
    /**
     * Teste de criação de dados de teste
     */
    public function test_create_test_data() {
        $test_data = cupompromo_create_test_data();
        
        $this->assertIsArray($test_data, 'Dados de teste devem ser um array');
        $this->assertArrayHasKey('store_id', $test_data, 'Deve ter store_id');
        $this->assertArrayHasKey('coupon_id', $test_data, 'Deve ter coupon_id');
        $this->assertGreaterThan(0, $test_data['store_id'], 'store_id deve ser maior que 0');
        $this->assertGreaterThan(0, $test_data['coupon_id'], 'coupon_id deve ser maior que 0');
        
        // Verifica se os posts foram criados
        $store = get_post($test_data['store_id']);
        $coupon = get_post($test_data['coupon_id']);
        
        $this->assertNotNull($store, 'Loja deve existir');
        $this->assertNotNull($coupon, 'Cupom deve existir');
        $this->assertEquals('cupompromo_store', $store->post_type, 'Post type da loja deve ser correto');
        $this->assertEquals('cupompromo_coupon', $coupon->post_type, 'Post type do cupom deve ser correto');
        
        // Limpa dados de teste
        wp_delete_post($test_data['store_id'], true);
        wp_delete_post($test_data['coupon_id'], true);
    }
    
    /**
     * Teste de mock de dados Awin
     */
    public function test_mock_awin_data() {
        $mock_data = cupompromo_mock_awin_data();
        
        $this->assertIsArray($mock_data, 'Dados mock devem ser um array');
        $this->assertArrayHasKey('id', $mock_data, 'Deve ter id');
        $this->assertArrayHasKey('merchant', $mock_data, 'Deve ter merchant');
        $this->assertArrayHasKey('voucher', $mock_data, 'Deve ter voucher');
        
        $this->assertEquals(12345, $mock_data['id'], 'ID deve ser 12345');
        $this->assertEquals('Loja Teste Awin', $mock_data['merchant']['name'], 'Nome da loja deve ser correto');
        $this->assertEquals('AWIN10', $mock_data['voucher']['code'], 'Código do cupom deve ser correto');
    }
    
    /**
     * Teste de funções helper
     */
    public function test_helper_functions() {
        // Testa se as funções helper existem
        $this->assertTrue(function_exists('cupompromo_create_test_data'), 'Função cupompromo_create_test_data deve existir');
        $this->assertTrue(function_exists('cupompromo_cleanup_test_data'), 'Função cupompromo_cleanup_test_data deve existir');
        $this->assertTrue(function_exists('cupompromo_mock_awin_data'), 'Função cupompromo_mock_awin_data deve existir');
    }
    
    /**
     * Teste de configurações de teste
     */
    public function test_test_configuration() {
        // Verifica se as configurações de teste foram aplicadas
        $this->assertEquals('test_api_key', get_option('cupompromo_awin_api_key'), 'API key de teste deve estar configurada');
        $this->assertEquals('test_publisher', get_option('cupompromo_awin_publisher_id'), 'Publisher ID de teste deve estar configurado');
        $this->assertEquals('BRL', get_option('cupompromo_currency'), 'Moeda de teste deve ser BRL');
    }
} 