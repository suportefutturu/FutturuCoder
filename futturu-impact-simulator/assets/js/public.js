/**
 * Public JavaScript for Futturu Impact Simulator
 */
(function($) {
    'use strict';

    // State
    let currentResults = null;
    let comparisonChart = null;

    // DOM Elements
    const $simulator = $('#fis-simulator');
    const $form = $('#fis-form');
    const $calculateBtn = $('#fis-calculate-btn');
    const $backBtn = $('#fis-back-btn');
    const $ctaBtn = $('#fis-cta-btn');
    const $modal = $('#fis-modal');
    const $modalClose = $('.fis-modal-close');
    const $modalOverlay = $('.fis-modal-overlay');
    const $contactForm = $('#fis-contact-form');
    const $contactBusinessType = $('#fis-contact-business-type');
    const $contactSuccess = $('#fis-contact-success');

    /**
     * Initialize the simulator
     */
    function init() {
        bindEvents();
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        $form.on('submit', handleFormSubmit);
        $backBtn.on('click', handleBackClick);
        $ctaBtn.on('click', handleCTAClick);
        $modalClose.on('click', closeModal);
        $modalOverlay.on('click', closeModal);
        $contactForm.on('submit', handleContactSubmit);

        // Close modal on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('active')) {
                closeModal();
            }
        });
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();

        // Get form values
        const formData = {
            business_type: $('#fis-business-type').val(),
            revenue_range: $('#fis-revenue-range').val(),
            target_audience: $('#fis-target-audience').val(),
            objective: $('#fis-objective').val(),
            nonce: fisData.nonce
        };

        // Validate
        if (!formData.business_type || !formData.revenue_range || 
            !formData.target_audience || !formData.objective) {
            showNotification('Por favor, preencha todos os campos.', 'error');
            return;
        }

        // Show loading state
        setLoading(true);
        switchStep(2);

        // Make AJAX request
        $.ajax({
            url: fisData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fis_calculate_impact',
                ...formData
            },
            success: function(response) {
                setLoading(false);
                
                if (response.success) {
                    currentResults = response.data;
                    displayResults(currentResults);
                    switchStep(3);
                } else {
                    showNotification(response.data.message || 'Erro ao calcular. Tente novamente.', 'error');
                    switchStep(1);
                }
            },
            error: function() {
                setLoading(false);
                showNotification('Erro de conexão. Por favor, tente novamente.', 'error');
                switchStep(1);
            }
        });
    }

    /**
     * Display results
     */
    function displayResults(results) {
        const messages = fisData.settings.messages;

        // Update current metrics
        $('#fis-current-metrics').html(`
            <div class="fis-metric">
                <span class="fis-metric-label">👁️ ${messages.visits_label}</span>
                <span class="fis-metric-value">${formatNumber(results.current.traffic)}</span>
            </div>
            <div class="fis-metric">
                <span class="fis-metric-label">👤 ${messages.leads_label}</span>
                <span class="fis-metric-value">${formatNumber(results.current.leads)}</span>
            </div>
            <div class="fis-metric">
                <span class="fis-metric-label">💼 ${messages.conversions_label}</span>
                <span class="fis-metric-value">${formatNumber(results.current.conversions)}</span>
            </div>
            <div class="fis-metric">
                <span class="fis-metric-label">💰 ${messages.revenue_label}</span>
                <span class="fis-metric-value">${formatCurrency(results.current.revenue)}</span>
            </div>
        `);

        // Update projected metrics
        $('#fis-projected-metrics').html(`
            <div class="fis-metric">
                <span class="fis-metric-label">👁️ ${messages.visits_label}</span>
                <span class="fis-metric-value highlight">${formatNumber(results.projected.traffic)}</span>
            </div>
            <div class="fis-metric">
                <span class="fis-metric-label">👤 ${messages.leads_label}</span>
                <span class="fis-metric-value highlight">${formatNumber(results.projected.leads)}</span>
            </div>
            <div class="fis-metric">
                <span class="fis-metric-label">💼 ${messages.conversions_label}</span>
                <span class="fis-metric-value highlight">${formatNumber(results.projected.conversions)}</span>
            </div>
            <div class="fis-metric">
                <span class="fis-metric-label">💰 ${messages.revenue_label}</span>
                <span class="fis-metric-value highlight">${formatCurrency(results.projected.revenue)}</span>
            </div>
        `);

        // Update highlights
        $('#fis-highlights').html(`
            <div class="fis-highlight">
                <div class="fis-highlight-icon">📈</div>
                <div class="fis-highlight-value">+${formatNumber(results.increase.traffic)}</div>
                <div class="fis-highlight-label">${messages.visits_label}</div>
            </div>
            <div class="fis-highlight">
                <div class="fis-highlight-icon">🎯</div>
                <div class="fis-highlight-value">+${formatNumber(results.increase.leads)}</div>
                <div class="fis-highlight-label">${messages.leads_label}</div>
            </div>
            <div class="fis-highlight">
                <div class="fis-highlight-icon">💰</div>
                <div class="fis-highlight-value">+${formatCurrency(results.increase.revenue)}</div>
                <div class="fis-highlight-label">${messages.revenue_label}</div>
            </div>
        `);

        // Update justifications
        let justificationsHtml = '';
        for (const [key, text] of Object.entries(results.justifications)) {
            const icons = {
                traffic: '🔍',
                leads: '📝',
                design: '🎨',
                performance: '⚡'
            };
            justificationsHtml += `
                <div class="fis-justification-item">
                    <span class="fis-justification-icon">${icons[key] || '✨'}</span>
                    <p class="fis-justification-text">${text}</p>
                </div>
            `;
        }
        $('#fis-justifications').html(justificationsHtml);

        // Create chart
        createChart(results);

        // Store business type for contact form
        const businessTypes = fisData.businessTypes;
        $contactBusinessType.val(businessTypes[results.business_info.business_type] || results.business_info.business_type);
    }

    /**
     * Create comparison chart with stacked bars for better visibility
     */
    function createChart(results) {
        const ctx = document.getElementById('fis-comparison-chart').getContext('2d');
        const messages = fisData.settings.messages;

        // Debug: Log results to console
        console.log('Chart Results:', results);
        console.log('Current data - Traffic:', parseInt(results.current.traffic) || 0, 'Leads:', parseInt(results.current.leads) || 0, 'Conversions:', parseInt(results.current.conversions) || 0);
        console.log('Projected data - Traffic:', parseInt(results.projected.traffic) || 0, 'Leads:', parseInt(results.projected.leads) || 0, 'Conversions:', parseInt(results.projected.conversions) || 0);

        // Destroy existing chart
        if (comparisonChart) {
            comparisonChart.destroy();
        }

        // Prepare data for grouped bar chart (one group per metric)
        // This shows each metric side by side for easy comparison
        comparisonChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [messages.visits_label, messages.leads_label, messages.conversions_label],
                datasets: [
                    {
                        label: messages.current_situation,
                        data: [
                            parseInt(results.current.traffic) || 0,
                            parseInt(results.current.leads) || 0,
                            parseInt(results.current.conversions) || 0
                        ],
                        backgroundColor: 'rgba(148, 163, 184, 0.9)',
                        borderColor: 'rgba(100, 116, 139, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    },
                    {
                        label: messages.with_futturu,
                        data: [
                            parseInt(results.projected.traffic) || 0,
                            parseInt(results.projected.leads) || 0,
                            parseInt(results.projected.conversions) || 0
                        ],
                        backgroundColor: 'rgba(59, 130, 246, 0.9)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.8,
                        categoryPercentage: 0.9
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                                weight: '600'
                            },
                            usePointStyle: true,
                            pointStyle: 'rect',
                            padding: 20,
                            color: '#1F2937'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.98)',
                        titleFont: {
                            size: 15,
                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                            weight: '600'
                        },
                        bodyFont: {
                            size: 14,
                            family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                        },
                        padding: 16,
                        cornerRadius: 12,
                        displayColors: true,
                        boxPadding: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.raw);
                            },
                            title: function(items) {
                                return items[0].label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(209, 213, 219, 0.3)',
                            lineWidth: 1,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                            },
                            color: '#6B7280',
                            padding: 12,
                            callback: function(value) {
                                return formatNumber(value);
                            },
                            maxTicksLimit: 6
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 13,
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                                weight: '600'
                            },
                            color: '#374151',
                            padding: 16
                        },
                        border: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 1400,
                    easing: 'easeOutQuart',
                    animateScale: true,
                    animateRotate: false
                }
            }
        });
    }

    /**
     * Handle back button click
     */
    function handleBackClick() {
        switchStep(1);
        currentResults = null;
    }

    /**
     * Handle CTA button click
     */
    function handleCTAClick() {
        openModal();
    }

    /**
     * Handle contact form submission
     */
    function handleContactSubmit(e) {
        e.preventDefault();

        const formData = {
            name: $('#fis-contact-name').val(),
            email: $('#fis-contact-email').val(),
            phone: $('#fis-contact-phone').val(),
            message: $('#fis-contact-message').val() + '\n\nInteressado na consultoria para o projeto: ' + $contactBusinessType.val(),
            business_type: $contactBusinessType.val(),
            nonce: fisData.nonce
        };

        // Validate
        if (!formData.name || !formData.email) {
            showNotification('Por favor, preencha nome e e-mail.', 'error');
            return;
        }

        // Show loading state
        const $submitBtn = $contactForm.find('button[type="submit"]');
        $submitBtn.prop('disabled', true);
        $submitBtn.find('.fis-btn-text').hide();
        $submitBtn.find('.fis-btn-loader').show();

        // Make AJAX request
        $.ajax({
            url: fisData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'fis_submit_contact',
                ...formData
            },
            success: function(response) {
                $submitBtn.prop('disabled', false);
                $submitBtn.find('.fis-btn-text').show();
                $submitBtn.find('.fis-btn-loader').hide();

                if (response.success) {
                    $contactForm.hide();
                    $contactSuccess.show();
                } else {
                    showNotification(response.data.message || 'Erro ao enviar. Tente novamente.', 'error');
                }
            },
            error: function() {
                $submitBtn.prop('disabled', false);
                $submitBtn.find('.fis-btn-text').show();
                $submitBtn.find('.fis-btn-loader').hide();
                showNotification('Erro de conexão. Por favor, tente novamente.', 'error');
            }
        });
    }

    /**
     * Switch step
     */
    function switchStep(step) {
        $simulator.find('.fis-step').removeClass('active');
        $simulator.find(`.fis-step[data-step="${step}"]`).addClass('active');
        
        // Scroll to top of simulator
        $('html, body').animate({
            scrollTop: $simulator.offset().top - 100
        }, 300);
    }

    /**
     * Set loading state
     */
    function setLoading(loading) {
        $calculateBtn.prop('disabled', loading);
        $calculateBtn.find('.fis-btn-text').toggle(!loading);
        $calculateBtn.find('.fis-btn-loader').toggle(loading);
    }

    /**
     * Open modal
     */
    function openModal() {
        $modal.addClass('active');
        $('body').css('overflow', 'hidden');
        
        // Focus first input
        setTimeout(() => {
            $('#fis-contact-name').focus();
        }, 100);
    }

    /**
     * Close modal
     */
    function closeModal() {
        $modal.removeClass('active');
        $('body').css('overflow', '');
        
        // Reset form after delay
        setTimeout(() => {
            $contactForm.show();
            $contactSuccess.hide();
            $contactForm[0].reset();
        }, 300);
    }

    /**
     * Format number
     */
    function formatNumber(num) {
        return new Intl.NumberFormat('pt-BR').format(num);
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(amount);
    }

    /**
     * Show notification
     */
    function showNotification(message, type) {
        // Simple alert for now - can be enhanced with a toast library
        alert(message);
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
