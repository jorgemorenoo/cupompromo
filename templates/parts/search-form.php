<?php
/**
 * Template part para formulário de busca
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Configurações padrão
$config = wp_parse_args($config ?? [], [
    'show_advanced_filters' => true,
    'show_categories' => true,
    'show_stores' => true,
    'show_types' => true,
    'show_sort' => true,
    'placeholder' => __('Buscar cupons de desconto...', 'cupompromo'),
    'form_action' => home_url('/'),
    'search_page' => false,
    'compact_mode' => false,
    'autocomplete' => true,
    'live_search' => false
]);

// Classes CSS do formulário
$form_classes = [
    'cupompromo-search-form',
    'search-form-' . ($config['search_page'] ? 'advanced' : 'simple')
];

if ($config['compact_mode']) {
    $form_classes[] = 'compact-mode';
}

if ($config['live_search']) {
    $form_classes[] = 'live-search';
}

$form_class = implode(' ', array_filter($form_classes));
?>

<form role="search" method="get" class="<?php echo esc_attr($form_class); ?>" action="<?php echo esc_url($config['form_action']); ?>">
    <input type="hidden" name="post_type" value="cupompromo_coupon">
    
    <?php if ($config['search_page']): ?>
        <input type="hidden" name="search_page" value="1">
    <?php endif; ?>
    
    <div class="search-form-container">
        <!-- Campo de Busca Principal -->
        <div class="search-input-wrapper">
            <label for="cupompromo-search" class="sr-only"><?php _e('Buscar cupons', 'cupompromo'); ?></label>
            <div class="search-input-group">
                <input 
                    type="search" 
                    id="cupompromo-search"
                    name="s" 
                    placeholder="<?php echo esc_attr($config['placeholder']); ?>"
                    value="<?php echo esc_attr(get_search_query()); ?>"
                    class="search-input"
                    <?php echo $config['autocomplete'] ? 'autocomplete="off"' : ''; ?>
                    <?php echo $config['live_search'] ? 'data-live-search="true"' : ''; ?>
                    required
                    aria-label="<?php esc_attr_e('Buscar cupons', 'cupompromo'); ?>"
                >
                <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Buscar', 'cupompromo'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
            
            <?php if ($config['live_search']): ?>
                <div class="live-search-results" id="live-search-results" hidden>
                    <div class="live-search-loading">
                        <div class="spinner"></div>
                        <?php _e('Buscando...', 'cupompromo'); ?>
                    </div>
                    <div class="live-search-content"></div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($config['show_advanced_filters'] && !$config['compact_mode']): ?>
            <!-- Filtros Avançados -->
            <div class="advanced-filters" id="advanced-filters">
                <button type="button" class="filter-toggle" aria-expanded="false" aria-controls="filter-panel">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                    </svg>
                    <?php _e('Filtros Avançados', 'cupompromo'); ?>
                    <span class="filter-count" id="active-filter-count"></span>
                </button>
                
                <div id="filter-panel" class="filter-panel" hidden>
                    <div class="filter-grid">
                        <?php if ($config['show_categories']): ?>
                            <div class="filter-group">
                                <label for="search-category"><?php _e('Categoria:', 'cupompromo'); ?></label>
                                <select id="search-category" name="category" class="filter-select">
                                    <option value=""><?php _e('Todas as categorias', 'cupompromo'); ?></option>
                                    <?php
                                    $categories = get_terms([
                                        'taxonomy' => 'cupompromo_category',
                                        'hide_empty' => true,
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'number' => 20
                                    ]);
                                    
                                    foreach ($categories as $category) {
                                        $selected = (isset($_GET['category']) && $_GET['category'] == $category->slug) ? 'selected' : '';
                                        echo sprintf(
                                            '<option value="%s" %s>%s (%d)</option>',
                                            esc_attr($category->slug),
                                            $selected,
                                            esc_html($category->name),
                                            $category->count
                                        );
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['show_stores']): ?>
                            <div class="filter-group">
                                <label for="search-store"><?php _e('Loja:', 'cupompromo'); ?></label>
                                <select id="search-store" name="store" class="filter-select">
                                    <option value=""><?php _e('Todas as lojas', 'cupompromo'); ?></option>
                                    <?php
                                    $stores = get_posts([
                                        'post_type' => 'cupompromo_store',
                                        'posts_per_page' => 20,
                                        'orderby' => 'title',
                                        'order' => 'ASC',
                                        'meta_query' => [
                                            [
                                                'key' => '_store_status',
                                                'value' => 'active',
                                                'compare' => '='
                                            ]
                                        ]
                                    ]);
                                    
                                    foreach ($stores as $store) {
                                        $selected = (isset($_GET['store']) && $_GET['store'] == $store->ID) ? 'selected' : '';
                                        echo sprintf(
                                            '<option value="%s" %s>%s</option>',
                                            esc_attr($store->ID),
                                            $selected,
                                            esc_html($store->post_title)
                                        );
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['show_types']): ?>
                            <div class="filter-group">
                                <label for="search-type"><?php _e('Tipo:', 'cupompromo'); ?></label>
                                <select id="search-type" name="type" class="filter-select">
                                    <option value=""><?php _e('Todos os tipos', 'cupompromo'); ?></option>
                                    <option value="code" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'code'); ?>>
                                        <?php _e('Códigos de Desconto', 'cupompromo'); ?>
                                    </option>
                                    <option value="offer" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'offer'); ?>>
                                        <?php _e('Ofertas Diretas', 'cupompromo'); ?>
                                    </option>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($config['show_sort']): ?>
                            <div class="filter-group">
                                <label for="search-sort"><?php _e('Ordenar por:', 'cupompromo'); ?></label>
                                <select id="search-sort" name="sort" class="filter-select">
                                    <option value="relevance" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'relevance'); ?>>
                                        <?php _e('Relevância', 'cupompromo'); ?>
                                    </option>
                                    <option value="date" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'date'); ?>>
                                        <?php _e('Mais Recentes', 'cupompromo'); ?>
                                    </option>
                                    <option value="popular" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'popular'); ?>>
                                        <?php _e('Mais Populares', 'cupompromo'); ?>
                                    </option>
                                    <option value="discount" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'discount'); ?>>
                                        <?php _e('Maior Desconto', 'cupompromo'); ?>
                                    </option>
                                    <option value="verified" <?php selected(isset($_GET['sort']) ? $_GET['sort'] : '', 'verified'); ?>>
                                        <?php _e('Verificados', 'cupompromo'); ?>
                                    </option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary apply-filters">
                            <?php _e('Aplicar Filtros', 'cupompromo'); ?>
                        </button>
                        <button type="button" class="btn btn-secondary clear-filters">
                            <?php _e('Limpar Filtros', 'cupompromo'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($config['compact_mode'] && $config['show_advanced_filters']): ?>
        <!-- Filtros Compactos -->
        <div class="compact-filters">
            <div class="filter-chips">
                <select name="category" class="filter-chip-select">
                    <option value=""><?php _e('Categoria', 'cupompromo'); ?></option>
                    <?php
                    $categories = get_terms([
                        'taxonomy' => 'cupompromo_category',
                        'hide_empty' => true,
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'number' => 10
                    ]);
                    
                    foreach ($categories as $category) {
                        $selected = (isset($_GET['category']) && $_GET['category'] == $category->slug) ? 'selected' : '';
                        echo sprintf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($category->slug),
                            $selected,
                            esc_html($category->name)
                        );
                    }
                    ?>
                </select>
                
                <select name="type" class="filter-chip-select">
                    <option value=""><?php _e('Tipo', 'cupompromo'); ?></option>
                    <option value="code" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'code'); ?>>
                        <?php _e('Códigos', 'cupompromo'); ?>
                    </option>
                    <option value="offer" <?php selected(isset($_GET['type']) ? $_GET['type'] : '', 'offer'); ?>>
                        <?php _e('Ofertas', 'cupompromo'); ?>
                    </option>
                </select>
            </div>
        </div>
    <?php endif; ?>
</form>

<?php if ($config['live_search']): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('cupompromo-search');
    const liveResults = document.getElementById('live-search-results');
    const liveContent = liveResults.querySelector('.live-search-content');
    const loading = liveResults.querySelector('.live-search-loading');
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            liveResults.hidden = true;
            return;
        }
        
        searchTimeout = setTimeout(function() {
            performLiveSearch(query);
        }, 300);
    });
    
    function performLiveSearch(query) {
        liveResults.hidden = false;
        loading.style.display = 'block';
        liveContent.innerHTML = '';
        
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'cupompromo_live_search',
                query: query,
                nonce: '<?php echo wp_create_nonce('cupompromo_live_search'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.success && data.data.results.length > 0) {
                liveContent.innerHTML = data.data.html;
            } else {
                liveContent.innerHTML = '<div class="no-results"><?php esc_html_e('Nenhum resultado encontrado', 'cupompromo'); ?></div>';
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            liveContent.innerHTML = '<div class="error"><?php esc_html_e('Erro na busca', 'cupompromo'); ?></div>';
        });
    }
    
    // Fechar resultados ao clicar fora
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !liveResults.contains(e.target)) {
            liveResults.hidden = true;
        }
    });
});
</script>
<?php endif; ?> 