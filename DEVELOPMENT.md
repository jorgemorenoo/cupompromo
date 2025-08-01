# Guia de Desenvolvimento - Cupompromo

Este documento contém informações importantes para desenvolvedores que trabalham no plugin **Cupompromo** - um portal de cupons de desconto profissional para WordPress.

## 🚀 Configuração do Ambiente

### Requisitos
- **PHP 8.1+** com tipagem estrita
- **WordPress 5.0+** 
- **MySQL 5.7+** ou **MariaDB 10.2+**
- **Composer** para dependências PHP
- **Node.js 16+** para assets frontend
- **Git** para versionamento

### Instalação Local

1. **Clone o repositório**
   ```bash
   git clone https://github.com/jorgemorenoo/cupompromo.git
   cd cupompromo
   ```

2. **Instale dependências PHP**
   ```bash
   composer install
   ```

3. **Instale dependências Node.js**
   ```bash
   npm install
   ```

4. **Configure o ambiente de desenvolvimento**
   ```bash
   cp config-dev.php wp-content/plugins/cupompromo/
   ```

5. **Ative o plugin no WordPress**
   - Acesse o painel administrativo
   - Vá em Plugins > Plugins Instalados
   - Ative o plugin "Cupompromo"

## 📁 Estrutura do Projeto

```
cupompromo/
├── cupompromo.php                 # Arquivo principal do plugin
├── includes/                       # Classes principais
│   ├── class-cupompromo.php       # Classe principal
│   ├── class-post-types.php       # CPTs e taxonomias
│   ├── class-admin.php            # Interface administrativa
│   ├── class-frontend.php         # Funcionalidades frontend
│   ├── class-api.php              # Endpoints REST API
│   ├── class-awin-api.php         # Integração Awin
│   ├── class-cache.php            # Sistema de cache
│   ├── class-coupon-manager.php   # Gerenciador de cupons
│   ├── class-store-card.php       # Componente de loja
│   ├── class-shortcodes.php       # Shortcodes
│   ├── class-gutenberg.php        # Blocos Gutenberg
│   ├── class-analytics.php        # Analytics e tracking
│   ├── class-advanced-analytics.php # Analytics avançado
│   ├── class-gamification.php     # Sistema de gamificação
│   ├── class-notifications.php    # Sistema de notificações
│   └── class-admin-settings.php   # Configurações admin
├── admin/                         # Área administrativa
│   └── views/                     # Templates admin
│       ├── coupons-list.php
│       ├── reports.php
│       └── settings.php
├── assets/                        # Assets fonte
│   ├── css/                       # CSS compilado
│   ├── js/                        # JavaScript
│   │   ├── admin/                 # Scripts admin
│   │   ├── frontend/              # Scripts frontend
│   │   └── blocks/                # Blocos Gutenberg
│   └── scss/                      # SCSS fonte
│       ├── admin/
│       └── frontend/
├── blocks/                        # Blocos Gutenberg
│   ├── stores-grid/
│   ├── coupon-grid/
│   └── search-bar/
├── templates/                     # Templates frontend
│   ├── single-cupompromo_store.php
│   ├── archive-cupompromo_coupon.php
│   ├── taxonomy-cupompromo_category.php
│   ├── search-cupompromo.php
│   └── parts/
│       ├── coupon-card.php
│       ├── search-form.php
│       └── pagination.php
├── languages/                     # Traduções
├── tests/                         # Testes unitários
├── docs/                          # Documentação
├── examples/                      # Exemplos de uso
├── composer.json                  # Dependências PHP
├── package.json                   # Dependências Node.js
├── webpack.config.js              # Configuração build
├── phpcs.xml                      # Padrões de código
├── phpunit.xml                    # Configuração testes
└── README.md                      # Documentação principal
```

## 🏗️ Arquitetura e Padrões

### 1. Padrões de Desenvolvimento
- **PHP 8.1+** com `declare(strict_types=1);`
- **WordPress Coding Standards** rigorosamente seguidos
- **PSR-12** para estrutura de código
- **Orientação a Objetos** com princípios SOLID
- **Nomenclatura consistente** com prefixo `cupompromo_`

### 2. Hooks e Filtros WordPress
```php
// Sempre use prefixo 'cupompromo_' para evitar conflitos
add_action('cupompromo_before_coupon_display', 'callback_function');
add_filter('cupompromo_coupon_discount_text', 'callback_function');
```

### 3. Custom Post Types e Taxonomias

#### CPT: cupompromo_store (Lojas)
```php
register_post_type('cupompromo_store', [
    'labels' => [
        'name' => 'Lojas',
        'singular_name' => 'Loja'
    ],
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'loja'],
    'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
    'show_in_rest' => true
]);

// Meta Fields:
- _store_logo (attachment_id)
- _affiliate_base_url (text)
- _default_commission (number)
- _store_description (textarea)
- _store_website (url)
- _featured_store (checkbox)
```

#### CPT: cupompromo_coupon (Cupons)
```php
register_post_type('cupompromo_coupon', [
    'labels' => [
        'name' => 'Cupons',
        'singular_name' => 'Cupom'
    ],
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'cupom'],
    'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
    'show_in_rest' => true
]);

// Meta Fields:
- _coupon_type (select: code|offer)
- _coupon_code (text)
- _affiliate_url (url)
- _discount_value (text)
- _discount_type (select: percentage|fixed)
- _expiry_date (date)
- _store_id (post_relationship)
- _click_count (number)
- _usage_count (number)
- _verified_date (date)
```

#### Taxonomias
```php
// cupompromo_category (Categorias)
register_taxonomy('cupompromo_category', ['cupompromo_coupon', 'cupompromo_store']);

// cupompromo_store_type (Tipo de Loja)
register_taxonomy('cupompromo_store_type', 'cupompromo_store');
```

## 🗄️ Estrutura do Banco de Dados

### Tabelas Customizadas
```sql
-- Tabela de cliques
CREATE TABLE {$wpdb->prefix}cupompromo_clicks (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    coupon_id bigint(20) NOT NULL,
    store_id bigint(20) NOT NULL,
    user_ip varchar(100),
    user_agent text,
    clicked_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY coupon_id (coupon_id),
    KEY store_id (store_id)
);

-- Tabela de analytics
CREATE TABLE {$wpdb->prefix}cupompromo_analytics (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    event_type varchar(50) NOT NULL,
    event_data json,
    user_id bigint(20),
    session_id varchar(100),
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY event_type (event_type),
    KEY user_id (user_id)
);

-- Tabela de cache
CREATE TABLE {$wpdb->prefix}cupompromo_cache (
    cache_key varchar(255) NOT NULL,
    cache_value longtext,
    expiration datetime,
    PRIMARY KEY (cache_key)
);
```

## 🧪 Testes

### Executar Testes
```bash
# Todos os testes
composer run test

# Testes específicos
vendor/bin/phpunit tests/test-store-card.php
vendor/bin/phpunit tests/test-coupon-manager.php

# Testes com cobertura
composer run test:coverage
```

### Configuração do PHPUnit
O arquivo `phpunit.xml` está configurado para:
- Usar o WordPress Test Suite
- Gerar relatórios de cobertura
- Excluir arquivos desnecessários
- Testar funcionalidades específicas do Cupompromo

## 🔧 Qualidade de Código

### PHP_CodeSniffer
```bash
# Verificar código
composer run lint

# Corrigir automaticamente
composer run fix
```

### Padrões de Código
- **WordPress Coding Standards** rigorosamente seguidos
- **PSR-12** para estrutura de código
- **PHP 8.1+** com tipagem estrita
- **Comentários PHPDoc** obrigatórios
- **Nomenclatura consistente** com prefixo `cupompromo_`

## 🎨 Frontend e Componentes

### Componentes Reutilizáveis

#### Cupompromo_Store_Card
```php
// Uso básico
$store_card = new Cupompromo_Store_Card($store_data);
echo $store_card->render();

// Modos de exibição
echo $store_card->render_minimal();    // Modo minimalista
echo $store_card->render_featured();   // Modo destaque
echo $store_card->render_compact();    // Modo compacto

// Configurações personalizadas
$config = [
    'show_logo' => true,
    'show_description' => false,
    'card_style' => 'featured',
    'logo_size' => 'large',
    'animation' => true
];
$store_card->set_config($config);
echo $store_card->render();
```

### Shortcodes Disponíveis
```php
[cupompromo_search]                    // Formulário de busca
[cupompromo_stores_grid]               // Grid de lojas
[cupompromo_popular_coupons]           // Cupons populares
[cupompromo_coupons_by_category]       // Cupons por categoria
[cupompromo_featured_stores]           // Lojas em destaque
[cupompromo_coupon_form]               // Formulário de cupom
[cupompromo_carousel]                  // Carrossel de banners
```

### Blocos Gutenberg
- `cupompromo/stores-grid` - Grid de lojas
- `cupompromo/coupons-list` - Lista de cupons
- `cupompromo/search-bar` - Barra de busca
- `cupompromo/featured-carousel` - Carrossel de destaques

## 🔌 APIs e Integrações

### REST API
- **Base URL**: `/wp-json/cupompromo/v1/`
- **Endpoints disponíveis**:
  - `GET /coupons` - Listar cupons
  - `GET /stores` - Listar lojas
  - `GET /categories` - Listar categorias
  - `POST /validate-coupon` - Validar cupom
  - `POST /analytics` - Registrar analytics
  - `GET /search` - Buscar cupons/lojas

### Exemplo de Uso
```php
// Buscar cupons
$response = wp_remote_get(home_url('/wp-json/cupompromo/v1/coupons'));

// Validar cupom
$response = wp_remote_post(home_url('/wp-json/cupompromo/v1/validate-coupon'), [
    'body' => [
        'coupon_code' => 'DESCONTO10'
    ]
]);
```

### Integração Awin API
```php
// Configuração
$awin_api = new Cupompromo_Awin_API();
$awin_api->set_api_key('your_api_key');
$awin_api->set_publisher_id('your_publisher_id');

// Buscar cupons
$coupons = $awin_api->get_coupons([
    'advertiser_id' => 123,
    'voucher_type' => 'discount'
]);

// Sincronizar lojas
$stores = $awin_api->get_advertisers();
```

## 🛠️ Painel Administrativo

### Dashboard Principal
```php
// Página: admin.php?page=cupompromo-dashboard
- Estatísticas Chave: Cupons ativos, clicks totais, lojas cadastradas
- Gráficos: Cupons mais clicados, performance por loja
- Ações Rápidas: Adicionar cupom, nova loja, sincronizar APIs
```

### Páginas de Administração

#### Gerenciamento de Lojas
```php
// Página: admin.php?page=cupompromo-stores
- Lista tabular com filtros
- Bulk actions: ativar/desativar, sincronizar
- Quick edit inline
- Import/Export CSV
```

#### Gerenciamento de Cupons
```php
// Página: admin.php?page=cupompromo-coupons
- Lista com status (ativo/expirado/pendente)
- Filtros: por loja, categoria, data
- Bulk actions: verificar validade, atualizar status
- Analytics por cupom
```

#### Configurações Avançadas
```php
// Página: admin.php?page=cupompromo-settings
// Abas:
- Geral: Moeda, textos padrão, timezone
- APIs: Configuração Awin, outros afiliados
- Estilo: Cores primárias, fonts, layout
- SEO: Meta tags, structured data
- Performance: Cache, otimizações
```

## 🎨 Design System

### Paleta de Cores
```css
:root {
    --cupompromo-primary: #622599;     /* Roxo principal */
    --cupompromo-secondary: #8BC53F;   /* Verde para sucesso */
    --cupompromo-accent: #FF6B35;      /* Laranja para CTAs */
    --cupompromo-neutral-100: #F8F9FA; /* Background claro */
    --cupompromo-neutral-800: #2D3748; /* Texto escuro */
    --cupompromo-error: #E53E3E;       /* Vermelho para erros */
}
```

### Tipografia
```css
/* Fonte principal: Poppins ou Montserrat */
.cupompromo-heading { font-weight: 600; }
.cupompromo-body { font-weight: 400; }
.cupompromo-small { font-size: 0.875rem; }
```

### Componentes Visuais
```css
/* Cards com sombras sutis */
.cupompromo-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

/* Botões com estados */
.cupompromo-btn {
    border-radius: 6px;
    padding: 12px 24px;
    transition: all 0.2s ease;
}
```

## 🔒 Segurança

### Boas Práticas
- Sempre use `wp_verify_nonce()` para formulários
- Sanitize todas as entradas com `sanitize_text_field()`
- Escape todas as saídas com `esc_html()`
- Use prepared statements para queries
- Verifique permissões com `current_user_can()`

### Exemplo
```php
// Verificar nonce
if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_action')) {
    wp_die(__('Erro de segurança.', 'cupompromo'));
}

// Sanitizar entrada
$coupon_code = sanitize_text_field($_POST['coupon_code']);

// Verificar permissões
if (!current_user_can('manage_options')) {
    wp_die(__('Permissão negada.', 'cupompromo'));
}

// Escape saída
echo esc_html($coupon->title);
```

## 🚀 Performance

### Cache
```php
// Usar transients para cache
$cached_data = get_transient('cupompromo_popular_coupons');
if (false === $cached_data) {
    $cached_data = $this->get_popular_coupons();
    set_transient('cupompromo_popular_coupons', $cached_data, HOUR_IN_SECONDS);
}
```

### Otimização de Queries
```php
// Usar prepared statements
$coupons = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}cupompromo_coupons WHERE status = %s",
    'active'
));
```

## 📝 Internacionalização

### Textos Traduzíveis
```php
// Usar __() para strings simples
echo __('Cupom válido!', 'cupompromo');

// Usar _e() para strings que são exibidas
_e('Erro ao salvar cupom.', 'cupompromo');

// Usar sprintf() para strings com variáveis
echo sprintf(
    __('Cupom %s aplicado com sucesso!', 'cupompromo'),
    esc_html($coupon_code)
);
```

### Gerar Arquivo POT
```bash
# Extrair strings traduzíveis
wp i18n make-pot . languages/cupompromo.pot
```

## 🔄 Versionamento

### Commits
- Use mensagens descritivas
- Siga o padrão Conventional Commits
- Referencie issues quando relevante

```bash
git commit -m "feat: adiciona validação de cupons expirados

- Implementa verificação de data de expiração
- Adiciona testes unitários
- Atualiza documentação

Closes #123"
```

### Tags
```bash
# Criar tag de versão
git tag -a v1.0.0 -m "Versão 1.0.0"

# Push da tag
git push origin v1.0.0
```

## 🐛 Debug

### Habilitar Debug
```php
// No wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('CUPOMPROMO_DEBUG', true);
```

### Logs Personalizados
```php
// Logar informações
error_log('Cupompromo: ' . $message);

// Logar arrays/objetos
error_log('Cupompromo: ' . print_r($data, true));
```

## 📊 Analytics e Logs

### Configuração de Logs
```php
// Habilitar logs detalhados
define('CUPOMPROMO_DETAILED_LOGGING', true);
define('CUPOMPROMO_LOG_QUERIES', true);
```

### Visualizar Logs
```bash
# Logs de debug
tail -f wp-content/logs/cupompromo-debug.log

# Logs de erro
tail -f wp-content/logs/cupompromo-errors.log
```

## 🎯 Funcionalidades Inovadoras

### 1. Sistema de Gamificação
```php
// User levels baseado em cupons utilizados
class Cupompromo_Gamification {
    // Bronze: 0-10 cupons | Prata: 11-50 | Ouro: 51+
    // Benefícios: Acesso antecipado a ofertas
}
```

### 2. Smart Notifications
```php
// Alertas por email para novos cupons de lojas favoritas
class Cupompromo_Notifications {
    // Seguir lojas específicas
    // Alertas de preço (futuro)
}
```

### 3. Analytics Avançado
```php
// Dashboard com métricas detalhadas
class Cupompromo_Advanced_Analytics {
    // Tracking de clicks, conversões
    // Heatmaps de cupons mais populares
    // ROI por loja/categoria
}
```

## 📚 Recursos Adicionais

### Documentação
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PHP 8.1 Documentation](https://www.php.net/manual/en/)

### Ferramentas Úteis
- [Query Monitor](https://wordpress.org/plugins/query-monitor/) - Debug de queries
- [Debug Bar](https://wordpress.org/plugins/debug-bar/) - Debug geral
- [Log Deprecated Notices](https://wordpress.org/plugins/log-deprecated-notices/) - Log de funções deprecated

### Comunidade
- [WordPress.org Forums](https://wordpress.org/support/)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/wordpress)
- [WordPress Slack](https://make.wordpress.org/chat/)

---

## 🛠️ Admin Panel Moderno

### Dashboard Principal com Estatísticas

```php
// admin/views/dashboard.php
class Cupompromo_Dashboard {
    public function render_dashboard() {
        $stats = $this->get_dashboard_stats();
        ?>
        <div class="wrap cupompromo-dashboard">
            <h1><?php _e('Dashboard Cupompromo', 'cupompromo'); ?></h1>
            
            <!-- Cards de Estatísticas -->
            <div class="cupompromo-stats-grid">
                <div class="cupompromo-stat-card">
                    <div class="stat-number"><?php echo esc_html($stats['total_coupons']); ?></div>
                    <div class="stat-label"><?php _e('Cupons Ativos', 'cupompromo'); ?></div>
                </div>
                <div class="cupompromo-stat-card">
                    <div class="stat-number"><?php echo esc_html($stats['total_clicks']); ?></div>
                    <div class="stat-label"><?php _e('Clicks Totais', 'cupompromo'); ?></div>
                </div>
                <div class="cupompromo-stat-card">
                    <div class="stat-number"><?php echo esc_html($stats['total_stores']); ?></div>
                    <div class="stat-label"><?php _e('Lojas Cadastradas', 'cupompromo'); ?></div>
                </div>
                <div class="cupompromo-stat-card">
                    <div class="stat-number"><?php echo esc_html($stats['revenue_today']); ?></div>
                    <div class="stat-label"><?php _e('Receita Hoje', 'cupompromo'); ?></div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="cupompromo-charts-grid">
                <div class="cupompromo-chart-container">
                    <h3><?php _e('Clicks por Dia (Últimos 30 dias)', 'cupompromo'); ?></h3>
                    <canvas id="clicksChart"></canvas>
                </div>
                <div class="cupompromo-chart-container">
                    <h3><?php _e('Top Lojas por Performance', 'cupompromo'); ?></h3>
                    <canvas id="storesChart"></canvas>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="cupompromo-quick-actions">
                <h3><?php _e('Ações Rápidas', 'cupompromo'); ?></h3>
                <div class="cupompromo-actions-grid">
                    <a href="<?php echo admin_url('post-new.php?post_type=cupompromo_coupon'); ?>" class="button button-primary">
                        <?php _e('Adicionar Cupom', 'cupompromo'); ?>
                    </a>
                    <a href="<?php echo admin_url('post-new.php?post_type=cupompromo_store'); ?>" class="button button-secondary">
                        <?php _e('Nova Loja', 'cupompromo'); ?>
                    </a>
                    <button class="button button-secondary" id="sync-apis">
                        <?php _e('Sincronizar APIs', 'cupompromo'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
```

### JavaScript para Gráficos

```javascript
// assets/js/admin/dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Clicks
    const clicksCtx = document.getElementById('clicksChart').getContext('2d');
    new Chart(clicksCtx, {
        type: 'line',
        data: {
            labels: cupompromoDashboardData.dates,
            datasets: [{
                label: 'Clicks',
                data: cupompromoDashboardData.clicks,
                borderColor: '#622599',
                backgroundColor: 'rgba(98, 37, 153, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de Lojas
    const storesCtx = document.getElementById('storesChart').getContext('2d');
    new Chart(storesCtx, {
        type: 'doughnut',
        data: {
            labels: cupompromoDashboardData.storeNames,
            datasets: [{
                data: cupompromoDashboardData.storeClicks,
                backgroundColor: [
                    '#622599', '#8BC53F', '#FF6B35', '#E53E3E', '#4299E1'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
```

### CSS para Dashboard

```css
/* assets/css/admin/dashboard.css */
.cupompromo-dashboard {
    margin: 20px 0;
}

.cupompromo-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.cupompromo-stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    border-left: 4px solid #622599;
}

.cupompromo-stat-card .stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #622599;
    margin-bottom: 5px;
}

.cupompromo-stat-card .stat-label {
    color: #666;
    font-size: 0.9rem;
}

.cupompromo-charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.cupompromo-chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cupompromo-quick-actions {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cupompromo-actions-grid {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .cupompromo-charts-grid {
        grid-template-columns: 1fr;
    }
    
    .cupompromo-actions-grid {
        flex-direction: column;
    }
}
```

### Página de Configurações Avançadas

```php
// admin/views/settings.php
class Cupompromo_Settings_Page {
    public function render_settings_page() {
        $active_tab = $_GET['tab'] ?? 'general';
        ?>
        <div class="wrap">
            <h1><?php _e('Configurações Cupompromo', 'cupompromo'); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=cupompromo-settings&tab=general" 
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Geral', 'cupompromo'); ?>
                </a>
                <a href="?page=cupompromo-settings&tab=apis" 
                   class="nav-tab <?php echo $active_tab === 'apis' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('APIs', 'cupompromo'); ?>
                </a>
                <a href="?page=cupompromo-settings&tab=display" 
                   class="nav-tab <?php echo $active_tab === 'display' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Exibição', 'cupompromo'); ?>
                </a>
                <a href="?page=cupompromo-settings&tab=tracking" 
                   class="nav-tab <?php echo $active_tab === 'tracking' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Tracking', 'cupompromo'); ?>
                </a>
                <a href="?page=cupompromo-settings&tab=advanced" 
                   class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Avançado', 'cupompromo'); ?>
                </a>
            </nav>

            <form method="post" action="options.php">
                <?php
                settings_fields('cupompromo_settings');
                
                switch ($active_tab) {
                    case 'general':
                        $this->render_general_settings();
                        break;
                    case 'apis':
                        $this->render_api_settings();
                        break;
                    case 'display':
                        $this->render_display_settings();
                        break;
                    case 'tracking':
                        $this->render_tracking_settings();
                        break;
                    case 'advanced':
                        $this->render_advanced_settings();
                        break;
                }
                
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private function render_general_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Moeda Padrão', 'cupompromo'); ?></th>
                <td>
                    <select name="cupompromo_currency">
                        <option value="BRL" <?php selected(get_option('cupompromo_currency'), 'BRL'); ?>>Real (R$)</option>
                        <option value="USD" <?php selected(get_option('cupompromo_currency'), 'USD'); ?>>Dólar ($)</option>
                        <option value="EUR" <?php selected(get_option('cupompromo_currency'), 'EUR'); ?>>Euro (€)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Timezone', 'cupompromo'); ?></th>
                <td>
                    <select name="cupompromo_timezone">
                        <?php echo wp_timezone_choice(get_option('cupompromo_timezone')); ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    private function render_api_settings() {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Awin API Key', 'cupompromo'); ?></th>
                <td>
                    <input type="text" name="cupompromo_awin_api_key" 
                           value="<?php echo esc_attr(get_option('cupompromo_awin_api_key')); ?>" 
                           class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Publisher ID', 'cupompromo'); ?></th>
                <td>
                    <input type="text" name="cupompromo_publisher_id" 
                           value="<?php echo esc_attr(get_option('cupompromo_publisher_id')); ?>" 
                           class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
    }
}
```

## 🛡️ LGPD e Privacidade

### Gerenciador de Consentimento LGPD

```php
// includes/class-lgpd-manager.php
class Cupompromo_LGPD_Manager {
    public function __construct() {
        add_action('wp_footer', [$this, 'render_cookie_banner']);
        add_action('wp_ajax_cupompromo_save_consent', [$this, 'save_consent']);
        add_action('wp_ajax_nopriv_cupompromo_save_consent', [$this, 'save_consent']);
    }

    public function render_cookie_banner() {
        if ($this->has_consent()) {
            return;
        }
        ?>
        <div id="cupompromo-cookie-banner" class="cupompromo-cookie-banner">
            <div class="cupompromo-cookie-content">
                <p><?php _e('Utilizamos cookies para melhorar sua experiência e personalizar conteúdo. Ao continuar navegando, você concorda com nossa política de privacidade.', 'cupompromo'); ?></p>
                <div class="cupompromo-cookie-buttons">
                    <button class="cupompromo-btn cupompromo-btn-primary" id="accept-all-cookies">
                        <?php _e('Aceitar Todos', 'cupompromo'); ?>
                    </button>
                    <button class="cupompromo-btn cupompromo-btn-secondary" id="customize-cookies">
                        <?php _e('Personalizar', 'cupompromo'); ?>
                    </button>
                    <button class="cupompromo-btn cupompromo-btn-text" id="reject-cookies">
                        <?php _e('Rejeitar', 'cupompromo'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    public function save_consent() {
        check_ajax_referer('cupompromo_consent_nonce', 'nonce');
        
        $consent_data = [
            'analytics' => (bool) $_POST['analytics'],
            'marketing' => (bool) $_POST['marketing'],
            'necessary' => true, // Sempre necessário
            'timestamp' => current_time('timestamp'),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        $this->store_consent($consent_data);
        
        wp_send_json_success(['message' => __('Preferências salvas com sucesso!', 'cupompromo')]);
    }

    private function store_consent($consent_data) {
        $user_id = get_current_user_id();
        $consent_key = 'cupompromo_consent_' . md5($this->get_client_ip());
        
        if ($user_id) {
            update_user_meta($user_id, 'cupompromo_consent', $consent_data);
        } else {
            set_transient($consent_key, $consent_data, YEAR_IN_SECONDS);
        }
    }

    public function has_consent($type = 'necessary') {
        $user_id = get_current_user_id();
        $consent_key = 'cupompromo_consent_' . md5($this->get_client_ip());
        
        if ($user_id) {
            $consent = get_user_meta($user_id, 'cupompromo_consent', true);
        } else {
            $consent = get_transient($consent_key);
        }
        
        return isset($consent[$type]) && $consent[$type];
    }

    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
```

### JavaScript para Banner de Cookies

```javascript
// assets/js/frontend/cookie-banner.js
document.addEventListener('DOMContentLoaded', function() {
    const cookieBanner = document.getElementById('cupompromo-cookie-banner');
    if (!cookieBanner) return;

    // Aceitar todos os cookies
    document.getElementById('accept-all-cookies').addEventListener('click', function() {
        saveConsent({
            analytics: true,
            marketing: true,
            necessary: true
        });
    });

    // Rejeitar cookies opcionais
    document.getElementById('reject-cookies').addEventListener('click', function() {
        saveConsent({
            analytics: false,
            marketing: false,
            necessary: true
        });
    });

    // Personalizar cookies
    document.getElementById('customize-cookies').addEventListener('click', function() {
        showCustomizeModal();
    });

    function saveConsent(consent) {
        const formData = new FormData();
        formData.append('action', 'cupompromo_save_consent');
        formData.append('nonce', cupompromoAjax.nonce);
        formData.append('analytics', consent.analytics);
        formData.append('marketing', consent.marketing);

        fetch(cupompromoAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideCookieBanner();
                // Recarregar scripts baseado no consentimento
                loadScriptsBasedOnConsent(consent);
            }
        });
    }

    function hideCookieBanner() {
        cookieBanner.style.display = 'none';
        localStorage.setItem('cupompromo_cookies_accepted', 'true');
    }

    function loadScriptsBasedOnConsent(consent) {
        if (consent.analytics) {
            // Carregar Google Analytics
            loadGoogleAnalytics();
        }
        
        if (consent.marketing) {
            // Carregar scripts de marketing
            loadMarketingScripts();
        }
    }
});
```

### Sistema de Exportação/Exclusão de Dados

```php
// includes/class-data-privacy.php
class Cupompromo_Data_Privacy {
    public function __construct() {
        add_action('wp_ajax_cupompromo_export_data', [$this, 'export_user_data']);
        add_action('wp_ajax_cupompromo_delete_data', [$this, 'delete_user_data']);
        add_action('wp_ajax_nopriv_cupompromo_export_data', [$this, 'export_user_data']);
        add_action('wp_ajax_nopriv_cupompromo_delete_data', [$this, 'delete_user_data']);
    }

    public function export_user_data() {
        check_ajax_referer('cupompromo_privacy_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $user = get_user_by('email', $email);
        
        if (!$user) {
            wp_send_json_error(['message' => __('Usuário não encontrado.', 'cupompromo')]);
        }

        $export_data = [
            'user_info' => [
                'id' => $user->ID,
                'email' => $user->user_email,
                'name' => $user->display_name,
                'registered' => $user->user_registered
            ],
            'consent_data' => get_user_meta($user->ID, 'cupompromo_consent', true),
            'click_history' => $this->get_user_clicks($user->ID),
            'favorite_stores' => get_user_meta($user->ID, 'cupompromo_favorite_stores', true),
            'newsletter_subscription' => get_user_meta($user->ID, 'cupompromo_newsletter', true)
        ];

        $filename = 'cupompromo_data_' . $user->ID . '_' . date('Y-m-d') . '.json';
        
        wp_send_json_success([
            'data' => $export_data,
            'filename' => $filename
        ]);
    }

    public function delete_user_data() {
        check_ajax_referer('cupompromo_privacy_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $user = get_user_by('email', $email);
        
        if (!$user) {
            wp_send_json_error(['message' => __('Usuário não encontrado.', 'cupompromo')]);
        }

        // Deletar dados do usuário
        $this->delete_user_data_complete($user->ID);
        
        wp_send_json_success(['message' => __('Dados deletados com sucesso.', 'cupompromo')]);
    }

    private function delete_user_data_complete($user_id) {
        global $wpdb;
        
        // Deletar meta dados
        delete_user_meta($user_id, 'cupompromo_consent');
        delete_user_meta($user_id, 'cupompromo_favorite_stores');
        delete_user_meta($user_id, 'cupompromo_newsletter');
        
        // Anonimizar cliques
        $wpdb->update(
            $wpdb->prefix . 'cupompromo_clicks',
            ['user_id' => 0, 'user_ip' => 'anonymized'],
            ['user_id' => $user_id]
        );
        
        // Anonimizar analytics
        $wpdb->update(
            $wpdb->prefix . 'cupompromo_analytics',
            ['user_id' => 0, 'session_id' => 'anonymized'],
            ['user_id' => $user_id]
        );
    }

    private function get_user_clicks($user_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, p.post_title as coupon_title, s.post_title as store_title 
             FROM {$wpdb->prefix}cupompromo_clicks c
             LEFT JOIN {$wpdb->posts} p ON c.coupon_id = p.ID
             LEFT JOIN {$wpdb->posts} s ON c.store_id = s.ID
             WHERE c.user_id = %d
             ORDER BY c.clicked_at DESC",
            $user_id
        ));
    }
}
```

### CSS para Banner de Cookies

```css
/* assets/css/frontend/cookie-banner.css */
.cupompromo-cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(45, 55, 72, 0.95);
    color: white;
    padding: 20px;
    z-index: 9999;
    backdrop-filter: blur(10px);
}

.cupompromo-cookie-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.cupompromo-cookie-content p {
    margin: 0;
    flex: 1;
    line-height: 1.5;
}

.cupompromo-cookie-buttons {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}

.cupompromo-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
}

.cupompromo-btn-primary {
    background: #622599;
    color: white;
}

.cupompromo-btn-primary:hover {
    background: #4a1d73;
}

.cupompromo-btn-secondary {
    background: transparent;
    color: white;
    border: 1px solid white;
}

.cupompromo-btn-secondary:hover {
    background: white;
    color: #2D3748;
}

.cupompromo-btn-text {
    background: transparent;
    color: #A0AEC0;
    text-decoration: underline;
}

.cupompromo-btn-text:hover {
    color: white;
}

@media (max-width: 768px) {
    .cupompromo-cookie-content {
        flex-direction: column;
        text-align: center;
    }
    
    .cupompromo-cookie-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .cupompromo-btn {
        width: 100%;
    }
}
```

## 🔔 Recursos Adicionais

### Sistema de Newsletter

```php
// includes/class-newsletter.php
class Cupompromo_Newsletter {
    public function __construct() {
        add_action('wp_ajax_cupompromo_subscribe_newsletter', [$this, 'subscribe']);
        add_action('wp_ajax_nopriv_cupompromo_subscribe_newsletter', [$this, 'subscribe']);
        add_action('wp_ajax_cupompromo_unsubscribe_newsletter', [$this, 'unsubscribe']);
        add_action('wp_ajax_nopriv_cupompromo_unsubscribe_newsletter', [$this, 'unsubscribe']);
    }

    public function subscribe() {
        check_ajax_referer('cupompromo_newsletter_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $name = sanitize_text_field($_POST['name'] ?? '');
        
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Email inválido.', 'cupompromo')]);
        }

        // Verificar se já está inscrito
        if ($this->is_subscribed($email)) {
            wp_send_json_error(['message' => __('Este email já está inscrito.', 'cupompromo')]);
        }

        // Salvar inscrição
        $subscriber_id = $this->save_subscriber($email, $name);
        
        // Enviar email de boas-vindas
        $this->send_welcome_email($email, $name);
        
        wp_send_json_success(['message' => __('Inscrição realizada com sucesso!', 'cupompromo')]);
    }

    public function unsubscribe() {
        check_ajax_referer('cupompromo_newsletter_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $token = sanitize_text_field($_POST['token']);
        
        if (!$this->verify_unsubscribe_token($email, $token)) {
            wp_send_json_error(['message' => __('Token inválido.', 'cupompromo')]);
        }

        $this->remove_subscriber($email);
        
        wp_send_json_success(['message' => __('Inscrição cancelada com sucesso.', 'cupompromo')]);
    }

    private function save_subscriber($email, $name) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'cupompromo_newsletter',
            [
                'email' => $email,
                'name' => $name,
                'status' => 'active',
                'subscribed_at' => current_time('mysql'),
                'unsubscribe_token' => wp_generate_password(32, false)
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
        
        return $wpdb->insert_id;
    }

    private function send_welcome_email($email, $name) {
        $subject = sprintf(__('Bem-vindo ao %s!', 'cupompromo'), get_bloginfo('name'));
        
        $message = sprintf(
            __('Olá %s,

Obrigado por se inscrever em nossa newsletter!

Você receberá as melhores ofertas e cupons de desconto diretamente no seu email.

Para cancelar a inscrição, clique no link abaixo:
%s

Atenciosamente,
Equipe %s', 'cupompromo'),
            $name ?: __('Amigo', 'cupompromo'),
            $this->get_unsubscribe_link($email),
            get_bloginfo('name')
        );
        
        wp_mail($email, $subject, $message);
    }

    public function send_coupon_digest() {
        $subscribers = $this->get_active_subscribers();
        $recent_coupons = $this->get_recent_coupons(10);
        
        if (empty($recent_coupons)) {
            return;
        }

        foreach ($subscribers as $subscriber) {
            $this->send_coupon_digest_email($subscriber, $recent_coupons);
        }
    }

    private function send_coupon_digest_email($subscriber, $coupons) {
        $subject = sprintf(__('Novos cupons disponíveis no %s!', 'cupompromo'), get_bloginfo('name'));
        
        $message = $this->build_digest_message($subscriber, $coupons);
        
        wp_mail($subscriber->email, $subject, $message, [
            'Content-Type: text/html; charset=UTF-8'
        ]);
    }

    private function build_digest_message($subscriber, $coupons) {
        $message = sprintf(
            '<h2>Olá %s!</h2>
            <p>Temos novos cupons incríveis para você:</p>',
            $subscriber->name ?: __('Amigo', 'cupompromo')
        );

        foreach ($coupons as $coupon) {
            $message .= sprintf(
                '<div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
                    <h3>%s</h3>
                    <p><strong>%s</strong></p>
                    <p>%s</p>
                    <a href="%s" style="background: #622599; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ver Cupom</a>
                </div>',
                esc_html($coupon->post_title),
                esc_html(get_post_meta($coupon->ID, '_discount_value', true)),
                esc_html($coupon->post_excerpt),
                esc_url(get_permalink($coupon->ID))
            );
        }

        $message .= sprintf(
            '<p><a href="%s">Ver todos os cupons</a></p>
            <p><small>Para cancelar a inscrição, <a href="%s">clique aqui</a></small></p>',
            home_url('/cupons/'),
            $this->get_unsubscribe_link($subscriber->email)
        );

        return $message;
    }
}
```

### Implementação PWA (Progressive Web App)

```json
// public/manifest.json
{
    "name": "Cupompromo - Cupons de Desconto",
    "short_name": "Cupompromo",
    "description": "Encontre os melhores cupons de desconto das principais lojas online",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#622599",
    "theme_color": "#622599",
    "orientation": "portrait-primary",
    "icons": [
        {
            "src": "/wp-content/plugins/cupompromo/public/images/icon-192x192.png",
            "sizes": "192x192",
            "type": "image/png"
        },
        {
            "src": "/wp-content/plugins/cupompromo/public/images/icon-512x512.png",
            "sizes": "512x512",
            "type": "image/png"
        }
    ],
    "categories": ["shopping", "finance"],
    "lang": "pt-BR"
}
```

```javascript
// public/sw.js (Service Worker)
const CACHE_NAME = 'cupompromo-v1';
const urlsToCache = [
    '/',
    '/wp-content/plugins/cupompromo/public/css/frontend.css',
    '/wp-content/plugins/cupompromo/public/js/frontend.js',
    '/wp-content/plugins/cupompromo/public/images/logo.png'
];

self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Retorna do cache se disponível
                if (response) {
                    return response;
                }
                
                // Caso contrário, busca da rede
                return fetch(event.request);
            }
        )
    );
});

self.addEventListener('push', function(event) {
    const options = {
        body: event.data.text(),
        icon: '/wp-content/plugins/cupompromo/public/images/icon-192x192.png',
        badge: '/wp-content/plugins/cupompromo/public/images/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Ver Cupons',
                icon: '/wp-content/plugins/cupompromo/public/images/checkmark.png'
            },
            {
                action: 'close',
                title: 'Fechar',
                icon: '/wp-content/plugins/cupompromo/public/images/xmark.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('Cupompromo', options)
    );
});
```

### Sistema de Push Notifications

```php
// includes/class-push-notifications.php
class Cupompromo_Push_Notifications {
    public function __construct() {
        add_action('wp_ajax_cupompromo_subscribe_push', [$this, 'subscribe_push']);
        add_action('wp_ajax_nopriv_cupompromo_subscribe_push', [$this, 'subscribe_push']);
        add_action('cupompromo_new_coupon_added', [$this, 'notify_new_coupon']);
    }

    public function subscribe_push() {
        check_ajax_referer('cupompromo_push_nonce', 'nonce');
        
        $subscription = json_decode(stripslashes($_POST['subscription']), true);
        
        if (!$subscription) {
            wp_send_json_error(['message' => __('Dados de inscrição inválidos.', 'cupompromo')]);
        }

        $user_id = get_current_user_id();
        $subscription_data = [
            'endpoint' => $subscription['endpoint'],
            'keys' => $subscription['keys'],
            'user_id' => $user_id,
            'created_at' => current_time('mysql')
        ];

        $this->save_subscription($subscription_data);
        
        wp_send_json_success(['message' => __('Notificações ativadas com sucesso!', 'cupompromo')]);
    }

    public function notify_new_coupon($coupon_id) {
        $subscriptions = $this->get_active_subscriptions();
        $coupon = get_post($coupon_id);
        $store = get_post(get_post_meta($coupon_id, '_store_id', true));
        
        $message = sprintf(
            __('Novo cupom: %s - %s', 'cupompromo'),
            $coupon->post_title,
            $store->post_title
        );

        foreach ($subscriptions as $subscription) {
            $this->send_push_notification($subscription, $message, $coupon_id);
        }
    }

    private function send_push_notification($subscription, $message, $coupon_id) {
        $vapid_keys = $this->get_vapid_keys();
        
        $payload = json_encode([
            'title' => __('Novo Cupom Disponível!', 'cupompromo'),
            'body' => $message,
            'icon' => home_url('/wp-content/plugins/cupompromo/public/images/icon-192x192.png'),
            'badge' => home_url('/wp-content/plugins/cupompromo/public/images/badge-72x72.png'),
            'data' => [
                'url' => get_permalink($coupon_id),
                'coupon_id' => $coupon_id
            ]
        ]);

        $headers = [
            'Authorization: vapid t=' . $this->generate_vapid_token($subscription['endpoint'], $vapid_keys),
            'Content-Type: application/json',
            'TTL: 86400'
        ];

        $response = wp_remote_post($subscription['endpoint'], [
            'headers' => $headers,
            'body' => $payload,
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            // Remover inscrição inválida
            $this->remove_subscription($subscription['id']);
        }
    }

    private function generate_vapid_token($endpoint, $vapid_keys) {
        $header = [
            'typ' => 'JWT',
            'alg' => 'ES256'
        ];

        $payload = [
            'aud' => parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST),
            'exp' => time() + 12 * 3600,
            'sub' => 'mailto:' . get_option('admin_email')
        ];

        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));

        $signature = '';
        openssl_sign(
            $header_encoded . '.' . $payload_encoded,
            $signature,
            $vapid_keys['private_key'],
            'SHA256'
        );

        $signature_encoded = $this->base64url_encode($signature);

        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
```

```javascript
// assets/js/frontend/push-notifications.js
class CupompromoPushNotifications {
    constructor() {
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;
        this.init();
    }

    async init() {
        if (!this.isSupported) {
            console.log('Push notifications não são suportadas');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/wp-content/plugins/cupompromo/public/sw.js');
            console.log('Service Worker registrado:', registration);

            // Verificar se já está inscrito
            const subscription = await registration.pushManager.getSubscription();
            if (subscription) {
                console.log('Já inscrito para push notifications');
                return;
            }

            // Solicitar permissão e inscrição
            this.requestNotificationPermission();
        } catch (error) {
            console.error('Erro ao registrar Service Worker:', error);
        }
    }

    async requestNotificationPermission() {
        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            this.subscribeToPushNotifications();
        } else {
            console.log('Permissão para notificações negada');
        }
    }

    async subscribeToPushNotifications() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(cupompromoPushConfig.vapidPublicKey)
            });

            // Enviar inscrição para o servidor
            await this.sendSubscriptionToServer(subscription);
            
            console.log('Inscrito para push notifications com sucesso');
        } catch (error) {
            console.error('Erro ao se inscrever para push notifications:', error);
        }
    }

    async sendSubscriptionToServer(subscription) {
        const formData = new FormData();
        formData.append('action', 'cupompromo_subscribe_push');
        formData.append('nonce', cupompromoPushConfig.nonce);
        formData.append('subscription', JSON.stringify(subscription));

        const response = await fetch(cupompromoPushConfig.ajaxurl, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            console.log('Inscrição salva no servidor');
        } else {
            console.error('Erro ao salvar inscrição:', data.data);
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    new CupompromoPushNotifications();
});
```

## 📊 Tracking e Analytics

### Integração Google Tag Manager

```php
// includes/class-gtm-integration.php
class Cupompromo_GTM_Integration {
    public function __construct() {
        add_action('wp_head', [$this, 'render_gtm_head']);
        add_action('wp_body_open', [$this, 'render_gtm_body']);
        add_action('wp_footer', [$this, 'render_data_layer']);
    }

    public function render_gtm_head() {
        $gtm_id = get_option('cupompromo_gtm_id');
        if (!$gtm_id) return;
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo esc_attr($gtm_id); ?>');</script>
        <!-- End Google Tag Manager -->
        <?php
    }

    public function render_gtm_body() {
        $gtm_id = get_option('cupompromo_gtm_id');
        if (!$gtm_id) return;
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($gtm_id); ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    }

    public function render_data_layer() {
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        
        // Configuração base do DataLayer
        dataLayer.push({
            'event': 'cupompromo_loaded',
            'cupompromo_version': '<?php echo CUPOMPROMO_VERSION; ?>',
            'user_type': '<?php echo is_user_logged_in() ? "logged_in" : "guest"; ?>'
        });

        <?php
        // Adicionar dados específicos da página
        if (is_singular('cupompromo_coupon')) {
            $this->add_coupon_data_layer();
        } elseif (is_singular('cupompromo_store')) {
            $this->add_store_data_layer();
        }
        ?>
        </script>
        <?php
    }

    private function add_coupon_data_layer() {
        global $post;
        $store_id = get_post_meta($post->ID, '_store_id', true);
        $store = get_post($store_id);
        ?>
        dataLayer.push({
            'event': 'view_coupon',
            'coupon_id': <?php echo $post->ID; ?>,
            'coupon_title': '<?php echo esc_js($post->post_title); ?>',
            'coupon_discount': '<?php echo esc_js(get_post_meta($post->ID, '_discount_value', true)); ?>',
            'store_id': <?php echo $store_id; ?>,
            'store_name': '<?php echo esc_js($store->post_title); ?>',
            'coupon_type': '<?php echo esc_js(get_post_meta($post->ID, '_coupon_type', true)); ?>'
        });
        <?php
    }

    private function add_store_data_layer() {
        global $post;
        ?>
        dataLayer.push({
            'event': 'view_store',
            'store_id': <?php echo $post->ID; ?>,
            'store_name': '<?php echo esc_js($post->post_title); ?>',
            'store_category': '<?php echo esc_js(wp_get_post_terms($post->ID, 'cupompromo_category', ['fields' => 'names'])[0] ?? ''); ?>'
        });
        <?php
    }
}
```

### JavaScript para Tracking de Cupons

```javascript
// assets/js/frontend/tracking.js
class CupompromoTracking {
    constructor() {
        this.init();
    }

    init() {
        this.trackCouponReveals();
        this.trackOfferClicks();
        this.trackSearch();
        this.trackStoreViews();
    }

    trackCouponReveals() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.cupompromo-reveal-coupon')) {
                const couponId = e.target.dataset.couponId;
                const storeId = e.target.dataset.storeId;
                
                this.pushEvent('coupon_reveal', {
                    coupon_id: couponId,
                    store_id: storeId,
                    coupon_title: e.target.dataset.couponTitle,
                    discount_value: e.target.dataset.discountValue
                });
            }
        });
    }

    trackOfferClicks() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.cupompromo-offer-link')) {
                const couponId = e.target.dataset.couponId;
                const storeId = e.target.dataset.storeId;
                const affiliateUrl = e.target.href;
                
                this.pushEvent('offer_click', {
                    coupon_id: couponId,
                    store_id: storeId,
                    affiliate_url: affiliateUrl,
                    coupon_title: e.target.dataset.couponTitle
                });

                // Aguardar um pouco antes do redirecionamento para garantir o tracking
                setTimeout(() => {
                    window.location.href = affiliateUrl;
                }, 100);
            }
        });
    }

    trackSearch() {
        const searchForm = document.querySelector('.cupompromo-search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                const searchTerm = searchForm.querySelector('input[name="s"]').value;
                
                this.pushEvent('search', {
                    search_term: searchTerm,
                    search_results_count: 0 // Será atualizado após a busca
                });
            });
        }
    }

    trackStoreViews() {
        // Tracking automático de visualização de loja
        if (document.body.classList.contains('single-cupompromo_store')) {
            const storeId = document.querySelector('[data-store-id]')?.dataset.storeId;
            if (storeId) {
                this.pushEvent('store_view', {
                    store_id: storeId,
                    store_name: document.querySelector('.store-title')?.textContent
                });
            }
        }
    }

    pushEvent(eventName, eventData) {
        // Push para DataLayer
        if (window.dataLayer) {
            window.dataLayer.push({
                event: eventName,
                ...eventData,
                timestamp: new Date().toISOString()
            });
        }

        // Enviar para servidor via AJAX
        this.sendToServer(eventName, eventData);
    }

    async sendToServer(eventName, eventData) {
        const formData = new FormData();
        formData.append('action', 'cupompromo_track_event');
        formData.append('nonce', cupompromoTracking.nonce);
        formData.append('event_name', eventName);
        formData.append('event_data', JSON.stringify(eventData));

        try {
            await fetch(cupompromoTracking.ajaxurl, {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Erro ao enviar tracking:', error);
        }
    }
}

// Inicializar tracking
document.addEventListener('DOMContentLoaded', function() {
    new CupompromoTracking();
});
```

### Função de Tracking de Conversão

```php
// includes/class-conversion-tracking.php
class Cupompromo_Conversion_Tracking {
    public function __construct() {
        add_action('wp_ajax_cupompromo_track_event', [$this, 'track_event']);
        add_action('wp_ajax_nopriv_cupompromo_track_event', [$this, 'track_event']);
        add_action('cupompromo_coupon_clicked', [$this, 'track_conversion']);
    }

    public function track_event() {
        check_ajax_referer('cupompromo_tracking_nonce', 'nonce');
        
        $event_name = sanitize_text_field($_POST['event_name']);
        $event_data = json_decode(stripslashes($_POST['event_data']), true);
        
        if (!$event_name || !$event_data) {
            wp_send_json_error(['message' => __('Dados inválidos.', 'cupompromo')]);
        }

        $this->save_event($event_name, $event_data);
        
        wp_send_json_success(['message' => __('Evento registrado com sucesso.', 'cupompromo')]);
    }

    public function track_conversion($coupon_id) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $session_id = $this->get_session_id();
        
        // Registrar clique
        $wpdb->insert(
            $wpdb->prefix . 'cupompromo_clicks',
            [
                'coupon_id' => $coupon_id,
                'store_id' => get_post_meta($coupon_id, '_store_id', true),
                'user_id' => $user_id,
                'user_ip' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'session_id' => $session_id,
                'clicked_at' => current_time('mysql')
            ],
            ['%d', '%d', '%d', '%s', '%s', '%s', '%s']
        );

        // Atualizar contador de cliques do cupom
        $current_clicks = get_post_meta($coupon_id, '_click_count', true) ?: 0;
        update_post_meta($coupon_id, '_click_count', $current_clicks + 1);

        // Registrar evento de analytics
        $this->save_event('coupon_click', [
            'coupon_id' => $coupon_id,
            'store_id' => get_post_meta($coupon_id, '_store_id', true),
            'user_id' => $user_id,
            'session_id' => $session_id
        ]);

        // Disparar evento para GTM
        do_action('cupompromo_conversion_tracked', $coupon_id, $user_id);
    }

    private function save_event($event_name, $event_data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'cupompromo_analytics',
            [
                'event_type' => $event_name,
                'event_data' => json_encode($event_data),
                'user_id' => get_current_user_id(),
                'session_id' => $this->get_session_id(),
                'user_ip' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );
    }

    private function get_session_id() {
        if (!session_id()) {
            session_start();
        }
        return session_id();
    }

    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
```

### Sistema de Redirecionamento

```php
// includes/class-redirect-system.php
class Cupompromo_Redirect_System {
    public function __construct() {
        add_action('init', [$this, 'handle_redirect']);
        add_action('wp_ajax_cupompromo_redirect', [$this, 'ajax_redirect']);
        add_action('wp_ajax_nopriv_cupompromo_redirect', [$this, 'ajax_redirect']);
    }

    public function handle_redirect() {
        if (isset($_GET['cupompromo_redirect']) && isset($_GET['coupon_id'])) {
            $coupon_id = intval($_GET['coupon_id']);
            $this->process_redirect($coupon_id);
        }
    }

    public function ajax_redirect() {
        check_ajax_referer('cupompromo_redirect_nonce', 'nonce');
        
        $coupon_id = intval($_POST['coupon_id']);
        $redirect_url = $this->get_redirect_url($coupon_id);
        
        if ($redirect_url) {
            // Registrar clique
            do_action('cupompromo_coupon_clicked', $coupon_id);
            
            wp_send_json_success(['redirect_url' => $redirect_url]);
        } else {
            wp_send_json_error(['message' => __('URL de redirecionamento não encontrada.', 'cupompromo')]);
        }
    }

    private function process_redirect($coupon_id) {
        $redirect_url = $this->get_redirect_url($coupon_id);
        
        if (!$redirect_url) {
            wp_die(__('Cupom não encontrado ou expirado.', 'cupompromo'));
        }

        // Registrar clique
        do_action('cupompromo_coupon_clicked', $coupon_id);

        // Redirecionar
        wp_redirect($redirect_url);
        exit;
    }

    private function get_redirect_url($coupon_id) {
        $coupon = get_post($coupon_id);
        
        if (!$coupon || $coupon->post_type !== 'cupompromo_coupon') {
            return false;
        }

        // Verificar se o cupom está ativo
        if ($coupon->post_status !== 'publish') {
            return false;
        }

        // Verificar data de expiração
        $expiry_date = get_post_meta($coupon_id, '_expiry_date', true);
        if ($expiry_date && strtotime($expiry_date) < time()) {
            return false;
        }

        // Obter URL de afiliado
        $affiliate_url = get_post_meta($coupon_id, '_affiliate_url', true);
        
        if (!$affiliate_url) {
            // Gerar URL de afiliado baseada na loja
            $store_id = get_post_meta($coupon_id, '_store_id', true);
            $store = get_post($store_id);
            
            if ($store) {
                $base_url = get_post_meta($store_id, '_affiliate_base_url', true);
                $coupon_code = get_post_meta($coupon_id, '_coupon_code', true);
                
                if ($base_url && $coupon_code) {
                    $affiliate_url = add_query_arg('coupon', $coupon_code, $base_url);
                }
            }
        }

        return $affiliate_url;
    }
}
```

### Página de Redirecionamento

```php
// templates/redirect.php
<?php
/**
 * Template para página de redirecionamento
 * 
 * Esta página é exibida brevemente antes do redirecionamento
 * para o site da loja, permitindo tracking e analytics
 */

get_header(); ?>

<div class="cupompromo-redirect-page">
    <div class="cupompromo-redirect-container">
        <div class="cupompromo-redirect-content">
            <div class="cupompromo-redirect-logo">
                <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../public/images/logo.png'); ?>" 
                     alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
            </div>
            
            <div class="cupompromo-redirect-message">
                <h2><?php _e('Redirecionando...', 'cupompromo'); ?></h2>
                <p><?php _e('Você será redirecionado para a loja em alguns segundos.', 'cupompromo'); ?></p>
                
                <div class="cupompromo-redirect-spinner">
                    <div class="spinner"></div>
                </div>
                
                <p class="cupompromo-redirect-note">
                    <?php _e('Se você não for redirecionado automaticamente,', 'cupompromo'); ?>
                    <a href="#" id="cupompromo-manual-redirect">
                        <?php _e('clique aqui', 'cupompromo'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Redirecionamento automático após 3 segundos
setTimeout(function() {
    window.location.href = '<?php echo esc_js($redirect_url); ?>';
}, 3000);

// Redirecionamento manual
document.getElementById('cupompromo-manual-redirect').addEventListener('click', function(e) {
    e.preventDefault();
    window.location.href = '<?php echo esc_js($redirect_url); ?>';
});
</script>

<?php get_footer(); ?>
```

```css
/* assets/css/frontend/redirect.css */
.cupompromo-redirect-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #622599 0%, #8BC53F 100%);
    color: white;
}

.cupompromo-redirect-container {
    text-align: center;
    max-width: 500px;
    padding: 40px 20px;
}

.cupompromo-redirect-logo img {
    max-width: 200px;
    margin-bottom: 30px;
}

.cupompromo-redirect-message h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    font-weight: 600;
}

.cupompromo-redirect-message p {
    font-size: 1.1rem;
    margin-bottom: 30px;
    line-height: 1.6;
}

.cupompromo-redirect-spinner {
    margin: 30px 0;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.cupompromo-redirect-note {
    font-size: 0.9rem;
    opacity: 0.8;
}

.cupompromo-redirect-note a {
    color: white;
    text-decoration: underline;
}

.cupompromo-redirect-note a:hover {
    text-decoration: none;
}
```

**Status do Projeto**: 🟡 Em desenvolvimento ativo
**Versão Atual**: 1.0.0-alpha
**Última Atualização**: Dezembro 2024
