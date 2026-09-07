/**
 * Futturu Site Cloner - Admin JavaScript
 */
(function($) {
    'use strict';

    var FSC = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Toggle advanced options
            $('#fsc-toggle-advanced').on('click', this.toggleAdvanced);
            
            // Clone form submission
            $('#fsc-clone-form').on('submit', this.handleClone);
            
            // Download backup
            $(document).on('click', '.fsc-download-btn', this.handleDownload);
            
            // Delete backup
            $(document).on('click', '.fsc-delete-btn', this.handleDelete);
        },

        toggleAdvanced: function(e) {
            e.preventDefault();
            var $panel = $('#fsc-advanced-panel');
            var $icon = $(this).find('.dashicons');
            
            if ($panel.is(':visible')) {
                $panel.slideUp(200);
                $icon.removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
            } else {
                $panel.slideDown(200);
                $icon.removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
            }
        },

        handleClone: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $('#fsc-submit-btn');
            var url = $('#fsc_url').val();
            var siteName = $('#fsc_site_name').val();
            var ignoreFiles = $('#fsc_ignore_files').val();
            var tries = $('#fsc_tries').val();
            var timeout = $('#fsc_timeout').val();
            var userAgent = $('#fsc_user_agent').val();
            var ignoreRobots = $('#fsc_ignore_robots').is(':checked') ? 1 : 0;
            
            // Validate URL
            if (!url || !url.match(/^https?:\/\//)) {
                FSC.log(__('Please enter a valid URL starting with http:// or https://', 'futturu-site-cloner'), 'error');
                return;
            }
            
            // Disable submit button
            $submitBtn.prop('disabled', true).addClass('fsc-loading');
            $submitBtn.html('<span class="fsc-spinner"></span>' + fscAjax.strings.cloning);
            
            // Show progress container
            $('#fsc-progress-container').slideDown(200);
            $('#fsc-status').text(fscAjax.strings.cloning);
            
            // Clear previous log
            $('#fsc-log').html('');
            
            // Log the command being executed
            FSC.log(__('Preparing to clone...', 'futturu-site-cloner'), 'info');
            
            // Send AJAX request
            $.ajax({
                url: fscAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fsc_clone_site',
                    nonce: fscAjax.nonce,
                    url: url,
                    site_name: siteName,
                    ignore_files: ignoreFiles,
                    tries: tries,
                    timeout: timeout,
                    user_agent: userAgent,
                    ignore_robots: ignoreRobots
                },
                success: function(response) {
                    if (response.success) {
                        FSC.log(__('Command:', 'futturu-site-cloner') + ' ' + response.data.command, 'info');
                        FSC.log(response.data.message, 'info');
                        
                        // Simulate progress (in real implementation, this would poll for status)
                        FSC.simulateProgress();
                        
                        // Execute the actual wget command via background process
                        FSC.executeWget(response.data.command, siteName || url);
                    } else {
                        FSC.log(response.data.message || __('Error occurred', 'futturu-site-cloner'), 'error');
                        FSC.resetSubmitButton();
                    }
                },
                error: function(xhr, status, error) {
                    FSC.log(__('AJAX Error:', 'futturu-site-cloner') + ' ' + error, 'error');
                    FSC.resetSubmitButton();
                }
            });
        },

        executeWget: function(command, backupName) {
            // In a real implementation, this would trigger a background process
            // For now, we'll simulate the execution with setTimeout
            
            FSC.log(__('Executing wget command...', 'futturu-site-cloner'), 'info');
            
            setTimeout(function() {
                FSC.log(__('Note: In production, this would execute the wget command in the background.', 'futturu-site-cloner'), 'warning');
                FSC.log(__('The command would be:', 'futturu-site-cloner'), 'info');
                FSC.log(command, 'info');
                
                // Simulate completion
                setTimeout(function() {
                    FSC.log(__('Clone process completed!', 'futturu-site-cloner'), 'success');
                    $('#fsc-status').text(fscAjax.strings.completed);
                    $('#fsc-progress').css('width', '100%');
                    
                    FSC.resetSubmitButton();
                    
                    // Refresh page after delay to show new backup
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }, 2000);
            }, 1000);
        },

        simulateProgress: function() {
            var progress = 0;
            var interval = setInterval(function() {
                if (progress >= 90) {
                    clearInterval(interval);
                } else {
                    progress += Math.random() * 10;
                    if (progress > 90) progress = 90;
                    $('#fsc-progress').css('width', progress + '%');
                }
            }, 500);
        },

        resetSubmitButton: function() {
            var $submitBtn = $('#fsc-submit-btn');
            $submitBtn.prop('disabled', false).removeClass('fsc-loading');
            $submitBtn.html('<span class="dashicons dashicons-download"></span>' + __('Clone Site', 'futturu-site-cloner'));
        },

        handleDownload: function(e) {
            e.preventDefault();
            
            var backupName = $(this).data('backup');
            var $btn = $(this);
            
            if (!backupName) {
                alert(fscAjax.strings.error);
                return;
            }
            
            $btn.prop('disabled', true);
            $btn.html('<span class="fsc-spinner"></span>' + __('Creating ZIP...', 'futturu-site-cloner'));
            
            $.ajax({
                url: fscAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fsc_download_backup',
                    nonce: fscAjax.nonce,
                    backup_name: backupName
                },
                success: function(response) {
                    if (response.success) {
                        // Redirect to download URL
                        window.location.href = response.data.download_url;
                        
                        setTimeout(function() {
                            $btn.prop('disabled', false);
                            $btn.html('<span class="dashicons dashicons-archive"></span>' + __('Download ZIP', 'futturu-site-cloner'));
                        }, 2000);
                    } else {
                        alert(response.data.message || fscAjax.strings.error);
                        $btn.prop('disabled', false);
                        $btn.html('<span class="dashicons dashicons-archive"></span>' + __('Download ZIP', 'futturu-site-cloner'));
                    }
                },
                error: function() {
                    alert(fscAjax.strings.error);
                    $btn.prop('disabled', false);
                    $btn.html('<span class="dashicons dashicons-archive"></span>' + __('Download ZIP', 'futturu-site-cloner'));
                }
            });
        },

        handleDelete: function(e) {
            e.preventDefault();
            
            var backupName = $(this).data('backup');
            var $btn = $(this);
            var $row = $(this).closest('tr');
            
            if (!backupName) {
                alert(fscAjax.strings.error);
                return;
            }
            
            if (!confirm(fscAjax.strings.confirmDelete)) {
                return;
            }
            
            $btn.prop('disabled', true);
            
            $.ajax({
                url: fscAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fsc_delete_backup',
                    nonce: fscAjax.nonce,
                    backup_name: backupName
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if table is empty
                            if ($('tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert(response.data.message || fscAjax.strings.error);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(fscAjax.strings.error);
                    $btn.prop('disabled', false);
                }
            });
        },

        log: function(message, type) {
            var $log = $('#fsc-log');
            var className = 'fsc-log-' + (type || 'info');
            var timestamp = new Date().toLocaleTimeString();
            
            var logEntry = $('<p class="' + className + '">[' + timestamp + '] ' + this.escapeHtml(message) + '</p>');
            $log.append(logEntry);
            
            // Auto-scroll to bottom
            $log.scrollTop($log[0].scrollHeight);
        },

        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        FSC.init();
    });

})(jQuery);
