<?php
/**
 * Template part para exibir um card de cupom
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Verifica se os dados do cupom foram passados
if (!isset($coupon_data) || !is_array($coupon_data)) {
    return;
}

// Configurações padrão
$config = wp_parse_args($config ?? [], [
    'show_store_info' => true,
    'show_categories' => true,
    'show_meta' => true,
    'show_actions' => true,
    'card_style' => 'default', // default, compact, featured
    'show_verification' => true,
    'show_usage_count' => true,
    'show_date' => false,
    'truncate_description' => true,
    'description_length' => 15
]);

// Classes CSS do card
$card_classes = [
    'cupom-coupon-card',
    'card-style-' . $config['card_style']
];

if ($config['card_style'] === 'featured') {
    $card_classes[] = 'featured-card';
}

$card_class = implode(' ', array_filter($card_classes));
?>

<article class="<?php echo esc_attr($card_class); ?>" data-coupon-id="<?php echo esc_attr($coupon_data['id']); ?>">
    
    <?php if ($config['show_store_info']): ?>
        <div class="coupon-header">
            <div class="store-info">
                <?php if (!empty($coupon_data['store_logo'])): ?>
                    <img 
                        src="<?php echo esc_url($coupon_data['store_logo']); ?>" 
                        alt="<?php echo esc_attr($coupon_data['store_name']); ?>"
                        class="store-logo"
                        loading="lazy"
                        width="40"
                        height="40"
                    >
                <?php endif; ?>
                <span class="store-name"><?php echo esc_html($coupon_data['store_name']); ?></span>
            </div>
            
            <div class="coupon-type">
                <span class="badge badge-<?php echo esc_attr($coupon_data['coupon_type']); ?>">
                    <?php echo $coupon_data['coupon_type'] === 'code' ? __('Código', 'cupompromo') : __('Oferta', 'cupompromo'); ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="coupon-content">
        <h3 class="coupon-title">
            <a href="<?php echo esc_url($coupon_data['permalink']); ?>" rel="bookmark">
                <?php echo esc_html($coupon_data['title']); ?>
            </a>
        </h3>
        
        <?php if (!empty($coupon_data['description'])): ?>
            <div class="coupon-description">
                <?php 
                if ($config['truncate_description']) {
                    echo wp_trim_words($coupon_data['description'], $config['description_length'], '...');
                } else {
                    echo wp_kses_post($coupon_data['description']);
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="coupon-discount">
            <span class="discount-value">
                <?php echo esc_html($coupon_data['discount_value']); ?>
            </span>
            <?php if (!empty($coupon_data['discount_type'])): ?>
                <span class="discount-type">
                    <?php echo $coupon_data['discount_type'] === 'percentage' ? '%' : __('OFF', 'cupompromo'); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($coupon_data['coupon_code'])): ?>
            <div class="coupon-code">
                <code><?php echo esc_html($coupon_data['coupon_code']); ?></code>
                <button 
                    class="copy-code" 
                    data-code="<?php echo esc_attr($coupon_data['coupon_code']); ?>" 
                    aria-label="<?php esc_attr_e('Copiar código', 'cupompromo'); ?>"
                    title="<?php esc_attr_e('Copiar código', 'cupompromo'); ?>"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if ($config['show_categories'] && !empty($coupon_data['categories'])): ?>
            <div class="coupon-categories">
                <?php foreach ($coupon_data['categories'] as $category): ?>
                    <a href="<?php echo esc_url($category['url']); ?>" class="category-tag">
                        <?php echo esc_html($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($config['show_meta'] || $config['show_actions']): ?>
        <div class="coupon-footer">
            <?php if ($config['show_meta']): ?>
                <div class="coupon-meta">
                    <?php if ($config['show_verification'] && !empty($coupon_data['verified_date'])): ?>
                        <span class="verification-status verified" title="<?php esc_attr_e('Cupom verificado', 'cupompromo'); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6L9 17l-5-5"></path>
                            </svg>
                            <?php 
                            printf(
                                __('Verificado %s', 'cupompromo'),
                                human_time_diff(strtotime($coupon_data['verified_date']), current_time('timestamp'))
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($config['show_usage_count'] && !empty($coupon_data['click_count'])): ?>
                        <span class="usage-count" title="<?php esc_attr_e('Número de vezes usado', 'cupompromo'); ?>">
                            <?php 
                            printf(
                                _n(
                                    'Usado %d vez',
                                    'Usado %d vezes',
                                    $coupon_data['click_count'],
                                    'cupompromo'
                                ),
                                $coupon_data['click_count']
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($config['show_date'] && !empty($coupon_data['post_date'])): ?>
                        <span class="post-date" title="<?php esc_attr_e('Data de publicação', 'cupompromo'); ?>">
                            <?php 
                            printf(
                                __('Publicado %s', 'cupompromo'),
                                human_time_diff(strtotime($coupon_data['post_date']), current_time('timestamp'))
                            );
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($config['show_actions']): ?>
                <div class="coupon-actions">
                    <a 
                        href="<?php echo esc_url($coupon_data['affiliate_url']); ?>" 
                        class="btn btn-primary get-coupon"
                        target="_blank"
                        rel="noopener noreferrer"
                        data-coupon-id="<?php echo esc_attr($coupon_data['id']); ?>"
                        data-store-id="<?php echo esc_attr($coupon_data['store_id']); ?>"
                        data-coupon-type="<?php echo esc_attr($coupon_data['coupon_type']); ?>"
                    >
                        <?php echo $coupon_data['coupon_type'] === 'code' ? __('Ver Cupom', 'cupompromo') : __('Ativar Oferta', 'cupompromo'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($config['card_style'] === 'featured'): ?>
        <div class="featured-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
            </svg>
            <?php _e('Destaque', 'cupompromo'); ?>
        </div>
    <?php endif; ?>
</article> 