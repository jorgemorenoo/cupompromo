<?php
/**
 * Template part para paginação
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Verifica se há paginação necessária
global $wp_query;

if (!$wp_query || $wp_query->max_num_pages <= 1) {
    return;
}

// Configurações padrão
$config = wp_parse_args($config ?? [], [
    'show_info' => true,
    'show_prev_next' => true,
    'show_first_last' => false,
    'show_numbers' => true,
    'max_pages' => 5,
    'prev_text' => __('Anterior', 'cupompromo'),
    'next_text' => __('Próxima', 'cupompromo'),
    'first_text' => __('Primeira', 'cupompromo'),
    'last_text' => __('Última', 'cupompromo'),
    'aria_label' => __('Navegação de páginas', 'cupompromo')
]);

$current_page = max(1, get_query_var('paged'));
$total_pages = $wp_query->max_num_pages;

// Classes CSS
$pagination_classes = [
    'cupompromo-pagination',
    'pagination-' . ($config['show_numbers'] ? 'numbered' : 'simple')
];

$pagination_class = implode(' ', array_filter($pagination_classes));
?>

<nav class="<?php echo esc_attr($pagination_class); ?>" aria-label="<?php echo esc_attr($config['aria_label']); ?>">
    
    <?php if ($config['show_info']): ?>
        <div class="pagination-info">
            <span class="pagination-summary">
                <?php 
                $per_page = $wp_query->query_vars['posts_per_page'];
                $start = ($current_page - 1) * $per_page + 1;
                $end = min($current_page * $per_page, $wp_query->found_posts);
                
                printf(
                    __('Mostrando %1$d-%2$d de %3$d resultados', 'cupompromo'),
                    $start,
                    $end,
                    $wp_query->found_posts
                );
                ?>
            </span>
        </div>
    <?php endif; ?>
    
    <div class="pagination-links">
        <?php if ($config['show_first_last'] && $current_page > 2): ?>
            <a href="<?php echo esc_url(get_pagenum_link(1)); ?>" class="pagination-link first-page" aria-label="<?php echo esc_attr($config['first_text']); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="11,17 6,12 11,7"></polyline>
                    <polyline points="17,17 12,12 17,7"></polyline>
                </svg>
                <span class="sr-only"><?php echo esc_html($config['first_text']); ?></span>
            </a>
        <?php endif; ?>
        
        <?php if ($config['show_prev_next'] && $current_page > 1): ?>
            <a href="<?php echo esc_url(get_pagenum_link($current_page - 1)); ?>" class="pagination-link prev-page" aria-label="<?php echo esc_attr($config['prev_text']); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15,18 9,12 15,6"></polyline>
                </svg>
                <span class="pagination-text"><?php echo esc_html($config['prev_text']); ?></span>
            </a>
        <?php endif; ?>
        
        <?php if ($config['show_numbers']): ?>
            <div class="pagination-numbers">
                <?php
                // Calcula o range de páginas a mostrar
                $start_page = max(1, $current_page - floor($config['max_pages'] / 2));
                $end_page = min($total_pages, $start_page + $config['max_pages'] - 1);
                
                // Ajusta o início se necessário
                if ($end_page - $start_page < $config['max_pages'] - 1) {
                    $start_page = max(1, $end_page - $config['max_pages'] + 1);
                }
                
                // Mostra elipses no início se necessário
                if ($start_page > 1): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i == $current_page): ?>
                        <span class="pagination-link current-page" aria-current="page">
                            <?php echo esc_html($i); ?>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo esc_url(get_pagenum_link($i)); ?>" class="pagination-link">
                            <?php echo esc_html($i); ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($config['show_prev_next'] && $current_page < $total_pages): ?>
            <a href="<?php echo esc_url(get_pagenum_link($current_page + 1)); ?>" class="pagination-link next-page" aria-label="<?php echo esc_attr($config['next_text']); ?>">
                <span class="pagination-text"><?php echo esc_html($config['next_text']); ?></span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9,18 15,12 9,6"></polyline>
                </svg>
            </a>
        <?php endif; ?>
        
        <?php if ($config['show_first_last'] && $current_page < $total_pages - 1): ?>
            <a href="<?php echo esc_url(get_pagenum_link($total_pages)); ?>" class="pagination-link last-page" aria-label="<?php echo esc_attr($config['last_text']); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="13,17 18,12 13,7"></polyline>
                    <polyline points="6,17 11,12 6,7"></polyline>
                </svg>
                <span class="sr-only"><?php echo esc_html($config['last_text']); ?></span>
            </a>
        <?php endif; ?>
    </div>
    
    <?php if ($config['show_info']): ?>
        <div class="pagination-meta">
            <span class="pagination-total">
                <?php 
                printf(
                    __('Página %1$d de %2$d', 'cupompromo'),
                    $current_page,
                    $total_pages
                );
                ?>
            </span>
        </div>
    <?php endif; ?>
</nav>

<?php if ($config['show_numbers']): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adiciona funcionalidade de teclado para acessibilidade
    const paginationLinks = document.querySelectorAll('.cupompromo-pagination .pagination-link');
    
    paginationLinks.forEach(function(link) {
        link.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Adiciona indicador de carregamento
    paginationLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (!this.classList.contains('current-page')) {
                // Adiciona classe de carregamento ao container
                const container = document.querySelector('.cupompromo-container, .cupompromo-main-content');
                if (container) {
                    container.classList.add('loading');
                }
            }
        });
    });
});
</script>
<?php endif; ?> 