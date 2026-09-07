<?php if (!defined('ABSPATH')) exit; ?>
<div class="wsmvp-simulator">
    <div class="wsmvp-progress-bar">
        <div class="wsmvp-progress" style="width: 0%;"></div>
    </div>
    
    <form id="wsmvp-form" class="wsmvp-form">
        <input type="hidden" name="action" value="wsmvp_save_simulation">
        <input type="hidden" name="nonce" value="<?php echo WSMVP_Core::generate_nonce('public'); ?>">
        
        <?php
        $steps = WSMVP_Questions::get_instance()->get_questions_by_steps();
        $step_count = count($steps);
        $current_step = 0;
        
        foreach ($steps as $step_key => $step):
            $current_step++;
        ?>
            <div class="wsmvp-step" data-step="<?php echo $current_step; ?>">
                <h2><?php echo esc_html($step['title']); ?></h2>
                <?php foreach ($step['questions'] as $question): 
                    WSMVP_Public::get_instance()->render_question($question, []); 
                endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="wsmvp-navigation">
            <button type="button" class="wsmvp-prev" style="display:none;">Voltar</button>
            <button type="button" class="wsmvp-next">Próximo</button>
            <button type="submit" class="wsmvp-submit" style="display:none;">Solicitar Proposta</button>
        </div>
    </form>
    
    <div id="wsmvp-preview" class="wsmvp-preview-container"></div>
    <div id="wsmvp-result" class="wsmvp-result" style="display:none;"></div>
</div>

<script>
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
</script>
