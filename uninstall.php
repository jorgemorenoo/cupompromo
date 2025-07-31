<?php
/**
 * Arquivo de desinstalação do plugin Cupom Promo
 *
 * @package CupomPromo
 * @since 1.0.0
 */

// Se não for chamado pelo WordPress, sair
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Verificar se o usuário tem permissão para desinstalar plugins
if (!current_user_can('activate_plugins')) {
    return;
}

// Verificar se deve remover dados
$remove_data = get_option('cupom_promo_remove_data_on_uninstall', false);

if ($remove_data) {
    global $wpdb;
    
    // Remover tabelas do plugin
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cupom_promo_coupons");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cupom_promo_usage");
    
    // Remover opções do plugin
    delete_option('cupom_promo_version');
    delete_option('cupom_promo_settings');
    delete_option('cupom_promo_remove_data_on_uninstall');
    
    // Remover metadados de usuários relacionados ao plugin
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'cupom_promo_%'");
    
    // Remover metadados de posts relacionados ao plugin
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'cupom_promo_%'");
    
    // Limpar cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Limpar rewrite rules
    flush_rewrite_rules();
} 