/**
 * Premium Simulator JavaScript
 * Handles multi-step form navigation, validation, and submission
 * 
 * @package Futturu_Premium_Simulator
 */

(function($) {
    'use strict';

    // Main simulator class
    const FutturuPremiumSimulator = {
        currentStep: 1,
        totalSteps: 9,
        formData: {},

        init() {
            this.cacheElements();
            this.bindEvents();
            this.updateProgress();
        },

        cacheElements() {
            this.$form = $('#futturu-simulation-form');
            this.$steps = $('.futturu-form-step');
            this.$progressSteps = $('.futturu-step');
            this.$progressFill = $('.futturu-progress-fill');
            this.$btnPrev = $('.futturu-btn-prev');
            this.$btnNext = $('.futturu-btn-next');
            this.$btnSubmit = $('.futturu-btn-submit');
            this.$summaryContent = $('#futturu-summary-content');
            this.$loading = $('#futturu-loading');
            this.$successMessage = $('#futturu-success-message');
        },

        bindEvents() {
            // Navigation buttons
            this.$btnNext.on('click', (e) => this.nextStep());
            this.$btnPrev.on('click', (e) => this.prevStep());

            // Form submission
            this.$form.on('submit', (e) => this.handleSubmit(e));

            // Real-time validation on input change
            this.$form.on('change input', 'input, select, textarea', (e) => {
                this.validateCurrentStep();
            });

            // Admin modal close
            $('.futturu-modal-close').on('click', function() {
                $('#futturu-detail-modal').hide();
            });

            // Admin view detail button
            $(document).on('click', '.futturu-view-detail', (e) => {
                const id = $(e.target).data('id');
                this.viewSimulationDetail(id);
            });

            // Admin status update
            $(document).on('change', '.futturu-status-select', (e) => {
                const $select = $(e.target);
                const id = $select.data('id');
                const status = $select.val();
                this.updateSimulationStatus(id, status);
            });
        },

        nextStep() {
            if (!this.validateCurrentStep()) {
                return;
            }

            if (this.currentStep < this.totalSteps) {
                // Save current step data
                this.saveCurrentStepData();

                // Generate summary if moving to step 9
                if (this.currentStep === 8) {
                    this.generateSummary();
                }

                // Update UI
                this.$steps.eq(this.currentStep - 1).removeClass('active').addClass('completed');
                this.$progressSteps.eq(this.currentStep - 1).removeClass('active').addClass('completed');
                
                this.currentStep++;
                
                this.$steps.eq(this.currentStep - 1).addClass('active');
                this.$progressSteps.eq(this.currentStep - 1).addClass('active');

                this.updateProgress();
                this.updateButtons();
                this.scrollToTop();
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.$steps.eq(this.currentStep - 1).removeClass('active');
                this.$progressSteps.eq(this.currentStep - 1).removeClass('active');
                
                this.currentStep--;
                
                this.$steps.eq(this.currentStep - 1).removeClass('completed').addClass('active');
                this.$progressSteps.eq(this.currentStep - 1).removeClass('completed').addClass('active');

                this.updateProgress();
                this.updateButtons();
                this.scrollToTop();
            }
        },

        updateProgress() {
            const progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            this.$progressFill.css('width', progress + '%');
        },

        updateButtons() {
            // Previous button
            this.$btnPrev.prop('disabled', this.currentStep === 1);

            // Next/Submit buttons
            if (this.currentStep === this.totalSteps) {
                this.$btnNext.hide();
                this.$btnSubmit.show();
            } else {
                this.$btnNext.show();
                this.$btnSubmit.hide();
            }
        },

        validateCurrentStep() {
            const $currentStepEl = this.$steps.eq(this.currentStep - 1);
            let isValid = true;

            // Remove previous error states
            $currentStepEl.find('.futturu-form-group').removeClass('error');
            $currentStepEl.find('.futturu-error-message').remove();

            // Get required fields in current step
            const $requiredFields = $currentStepEl.find('[required]');

            $requiredFields.each((index, field) => {
                const $field = $(field);
                let fieldValue = $field.val();

                // Handle checkbox groups
                if ($field.attr('type') === 'checkbox' && $field.attr('name')?.includes('[]')) {
                    const checkedBoxes = $currentStepEl.find(`input[name="${$field.attr('name')}"]:checked`);
                    fieldValue = checkedBoxes.length > 0 ? 'valid' : '';
                }

                if (!fieldValue || fieldValue.trim() === '') {
                    isValid = false;
                    this.showError($field, futturuPremium.messages.required);
                }

                // Email validation
                if ($field.attr('type') === 'email' && fieldValue) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(fieldValue)) {
                        isValid = false;
                        this.showError($field, futturuPremium.messages.emailInvalid);
                    }
                }

                // Phone validation (Brazilian format)
                if ($field.attr('type') === 'tel' && fieldValue) {
                    const phoneRegex = /^[\d\s\-\(\)\+]+$/;
                    if (!phoneRegex.test(fieldValue) || fieldValue.replace(/\D/g, '').length < 10) {
                        isValid = false;
                        this.showError($field, futturuPremium.messages.phoneInvalid);
                    }
                }
            });

            return isValid;
        },

        showError($field, message) {
            const $formGroup = $field.closest('.futturu-form-group');
            $formGroup.addClass('error');
            
            if ($formGroup.find('.futturu-error-message').length === 0) {
                $formGroup.append(`<div class="futturu-error-message">${message}</div>`);
            }
        },

        saveCurrentStepData() {
            const $currentStepEl = this.$steps.eq(this.currentStep - 1);
            const inputs = $currentStepEl.find('input, select, textarea');

            inputs.each((index, input) => {
                const $input = $(input);
                const name = $input.attr('name');
                
                if (!name) return;

                // Handle checkboxes
                if ($input.attr('type') === 'checkbox') {
                    if ($input.is(':checked')) {
                        if (!this.formData[name]) {
                            this.formData[name] = [];
                        }
                        if (name.includes('[]')) {
                            this.formData[name].push($input.val());
                        } else {
                            this.formData[name] = $input.val();
                        }
                    }
                } else {
                    this.formData[name] = $input.val();
                }
            });
        },

        generateSummary() {
            // Collect all form data first
            this.collectAllFormData();

            const labels = {
                project_type: 'Tipo de Projeto',
                site_category: 'Categoria do Site',
                complexity: 'Complexidade',
                pages_count: 'Número de Páginas',
                languages: 'Idiomas',
                text_origin: 'Origem dos Textos',
                image_origin: 'Origem das Imagens',
                seo_level: 'Nível de SEO',
                domain_status: 'Status do Domínio',
                cloud_interest: 'Interesse Cloud Premium',
                maintenance_frequency: 'Frequência de Manutenção',
                maintenance_plan: 'Plano de Manutenção',
                company_category: 'Categoria da Empresa',
                budget_range: 'Faixa de Budget',
                desired_deadline: 'Prazo Desejado',
                meeting_type: 'Tipo de Reunião',
                client_name: 'Nome',
                client_email: 'E-mail',
                client_phone: 'Telefone/WhatsApp',
                client_cnpj: 'CNPJ',
                client_segment: 'Segmento',
                how_found_us: 'Como nos Conheceu'
            };

            const valueLabels = {
                project_type: { novo: 'Site Novo', redesenho: 'Redesenho' },
                complexity: { baixa: 'Baixa', media: 'Média', alta: 'Alta' },
                seo_level: { basico: 'Básico', avancado: 'Avançado' },
                cloud_interest: { sim: 'Sim', nao: 'Não', quero: 'Quero Migrar' },
                maintenance_plan: { nenhum: 'Nenhum', basico: 'Básico', padrao: 'Padrão', premium: 'Premium', empresarial: 'Empresarial' }
            };

            let html = '';

            // Project Details
            html += '<div class="summary-section">';
            html += '<h4>🌐 Detalhes do Projeto</h4>';
            
            ['project_type', 'site_category', 'complexity', 'pages_count'].forEach(key => {
                if (this.formData[key]) {
                    let value = this.formData[key];
                    if (valueLabels[key] && valueLabels[key][value]) {
                        value = valueLabels[key][value];
                    } else if (key === 'site_category') {
                        value = value.charAt(0).toUpperCase() + value.slice(1);
                    }
                    html += `<div class="summary-item"><span class="summary-label">${labels[key]}:</span><span class="summary-value">${value}</span></div>`;
                }
            });

            // Pages checklist
            if (this.formData['pages_checklist[]'] && this.formData['pages_checklist[]'].length > 0) {
                html += '<div class="summary-item"><span class="summary-label">Páginas Incluídas:</span>';
                html += '<div class="summary-tags">';
                this.formData['pages_checklist[]'].forEach(page => {
                    html += `<span class="summary-tag">${page.charAt(0).toUpperCase() + page.slice(1)}</span>`;
                });
                html += '</div></div>';
            }

            html += '</div>';

            // Add-ons
            if (this.formData['addons_selected[]'] && this.formData['addons_selected[]'].length > 0) {
                html += '<div class="summary-section">';
                html += '<h4>🔧 Recursos Adicionais</h4>';
                html += '<div class="summary-tags">';
                this.formData['addons_selected[]'].forEach(addon => {
                    html += `<span class="summary-tag">${addon.replace(/_/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')}</span>`;
                });
                html += '</div></div>';
            }

            // Marketing & SEO
            html += '<div class="summary-section">';
            html += '<h4>📈 Marketing e SEO</h4>';
            
            if (this.formData['google_integrations[]'] && this.formData['google_integrations[]'].length > 0) {
                html += '<div class="summary-item"><span class="summary-label">Integrações Google:</span>';
                html += '<div class="summary-tags">';
                this.formData['google_integrations[]'].forEach(integration => {
                    html += `<span class="summary-tag">${integration.replace(/_/g, ' ').toUpperCase()}</span>`;
                });
                html += '</div></div>';
            }

            ['seo_level'].forEach(key => {
                if (this.formData[key]) {
                    let value = valueLabels[key] && valueLabels[key][this.formData[key]] ? valueLabels[key][this.formData[key]] : this.formData[key];
                    html += `<div class="summary-item"><span class="summary-label">${labels[key]}:</span><span class="summary-value">${value}</span></div>`;
                }
            });

            html += '</div>';

            // Hosting & Infrastructure
            html += '<div class="summary-section">';
            html += '<h4>☁️ Hospedagem e Infraestrutura</h4>';
            
            ['domain_status', 'cloud_interest', 'maintenance_plan'].forEach(key => {
                if (this.formData[key]) {
                    let value = this.formData[key];
                    if (valueLabels[key] && valueLabels[key][value]) {
                        value = valueLabels[key][value];
                    }
                    html += `<div class="summary-item"><span class="summary-label">${labels[key]}:</span><span class="summary-value">${value}</span></div>`;
                }
            });

            html += '</div>';

            // Investment Expectations
            html += '<div class="summary-section">';
            html += '<h4>💰 Expectativas de Investimento</h4>';
            
            ['company_category', 'budget_range', 'desired_deadline', 'meeting_type'].forEach(key => {
                if (this.formData[key]) {
                    let value = this.formData[key].charAt(0).toUpperCase() + this.formData[key].slice(1);
                    html += `<div class="summary-item"><span class="summary-label">${labels[key]}:</span><span class="summary-value">${value}</span></div>`;
                }
            });

            html += '</div>';

            // Client Data
            html += '<div class="summary-section">';
            html += '<h4>👤 Dados do Cliente</h4>';
            
            ['client_name', 'client_email', 'client_phone', 'client_cnpj', 'client_segment'].forEach(key => {
                if (this.formData[key] && this.formData[key].trim() !== '') {
                    html += `<div class="summary-item"><span class="summary-label">${labels[key]}:</span><span class="summary-value">${this.formData[key]}</span></div>`;
                }
            });

            if (this.formData['how_found_us']) {
                html += `<div class="summary-item"><span class="summary-label">${labels.how_found_us}:</span><span class="summary-value">${this.formData['how_found_us'].charAt(0).toUpperCase() + this.formData['how_found_us'].slice(1)}</span></div>`;
            }

            if (this.formData['observations'] && this.formData['observations'].trim() !== '') {
                html += `<div class="summary-item" style="display:block; margin-top:10px;"><span class="summary-label">Observações:</span><p style="margin:5px 0 0; color:#666;">${this.formData['observations']}</p></div>`;
            }

            html += '</div>';

            this.$summaryContent.html(html);
        },

        collectAllFormData() {
            this.formData = {};
            const inputs = this.$form.find('input, select, textarea');

            inputs.each((index, input) => {
                const $input = $(input);
                const name = $input.attr('name');
                
                if (!name) return;

                if ($input.attr('type') === 'checkbox') {
                    if ($input.is(':checked')) {
                        if (!this.formData[name]) {
                            this.formData[name] = [];
                        }
                        if (name.includes('[]')) {
                            if (!this.formData[name].includes($input.val())) {
                                this.formData[name].push($input.val());
                            }
                        } else {
                            this.formData[name] = $input.val();
                        }
                    }
                } else {
                    this.formData[name] = $input.val();
                }
            });
        },

        handleSubmit(e) {
            e.preventDefault();

            // Validate terms acceptance
            if (!$('#terms_acceptance').is(':checked')) {
                alert('É necessário aceitar os termos para continuar.');
                return;
            }

            // Final validation
            if (!this.validateCurrentStep()) {
                return;
            }

            // Collect all data
            this.collectAllFormData();

            // Show loading
            this.$loading.show();

            // Prepare AJAX data
            const ajaxData = {
                action: 'futturu_premium_submit',
                security: futturuPremium.nonce
            };

            // Add all form data
            $.extend(ajaxData, this.formData);

            // Send AJAX request
            $.ajax({
                url: futturuPremium.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    this.$loading.hide();
                    
                    if (response.success) {
                        this.$form.hide();
                        $('.futturu-progress-bar').hide();
                        this.$successMessage.show();
                    } else {
                        alert(response.data.message || futturuPremium.messages.error);
                    }
                },
                error: () => {
                    this.$loading.hide();
                    alert(futturuPremium.messages.error);
                }
            });
        },

        scrollToTop() {
            $('html, body').animate({
                scrollTop: $('.futturu-premium-simulator').offset().top - 20
            }, 300);
        },

        // Admin functions
        viewSimulationDetail(id) {
            $.ajax({
                url: futturuPremiumAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'futturu_premium_get_simulation',
                    security: futturuPremiumAdmin.nonce,
                    id: id
                },
                success: (response) => {
                    if (response.success) {
                        this.showDetailModal(response.data);
                    }
                }
            });
        },

        showDetailModal(data) {
            let html = '<h2 style="margin-bottom:20px; color:#1a73e8;">Detalhes da Simulação #' + data.id + '</h2>';
            
            html += '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">';
            html += '<div><strong>Cliente:</strong> ' + data.client_name + '</div>';
            html += '<div><strong>E-mail:</strong> ' + data.client_email + '</div>';
            html += '<div><strong>Telefone:</strong> ' + data.client_phone + '</div>';
            html += '<div><strong>Data:</strong> ' + data.created_at + '</div>';
            html += '</div>';

            html += '<h3>Projeto</h3>';
            html += '<p>Tipo: ' + data.project_type + ' | Categoria: ' + data.site_category + ' | Complexidade: ' + data.complexity + '</p>';
            html += '<p>Páginas: ' + data.pages_count + '</p>';

            if (data.addons_selected) {
                const addons = JSON.parse(data.addons_selected);
                html += '<p><strong>Add-ons:</strong> ' + addons.join(', ') + '</p>';
            }

            html += '<h3 style="margin-top:20px;">Valores Internos</h3>';
            html += '<div style="background:#fff3cd; padding:15px; border-radius:8px;">';
            html += '<p>Desenvolvimento: R$ ' + parseFloat(data.estimated_value_internal).toFixed(2).replace('.', ',') + '</p>';
            html += '<p>Hospedagem Anual: R$ ' + parseFloat(data.hosting_cost_annual).toFixed(2).replace('.', ',') + '</p>';
            html += '<p>Manutenção Anual: R$ ' + parseFloat(data.maintenance_cost_annual).toFixed(2).replace('.', ',') + '</p>';
            html += '<p style="font-size:18px; font-weight:bold; margin-top:10px;">Total: R$ ' + parseFloat(data.total_estimated).toFixed(2).replace('.', ',') + '</p>';
            html += '</div>';

            if (data.observations) {
                html += '<h3 style="margin-top:20px;">Observações</h3>';
                html += '<p>' + data.observations + '</p>';
            }

            html += '<div style="margin-top:20px; padding-top:20px; border-top:1px solid #ddd;">';
            html += '<label><strong>Atualizar Status:</strong> ';
            html += '<select class="futturu-status-select" data-id="' + data.id + '" style="margin-left:10px; padding:8px;">';
            html += '<option value="new"' + (data.status === 'new' ? ' selected' : '') + '>Novo</option>';
            html += '<option value="contacted"' + (data.status === 'contacted' ? ' selected' : '') + '>Contatado</option>';
            html += '<option value="qualified"' + (data.status === 'qualified' ? ' selected' : '') + '>Qualificado</option>';
            html += '<option value="closed"' + (data.status === 'closed' ? ' selected' : '') + '>Fechado</option>';
            html += '<option value="lost"' + (data.status === 'lost' ? ' selected' : '') + '>Perdido</option>';
            html += '</select></label>';
            html += '</div>';

            $('#futturu-modal-body').html(html);
            $('#futturu-detail-modal').show();
        },

        updateSimulationStatus(id, status) {
            $.ajax({
                url: futturuPremiumAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'futturu_premium_update_status',
                    security: futturuPremiumAdmin.nonce,
                    id: id,
                    status: status
                },
                success: (response) => {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Erro ao atualizar status');
                    }
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        if ($('#futturuPremiumSimulator').length) {
            FutturuPremiumSimulator.init();
        }
    });

})(jQuery);
