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
            
            // Display summary directly without AJAX calculation
            this.displaySummary(formData);
        },

        displaySummary: function(data) {
            console.log('Display summary - data:', data);
            
            let html = '';

            // Project Info
            html += '<div class="summary-section">';
            html += '<h4>📋 Projeto</h4>';
            html += '<ul>';
            html += `<li><strong>Tipo:</strong> ${data.project_type === 'novo' ? 'Site Novo' : 'Redesenho'}</li>`;
            
            // Site type label mapping
            const siteTypeLabels = {
                'blog': 'Blog',
                'news': 'Notícias',
                'portfolio': 'Portfólio',
                'hotsite': 'Hotsite',
                'institutional': 'Institucional',
                'ecommerce': 'E-commerce',
                'other': data.site_type_other || 'Outro'
            };
            html += `<li><strong>Site:</strong> ${siteTypeLabels[data.site_type] || data.site_type}</li>`;
            
            // Complexity label mapping
            const complexityLabels = {
                'baixa': 'Baixa',
                'media': 'Média',
                'alta': 'Alta'
            };
            html += `<li><strong>Complexidade:</strong> ${complexityLabels[data.complexity_level] || data.complexity_level}</li>`;
            
            // Pages label mapping
            const pagesLabels = {
                'ate_6': 'Até 6 seções',
                'ate_10': 'Até 10 seções',
                'ate_20': 'Até 20 seções',
                'ate_30': 'Até 30 seções',
                'sob_medida': data.num_pages_custom || 'Sob Medida'
            };
            html += `<li><strong>Páginas:</strong> ${pagesLabels[data.num_pages] || data.num_pages}</li>`;
            html += '</ul>';
            html += '</div>';

            // Features
            html += '<div class="summary-section">';
            html += '<h4>🎯 Recursos</h4>';
            html += '<ul>';
            if (data.menu_pages && Array.isArray(data.menu_pages) && data.menu_pages.length > 0) {
                const menuLabels = data.menu_pages.map(page => {
                    const labels = {
                        'pagina_inicial': 'Página Inicial',
                        'sobre_nos': 'Sobre Nós',
                        'produtos_servicos': 'Produtos/Serviços',
                        'portfolio': 'Portfólio',
                        'depoimentos': 'Depoimentos',
                        'blog': 'Blog',
                        'contato': 'Contato',
                        'faq': 'FAQ',
                        'politica_privacidade': 'Política de Privacidade',
                        'termos_servico': 'Termos de Serviço',
                        'equipe': 'Equipe',
                        'carreira': 'Carreira',
                        'localizacao': 'Localização',
                        'redes_sociais': 'Redes Sociais',
                        'newsletter': 'Newsletter',
                        'cta': 'Chamada para Ação',
                        'testemunhos': 'Testemunhos',
                        'videos': 'Vídeos',
                        'galeria': 'Galeria',
                        'parceiros': 'Parceiros',
                        'outra': data.menu_pages_other || 'Outra'
                    };
                    return labels[page] || page;
                });
                html += `<li><strong>Páginas do Menu:</strong> ${menuLabels.join(', ')}</li>`;
            }
            if (data.addons && Array.isArray(data.addons) && data.addons.length > 0) {
                const addonLabels = data.addons.map(addon => {
                    const labels = {
                        'faq_page': 'Página FAQ',
                        'event_calendar': 'Calendário de Eventos',
                        'registration_form': 'Formulário de Inscrição',
                        'login_area': 'Área de Login',
                        'product_search': 'Busca de Produtos/Serviços',
                        'ecommerce': 'E-commerce',
                        'sitemap': 'Mapa do Site',
                        'custom_menu': 'Menu Personalizado',
                        'newsletter': 'Newsletter',
                        'reviews': 'Avaliações',
                        'quizzes': 'Questionários',
                        'tutorial_videos': 'Vídeos Tutoriais',
                        'ads': 'Anúncios',
                        'budget_calculator': 'Calculadora de Orçamento',
                        'career_pages': 'Páginas de Carreira',
                        'corporate_videos': 'Vídeos Corporativos',
                        'phone_support': 'Atendimento Telefônico',
                        'booking_system': 'Sistema de Reservas',
                        'vfaq': 'VFAQ',
                        'translations': 'Traduções',
                        'comparison_tool': 'Ferramenta de Comparação',
                        'outro': data.addons_other || 'Outro'
                    };
                    return labels[addon] || addon;
                });
                html += `<li><strong>Add-ons:</strong> ${addonLabels.join(', ')}</li>`;
            }
            if (data.seo_basic == '1' || data.seo_basic === true) html += '<li>SEO Básico</li>';
            if (data.seo_advanced == '1' || data.seo_advanced === true) html += '<li>SEO Avançado</li>';
            html += '</ul>';
            html += '</div>';

            // Hosting & Maintenance
            html += '<div class="summary-section">';
            html += '<h4>🖥️ Hospedagem e Manutenção</h4>';
            html += '<ul>';
            const domainLabels = {
                'ja_registrado': 'Já registrado',
                'preciso_registrar': 'Preciso registrar'
            };
            html += `<li><strong>Domínio:</strong> ${domainLabels[data.domain_status] || data.domain_status}</li>`;
            
            const hostingLabels = {
                'nao_tenho': 'Não tenho',
                'compartilhada': 'Compartilhada',
                'cloud_preciso_avaliar': 'Cloud (Preciso avaliar)',
                'quero_migrar_cloud': 'Quero migrar para Cloud'
            };
            html += `<li><strong>Hospedagem:</strong> ${hostingLabels[data.hosting_current] || data.hosting_current}</li>`;
            
            const maintenanceLabels = {
                'sim_quero_proposta': 'Quero proposta',
                'nao_farei_mesmo': 'Farei eu mesmo'
            };
            html += `<li><strong>Manutenção:</strong> ${maintenanceLabels[data.maintenance_package] || data.maintenance_package}</li>`;
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
        },

        getFormData: function() {
            const data = {};
            const form = this.form[0];
            
            if (!form) {
                console.error('Formulário não encontrado');
                return {};
            }
            
            // Get all form elements using FormData - from ENTIRE form
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
            
            // Handle "other" text fields for checkboxes
            const otherFields = {
                'menu_pages_other': 'menu_outra_check',
                'addons_other': 'addon_outro_check',
                'google_marketing_other': 'google_outro_check'
            };
            
            for (const [otherField, otherCheckId] of Object.entries(otherFields)) {
                const otherInput = form.querySelector(`input[name="${otherField}"]`);
                const otherCheck = form.querySelector(`#${otherCheckId}`);
                
                if (otherCheck && otherCheck.checked && otherInput && otherInput.value.trim() !== '') {
                    const arrayName = otherField.replace('_other', '');
                    if (!data[arrayName]) data[arrayName] = [];
                    data[arrayName].push(otherInput.value.trim());
                }
            }
            
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
