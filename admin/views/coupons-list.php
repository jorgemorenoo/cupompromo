<?php
/**
 * Página de listagem de cupons
 *
 * @package CupomPromo
 * @since 1.0.0
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

$coupon = new CupomPromo_Coupon();
$coupons = $coupon->get_all();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Cupons', 'cupom-promo'); ?></h1>
    <a href="#" class="page-title-action" id="add-new-coupon"><?php _e('Adicionar Novo', 'cupom-promo'); ?></a>
    <hr class="wp-header-end">
    
    <!-- Modal para adicionar/editar cupom -->
    <div id="coupon-modal" class="cupom-modal" style="display: none;">
        <div class="cupom-modal-content">
            <span class="cupom-modal-close">&times;</span>
            <h2 id="modal-title"><?php _e('Adicionar Cupom', 'cupom-promo'); ?></h2>
            
            <form id="coupon-form">
                <input type="hidden" id="coupon-id" name="coupon_id" value="">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="coupon-code"><?php _e('Código do Cupom', 'cupom-promo'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="coupon-code" name="code" class="regular-text" required>
                            <p class="description"><?php _e('Código único para o cupom (ex: DESCONTO10)', 'cupom-promo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="discount-type"><?php _e('Tipo de Desconto', 'cupom-promo'); ?></label>
                        </th>
                        <td>
                            <select id="discount-type" name="discount_type" required>
                                <option value=""><?php _e('Selecione...', 'cupom-promo'); ?></option>
                                <option value="percentage"><?php _e('Porcentagem (%)', 'cupom-promo'); ?></option>
                                <option value="fixed"><?php _e('Valor Fixo (R$)', 'cupom-promo'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="discount-value"><?php _e('Valor do Desconto', 'cupom-promo'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="discount-value" name="discount_value" class="regular-text" step="0.01" min="0" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="usage-limit"><?php _e('Limite de Uso', 'cupom-promo'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="usage-limit" name="usage_limit" class="regular-text" min="0" value="0">
                            <p class="description"><?php _e('0 = sem limite', 'cupom-promo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="start-date"><?php _e('Data de Início', 'cupom-promo'); ?></label>
                        </th>
                        <td>
                            <input type="datetime-local" id="start-date" name="start_date" class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="end-date"><?php _e('Data de Fim', 'cupom-promo'); ?></label>
                        </th>
                        <td>
                            <input type="datetime-local" id="end-date" name="end_date" class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="status"><?php _e('Status', 'cupom-promo'); ?></label>
                        </th>
                        <td>
                            <select id="status" name="status">
                                <option value="active"><?php _e('Ativo', 'cupom-promo'); ?></option>
                                <option value="inactive"><?php _e('Inativo', 'cupom-promo'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php _e('Salvar Cupom', 'cupom-promo'); ?></button>
                    <button type="button" class="button" id="cancel-coupon"><?php _e('Cancelar', 'cupom-promo'); ?></button>
                </p>
            </form>
        </div>
    </div>
    
    <!-- Tabela de cupons -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select id="bulk-action-selector-top">
                <option value="-1"><?php _e('Ações em massa', 'cupom-promo'); ?></option>
                <option value="delete"><?php _e('Excluir', 'cupom-promo'); ?></option>
            </select>
            <button class="button action" id="doaction"><?php _e('Aplicar', 'cupom-promo'); ?></button>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <input type="checkbox" id="cb-select-all-1">
                </td>
                <th scope="col" class="manage-column column-code"><?php _e('Código', 'cupom-promo'); ?></th>
                <th scope="col" class="manage-column column-discount"><?php _e('Desconto', 'cupom-promo'); ?></th>
                <th scope="col" class="manage-column column-usage"><?php _e('Uso', 'cupom-promo'); ?></th>
                <th scope="col" class="manage-column column-dates"><?php _e('Período', 'cupom-promo'); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e('Status', 'cupom-promo'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e('Ações', 'cupom-promo'); ?></th>
            </tr>
        </thead>
        
        <tbody id="the-list">
            <?php if (empty($coupons)): ?>
                <tr>
                    <td colspan="7"><?php _e('Nenhum cupom encontrado.', 'cupom-promo'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($coupons as $coupon_item): ?>
                    <tr id="coupon-<?php echo $coupon_item->id; ?>">
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="coupon_ids[]" value="<?php echo $coupon_item->id; ?>">
                        </th>
                        <td class="column-code">
                            <strong><?php echo esc_html($coupon_item->code); ?></strong>
                        </td>
                        <td class="column-discount">
                            <?php 
                            if ($coupon_item->discount_type === 'percentage') {
                                echo number_format($coupon_item->discount_value, 0) . '%';
                            } else {
                                echo 'R$ ' . number_format($coupon_item->discount_value, 2, ',', '.');
                            }
                            ?>
                        </td>
                        <td class="column-usage">
                            <?php 
                            $usage_text = $coupon_item->usage_count;
                            if ($coupon_item->usage_limit > 0) {
                                $usage_text .= ' / ' . $coupon_item->usage_limit;
                            }
                            echo $usage_text;
                            ?>
                        </td>
                        <td class="column-dates">
                            <?php 
                            $dates = array();
                            if (!empty($coupon_item->start_date)) {
                                $dates[] = __('Início:', 'cupom-promo') . ' ' . date('d/m/Y H:i', strtotime($coupon_item->start_date));
                            }
                            if (!empty($coupon_item->end_date)) {
                                $dates[] = __('Fim:', 'cupom-promo') . ' ' . date('d/m/Y H:i', strtotime($coupon_item->end_date));
                            }
                            echo !empty($dates) ? implode('<br>', $dates) : __('Sem período definido', 'cupom-promo');
                            ?>
                        </td>
                        <td class="column-status">
                            <span class="status-<?php echo $coupon_item->status; ?>">
                                <?php echo $coupon_item->status === 'active' ? __('Ativo', 'cupom-promo') : __('Inativo', 'cupom-promo'); ?>
                            </span>
                        </td>
                        <td class="column-actions">
                            <a href="#" class="edit-coupon" data-id="<?php echo $coupon_item->id; ?>"><?php _e('Editar', 'cupom-promo'); ?></a> |
                            <a href="#" class="delete-coupon" data-id="<?php echo $coupon_item->id; ?>"><?php _e('Excluir', 'cupom-promo'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div> 