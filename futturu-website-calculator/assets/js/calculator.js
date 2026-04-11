/**
 * Futturu Website Calculator - Frontend JavaScript
 * Version: 2.0.0
 */

(function($) {
    'use strict';
    
    // Configuration
    const CONFIG = {
        pagePrice: 150,
        selectors: {
            form: '#futturu-calculator-form',
            websiteType: '#website_type',
            complexityLevel: '#complexity_level',
            numPages: '#num_pages',
            extras: '.futturu-checkbox',
            hostingPlan: '#hosting_plan',
            totalEstimate: '#total_estimate',
            developmentTotal: '#development_total',
            hostingTotal: '#hosting_total',
            hostingRow: '#hosting_row',
            submitBtn: '#futturu-submit-btn',
            messageContainer: '#futturu-message-container',
            btnText: '.btn-text',
            btnLoading: '.btn-loading'
        }
    };
    
    // State
    let state = {
        basePrice: 0,
        multiplier: 0,
        pagesTotal: 0,
        extrasTotal: 0,
        hostingMonthly: 0,
        developmentTotal: 0
    };
    
    /**
     * Initialize calculator
     */
    function init() {
        bindEvents();
        calculateTotal();
    }
    
    /**
     * Bind event listeners
     */
    function bindEvents() {
        $(CONFIG.selectors.websiteType).on('change', calculateTotal);
        $(CONFIG.selectors.complexityLevel).on('change', calculateTotal);
        $(CONFIG.selectors.numPages).on('input change', calculateTotal);
        $(CONFIG.selectors.extras).on('change', calculateTotal);
        $(CONFIG.selectors.hostingPlan).on('change', calculateTotal);
        $(CONFIG.selectors.form).on('submit', handleFormSubmit);
    }
    
    /**
     * Calculate total estimate
     */
    function calculateTotal() {
        // Get values from form
        const $selectedType = $(CONFIG.selectors.websiteType + ' option:selected');
        const $selectedComplexity = $(CONFIG.selectors.complexityLevel + ' option:selected');
        const numPages = parseInt($(CONFIG.selectors.numPages).val()) || 0;
        
        // Base price from website type
        state.basePrice = parseFloat($selectedType.data('price')) || 0;
        
        // Complexity multiplier
        state.multiplier = parseFloat($selectedComplexity.data('multiplier')) || 0;
        
        // Pages total
        state.pagesTotal = numPages * CONFIG.pagePrice;
        
        // Extras total
        state.extrasTotal = 0;
        $(CONFIG.selectors.extras + ':checked').each(function() {
            state.extrasTotal += parseFloat($(this).data('price')) || 0;
        });
        
        // Calculate subtotal and apply complexity multiplier
        const subtotal = state.basePrice + state.pagesTotal + state.extrasTotal;
        const complexityAdditional = subtotal * state.multiplier;
        state.developmentTotal = subtotal + complexityAdditional;
        
        // Hosting monthly
        const $selectedHosting = $(CONFIG.selectors.hostingPlan + ' option:selected');
        state.hostingMonthly = parseFloat($selectedHosting.data('price')) || 0;
        
        // Update UI
        updateDisplay();
    }
    
    /**
     * Update display with calculated values
     */
    function updateDisplay() {
        // Format numbers to Brazilian Real format
        const formatCurrency = (value) => {
            return value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };
        
        // Update total estimate (development only)
        $(CONFIG.selectors.totalEstimate).text(formatCurrency(state.developmentTotal));
        
        // Update development total in breakdown
        $(CONFIG.selectors.developmentTotal).text('R$ ' + formatCurrency(state.developmentTotal));
        
        // Update hosting info
        if (state.hostingMonthly > 0) {
            $(CONFIG.selectors.hostingTotal).text('R$ ' + formatCurrency(state.hostingMonthly));
            $(CONFIG.selectors.hostingRow).show();
        } else {
            $(CONFIG.selectors.hostingRow).hide();
        }
        
        // Add animation effect
        $(CONFIG.selectors.totalEstimate).addClass('animate-pulse');
        setTimeout(() => {
            $(CONFIG.selectors.totalEstimate).removeClass('animate-pulse');
        }, 300);
    }
    
    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Validate required fields
        if (!validateForm()) {
            showMessage('Por favor, preencha todos os campos obrigatórios.', 'error');
            return;
        }
        
        // Check if calculation was made
        if (state.developmentTotal === 0) {
            showMessage('Por favor, selecione pelo menos o tipo de website e complexidade.', 'error');
            return;
        }
        
        // Prepare form data
        const formData = new FormData($(CONFIG.selectors.form)[0]);
        formData.append('action', 'futturu_send_quote');
        formData.append('nonce', futturuCalcAjax.nonce);
        
        // Disable button and show loading state
        setLoadingState(true);
        
        // Send AJAX request
        $.ajax({
            url: futturuCalcAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    // Optional: Reset form after successful submission
                    // $(CONFIG.selectors.form)[0].reset();
                    // calculateTotal();
                } else {
                    showMessage(response.data.message || 'Ocorreu um erro ao enviar. Tente novamente.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showMessage('Erro de conexão. Por favor, verifique sua internet e tente novamente.', 'error');
            },
            complete: function() {
                setLoadingState(false);
            }
        });
    }
    
    /**
     * Validate form fields
     */
    function validateForm() {
        const requiredFields = [
            CONFIG.selectors.websiteType,
            CONFIG.selectors.complexityLevel,
            CONFIG.selectors.numPages,
            '#contact_name',
            '#contact_email',
            '#contact_phone'
        ];
        
        let isValid = true;
        
        requiredFields.forEach(selector => {
            const $field = $(selector);
            const value = $field.val();
            
            if (!value || value.trim() === '') {
                $field.addClass('error');
                isValid = false;
            } else {
                $field.removeClass('error');
            }
            
            // Additional email validation
            if (selector === '#contact_email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    $field.addClass('error');
                    isValid = false;
                }
            }
        });
        
        return isValid;
    }
    
    /**
     * Show message to user
     */
    function showMessage(message, type) {
        const messageHtml = `
            <div class="futturu-message ${type}">
                ${message}
            </div>
        `;
        
        $(CONFIG.selectors.messageContainer).html(messageHtml);
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: $(CONFIG.selectors.messageContainer).offset().top - 100
        }, 500);
        
        // Auto-hide success messages after 10 seconds
        if (type === 'success') {
            setTimeout(() => {
                $(CONFIG.selectors.messageContainer).fadeOut();
            }, 10000);
        }
    }
    
    /**
     * Set loading state for submit button
     */
    function setLoadingState(isLoading) {
        const $btn = $(CONFIG.selectors.submitBtn);
        
        if (isLoading) {
            $btn.prop('disabled', true);
            $(CONFIG.selectors.btnText).hide();
            $(CONFIG.selectors.btnLoading).show();
        } else {
            $btn.prop('disabled', false);
            $(CONFIG.selectors.btnText).show();
            $(CONFIG.selectors.btnLoading).hide();
        }
    }
    
    /**
     * Phone mask for Brazilian phone numbers
     */
    function applyPhoneMask(value) {
        // Remove non-numeric characters
        value = value.replace(/\D/g, '');
        
        // Apply mask: (XX) XXXXX-XXXX or (XX) XXXX-XXXX
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
        } else if (value.length > 5) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
        } else {
            value = value.replace(/^(\d*)/, '($1');
        }
        
        return value;
    }
    
    // Apply phone mask on input
    $(document).on('input', '#contact_phone', function() {
        $(this).val(applyPhoneMask($(this).val()));
    });
    
    // Initialize on document ready
    $(document).ready(init);
    
})(jQuery);
