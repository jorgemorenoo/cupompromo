<?php
/**
 * Classe Cupompromo_Store_Card
 * 
 * Responsável por renderizar cards de lojas de forma consistente e reutilizável.
 * Segue o padrão de design system definido no .cursorrules.
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
 * Classe Cupompromo_Store_Card
 */
class Cupompromo_Store_Card {
    
    /**
     * Dados da loja
     */
    private $store;
    
    /**
     * Configurações do card
     */
    private $config;
    
    /**
     * Cache de dados calculados
     */
    private $cache = array();
    
    /**
     * Construtor da classe
     */
    public function __construct(object $store, array $config = array()) {
        $this->store = $store;
        $this->config = wp_parse_args($config, $this->get_default_config());
        $this->validate_store_data();
    }
    
    /**
     * Valida os dados da loja
     */
    private function validate_store_data(): void {
        if (!isset($this->store->id) || !isset($this->store->name)) {
            throw new InvalidArgumentException(__('Dados da loja inválidos', 'cupompromo'));
        }
    }
    
    /**
     * Obtém configurações padrão
     */
    private function get_default_config(): array {
        return array(
            'show_logo' => true,
            'show_description' => true,
            'show_stats' => true,
            'show_featured_badge' => true,
            'show_coupons_count' => true,
            'card_style' => 'default', // default, minimal, featured, compact
            'logo_size' => 'medium', // small, medium, large
            'description_length' => 100,
            'link_target' => '_self',
            'css_classes' => array(),
            'animation' => true,
            'lazy_loading' => true,
            'enable_cache' => true,
            'cache_duration' => 3600, // 1 hora
            'show_commission' => true,
            'show_website_link' => true,
            'truncate_description' => true
        );
    }
    
    /**
     * Renderiza o card da loja
     */
    public function render(): string {
        // Verifica cache se habilitado
        if ($this->config['enable_cache']) {
            $cache_key = $this->get_cache_key();
            $cached_output = wp_cache_get($cache_key, 'cupompromo_store_cards');
            if ($cached_output !== false) {
                return $cached_output;
            }
        }
        
        ob_start();
        ?>
        <div class="cupompromo-store-card <?php echo $this->get_css_classes(); ?>" 
             data-store-id="<?php echo esc_attr($this->store->id); ?>"
             <?php echo $this->get_data_attributes(); ?>>
            
            <?php $this->render_card_header(); ?>
            <?php $this->render_card_content(); ?>
            <?php $this->render_card_footer(); ?>
            
        </div>
        <?php
        $output = ob_get_clean();
        
        // Salva no cache se habilitado
        if ($this->config['enable_cache']) {
            wp_cache_set($cache_key, $output, 'cupompromo_store_cards', $this->config['cache_duration']);
        }
        
        return $output;
    }
    
    /**
     * Gera chave de cache única
     */
    private function get_cache_key(): string {
        $config_hash = wp_hash(serialize($this->config));
        return 'store_card_' . $this->store->id . '_' . $config_hash;
    }
    
    /**
     * Renderiza o cabeçalho do card
     */
    private function render_card_header(): void {
        ?>
        <div class="store-card-header">
            <?php if ($this->config['show_logo']): ?>
                <div class="store-logo-wrapper">
                    <?php $this->render_store_logo(); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($this->config['show_featured_badge'] && $this->is_featured()): ?>
                <div class="featured-badge">
                    <span class="badge-icon">⭐</span>
                    <span class="badge-text"><?php _e('Destaque', 'cupompromo'); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Renderiza o logo da loja
     */
    private function render_store_logo(): void {
        if (!empty($this->store->logo_url)) {
            $logo_classes = 'store-logo store-logo-' . $this->config['logo_size'];
            $loading_attr = $this->config['lazy_loading'] ? 'loading="lazy"' : '';
            
            ?>
            <img src="<?php echo esc_url($this->store->logo_url); ?>" 
                 alt="<?php echo esc_attr($this->store->name); ?>"
                 class="<?php echo esc_attr($logo_classes); ?>"
                 <?php echo $loading_attr; ?>
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <?php
            // Fallback placeholder
            $this->render_logo_placeholder(true);
        } else {
            $this->render_logo_placeholder(false);
        }
    }
    
    /**
     * Renderiza placeholder do logo
     */
    private function render_logo_placeholder(bool $is_fallback = false): void {
        $initial = strtoupper(substr($this->store->name, 0, 1));
        $color = $this->generate_color_from_string($this->store->name);
        $display_style = $is_fallback ? 'display: none;' : '';
        
        ?>
        <div class="store-logo-placeholder" style="background-color: <?php echo esc_attr($color); ?>; <?php echo $display_style; ?>">
            <span class="store-initial"><?php echo esc_html($initial); ?></span>
        </div>
        <?php
    }
    
    /**
     * Renderiza o conteúdo do card
     */
    private function render_card_content(): void {
        ?>
        <div class="store-card-content">
            <h3 class="store-name">
                <a href="<?php echo $this->get_store_url(); ?>" 
                   target="<?php echo esc_attr($this->config['link_target']); ?>"
                   rel="nofollow"
                   title="<?php echo esc_attr($this->store->name); ?>">
                    <?php echo esc_html($this->store->name); ?>
                </a>
            </h3>
            
            <?php if ($this->config['show_description'] && !empty($this->store->store_description)): ?>
                <div class="store-description">
                    <?php echo $this->truncate_text($this->store->store_description, $this->config['description_length']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($this->config['show_stats']): ?>
                <div class="store-stats">
                    <?php $this->render_store_stats(); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Renderiza estatísticas da loja
     */
    private function render_store_stats(): void {
        $coupons_count = $this->get_store_coupons_count();
        $avg_discount = $this->get_average_discount();
        
        ?>
        <div class="stats-grid">
            <?php if ($this->config['show_coupons_count']): ?>
                <div class="stat-item">
                    <span class="stat-icon" aria-hidden="true">🎫</span>
                    <span class="stat-value"><?php echo number_format($coupons_count); ?></span>
                    <span class="stat-label"><?php _e('cupons', 'cupompromo'); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($avg_discount > 0): ?>
                <div class="stat-item">
                    <span class="stat-icon" aria-hidden="true">💰</span>
                    <span class="stat-value"><?php echo number_format($avg_discount, 0); ?>%</span>
                    <span class="stat-label"><?php _e('desconto médio', 'cupompromo'); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Renderiza o rodapé do card
     */
    private function render_card_footer(): void {
        ?>
        <div class="store-card-footer">
            <div class="store-actions">
                <a href="<?php echo $this->get_store_url(); ?>" 
                   class="btn-view-coupons"
                   target="<?php echo esc_attr($this->config['link_target']); ?>"
                   rel="nofollow"
                   aria-label="<?php printf(__('Ver cupons da %s', 'cupompromo'), esc_attr($this->store->name)); ?>">
                    <span class="btn-icon" aria-hidden="true">🔍</span>
                    <span class="btn-text"><?php _e('Ver Cupons', 'cupompromo'); ?></span>
                </a>
                
                <?php if ($this->config['show_website_link'] && !empty($this->store->store_website)): ?>
                    <a href="<?php echo esc_url($this->store->store_website); ?>" 
                       class="btn-visit-store"
                       target="_blank"
                       rel="nofollow"
                       aria-label="<?php printf(__('Visitar site da %s', 'cupompromo'), esc_attr($this->store->name)); ?>">
                        <span class="btn-icon" aria-hidden="true">🌐</span>
                        <span class="btn-text"><?php _e('Visitar Loja', 'cupompromo'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($this->config['show_commission'] && $this->store->default_commission > 0): ?>
                <div class="commission-info">
                    <span class="commission-label"><?php _e('Comissão:', 'cupompromo'); ?></span>
                    <span class="commission-value"><?php echo number_format($this->store->default_commission, 1); ?>%</span>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Obtém classes CSS do card
     */
    private function get_css_classes(): string {
        $classes = array(
            'cupompromo-store-card',
            'card-style-' . $this->config['card_style'],
            'logo-size-' . $this->config['logo_size']
        );
        
        if ($this->is_featured()) {
            $classes[] = 'is-featured';
        }
        
        if ($this->config['animation']) {
            $classes[] = 'has-animation';
        }
        
        if (!$this->is_active()) {
            $classes[] = 'is-inactive';
        }
        
        if (!empty($this->config['css_classes'])) {
            $classes = array_merge($classes, $this->config['css_classes']);
        }
        
        return esc_attr(implode(' ', $classes));
    }
    
    /**
     * Obtém atributos de dados
     */
    private function get_data_attributes(): string {
        $attributes = array(
            'data-store-id' => $this->store->id,
            'data-store-slug' => $this->store->slug ?? '',
            'data-featured' => $this->is_featured() ? 'true' : 'false',
            'data-active' => $this->is_active() ? 'true' : 'false',
            'data-coupons-count' => $this->get_store_coupons_count(),
            'data-avg-discount' => $this->get_average_discount()
        );
        
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
        
        return $html;
    }
    
    /**
     * Obtém URL da loja
     */
    private function get_store_url(): string {
        return home_url('/loja/' . ($this->store->slug ?? sanitize_title($this->store->name)));
    }
    
    /**
     * Obtém contagem de cupons da loja
     */
    private function get_store_coupons_count(): int {
        // Verifica cache
        if (isset($this->cache['coupons_count'])) {
            return $this->cache['coupons_count'];
        }
        
        global $wpdb;
        
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        
        $count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_coupons WHERE store_id = %d AND status = 'active'",
            $this->store->id
        ));
        
        $this->cache['coupons_count'] = $count;
        return $count;
    }
    
    /**
     * Obtém desconto médio da loja
     */
    private function get_average_discount(): float {
        // Verifica cache
        if (isset($this->cache['avg_discount'])) {
            return $this->cache['avg_discount'];
        }
        
        global $wpdb;
        
        $table_coupons = $wpdb->prefix . 'cupompromo_coupons';
        
        $avg_discount = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(CAST(REPLACE(REPLACE(discount_value, '%', ''), 'OFF', '') AS DECIMAL(5,2)))
             FROM $table_coupons 
             WHERE store_id = %d AND status = 'active' AND discount_type = 'percentage'",
            $this->store->id
        ));
        
        $result = $avg_discount ? (float) $avg_discount : 0.0;
        $this->cache['avg_discount'] = $result;
        return $result;
    }
    
    /**
     * Trunca texto para o tamanho especificado
     */
    private function truncate_text(string $text, int $length): string {
        if (!$this->config['truncate_description'] || strlen($text) <= $length) {
            return esc_html($text);
        }
        
        $truncated = substr($text, 0, $length);
        $last_space = strrpos($truncated, ' ');
        
        if ($last_space !== false) {
            $truncated = substr($truncated, 0, $last_space);
        }
        
        return esc_html($truncated) . '...';
    }
    
    /**
     * Gera cor baseada no nome da loja
     */
    private function generate_color_from_string(string $string): string {
        $colors = array(
            '#622599', // Roxo principal
            '#8BC53F', // Verde secundário
            '#FF6B35', // Laranja accent
            '#E53E3E', // Vermelho
            '#3182CE', // Azul
            '#38A169', // Verde
            '#D69E2E', // Amarelo
            '#805AD5'  // Roxo claro
        );
        
        $hash = crc32($string);
        $index = abs($hash) % count($colors);
        
        return $colors[$index];
    }
    
    /**
     * Renderiza card em modo minimalista
     */
    public function render_minimal(): string {
        $this->config['card_style'] = 'minimal';
        $this->config['show_description'] = false;
        $this->config['show_stats'] = false;
        
        return $this->render();
    }
    
    /**
     * Renderiza card em modo destaque
     */
    public function render_featured(): string {
        $this->config['card_style'] = 'featured';
        $this->config['logo_size'] = 'large';
        $this->config['description_length'] = 150;
        
        return $this->render();
    }
    
    /**
     * Renderiza card compacto
     */
    public function render_compact(): string {
        $this->config['card_style'] = 'compact';
        $this->config['logo_size'] = 'small';
        $this->config['show_description'] = false;
        $this->config['show_stats'] = false;
        $this->config['show_featured_badge'] = false;
        
        return $this->render();
    }
    
    /**
     * Renderiza card para lista horizontal
     */
    public function render_horizontal(): string {
        $this->config['card_style'] = 'horizontal';
        $this->config['logo_size'] = 'small';
        $this->config['show_description'] = false;
        $this->config['show_stats'] = false;
        
        return $this->render();
    }
    
    /**
     * Obtém dados da loja
     */
    public function get_store(): object {
        return $this->store;
    }
    
    /**
     * Define configurações
     */
    public function set_config(array $config): void {
        $this->config = wp_parse_args($config, $this->config);
        // Limpa cache ao alterar configurações
        $this->cache = array();
    }
    
    /**
     * Obtém configurações
     */
    public function get_config(): array {
        return $this->config;
    }
    
    /**
     * Verifica se a loja está ativa
     */
    public function is_active(): bool {
        return $this->store->status === 'active';
    }
    
    /**
     * Verifica se a loja é destaque
     */
    public function is_featured(): bool {
        return (bool) ($this->store->featured_store ?? false);
    }
    
    /**
     * Obtém informações resumidas da loja
     */
    public function get_summary(): array {
        return array(
            'id' => $this->store->id,
            'name' => $this->store->name,
            'slug' => $this->store->slug ?? sanitize_title($this->store->name),
            'logo_url' => $this->store->logo_url ?? '',
            'website' => $this->store->store_website ?? '',
            'featured' => $this->is_featured(),
            'active' => $this->is_active(),
            'coupons_count' => $this->get_store_coupons_count(),
            'avg_discount' => $this->get_average_discount(),
            'commission' => $this->store->default_commission ?? 0
        );
    }
    
    /**
     * Obtém dados para JSON
     */
    public function to_json(): array {
        return array(
            'store' => $this->get_summary(),
            'config' => $this->config,
            'html' => $this->render()
        );
    }
    
    /**
     * Limpa cache da instância
     */
    public function clear_cache(): void {
        $this->cache = array();
    }
    
    /**
     * Verifica se o card tem cupons ativos
     */
    public function has_active_coupons(): bool {
        return $this->get_store_coupons_count() > 0;
    }
    
    /**
     * Obtém estatísticas da loja
     */
    public function get_stats(): array {
        return array(
            'coupons_count' => $this->get_store_coupons_count(),
            'avg_discount' => $this->get_average_discount(),
            'has_active_coupons' => $this->has_active_coupons(),
            'is_featured' => $this->is_featured(),
            'is_active' => $this->is_active()
        );
    }
} 