<?php
/**
 * Configurações de Desenvolvimento para Cupompromo
 * 
 * Este arquivo contém configurações específicas para o ambiente de desenvolvimento.
 * Não inclua este arquivo em produção.
 */

// Configurações de Debug
define('CUPOMPROMO_DEBUG', true);
define('CUPOMPROMO_LOG_LEVEL', 'debug');

// Configurações de Cache
define('CUPOMPROMO_CACHE_ENABLED', false);
define('CUPOMPROMO_CACHE_DURATION', 300);

// Configurações de Analytics
define('CUPOMPROMO_ANALYTICS_ENABLED', true);
define('CUPOMPROMO_TRACKING_ENABLED', true);

// Configurações de APIs (opcional)
define('AWIN_API_KEY', 'your_awin_api_key_here');
define('AWIN_API_SECRET', 'your_awin_api_secret_here');

// Configurações de Email
define('CUPOMPROMO_SMTP_HOST', 'smtp.gmail.com');
define('CUPOMPROMO_SMTP_PORT', 587);
define('CUPOMPROMO_SMTP_USERNAME', 'your_email@gmail.com');
define('CUPOMPROMO_SMTP_PASSWORD', 'your_app_password');

// Configurações de Testes
define('CUPOMPROMO_TEST_MODE', true);
define('CUPOMPROMO_MOCK_DATA', true);

// Configurações de Performance
define('CUPOMPROMO_QUERY_LOG', true);
define('CUPOMPROMO_SLOW_QUERY_THRESHOLD', 1.0);

// Configurações de Segurança (Desenvolvimento)
define('CUPOMPROMO_DISABLE_NONCE_CHECK', false);
define('CUPOMPROMO_ALLOW_INSECURE_REQUESTS', false);

// Configurações de Log
define('CUPOMPROMO_LOG_FILE', WP_CONTENT_DIR . '/logs/cupompromo-debug.log');
define('CUPOMPROMO_ERROR_LOG', WP_CONTENT_DIR . '/logs/cupompromo-errors.log');

// Configurações de Backup
define('CUPOMPROMO_AUTO_BACKUP', true);
define('CUPOMPROMO_BACKUP_RETENTION', 7);

// Configurações de Notificações
define('CUPOMPROMO_EMAIL_NOTIFICATIONS', true);
define('CUPOMPROMO_ADMIN_EMAIL', 'admin@exemplo.com');

// Configurações de Integração
define('CUPOMPROMO_WOOCOMMERCE_INTEGRATION', true);
define('CUPOMPROMO_GOOGLE_ANALYTICS', false);
define('CUPOMPROMO_FACEBOOK_PIXEL', false);

// Configurações de Rate Limiting
define('CUPOMPROMO_RATE_LIMIT_ENABLED', false);
define('CUPOMPROMO_RATE_LIMIT_REQUESTS', 100);
define('CUPOMPROMO_RATE_LIMIT_WINDOW', 3600);

// Configurações de Cache de Templates
define('CUPOMPROMO_TEMPLATE_CACHE', false);
define('CUPOMPROMO_TEMPLATE_CACHE_DURATION', 3600);

// Configurações de Minificação
define('CUPOMPROMO_MINIFY_CSS', false);
define('CUPOMPROMO_MINIFY_JS', false);

// Configurações de CDN
define('CUPOMPROMO_CDN_ENABLED', false);
define('CUPOMPROMO_CDN_URL', '');

// Configurações de Monitoramento
define('CUPOMPROMO_HEALTH_CHECK_ENABLED', true);
define('CUPOMPROMO_PERFORMANCE_MONITORING', true);

// Configurações de Backup Automático
define('CUPOMPROMO_AUTO_BACKUP_ENABLED', true);
define('CUPOMPROMO_BACKUP_SCHEDULE', 'daily');
define('CUPOMPROMO_BACKUP_RETENTION_DAYS', 7);

// Configurações de Notificações de Erro
define('CUPOMPROMO_ERROR_NOTIFICATIONS', true);
define('CUPOMPROMO_ERROR_EMAIL', 'errors@exemplo.com');

// Configurações de Logs Detalhados
define('CUPOMPROMO_DETAILED_LOGGING', true);
define('CUPOMPROMO_LOG_QUERIES', true);
define('CUPOMPROMO_LOG_ACTIONS', true);
define('CUPOMPROMO_LOG_FILTERS', true);

// Configurações de Desenvolvimento Específicas
define('CUPOMPROMO_DEV_MODE', true);
define('CUPOMPROMO_SHOW_DEBUG_INFO', true);
define('CUPOMPROMO_DISABLE_CACHE', true);
define('CUPOMPROMO_SHOW_SQL_QUERIES', true); 