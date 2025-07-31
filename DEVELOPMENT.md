# Guia de Desenvolvimento - Cupompromo

Este documento contém informações importantes para desenvolvedores que trabalham no plugin Cupompromo.

## 🚀 Configuração do Ambiente

### Requisitos
- PHP 8.1 ou superior
- WordPress 5.0 ou superior
- MySQL 5.7 ou superior
- Composer
- Node.js (para assets)

### Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/cupompromo.git
   cd cupompromo
   ```

2. **Instale as dependências**
   ```bash
   composer install
   ```

3. **Configure o ambiente de desenvolvimento**
   ```bash
   cp config-dev.php wp-content/plugins/cupompromo/
   ```

4. **Ative o plugin no WordPress**
   - Acesse o painel administrativo
   - Vá em Plugins > Plugins Instalados
   - Ative o plugin "Cupompromo"

## 📁 Estrutura do Projeto

```
cupompromo/
├── cupompromo.php                 # Arquivo principal
├── includes/                       # Classes principais
│   ├── class-cupompromo.php       # Classe principal
│   ├── class-post-types.php       # CPTs e taxonomias
│   ├── class-admin.php            # Interface administrativa
│   ├── class-frontend.php         # Funcionalidades frontend
│   ├── class-api.php              # Endpoints REST API
│   ├── class-shortcodes.php       # Shortcodes
│   ├── class-gutenberg.php        # Blocos Gutenberg
│   └── class-analytics.php        # Analytics e tracking
├── admin/                         # Área administrativa
│   ├── views/                     # Templates admin
│   ├── css/                       # Estilos admin
│   └── js/                        # Scripts admin
├── public/                        # Frontend
│   ├── css/                       # Estilos frontend
│   ├── js/                        # Scripts frontend
│   └── images/                    # Assets
├── templates/                     # Templates personalizados
├── blocks/                        # Blocos Gutenberg
├── languages/                     # Traduções
├── tests/                         # Testes unitários
├── assets/                        # Arquivos fonte
└── docs/                          # Documentação
```

## 🧪 Testes

### Executar Testes
```bash
# Todos os testes
composer run test

# Com cobertura
composer run test:coverage

# Testes específicos
vendor/bin/phpunit tests/Unit/
vendor/bin/phpunit tests/Integration/
```

### Configuração do PHPUnit
O arquivo `phpunit.xml` está configurado para:
- Usar o WordPress Test Suite
- Gerar relatórios de cobertura
- Excluir arquivos desnecessários

## 🔧 Qualidade de Código

### PHP_CodeSniffer
```bash
# Verificar código
composer run lint

# Corrigir automaticamente
composer run fix
```

### Padrões de Código
- WordPress Coding Standards
- PSR-12 para estrutura
- PHP 8.1+ com tipagem estrita
- Comentários PHPDoc obrigatórios

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

## 🔌 APIs e Integrações

### REST API
- Base URL: `/wp-json/cupompromo/v1/`
- Endpoints disponíveis:
  - `GET /coupons` - Listar cupons
  - `GET /stores` - Listar lojas
  - `POST /validate-coupon` - Validar cupom
  - `POST /analytics` - Registrar analytics

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

## 🎨 Frontend

### Componentes Reutilizáveis

#### Cupompromo_Store_Card
```php
// Uso básico
$store_card = new Cupompromo_Store_Card($store_data);
echo $store_card->render();

// Modo minimalista
echo $store_card->render_minimal();

// Modo destaque
echo $store_card->render_featured();

// Modo compacto
echo $store_card->render_compact();

// Configurações personalizadas
$config = array(
    'show_logo' => true,
    'show_description' => false,
    'card_style' => 'featured',
    'logo_size' => 'large',
    'animation' => true
);
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
```

### Blocos Gutenberg
- `cupompromo/stores-grid` - Grid de lojas
- `cupompromo/coupons-list` - Lista de cupons
- `cupompromo/search-bar` - Barra de busca
- `cupompromo/featured-carousel` - Carrossel

## 🗄️ Banco de Dados

### Tabelas Principais
```sql
-- Lojas
wp_cupompromo_stores

-- Cupons
wp_cupompromo_coupons

-- Categorias
wp_cupompromo_categories

-- Relacionamento cupons-categorias
wp_cupompromo_coupon_categories

-- Analytics
wp_cupompromo_analytics
```

### Migrações
```bash
# Ativar plugin (cria tabelas)
wp plugin activate cupompromo

# Desativar plugin (remove dados se configurado)
wp plugin deactivate cupompromo
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

**Lembre-se**: Sempre teste suas alterações antes de fazer commit e mantenha o código limpo e bem documentado! 🚀 

##  **Relatório de Conformidade com as Regras do Projeto**

### ✅ **Pontos Positivos - O que está de acordo:**

1. **Estrutura e Arquitetura**
   - ✅ Uso de `declare(strict_types=1);` em todas as classes
   - ✅ Prevenção de acesso direto com `!defined('ABSPATH')`
   - ✅ Nomenclatura consistente com prefixo `cupompromo_`
   - ✅ Estrutura de classes bem organizada
   - ✅ Uso de hooks WordPress (actions/filters)

2. **Segurança**
   - ✅ Verificação de nonces em AJAX
   - ✅ Sanitização de entradas com `sanitize_text_field()`
   - ✅ Escape de saídas com `esc_html()`
   - ✅ Verificação de permissões com `current_user_can()`

3. **Funcionalidades Core**
   - ✅ Custom Post Types implementados
   - ✅ REST API endpoints funcionais
   - ✅ Sistema de analytics básico
   - ✅ Integração com Awin API
   - ✅ Shortcodes implementados

### ⚠️ **Problemas Identificados:**

1. **Padrões de Código**
   - ❌ Não segue PSR-12 completamente (espaçamento, nomenclatura)
   - ❌ Falta de namespaces
   - ❌ Alguns métodos não seguem camelCase

2. **Estrutura de Arquivos**
   - ❌ Falta de assets CSS/JS compilados
   - ❌ Blocos Gutenberg incompletos
   - ❌ Templates frontend limitados

## 🚧 **O que precisa ser desenvolvido:**

### **1. PRIORIDADE ALTA - Frontend Completo**

#### Templates Faltantes:
```php
// templates/archive-cupompromo_coupon.php
// templates/taxonomy-cupompromo_category.php  
// templates/search-cupompromo.php
// templates/parts/coupon-card.php
// templates/parts/store-card.php
// templates/parts/search-form.php
```

#### Componentes React:
```javascript
// assets/js/components/CouponModal.js
// assets/js/components/SearchBar.js
// assets/js/components/FilterPanel.js
// assets/js/components/CouponGrid.js
```

### **2. PRIORIDADE ALTA - Blocos Gutenberg Completos**

#### Blocos React:
```bash
# Criar blocos usando @wordpress/create-block
npx @wordpress/create-block cupompromo-stores-grid
npx @wordpress/create-block cupompromo-coupons-list
npx @wordpress/create-block cupompromo-search-bar
npx @wordpress/create-block cupompromo-featured-carousel
```

#### Estrutura dos Blocos:
```javascript
// blocks/stores-grid/src/index.js
// blocks/coupons-list/src/index.js
// blocks/search-bar/src/index.js
// blocks/featured-carousel/src/index.js
```

### **3. PRIORIDADE MÉDIA - Assets e Estilos**

#### CSS/SCSS:
```scss
// assets/scss/frontend/
// ├── _variables.scss
// ├── _mixins.scss
// ├── _components.scss
// ├── _layout.scss
// └── main.scss

// assets/scss/admin/
// ├── _dashboard.scss
// ├── _forms.scss
// └── main.scss
```

#### JavaScript:
```javascript
// assets/js/frontend/
// ├── components/
// ├── utils/
// └── main.js

// assets/js/admin/
// ├── dashboard.js
// ├── analytics.js
// └── main.js
```

### **4. PRIORIDADE MÉDIA - Funcionalidades Avançadas**

#### Sistema de Cache:
```php
// includes/class-cache.php
class Cupompromo_Cache {
    public function cache_coupons($store_id, $data);
    public function cache_stores($data);
    public function invalidate_cache($type);
}
```

#### Sistema de Notificações:
```php
// includes/class-notifications.php
class Cupompromo_Notifications {
    public function send_coupon_alert($user_id, $coupon);
    public function send_store_update($user_id, $store);
}
```

### **5. PRIORIDADE BAIXA - Melhorias e Otimizações**

#### Gamificação:
```php
// includes/class-gamification.php
class Cupompromo_Gamification {
    public function get_user_level($user_id);
    public function award_points($user_id, $action);
    public function get_leaderboard();
}
```

#### Analytics Avançado:
```php
// includes/class-advanced-analytics.php
class Cupompromo_Advanced_Analytics {
    public function generate_heatmap();
    public function track_conversions();
    public function calculate_roi();
}
```

## 🎯 **Plano de Desenvolvimento Recomendado:**

### **Fase 1 (1-2 semanas): Frontend Core**
1. Criar templates de arquivo e categoria
2. Implementar componentes React básicos
3. Desenvolver sistema de busca
4. Criar modal de cupons

### **Fase 2 (1-2 semanas): Blocos Gutenberg**
1. Configurar @wordpress/create-block
2. Desenvolver blocos React
3. Implementar renderização server-side
4. Testar integração com editor

### **Fase 3 (1 semana): Assets e Estilos**
1. Configurar build system (webpack/vite)
2. Desenvolver CSS/SCSS
3. Otimizar JavaScript
4. Implementar lazy loading

### **Fase 4 (1 semana): Funcionalidades Avançadas**
1. Sistema de cache
2. Notificações
3. Analytics avançado
4. Gamificação

## ️ **Próximos Passos Imediatos:**

1. **Corrigir padrões de código** (PSR-12)
2. **Criar estrutura de assets** (CSS/JS)
3. **Implementar templates frontend** básicos
4. **Configurar build system** para blocos Gutenberg

Quer que eu comece implementando alguma dessas funcionalidades específicas? Posso começar com os templates frontend ou a configuração dos blocos Gutenberg. 