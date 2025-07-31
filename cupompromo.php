<?php
/**
 * Plugin Name: Cupompromo
 * Plugin URI: https://github.com/seu-usuario/cupompromo
 * Description: Portal de cupons de desconto robusto e escalável com painel administrativo intuitivo.
 * Version: 1.0.0
 * Author: Seu Nome
 * Author URI: https://seusite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cupompromo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 8.1
 * Network: false
 *
 * @package Cupompromo
 * @version 1.0.0
 * @author Seu Nome
 * @license GPL v2 or later
 */

declare(strict_types=1);

namespace Cupompromo;

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Define constantes do plugin
define('CUPOMPROMO_VERSION', '1.0.0');
define('CUPOMPROMO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CUPOMPROMO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CUPOMPROMO_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Classe principal do plugin Cupompromo
 */
class Plugin
{
    /**
     * Instância única da classe
     */
    private static $instance = null;

    /**
     * Construtor da classe
     */
    private function __construct()
    {
        $this->initHooks();
    }

    /**
     * Retorna a instância única da classe
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializa os hooks do WordPress
     */
    private function initHooks(): void
    {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'loadTextdomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Inicialização do plugin
     */
    public function init(): void
    {
        // Carrega as dependências
        $this->loadDependencies();

        // Inicializa o admin
        if (is_admin()) {
            $this->initAdmin();
        }

        // Inicializa o frontend
        $this->initFrontend();
    }

    /**
     * Carrega as dependências do plugin
     */
    private function loadDependencies(): void
    {
        // Carrega classes principais
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-cupompromo.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-post-types.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-admin.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-frontend.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-api.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-shortcodes.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-gutenberg.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-analytics.php';

// Carrega funcionalidades avançadas
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-cache.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-notifications.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-gamification.php';
require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-advanced-analytics.php';
        require_once CUPOMPROMO_PLUGIN_PATH . 'includes/class-store-card.php';
    }

    /**
     * Inicializa a área administrativa
     */
    private function initAdmin(): void
    {
        new \Cupompromo_Admin();
    }

    /**
     * Inicializa o frontend
     */
    private function initFrontend(): void
    {
        new \Cupompromo_Frontend();
    }

    /**
     * Carrega o textdomain para internacionalização
     */
    public function loadTextdomain(): void
    {
        load_plugin_textdomain(
            'cupompromo',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Ativação do plugin
     */
    public function activate(): void
    {
        // Cria as tabelas necessárias
        $this->createTables();

        // Define a versão atual
        update_option('cupompromo_version', CUPOMPROMO_VERSION);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Desativação do plugin
     */
    public function deactivate(): void
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Cria as tabelas necessárias no banco de dados
     */
    private function createTables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Tabela de lojas
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        $sql_stores = "CREATE TABLE $table_stores (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            logo_url varchar(500) DEFAULT NULL,
            affiliate_base_url varchar(500) DEFAULT NULL,
            default_commission decimal(5,2) DEFAULT 0.00,
            store_description text,
            store_website varchar(500) DEFAULT NULL,
            featured_store tinyint(1) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";

        // Tabela de cupons
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $sql_coupons = "CREATE TABLE $table_coupons (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            store_id mediumint(9) NOT NULL,
            title varchar(255) NOT NULL,
            coupon_type enum('code','offer') NOT NULL,
            coupon_code varchar(100) DEFAULT NULL,
            affiliate_url varchar(500) DEFAULT NULL,
            discount_value varchar(100) NOT NULL,
            discount_type enum('percentage','fixed') NOT NULL,
            expiry_date datetime DEFAULT NULL,
            click_count int(11) DEFAULT 0,
            usage_count int(11) DEFAULT 0,
            verified_date datetime DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY coupon_type (coupon_type),
            KEY status (status)
        ) $charset_collate;";

        // Tabela de categorias
        $table_categories = $wpdb->prefix . 'cupompromo_categories';
        $sql_categories = "CREATE TABLE $table_categories (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            parent_id mediumint(9) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";

        // Tabela de relacionamento cupons-categorias
        $table_coupon_categories = $wpdb->prefix . 'cupompromo_coupon_categories';
        $sql_coupon_categories = "CREATE TABLE $table_coupon_categories (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            coupon_id mediumint(9) NOT NULL,
            category_id mediumint(9) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY coupon_category (coupon_id, category_id),
            KEY coupon_id (coupon_id),
            KEY category_id (category_id)
        ) $charset_collate;";

        // Tabela de analytics
        $table_analytics = $wpdb->prefix . 'cupompromo_analytics';
        $sql_analytics = "CREATE TABLE $table_analytics (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            coupon_id mediumint(9) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            action_type enum('click','conversion') NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text,
            referrer varchar(500) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY coupon_id (coupon_id),
            KEY user_id (user_id),
            KEY action_type (action_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_stores);
        dbDelta($sql_coupons);
        dbDelta($sql_categories);
        dbDelta($sql_coupon_categories);
        dbDelta($sql_analytics);
    }
}

/**
 * Inicializa o plugin
 */
function cupompromoInit()
{
    return Plugin::getInstance();
}

// Inicia o plugin
cupompromoInit(); 