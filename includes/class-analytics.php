<?php
/**
 * Classe de analytics do plugin Cupompromo
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
 * Classe Cupompromo_Analytics
 */
class Cupompromo_Analytics {
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        add_action('wp_ajax_cupompromo_get_analytics', array($this, 'get_analytics'));
        add_action('wp_ajax_cupompromo_export_analytics', array($this, 'export_analytics'));
    }
    
    /**
     * Obtém dados de analytics
     */
    public function get_analytics(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_analytics_nonce')) {
            wp_send_json_error(array(
                'message' => __('Erro de segurança.', 'cupompromo')
            ));
        }
        
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Permissão negada.', 'cupompromo')
            ));
        }
        
        $period = sanitize_text_field($_POST['period'] ?? '30');
        $store_id = intval($_POST['store_id'] ?? 0);
        
        $data = $this->get_analytics_data($period, $store_id);
        
        wp_send_json_success($data);
    }
    
    /**
     * Exporta dados de analytics
     */
    public function export_analytics(): void {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cupompromo_analytics_nonce')) {
            wp_die(__('Erro de segurança.', 'cupompromo'));
        }
        
        // Verificar permissões
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissão negada.', 'cupompromo'));
        }
        
        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        $period = sanitize_text_field($_POST['period'] ?? '30');
        $store_id = intval($_POST['store_id'] ?? 0);
        
        $data = $this->get_analytics_data($period, $store_id);
        
        if ($format === 'csv') {
            $this->export_csv($data);
        } else {
            $this->export_json($data);
        }
    }
    
    /**
     * Obtém dados de analytics
     */
    private function get_analytics_data(string $period, int $store_id): array {
        global $wpdb;
        
        $table_analytics = $wpdb->prefix . 'cupompromo_analytics';
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $where_conditions = array();
        $where_values = array();
        
        // Filtro por período
        $date_filter = '';
        switch ($period) {
            case '7':
                $date_filter = 'DATE(a.created_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                break;
            case '30':
                $date_filter = 'DATE(a.created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                break;
            case '90':
                $date_filter = 'DATE(a.created_at) >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
                break;
            case '365':
                $date_filter = 'DATE(a.created_at) >= DATE_SUB(NOW(), INTERVAL 365 DAY)';
                break;
        }
        
        if ($date_filter) {
            $where_conditions[] = $date_filter;
        }
        
        // Filtro por loja
        if ($store_id > 0) {
            $where_conditions[] = 'c.store_id = %d';
            $where_values[] = $store_id;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Estatísticas gerais
        $stats_sql = "SELECT 
                        COUNT(*) as total_actions,
                        SUM(CASE WHEN a.action_type = 'click' THEN 1 ELSE 0 END) as total_clicks,
                        SUM(CASE WHEN a.action_type = 'conversion' THEN 1 ELSE 0 END) as total_conversions
                      FROM $table_analytics a
                      LEFT JOIN $table_coupons c ON a.coupon_id = c.id
                      $where_clause";
        
        if (!empty($where_values)) {
            $stats_sql = $wpdb->prepare($stats_sql, ...$where_values);
        }
        
        $stats = $wpdb->get_row($stats_sql);
        
        // Top cupons
        $top_coupons_sql = "SELECT 
                              c.title,
                              c.coupon_code,
                              c.click_count,
                              s.name as store_name,
                              COUNT(a.id) as action_count
                            FROM $table_coupons c
                            LEFT JOIN $table_stores s ON c.store_id = s.id
                            LEFT JOIN $table_analytics a ON c.id = a.coupon_id
                            $where_clause
                            GROUP BY c.id
                            ORDER BY action_count DESC
                            LIMIT 10";
        
        if (!empty($where_values)) {
            $top_coupons_sql = $wpdb->prepare($top_coupons_sql, ...$where_values);
        }
        
        $top_coupons = $wpdb->get_results($top_coupons_sql);
        
        // Top lojas
        $top_stores_sql = "SELECT 
                            s.name,
                            s.logo_url,
                            COUNT(a.id) as action_count,
                            SUM(CASE WHEN a.action_type = 'click' THEN 1 ELSE 0 END) as clicks,
                            SUM(CASE WHEN a.action_type = 'conversion' THEN 1 ELSE 0 END) as conversions
                          FROM $table_stores s
                          LEFT JOIN $table_coupons c ON s.id = c.store_id
                          LEFT JOIN $table_analytics a ON c.id = a.coupon_id
                          $where_clause
                          GROUP BY s.id
                          ORDER BY action_count DESC
                          LIMIT 10";
        
        if (!empty($where_values)) {
            $top_stores_sql = $wpdb->prepare($top_stores_sql, ...$where_values);
        }
        
        $top_stores = $wpdb->get_results($top_stores_sql);
        
        // Dados por dia
        $daily_sql = "SELECT 
                        DATE(a.created_at) as date,
                        COUNT(*) as total_actions,
                        SUM(CASE WHEN a.action_type = 'click' THEN 1 ELSE 0 END) as clicks,
                        SUM(CASE WHEN a.action_type = 'conversion' THEN 1 ELSE 0 END) as conversions
                      FROM $table_analytics a
                      LEFT JOIN $table_coupons c ON a.coupon_id = c.id
                      $where_clause
                      GROUP BY DATE(a.created_at)
                      ORDER BY date DESC
                      LIMIT 30";
        
        if (!empty($where_values)) {
            $daily_sql = $wpdb->prepare($daily_sql, ...$where_values);
        }
        
        $daily_data = $wpdb->get_results($daily_sql);
        
        return array(
            'stats' => $stats,
            'top_coupons' => $top_coupons,
            'top_stores' => $top_stores,
            'daily_data' => $daily_data
        );
    }
    
    /**
     * Exporta dados em CSV
     */
    private function export_csv(array $data): void {
        $filename = 'cupompromo-analytics-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Cabeçalho das estatísticas
        fputcsv($output, array('Estatísticas Gerais'));
        fputcsv($output, array('Total de Ações', 'Total de Cliques', 'Total de Conversões'));
        fputcsv($output, array(
            $data['stats']->total_actions ?? 0,
            $data['stats']->total_clicks ?? 0,
            $data['stats']->total_conversions ?? 0
        ));
        fputcsv($output, array(''));
        
        // Top cupons
        fputcsv($output, array('Top Cupons'));
        fputcsv($output, array('Título', 'Código', 'Loja', 'Cliques', 'Ações'));
        foreach ($data['top_coupons'] as $coupon) {
            fputcsv($output, array(
                $coupon->title,
                $coupon->coupon_code,
                $coupon->store_name,
                $coupon->click_count,
                $coupon->action_count
            ));
        }
        fputcsv($output, array(''));
        
        // Top lojas
        fputcsv($output, array('Top Lojas'));
        fputcsv($output, array('Nome', 'Ações', 'Cliques', 'Conversões'));
        foreach ($data['top_stores'] as $store) {
            fputcsv($output, array(
                $store->name,
                $store->action_count,
                $store->clicks,
                $store->conversions
            ));
        }
        fputcsv($output, array(''));
        
        // Dados diários
        fputcsv($output, array('Dados Diários'));
        fputcsv($output, array('Data', 'Total de Ações', 'Cliques', 'Conversões'));
        foreach ($data['daily_data'] as $daily) {
            fputcsv($output, array(
                $daily->date,
                $daily->total_actions,
                $daily->clicks,
                $daily->conversions
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exporta dados em JSON
     */
    private function export_json(array $data): void {
        $filename = 'cupompromo-analytics-' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Obtém estatísticas resumidas
     */
    public function get_summary_stats(): array {
        global $wpdb;
        
        $table_analytics = $wpdb->prefix . 'cupompromo_analytics';
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        // Estatísticas gerais
        $total_stores = $wpdb->get_var("SELECT COUNT(*) FROM $table_stores WHERE status = 'active'");
        $total_coupons = $wpdb->get_var("SELECT COUNT(*) FROM $table_coupons WHERE status = 'active'");
        $total_clicks = $wpdb->get_var("SELECT COUNT(*) FROM $table_analytics WHERE action_type = 'click'");
        $total_conversions = $wpdb->get_var("SELECT COUNT(*) FROM $table_analytics WHERE action_type = 'conversion'");
        
        // Estatísticas dos últimos 30 dias
        $recent_clicks = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_analytics WHERE action_type = 'click' AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            30
        ));
        
        $recent_conversions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_analytics WHERE action_type = 'conversion' AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            30
        ));
        
        // Taxa de conversão
        $conversion_rate = $total_clicks > 0 ? ($total_conversions / $total_clicks) * 100 : 0;
        $recent_conversion_rate = $recent_clicks > 0 ? ($recent_conversions / $recent_clicks) * 100 : 0;
        
        return array(
            'total_stores' => $total_stores,
            'total_coupons' => $total_coupons,
            'total_clicks' => $total_clicks,
            'total_conversions' => $total_conversions,
            'recent_clicks' => $recent_clicks,
            'recent_conversions' => $recent_conversions,
            'conversion_rate' => round($conversion_rate, 2),
            'recent_conversion_rate' => round($recent_conversion_rate, 2)
        );
    }
    
    /**
     * Obtém dados para gráficos
     */
    public function get_chart_data(string $period = '30'): array {
        global $wpdb;
        
        $table_analytics = $wpdb->prefix . 'cupompromo_analytics';
        
        $sql = $wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_actions,
                SUM(CASE WHEN action_type = 'click' THEN 1 ELSE 0 END) as clicks,
                SUM(CASE WHEN action_type = 'conversion' THEN 1 ELSE 0 END) as conversions
             FROM $table_analytics 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            intval($period)
        );
        
        $data = $wpdb->get_results($sql);
        
        $labels = array();
        $clicks_data = array();
        $conversions_data = array();
        
        foreach ($data as $row) {
            $labels[] = date('d/m', strtotime($row->date));
            $clicks_data[] = intval($row->clicks);
            $conversions_data[] = intval($row->conversions);
        }
        
        return array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => __('Cliques', 'cupompromo'),
                    'data' => $clicks_data,
                    'borderColor' => '#622599',
                    'backgroundColor' => 'rgba(98, 37, 153, 0.1)',
                    'tension' => 0.4
                ),
                array(
                    'label' => __('Conversões', 'cupompromo'),
                    'data' => $conversions_data,
                    'borderColor' => '#8BC53F',
                    'backgroundColor' => 'rgba(139, 197, 63, 0.1)',
                    'tension' => 0.4
                )
            )
        );
    }
    
    /**
     * Obtém relatório de performance por loja
     */
    public function get_store_performance(): array {
        global $wpdb;
        
        $table_analytics = $wpdb->prefix . 'cupompromo_analytics';
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        $table_stores = $wpdb->prefix . 'cupompromo_stores';
        
        $sql = "SELECT 
                    s.name,
                    s.logo_url,
                    COUNT(DISTINCT c.id) as total_coupons,
                    COUNT(a.id) as total_actions,
                    SUM(CASE WHEN a.action_type = 'click' THEN 1 ELSE 0 END) as clicks,
                    SUM(CASE WHEN a.action_type = 'conversion' THEN 1 ELSE 0 END) as conversions,
                    CASE 
                        WHEN SUM(CASE WHEN a.action_type = 'click' THEN 1 ELSE 0 END) > 0 
                        THEN (SUM(CASE WHEN a.action_type = 'conversion' THEN 1 ELSE 0 END) / SUM(CASE WHEN a.action_type = 'click' THEN 1 ELSE 0 END)) * 100
                        ELSE 0 
                    END as conversion_rate
                FROM $table_stores s
                LEFT JOIN $table_coupons c ON s.id = c.store_id AND c.status = 'active'
                LEFT JOIN $table_analytics a ON c.id = a.coupon_id
                WHERE s.status = 'active'
                GROUP BY s.id
                ORDER BY clicks DESC";
        
        return $wpdb->get_results($sql);
    }
} 