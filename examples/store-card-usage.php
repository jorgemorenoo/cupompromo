<?php
/**
 * Exemplo de uso da classe Cupompromo_Store_Card
 * 
 * Este arquivo demonstra como usar a classe Cupompromo_Store_Card
 * em diferentes cenários e configurações.
 *
 * @package Cupompromo
 * @since 1.0.0
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exemplos de uso da classe Cupompromo_Store_Card
 */
class Cupompromo_Store_Card_Examples {
    
    /**
     * Exemplo 1: Uso básico
     */
    public function basic_usage_example() {
        // Dados de exemplo da loja
        $store_data = (object) array(
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
        
        // Criar e renderizar o card
        $store_card = new Cupompromo_Store_Card($store_data);
        echo $store_card->render();
    }
    
    /**
     * Exemplo 2: Card minimalista
     */
    public function minimal_card_example() {
        $store_data = (object) array(
            'id' => 2,
            'name' => 'Mercado Livre',
            'slug' => 'mercado-livre',
            'logo_url' => 'https://exemplo.com/mercadolivre-logo.png',
            'store_description' => 'Plataforma de compra e venda online.',
            'store_website' => 'https://mercadolivre.com.br',
            'featured_store' => 0,
            'default_commission' => 3.5,
            'status' => 'active'
        );
        
        $store_card = new Cupompromo_Store_Card($store_data);
        echo $store_card->render_minimal();
    }
    
    /**
     * Exemplo 3: Card em destaque
     */
    public function featured_card_example() {
        $store_data = (object) array(
            'id' => 3,
            'name' => 'Americanas',
            'slug' => 'americanas',
            'logo_url' => 'https://exemplo.com/americanas-logo.png',
            'store_description' => 'Uma das maiores redes de varejo do Brasil com presença online.',
            'store_website' => 'https://americanas.com.br',
            'featured_store' => 1,
            'default_commission' => 4.0,
            'status' => 'active'
        );
        
        $store_card = new Cupompromo_Store_Card($store_data);
        echo $store_card->render_featured();
    }
    
    /**
     * Exemplo 4: Card compacto
     */
    public function compact_card_example() {
        $store_data = (object) array(
            'id' => 4,
            'name' => 'Magazine Luiza',
            'slug' => 'magazine-luiza',
            'logo_url' => 'https://exemplo.com/magalu-logo.png',
            'store_description' => 'E-commerce brasileiro com ampla variedade de produtos.',
            'store_website' => 'https://magazineluiza.com.br',
            'featured_store' => 0,
            'default_commission' => 3.0,
            'status' => 'active'
        );
        
        $store_card = new Cupompromo_Store_Card($store_data);
        echo $store_card->render_compact();
    }
    
    /**
     * Exemplo 5: Configurações personalizadas
     */
    public function custom_config_example() {
        $store_data = (object) array(
            'id' => 5,
            'name' => 'Netshoes',
            'slug' => 'netshoes',
            'logo_url' => 'https://exemplo.com/netshoes-logo.png',
            'store_description' => 'Especialista em artigos esportivos e moda fitness.',
            'store_website' => 'https://netshoes.com.br',
            'featured_store' => 1,
            'default_commission' => 6.0,
            'status' => 'active'
        );
        
        $custom_config = array(
            'show_logo' => true,
            'show_description' => true,
            'show_stats' => true,
            'show_featured_badge' => true,
            'card_style' => 'featured',
            'logo_size' => 'large',
            'description_length' => 120,
            'link_target' => '_blank',
            'css_classes' => array('custom-store-card', 'highlight'),
            'animation' => true,
            'lazy_loading' => false
        );
        
        $store_card = new Cupompromo_Store_Card($store_data, $custom_config);
        echo $store_card->render();
    }
    
    /**
     * Exemplo 6: Grid de lojas com diferentes estilos
     */
    public function grid_with_different_styles() {
        $stores = $this->get_sample_stores();
        
        ob_start();
        ?>
        <div class="cupompromo-stores-showcase">
            <h2><?php _e('Lojas em Destaque', 'cupompromo'); ?></h2>
            <div class="featured-stores">
                <?php foreach (array_slice($stores, 0, 3) as $store): ?>
                    <?php
                    $store_card = new Cupompromo_Store_Card($store, array(
                        'card_style' => 'featured',
                        'logo_size' => 'large'
                    ));
                    echo $store_card->render();
                    ?>
                <?php endforeach; ?>
            </div>
            
            <h3><?php _e('Outras Lojas', 'cupompromo'); ?></h3>
            <div class="regular-stores">
                <?php foreach (array_slice($stores, 3) as $store): ?>
                    <?php
                    $store_card = new Cupompromo_Store_Card($store, array(
                        'card_style' => 'default',
                        'logo_size' => 'medium'
                    ));
                    echo $store_card->render();
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Exemplo 7: Lista compacta de lojas
     */
    public function compact_stores_list() {
        $stores = $this->get_sample_stores();
        
        ob_start();
        ?>
        <div class="cupompromo-stores-list">
            <?php foreach ($stores as $store): ?>
                <?php
                $store_card = new Cupompromo_Store_Card($store, array(
                    'card_style' => 'compact',
                    'show_description' => false,
                    'show_stats' => false,
                    'show_featured_badge' => false
                ));
                echo $store_card->render();
                ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Exemplo 8: Informações da loja
     */
    public function store_information_example() {
        $store_data = (object) array(
            'id' => 1,
            'name' => 'Amazon Brasil',
            'slug' => 'amazon-brasil',
            'logo_url' => 'https://exemplo.com/amazon-logo.png',
            'store_description' => 'A maior loja online do mundo.',
            'store_website' => 'https://amazon.com.br',
            'featured_store' => 1,
            'default_commission' => 5.0,
            'status' => 'active'
        );
        
        $store_card = new Cupompromo_Store_Card($store_data);
        
        // Obter informações da loja
        $summary = $store_card->get_summary();
        
        echo '<h3>Informações da Loja</h3>';
        echo '<ul>';
        echo '<li><strong>Nome:</strong> ' . esc_html($summary['name']) . '</li>';
        echo '<li><strong>Slug:</strong> ' . esc_html($summary['slug']) . '</li>';
        echo '<li><strong>Website:</strong> ' . esc_html($summary['website']) . '</li>';
        echo '<li><strong>Destaque:</strong> ' . ($summary['featured'] ? 'Sim' : 'Não') . '</li>';
        echo '<li><strong>Ativa:</strong> ' . ($summary['active'] ? 'Sim' : 'Não') . '</li>';
        echo '<li><strong>Total de Cupons:</strong> ' . number_format($summary['coupons_count']) . '</li>';
        echo '<li><strong>Desconto Médio:</strong> ' . number_format($summary['avg_discount'], 1) . '%</li>';
        echo '<li><strong>Comissão:</strong> ' . number_format($summary['commission'], 1) . '%</li>';
        echo '</ul>';
    }
    
    /**
     * Obtém dados de exemplo de lojas
     */
    private function get_sample_stores(): array {
        return array(
            (object) array(
                'id' => 1,
                'name' => 'Amazon Brasil',
                'slug' => 'amazon-brasil',
                'logo_url' => 'https://exemplo.com/amazon-logo.png',
                'store_description' => 'A maior loja online do mundo com milhões de produtos.',
                'store_website' => 'https://amazon.com.br',
                'featured_store' => 1,
                'default_commission' => 5.0,
                'status' => 'active'
            ),
            (object) array(
                'id' => 2,
                'name' => 'Mercado Livre',
                'slug' => 'mercado-livre',
                'logo_url' => 'https://exemplo.com/mercadolivre-logo.png',
                'store_description' => 'Plataforma de compra e venda online.',
                'store_website' => 'https://mercadolivre.com.br',
                'featured_store' => 0,
                'default_commission' => 3.5,
                'status' => 'active'
            ),
            (object) array(
                'id' => 3,
                'name' => 'Americanas',
                'slug' => 'americanas',
                'logo_url' => 'https://exemplo.com/americanas-logo.png',
                'store_description' => 'Uma das maiores redes de varejo do Brasil.',
                'store_website' => 'https://americanas.com.br',
                'featured_store' => 1,
                'default_commission' => 4.0,
                'status' => 'active'
            ),
            (object) array(
                'id' => 4,
                'name' => 'Magazine Luiza',
                'slug' => 'magazine-luiza',
                'logo_url' => 'https://exemplo.com/magalu-logo.png',
                'store_description' => 'E-commerce brasileiro com ampla variedade.',
                'store_website' => 'https://magazineluiza.com.br',
                'featured_store' => 0,
                'default_commission' => 3.0,
                'status' => 'active'
            ),
            (object) array(
                'id' => 5,
                'name' => 'Netshoes',
                'slug' => 'netshoes',
                'logo_url' => 'https://exemplo.com/netshoes-logo.png',
                'store_description' => 'Especialista em artigos esportivos.',
                'store_website' => 'https://netshoes.com.br',
                'featured_store' => 1,
                'default_commission' => 6.0,
                'status' => 'active'
            )
        );
    }
}

// Exemplo de uso
if (class_exists('Cupompromo_Store_Card')) {
    $examples = new Cupompromo_Store_Card_Examples();
    
    // Descomente para testar os exemplos
    // echo $examples->basic_usage_example();
    // echo $examples->minimal_card_example();
    // echo $examples->featured_card_example();
    // echo $examples->compact_card_example();
    // echo $examples->custom_config_example();
    // echo $examples->grid_with_different_styles();
    // echo $examples->compact_stores_list();
    // echo $examples->store_information_example();
} 