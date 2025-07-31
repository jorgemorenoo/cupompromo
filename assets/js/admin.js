/**
 * JavaScript do Painel Administrativo Cupompromo
 */

(function($) {
    'use strict';

    // Objeto principal do admin
    const CupompromoAdmin = {
        
        /**
         * Inicialização
         */
        init: function() {
            this.bindEvents();
            this.initCharts();
            this.initColorPickers();
        },

        /**
         * Vincula eventos
         */
        bindEvents: function() {
            // Sincronização com Awin
            $(document).on('click', '#cupompromo-sync-awin', this.syncAwin);
            
            // Teste de API
            $(document).on('click', '#cupompromo-test-api', this.testApi);
            
            // Exportar dados
            $(document).on('click', '#cupompromo-export-coupons', this.exportCoupons);
            $(document).on('click', '#cupompromo-export-stats', this.exportStats);
            
            // Filtros de cupons
            $(document).on('change', '.cupompromo-filters select', this.filterCoupons);
            
            // Notificações
            $(document).on('click', '.notice-dismiss', this.dismissNotice);
        },

        /**
         * Sincroniza com API Awin
         */
        syncAwin: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.text();
            
            // Mostra loading
            $button.prop('disabled', true).html('<span class="cupompromo-loading"></span> ' + cupompromo_admin.strings.syncing);
            
            // Faz requisição AJAX
            $.post(cupompromo_admin.ajax_url, {
                action: 'cupompromo_sync_awin',
                nonce: cupompromo_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    CupompromoAdmin.showNotice('success', response.data.message);
                    
                    // Atualiza estatísticas
                    CupompromoAdmin.updateStats();
                } else {
                    CupompromoAdmin.showNotice('error', response.data.message || cupompromo_admin.strings.sync_error);
                }
            })
            .fail(function() {
                CupompromoAdmin.showNotice('error', cupompromo_admin.strings.sync_error);
            })
            .always(function() {
                // Restaura botão
                $button.prop('disabled', false).text(originalText);
            });
        },

        /**
         * Testa conexão com API
         */
        testApi: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $result = $('#cupompromo-test-result');
            const originalText = $button.text();
            
            // Mostra loading
            $button.prop('disabled', true).html('<span class="cupompromo-loading"></span> Testando...');
            $result.removeClass('success error').text('');
            
            // Faz requisição AJAX
            $.post(cupompromo_admin.ajax_url, {
                action: 'cupompromo_test_api',
                nonce: cupompromo_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    $result.addClass('success').text('Conexão estabelecida com sucesso!');
                } else {
                    $result.addClass('error').text(response.data.message || 'Erro na conexão');
                }
            })
            .fail(function() {
                $result.addClass('error').text('Erro na requisição');
            })
            .always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        },

        /**
         * Atualiza estatísticas
         */
        updateStats: function() {
            $.post(cupompromo_admin.ajax_url, {
                action: 'cupompromo_get_stats',
                nonce: cupompromo_admin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    CupompromoAdmin.updateStatsDisplay(response.data);
                }
            });
        },

        /**
         * Atualiza display das estatísticas
         */
        updateStatsDisplay: function(stats) {
            // Atualiza números nas cards
            $('.cupompromo-stat-card').each(function() {
                const $card = $(this);
                const $number = $card.find('h3');
                const $icon = $card.find('.stat-icon');
                
                if ($icon.text().includes('🎫')) {
                    $number.text(stats.total_coupons.toLocaleString());
                } else if ($icon.text().includes('🏪')) {
                    $number.text(stats.total_stores.toLocaleString());
                } else if ($icon.text().includes('👁️')) {
                    $number.text(stats.total_clicks.toLocaleString());
                } else if ($icon.text().includes('📊')) {
                    $number.text(stats.total_usage.toLocaleString());
                }
            });
            
            // Atualiza status da API
            if (stats.awin_configured) {
                $('.cupompromo-api-status .status-info').html(
                    '<p><strong>Configurada:</strong> Sim</p>' +
                    '<p><strong>Última Sincronização:</strong> ' + 
                    (stats.last_sync ? new Date(stats.last_sync).toLocaleString() : 'Nunca') + '</p>'
                );
            }
        },

        /**
         * Filtra cupons
         */
        filterCoupons: function() {
            const $form = $(this).closest('form');
            $form.submit();
        },

        /**
         * Exporta cupons
         */
        exportCoupons: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Exportando...');
            
            // Cria link de download
            const params = new URLSearchParams(window.location.search);
            params.append('action', 'cupompromo_export_coupons');
            params.append('nonce', cupompromo_admin.nonce);
            
            const downloadUrl = cupompromo_admin.ajax_url + '?' + params.toString();
            
            // Faz download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = 'cupons-cupompromo-' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            $button.prop('disabled', false).text(originalText);
        },

        /**
         * Exporta estatísticas
         */
        exportStats: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Exportando...');
            
            // Cria link de download
            const downloadUrl = cupompromo_admin.ajax_url + '?' + new URLSearchParams({
                action: 'cupompromo_export_stats',
                nonce: cupompromo_admin.nonce
            }).toString();
            
            // Faz download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = 'estatisticas-cupompromo-' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            $button.prop('disabled', false).text(originalText);
        },

        /**
         * Mostra notificação
         */
        showNotice: function(type, message) {
            const noticeClass = 'cupompromo-notice ' + type;
            const notice = '<div class="' + noticeClass + '"><p>' + message + '</p></div>';
            
            // Remove notificações existentes
            $('.cupompromo-notice').remove();
            
            // Adiciona nova notificação
            $('.wrap h1').after(notice);
            
            // Remove automaticamente após 5 segundos
            setTimeout(function() {
                $('.cupompromo-notice').fadeOut();
            }, 5000);
        },

        /**
         * Descarta notificação
         */
        dismissNotice: function() {
            $(this).closest('.cupompromo-notice').fadeOut();
        },

        /**
         * Inicializa gráficos
         */
        initCharts: function() {
            // Verifica se Chart.js está disponível
            if (typeof Chart === 'undefined') {
                return;
            }
            
            // Gráfico de cupons por loja
            const storeChart = document.getElementById('coupons-by-store-chart');
            if (storeChart) {
                new Chart(storeChart, {
                    type: 'doughnut',
                    data: {
                        labels: ['Amazon', 'Magazine Luiza', 'Americanas', 'Outros'],
                        datasets: [{
                            data: [30, 25, 20, 25],
                            backgroundColor: [
                                '#622599',
                                '#8BC53F',
                                '#FF6B35',
                                '#E53E3E'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            // Gráfico de cliques por mês
            const clicksChart = document.getElementById('clicks-by-month-chart');
            if (clicksChart) {
                new Chart(clicksChart, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                        datasets: [{
                            label: 'Cliques',
                            data: [1200, 1900, 3000, 5000, 2000, 3000],
                            borderColor: '#622599',
                            backgroundColor: 'rgba(98, 37, 153, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },

        /**
         * Inicializa color pickers
         */
        initColorPickers: function() {
            // Verifica se wp-color-picker está disponível
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.cupompromo-settings-page input[type="color"]').wpColorPicker();
            }
        },

        /**
         * Confirma exclusão
         */
        confirmDelete: function(message) {
            return confirm(message || cupompromo_admin.strings.confirm_delete);
        },

        /**
         * Formata número
         */
        formatNumber: function(num) {
            return num.toLocaleString();
        },

        /**
         * Formata data
         */
        formatDate: function(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        /**
         * Valida formulário
         */
        validateForm: function($form) {
            let isValid = true;
            const errors = [];
            
            // Valida campos obrigatórios
            $form.find('[required]').each(function() {
                const $field = $(this);
                if (!$field.val().trim()) {
                    isValid = false;
                    errors.push($field.attr('name') + ' é obrigatório');
                    $field.addClass('error');
                } else {
                    $field.removeClass('error');
                }
            });
            
            // Valida email
            $form.find('input[type="email"]').each(function() {
                const $field = $(this);
                const email = $field.val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    isValid = false;
                    errors.push('Email inválido');
                    $field.addClass('error');
                }
            });
            
            // Mostra erros
            if (!isValid) {
                this.showNotice('error', errors.join('<br>'));
            }
            
            return isValid;
        },

        /**
         * Salva configurações via AJAX
         */
        saveSettings: function($form) {
            const formData = new FormData($form[0]);
            formData.append('action', 'cupompromo_save_settings');
            formData.append('nonce', cupompromo_admin.nonce);
            
            $.ajax({
                url: cupompromo_admin.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            })
            .done(function(response) {
                if (response.success) {
                    CupompromoAdmin.showNotice('success', 'Configurações salvas com sucesso!');
                } else {
                    CupompromoAdmin.showNotice('error', response.data.message || 'Erro ao salvar configurações');
                }
            })
            .fail(function() {
                CupompromoAdmin.showNotice('error', 'Erro na requisição');
            });
        }
    };

    // Inicializa quando documento estiver pronto
    $(document).ready(function() {
        CupompromoAdmin.init();
    });

    // Expõe para uso global
    window.CupompromoAdmin = CupompromoAdmin;

})(jQuery); 