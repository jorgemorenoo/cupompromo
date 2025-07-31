# Cupompromo_Coupon_Manager - Documentação da Classe

## Visão Geral

A classe `Cupompromo_Coupon_Manager` é responsável pelo gerenciamento centralizado de cupons no plugin Cupompromo. Ela integra com a API Awin, gerencia posts do WordPress, implementa cache e rate limiting, e fornece sistema de tracking completo.

## Características Principais

- ✅ **Sincronização Awin**: Integração completa com API Awin
- ✅ **CRUD WordPress**: Criação/atualização de posts
- ✅ **Cache Inteligente**: Sistema de cache para performance
- ✅ **Rate Limiting**: Proteção contra sobrecarga da API
- ✅ **Tracking**: Sistema completo de rastreamento
- ✅ **Status Management**: Gerenciamento de status (ativo/expirado)
- ✅ **Automation**: Sincronização automática agendada

## Uso Básico

```php
// Obter instância
$coupon_manager = Cupompromo_Coupon_Manager::get_instance();

// Sincronizar com Awin
$result = $coupon_manager->sync_awin_coupons();

// Obter cupons
$coupons = $coupon_manager->cupompromo_get_coupons(array(
    'limit' => 20,
    'store_id' => 1,
    'coupon_type' => 'code'
));

// Obter estatísticas
$stats = $coupon_manager->cupompromo_get_coupon_stats();
```

## Métodos Principais

### Sincronização com Awin

#### `sync_awin_coupons()`
Sincroniza cupons da API Awin com o WordPress.

```php
$result = $coupon_manager->sync_awin_coupons();

// Retorna:
array(
    'success' => true,
    'message' => 'Sincronização concluída: 150 processados, 25 criados, 125 atualizados',
    'stats' => array(
        'total_processed' => 150,
        'posts_created' => 25,
        'posts_updated' => 125,
        'posts_skipped' => 0,
        'errors' => 0,
        'duration' => 2.5
    )
)
```

#### `cupompromo_force_sync()`
Força sincronização manual, ignorando rate limiting.

```php
$result = $coupon_manager->cupompromo_force_sync();
```

### Gerenciamento de Cupons

#### `cupompromo_get_coupons(array $args)`
Obtém cupons com filtros avançados.

```php
$coupons = $coupon_manager->cupompromo_get_coupons(array(
    'store_id' => 1,           // ID da loja
    'category_id' => 2,         // ID da categoria
    'coupon_type' => 'code',    // code|offer
    'status' => 'publish',      // publish|draft|expired
    'limit' => 20,              // Limite de resultados
    'offset' => 0,              // Offset para paginação
    'orderby' => 'date',        // Campo para ordenação
    'order' => 'DESC',          // ASC|DESC
    'featured_only' => false,   // Apenas cupons em destaque
    'expired' => false          // Incluir cupons expirados
));
```

### Tracking e Analytics

#### `track_coupon_click()`
Registra clique em cupom via AJAX.

```php
// Via AJAX
wp_ajax_cupompromo_track_click
wp_ajax_nopriv_cupompromo_track_click

// Parâmetros:
$_POST['coupon_id'] // ID do cupom
$_POST['nonce']     // Nonce de segurança
```

#### `copy_coupon_code()`
Registra cópia de código via AJAX.

```php
// Via AJAX
wp_ajax_cupompromo_copy_code
wp_ajax_nopriv_cupompromo_copy_code

// Parâmetros:
$_POST['coupon_id'] // ID do cupom
$_POST['nonce']     // Nonce de segurança
```

### Estatísticas

#### `cupompromo_get_coupon_stats()`
Obtém estatísticas completas dos cupons.

```php
$stats = $coupon_manager->cupompromo_get_coupon_stats();

// Retorna:
array(
    'total_coupons' => 150,
    'expired_coupons' => 25,
    'total_stores' => 45,
    'total_clicks' => 1250,
    'total_usage' => 890,
    'verified_coupons' => 120,
    'last_sync' => '2024-01-15 10:30:00',
    'awin_configured' => true
)
```

## Processamento de Dados Awin

### Estrutura de Dados Awin

```php
$awin_coupon = array(
    'id' => 12345,
    'merchant' => array(
        'id' => 678,
        'name' => 'Amazon Brasil',
        'url' => 'https://amazon.com.br',
        'description' => 'Maior loja online',
        'commission' => 5.0
    ),
    'voucher' => array(
        'code' => 'AMAZON10',
        'description' => '10% OFF em eletrônicos',
        'url' => 'https://awin.com/click/...',
        'endDate' => '2024-12-31T23:59:59Z',
        'active' => true
    )
);
```

### Conversão para WordPress

```php
$coupon_data = array(
    'post_title' => '10% OFF em eletrônicos',
    'post_content' => '10% OFF em eletrônicos',
    'post_status' => 'publish',
    'post_type' => 'cupompromo_coupon',
    'meta_input' => array(
        '_awin_id' => 12345,
        '_awin_merchant_id' => 678,
        '_coupon_type' => 'code',
        '_coupon_code' => 'AMAZON10',
        '_affiliate_url' => 'https://awin.com/click/...',
        '_discount_value' => '10%',
        '_discount_type' => 'percentage',
        '_expiry_date' => '2024-12-31 23:59:59',
        '_store_id' => 15, // ID da loja criada
        '_awin_data' => '{"id":12345,...}',
        '_click_count' => 0,
        '_usage_count' => 0,
        '_verified_date' => '2024-01-15 10:30:00',
        '_last_sync' => '2024-01-15 10:30:00'
    )
);
```

## Rate Limiting

### Sistema de Proteção

```php
// Verifica rate limiting
if ($this->is_rate_limited('awin_sync')) {
    return array(
        'success' => false,
        'message' => 'Rate limit atingido. Tente novamente em alguns minutos.'
    );
}

// Define rate limiting
$this->set_rate_limit('awin_sync', 3600); // 1 hora
```

### Configurações de Rate Limiting

- **Ação**: `awin_sync`
- **Limite**: 10 requisições
- **Duração**: 1 hora (3600 segundos)
- **Armazenamento**: WordPress Transients

## Cache System

### Cache de Instância

```php
// Verifica cache
$cache_key = 'coupons_' . md5(serialize($args));
if (isset($this->cache[$cache_key])) {
    return $this->cache[$cache_key];
}

// Salva no cache
$this->cache[$cache_key] = $coupons;
```

### Limpeza de Cache

```php
// Limpa cache da instância
$coupon_manager->cupompromo_clear_cache();

// Limpa cache WordPress
wp_cache_flush_group('cupompromo_coupons');
```

## Hooks e Actions

### Actions Disponíveis

```php
// Após criação de cupom
do_action('cupompromo_coupon_created', $post_id, $coupon_data);

// Após atualização de cupom
do_action('cupompromo_coupon_updated', $post_id, $coupon_data);

// Após clique em cupom
do_action('cupompromo_coupon_clicked', $coupon_id, $new_clicks);

// Após cópia de código
do_action('cupompromo_coupon_code_copied', $coupon_id, $new_usage);

// Após expiração de cupons
do_action('cupompromo_coupons_expired', $updated_count);
```

### Filtros Disponíveis

```php
// Filtra dados de cupom antes de salvar
apply_filters('cupompromo_coupon_data_before_save', $coupon_data, $awin_coupon);

// Filtra estatísticas
apply_filters('cupompromo_coupon_stats', $stats);
```

## Agendamento Automático

### Cron Jobs

```php
// Sincronização automática (a cada hora)
wp_schedule_event(time(), 'hourly', 'cupompromo_sync_awin_coupons');

// Limpeza de cupons expirados (diário)
wp_schedule_event(time(), 'daily', 'cupompromo_cleanup_expired_coupons');
```

### Configuração Manual

```php
// Agenda sincronização
wp_schedule_single_event(time() + 3600, 'cupompromo_sync_awin_coupons');

// Remove agendamento
wp_clear_scheduled_hook('cupompromo_sync_awin_coupons');
```

## Tratamento de Erros

### Logs de Erro

```php
// Log de erro individual
error_log('Cupompromo Awin Sync Error: ' . $e->getMessage());

// Log de erro fatal
error_log('Cupompromo Awin Sync Fatal Error: ' . $e->getMessage());
```

### Validação de Dados

```php
// Validação de cupom
if (empty($coupon_data['post_title']) || empty($coupon_data['meta_input']['_store_id'])) {
    return 'skipped';
}

// Validação de loja
if (empty($merchant_name)) {
    return 0;
}
```

## Performance e Otimização

### Otimizações Implementadas

1. **Cache Inteligente**: Evita queries repetidas
2. **Rate Limiting**: Protege contra sobrecarga da API
3. **Batch Processing**: Processa cupons em lotes
4. **Lazy Loading**: Carrega dados sob demanda
5. **Indexed Queries**: Usa meta_query otimizada

### Métricas de Performance

```php
// Tempo de sincronização
$start_time = microtime(true);
// ... processamento ...
$duration = microtime(true) - $start_time;

// Estatísticas de performance
$stats = array(
    'total_processed' => 150,
    'posts_created' => 25,
    'posts_updated' => 125,
    'duration' => 2.5, // segundos
    'rate' => 60 // cupons/segundo
);
```

## Segurança

### Validação e Sanitização

```php
// Sanitização de dados
'post_title' => sanitize_text_field($voucher_data['description']),
'post_content' => wp_kses_post($voucher_data['description']),
'_coupon_code' => sanitize_text_field($voucher_data['code']),
'_affiliate_url' => esc_url_raw($voucher_data['url']),
'_awin_data' => json_encode($awin_coupon)
```

### Nonces e Verificação

```php
// Verificação de nonce
check_ajax_referer('cupompromo_track_click', 'nonce');
check_ajax_referer('cupompromo_copy_code', 'nonce');
```

## Exemplos de Uso

### 1. Sincronização Manual

```php
$coupon_manager = Cupompromo_Coupon_Manager::get_instance();
$result = $coupon_manager->cupompromo_force_sync();

if ($result['success']) {
    echo 'Sincronização concluída com sucesso!';
    echo 'Cupons criados: ' . $result['stats']['posts_created'];
    echo 'Cupons atualizados: ' . $result['stats']['posts_updated'];
} else {
    echo 'Erro na sincronização: ' . $result['message'];
}
```

### 2. Obter Cupons Populares

```php
$popular_coupons = $coupon_manager->cupompromo_get_coupons(array(
    'limit' => 10,
    'orderby' => 'meta_value_num',
    'meta_key' => '_click_count',
    'order' => 'DESC'
));
```

### 3. Tracking de Cliques

```php
// JavaScript
jQuery.post('/wp-admin/admin-ajax.php', {
    action: 'cupompromo_track_click',
    coupon_id: 123,
    nonce: '<?php echo wp_create_nonce("cupompromo_track_click"); ?>'
}, function(response) {
    if (response.success) {
        console.log('Clique registrado:', response.data.clicks);
    }
});
```

### 4. Cópia de Código

```php
// JavaScript
jQuery.post('/wp-admin/admin-ajax.php', {
    action: 'cupompromo_copy_code',
    coupon_id: 123,
    nonce: '<?php echo wp_create_nonce("cupompromo_copy_code"); ?>'
}, function(response) {
    if (response.success) {
        navigator.clipboard.writeText(response.data.code);
        alert('Código copiado!');
    }
});
```

## Troubleshooting

### Problemas Comuns

1. **API não configurada**: Verifique configurações da Awin
2. **Rate limit atingido**: Aguarde 1 hora ou use `cupompromo_force_sync()`
3. **Cupons não sincronizam**: Verifique logs de erro
4. **Performance lenta**: Verifique cache e rate limiting

### Debug

```php
// Habilita debug
define('CUPOMPROMO_DEBUG', true);

// Verifica configuração Awin
$awin_configured = $coupon_manager->awin_api->is_configured();

// Verifica rate limiting
$is_limited = $coupon_manager->is_rate_limited('awin_sync');

// Limpa cache
$coupon_manager->cupompromo_clear_cache();
```

## Changelog

### v1.0.0
- ✅ Sincronização completa com API Awin
- ✅ CRUD de posts WordPress
- ✅ Sistema de cache inteligente
- ✅ Rate limiting para proteção
- ✅ Tracking de cliques e usos
- ✅ Gerenciamento de status
- ✅ Sincronização automática
- ✅ Documentação completa

---

**Status**: ✅ **Implementação Completa**

A classe `Cupompromo_Coupon_Manager` está completamente implementada e funcional, seguindo todos os padrões de qualidade e segurança do plugin Cupompromo. 