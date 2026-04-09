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
            $('.progress-step').each(function(index) {
                const $this = $(this);
                const stepNum = parseInt($this.data('step')) + 1;

                $this.removeClass('active completed');

                if (stepNum === this.currentStep) {
                    $this.addClass('active');
                } else if (stepNum < this.currentStep) {
                    $this.addClass('completed');
                }
            }.bind(this));
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
            
            $.ajax({
                url: futturuSimulator.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'futturu_calculate',
                    nonce: futturuSimulator.nonce,
                    ...formData
                },
                beforeSend: () => {
                    $('#summaryContainer').html('<p>' + futturuSimulator.strings.calculating + '</p>');
                },
                success: (response) => {
                    if (response.success) {
                        this.displaySummary(formData, response.data.calculation);
                    } else {
                        $('#summaryContainer').html('<p>Erro ao calcular.</p>');
                    }
                },
                error: () => {
                    $('#summaryContainer').html('<p>Erro ao calcular.</p>');
                }
            });
        },

        displaySummary: function(data, calculation) {
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
            if (data.menu_pages && data.menu_pages.length > 0) {
                html += `<li><strong>Páginas:</strong> ${data.menu_pages.join(', ')}</li>`;
            }
            if (data.addons && data.addons.length > 0) {
                html += `<li><strong>Add-ons:</strong> ${data.addons.join(', ')}</li>`;
            }
            if (data.seo_basic) html += '<li>SEO Básico</li>';
            if (data.seo_advanced) html += '<li>SEO Avançado</li>';
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
            html += `<li><strong>Nome:</strong> ${data.client_name}</li>`;
            html += `<li><strong>E-mail:</strong> ${data.client_email}</li>`;
            html += `<li><strong>WhatsApp:</strong> ${data.client_phone}</li>`;
            html += `<li><strong>Segmento:</strong> ${data.market_segment}</li>`;
            html += '</ul>';
            html += '</div>';

            $('#summaryContainer').html(html);

            // Update investment display
            $('#investmentValue').text(calculation.formatted.estimated);
            $('#investmentRange').text(`Range: ${calculation.formatted.min} - ${calculation.formatted.max}`);

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
            const formData = new FormData(this.form[0]);
            const data = {};

            formData.forEach((value, key) => {
                if (data[key]) {
                    if (!Array.isArray(data[key])) {
                        data[key] = [data[key]];
                    }
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            });

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
