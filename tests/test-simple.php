<?php
/**
 * Teste simples para verificar se o PHPUnit está funcionando
 *
 * @package Cupompromo
 * @since 1.0.0
 */

/**
 * Classe de teste simples
 */
class Cupompromo_Simple_Test extends PHPUnit\Framework\TestCase {
    
    /**
     * Teste básico de funcionamento
     */
    public function test_basic_functionality() {
        $this->assertTrue(true, 'Teste básico deve passar');
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
     * Teste de funções helper
     */
    public function test_helper_functions() {
        // Testa se as funções helper existem
        $this->assertTrue(function_exists('cupompromo_create_test_data'), 'Função cupompromo_create_test_data deve existir');
        $this->assertTrue(function_exists('cupompromo_cleanup_test_data'), 'Função cupompromo_cleanup_test_data deve existir');
        $this->assertTrue(function_exists('cupompromo_mock_awin_data'), 'Função cupompromo_mock_awin_data deve existir');
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
     * Teste de matemática básica
     */
    public function test_basic_math() {
        $this->assertEquals(4, 2 + 2, '2 + 2 deve ser 4');
        $this->assertEquals(0, 2 - 2, '2 - 2 deve ser 0');
        $this->assertEquals(4, 2 * 2, '2 * 2 deve ser 4');
        $this->assertEquals(1, 2 / 2, '2 / 2 deve ser 1');
    }
    
    /**
     * Teste de strings
     */
    public function test_strings() {
        $string = 'Cupompromo';
        $this->assertEquals('Cupompromo', $string, 'String deve ser Cupompromo');
        $this->assertEquals(10, strlen($string), 'String deve ter 10 caracteres');
        $this->assertStringContainsString('promo', $string, 'String deve conter "promo"');
    }
    
    /**
     * Teste de arrays
     */
    public function test_arrays() {
        $array = array('cupom', 'promo', 'desconto');
        $this->assertIsArray($array, 'Deve ser um array');
        $this->assertCount(3, $array, 'Array deve ter 3 elementos');
        $this->assertContains('cupom', $array, 'Array deve conter "cupom"');
    }
} 