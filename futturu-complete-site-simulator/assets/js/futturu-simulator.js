/**
 * Futturu Simulator Frontend JavaScript
 */

(function($) {
    'use strict';

    const futturuSimulator = {
        currentStep: 1,
        totalSteps: 9,
        form: null,
        
        init: function() {
            this.form = $('#futturu-simulator-form');
            this.bindEvents();
            this.updateProgressBar();
            this.handleConditionalFields();
        },

        bindEvents: function() {
            // Navigation buttons
            $(document).on('click', '.btn-next', (e) => {
                e.preventDefault();
                this.nextStep();
            });

            $(document).on('click', '.btn-prev', (e) => {
                e.preventDefault();
                this.prevStep();
            });

            // Form submission
            this.form.on('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            // Conditional fields
            $('#site_type').on('change', () => {
                const isOther = $('#site_type').val() === 'other';
                $('#site_type_other').toggle(isOther);
            });

            $('#num_pages').on('change', () => {
                const isCustom = $('#num_pages').val() === 'sob_medida';
                $('#num_pages_custom').toggle(isCustom);
            });

            $('#menu_outra_check').on('change', function() {
                $('#menu_pages_other').toggle($(this).is(':checked'));
            });

            $('#addon_outro_check').on('change', function() {
                $('#addons_other').toggle($(this).is(':checked'));
            });

            $('#google_outro_check').on('change', function() {
                $('#google_marketing_other').toggle($(this).is(':checked'));
            });

            // Real-time calculation on step change
            $(document).on('click', '.btn-next', () => {
                if (this.currentStep >= 8) {
                    this.calculateAndShowSummary();
                }
            });
        },

        nextStep: function() {
            const currentStepEl = $(`.simulator-step[data-step="${this.currentStep}"]`);
            
            // Validate current step
            if (!this.validateStep(this.currentStep)) {
                return;
            }

            // Hide current step
            currentStepEl.removeClass('active');

            // Show next step
            this.currentStep++;
            const nextStepEl = $(`.simulator-step[data-step="${this.currentStep}"]`);
            nextStepEl.addClass('active');

            // Update progress bar
            this.updateProgressBar();

            // Scroll to top of form
            $('html, body').animate({
                scrollTop: $('#futturuSimulator').offset().top - 100
            }, 300);

            // If moving to summary step, calculate
            if (this.currentStep === 9) {
                this.calculateAndShowSummary();
            }

            // Handle conditional fields
            this.handleConditionalFields();
        },

        prevStep: function() {
            if (this.currentStep > 1) {
                const currentStepEl = $(`.simulator-step[data-step="${this.currentStep}"]`);
                currentStepEl.removeClass('active');

                this.currentStep--;
                const prevStepEl = $(`.simulator-step[data-step="${this.currentStep}"]`);
                prevStepEl.addClass('active');

                this.updateProgressBar();

                $('html, body').animate({
                    scrollTop: $('#futturuSimulator').offset().top - 100
                }, 300);
            }
        },

        validateStep: function(step) {
            const stepEl = $(`.simulator-step[data-step="${step}"]`);
            let isValid = true;
            const self = this;

            // Remove previous errors
            stepEl.find('.error-message').remove();
            stepEl.find('.form-group.error').removeClass('error');

            // Required fields validation
            stepEl.find('[required]').each(function() {
                const $this = $(this);
                const value = $this.val();

                if (!value || value.trim() === '') {
                    isValid = false;
                    self.showError($this, futturuSimulator.strings.required);
                }

                // Email validation
                if ($this.attr('type') === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        self.showError($this, futturuSimulator.strings.invalidEmail);
                    }
                }

                // Phone validation (Brazilian format)
                if ($this.attr('type') === 'tel' && value) {
                    const phone = value.replace(/\D/g, '');
                    if (phone.length < 10 || phone.length > 11) {
                        isValid = false;
                        self.showError($this, futturuSimulator.strings.invalidPhone);
                    }
                }
            });

            // Radio button validation for complexity
            if (step === 1) {
                const complexitySelected = stepEl.find('input[name="complexity_level"]:checked').length > 0;
                if (!complexitySelected) {
                    isValid = false;
                    stepEl.find('input[name="complexity_level"]').first().closest('.form-group').addClass('error');
                }
            }

            if (!isValid) {
                alert(futturuSimulator.strings.error);
            }

            return isValid;
        },

        showError: function($field, message) {
            $field.closest('.form-group').addClass('error');
            $field.after(`<span class="error-message">${message}</span>`);
        },

        updateProgressBar: function() {
            const self = this;
            $('.progress-step').each(function(index) {
                const $this = $(this);
                const stepNum = parseInt($this.data('step')) + 1;

                $this.removeClass('active completed');

                if (stepNum === self.currentStep) {
                    $this.addClass('active');
                } else if (stepNum < self.currentStep) {
                    $this.addClass('completed');
                }
            });
        },

        handleConditionalFields: function() {
            // Site type other field
            if ($('#site_type').val() === 'other') {
                $('#site_type_other').show();
            }

            // Num pages custom field
            if ($('#num_pages').val() === 'sob_medida') {
                $('#num_pages_custom').show();
            }
        },

        calculateAndShowSummary: function() {
            const formData = this.getFormData();
            
            // Debug log
            console.log('Form data being sent:', formData);
            
            // Check if we have minimum required data
            if (!formData || Object.keys(formData).length === 0) {
                console.error('Nenhum dado no formulário');
                $('#summaryContainer').html('<p>Erro: Nenhum dado encontrado. Por favor, preencha o formulário.</p>');
                return;
            }
            
            $.ajax({
                url: futturuSimulator.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'futturu_calculate',
                    nonce: futturuSimulator.nonce,
                    ...formData
                },
                beforeSend: () => {
                    console.log('Iniciando cálculo AJAX...');
                    $('#summaryContainer').html('<p>' + futturuSimulator.strings.calculating + '</p>');
                    $('#investmentValue').text('-');
                    $('#investmentRange').text('-');
                    $('#deliveryValue').text('-');
                },
                success: (response) => {
                    console.log('AJAX Response:', response);
                    if (response.success && response.data && response.data.calculation) {
                        console.log('Cálculo recebido:', response.data.calculation);
                        this.displaySummary(formData, response.data.calculation);
                    } else {
                        console.error('Calculation error:', response);
                        $('#summaryContainer').html('<p>Erro ao calcular. Tente novamente.</p>');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', status, error);
                    console.error('XHR details:', xhr);
                    $('#summaryContainer').html('<p>Erro ao calcular. Verifique sua conexão e tente novamente.</p>');
                }
            });
        },

        displaySummary: function(data, calculation) {
            console.log('Display summary - data:', data);
            console.log('Display summary - calculation:', calculation);
            
            let html = '';

            // Project Info
            html += '<div class="summary-section">';
            html += '<h4>📋 Projeto</h4>';
            html += '<ul>';
            html += `<li><strong>Tipo:</strong> ${data.project_type === 'novo' ? 'Site Novo' : 'Redesenho'}</li>`;
            html += `<li><strong>Site:</strong> ${this.getSiteTypeLabel(data.site_type)}</li>`;
            html += `<li><strong>Complexidade:</strong> ${this.getComplexityLabel(data.complexity_level)}</li>`;
            html += `<li><strong>Páginas:</strong> ${this.getPagesLabel(data.num_pages)}</li>`;
            html += '</ul>';
            html += '</div>';

            // Features
            html += '<div class="summary-section">';
            html += '<h4>🎯 Recursos</h4>';
            html += '<ul>';
            if (data.menu_pages && Array.isArray(data.menu_pages) && data.menu_pages.length > 0) {
                html += `<li><strong>Páginas do Menu:</strong> ${data.menu_pages.join(', ')}</li>`;
            }
            if (data.addons && Array.isArray(data.addons) && data.addons.length > 0) {
                html += `<li><strong>Add-ons:</strong> ${data.addons.join(', ')}</li>`;
            }
            if (data.seo_basic == '1' || data.seo_basic === true) html += '<li>SEO Básico</li>';
            if (data.seo_advanced == '1' || data.seo_advanced === true) html += '<li>SEO Avançado</li>';
            html += '</ul>';
            html += '</div>';

            // Hosting & Maintenance
            html += '<div class="summary-section">';
            html += '<h4>🖥️ Hospedagem e Manutenção</h4>';
            html += '<ul>';
            html += `<li><strong>Domínio:</strong> ${data.domain_status === 'ja_registrado' ? 'Já registrado' : 'Preciso registrar'}</li>`;
            html += `<li><strong>Hospedagem:</strong> ${this.getHostingLabel(data.hosting_current)}</li>`;
            html += `<li><strong>Manutenção:</strong> ${data.maintenance_package === 'sim_quero_proposta' ? 'Quero proposta' : 'Farei eu mesmo'}</li>`;
            html += '</ul>';
            html += '</div>';

            // Client Info
            html += '<div class="summary-section">';
            html += '<h4>👤 Cliente</h4>';
            html += '<ul>';
            html += `<li><strong>Nome:</strong> ${data.client_name || 'Não informado'}</li>`;
            html += `<li><strong>E-mail:</strong> ${data.client_email || 'Não informado'}</li>`;
            html += `<li><strong>WhatsApp:</strong> ${data.client_phone || 'Não informado'}</li>`;
            html += `<li><strong>Segmento:</strong> ${data.market_segment || 'Não informado'}</li>`;
            html += '</ul>';
            html += '</div>';

            $('#summaryContainer').html(html);

            // Update investment display with safety checks
            if (calculation && calculation.formatted) {
                $('#investmentValue').text(calculation.formatted.estimated || 'R$ 0,00');
                $('#investmentRange').text(`Range: ${calculation.formatted.min || 'R$ 0,00'} - ${calculation.formatted.max || 'R$ 0,00'}`);
            } else {
                $('#investmentValue').text('R$ 0,00');
                $('#investmentRange').text('Range: R$ 0,00 - R$ 0,00');
            }

            // Calculate delivery estimate
            const deliveryTime = data.delivery_time || '30-45';
            const deliveryLabels = {
                '30-45': '30-45 dias úteis',
                '45-60': '45-60 dias úteis',
                '60-90': '60-90 dias úteis',
                'flexivel': 'A combinar'
            };
            $('#deliveryValue').text(deliveryLabels[deliveryTime] || '30-45 dias úteis');
        },

        getFormData: function() {
            const data = {};
            const form = this.form[0];
            
            if (!form) {
                console.error('Formulário não encontrado');
                return {};
            }
            
            // Get all form elements using FormData
            const formData = new FormData(form);
            
            // Process FormData properly
            for (let [key, value] of formData.entries()) {
                // Skip empty values
                if (!value || value.trim() === '') continue;
                
                // Handle array fields (checkboxes with [] suffix)
                if (key.endsWith('[]')) {
                    const cleanKey = key.replace('[]', '');
                    if (!data[cleanKey]) {
                        data[cleanKey] = [];
                    }
                    data[cleanKey].push(value);
                } else {
                    // For radio buttons and other fields, only take the first/selected value
                    if (!data[key]) {
                        data[key] = value;
                    }
                }
            }
            
            // Also manually collect checked checkboxes to ensure we get them all
            const checkboxArrays = ['menu_pages', 'addons', 'google_marketing', 'hosting_problems', 
                                    'hosting_features', 'proposal_type', 'contact_channel'];
            
            checkboxArrays.forEach(arrayName => {
                const checkboxes = form.querySelectorAll(`input[name="${arrayName}[]"]:checked`);
                if (checkboxes.length > 0) {
                    data[arrayName] = [];
                    checkboxes.forEach(cb => {
                        if (!data[arrayName].includes(cb.value)) {
                            data[arrayName].push(cb.value);
                        }
                    });
                }
            });
            
            // Ensure boolean fields are properly set
            data['seo_basic'] = form.querySelector('input[name="seo_basic"]:checked') ? '1' : '0';
            data['seo_advanced'] = form.querySelector('input[name="seo_advanced"]:checked') ? '1' : '0';
            
            // Debug log
            console.log('getFormData result:', data);
            
            return data;
        },

        submitForm: function() {
            // Final validation
            if (!this.validateStep(8)) {
                return;
            }

            const formData = this.getFormData();

            $.ajax({
                url: futturuSimulator.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'futturu_submit',
                    nonce: futturuSimulator.nonce,
                    ...formData
                },
                beforeSend: () => {
                    $('#loadingOverlay').fadeIn();
                    $('#loadingText').text(futturuSimulator.strings.sending);
                },
                success: (response) => {
                    $('#loadingOverlay').fadeOut();
                    
                    if (response.success) {
                        this.form.hide();
                        $('.futturu-progress-bar').hide();
                        $('#successMessage').fadeIn();
                        $('html, body').animate({
                            scrollTop: $('#futturuSimulator').offset().top - 100
                        }, 300);
                    } else {
                        alert(response.data.message || futturuSimulator.strings.errorSubmit);
                    }
                },
                error: () => {
                    $('#loadingOverlay').fadeOut();
                    alert(futturuSimulator.strings.errorSubmit);
                }
            });
        },

        // Helper functions for labels
        getSiteTypeLabel: function(type) {
            const labels = {
                'blog': 'Blog',
                'news': 'Notícias',
                'portfolio': 'Portfólio',
                'hotsite': 'Hotsite',
                'institutional': 'Institucional',
                'ecommerce': 'E-commerce',
                'other': 'Outro'
            };
            return labels[type] || type;
        },

        getComplexityLabel: function(level) {
            const labels = {
                'baixa': 'Baixa',
                'media': 'Média',
                'alta': 'Alta'
            };
            return labels[level] || level;
        },

        getPagesLabel: function(pages) {
            const labels = {
                'ate_6': 'Até 6 seções',
                'ate_10': 'Até 10 seções',
                'ate_20': 'Até 20 seções',
                'ate_30': 'Até 30 seções',
                'sob_medida': 'Sob Medida'
            };
            return labels[pages] || pages;
        },

        getHostingLabel: function(hosting) {
            const labels = {
                'nao_tenho': 'Não tenho',
                'compartilhada': 'Compartilhada',
                'cloud_preciso_avaliar': 'Cloud (avaliar)',
                'quero_migrar_cloud': 'Quero migrar para Cloud'
            };
            return labels[hosting] || hosting;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(() => {
        futturuSimulator.init();
    });

})(jQuery);
