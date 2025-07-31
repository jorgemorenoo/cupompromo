<?php
/**
 * Página de relatórios
 *
 * @package CupomPromo
 * @since 1.0.0
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

$coupon = new CupomPromo_Coupon();
$stats = $coupon->get_usage_stats();
$coupons = $coupon->get_all();
?>

<div class="wrap">
    <h1><?php _e('Relatórios', 'cupom-promo'); ?></h1>
    
    <!-- Cards de estatísticas -->
    <div class="cupom-stats-grid">
        <div class="cupom-stat-card">
            <h3><?php _e('Total de Cupons', 'cupom-promo'); ?></h3>
            <div class="stat-number"><?php echo count($coupons); ?></div>
        </div>
        
        <div class="cupom-stat-card">
            <h3><?php _e('Cupons Ativos', 'cupom-promo'); ?></h3>
            <div class="stat-number">
                <?php 
                $active_count = 0;
                foreach ($coupons as $coupon_item) {
                    if ($coupon_item->status === 'active') {
                        $active_count++;
                    }
                }
                echo $active_count;
                ?>
            </div>
        </div>
        
        <div class="cupom-stat-card">
            <h3><?php _e('Total de Usos', 'cupom-promo'); ?></h3>
            <div class="stat-number"><?php echo $stats['total_usage']; ?></div>
        </div>
        
        <div class="cupom-stat-card">
            <h3><?php _e('Total de Descontos', 'cupom-promo'); ?></h3>
            <div class="stat-number">R$ <?php echo number_format($stats['total_discount'], 2, ',', '.'); ?></div>
        </div>
    </div>
    
    <!-- Gráfico de uso por cupom -->
    <div class="cupom-chart-section">
        <h2><?php _e('Uso por Cupom', 'cupom-promo'); ?></h2>
        <div id="usage-chart" style="width: 100%; height: 400px;"></div>
    </div>
    
    <!-- Tabela de relatórios detalhados -->
    <div class="cupom-reports-section">
        <h2><?php _e('Relatório Detalhado', 'cupom-promo'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Código', 'cupom-promo'); ?></th>
                    <th scope="col"><?php _e('Desconto', 'cupom-promo'); ?></th>
                    <th scope="col"><?php _e('Usos', 'cupom-promo'); ?></th>
                    <th scope="col"><?php _e('Limite', 'cupom-promo'); ?></th>
                    <th scope="col"><?php _e('Status', 'cupom-promo'); ?></th>
                    <th scope="col"><?php _e('Último Uso', 'cupom-promo'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $coupon_item): ?>
                    <?php 
                    $coupon_stats = $coupon->get_usage_stats($coupon_item->id);
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($coupon_item->code); ?></strong></td>
                        <td>
                            <?php 
                            if ($coupon_item->discount_type === 'percentage') {
                                echo number_format($coupon_item->discount_value, 0) . '%';
                            } else {
                                echo 'R$ ' . number_format($coupon_item->discount_value, 2, ',', '.');
                            }
                            ?>
                        </td>
                        <td><?php echo $coupon_stats['total_usage']; ?></td>
                        <td>
                            <?php 
                            if ($coupon_item->usage_limit > 0) {
                                echo $coupon_item->usage_limit;
                            } else {
                                _e('Sem limite', 'cupom-promo');
                            }
                            ?>
                        </td>
                        <td>
                            <span class="status-<?php echo $coupon_item->status; ?>">
                                <?php echo $coupon_item->status === 'active' ? __('Ativo', 'cupom-promo') : __('Inativo', 'cupom-promo'); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            // Aqui você pode implementar a lógica para obter a data do último uso
                            echo __('N/A', 'cupom-promo');
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Exportar relatórios -->
    <div class="cupom-export-section">
        <h2><?php _e('Exportar Relatórios', 'cupom-promo'); ?></h2>
        <p><?php _e('Exporte os dados dos cupons em diferentes formatos:', 'cupom-promo'); ?></p>
        
        <div class="export-buttons">
            <button class="button" id="export-csv"><?php _e('Exportar CSV', 'cupom-promo'); ?></button>
            <button class="button" id="export-pdf"><?php _e('Exportar PDF', 'cupom-promo'); ?></button>
        </div>
    </div>
</div>

<script>
// Dados para o gráfico
var chartData = [
    <?php foreach ($coupons as $coupon_item): ?>
        {
            code: '<?php echo esc_js($coupon_item->code); ?>',
            usage: <?php echo $coupon->get_usage_stats($coupon_item->id)['total_usage']; ?>
        },
    <?php endforeach; ?>
];

// Inicializar gráfico quando a página carregar
jQuery(document).ready(function($) {
    // Aqui você pode implementar a lógica do gráfico
    // Por exemplo, usando Chart.js ou Google Charts
    console.log('Dados do gráfico:', chartData);
});
</script> 