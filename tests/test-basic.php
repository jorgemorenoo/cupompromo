<?php
/**
 * Testes básicos do Cupompromo
 *
 * @package Cupompromo
 * @since 1.0.0
 */

class Cupompromo_Basic_Test extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        
        // Configurar dados de teste
        cupompromo_test_setup();
    }

    public function tearDown(): void {
        // Limpar dados de teste
        cupompromo_test_teardown();
        
        parent::tearDown();
    }

    /**
     * Teste básico para verificar se o plugin está carregado
     */
    public function test_plugin_loaded() {
        $this->assertTrue(defined('CUPOMPROMO_VERSION'));
        $this->assertEquals('1.0.0', CUPOMPROMO_VERSION);
    }

    /**
     * Teste para verificar se as classes principais existem
     */
    public function test_main_classes_exist() {
        $this->assertTrue(class_exists('Cupompromo_Rest_Client'));
        $this->assertTrue(class_exists('Cupompromo_API_Manager'));
        $this->assertTrue(class_exists('Cupompromo_APIs_Dashboard'));
        $this->assertTrue(class_exists('Cupompromo_Awin_API'));
    }

    /**
     * Teste para verificar se as opções padrão estão definidas
     */
    public function test_default_options() {
        $this->assertEquals('BRL', get_option('cupompromo_currency'));
        $this->assertEquals('#622599', get_option('cupompromo_primary_color'));
        $this->assertEquals('#8BC53F', get_option('cupompromo_secondary_color'));
    }

    /**
     * Teste para verificar se os post types estão registrados
     */
    public function test_post_types_registered() {
        $post_types = get_post_types();
        
        $this->assertArrayHasKey('cupompromo_store', $post_types);
        $this->assertArrayHasKey('cupompromo_coupon', $post_types);
    }

    /**
     * Teste para verificar se as taxonomias estão registradas
     */
    public function test_taxonomies_registered() {
        $taxonomies = get_taxonomies();
        
        $this->assertArrayHasKey('cupompromo_category', $taxonomies);
        $this->assertArrayHasKey('cupompromo_store_type', $taxonomies);
    }

    /**
     * Teste para verificar se o RestClient funciona
     */
    public function test_rest_client_initialization() {
        $client = new Cupompromo_Rest_Client();
        
        $this->assertInstanceOf('Cupompromo_Rest_Client', $client);
        $this->assertNull($client->get_uri());
    }

    /**
     * Teste para verificar se o API Manager funciona
     */
    public function test_api_manager_initialization() {
        $manager = new Cupompromo_API_Manager();
        
        $this->assertInstanceOf('Cupompromo_API_Manager', $manager);
    }

    /**
     * Teste para verificar se a API Awin funciona
     */
    public function test_awin_api_initialization() {
        $api = new Cupompromo_Awin_API();
        
        $this->assertInstanceOf('Cupompromo_Awin_API', $api);
        $this->assertInstanceOf('Cupompromo_Rest_Client', $api);
    }

    /**
     * Teste para verificar se as funções helper existem
     */
    public function test_helper_functions_exist() {
        $this->assertTrue(function_exists('cupompromo_create_test_data'));
        $this->assertTrue(function_exists('cupompromo_cleanup_test_data'));
        $this->assertTrue(function_exists('cupompromo_mock_awin_data'));
    }

    /**
     * Teste para verificar se os dados de teste podem ser criados
     */
    public function test_test_data_creation() {
        $test_data = cupompromo_create_test_data();
        
        $this->assertIsArray($test_data);
        $this->assertArrayHasKey('store_id', $test_data);
        $this->assertArrayHasKey('coupon_id', $test_data);
        $this->assertGreaterThan(0, $test_data['store_id']);
        $this->assertGreaterThan(0, $test_data['coupon_id']);
        
        // Verificar se os posts foram criados
        $store = get_post($test_data['store_id']);
        $coupon = get_post($test_data['coupon_id']);
        
        $this->assertNotNull($store);
        $this->assertNotNull($coupon);
        $this->assertEquals('cupompromo_store', $store->post_type);
        $this->assertEquals('cupompromo_coupon', $coupon->post_type);
    }

    /**
     * Teste para verificar se os dados mock funcionam
     */
    public function test_mock_data() {
        $mock_data = cupompromo_mock_awin_data();
        
        $this->assertIsArray($mock_data);
        $this->assertArrayHasKey('id', $mock_data);
        $this->assertArrayHasKey('merchant', $mock_data);
        $this->assertArrayHasKey('voucher', $mock_data);
        $this->assertEquals(12345, $mock_data['id']);
    }

    /**
     * Teste para verificar se as configurações de teste funcionam
     */
    public function test_test_configuration() {
        // Verificar se as configurações foram aplicadas
        $this->assertEquals('test_api_key', get_option('cupompromo_awin_api_key'));
        $this->assertEquals('test_publisher', get_option('cupompromo_awin_publisher_id'));
        
        // Verificar se a limpeza funciona
        cupompromo_test_teardown();
        
        $this->assertFalse(get_option('cupompromo_awin_api_key'));
        $this->assertFalse(get_option('cupompromo_awin_publisher_id'));
    }
} 