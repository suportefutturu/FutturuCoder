/**
 * Futturu Website Calculator - Frontend JavaScript
 * Handles calculation logic and form submission
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize calculator
        initCalculator();
    });

    function initCalculator() {
        var $form = $('#futturu-calculator-form');
        if (!$form.length) return;

        // Calculate button click
        $('#futturu-calc-calculate').on('click', function() {
            calculateTotal();
        });

        // Real-time calculation on input change
        $form.on('change', 'select, input[type="checkbox"], input[type="number"]', function() {
            // Optional: enable real-time calculation
            // calculateTotal();
        });

        // Form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });
    }

    function calculateTotal() {
        var $form = $('#futturu-calculator-form');
        
        // Validate required fields
        var websiteType = $('#website_type').val();
        var complexity = $('#complexity').val();
        var numPages = parseInt($('#num_pages').val()) || 0;

        if (!websiteType) {
            showMessage(__('Por favor, selecione o tipo de website.', 'futturu-calculator'), 'error');
            scrollToSection('website_type');
            return false;
        }

        if (!complexity) {
            showMessage(__('Por favor, selecione a complexidade do projeto.', 'futturu-calculator'), 'error');
            scrollToSection('complexity');
            return false;
        }

        if (numPages < 1) {
            showMessage(__('O número mínimo de páginas é 1.', 'futturu-calculator'), 'error');
            scrollToSection('num_pages');
            return false;
        }

        // Get values
        var basePrice = parseFloat($('#website_type option:selected').data('price')) || 0;
        var multiplier = parseFloat($('#complexity option:selected').data('multiplier')) || 1;
        var pagePrice = parseFloat($form.find('input[name="num_pages"]').attr('data-page-price')) || 150;
        
        // We need to get page_price from backend settings
        // For now, we'll use a default that should be updated via localization
        var pagePriceDefault = futturuCalcAjax.page_price || 150;

        // Calculate pages cost (pages beyond the first one which is included)
        var pagesCost = (numPages > 1) ? (numPages - 1) * pagePriceDefault : 0;

        // Calculate extras
        var extrasTotal = 0;
        var selectedExtras = [];
        $form.find('input[name="extras[]"]:checked').each(function() {
            extrasTotal += parseFloat($(this).data('price')) || 0;
            selectedExtras.push($(this).parent().find('.futturu-calc-checkbox-label').contents().first().text().trim());
        });

        // Calculate development total
        var subtotal = basePrice + pagesCost + extrasTotal;
        var developmentTotal = subtotal * multiplier;

        // Get hosting
        var hostingPrice = 0;
        var hostingLabel = '';
        var $hostingSelect = $('#hosting');
        if ($hostingSelect.val()) {
            hostingPrice = parseFloat($hostingSelect.find('option:selected').data('price')) || 0;
            hostingLabel = $hostingSelect.find('option:selected').text();
        }

        // Update display
        $('#development_total').text(formatCurrency(developmentTotal));
        
        if (hostingPrice > 0) {
            $('#hosting_total').text(formatCurrency(hostingPrice) + '/mês');
            $('#hosting_display').slideDown(300);
        } else {
            $('#hosting_display').slideUp(300);
        }

        // Build summary for hidden field
        var summary = buildSummary(websiteType, complexity, numPages, selectedExtras, hostingLabel, developmentTotal, hostingPrice);
        $('#calc_summary').val(summary);
        $('#calc_development_total').val(formatCurrency(developmentTotal));
        $('#calc_hosting_total').val(hostingPrice > 0 ? formatCurrency(hostingPrice) + '/mês' : 'Não selecionado');

        // Show contact form
        $('#futturu-contact-form').slideDown(400);
        scrollToSection('futturu-contact-form');

        // Hide any previous messages
        $('#futturu-calc-message').hide();

        return true;
    }

    function buildSummary(websiteType, complexity, numPages, extras, hostingLabel, devTotal, hostingPrice) {
        var websiteLabel = $('#website_type option:selected').text();
        var complexityLabel = $('#complexity option:selected').text();
        
        var summary = '';
        summary += '• Tipo de Website: ' + websiteLabel + '\n';
        summary += '• Complexidade: ' + complexityLabel + '\n';
        summary += '• Número de Páginas: ' + numPages + '\n';
        
        if (extras.length > 0) {
            summary += '• Extras Selecionados:\n';
            extras.forEach(function(extra) {
                summary += '  - ' + extra + '\n';
            });
        } else {
            summary += '• Extras: Nenhum\n';
        }
        
        if (hostingLabel) {
            summary += '• Hospedagem: ' + hostingLabel + '\n';
        }
        
        return summary;
    }

    function submitForm() {
        // First, ensure calculation is done
        if ($('#futturu-contact-form').is(':hidden')) {
            calculateTotal();
            return;
        }

        var $form = $('#futturu-calculator-form');
        var $submitBtn = $('#futturu-calc-submit');
        
        // Validate contact fields
        var name = $('#contact_name').val().trim();
        var email = $('#contact_email').val().trim();
        var phone = $('#contact_phone').val().trim();
        
        if (!name) {
            showMessage(__('Por favor, informe seu nome completo.', 'futturu-calculator'), 'error');
            scrollToSection('contact_name');
            return;
        }
        
        if (!email || !isValidEmail(email)) {
            showMessage(__('Por favor, informe um e-mail válido.', 'futturu-calculator'), 'error');
            scrollToSection('contact_email');
            return;
        }
        
        if (!phone) {
            showMessage(__('Por favor, informe seu telefone/WhatsApp.', 'futturu-calculator'), 'error');
            scrollToSection('contact_phone');
            return;
        }

        // Disable button and show loading
        $submitBtn.prop('disabled', true).html('<span class="futturu-calc-loading"></span> Enviando...');

        // Prepare data
        var formData = {
            action: 'futturu_send_quote',
            nonce: futturuCalcAjax.nonce,
            contact_name: name,
            contact_email: email,
            contact_phone: phone,
            contact_company: $('#contact_company').val().trim(),
            contact_message: $('#contact_message').val().trim(),
            calc_summary: $('#calc_summary').val(),
            calc_development_total: $('#calc_development_total').val(),
            calc_hosting_total: $('#calc_hosting_total').val()
        };

        // Send AJAX request
        $.ajax({
            url: futturuCalcAjax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    $form[0].reset();
                    $('#futturu-contact-form').slideUp(300);
                    $('#development_total').text('R$ 0,00');
                    $('#hosting_display').hide();
                } else {
                    showMessage(response.data.message || futturuCalcAjax.error_message, 'error');
                }
            },
            error: function() {
                showMessage(futturuCalcAjax.error_message, 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(__('Enviar Solicitação', 'futturu-calculator'));
            }
        });
    }

    function showMessage(message, type) {
        var $messageDiv = $('#futturu-calc-message');
        $messageDiv.text(message)
            .removeClass('success error')
            .addClass(type)
            .fadeIn(300);
        
        scrollToSection('futturu-calc-message');
    }

    function scrollToSection(elementId) {
        var $element = $('#' + elementId);
        if ($element.length) {
            $('html, body').animate({
                scrollTop: $element.offset().top - 100
            }, 500);
        }
    }

    function formatCurrency(value) {
        return value.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function isValidEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Translation helper
    function __(text, domain) {
        // In a real implementation, this would use WordPress i18n
        return text;
    }

})(jQuery);
