<?php
/**
 * Futturu Plans Frontend Class
 * Handles shortcodes and frontend rendering of plan tables
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Plans_Frontend {
    
    public function __construct() {
        // Register shortcodes
        add_shortcode('futturu_planos_criacao', array($this, 'render_criacao_table'));
        add_shortcode('futturu_planos_hospedagem', array($this, 'render_hospedagem_table'));
        add_shortcode('futturu_planos_manutencao', array($this, 'render_manutencao_table'));
        add_shortcode('futturu_planos_all', array($this, 'render_all_tables'));
        
        // Enqueue styles and scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Handle CTA form submission
        add_action('wp_ajax_futturu_cta_submit', array($this, 'handle_cta_submit'));
        add_action('wp_ajax_nopriv_futturu_cta_submit', array($this, 'handle_cta_submit'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'futturu-plans-style',
            FUTTURT_PLANS_PLUGIN_URL . 'assets/css/futturu-plans.css',
            array(),
            FUTTURT_PLANS_VERSION
        );
        
        wp_enqueue_script(
            'futturu-plans-script',
            FUTTURT_PLANS_PLUGIN_URL . 'assets/js/futturu-plans.js',
            array('jquery'),
            FUTTURT_PLANS_VERSION,
            true
        );
        
        wp_localize_script('futturu-plans-script', 'futturuPlansAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_cta_nonce')
        ));
    }
    
    /**
     * Render Criação de Websites table
     */
    public function render_criacao_table($atts) {
        return $this->render_table('criacao', $atts);
    }
    
    /**
     * Render Hospedagem table
     */
    public function render_hospedagem_table($atts) {
        return $this->render_table('hospedagem', $atts);
    }
    
    /**
     * Render Manutenção table
     */
    public function render_manutencao_table($atts) {
        return $this->render_table('manutencao', $atts);
    }
    
    /**
     * Render all tables with tabs
     */
    public function render_all_tables($atts) {
        $settings = Futturu_Plans_Settings::get_settings();
        
        ob_start();
        ?>
        <div class="futturu-plans-container futturu-plans-tabs">
            <div class="futturu-tabs-nav">
                <?php if (!empty($settings['criacao']['enabled'])): ?>
                    <button class="futturu-tab-btn active" data-tab="criacao">
                        <span class="tab-icon">🌐</span>
                        Criação de Websites
                    </button>
                <?php endif; ?>
                <?php if (!empty($settings['hospedagem']['enabled'])): ?>
                    <button class="futturu-tab-btn" data-tab="hospedagem">
                        <span class="tab-icon">☁️</span>
                        Hospedagem Cloud
                    </button>
                <?php endif; ?>
                <?php if (!empty($settings['manutencao']['enabled'])): ?>
                    <button class="futturu-tab-btn" data-tab="manutencao">
                        <span class="tab-icon">🔧</span>
                        Manutenção & Suporte
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="futturu-tabs-content">
                <?php if (!empty($settings['criacao']['enabled'])): ?>
                    <div class="futturu-tab-panel active" id="tab-criacao">
                        <?php echo $this->render_table_content('criacao'); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($settings['hospedagem']['enabled'])): ?>
                    <div class="futturu-tab-panel" id="tab-hospedagem">
                        <?php echo $this->render_table_content('hospedagem'); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($settings['manutencao']['enabled'])): ?>
                    <div class="futturu-tab-panel" id="tab-manutencao">
                        <?php echo $this->render_table_content('manutencao'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Generic render table method
     */
    private function render_table($category, $atts) {
        return $this->render_table_content($category);
    }
    
    /**
     * Render table content
     */
    private function render_table_content($category) {
        $settings = Futturu_Plans_Settings::get_settings();
        
        if (!isset($settings[$category]) || empty($settings[$category]['enabled'])) {
            return '<p class="futturu-plans-message">Esta categoria de planos está temporariamente indisponível.</p>';
        }
        
        $cat_data = $settings[$category];
        $plans = !empty($cat_data['plans']) ? $cat_data['plans'] : array();
        
        if (empty($plans)) {
            return '<p class="futturu-plans-message">Nenhum plano disponível no momento.</p>';
        }
        
        // Collect all unique features
        $all_features = array();
        foreach ($plans as $plan) {
            if (!empty($plan['features'])) {
                foreach ($plan['features'] as $key => $feature) {
                    if (!isset($all_features[$key])) {
                        $all_features[$key] = $feature['label'];
                    }
                }
            }
        }
        
        ob_start();
        ?>
        <div class="futturu-plans-table-wrapper">
            <div class="futturu-plans-header">
                <h2 class="futturu-plans-title"><?php echo esc_html($cat_data['title']); ?></h2>
                <?php if (!empty($cat_data['description'])): ?>
                    <p class="futturu-plans-description"><?php echo esc_html($cat_data['description']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="futturu-plans-table">
                <!-- Header Row -->
                <div class="futturu-plans-row futturu-plans-header-row">
                    <div class="futturu-plans-cell futturu-plans-feature-name">
                        <span>Planos</span>
                    </div>
                    <?php foreach ($plans as $plan): ?>
                        <div class="futturu-plans-cell futturu-plans-plan-header <?php echo !empty($plan['highlight']) ? 'futturu-highlighted' : ''; ?>">
                            <div class="futturu-plan-name"><?php echo esc_html($plan['name']); ?></div>
                            <div class="futturu-plan-price"><?php echo esc_html($plan['price']); ?></div>
                            <?php if (!empty($plan['badge'])): ?>
                                <span class="futturu-plan-badge futturu-badge-<?php echo esc_attr($plan['badge_type']); ?>">
                                    <?php echo esc_html($plan['badge']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Features Rows -->
                <?php foreach ($all_features as $feature_key => $feature_label): ?>
                    <div class="futturu-plans-row">
                        <div class="futturu-plans-cell futturu-plans-feature-name">
                            <?php echo esc_html($feature_label); ?>
                        </div>
                        <?php foreach ($plans as $plan): ?>
                            <div class="futturu-plans-cell futturu-plans-feature-value <?php echo !empty($plan['highlight']) ? 'futturu-highlighted' : ''; ?>">
                                <?php if (!empty($plan['features'][$feature_key]['included'])): ?>
                                    <span class="futturu-feature-check">✓</span>
                                <?php else: ?>
                                    <span class="futturu-feature-cross">—</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
                <!-- Value Proposition Row -->
                <div class="futturu-plans-row futturu-plans-value-row">
                    <div class="futturu-plans-cell futturu-plans-feature-name">
                        <strong>Valor Percebido</strong>
                    </div>
                    <?php foreach ($plans as $plan): ?>
                        <div class="futturu-plans-cell futturu-plans-value-prop <?php echo !empty($plan['highlight']) ? 'futturu-highlighted' : ''; ?>">
                            <em><?php echo esc_html($plan['value_proposition']); ?></em>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- CTA Section -->
            <div class="futturu-plans-cta">
                <?php 
                $cta_link = !empty($cat_data['cta_link']) ? $cat_data['cta_link'] : '/contato/';
                $cta_text = !empty($cat_data['cta_text']) ? $cat_data['cta_text'] : 'Falar com um Especialista';
                ?>
                <a href="<?php echo esc_url($cta_link); ?>" class="futturu-cta-button">
                    <?php echo esc_html($cta_text); ?>
                </a>
                <p class="futturu-cta-subtext">Receba uma consultoria gratuita com nossos especialistas</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle CTA form submission via AJAX
     */
    public function handle_cta_submit() {
        check_ajax_referer('futturu_cta_nonce', 'nonce');
        
        $category = sanitize_text_field($_POST['category']);
        $plan_name = sanitize_text_field($_POST['plan_name']);
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $customer_phone = sanitize_text_field($_POST['customer_phone']);
        $message = sanitize_textarea_field($_POST['message']);
        
        // Validate required fields
        if (empty($customer_name) || empty($customer_email)) {
            wp_send_json_error(array('message' => 'Por favor, preencha os campos obrigatórios.'));
        }
        
        // Prepare email
        $to = get_option('admin_email');
        $custom_email = get_option('futturu_plans_contact_email');
        if (!empty($custom_email)) {
            $to = $custom_email;
        }
        
        $subject = sprintf('Novo Interesse: %s - %s', $category, $plan_name);
        
        $email_body = sprintf(
            "Novo lead gerado através da tabela de planos:\n\n" .
            "Categoria: %s\n" .
            "Plano de Interesse: %s\n" .
            "Nome: %s\n" .
            "E-mail: %s\n" .
            "Telefone: %s\n" .
            "Mensagem: %s\n\n" .
            "Enviado em: %s",
            $category,
            $plan_name,
            $customer_name,
            $customer_email,
            $customer_phone,
            $message,
            current_time('mysql')
        );
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $customer_email
        );
        
        // Send email
        $sent = wp_mail($to, $subject, $email_body, $headers);
        
        if ($sent) {
            wp_send_json_success(array('message' => 'Obrigado! Entraremos em contato em breve.'));
        } else {
            wp_send_json_error(array('message' => 'Erro ao enviar mensagem. Tente novamente.'));
        }
    }
}
