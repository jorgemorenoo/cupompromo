<?php
/**
 * Classe para Custom Post Types e Taxonomias
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
 * Classe Cupompromo_Post_Types
 */
class Cupompromo_Post_Types {
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
    }
    
    /**
     * Registra os Custom Post Types
     */
    public function register_post_types(): void {
        // CPT: cupompromo_store (Lojas)
        register_post_type('cupompromo_store', array(
            'labels' => array(
                'name' => __('Lojas', 'cupompromo'),
                'singular_name' => __('Loja', 'cupompromo'),
                'menu_name' => __('Lojas', 'cupompromo'),
                'add_new' => __('Adicionar Nova', 'cupompromo'),
                'add_new_item' => __('Adicionar Nova Loja', 'cupompromo'),
                'edit_item' => __('Editar Loja', 'cupompromo'),
                'new_item' => __('Nova Loja', 'cupompromo'),
                'view_item' => __('Ver Loja', 'cupompromo'),
                'search_items' => __('Buscar Lojas', 'cupompromo'),
                'not_found' => __('Nenhuma loja encontrada', 'cupompromo'),
                'not_found_in_trash' => __('Nenhuma loja encontrada na lixeira', 'cupompromo'),
            ),
            'public' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'rewrite' => array('slug' => 'loja'),
            'menu_icon' => 'dashicons-store',
            'has_archive' => true,
            'show_in_menu' => false, // Será gerenciado pelo menu principal
        ));
        
        // CPT: cupompromo_coupon (Cupons)
        register_post_type('cupompromo_coupon', array(
            'labels' => array(
                'name' => __('Cupons', 'cupompromo'),
                'singular_name' => __('Cupom', 'cupompromo'),
                'menu_name' => __('Cupons', 'cupompromo'),
                'add_new' => __('Adicionar Novo', 'cupompromo'),
                'add_new_item' => __('Adicionar Novo Cupom', 'cupompromo'),
                'edit_item' => __('Editar Cupom', 'cupompromo'),
                'new_item' => __('Novo Cupom', 'cupompromo'),
                'view_item' => __('Ver Cupom', 'cupompromo'),
                'search_items' => __('Buscar Cupons', 'cupompromo'),
                'not_found' => __('Nenhum cupom encontrado', 'cupompromo'),
                'not_found_in_trash' => __('Nenhum cupom encontrado na lixeira', 'cupompromo'),
            ),
            'public' => true,
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'rewrite' => array('slug' => 'cupom'),
            'menu_icon' => 'dashicons-tickets-alt',
            'has_archive' => true,
            'show_in_menu' => false, // Será gerenciado pelo menu principal
        ));
    }
    
    /**
     * Registra as taxonomias
     */
    public function register_taxonomies(): void {
        // Taxonomia: cupompromo_category (Categorias)
        register_taxonomy('cupompromo_category', array('cupompromo_coupon', 'cupompromo_store'), array(
            'labels' => array(
                'name' => __('Categorias', 'cupompromo'),
                'singular_name' => __('Categoria', 'cupompromo'),
                'search_items' => __('Buscar Categorias', 'cupompromo'),
                'all_items' => __('Todas as Categorias', 'cupompromo'),
                'parent_item' => __('Categoria Pai', 'cupompromo'),
                'parent_item_colon' => __('Categoria Pai:', 'cupompromo'),
                'edit_item' => __('Editar Categoria', 'cupompromo'),
                'update_item' => __('Atualizar Categoria', 'cupompromo'),
                'add_new_item' => __('Adicionar Nova Categoria', 'cupompromo'),
                'new_item_name' => __('Nome da Nova Categoria', 'cupompromo'),
                'menu_name' => __('Categorias', 'cupompromo'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'categoria'),
        ));
        
        // Taxonomia: cupompromo_store_type (Tipo de Loja)
        register_taxonomy('cupompromo_store_type', 'cupompromo_store', array(
            'labels' => array(
                'name' => __('Tipos de Loja', 'cupompromo'),
                'singular_name' => __('Tipo de Loja', 'cupompromo'),
                'search_items' => __('Buscar Tipos', 'cupompromo'),
                'all_items' => __('Todos os Tipos', 'cupompromo'),
                'edit_item' => __('Editar Tipo', 'cupompromo'),
                'update_item' => __('Atualizar Tipo', 'cupompromo'),
                'add_new_item' => __('Adicionar Novo Tipo', 'cupompromo'),
                'new_item_name' => __('Nome do Novo Tipo', 'cupompromo'),
                'menu_name' => __('Tipos de Loja', 'cupompromo'),
            ),
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'tipo-loja'),
        ));
    }
    
    /**
     * Adiciona meta boxes
     */
    public function add_meta_boxes(): void {
        // Meta box para lojas
        add_meta_box(
            'cupompromo_store_details',
            __('Detalhes da Loja', 'cupompromo'),
            array($this, 'store_meta_box_callback'),
            'cupompromo_store',
            'normal',
            'high'
        );
        
        // Meta box para cupons
        add_meta_box(
            'cupompromo_coupon_details',
            __('Detalhes do Cupom', 'cupompromo'),
            array($this, 'coupon_meta_box_callback'),
            'cupompromo_coupon',
            'normal',
            'high'
        );
    }
    
    /**
     * Callback para meta box da loja
     */
    public function store_meta_box_callback($post): void {
        wp_nonce_field('cupompromo_store_meta_box', 'cupompromo_store_meta_box_nonce');
        
        $store_logo = get_post_meta($post->ID, '_store_logo', true);
        $affiliate_base_url = get_post_meta($post->ID, '_affiliate_base_url', true);
        $default_commission = get_post_meta($post->ID, '_default_commission', true);
        $store_website = get_post_meta($post->ID, '_store_website', true);
        $featured_store = get_post_meta($post->ID, '_featured_store', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="store_logo"><?php _e('Logo da Loja', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="url" id="store_logo" name="store_logo" value="<?php echo esc_attr($store_logo); ?>" class="regular-text">
                    <p class="description"><?php _e('URL da logo da loja', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="affiliate_base_url"><?php _e('URL Base de Afiliado', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="url" id="affiliate_base_url" name="affiliate_base_url" value="<?php echo esc_attr($affiliate_base_url); ?>" class="regular-text">
                    <p class="description"><?php _e('URL base para links de afiliado', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="default_commission"><?php _e('Comissão Padrão (%)', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="number" id="default_commission" name="default_commission" value="<?php echo esc_attr($default_commission); ?>" class="small-text" min="0" max="100" step="0.01">
                    <p class="description"><?php _e('Comissão padrão para cupons desta loja', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="store_website"><?php _e('Website da Loja', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="url" id="store_website" name="store_website" value="<?php echo esc_attr($store_website); ?>" class="regular-text">
                    <p class="description"><?php _e('URL do website oficial da loja', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="featured_store"><?php _e('Loja em Destaque', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="featured_store" name="featured_store" value="1" <?php checked($featured_store, '1'); ?>>
                    <label for="featured_store"><?php _e('Marcar como loja em destaque', 'cupompromo'); ?></label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Callback para meta box do cupom
     */
    public function coupon_meta_box_callback($post): void {
        wp_nonce_field('cupompromo_coupon_meta_box', 'cupompromo_coupon_meta_box_nonce');
        
        $store_id = get_post_meta($post->ID, '_store_id', true);
        $coupon_type = get_post_meta($post->ID, '_coupon_type', true);
        $coupon_code = get_post_meta($post->ID, '_coupon_code', true);
        $affiliate_url = get_post_meta($post->ID, '_affiliate_url', true);
        $discount_value = get_post_meta($post->ID, '_discount_value', true);
        $discount_type = get_post_meta($post->ID, '_discount_type', true);
        $expiry_date = get_post_meta($post->ID, '_expiry_date', true);
        $verified_date = get_post_meta($post->ID, '_verified_date', true);
        
        // Buscar lojas
        $stores = get_posts(array(
            'post_type' => 'cupompromo_store',
            'numberposts' => -1,
            'post_status' => 'publish'
        ));
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="store_id"><?php _e('Loja', 'cupompromo'); ?></label>
                </th>
                <td>
                    <select id="store_id" name="store_id" required>
                        <option value=""><?php _e('Selecione uma loja', 'cupompromo'); ?></option>
                        <?php foreach ($stores as $store): ?>
                            <option value="<?php echo $store->ID; ?>" <?php selected($store_id, $store->ID); ?>>
                                <?php echo esc_html($store->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="coupon_type"><?php _e('Tipo de Cupom', 'cupompromo'); ?></label>
                </th>
                <td>
                    <select id="coupon_type" name="coupon_type" required>
                        <option value=""><?php _e('Selecione o tipo', 'cupompromo'); ?></option>
                        <option value="code" <?php selected($coupon_type, 'code'); ?>><?php _e('Código', 'cupompromo'); ?></option>
                        <option value="offer" <?php selected($coupon_type, 'offer'); ?>><?php _e('Oferta Direta', 'cupompromo'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="coupon_code"><?php _e('Código do Cupom', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="text" id="coupon_code" name="coupon_code" value="<?php echo esc_attr($coupon_code); ?>" class="regular-text">
                    <p class="description"><?php _e('Código do cupom (para cupons do tipo "Código")', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="affiliate_url"><?php _e('URL de Afiliado', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="url" id="affiliate_url" name="affiliate_url" value="<?php echo esc_attr($affiliate_url); ?>" class="regular-text">
                    <p class="description"><?php _e('URL de afiliado para este cupom', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="discount_value"><?php _e('Valor do Desconto', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="text" id="discount_value" name="discount_value" value="<?php echo esc_attr($discount_value); ?>" class="regular-text" required>
                    <p class="description"><?php _e('Ex: 10% OFF, R$ 50 OFF', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="discount_type"><?php _e('Tipo de Desconto', 'cupompromo'); ?></label>
                </th>
                <td>
                    <select id="discount_type" name="discount_type" required>
                        <option value=""><?php _e('Selecione o tipo', 'cupompromo'); ?></option>
                        <option value="percentage" <?php selected($discount_type, 'percentage'); ?>><?php _e('Porcentagem', 'cupompromo'); ?></option>
                        <option value="fixed" <?php selected($discount_type, 'fixed'); ?>><?php _e('Valor Fixo', 'cupompromo'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="expiry_date"><?php _e('Data de Expiração', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" id="expiry_date" name="expiry_date" value="<?php echo esc_attr($expiry_date); ?>">
                    <p class="description"><?php _e('Data de expiração do cupom (opcional)', 'cupompromo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="verified_date"><?php _e('Data de Verificação', 'cupompromo'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" id="verified_date" name="verified_date" value="<?php echo esc_attr($verified_date); ?>">
                    <p class="description"><?php _e('Data da última verificação do cupom', 'cupompromo'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Salva os meta boxes
     */
    public function save_meta_boxes(int $post_id): void {
        // Verificar nonce
        if (!isset($_POST['cupompromo_store_meta_box_nonce']) && !isset($_POST['cupompromo_coupon_meta_box_nonce'])) {
            return;
        }
        
        // Verificar permissões
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Salvar dados da loja
        if (isset($_POST['cupompromo_store_meta_box_nonce']) && wp_verify_nonce($_POST['cupompromo_store_meta_box_nonce'], 'cupompromo_store_meta_box')) {
            $this->save_store_meta($post_id);
        }
        
        // Salvar dados do cupom
        if (isset($_POST['cupompromo_coupon_meta_box_nonce']) && wp_verify_nonce($_POST['cupompromo_coupon_meta_box_nonce'], 'cupompromo_coupon_meta_box')) {
            $this->save_coupon_meta($post_id);
        }
    }
    
    /**
     * Salva meta dados da loja
     */
    private function save_store_meta(int $post_id): void {
        $fields = array(
            'store_logo' => 'url',
            'affiliate_base_url' => 'url',
            'default_commission' => 'float',
            'store_website' => 'url',
            'featured_store' => 'checkbox'
        );
        
        foreach ($fields as $field => $type) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                
                switch ($type) {
                    case 'url':
                        $value = esc_url_raw($value);
                        break;
                    case 'float':
                        $value = floatval($value);
                        break;
                    case 'checkbox':
                        $value = '1';
                        break;
                }
                
                update_post_meta($post_id, '_' . $field, $value);
            } else {
                delete_post_meta($post_id, '_' . $field);
            }
        }
    }
    
    /**
     * Salva meta dados do cupom
     */
    private function save_coupon_meta(int $post_id): void {
        $fields = array(
            'store_id' => 'int',
            'coupon_type' => 'text',
            'coupon_code' => 'text',
            'affiliate_url' => 'url',
            'discount_value' => 'text',
            'discount_type' => 'text',
            'expiry_date' => 'datetime',
            'verified_date' => 'datetime'
        );
        
        foreach ($fields as $field => $type) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                
                switch ($type) {
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'url':
                        $value = esc_url_raw($value);
                        break;
                    case 'datetime':
                        $value = sanitize_text_field($value);
                        break;
                    default:
                        $value = sanitize_text_field($value);
                }
                
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    }
} 