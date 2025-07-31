# Cupompromo_Admin_Settings - Documentação da Classe

## Visão Geral

A classe `Cupompromo_Admin_Settings` é responsável pelo painel administrativo completo do plugin Cupompromo. Ela gerencia menus, configurações, dashboard, gerenciamento de cupons e sistema de notificações.

## Características Principais

- ✅ **Menu Administrativo**: Dashboard, Cupons, Lojas, Configurações, Analytics
- ✅ **WordPress Settings API**: Configurações padronizadas
- ✅ **Dashboard Completo**: Estatísticas, ações rápidas, status da API
- ✅ **Gerenciamento de Cupons**: Lista com filtros, edição, status
- ✅ **Configurações Visuais**: Cores, moeda, notificações
- ✅ **Sistema de Notificações**: Alertas administrativos
- ✅ **AJAX Integration**: Sincronização, testes, exportação
- ✅ **Nomenclatura Consistente**: Prefixo `cupompromo_` em todos os métodos

## Estrutura do Menu

```
Cupompromo (Menu Principal)
├── Dashboard
├── Cupons
├── Lojas
├── Configurações
└── Analytics
```

## Páginas Administrativas

### 1. Dashboard (`cupompromo-dashboard`)

**Funcionalidades:**
- Cards de estatísticas (cupons, lojas, cliques, usos)
- Ações rápidas (sincronizar, gerenciar, configurar)
- Status da API Awin
- Cupons recentes
- Gráficos de performance

**Componentes:**
```php
// Cards de estatísticas
.cupompromo-stats-grid
├── .cupompromo-stat-card
    ├── .stat-icon (🎫🏪👁️📊)
    └── .stat-content
        ├── h3 (número)
        └── p (descrição)

// Ações rápidas
.cupompromo-quick-actions
└── .action-buttons

// Status da API
.cupompromo-api-status
└── .status-info

// Cupons recentes
.cupompromo-recent-coupons
└── table
```

### 2. Cupons (`cupompromo-coupons`)

**Funcionalidades:**
- Lista de cupons com filtros
- Filtros por loja, tipo, status
- Ações de editar/ver
- Paginação
- Exportação

**Filtros Disponíveis:**
- **Loja**: Todas as lojas ou específica
- **Tipo**: Códigos ou Ofertas
- **Status**: Ativos, Expirados, Rascunhos

**Estrutura da Tabela:**
```php
<table>
├── <thead>
│   └── <tr>
│       ├── Cupom (título + código)
│       ├── Loja
│       ├── Tipo (badge)
│       ├── Desconto
│       ├── Cliques
│       ├── Status
│       └── Ações
└── <tbody>
    └── <tr> (para cada cupom)
```

### 3. Lojas (`cupompromo-stores`)

**Funcionalidades:**
- Grid de lojas cadastradas
- Informações da loja (logo, nome, descrição)
- Links para editar/ver
- Status da loja

**Estrutura:**
```php
.cupompromo-stores-grid
└── .cupompromo-store-card
    ├── .store-header
    │   ├── .store-logo
    │   └── h3 (nome)
    ├── .store-content
    │   └── p (descrição)
    └── .store-actions
        ├── Editar
        └── Ver
```

### 4. Configurações (`cupompromo-settings`)

**Seções de Configuração:**

#### API Awin
```php
// Campos
cupompromo_awin_api_key      // API Key
cupompromo_awin_publisher_id // Publisher ID
cupompromo_awin_region       // Região (BR, US, UK, DE)
```

#### Configurações Gerais
```php
// Campos
cupompromo_currency              // Moeda (BRL, USD, EUR, GBP)
cupompromo_enable_notifications  // Notificações
cupompromo_auto_sync_interval    // Intervalo de sincronização
```

#### Configurações Visuais
```php
// Campos
cupompromo_primary_color    // Cor primária
cupompromo_secondary_color  // Cor secundária
```

### 5. Analytics (`cupompromo-analytics`)

**Funcionalidades:**
- Gráficos de performance
- Relatórios exportáveis
- Métricas detalhadas

**Gráficos:**
- Cupons por loja (doughnut chart)
- Cliques por mês (line chart)

## Configurações WordPress Settings API

### Registro de Configurações

```php
// Registra opções
register_setting('cupompromo_settings', 'cupompromo_awin_api_key');
register_setting('cupompromo_settings', 'cupompromo_awin_publisher_id');
register_setting('cupompromo_settings', 'cupompromo_awin_region');
register_setting('cupompromo_settings', 'cupompromo_currency');
register_setting('cupompromo_settings', 'cupompromo_primary_color');
register_setting('cupompromo_settings', 'cupompromo_secondary_color');
register_setting('cupompromo_settings', 'cupompromo_enable_notifications');
register_setting('cupompromo_settings', 'cupompromo_auto_sync_interval');
```

### Seções de Configuração

```php
// Seções
add_settings_section('cupompromo_api_section', 'Configurações da API Awin');
add_settings_section('cupompromo_general_section', 'Configurações Gerais');
add_settings_section('cupompromo_visual_section', 'Configurações Visuais');
```

### Campos de Configuração

```php
// Campos
add_settings_field('cupompromo_awin_api_key', 'API Key', 'callback');
add_settings_field('cupompromo_awin_publisher_id', 'Publisher ID', 'callback');
add_settings_field('cupompromo_awin_region', 'Região', 'callback');
add_settings_field('cupompromo_currency', 'Moeda', 'callback');
add_settings_field('cupompromo_primary_color', 'Cor Primária', 'callback');
add_settings_field('cupompromo_secondary_color', 'Cor Secundária', 'callback');
add_settings_field('cupompromo_enable_notifications', 'Notificações', 'callback');
add_settings_field('cupompromo_auto_sync_interval', 'Intervalo de Sincronização', 'callback');
```

## AJAX Endpoints

### 1. Sincronização Awin
```php
// Action: cupompromo_sync_awin
// Método: cupompromo_ajax_sync_awin()
// Parâmetros: nonce
// Retorna: resultado da sincronização
```

### 2. Obter Estatísticas
```php
// Action: cupompromo_get_stats
// Método: cupompromo_ajax_get_stats()
// Parâmetros: nonce
// Retorna: estatísticas atualizadas
```

### 3. Teste de API
```php
// Action: cupompromo_test_api
// Método: cupompromo_ajax_test_api()
// Parâmetros: nonce
// Retorna: status da conexão
```

## Sistema de Notificações

### Notificações Administrativas

```php
// Verifica configuração da API
if (empty($api_key) || empty($publisher_id)) {
    echo '<div class="notice notice-warning">';
    echo '<p>Configure sua API Awin para começar a sincronizar cupons.</p>';
    echo '</div>';
}

// Verifica última sincronização
if ($hours_ago > 24) {
    echo '<div class="notice notice-info">';
    echo '<p>A última sincronização foi há mais de 24 horas.</p>';
    echo '</div>';
}
```

### Notificações AJAX

```php
// Sucesso
CupompromoAdmin.showNotice('success', 'Operação realizada com sucesso!');

// Erro
CupompromoAdmin.showNotice('error', 'Erro na operação');

// Aviso
CupompromoAdmin.showNotice('warning', 'Atenção: verifique as configurações');
```

## JavaScript Admin

### Funcionalidades JavaScript

1. **Sincronização Awin**
   - Loading state
   - Feedback visual
   - Atualização de estatísticas

2. **Teste de API**
   - Verificação de conexão
   - Resultado visual
   - Tratamento de erros

3. **Exportação de Dados**
   - Download de CSV
   - Nomeação automática
   - Feedback de progresso

4. **Gráficos**
   - Chart.js integration
   - Gráficos responsivos
   - Cores personalizadas

5. **Filtros**
   - Filtros em tempo real
   - Persistência de estado
   - UX otimizada

### Estrutura JavaScript

```javascript
const CupompromoAdmin = {
    init: function() {
        this.bindEvents();
        this.initCharts();
        this.initColorPickers();
    },
    
    bindEvents: function() {
        // Event listeners
    },
    
    syncAwin: function(e) {
        // Sincronização AJAX
    },
    
    testApi: function(e) {
        // Teste de API
    },
    
    updateStats: function() {
        // Atualização de estatísticas
    },
    
    showNotice: function(type, message) {
        // Sistema de notificações
    }
};
```

## CSS Admin

### Estrutura de Estilos

```css
/* Dashboard */
.cupompromo-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.cupompromo-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Filtros */
.cupompromo-filters {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

/* Lista de Cupons */
.cupompromo-coupons-list table {
    width: 100%;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-code {
    background: var(--cupompromo-secondary, #8BC53F);
    color: white;
}

.badge-offer {
    background: var(--cupompromo-accent, #FF6B35);
    color: white;
}

/* Status */
.status-publish { color: #46b450; }
.status-expired { color: #dc3232; }
.status-draft { color: #666; }

/* Loading */
.cupompromo-loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--cupompromo-primary, #622599);
    border-radius: 50%;
    animation: cupompromo-spin 1s linear infinite;
}

@keyframes cupompromo-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
```

## Responsividade

### Breakpoints

```css
@media (max-width: 768px) {
    .cupompromo-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .cupompromo-stores-grid {
        grid-template-columns: 1fr;
    }
    
    .cupompromo-analytics-charts {
        grid-template-columns: 1fr;
    }
    
    .cupompromo-filters form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
```

## Acessibilidade

### Recursos de Acessibilidade

1. **Focus Indicators**
   ```css
   .cupompromo-stat-card:focus-within,
   .cupompromo-store-card:focus-within {
       outline: 2px solid var(--cupompromo-primary, #622599);
       outline-offset: 2px;
   }
   ```

2. **High Contrast Mode**
   ```css
   @media (prefers-contrast: high) {
       .cupompromo-stat-card,
       .cupompromo-quick-actions {
           border-width: 2px;
       }
   }
   ```

3. **Dark Mode Support**
   ```css
   @media (prefers-color-scheme: dark) {
       .cupompromo-stat-card {
           background: #1e1e1e;
           border-color: #404040;
           color: #e0e0e0;
       }
   }
   ```

## Segurança

### Validações

1. **Nonces**
   ```php
   check_ajax_referer('cupompromo_admin_nonce', 'nonce');
   ```

2. **Capabilities**
   ```php
   if (!current_user_can('manage_options')) {
       wp_die(__('Permissão negada', 'cupompromo'));
   }
   ```

3. **Sanitização**
   ```php
   $store_id = intval($_GET['store_id'] ?? 0);
   $coupon_type = sanitize_text_field($_GET['coupon_type'] ?? '');
   ```

## Exemplos de Uso

### 1. Adicionar Nova Página Admin

```php
add_submenu_page(
    'cupompromo-dashboard',
    'Nova Página',
    'Nova Página',
    'manage_options',
    'cupompromo-nova-pagina',
    array($this, 'cupompromo_nova_pagina')
);

public function cupompromo_nova_pagina() {
    ?>
    <div class="wrap">
        <h1><?php _e('Nova Página', 'cupompromo'); ?></h1>
        <!-- Conteúdo da página -->
    </div>
    <?php
}
```

### 2. Adicionar Nova Configuração

```php
// No método cupompromo_init_settings()
register_setting('cupompromo_settings', 'cupompromo_nova_config');

add_settings_field(
    'cupompromo_nova_config',
    'Nova Configuração',
    array($this, 'cupompromo_nova_config_field'),
    'cupompromo_settings',
    'cupompromo_general_section'
);

public function cupompromo_nova_config_field() {
    $value = get_option('cupompromo_nova_config', '');
    echo '<input type="text" name="cupompromo_nova_config" value="' . esc_attr($value) . '" class="regular-text" />';
}
```

### 3. Adicionar Novo AJAX Endpoint

```php
// No método init_hooks()
add_action('wp_ajax_cupompromo_nova_acao', array($this, 'cupompromo_ajax_nova_acao'));

public function cupompromo_ajax_nova_acao() {
    check_ajax_referer('cupompromo_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Permissão negada', 'cupompromo'));
    }
    
    // Lógica da ação
    
    wp_send_json_success(array(
        'message' => __('Ação realizada com sucesso', 'cupompromo')
    ));
}
```

## Troubleshooting

### Problemas Comuns

1. **Menu não aparece**
   - Verificar se `admin_menu` hook está sendo chamado
   - Verificar capabilities do usuário

2. **Configurações não salvam**
   - Verificar se `admin_init` hook está sendo chamado
   - Verificar se `register_setting` foi chamado

3. **AJAX não funciona**
   - Verificar se nonce está correto
   - Verificar se action está registrado
   - Verificar console do navegador

4. **Estilos não carregam**
   - Verificar se `admin_enqueue_scripts` está sendo chamado
   - Verificar se hook está correto
   - Verificar se arquivo CSS existe

### Debug

```php
// Habilita debug
define('CUPOMPROMO_DEBUG', true);

// Log de debug
if (defined('CUPOMPROMO_DEBUG') && CUPOMPROMO_DEBUG) {
    error_log('Cupompromo Debug: ' . $message);
}

// Verifica se hooks estão funcionando
add_action('admin_menu', function() {
    error_log('Cupompromo: admin_menu hook executado');
});
```

## Changelog

### v1.0.0
- ✅ Menu administrativo completo
- ✅ Dashboard com estatísticas
- ✅ Gerenciamento de cupons
- ✅ Configurações WordPress Settings API
- ✅ Sistema de notificações
- ✅ AJAX endpoints
- ✅ JavaScript admin
- ✅ CSS responsivo
- ✅ Acessibilidade
- ✅ Segurança
- ✅ Documentação completa

---

**Status**: ✅ **Implementação Completa**

A classe `Cupompromo_Admin_Settings` está completamente implementada e funcional, seguindo todos os padrões WordPress e de qualidade do plugin Cupompromo. 