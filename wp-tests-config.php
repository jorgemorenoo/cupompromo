<?php
/**
 * Configuração do WordPress Test Suite
 */

// Configurações do banco de dados
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// Configurações do WordPress
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

// Configurações de debug
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

// Configurações de teste
define( 'WP_TESTS_DIR', '/tmp/wordpress-tests-lib' );
define( 'WP_CORE_DIR', '/tmp/wordpress' );

// Configurações do plugin
if ( ! defined( 'CUPOMPROMO_PLUGIN_PATH' ) ) {
    define( 'CUPOMPROMO_PLUGIN_PATH', dirname( __FILE__ ) );
}
if ( ! defined( 'CUPOMPROMO_VERSION' ) ) {
    define( 'CUPOMPROMO_VERSION', '1.0.0' );
}

// Configurações de memória
if ( ! defined( 'WP_MEMORY_LIMIT' ) ) {
    define( 'WP_MEMORY_LIMIT', '256M' );
}

// Configurações de cache
if ( ! defined( 'WP_CACHE' ) ) {
    define( 'WP_CACHE', false );
}

// Configurações de upload
define( 'UPLOADS', 'wp-content/uploads' );

// Configurações de tema
define( 'WP_DEFAULT_THEME', 'default' );

// Configurações de plugins
define( 'WP_PLUGIN_DIR', dirname( __FILE__ ) . '/wp-content/plugins/' );
define( 'WPMU_PLUGIN_DIR', dirname( __FILE__ ) . '/wp-content/mu-plugins/' );

// Configurações de conteúdo
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/wp-content' );
define( 'WP_CONTENT_URL', 'http://' . WP_TESTS_DOMAIN . '/wp-content' );

// Configurações de administração
define( 'WP_ADMIN_DIR', WP_CORE_DIR . '/wp-admin' );

// Configurações de includes
define( 'WP_INC_DIR', WP_CORE_DIR . '/wp-includes' );

// Configurações de cron
if ( ! defined( 'DISABLE_WP_CRON' ) ) {
    define( 'DISABLE_WP_CRON', true );
}

// Configurações de multisite
if ( ! defined( 'WP_ALLOW_MULTISITE' ) ) {
    define( 'WP_ALLOW_MULTISITE', false );
}

// Configurações de SSL
if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
    define( 'FORCE_SSL_ADMIN', false );
}

// Configurações de autosave
if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
    define( 'AUTOSAVE_INTERVAL', 60 );
}

// Configurações de post revisions
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
    define( 'WP_POST_REVISIONS', false );
}

// Configurações de trash
if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
    define( 'EMPTY_TRASH_DAYS', 0 );
}

// Configurações de debug
if ( ! defined( 'SAVEQUERIES' ) ) {
    define( 'SAVEQUERIES', false );
}
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
    define( 'WP_DEBUG_DISPLAY', false );
}
if ( ! defined( 'WP_DEBUG_LOG' ) ) {
    define( 'WP_DEBUG_LOG', false );
}
if ( ! defined( 'SCRIPT_DEBUG' ) ) {
    define( 'SCRIPT_DEBUG', false );
}

// Configurações de compressão
if ( ! defined( 'COMPRESS_SCRIPTS' ) ) {
    define( 'COMPRESS_SCRIPTS', false );
}
if ( ! defined( 'COMPRESS_CSS' ) ) {
    define( 'COMPRESS_CSS', false );
}

// Configurações de concatenação
if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
    define( 'CONCATENATE_SCRIPTS', false );
}

// Configurações de enqueue
if ( ! defined( 'ENFORCE_GZIP' ) ) {
    define( 'ENFORCE_GZIP', false );
}

// Configuração do PHP binary
define( 'WP_PHP_BINARY', 'php' );

// Configurações adicionais necessárias
define( 'ABSPATH', WP_CORE_DIR . '/' );
define( 'WPINC', 'wp-includes' );
define( 'WP_LANG_DIR', ABSPATH . WPINC . '/languages' );
define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins/' );
define( 'WPMU_PLUGIN_DIR', ABSPATH . 'wp-content/mu-plugins/' );
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define( 'WP_CONTENT_URL', 'http://' . WP_TESTS_DOMAIN . '/wp-content' );

// Configurações de tabela
$table_prefix = 'wp_';

// Configurações de sal
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' ); 