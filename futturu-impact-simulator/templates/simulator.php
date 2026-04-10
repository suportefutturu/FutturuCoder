<?php
/**
 * Template for the Impact Simulator
 */
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('fis_settings');
$messages = isset($settings['messages']) ? $settings['messages'] : fis_get_default_messages();
$business_types = fis_get_business_types();
$revenue_ranges = fis_get_revenue_ranges();
$target_audiences = fis_get_target_audiences();
$objectives = fis_get_objectives();
?>

<div id="fis-simulator" class="fis-simulator-container">
    <!-- Header -->
    <div class="fis-header">
        <h2 class="fis-title"><?php echo esc_html($messages['intro_title']); ?></h2>
        <p class="fis-subtitle"><?php echo esc_html($messages['intro_subtitle']); ?></p>
    </div>

    <!-- Step 1: Form -->
    <div class="fis-step fis-step-1 active" data-step="1">
        <div class="fis-step-header">
            <span class="fis-step-number">1</span>
            <h3><?php echo esc_html($messages['step1_title']); ?></h3>
        </div>
        
        <form id="fis-form" class="fis-form">
            <div class="fis-form-grid">
                <!-- Business Type -->
                <div class="fis-form-group">
                    <label for="fis-business-type" class="fis-label">
                        <span class="fis-icon">🏢</span>
                        <?php _e('Tipo de Negócio', 'futturu-impact-simulator'); ?>
                    </label>
                    <select id="fis-business-type" name="business_type" class="fis-select" required>
                        <option value=""><?php _e('Selecione...', 'futturu-impact-simulator'); ?></option>
                        <?php foreach ($business_types as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Revenue Range -->
                <div class="fis-form-group">
                    <label for="fis-revenue-range" class="fis-label">
                        <span class="fis-icon">💰</span>
                        <?php _e('Faturamento Atual Aproximado', 'futturu-impact-simulator'); ?>
                    </label>
                    <select id="fis-revenue-range" name="revenue_range" class="fis-select" required>
                        <option value=""><?php _e('Selecione...', 'futturu-impact-simulator'); ?></option>
                        <?php foreach ($revenue_ranges as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Target Audience -->
                <div class="fis-form-group">
                    <label for="fis-target-audience" class="fis-label">
                        <span class="fis-icon">🎯</span>
                        <?php _e('Público-Alvo Principal', 'futturu-impact-simulator'); ?>
                    </label>
                    <select id="fis-target-audience" name="target_audience" class="fis-select" required>
                        <option value=""><?php _e('Selecione...', 'futturu-impact-simulator'); ?></option>
                        <?php foreach ($target_audiences as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Objective -->
                <div class="fis-form-group">
                    <label for="fis-objective" class="fis-label">
                        <span class="fis-icon">🚀</span>
                        <?php _e('Objetivo Atual com Presença Online', 'futturu-impact-simulator'); ?>
                    </label>
                    <select id="fis-objective" name="objective" class="fis-select" required>
                        <option value=""><?php _e('Selecione...', 'futturu-impact-simulator'); ?></option>
                        <?php foreach ($objectives as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="fis-form-actions">
                <button type="submit" id="fis-calculate-btn" class="fis-btn fis-btn-primary">
                    <span class="fis-btn-text"><?php echo esc_html($messages['calculate_button']); ?></span>
                    <span class="fis-btn-loader" style="display: none;">
                        <svg class="fis-spinner" viewBox="0 0 24 24" width="20" height="20">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" stroke-linecap="round"/>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Step 2: Loading -->
    <div class="fis-step fis-step-2" data-step="2">
        <div class="fis-loading">
            <div class="fis-loading-animation">
                <div class="fis-pulse"></div>
                <div class="fis-chart-icon">📊</div>
            </div>
            <h3><?php echo esc_html($messages['step2_title']); ?></h3>
            <p><?php _e('Analisando benchmarks do seu setor...', 'futturu-impact-simulator'); ?></p>
        </div>
    </div>

    <!-- Step 3: Results -->
    <div class="fis-step fis-step-3" data-step="3">
        <div class="fis-step-header">
            <span class="fis-step-number">3</span>
            <h3><?php echo esc_html($messages['step3_title']); ?></h3>
        </div>

        <!-- Comparison Panels -->
        <div class="fis-comparison">
            <div class="fis-panel fis-panel-current">
                <div class="fis-panel-header">
                    <span class="fis-panel-icon">📍</span>
                    <h4><?php echo esc_html($messages['current_situation']); ?></h4>
                </div>
                <div class="fis-metrics" id="fis-current-metrics">
                    <!-- Populated by JS -->
                </div>
            </div>

            <div class="fis-panel fis-panel-projected">
                <div class="fis-panel-header">
                    <span class="fis-panel-icon">✨</span>
                    <h4><?php echo esc_html($messages['with_futturu']); ?></h4>
                </div>
                <div class="fis-metrics" id="fis-projected-metrics">
                    <!-- Populated by JS -->
                </div>
                <div class="fis-badge"><?php _e('PROJEÇÃO', 'futturu-impact-simulator'); ?></div>
            </div>
        </div>

        <!-- Charts -->
        <div class="fis-charts-container">
            <div class="fis-chart-wrapper">
                <canvas id="fis-comparison-chart"></canvas>
            </div>
        </div>

        <!-- Increase Highlights -->
        <div class="fis-highlights" id="fis-highlights">
            <!-- Populated by JS -->
        </div>

        <!-- Justifications -->
        <div class="fis-justifications" id="fis-justifications">
            <!-- Populated by JS -->
        </div>

        <!-- Disclaimer -->
        <div class="fis-disclaimer">
            <p><small><?php echo esc_html($settings['fis_disclaimer'] ?? $messages['disclaimer']); ?></small></p>
        </div>

        <!-- CTA Section -->
        <div class="fis-cta-section">
            <h3 class="fis-cta-title"><?php echo esc_html($messages['cta_title']); ?></h3>
            <p class="fis-cta-subtitle"><?php echo esc_html($messages['cta_subtitle']); ?></p>
            <button type="button" id="fis-cta-btn" class="fis-btn fis-btn-cta">
                <?php echo esc_html($settings['fis_cta_text'] ?? 'Falar com um Especialista da Futturu'); ?>
            </button>
        </div>

        <!-- Back Button -->
        <div class="fis-form-actions">
            <button type="button" id="fis-back-btn" class="fis-btn fis-btn-secondary">
                <?php echo esc_html($messages['back_button']); ?>
            </button>
        </div>
    </div>

    <!-- Contact Modal -->
    <div id="fis-modal" class="fis-modal">
        <div class="fis-modal-overlay"></div>
        <div class="fis-modal-content">
            <button class="fis-modal-close" aria-label="<?php _e('Fechar', 'futturu-impact-simulator'); ?>">&times;</button>
            <div class="fis-modal-header">
                <h3><?php echo esc_html($messages['contact_form_title']); ?></h3>
            </div>
            <form id="fis-contact-form" class="fis-contact-form">
                <input type="hidden" id="fis-contact-business-type" name="business_type" value="">
                
                <div class="fis-form-group">
                    <label for="fis-contact-name" class="fis-label"><?php _e('Nome *', 'futturu-impact-simulator'); ?></label>
                    <input type="text" id="fis-contact-name" name="name" class="fis-input" required>
                </div>

                <div class="fis-form-group">
                    <label for="fis-contact-email" class="fis-label"><?php _e('E-mail *', 'futturu-impact-simulator'); ?></label>
                    <input type="email" id="fis-contact-email" name="email" class="fis-input" required>
                </div>

                <div class="fis-form-group">
                    <label for="fis-contact-phone" class="fis-label"><?php _e('Telefone', 'futturu-impact-simulator'); ?></label>
                    <input type="tel" id="fis-contact-phone" name="phone" class="fis-input">
                </div>

                <div class="fis-form-group">
                    <label for="fis-contact-message" class="fis-label"><?php _e('Mensagem', 'futturu-impact-simulator'); ?></label>
                    <textarea id="fis-contact-message" name="message" class="fis-textarea" rows="4"></textarea>
                </div>

                <div class="fis-form-actions">
                    <button type="submit" class="fis-btn fis-btn-primary fis-btn-full">
                        <span class="fis-btn-text"><?php _e('Enviar Solicitação', 'futturu-impact-simulator'); ?></span>
                        <span class="fis-btn-loader" style="display: none;">
                            <svg class="fis-spinner" viewBox="0 0 24 24" width="20" height="20">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
            <div id="fis-contact-success" class="fis-success-message" style="display: none;">
                <div class="fis-success-icon">✓</div>
                <p><?php echo esc_html($messages['success_message']); ?></p>
            </div>
        </div>
    </div>
</div>
