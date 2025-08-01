/**
 * Dashboard de APIs de Afiliados - Cupompromo
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

(function($) {
    'use strict';

    class CupompromoAPIsDashboard {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.loadInitialData();
            this.startAutoRefresh();
        }

        bindEvents() {
            // Testar conexão com API
            $(document).on('click', '.test-api', (e) => {
                e.preventDefault();
                this.testAPI($(e.target).data('api'));
            });

            // Sincronizar advertisers
            $(document).on('click', '.sync-api', (e) => {
                e.preventDefault();
                this.syncAdvertisers($(e.target).data('api'));
            });

            // Atualizar logs
            $(document).on('click', '#refresh-logs', (e) => {
                e.preventDefault();
                this.loadLogs();
            });

            // Filtros de log
            $(document).on('change', '#log-api-filter, #log-level-filter', () => {
                this.loadLogs();
            });

            // Auto-refresh de estatísticas
            setInterval(() => {
                this.loadStatistics();
            }, 30000); // 30 segundos
        }

        /**
         * Carregar dados iniciais
         */
        loadInitialData() {
            this.loadStatistics();
            this.loadLogs();
        }

        /**
         * Testar conexão com API
         */
        testAPI(apiName) {
            const $button = $(`.test-api[data-api="${apiName}"]`);
            const $status = $(`#status-${apiName}`);
            const originalText = $button.text();

            // Atualizar UI
            $button.prop('disabled', true).text(cupompromoAPIs.strings.testing);
            $status.find('.status-indicator').removeClass('active error').addClass('loading');

            // Fazer requisição
            $.ajax({
                url: cupompromoAPIs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'cupompromo_test_api',
                    api: apiName,
                    nonce: cupompromoAPIs.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $status.find('.status-indicator').removeClass('loading').addClass('active');
                        $status.text(cupompromoAPIs.strings.success);
                        this.showNotification(response.data.message, 'success');
                    } else {
                        $status.find('.status-indicator').removeClass('loading').addClass('error');
                        $status.text(cupompromoAPIs.strings.error);
                        this.showNotification(response.data.message, 'error');
                    }
                },
                error: () => {
                    $status.find('.status-indicator').removeClass('loading').addClass('error');
                    $status.text(cupompromoAPIs.strings.error);
                    this.showNotification('Erro na comunicação com o servidor', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        }

        /**
         * Sincronizar advertisers
         */
        syncAdvertisers(apiName) {
            const $button = $(`.sync-api[data-api="${apiName}"]`);
            const originalText = $button.text();

            // Atualizar UI
            $button.prop('disabled', true).text(cupompromoAPIs.strings.syncing);

            // Fazer requisição
            $.ajax({
                url: cupompromoAPIs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'cupompromo_sync_advertisers',
                    api: apiName,
                    nonce: cupompromoAPIs.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message, 'success');
                        this.loadStatistics(); // Recarregar estatísticas
                    } else {
                        this.showNotification(response.data.message, 'error');
                    }
                },
                error: () => {
                    this.showNotification('Erro na comunicação com o servidor', 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        }

        /**
         * Carregar estatísticas das APIs
         */
        loadStatistics() {
            $.ajax({
                url: cupompromoAPIs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'cupompromo_get_api_stats',
                    nonce: cupompromoAPIs.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStatistics(response.data);
                    }
                }
            });
        }

        /**
         * Atualizar estatísticas na interface
         */
        updateStatistics(stats) {
            Object.keys(stats).forEach(apiName => {
                const apiStats = stats[apiName];
                
                // Atualizar contadores
                $(`#stores-${apiName}`).text(apiStats.stores || 0);
                $(`#coupons-${apiName}`).text(apiStats.coupons || 0);
                $(`#last-sync-${apiName}`).text(apiStats.last_sync || '-');

                // Atualizar status
                const $status = $(`#status-${apiName}`);
                const $indicator = $status.find('.status-indicator');
                
                $indicator.removeClass('active error loading unknown');
                
                switch (apiStats.status) {
                    case 'active':
                        $indicator.addClass('active');
                        $status.text('Ativo');
                        break;
                    case 'error':
                        $indicator.addClass('error');
                        $status.text('Erro');
                        break;
                    case 'loading':
                        $indicator.addClass('loading');
                        $status.text('Carregando...');
                        break;
                    default:
                        $indicator.addClass('unknown');
                        $status.text('Desconhecido');
                }
            });
        }

        /**
         * Carregar logs
         */
        loadLogs() {
            const apiFilter = $('#log-api-filter').val();
            const levelFilter = $('#log-level-filter').val();

            $.ajax({
                url: cupompromoAPIs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'cupompromo_get_api_logs',
                    api_filter: apiFilter,
                    level_filter: levelFilter,
                    nonce: cupompromoAPIs.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.renderLogs(response.data);
                    }
                }
            });
        }

        /**
         * Renderizar logs na interface
         */
        renderLogs(logs) {
            const $container = $('#api-logs');
            
            if (!logs || logs.length === 0) {
                $container.html('<p class="no-logs">Nenhum log encontrado.</p>');
                return;
            }

            let html = '<div class="log-table">';
            html += '<table class="wp-list-table widefat fixed striped">';
            html += '<thead><tr>';
            html += '<th>Data/Hora</th>';
            html += '<th>API</th>';
            html += '<th>Ação</th>';
            html += '<th>Status</th>';
            html += '<th>Mensagem</th>';
            html += '</tr></thead>';
            html += '<tbody>';

            logs.forEach(log => {
                const statusClass = log.status === 'success' ? 'success' : 
                                  log.status === 'error' ? 'error' : 'warning';
                
                html += '<tr>';
                html += `<td>${this.formatDate(log.created_at)}</td>`;
                html += `<td><span class="api-badge api-${log.api_name}">${log.api_name}</span></td>`;
                html += `<td>${log.action}</td>`;
                html += `<td><span class="status-badge status-${statusClass}">${log.status}</span></td>`;
                html += `<td>${log.message || '-'}</td>`;
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            $container.html(html);
        }

        /**
         * Formatar data
         */
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('pt-BR');
        }

        /**
         * Mostrar notificação
         */
        showNotification(message, type = 'info') {
            const $notification = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                </div>
            `);

            $('.wrap').first().prepend($notification);

            // Auto-dismiss após 5 segundos
            setTimeout(() => {
                $notification.fadeOut(() => $notification.remove());
            }, 5000);
        }

        /**
         * Iniciar auto-refresh
         */
        startAutoRefresh() {
            // Auto-refresh de logs a cada 2 minutos
            setInterval(() => {
                this.loadLogs();
            }, 120000);
        }

        /**
         * Exportar dados
         */
        exportData(apiName, type) {
            const data = {
                action: 'cupompromo_export_api_data',
                api: apiName,
                type: type,
                nonce: cupompromoAPIs.nonce
            };

            // Criar formulário temporário para download
            const $form = $('<form>', {
                method: 'POST',
                action: cupompromoAPIs.ajaxurl,
                target: '_blank'
            });

            Object.keys(data).forEach(key => {
                $form.append($('<input>', {
                    type: 'hidden',
                    name: key,
                    value: data[key]
                }));
            });

            $('body').append($form);
            $form.submit();
            $form.remove();
        }

        /**
         * Limpar logs antigos
         */
        clearOldLogs() {
            if (!confirm('Tem certeza que deseja limpar os logs antigos?')) {
                return;
            }

            $.ajax({
                url: cupompromoAPIs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'cupompromo_clear_old_logs',
                    nonce: cupompromoAPIs.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotification('Logs antigos removidos com sucesso', 'success');
                        this.loadLogs();
                    } else {
                        this.showNotification(response.data.message, 'error');
                    }
                }
            });
        }
    }

    // Inicializar quando o DOM estiver pronto
    $(document).ready(() => {
        new CupompromoAPIsDashboard();
    });

})(jQuery); 