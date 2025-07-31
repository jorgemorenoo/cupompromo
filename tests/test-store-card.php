<?php
/**
 * Testes unitários para Cupompromo_Store_Card
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
 * Classe de testes para Cupompromo_Store_Card
 */
class Cupompromo_Store_Card_Test extends WP_UnitTestCase {
    
    /**
     * Dados de teste da loja
     */
    private $test_store;
    
    /**
     * Setup dos testes
     */
    public function setUp(): void {
        parent::setUp();
        
        // Dados de teste
        $this->test_store = (object) array(
            'id' => 1,
            'name' => 'Amazon Brasil',
            'slug' => 'amazon-brasil',
            'logo_url' => 'https://exemplo.com/amazon-logo.png',
            'store_description' => 'A maior loja online do mundo com milhões de produtos.',
            'store_website' => 'https://amazon.com.br',
            'featured_store' => 1,
            'default_commission' => 5.0,
            'status' => 'active'
        );
    }
    
    /**
     * Testa criação da classe
     */
    public function test_store_card_creation() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        
        $this->assertInstanceOf('Cupompromo_Store_Card', $store_card);
        $this->assertEquals($this->test_store, $store_card->get_store());
    }
    
    /**
     * Testa criação com dados inválidos
     */
    public function test_store_card_invalid_data() {
        $invalid_store = (object) array(
            'name' => 'Loja sem ID'
        );
        
        $this->expectException(InvalidArgumentException::class);
        new Cupompromo_Store_Card($invalid_store);
    }
    
    /**
     * Testa configurações padrão
     */
    public function test_default_config() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $config = $store_card->get_config();
        
        $this->assertTrue($config['show_logo']);
        $this->assertTrue($config['show_description']);
        $this->assertTrue($config['show_stats']);
        $this->assertEquals('default', $config['card_style']);
        $this->assertEquals('medium', $config['logo_size']);
    }
    
    /**
     * Testa configurações customizadas
     */
    public function test_custom_config() {
        $custom_config = array(
            'card_style' => 'featured',
            'logo_size' => 'large',
            'show_description' => false
        );
        
        $store_card = new Cupompromo_Store_Card($this->test_store, $custom_config);
        $config = $store_card->get_config();
        
        $this->assertEquals('featured', $config['card_style']);
        $this->assertEquals('large', $config['logo_size']);
        $this->assertFalse($config['show_description']);
    }
    
    /**
     * Testa renderização básica
     */
    public function test_basic_render() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render();
        
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('cupompromo-store-card', $html);
        $this->assertStringContainsString('Amazon Brasil', $html);
        $this->assertStringContainsString('amazon-brasil', $html);
    }
    
    /**
     * Testa renderização minimalista
     */
    public function test_minimal_render() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render_minimal();
        
        $this->assertStringContainsString('card-style-minimal', $html);
        $this->assertStringNotContainsString('store-description', $html);
        $this->assertStringNotContainsString('store-stats', $html);
    }
    
    /**
     * Testa renderização em destaque
     */
    public function test_featured_render() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render_featured();
        
        $this->assertStringContainsString('card-style-featured', $html);
        $this->assertStringContainsString('logo-size-large', $html);
    }
    
    /**
     * Testa renderização compacta
     */
    public function test_compact_render() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render_compact();
        
        $this->assertStringContainsString('card-style-compact', $html);
        $this->assertStringContainsString('logo-size-small', $html);
    }
    
    /**
     * Testa renderização horizontal
     */
    public function test_horizontal_render() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render_horizontal();
        
        $this->assertStringContainsString('card-style-horizontal', $html);
        $this->assertStringContainsString('logo-size-small', $html);
    }
    
    /**
     * Testa métodos de status
     */
    public function test_status_methods() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        
        $this->assertTrue($store_card->is_active());
        $this->assertTrue($store_card->is_featured());
    }
    
    /**
     * Testa loja inativa
     */
    public function test_inactive_store() {
        $inactive_store = clone $this->test_store;
        $inactive_store->status = 'inactive';
        $inactive_store->featured_store = 0;
        
        $store_card = new Cupompromo_Store_Card($inactive_store);
        
        $this->assertFalse($store_card->is_active());
        $this->assertFalse($store_card->is_featured());
    }
    
    /**
     * Testa resumo da loja
     */
    public function test_store_summary() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $summary = $store_card->get_summary();
        
        $this->assertEquals(1, $summary['id']);
        $this->assertEquals('Amazon Brasil', $summary['name']);
        $this->assertEquals('amazon-brasil', $summary['slug']);
        $this->assertTrue($summary['featured']);
        $this->assertTrue($summary['active']);
        $this->assertEquals(5.0, $summary['commission']);
    }
    
    /**
     * Testa dados JSON
     */
    public function test_json_data() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $json_data = $store_card->to_json();
        
        $this->assertArrayHasKey('store', $json_data);
        $this->assertArrayHasKey('config', $json_data);
        $this->assertArrayHasKey('html', $json_data);
        $this->assertEquals('Amazon Brasil', $json_data['store']['name']);
    }
    
    /**
     * Testa cache de instância
     */
    public function test_instance_cache() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        
        // Primeira chamada
        $summary1 = $store_card->get_summary();
        
        // Segunda chamada (deve usar cache)
        $summary2 = $store_card->get_summary();
        
        $this->assertEquals($summary1, $summary2);
    }
    
    /**
     * Testa limpeza de cache
     */
    public function test_cache_clear() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        
        // Primeira chamada
        $summary1 = $store_card->get_summary();
        
        // Limpa cache
        $store_card->clear_cache();
        
        // Segunda chamada (deve recalcular)
        $summary2 = $store_card->get_summary();
        
        $this->assertEquals($summary1, $summary2); // Dados devem ser iguais
    }
    
    /**
     * Testa alteração de configurações
     */
    public function test_config_change() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        
        $original_config = $store_card->get_config();
        $store_card->set_config(array('card_style' => 'featured'));
        $new_config = $store_card->get_config();
        
        $this->assertEquals('default', $original_config['card_style']);
        $this->assertEquals('featured', $new_config['card_style']);
    }
    
    /**
     * Testa geração de cor do placeholder
     */
    public function test_color_generation() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        
        // Testa com loja sem logo
        $store_no_logo = clone $this->test_store;
        $store_no_logo->logo_url = '';
        
        $store_card_no_logo = new Cupompromo_Store_Card($store_no_logo);
        $html = $store_card_no_logo->render();
        
        $this->assertStringContainsString('store-logo-placeholder', $html);
        $this->assertStringContainsString('A', $html); // Inicial da Amazon
    }
    
    /**
     * Testa truncamento de texto
     */
    public function test_text_truncation() {
        $long_description_store = clone $this->test_store;
        $long_description_store->store_description = 'Esta é uma descrição muito longa que deve ser truncada para caber no card da loja. Ela contém mais de cem caracteres para testar a funcionalidade de truncamento.';
        
        $store_card = new Cupompromo_Store_Card($long_description_store);
        $html = $store_card->render();
        
        $this->assertStringContainsString('...', $html);
    }
    
    /**
     * Testa dados estruturados
     */
    public function test_data_attributes() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render();
        
        $this->assertStringContainsString('data-store-id="1"', $html);
        $this->assertStringContainsString('data-store-slug="amazon-brasil"', $html);
        $this->assertStringContainsString('data-featured="true"', $html);
        $this->assertStringContainsString('data-active="true"', $html);
    }
    
    /**
     * Testa acessibilidade
     */
    public function test_accessibility() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render();
        
        // Verifica ARIA labels
        $this->assertStringContainsString('aria-label', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        
        // Verifica alt text
        $this->assertStringContainsString('alt="Amazon Brasil"', $html);
        
        // Verifica rel="nofollow"
        $this->assertStringContainsString('rel="nofollow"', $html);
    }
    
    /**
     * Testa classes CSS
     */
    public function test_css_classes() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render();
        
        $this->assertStringContainsString('cupompromo-store-card', $html);
        $this->assertStringContainsString('card-style-default', $html);
        $this->assertStringContainsString('logo-size-medium', $html);
        $this->assertStringContainsString('is-featured', $html);
        $this->assertStringContainsString('has-animation', $html);
    }
    
    /**
     * Testa classes CSS customizadas
     */
    public function test_custom_css_classes() {
        $custom_config = array(
            'css_classes' => array('my-custom-class', 'another-class')
        );
        
        $store_card = new Cupompromo_Store_Card($this->test_store, $custom_config);
        $html = $store_card->render();
        
        $this->assertStringContainsString('my-custom-class', $html);
        $this->assertStringContainsString('another-class', $html);
    }
    
    /**
     * Testa URL da loja
     */
    public function test_store_url() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render();
        
        $this->assertStringContainsString('/loja/amazon-brasil', $html);
    }
    
    /**
     * Testa loja sem slug
     */
    public function test_store_without_slug() {
        $store_no_slug = clone $this->test_store;
        unset($store_no_slug->slug);
        
        $store_card = new Cupompromo_Store_Card($store_no_slug);
        $html = $store_card->render();
        
        $this->assertStringContainsString('/loja/amazon-brasil', $html); // Deve gerar slug do nome
    }
    
    /**
     * Testa estatísticas da loja
     */
    public function test_store_stats() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $stats = $store_card->get_stats();
        
        $this->assertArrayHasKey('coupons_count', $stats);
        $this->assertArrayHasKey('avg_discount', $stats);
        $this->assertArrayHasKey('has_active_coupons', $stats);
        $this->assertArrayHasKey('is_featured', $stats);
        $this->assertArrayHasKey('is_active', $stats);
    }
    
    /**
     * Testa verificação de cupons ativos
     */
    public function test_has_active_coupons() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $has_coupons = $store_card->has_active_coupons();
        
        $this->assertIsBool($has_coupons);
    }
    
    /**
     * Testa configurações de cache
     */
    public function test_cache_config() {
        $no_cache_config = array(
            'enable_cache' => false
        );
        
        $store_card = new Cupompromo_Store_Card($this->test_store, $no_cache_config);
        $config = $store_card->get_config();
        
        $this->assertFalse($config['enable_cache']);
    }
    
    /**
     * Testa configurações de acessibilidade
     */
    public function test_accessibility_config() {
        $store_card = new Cupompromo_Store_Card($this->test_store);
        $html = $store_card->render();
        
        // Verifica se os ícones têm aria-hidden
        $this->assertStringContainsString('aria-hidden="true"', $html);
        
        // Verifica se os links têm aria-label
        $this->assertStringContainsString('aria-label', $html);
    }
    
    /**
     * Testa fallback de logo
     */
    public function test_logo_fallback() {
        $store_with_broken_logo = clone $this->test_store;
        $store_with_broken_logo->logo_url = 'https://exemplo.com/broken-logo.png';
        
        $store_card = new Cupompromo_Store_Card($store_with_broken_logo);
        $html = $store_card->render();
        
        // Verifica se tem onerror para fallback
        $this->assertStringContainsString('onerror', $html);
        $this->assertStringContainsString('store-logo-placeholder', $html);
    }
    
    /**
     * Testa configurações de truncamento
     */
    public function test_truncation_config() {
        $no_truncate_config = array(
            'truncate_description' => false
        );
        
        $long_description_store = clone $this->test_store;
        $long_description_store->store_description = 'Esta é uma descrição muito longa que não deve ser truncada mesmo tendo mais de cem caracteres.';
        
        $store_card = new Cupompromo_Store_Card($long_description_store, $no_truncate_config);
        $html = $store_card->render();
        
        $this->assertStringNotContainsString('...', $html);
    }
} 