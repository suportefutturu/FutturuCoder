/**
 * Simple Centered Popup - Admin JavaScript
 * Version 2.0
 * 
 * Handles admin UI interactions including tabs, color pickers,
 * media uploader, and range slider value display.
 *
 * @package Simple_Centered_Popup
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // ==========================================
        // TABS FUNCTIONALITY
        // ==========================================
        initTabs();
        
        // ==========================================
        // COLOR PICKER INITIALIZATION
        // ==========================================
        initColorPickers();
        
        // ==========================================
        // MEDIA UPLOADER
        // ==========================================
        initMediaUploader();
        
        // ==========================================
        // RANGE SLIDER VALUE DISPLAY
        // ==========================================
        initRangeSliders();
        
        // ==========================================
        // FORM VALIDATION & UX IMPROVEMENTS
        // ==========================================
        initFormImprovements();
    });

    /**
     * Initialize tab navigation
     */
    function initTabs() {
        const $tabs = $('.scp-tab');
        const $sections = $('.form-table').closest('div[id^="setting-"]').parent();
        
        if ($tabs.length === 0) return;
        
        // Show first tab by default
        $tabs.first().addClass('nav-tab-active');
        $sections.first().addClass('active').css('display', 'block');
        
        $tabs.on('click', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const tabId = $this.data('tab');
            
            // Update tab active state
            $tabs.removeClass('nav-tab-active');
            $this.addClass('nav-tab-active');
            
            // Show corresponding section
            $sections.hide().removeClass('active');
            
            // Find and show the correct section based on tab
            const sectionMap = {
                'general': 'scp_general_section',
                'content': 'scp_content_section',
                'behavior': 'scp_behavior_section',
                'design': 'scp_design_section',
                'typography': 'scp_typography_section',
                'animation': 'scp_animation_section',
                'visibility': 'scp_visibility_section'
            };
            
            const targetSection = sectionMap[tabId];
            if (targetSection) {
                $(`#setting-${targetSection}`).closest('.form-table').parent().show().addClass('active');
            }
            
            // Scroll to top of settings area
            $('html, body').animate({
                scrollTop: $('.scp-tabs').offset().top - 100
            }, 300);
        });
    }

    /**
     * Initialize WordPress color pickers
     */
    function initColorPickers() {
        $('.scp-color-picker').wpColorPicker({
            change: function(event, ui) {
                // Optional: Add live preview here
                console.log('Color changed:', ui.color.toString());
            },
            clear: function() {
                // Handle color clear
            },
            palettes: true,
            width: 250,
            mode: 'hex'
        });
    }

    /**
     * Initialize media uploader for image fields
     */
    function initMediaUploader() {
        let mediaUploader;
        
        $(document).on('click', '.scp-upload-button', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $container = $button.closest('.scp-image-upload-container');
            const $urlInput = $container.find('.scp-image-url');
            const $altInput = $container.find('.scp-image-alt');
            const $preview = $container.find('.scp-image-preview');
            const $removeBtn = $container.find('.scp-remove-button');
            
            // Create media uploader if it doesn't exist
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: scpAdminConfig.uploadTitle || 'Select Image',
                button: {
                    text: scpAdminConfig.uploadButton || 'Use this image'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Update URL input
                $urlInput.val(attachment.url).trigger('change');
                
                // Update alt text input
                $altInput.val(attachment.alt || attachment.title || '').trigger('change');
                
                // Update preview
                $preview.html(`<img src="${attachment.url}" alt="${attachment.alt || attachment.title || ''}" style="max-width:300px;height:auto;border-radius:4px;" />`).show();
                
                // Show remove button
                $removeBtn.show();
            });
            
            mediaUploader.open();
        });
        
        // Remove image
        $(document).on('click', '.scp-remove-button', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $container = $button.closest('.scp-image-upload-container');
            const $urlInput = $container.find('.scp-image-url');
            const $altInput = $container.find('.scp-image-alt');
            const $preview = $container.find('.scp-image-preview');
            
            // Clear inputs
            $urlInput.val('').trigger('change');
            $altInput.val('').trigger('change');
            
            // Hide preview
            $preview.hide().html('');
            
            // Hide remove button
            $button.hide();
        });
    }

    /**
     * Initialize range sliders with value display
     */
    function initRangeSliders() {
        $('.scp-range-slider').each(function() {
            const $slider = $(this);
            const $valueDisplay = $slider.next('.scp-range-value');
            
            if ($valueDisplay.length === 0) {
                $slider.after(`<span class="scp-range-value">${$slider.val()}</span>`);
            }
            
            $slider.on('input change', function() {
                $(this).next('.scp-range-value').text($(this).val());
            });
        });
    }

    /**
     * Initialize form UX improvements
     */
    function initFormImprovements() {
        // Auto-select text in input fields on focus
        $('input[type="text"]:not(.wp-color-picker)').on('focus', function() {
            $(this).select();
        });
        
        // Confirm before leaving with unsaved changes
        let formChanged = false;
        $('.scp-settings-page input, .scp-settings-page textarea, .scp-settings-page select').on('change input', function() {
            formChanged = true;
        });
        
        $(window).on('beforeunload', function() {
            if (formChanged) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
        // Reset form changed flag on submit
        $('.scp-settings-page form').on('submit', function() {
            formChanged = false;
        });
        
        // Add visual feedback on save
        $('.scp-settings-page .button-primary').on('click', function() {
            const $button = $(this);
            const originalText = $button.val();
            
            $button.val('Saving...').prop('disabled', true);
            
            setTimeout(function() {
                $button.val(originalText).prop('disabled', false);
            }, 2000);
        });
        
        // Tooltip for complex fields (optional enhancement)
        $('[data-tooltip]').each(function() {
            const $field = $(this);
            const tooltip = $field.data('tooltip');
            
            $field.attr('title', tooltip);
        });
    }

    /**
     * Live preview functionality (can be extended)
     */
    function updateLivePreview() {
        // This could be extended to show a live preview of the popup
        // in the admin panel as settings are changed
        console.log('Live preview update triggered');
    }

})(jQuery);
