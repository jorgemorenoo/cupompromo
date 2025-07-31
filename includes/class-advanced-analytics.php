<?php
/**
 * Sistema de Analytics Avançado
 *
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal do sistema de analytics
 */
class Cupompromo_Advanced_Analytics {

    /**
     * Tipos de eventos
     */
    const EVENT_TYPES = [
        'page_view' => 'Visualização de página',
        'coupon_click' => 'Clique em cupom',
        'coupon_copy' => 'Cópia de código',
        'coupon_use' => 'Uso de cupom',
        'search_performed' => 'Busca realizada',
        'filter_applied' => 'Filtro aplicado',
        'store_follow' => 'Seguir loja',
        'user_register' => 'Registro de usuário',
        'user_login' => 'Login de usuário',
        'conversion' => 'Conversão',
        'error' => 'Erro'
    ];

    /**
     * Métricas principais
     */
    const METRICS = [
        'clicks' => 'Cliques',
        'conversions' => 'Conversões',
        'revenue' => 'Receita',
        'ctr' => 'Taxa de Clique',
        'cvr' => 'Taxa de Conversão',
        'roas' => 'ROAS',
        'unique_users' => 'Usuários Únicos',
        'sessions' => 'Sessões',
        'bounce_rate' => 'Taxa de Rejeição',
        'avg_session_duration' => 'Duração Média da Sessão'
    ];

    /**
     * Instância singleton
     */
    private static $instance = null;

    /**
     * Cache de analytics
     */
    private $cache;

    /**
     * Construtor privado
     */
    private function __construct() {
        $this->cache = Cupompromo_Cache::get_instance();
        $this->init_hooks();
    }

    /**
     * Obtém instância singleton
     */
    public static function get_instance(): Cupompromo_Advanced_Analytics {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializa hooks
     */
    private function init_hooks(): void {
        // Tracking de eventos
        add_action('wp_head', [$this, 'inject_tracking_code']);
        add_action('wp_footer', [$this, 'inject_analytics_script']);
        
        // Eventos específicos
        add_action('cupompromo_coupon_clicked', [$this, 'track_coupon_click']);
        add_action('cupompromo_coupon_copied', [$this, 'track_coupon_copy']);
        add_action('cupompromo_coupon_used', [$this, 'track_coupon_use']);
        add_action('cupompromo_search_performed', [$this, 'track_search']);
        add_action('cupompromo_filter_applied', [$this, 'track_filter']);
        add_action('cupompromo_store_followed', [$this, 'track_store_follow']);
        add_action('cupompromo_conversion', [$this, 'track_conversion']);
        
        // Limpeza de dados antigos
        add_action('cupompromo_cleanup_analytics', [$this, 'cleanup_old_data']);
        
        // Geração de relatórios
        add_action('cupompromo_generate_reports', [$this, 'generate_daily_reports']);
        
        // Agenda tarefas
        if (!wp_next_scheduled('cupompromo_cleanup_analytics')) {
            wp_schedule_event(time(), 'daily', 'cupompromo_cleanup_analytics');
        }
        
        if (!wp_next_scheduled('cupompromo_generate_reports')) {
            wp_schedule_event(time(), 'daily', 'cupompromo_generate_reports');
        }
    }

    /**
     * Registra evento
     */
    public function track_event(string $event_type, array $data = [], int $user_id = null): bool {
        if (!isset(self::EVENT_TYPES[$event_type])) {
            return false;
        }

        $event = [
            'event_type' => $event_type,
            'user_id' => $user_id ?? get_current_user_id(),
            'session_id' => $this->get_session_id(),
            'timestamp' => current_time('mysql'),
            'data' => $data,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'page_url' => $this->get_current_url(),
            'device_type' => $this->get_device_type(),
            'browser' => $this->get_browser_info()
        ];

        return $this->save_event($event);
    }

    /**
     * Salva evento no banco
     */
    private function save_event(array $event): bool {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_analytics_events';
        
        $result = $wpdb->insert($table_name, [
            'event_type' => $event['event_type'],
            'user_id' => $event['user_id'],
            'session_id' => $event['session_id'],
            'timestamp' => $event['timestamp'],
            'data' => json_encode($event['data']),
            'ip_address' => $event['ip_address'],
            'user_agent' => $event['user_agent'],
            'referrer' => $event['referrer'],
            'page_url' => $event['page_url'],
            'device_type' => $event['device_type'],
            'browser' => $event['browser']
        ]);

        if ($result) {
            // Atualiza cache de métricas
            $this->update_metrics_cache($event);
            
            // Dispara hook
            do_action('cupompromo_event_tracked', $event);
        }

        return (bool) $result;
    }

    /**
     * Atualiza cache de métricas
     */
    private function update_metrics_cache(array $event): void {
        $date = date('Y-m-d', strtotime($event['timestamp']));
        $cache_key = "analytics_metrics_{$date}";
        
        $metrics = $this->cache->get($cache_key, 'analytics') ?: [
            'date' => $date,
            'events' => 0,
            'unique_users' => [],
            'sessions' => [],
            'clicks' => 0,
            'conversions' => 0,
            'revenue' => 0,
            'event_types' => []
        ];
        
        // Incrementa contadores
        $metrics['events']++;
        
        if ($event['user_id']) {
            $metrics['unique_users'][$event['user_id']] = true;
        }
        
        $metrics['sessions'][$event['session_id']] = true;
        
        if ($event['event_type'] === 'coupon_click') {
            $metrics['clicks']++;
        }
        
        if ($event['event_type'] === 'conversion') {
            $metrics['conversions']++;
            $metrics['revenue'] += $event['data']['revenue'] ?? 0;
        }
        
        $metrics['event_types'][$event['event_type']] = ($metrics['event_types'][$event['event_type']] ?? 0) + 1;
        
        // Salva no cache
        $this->cache->set($cache_key, $metrics, 'analytics', 86400);
    }

    /**
     * Obtém métricas para período
     */
    public function get_metrics(string $start_date, string $end_date, array $filters = []): array {
        $cache_key = "metrics_{$start_date}_{$end_date}_" . md5(serialize($filters));
        
        return $this->cache->remember($cache_key, function() use ($start_date, $end_date, $filters) {
            return $this->calculate_metrics($start_date, $end_date, $filters);
        }, 'analytics', 3600);
    }

    /**
     * Calcula métricas
     */
    private function calculate_metrics(string $start_date, string $end_date, array $filters): array {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_analytics_events';
        
        // Query base
        $where_conditions = [
            "timestamp BETWEEN '{$start_date}' AND '{$end_date}'"
        ];
        
        // Aplica filtros
        if (!empty($filters['event_type'])) {
            $where_conditions[] = $wpdb->prepare("event_type = %s", $filters['event_type']);
        }
        
        if (!empty($filters['user_id'])) {
            $where_conditions[] = $wpdb->prepare("user_id = %d", $filters['user_id']);
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Métricas básicas
        $total_events = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}");
        $unique_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$table_name} WHERE {$where_clause} AND user_id > 0");
        $unique_sessions = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM {$table_name} WHERE {$where_clause}");
        
        // Cliques e conversões
        $clicks = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause} AND event_type = 'coupon_click'");
        $conversions = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause} AND event_type = 'conversion'");
        
        // Receita
        $revenue = $wpdb->get_var("SELECT SUM(JSON_EXTRACT(data, '$.revenue')) FROM {$table_name} WHERE {$where_clause} AND event_type = 'conversion'");
        
        // Eventos por tipo
        $events_by_type = $wpdb->get_results("
            SELECT event_type, COUNT(*) as count 
            FROM {$table_name} 
            WHERE {$where_clause} 
            GROUP BY event_type 
            ORDER BY count DESC
        ");
        
        // Dispositivos
        $devices = $wpdb->get_results("
            SELECT device_type, COUNT(*) as count 
            FROM {$table_name} 
            WHERE {$where_clause} 
            GROUP BY device_type 
            ORDER BY count DESC
        ");
        
        // Browsers
        $browsers = $wpdb->get_results("
            SELECT browser, COUNT(*) as count 
            FROM {$table_name} 
            WHERE {$where_clause} 
            GROUP BY browser 
            ORDER BY count DESC
        ");
        
        // Páginas mais visitadas
        $top_pages = $wpdb->get_results("
            SELECT page_url, COUNT(*) as count 
            FROM {$table_name} 
            WHERE {$where_clause} AND event_type = 'page_view' 
            GROUP BY page_url 
            ORDER BY count DESC 
            LIMIT 10
        ");
        
        // Cupons mais clicados
        $top_coupons = $wpdb->get_results("
            SELECT JSON_EXTRACT(data, '$.coupon_id') as coupon_id, COUNT(*) as count 
            FROM {$table_name} 
            WHERE {$where_clause} AND event_type = 'coupon_click' 
            GROUP BY coupon_id 
            ORDER BY count DESC 
            LIMIT 10
        ");
        
        return [
            'period' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ],
            'metrics' => [
                'total_events' => (int) $total_events,
                'unique_users' => (int) $unique_users,
                'unique_sessions' => (int) $unique_sessions,
                'clicks' => (int) $clicks,
                'conversions' => (int) $conversions,
                'revenue' => (float) $revenue,
                'ctr' => $clicks > 0 ? round(($clicks / $total_events) * 100, 2) : 0,
                'cvr' => $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0,
                'roas' => $revenue > 0 ? round($revenue / 100, 2) : 0 // Assumindo 1% de comissão
            ],
            'breakdown' => [
                'events_by_type' => $events_by_type,
                'devices' => $devices,
                'browsers' => $browsers,
                'top_pages' => $top_pages,
                'top_coupons' => $top_coupons
            ]
        ];
    }

    /**
     * Gera relatório diário
     */
    public function generate_daily_reports(): void {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $metrics = $this->get_metrics($yesterday, $yesterday);
        
        // Salva relatório
        $this->save_daily_report($yesterday, $metrics);
        
        // Envia relatório por email se configurado
        if (get_option('cupompromo_send_daily_reports', false)) {
            $this->send_daily_report_email($yesterday, $metrics);
        }
    }

    /**
     * Salva relatório diário
     */
    private function save_daily_report(string $date, array $metrics): void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_analytics_reports';
        
        $wpdb->replace($table_name, [
            'date' => $date,
            'metrics' => json_encode($metrics),
            'created_at' => current_time('mysql')
        ]);
    }

    /**
     * Envia relatório por email
     */
    private function send_daily_report_email(string $date, array $metrics): void {
        $admin_email = get_option('admin_email');
        $subject = sprintf('Relatório Diário Cupompromo - %s', $date);
        
        $message = $this->generate_report_email_content($date, $metrics);
        
        wp_mail($admin_email, $subject, $message, [
            'Content-Type: text/html; charset=UTF-8'
        ]);
    }

    /**
     * Gera conteúdo do email de relatório
     */
    private function generate_report_email_content(string $date, array $metrics): string {
        $m = $metrics['metrics'];
        
        return "
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;'>
            <h2>Relatório Diário - {$date}</h2>
            
            <h3>Métricas Principais</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Eventos Totais:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$m['total_events']}</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Usuários Únicos:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$m['unique_users']}</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Cliques:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$m['clicks']}</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Conversões:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$m['conversions']}</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Receita:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>R$ " . number_format($m['revenue'], 2, ',', '.') . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>CTR:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$m['ctr']}%</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>CVR:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$m['cvr']}%</td>
                </tr>
            </table>
        </div>";
    }

    /**
     * Gera heatmap de cliques
     */
    public function generate_click_heatmap(string $start_date, string $end_date): array {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_analytics_events';
        
        $clicks = $wpdb->get_results($wpdb->prepare(
            "SELECT page_url, JSON_EXTRACT(data, '$.x') as x, JSON_EXTRACT(data, '$.y') as y, COUNT(*) as count 
             FROM {$table_name} 
             WHERE event_type = 'coupon_click' 
             AND timestamp BETWEEN %s AND %s 
             AND JSON_EXTRACT(data, '$.x') IS NOT NULL 
             AND JSON_EXTRACT(data, '$.y') IS NOT NULL 
             GROUP BY page_url, x, y 
             ORDER BY count DESC",
            $start_date,
            $end_date
        ));
        
        $heatmap = [];
        foreach ($clicks as $click) {
            $page = $click->page_url;
            if (!isset($heatmap[$page])) {
                $heatmap[$page] = [];
            }
            
            $heatmap[$page][] = [
                'x' => (int) $click->x,
                'y' => (int) $click->y,
                'count' => (int) $click->count
            ];
        }
        
        return $heatmap;
    }

    /**
     * Calcula ROI por loja
     */
    public function calculate_store_roi(string $start_date, string $end_date): array {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_analytics_events';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                JSON_EXTRACT(data, '$.store_id') as store_id,
                COUNT(CASE WHEN event_type = 'coupon_click' THEN 1 END) as clicks,
                COUNT(CASE WHEN event_type = 'conversion' THEN 1 END) as conversions,
                SUM(CASE WHEN event_type = 'conversion' THEN JSON_EXTRACT(data, '$.revenue') ELSE 0 END) as revenue
             FROM {$table_name} 
             WHERE timestamp BETWEEN %s AND %s 
             AND event_type IN ('coupon_click', 'conversion')
             GROUP BY store_id 
             ORDER BY revenue DESC",
            $start_date,
            $end_date
        ));
        
        $roi_data = [];
        foreach ($results as $result) {
            $store_id = $result->store_id;
            $store = get_post($store_id);
            
            if ($store) {
                $roi_data[] = [
                    'store_id' => $store_id,
                    'store_name' => $store->post_title,
                    'clicks' => (int) $result->clicks,
                    'conversions' => (int) $result->conversions,
                    'revenue' => (float) $result->revenue,
                    'ctr' => $result->clicks > 0 ? round(($result->conversions / $result->clicks) * 100, 2) : 0,
                    'roi' => $result->revenue > 0 ? round($result->revenue / 100, 2) : 0
                ];
            }
        }
        
        return $roi_data;
    }

    /**
     * Métodos auxiliares
     */
    private function get_session_id(): string {
        if (!session_id()) {
            session_start();
        }
        return session_id();
    }

    private function get_client_ip(): string {
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

    private function get_current_url(): string {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
               "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    private function get_device_type(): string {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($user_agent))) {
            return 'tablet';
        }
        
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($user_agent))) {
            return 'mobile';
        }
        
        return 'desktop';
    }

    private function get_browser_info(): string {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (strpos($user_agent, 'Chrome') !== false) return 'Chrome';
        if (strpos($user_agent, 'Firefox') !== false) return 'Firefox';
        if (strpos($user_agent, 'Safari') !== false) return 'Safari';
        if (strpos($user_agent, 'Edge') !== false) return 'Edge';
        if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) return 'Internet Explorer';
        
        return 'Unknown';
    }

    /**
     * Injeção de código de tracking
     */
    public function inject_tracking_code(): void {
        if (!is_admin()) {
            echo '<script>window.cupompromo_analytics = { enabled: true };</script>';
        }
    }

    public function inject_analytics_script(): void {
        if (!is_admin()) {
            ?>
            <script>
            (function() {
                if (!window.cupompromo_analytics || !window.cupompromo_analytics.enabled) return;
                
                // Tracking de página
                cupompromo_track_page_view();
                
                // Tracking de cliques
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.cupompromo-coupon-card')) {
                        cupompromo_track_coupon_click(e);
                    }
                });
                
                function cupompromo_track_page_view() {
                    fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'cupompromo_track_event',
                            event_type: 'page_view',
                            nonce: '<?php echo wp_create_nonce('cupompromo_analytics'); ?>'
                        })
                    });
                }
                
                function cupompromo_track_coupon_click(e) {
                    const card = e.target.closest('.cupompromo-coupon-card');
                    const couponId = card.dataset.couponId;
                    const rect = card.getBoundingClientRect();
                    
                    fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'cupompromo_track_event',
                            event_type: 'coupon_click',
                            coupon_id: couponId,
                            x: Math.round(e.clientX - rect.left),
                            y: Math.round(e.clientY - rect.top),
                            nonce: '<?php echo wp_create_nonce('cupompromo_analytics'); ?>'
                        })
                    });
                }
            })();
            </script>
            <?php
        }
    }

    /**
     * Tracking de eventos específicos
     */
    public function track_coupon_click(int $user_id, int $coupon_id): void {
        $this->track_event('coupon_click', [
            'coupon_id' => $coupon_id,
            'store_id' => get_post_meta($coupon_id, '_store_id', true)
        ], $user_id);
    }

    public function track_coupon_copy(int $user_id, int $coupon_id): void {
        $this->track_event('coupon_copy', [
            'coupon_id' => $coupon_id,
            'store_id' => get_post_meta($coupon_id, '_store_id', true)
        ], $user_id);
    }

    public function track_coupon_use(int $user_id, int $coupon_id): void {
        $this->track_event('coupon_use', [
            'coupon_id' => $coupon_id,
            'store_id' => get_post_meta($coupon_id, '_store_id', true)
        ], $user_id);
    }

    public function track_search(int $user_id, string $query, array $filters): void {
        $this->track_event('search_performed', [
            'query' => $query,
            'filters' => $filters
        ], $user_id);
    }

    public function track_filter(int $user_id, array $filters): void {
        $this->track_event('filter_applied', [
            'filters' => $filters
        ], $user_id);
    }

    public function track_store_follow(int $user_id, int $store_id): void {
        $this->track_event('store_follow', [
            'store_id' => $store_id
        ], $user_id);
    }

    public function track_conversion(int $user_id, int $coupon_id, float $revenue): void {
        $this->track_event('conversion', [
            'coupon_id' => $coupon_id,
            'store_id' => get_post_meta($coupon_id, '_store_id', true),
            'revenue' => $revenue
        ], $user_id);
    }

    /**
     * Limpa dados antigos
     */
    public function cleanup_old_data(): void {
        global $wpdb;

        // Remove eventos antigos (mais de 1 ano)
        $events_table = $wpdb->prefix . 'cupompromo_analytics_events';
        $wpdb->query(
            "DELETE FROM {$events_table} 
             WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 YEAR)"
        );
        
        // Remove relatórios antigos (mais de 2 anos)
        $reports_table = $wpdb->prefix . 'cupompromo_analytics_reports';
        $wpdb->query(
            "DELETE FROM {$reports_table} 
             WHERE date < DATE_SUB(NOW(), INTERVAL 2 YEAR)"
        );
    }
} 