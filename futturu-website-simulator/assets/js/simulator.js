/**
 * Futturu Website Simulator JavaScript
 */

(function($) {
    'use strict';

    // Simulator state
    const simulator = {
        currentStep: 1,
        totalSteps: 3,
        data: {
            siteType: '',
            category: '',
            businessName: '',
            locality: '',
            services: '',
            audience: '',
            differential: '',
            generatedDescription: '',
            fullName: '',
            phone: '',
            email: ''
        }
    };

    // DOM Elements
    const $form = $('#futturu-simulator-form');
    const $steps = $('.futturu-step-content');
    const $progressSteps = $('.futturu-step');
    const $descriptionText = $('#generated-description-text');
    const $previewBusinessName = $('#preview-business-name');
    const $previewDescription = $('#preview-description');
    const $previewServices = $('#preview-services');
    const $previewAbout = $('#preview-about');
    const $messageBox = $('#futturu-message');

    // Initialize
    function init() {
        bindEvents();
        updatePreview();
    }

    // Bind Events
    function bindEvents() {
        // Navigation buttons
        $('.futturu-btn-next').on('click', handleNextStep);
        $('.futturu-btn-back').on('click', handlePrevStep);

        // Form input changes for live preview
        $('#business_name, #business_category, #business_locality, #business_services, #business_audience, #business_differential')
            .on('input change', debounce(updatePreview, 500));

        // Site type selection
        $('input[name="site_type"]').on('change', function() {
            simulator.data.siteType = $(this).val();
        });

        // Form submission
        $form.on('submit', handleFormSubmit);

        // Phone mask
        $('#contact_phone').on('input', formatPhone);
    }

    // Handle Next Step
    function handleNextStep(e) {
        e.preventDefault();
        const nextStep = parseInt($(this).data('next'));
        
        if (validateCurrentStep()) {
            goToStep(nextStep);
        }
    }

    // Handle Previous Step
    function handlePrevStep(e) {
        e.preventDefault();
        const prevStep = parseInt($(this).data('prev'));
        goToStep(prevStep);
    }

    // Go to Specific Step
    function goToStep(step) {
        if (step < 1 || step > simulator.totalSteps) return;

        // Update steps visibility
        $steps.removeClass('active');
        $(`.futturu-step-content[data-step="${step}"]`).addClass('active');

        // Update progress indicators
        $progressSteps.removeClass('active completed');
        $progressSteps.each(function() {
            const stepNum = parseInt($(this).data('step'));
            if (stepNum < step) {
                $(this).addClass('completed');
            } else if (stepNum === step) {
                $(this).addClass('active');
            }
        });

        simulator.currentStep = step;

        // Update summary on step 3
        if (step === 3) {
            updateSummary();
        }

        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $form.offset().top - 100
        }, 300);
    }

    // Validate Current Step
    function validateCurrentStep() {
        let isValid = true;
        const $currentStepContent = $(`.futturu-step-content[data-step="${simulator.currentStep}"]`);

        // Clear previous errors
        $currentStepContent.find('.error').removeClass('error');
        $currentStepContent.find('.error-message').removeClass('show');

        if (simulator.currentStep === 1) {
            const selectedType = $('input[name="site_type"]:checked').val();
            if (!selectedType) {
                showError(futturuSimulator.strings.required);
                isValid = false;
            } else {
                simulator.data.siteType = selectedType;
            }
        }

        if (simulator.currentStep === 2) {
            const fields = [
                { id: '#business_category', name: 'category', required: true },
                { id: '#business_name', name: 'businessName', required: true },
                { id: '#business_locality', name: 'locality', required: true },
                { id: '#business_services', name: 'services', required: true },
                { id: '#business_audience', name: 'audience', required: true },
                { id: '#business_differential', name: 'differential', required: true }
            ];

            fields.forEach(field => {
                const $field = $(field.id);
                const value = $field.val().trim();

                if (field.required && !value) {
                    $field.addClass('error');
                    showError(futturuSimulator.strings.required, $field);
                    isValid = false;
                } else {
                    simulator.data[field.name] = value;
                }
            });

            // Generate description if not already done
            if (isValid && !simulator.data.generatedDescription) {
                generateDescription();
            }
        }

        if (simulator.currentStep === 3) {
            const fields = [
                { id: '#contact_name', name: 'fullName', required: true },
                { id: '#contact_email', name: 'email', required: true, validate: isValidEmail }
            ];

            fields.forEach(field => {
                const $field = $(field.id);
                const value = $field.val().trim();

                if (field.required && !value) {
                    $field.addClass('error');
                    showError(futturuSimulator.strings.required, $field);
                    isValid = false;
                } else if (field.validate && !field.validate(value)) {
                    $field.addClass('error');
                    showError(futturuSimulator.strings.invalidEmail, $field);
                    isValid = false;
                } else {
                    simulator.data[field.name] = value;
                }
            });

            // Optional phone validation
            const phoneValue = $('#contact_phone').val().trim();
            if (phoneValue && !isValidPhone(phoneValue)) {
                $('#contact_phone').addClass('error');
                showError(futturuSimulator.strings.invalidPhone, $('#contact_phone'));
                isValid = false;
            } else {
                simulator.data.phone = phoneValue;
            }
        }

        return isValid;
    }

    // Show Error Message
    function showError(message, $field) {
        if ($field && $field.length) {
            let $errorMsg = $field.next('.error-message');
            if (!$errorMsg.length) {
                $errorMsg = $('<span class="error-message"></span>');
                $field.after($errorMsg);
            }
            $errorMsg.text(message).addClass('show');
        }
    }

    // Generate Description from Template
    function generateDescription() {
        if (!simulator.data.businessName || !simulator.data.category) return;

        // Get templates from backend (would be passed via wp_localize_script in real implementation)
        // For now, we'll use a simple template
        const templates = [
            'Transforme seu {categoria} em referência em {localidade}! O {nome} oferece {servicos} com o diferencial de {diferencial}. Perfeito para {publico} que busca qualidade e excelência.',
            'Descubra o {nome}, o {categoria} ideal em {localidade}. Especializado em {servicos}, nosso maior diferencial é {diferencial}. Atendemos {publico} com dedicação total.',
            '{nome}: Seu {categoria} de confiança em {localidade}. Oferecemos {servicos} com {diferencial}. A escolha certa para {publico} que valoriza qualidade.',
            'Em {localidade}, o {nome} se destaca como {categoria}. Com {servicos} e {diferencial}, somos a solução perfeita para {publico}.',
            'Conheça o {nome}, referência em {categoria} em {localidade}. Nossos serviços incluem {servicos}, com o diferencial de {diferencial}. Ideal para {publico}.',
            'O {nome} é o {categoria} que {publico} de {localidade} estava esperando. Oferecemos {servicos} com {diferencial} para você.',
            'Procurando um {categoria} em {localidade}? O {nome} tem {servicos} e o diferencial de {diferencial}. Feito para {publico} exigentes.',
            '{nome}: Excelência em {categoria} em {localidade}. Contamos com {servicos} e {diferencial}. A melhor opção para {publico}.',
            'Seu {categoria} em {localidade} com a qualidade do {nome}. Oferecemos {servicos}, tendo {diferencial} como nosso grande diferencial. Pensado para {publico}.',
            'O {nome} chega em {localidade} como referência em {categoria}. Com {servicos} e {diferencial}, atendemos {publico} com maestria.'
        ];

        // Select random template
        const templateIndex = Math.floor(Math.random() * templates.length);
        let template = templates[templateIndex];

        // Replace placeholders
        template = template.replace(/{nome}/g, simulator.data.businessName);
        template = template.replace(/{categoria}/g, simulator.data.category);
        template = template.replace(/{localidade}/g, simulator.data.locality || '[sua localidade]');
        template = template.replace(/{servicos}/g, simulator.data.services || '[seus serviços]');
        template = template.replace(/{publico}/g, simulator.data.audience || '[seu público]');
        template = template.replace(/{diferencial}/g, simulator.data.differential || '[seu diferencial]');

        simulator.data.generatedDescription = template;

        // Update UI
        $descriptionText.html(`<p>${template}</p>`);
        updatePreview();
    }

    // Update Preview
    function updatePreview() {
        // Update business name
        const businessName = $('#business_name').val().trim() || 'Seu Negócio';
        $previewBusinessName.text(businessName);

        // Update description
        if (simulator.data.generatedDescription) {
            $previewDescription.text(simulator.data.generatedDescription);
        } else {
            const category = $('#business_category').val() || 'negócio';
            const locality = $('#business_locality').val().trim() || 'sua cidade';
            $previewDescription.text(`Descrição gerada automaticamente para seu ${category} em ${locality}`);
        }

        // Update services
        const services = $('#business_services').val().trim() || 'Seus serviços serão exibidos aqui';
        $previewServices.text(services);

        // Update about section
        const audience = $('#business_audience').val().trim() || 'seu público-alvo';
        const differential = $('#business_differential').val().trim() || 'seu diferencial';
        $previewAbout.text(`Atendemos ${audience} com ${differential}.`);
    }

    // Update Summary
    function updateSummary() {
        // Get latest values from form
        const siteTypeName = $('input[name="site_type"]:checked').closest('.futturu-site-type-card').find('.card-title').text() || simulator.data.siteType;
        
        $('#summary-site-type').text(siteTypeName);
        $('#summary-business').text($('#business_name').val().trim() || '-');
        $('#summary-category').text($('#business_category').val() || '-');
        $('#summary-locality').text($('#business_locality').val().trim() || '-');
    }

    // Handle Form Submit
    function handleFormSubmit(e) {
        e.preventDefault();

        if (!validateCurrentStep()) {
            return;
        }

        // Get latest values
        simulator.data.fullName = $('#contact_name').val().trim();
        simulator.data.phone = $('#contact_phone').val().trim();
        simulator.data.email = $('#contact_email').val().trim();

        // Show loading state
        const $submitBtn = $('.futturu-btn-submit');
        const originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text(futturuSimulator.strings.sending);

        // Send AJAX request
        $.ajax({
            url: futturuSimulator.ajaxUrl,
            type: 'POST',
            data: {
                action: 'futturu_send_lead',
                nonce: futturuSimulator.nonce,
                site_type: simulator.data.siteType,
                business_name: simulator.data.businessName,
                category: simulator.data.category,
                locality: simulator.data.locality,
                services: simulator.data.services,
                audience: simulator.data.audience,
                differential: simulator.data.differential,
                generated_description: simulator.data.generatedDescription,
                full_name: simulator.data.fullName,
                phone: simulator.data.phone,
                email: simulator.data.email
            },
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    $form.trigger('reset');
                    resetSimulator();
                } else {
                    showMessage(response.data.message || futturuSimulator.strings.error, 'error');
                }
            },
            error: function() {
                showMessage(futturuSimulator.strings.error, 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    }

    // Show Message
    function showMessage(message, type) {
        $messageBox
            .removeClass('success error')
            .addClass(type)
            .text(message)
            .fadeIn(300);

        setTimeout(function() {
            $messageBox.fadeOut(300);
        }, 8000);
    }

    // Reset Simulator
    function resetSimulator() {
        simulator.currentStep = 1;
        simulator.data = {
            siteType: '',
            category: '',
            businessName: '',
            locality: '',
            services: '',
            audience: '',
            differential: '',
            generatedDescription: '',
            fullName: '',
            phone: '',
            email: ''
        };

        goToStep(1);
        $descriptionText.html('<em>Preencha os campos acima para gerar sua descrição...</em>');
        resetPreview();
    }

    // Reset Preview
    function resetPreview() {
        $previewBusinessName.text('Seu Negócio');
        $previewDescription.text('Sua descrição aparecerá aqui');
        $previewServices.text('Seus serviços serão exibidos aqui');
        $previewAbout.text('Informações sobre seu negócio');
    }

    // Format Phone Number
    function formatPhone(e) {
        let value = $(this).val().replace(/\D/g, '');
        
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '+$1 ($2) $3-$4');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '+$1 ($2) $3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5}).*/, '+$1 ($2');
        } else if (value.length > 0) {
            value = value.replace(/^(\d*)/, '+$1');
        }
        
        $(this).val(value);
    }

    // Validation Helpers
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function isValidPhone(phone) {
        const re = /^\+?\d{10,15}$/;
        const cleanPhone = phone.replace(/\D/g, '');
        return re.test(cleanPhone);
    }

    // Debounce Function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
