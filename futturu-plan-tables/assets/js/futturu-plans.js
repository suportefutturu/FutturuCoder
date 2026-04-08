/**
 * Futturu Plans Tables - Frontend JavaScript
 * Handles tabs navigation and interactions
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize tabs for all tables view
        initTabs();
        
        // Smooth scroll to CTA
        initCTAScroll();
        
        // Add hover effects
        initHoverEffects();
    });

    /**
     * Initialize tab navigation
     */
    function initTabs() {
        $('.futturu-tab-btn').on('click', function(e) {
            e.preventDefault();
            
            var tabId = $(this).data('tab');
            
            // Remove active class from all buttons and panels
            $('.futturu-tab-btn').removeClass('active');
            $('.futturu-tab-panel').removeClass('active');
            
            // Add active class to clicked button and corresponding panel
            $(this).addClass('active');
            $('#tab-' + tabId).addClass('active');
            
            // Smooth scroll to table
            $('html, body').animate({
                scrollTop: $('#tab-' + tabId).offset().top - 100
            }, 300);
        });
    }

    /**
     * Initialize admin tabs
     */
    if ($('.futturu-admin-tab').length) {
        $('.futturu-admin-tab').on('click', function(e) {
            e.preventDefault();
            
            var tabId = $(this).data('tab');
            
            // Remove active class from all tabs and panels
            $('.futturu-admin-tab').removeClass('active');
            $('.futturu-admin-panel').removeClass('active');
            
            // Add active class to clicked tab and corresponding panel
            $(this).addClass('active');
            $('#panel-' + tabId).addClass('active');
        });
    }

    /**
     * Initialize CTA smooth scroll
     */
    function initCTAScroll() {
        $('.futturu-cta-button').on('click', function(e) {
            var href = $(this).attr('href');
            
            // If it's an anchor link on the same page
            if (href && href.charAt(0) === '#') {
                e.preventDefault();
                var target = $(href);
                
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 500);
                }
            }
        });
    }

    /**
     * Add hover effects for plan cards
     */
    function initHoverEffects() {
        $('.futturu-plans-plan-header').hover(
            function() {
                $(this).closest('.futturu-plans-cell').css({
                    'transform': 'translateY(-2px)',
                    'transition': 'all 0.3s ease'
                });
            },
            function() {
                $(this).closest('.futturu-plans-cell').css({
                    'transform': 'translateY(0)',
                    'transition': 'all 0.3s ease'
                });
            }
        );
    }

    /**
     * Handle form submission (if using modal form)
     */
    if ($('#futturu-cta-form').length) {
        $('#futturu-cta-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var $spinner = $form.find('.spinner');
            
            // Disable button and show spinner
            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            
            // Collect form data
            var formData = {
                action: 'futturu_cta_submit',
                nonce: futturuPlansAjax.nonce,
                category: $form.find('[name="category"]').val(),
                plan_name: $form.find('[name="plan_name"]').val(),
                customer_name: $form.find('[name="customer_name"]').val(),
                customer_email: $form.find('[name="customer_email"]').val(),
                customer_phone: $form.find('[name="customer_phone"]').val(),
                message: $form.find('[name="message"]').val()
            };
            
            // Send AJAX request
            $.ajax({
                url: futturuPlansAjax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $form.html('<div class="futturu-success-message">' + response.data.message + '</div>');
                    } else {
                        alert(response.data.message);
                        $button.prop('disabled', false);
                        $spinner.removeClass('is-active');
                    }
                },
                error: function() {
                    alert('Erro ao enviar mensagem. Tente novamente.');
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });
    }

    /**
     * Animate elements on scroll
     */
    function animateOnScroll() {
        var elements = $('.futturu-plans-row');
        
        elements.each(function() {
            var elementTop = $(this).offset().top;
            var windowBottom = $(window).scrollTop() + $(window).height();
            
            if (elementTop < windowBottom - 50) {
                $(this).addClass('animated');
            }
        });
    }

    // Trigger animation on scroll
    $(window).on('scroll', function() {
        animateOnScroll();
    });
    
    // Initial animation check
    animateOnScroll();

})(jQuery);
