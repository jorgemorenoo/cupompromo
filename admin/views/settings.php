<?php
/**
 * Página de configurações
 *
 * @package CupomPromo
 * @since 1.0.0
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Salvar configurações
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['cupom_promo_settings_nonce'], 'cupom_promo_settings')) {
        $settings = array(
            'enable_woocommerce_integration' => isset($_POST['enable_woocommerce_integration']) ? 1 : 0,
            'default_discount_type' => sanitize_text_field($_POST['default_discount_type']),
            'currency_symbol' => sanitize_text_field($_POST['currency_symbol']),
            'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? 1 : 0,
            'admin_email' => sanitize_email($_POST['admin_email']),
            'auto_generate_codes' => isset($_POST['auto_generate_codes']) ? 1 : 0,
            'code_prefix' => sanitize_text_field($_POST['code_prefix']),
            'code_length' => intval($_POST['code_length'])
        );
        
        update_option('cupom_promo_settings', $settings);
        echo '<div class="notice notice-success"><p>' . __('Configurações salvas com sucesso!', 'cupom-promo') . '</p></div>';
    }
}

$settings = get_option('cupom_promo_settings', array(
    'enable_woocommerce_integration' => 0,
    'default_discount_type' => 'percentage',
    'currency_symbol' => 'R$',
    'enable_email_notifications' => 0,
    'admin_email' => get_option('admin_email'),
    'auto_generate_codes' => 0,
    'code_prefix' => 'CUPOM',
    'code_length' => 8
));
?>

<div class="wrap">
    <h1><?php _e('Configurações do Cupom Promo', 'cupom-promo'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('cupom_promo_settings', 'cupom_promo_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enable_woocommerce_integration"><?php _e('Integração com WooCommerce', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="enable_woocommerce_integration" name="enable_woocommerce_integration" value="1" <?php checked($settings['enable_woocommerce_integration'], 1); ?>>
                    <label for="enable_woocommerce_integration"><?php _e('Ativar integração com WooCommerce', 'cupom-promo'); ?></label>
                    <p class="description"><?php _e('Permite aplicar cupons automaticamente nos pedidos do WooCommerce.', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="default_discount_type"><?php _e('Tipo de Desconto Padrão', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <select id="default_discount_type" name="default_discount_type">
                        <option value="percentage" <?php selected($settings['default_discount_type'], 'percentage'); ?>><?php _e('Porcentagem (%)', 'cupom-promo'); ?></option>
                        <option value="fixed" <?php selected($settings['default_discount_type'], 'fixed'); ?>><?php _e('Valor Fixo', 'cupom-promo'); ?></option>
                    </select>
                    <p class="description"><?php _e('Tipo de desconto padrão ao criar novos cupons.', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="currency_symbol"><?php _e('Símbolo da Moeda', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="<?php echo esc_attr($settings['currency_symbol']); ?>" class="regular-text">
                    <p class="description"><?php _e('Símbolo da moeda para exibição dos valores (ex: R$, $, €).', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_email_notifications"><?php _e('Notificações por Email', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="enable_email_notifications" name="enable_email_notifications" value="1" <?php checked($settings['enable_email_notifications'], 1); ?>>
                    <label for="enable_email_notifications"><?php _e('Enviar notificações por email', 'cupom-promo'); ?></label>
                    <p class="description"><?php _e('Envia emails quando cupons são utilizados ou expiram.', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="admin_email"><?php _e('Email do Administrador', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($settings['admin_email']); ?>" class="regular-text">
                    <p class="description"><?php _e('Email para receber notificações sobre cupons.', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="auto_generate_codes"><?php _e('Gerar Códigos Automaticamente', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="auto_generate_codes" name="auto_generate_codes" value="1" <?php checked($settings['auto_generate_codes'], 1); ?>>
                    <label for="auto_generate_codes"><?php _e('Gerar códigos de cupom automaticamente', 'cupom-promo'); ?></label>
                    <p class="description"><?php _e('Gera códigos únicos automaticamente ao criar cupons.', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="code_prefix"><?php _e('Prefixo dos Códigos', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <input type="text" id="code_prefix" name="code_prefix" value="<?php echo esc_attr($settings['code_prefix']); ?>" class="regular-text">
                    <p class="description"><?php _e('Prefixo para códigos gerados automaticamente.', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="code_length"><?php _e('Comprimento dos Códigos', 'cupom-promo'); ?></label>
                </th>
                <td>
                    <input type="number" id="code_length" name="code_length" value="<?php echo esc_attr($settings['code_length']); ?>" class="small-text" min="4" max="20">
                    <p class="description"><?php _e('Comprimento dos códigos gerados automaticamente (4-20 caracteres).', 'cupom-promo'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Configurações Avançadas', 'cupom-promo'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php _e('Limpar Dados', 'cupom-promo'); ?>
                </th>
                <td>
                    <button type="button" class="button" id="clear-coupon-data"><?php _e('Limpar Todos os Cupons', 'cupom-promo'); ?></button>
                    <p class="description"><?php _e('Remove todos os cupons e dados de uso. Esta ação não pode ser desfeita!', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <?php _e('Exportar Configurações', 'cupom-promo'); ?>
                </th>
                <td>
                    <button type="button" class="button" id="export-settings"><?php _e('Exportar Configurações', 'cupom-promo'); ?></button>
                    <p class="description"><?php _e('Exporta as configurações atuais do plugin.', 'cupom-promo'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <?php _e('Importar Configurações', 'cupom-promo'); ?>
                </th>
                <td>
                    <input type="file" id="import-settings-file" accept=".json" style="display: none;">
                    <button type="button" class="button" id="import-settings"><?php _e('Importar Configurações', 'cupom-promo'); ?></button>
                    <p class="description"><?php _e('Importa configurações de um arquivo JSON.', 'cupom-promo'); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Salvar Configurações', 'cupom-promo'); ?>">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Limpar dados
    $('#clear-coupon-data').on('click', function() {
        if (confirm('<?php _e('Tem certeza que deseja limpar todos os dados dos cupons? Esta ação não pode ser desfeita!', 'cupom-promo'); ?>')) {
            // Implementar lógica de limpeza
            alert('<?php _e('Funcionalidade em desenvolvimento.', 'cupom-promo'); ?>');
        }
    });
    
    // Exportar configurações
    $('#export-settings').on('click', function() {
        // Implementar lógica de exportação
        alert('<?php _e('Funcionalidade em desenvolvimento.', 'cupom-promo'); ?>');
    });
    
    // Importar configurações
    $('#import-settings').on('click', function() {
        $('#import-settings-file').click();
    });
    
    $('#import-settings-file').on('change', function() {
        // Implementar lógica de importação
        alert('<?php _e('Funcionalidade em desenvolvimento.', 'cupom-promo'); ?>');
    });
});
</script> 