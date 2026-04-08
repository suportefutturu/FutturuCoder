/**
 * Futturu ROI Simulator - Frontend JavaScript
 */
(function($) {
    'use strict';

    // Configuration values from backend
    const annualPlanCost = parseFloat(window.futturuRoiConfig?.annualPlanCost || 2500);
    const residualPercent = parseFloat(window.futturuRoiConfig?.residualPercent || 20);

    $(document).ready(function() {
        // Update hours display when slider changes
        $('#futturu_hours_month').on('input', function() {
            $('#futturu_hours_display').text($(this).val());
        });

        // Handle calculation form submission
        $('#futturu-roi-form').on('submit', function(e) {
            e.preventDefault();
            calculateROI();
        });

        // Handle contact form submission
        $('#futturu-contact-form').on('submit', function(e) {
            e.preventDefault();
            sendContactForm();
        });
    });

    /**
     * Calculate ROI and display results
     */
    function calculateROI() {
        // Get input values
        const websitesCount = parseInt($('#futturu_websites_count').val()) || 1;
        const hoursPerMonth = parseInt($('#futturu_hours_month').val()) || 0;
        const hourlyRate = parseFloat($('#futturu_hourly_rate').val()) || 0;
        const hostingCost = parseFloat($('#futturu_hosting_cost').val()) || 0;

        // Validate inputs
        if (hoursPerMonth < 0 || hourlyRate < 0) {
            alert('Por favor, insira valores válidos.');
            return;
        }

        // Calculate current annual cost
        // Time cost per month * 12 months + hosting/third-party costs
        const monthlyTimeCost = hoursPerMonth * hourlyRate;
        const annualTimeCost = monthlyTimeCost * 12;
        const currentAnnualCost = annualTimeCost + hostingCost;

        // Calculate Futturu annual cost
        // Plan cost + residual time cost (20% of original time by default)
        const residualHoursPerMonth = hoursPerMonth * (residualPercent / 100);
        const residualMonthlyCost = residualHoursPerMonth * hourlyRate;
        const residualAnnualCost = residualMonthlyCost * 12;
        const futturuAnnualCost = annualPlanCost + residualAnnualCost;

        // Calculate savings
        const savings = currentAnnualCost - futturuAnnualCost;
        const savingsPercent = currentAnnualCost > 0 ? (savings / currentAnnualCost) * 100 : 0;
        const hoursFreed = hoursPerMonth - residualHoursPerMonth;

        // Display results
        displayResults({
            currentCost: currentAnnualCost,
            futturuCost: futturuAnnualCost,
            savings: savings,
            savingsPercent: savingsPercent,
            hoursFreed: hoursFreed,
            hoursPerMonth: hoursPerMonth,
            residualHoursPerMonth: residualHoursPerMonth
        });
    }

    /**
     * Display calculated results
     */
    function displayResults(data) {
        // Format numbers for Brazilian Portuguese locale
        const formatCurrency = (value) => {
            return value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        const formatNumber = (value) => {
            return value.toLocaleString('pt-BR', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1
            });
        };

        // Update result elements
        $('#futturu-current-cost').text(formatCurrency(data.currentCost));
        $('#futturu-futturu-cost').text(formatCurrency(data.futturuCost));
        $('#futturu-savings').text(formatCurrency(data.savings));
        $('#futturu-savings-percent').text(formatNumber(data.savingsPercent) + '%');
        $('#futturu-hours-freed').text(formatNumber(data.hoursFreed));

        // Color code the savings percentage
        const $savingsPercent = $('#futturu-savings-percent');
        if (data.savings > 0) {
            $savingsPercent.css('background', 'rgba(255, 255, 255, 0.3)');
        } else if (data.savings < 0) {
            $savingsPercent.css('background', 'rgba(255, 255, 255, 0.1)');
        }

        // Store simulation data for contact form
        $('#futturu-simulation-data').val(JSON.stringify({
            current_cost: data.currentCost,
            futturu_cost: data.futturuCost,
            savings: data.savings,
            savings_percent: data.savingsPercent,
            hours_freed: data.hoursFreed,
            hours_per_month: data.hoursPerMonth,
            residual_hours_per_month: data.residualHoursPerMonth
        }));

        // Show results section with animation
        $('#futturu-roi-results').slideDown(400);

        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#futturu-roi-results').offset().top - 100
        }, 600);
    }

    /**
     * Send contact form via AJAX
     */
    function sendContactForm() {
        const $form = $('#futturu-contact-form');
        const $button = $form.find('button[type="submit"]');
        const $successMessage = $('#futturu-contact-success');

        // Get form data
        const name = $('#futturu_name').val().trim();
        const email = $('#futturu_email').val().trim();
        const phone = $('#futturu_phone').val().trim();
        const company = $('#futturu_company').val().trim();
        const message = $('#futturu_message').val().trim();
        const simulationData = $('#futturu-simulation-data').val();

        // Validate required fields
        if (!name || !email || !phone) {
            alert('Por favor, preencha todos os campos obrigatórios.');
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Por favor, insira um e-mail válido.');
            return;
        }

        // Show loading state
        $button.prop('disabled', true).addClass('futturu-roi-loading');
        $button.html('<span class="futturu-roi-spinner"></span> Enviando...');

        // Send AJAX request
        $.ajax({
            url: futturuRoiAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'futturu_send_simulation',
                nonce: futturuRoiAjax.nonce,
                futturu_name: name,
                futturu_email: email,
                futturu_phone: phone,
                futturu_company: company,
                futturu_message: message,
                futturu_simulation_data: simulationData
            },
            success: function(response) {
                if (response.success) {
                    // Hide form and show success message
                    $form.slideUp(300);
                    $successMessage.slideDown(300);
                } else {
                    alert(response.data.message || 'Erro ao enviar. Por favor, tente novamente.');
                    resetButton();
                }
            },
            error: function() {
                alert('Erro de conexão. Por favor, tente novamente.');
                resetButton();
            }
        });

        function resetButton() {
            $button.prop('disabled', false).removeClass('futturu-roi-loading');
            $button.text('Enviar e Solicitar Contato');
        }
    }

})(jQuery);
