/**
 * Futturu Plans Tables - Admin JavaScript
 * Handles admin panel interactions
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize admin tabs
        initAdminTabs();
        
        // Handle form submission with AJAX feedback
        handleFormSubmit();
        
        // Add visual feedback for highlighted plans
        highlightPlanCards();
    });

    /**
     * Initialize admin tab navigation
     */
    function initAdminTabs() {
        $('.futturu-admin-tab').on('click', function(e) {
            e.preventDefault();
            
            var tabId = $(this).data('tab');
            
            // Remove active class from all tabs and panels
            $('.futturu-admin-tab').removeClass('active');
            $('.futturu-admin-panel').removeClass('active');
            
            // Add active class to clicked tab and corresponding panel
            $(this).addClass('active');
            $('#panel-' + tabId).addClass('active');
            
            // Save tab preference in sessionStorage
            sessionStorage.setItem('futturuActiveTab', tabId);
        });
        
        // Restore last active tab from sessionStorage
        var savedTab = sessionStorage.getItem('futturuActiveTab');
        if (savedTab && $('.futturu-admin-tab[data-tab="' + savedTab + '"]').length) {
            $('.futturu-admin-tab[data-tab="' + savedTab + '"]').trigger('click');
        }
    }

    /**
     * Handle form submission with AJAX feedback
     */
    function handleFormSubmit() {
        $('#futturu-settings-form').on('submit', function(e) {
            var $form = $(this);
            var $spinner = $form.find('.spinner');
            var $submitButton = $form.find('button[type="submit"]');
            
            // Show spinner
            $spinner.addClass('is-active');
            $submitButton.prop('disabled', true);
            
            // Let WordPress handle the save via options.php
            // We'll just provide visual feedback
            
            setTimeout(function() {
                $spinner.removeClass('is-active');
                $submitButton.prop('disabled', false);
                
                // Show success notification
                showNotification(futturuAdminAjax.saveSuccess, 'success');
            }, 500);
        });
    }

    /**
     * Highlight plan cards when checkbox is toggled
     */
    function highlightPlanCards() {
        $('input[name*="[highlight]"]').on('change', function() {
            var $card = $(this).closest('.futturu-plan-card');
            
            if ($(this).is(':checked')) {
                $card.addClass('futturu-highlighted-plan');
            } else {
                $card.removeClass('futturu-highlighted-plan');
            }
        });
        
        // Initialize on page load
        $('input[name*="[highlight]"]:checked').each(function() {
            $(this).closest('.futturu-plan-card').addClass('futturu-highlighted-plan');
        });
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        // Remove existing notifications
        $('.futturu-admin-notice').remove();
        
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var noticeHtml = '<div class="notice ' + noticeClass + ' futturu-admin-notice is-dismissible">' +
                         '<p>' + message + '</p>' +
                         '</div>';
        
        $('.futturu-admin-wrap h1').after(noticeHtml);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $('.futturu-admin-notice').fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Add confirmation before resetting to defaults
     */
    if ($('.futturu-reset-defaults').length) {
        $('.futturu-reset-defaults').on('click', function(e) {
            if (!confirm('Tem certeza que deseja restaurar as configurações padrão? Todas as suas personalizações serão perdidas.')) {
                e.preventDefault();
            }
        });
    }

    /**
     * Enhance feature list checkboxes with toggle all
     */
    function initFeatureToggles() {
        $('.futturu-features-list').each(function() {
            var $list = $(this);
            var $checkboxes = $list.find('input[type="checkbox"]');
            
            // Add "Toggle All" button
            var $toggleAll = $('<button type="button" class="button button-small futturu-toggle-all">Marcar/Desmarcar Todos</button>');
            $list.before($toggleAll);
            
            $toggleAll.on('click', function() {
                var allChecked = $checkboxes.length === $checkboxes.filter(':checked').length;
                $checkboxes.prop('checked', !allChecked);
            });
        });
    }

    // Initialize feature toggles
    initFeatureToggles();

    /**
     * Add character counter for text areas
     */
    $('textarea').each(function() {
        var $textarea = $(this);
        var maxLength = $textarea.attr('maxlength');
        
        if (maxLength) {
            var $counter = $('<span class="futturu-char-counter"></span>');
            $textarea.after($counter);
            
            $textarea.on('input', function() {
                var currentLength = $(this).val().length;
                $counter.text(currentLength + '/' + maxLength);
            });
            
            // Trigger initial count
            $textarea.trigger('input');
        }
    });

})(jQuery);
