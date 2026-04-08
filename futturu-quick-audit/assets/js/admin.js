jQuery(document).ready(function($) {
    
    $('#futturu-audit-form').on('submit', function(e) {
        e.preventDefault();
        
        var siteUrl = $('#futturu_site_url').val();
        
        if (!siteUrl) {
            showError('Por favor, digite a URL do seu site.');
            return;
        }
        
        // Mostrar loading, esconder formulário
        $('#futturu-input-section').hide();
        $('#futturu-loading').show();
        $('#futturu-error-msg').hide();
        
        $.ajax({
            url: futturuAuditAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'futturu_run_external_audit',
                nonce: futturuAuditAjax.nonce,
                site_url: siteUrl
            },
            success: function(response) {
                $('#futturu-loading').hide();
                
                if (response.success) {
                    displayResults(response.data);
                } else {
                    showError(response.data.message || 'Erro ao analisar o site.');
                    $('#futturu-input-section').show();
                }
            },
            error: function() {
                $('#futturu-loading').hide();
                showError('Erro de comunicação com o servidor. Tente novamente.');
                $('#futturu-input-section').show();
            }
        });
    });
    
    function displayResults(data) {
        // Atualizar scores
        animateScore('score-speed', data.scores.speed);
        animateScore('score-seo', data.scores.seo);
        animateScore('score-security', data.scores.security);
        
        // Gerar relatório detalhado
        var reportHtml = '';
        
        // Velocidade
        reportHtml += generateCategoryReport('Velocidade', data.details.speed);
        
        // SEO
        reportHtml += generateCategoryReport('SEO', data.details.seo);
        
        // Segurança
        reportHtml += generateCategoryReport('Segurança', data.details.security);
        
        $('#futturu-report-content').html(reportHtml);
        $('#futturu-results').fadeIn();
    }
    
    function generateCategoryReport(category, checks) {
        var html = '<div class="report-category"><h4>' + category + '</h4><ul class="checks-list">';
        
        checks.forEach(function(check) {
            var statusClass = check.pass ? 'check-pass' : 'check-fail';
            var icon = check.pass ? '✓' : '✗';
            
            html += '<li class="' + statusClass + '">';
            html += '<strong>' + icon + ' ' + check.name + '</strong><br>';
            html += '<span class="check-msg">' + check.msg + '</span>';
            
            if (!check.pass && check.fix) {
                html += '<div class="check-fix">💡 ' + check.fix + '</div>';
            }
            html += '</li>';
        });
        
        html += '</ul></div>';
        return html;
    }
    
    function animateScore(elementId, targetScore) {
        var $el = $('#' + elementId);
        var current = 0;
        var interval = setInterval(function() {
            if (current >= targetScore) {
                clearInterval(interval);
            } else {
                current++;
                $el.text(current);
            }
        }, 15);
        
        // Adicionar classe de cor baseada no score
        $el.removeClass('score-low score-medium score-high');
        if (targetScore >= 80) {
            $el.addClass('score-high');
        } else if (targetScore >= 50) {
            $el.addClass('score-medium');
        } else {
            $el.addClass('score-low');
        }
    }
    
    function showError(message) {
        $('#futturu-error-msg').text(message).show();
    }
});
