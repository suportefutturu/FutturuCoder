/**
 * Futturu Quick Audit - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Admin page audit button
        $('#futturu-run-audit').on('click', function() {
            runAudit($(this), $('#futturu-progress'), $('#futturu-results'));
        });

        // History detail function (global)
        window.futturuShowHistory = function(index) {
            var detailDiv = $('#futturu-history-detail');
            
            if (detailDiv.is(':visible')) {
                detailDiv.hide();
                return;
            }
            
            // Get the report HTML from the stored data
            // This would require passing the data to the frontend
            // For now, we'll show a message
            detailDiv.html('<div class="notice notice-info"><p>' + futturuAudit.completeText + '</p></div>').show();
        };
    });

    /**
     * Run audit via AJAX
     */
    function runAudit(button, progress, results) {
        button.prop('disabled', true);
        progress.show();
        results.html('');

        $.post(futturuAudit.ajaxUrl, {
            action: 'futturu_run_audit',
            nonce: futturuAudit.nonce
        }, function(response) {
            progress.hide();
            button.prop('disabled', false);

            if (response.success) {
                results.html(response.data.html);
                
                // Scroll to results
                $('html, body').animate({
                    scrollTop: results.offset().top - 100
                }, 500);
            } else {
                results.html('<div class="futturu-error"><strong>Erro:</strong> ' + response.data.message + '</div>');
            }
        }).fail(function() {
            progress.hide();
            button.prop('disabled', false);
            results.html('<div class="futturu-error"><strong>Erro:</strong> Falha na comunicação com o servidor. Tente novamente.</div>');
        });
    }

})(jQuery);
