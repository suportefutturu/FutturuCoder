jQuery(function($) {
    const $form = $('#wsmvp-form');
    const $steps = $('.wsmvp-step');
    const $progress = $('.wsmvp-progress');
    const $prev = $('.wsmvp-prev');
    const $next = $('.wsmvp-next');
    const $submit = $('.wsmvp-submit');
    const $preview = $('#wsmvp-preview');
    const $result = $('#wsmvp-result');
    
    let currentStep = 0;
    const totalSteps = $steps.length;
    
    function updateProgress() {
        const percent = ((currentStep) / (totalSteps - 1)) * 100;
        $progress.css('width', percent + '%');
        $steps.hide().eq(currentStep).show();
        $prev.toggle(currentStep > 0);
        $next.toggle(currentStep < totalSteps - 1);
        $submit.toggle(currentStep === totalSteps - 1);
    }
    
    function collectResponses() {
        const responses = {};
        $form.find('[name]').each(function() {
            const $this = $(this);
            const name = $this.attr('name');
            if (name.endsWith('[]')) {
                responses[name.slice(0, -2)] = $form.find('[name="' + name + '"]:checked').map(function() { return $(this).val(); }).get();
            } else if ($this.is(':radio,:checkbox')) {
                if ($this.is(':checked')) responses[name] = $this.val();
            } else {
                responses[name] = $this.val();
            }
        });
        return responses;
    }
    
    function updatePreview() {
        const responses = collectResponses();
        $.post(ajaxurl, {
            action: 'wsmvp_get_preview',
            nonce: wsmvp_config.nonce,
            responses: responses
        }, function(res) {
            if (res.success) $preview.html(res.data.html);
        });
    }
    
    $next.on('click', function() {
        if (currentStep < totalSteps - 1) {
            currentStep++;
            updateProgress();
            updatePreview();
        }
    });
    
    $prev.on('click', function() {
        if (currentStep > 0) {
            currentStep--;
            updateProgress();
        }
    });
    
    $form.on('submit', function(e) {
        e.preventDefault();
        const responses = collectResponses();
        $.post(ajaxurl, {
            action: 'wsmvp_save_simulation',
            nonce: wsmvp_config.nonce,
            data: { responses: responses, name: responses.name, email: responses.email, consent: true }
        }, function(res) {
            if (res.success) {
                $form.hide();
                $result.html('<h2>Obrigado!</h2><p>Sua simulação foi salva. ID: ' + res.data.simulation_id + '</p>');
                $result.show();
            } else {
                alert(res.data.message || 'Erro');
            }
        });
    });
    
    $form.on('change', 'input,select,textarea', updatePreview);
    updateProgress();
});
