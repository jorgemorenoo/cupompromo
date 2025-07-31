# Cupompromo_Store_Card - Documentação da Classe

## Visão Geral

A classe `Cupompromo_Store_Card` é responsável por renderizar cards de lojas de forma consistente e reutilizável no plugin Cupompromo. Ela segue o design system definido e oferece múltiplas opções de personalização.

## Características Principais

- ✅ **PHP 8.1+** com tipagem estrita
- ✅ **WordPress Coding Standards** e PSR-12
- ✅ **Design System** alinhado com `.cursorrules`
- ✅ **Acessibilidade** (WCAG 2.1 AA)
- ✅ **Responsivo** mobile-first
- ✅ **Cache inteligente** para performance
- ✅ **Múltiplos estilos** de card
- ✅ **Fallback** para logos quebrados
- ✅ **Animações** suaves
- ✅ **SEO-friendly** com dados estruturados

## Uso Básico

```php
// Dados da loja
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

// Criar e renderizar o card
$store_card = new Cupompromo_Store_Card($store_data);
echo $store_card->render();
```

## Configurações Disponíveis

### Configurações Padrão

```php
$config = array(
    'show_logo' => true,              // Exibir logo da loja
    'show_description' => true,        // Exibir descrição
    'show_stats' => true,             // Exibir estatísticas
    'show_featured_badge' => true,    // Exibir badge de destaque
    'show_coupons_count' => true,     // Exibir contagem de cupons
    'card_style' => 'default',        // Estilo do card
    'logo_size' => 'medium',          // Tamanho do logo
    'description_length' => 100,       // Comprimento da descrição
    'link_target' => '_self',         // Target dos links
    'css_classes' => array(),         // Classes CSS adicionais
    'animation' => true,              // Habilitar animações
    'lazy_loading' => true,           // Lazy loading de imagens
    'enable_cache' => true,           // Habilitar cache
    'cache_duration' => 3600,         // Duração do cache (1h)
    'show_commission' => true,        // Exibir comissão
    'show_website_link' => true,      // Exibir link do site
    'truncate_description' => true    // Truncar descrição
);
```

### Estilos de Card Disponíveis

1. **`default`** - Card padrão com todas as informações
2. **`minimal`** - Card minimalista sem descrição e estatísticas
3. **`featured`** - Card em destaque com logo maior
4. **`compact`** - Card compacto para listas
5. **`horizontal`** - Layout horizontal para listas

### Tamanhos de Logo

- **`small`** - 60x60px
- **`medium`** - 80x80px (padrão)
- **`large`** - 120x120px

## Métodos Principais

### Renderização

```php
// Renderização padrão
$store_card->render();

// Renderização minimalista
$store_card->render_minimal();

// Renderização em destaque
$store_card->render_featured();

// Renderização compacta
$store_card->render_compact();

// Renderização horizontal
$store_card->render_horizontal();
```

### Informações e Status

```php
// Verificar se a loja está ativa
$is_active = $store_card->is_active();

// Verificar se é destaque
$is_featured = $store_card->is_featured();

// Verificar se tem cupons ativos
$has_coupons = $store_card->has_active_coupons();

// Obter estatísticas
$stats = $store_card->get_stats();

// Obter resumo da loja
$summary = $store_card->get_summary();

// Obter dados para JSON
$json_data = $store_card->to_json();
```

### Configuração e Cache

```php
// Definir configurações
$store_card->set_config(array(
    'card_style' => 'featured',
    'show_description' => false
));

// Obter configurações atuais
$config = $store_card->get_config();

// Limpar cache da instância
$store_card->clear_cache();
```

## Exemplos de Uso

### 1. Card Padrão

```php
$store_card = new Cupompromo_Store_Card($store_data);
echo $store_card->render();
```

### 2. Card Minimalista

```php
$store_card = new Cupompromo_Store_Card($store_data);
echo $store_card->render_minimal();
```

### 3. Card com Configurações Customizadas

```php
$custom_config = array(
    'card_style' => 'featured',
    'logo_size' => 'large',
    'description_length' => 150,
    'show_commission' => false,
    'css_classes' => array('my-custom-class')
);

$store_card = new Cupompromo_Store_Card($store_data, $custom_config);
echo $store_card->render();
```

### 4. Grid de Lojas

```php
$stores = get_stores_from_database();

echo '<div class="cupompromo-stores-grid" style="--columns: 3">';
foreach ($stores as $store) {
    $store_card = new Cupompromo_Store_Card($store);
    echo $store_card->render();
}
echo '</div>';
```

### 5. Lista Horizontal

```php
$stores = get_stores_from_database();

echo '<div class="stores-horizontal-list">';
foreach ($stores as $store) {
    $store_card = new Cupompromo_Store_Card($store);
    echo $store_card->render_horizontal();
}
echo '</div>';
```

## Estrutura HTML Gerada

```html
<div class="cupompromo-store-card card-style-default logo-size-medium" 
     data-store-id="1" 
     data-store-slug="amazon-brasil" 
     data-featured="true" 
     data-active="true" 
     data-coupons-count="25" 
     data-avg-discount="15">
    
    <!-- Cabeçalho -->
    <div class="store-card-header">
        <div class="store-logo-wrapper">
            <img src="logo.png" alt="Amazon Brasil" class="store-logo store-logo-medium" loading="lazy">
            <div class="store-logo-placeholder" style="display: none;">
                <span class="store-initial">A</span>
            </div>
        </div>
        <div class="featured-badge">
            <span class="badge-icon">⭐</span>
            <span class="badge-text">Destaque</span>
        </div>
    </div>
    
    <!-- Conteúdo -->
    <div class="store-card-content">
        <h3 class="store-name">
            <a href="/loja/amazon-brasil" target="_self" rel="nofollow" title="Amazon Brasil">
                Amazon Brasil
            </a>
        </h3>
        <div class="store-description">
            A maior loja online do mundo com milhões de produtos...
        </div>
        <div class="store-stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-icon" aria-hidden="true">🎫</span>
                    <span class="stat-value">25</span>
                    <span class="stat-label">cupons</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon" aria-hidden="true">💰</span>
                    <span class="stat-value">15%</span>
                    <span class="stat-label">desconto médio</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rodapé -->
    <div class="store-card-footer">
        <div class="store-actions">
            <a href="/loja/amazon-brasil" class="btn-view-coupons" target="_self" rel="nofollow" 
               aria-label="Ver cupons da Amazon Brasil">
                <span class="btn-icon" aria-hidden="true">🔍</span>
                <span class="btn-text">Ver Cupons</span>
            </a>
            <a href="https://amazon.com.br" class="btn-visit-store" target="_blank" rel="nofollow"
               aria-label="Visitar site da Amazon Brasil">
                <span class="btn-icon" aria-hidden="true">🌐</span>
                <span class="btn-text">Visitar Loja</span>
            </a>
        </div>
        <div class="commission-info">
            <span class="commission-label">Comissão:</span>
            <span class="commission-value">5.0%</span>
        </div>
    </div>
</div>
```

## Cache e Performance

A classe implementa cache inteligente para otimizar performance:

- **Cache de instância**: Dados calculados são armazenados em memória
- **Cache WordPress**: HTML renderizado é cacheado por 1 hora
- **Lazy loading**: Imagens carregam sob demanda
- **Fallback**: Placeholder para logos quebrados

## Acessibilidade

- ✅ **ARIA labels** em todos os links
- ✅ **Alt text** em imagens
- ✅ **Focus indicators** visíveis
- ✅ **Semantic HTML** com landmarks
- ✅ **Keyboard navigation** suportada
- ✅ **Screen reader** friendly

## Responsividade

- ✅ **Mobile-first** design
- ✅ **Grid adaptativo** (1-4 colunas)
- ✅ **Breakpoints** otimizados
- ✅ **Touch-friendly** botões
- ✅ **Flexible layouts** para diferentes telas

## Integração com WordPress

### Shortcodes

```php
// [cupompromo_stores_grid limit="6" columns="3" card_style="default"]
```

### Gutenberg Blocks

```javascript
// Bloco: cupompromo/store-grid
```

### Hooks Disponíveis

```php
// Filtro para modificar configurações
add_filter('cupompromo_store_card_config', function($config, $store) {
    $config['show_commission'] = false;
    return $config;
}, 10, 2);

// Ação após renderização
do_action('cupompromo_store_card_rendered', $store_card, $store);
```

## Troubleshooting

### Problemas Comuns

1. **Logo não aparece**: Verifique se `logo_url` está definido
2. **Cache não funciona**: Verifique se `wp_cache_*` está disponível
3. **Estilo não aplica**: Verifique se CSS está carregado
4. **Erro de validação**: Verifique se `id` e `name` estão definidos

### Debug

```php
// Habilitar debug
$store_card->set_config(array('enable_cache' => false));

// Verificar dados
var_dump($store_card->get_summary());

// Verificar configurações
var_dump($store_card->get_config());
```

## Changelog

### v1.0.0
- ✅ Implementação inicial da classe
- ✅ Múltiplos estilos de card
- ✅ Sistema de cache
- ✅ Acessibilidade completa
- ✅ Responsividade mobile-first
- ✅ Integração com WordPress
- ✅ Documentação completa

## Contribuição

Para contribuir com melhorias na classe:

1. Siga os **WordPress Coding Standards**
2. Mantenha a **compatibilidade** com PHP 8.1+
3. Teste a **acessibilidade** com screen readers
4. Verifique a **responsividade** em diferentes dispositivos
5. Atualize a **documentação** conforme necessário 