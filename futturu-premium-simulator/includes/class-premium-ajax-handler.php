<?php
/**
 * AJAX Handler Class
 * Handles form submission, calculations, and email notifications
 * 
 * @package Futturu_Premium_Simulator
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Futturu_Premium_Ajax_Handler {
    
    private static $instance = null;
    
    // Base values (configurable via admin)
    private $base_values = array();
    private $complexity_multipliers = array();
    private $addon_costs = array();
    private $hosting_plans = array();
    private $maintenance_plans = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_configurations();
    }
    
    private function init_hooks() {
        // Public AJAX actions (for non-logged-in users)
        add_action('wp_ajax_nopriv_futturu_premium_submit', array($this, 'handle_form_submission'));
        add_action('wp_ajax_futturu_premium_submit', array($this, 'handle_form_submission'));
    }
    
    /**
     * Load configuration values from options or set defaults
     */
    private function load_configurations() {
        // Base values by site category (Sinapro table reference)
        $this->base_values = get_option('futturu_premium_base_values', array(
            'institucional' => 3500,
            'ecommerce' => 8000,
            'landing_page' => 2000,
            'portal' => 12000,
            'blog' => 3000,
            'marketplace' => 15000,
            'saas' => 18000,
            'outro' => 4000
        ));
        
        // Complexity multipliers
        $this->complexity_multipliers = get_option('futturu_premium_complexity_multipliers', array(
            'baixa' => 1.0,
            'media' => 1.4,
            'alta' => 1.9
        ));
        
        // Add-on costs
        $this->addon_costs = get_option('futturu_premium_addon_costs', array(
            'faq' => 300,
            'login_sistema' => 1500,
            'newsletter' => 500,
            'busca_avancada' => 800,
            'chat_online' => 600,
            'agendamento' => 1200,
            'area_cliente' => 2000,
            'multidioma' => 1500,
            'blog_integrado' => 800,
            'galeria_fotos' => 400,
            'video_embed' => 300,
            'redes_sociais' => 350,
            'whatsapp_button' => 200,
            'analytics' => 400
        ));
        
        // Hosting plans (Cloudez - annual costs)
        $this->hosting_plans = get_option('futturu_premium_hosting_plans', array(
            'starter' => 600,      // R$ 50/month
            'professional' => 1200, // R$ 100/month
            'business' => 2400,     // R$ 200/month
            'enterprise' => 4800    // R$ 400/month
        ));
        
        // Maintenance plans (annual costs)
        $this->maintenance_plans = get_option('futturu_premium_maintenance_plans', array(
            'basico' => 1200,      // R$ 100/month
            'padrao' => 2400,      // R$ 200/month
            'premium' => 4800,     // R$ 400/month
            'empresarial' => 9600  // R$ 800/month
        ));
    }
    
    /**
     * Handle form submission via AJAX
     */
    public function handle_form_submission() {
        // Verify nonce
        check_ajax_referer('futturu_premium_nonce', 'security');
        
        // Validate required fields
        $required_fields = array(
            'project_type', 'site_category', 'complexity', 'pages_count',
            'client_name', 'client_email', 'client_phone'
        );
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array(
                    'message' => sprintf(__('Campo obrigatório ausente: %s', 'futturu-premium-simulator'), $field)
                ));
            }
        }
        
        // Sanitize and collect data
        $data = $this->sanitize_submission_data($_POST);
        
        // Calculate internal estimates
        $calculations = $this->calculate_estimates($data);
        
        // Merge calculations with data
        $data = array_merge($data, $calculations);
        
        // Insert into database
        $db = Futturu_Premium_Database::get_instance();
        $insert_id = $db->insert_simulation($data);
        
        if (is_wp_error($insert_id)) {
            wp_send_json_error(array(
                'message' => __('Erro ao salvar dados. Tente novamente.', 'futturu-premium-simulator')
            ));
        }
        
        // Send email notification
        $email_sent = $this->send_notification_email($data, $insert_id);
        
        // Success response
        wp_send_json_success(array(
            'message' => __('Simulação enviada com sucesso! Nossa equipe entrará em contato em breve.', 'futturu-premium-simulator'),
            'submission_id' => $insert_id
        ));
    }
    
    /**
     * Sanitize all submitted data
     * 
     * @param array $post_data Raw POST data
     * @return array Sanitized data
     */
    private function sanitize_submission_data($post_data) {
        return array(
            'project_type' => sanitize_text_field($post_data['project_type']),
            'site_category' => sanitize_text_field($post_data['site_category']),
            'complexity' => sanitize_text_field($post_data['complexity']),
            'pages_count' => absint($post_data['pages_count']),
            'pages_checklist' => isset($post_data['pages_checklist']) ? (array)$post_data['pages_checklist'] : array(),
            'languages' => sanitize_text_field($post_data['languages'] ?? ''),
            'text_origin' => sanitize_text_field($post_data['text_origin'] ?? ''),
            'image_origin' => sanitize_text_field($post_data['image_origin'] ?? ''),
            'addons_selected' => isset($post_data['addons_selected']) ? (array)$post_data['addons_selected'] : array(),
            'google_integrations' => isset($post_data['google_integrations']) ? (array)$post_data['google_integrations'] : array(),
            'seo_level' => sanitize_text_field($post_data['seo_level'] ?? 'basico'),
            'domain_status' => sanitize_text_field($post_data['domain_status'] ?? 'nao_tenho'),
            'current_hosting' => sanitize_text_field($post_data['current_hosting'] ?? ''),
            'cloud_interest' => sanitize_text_field($post_data['cloud_interest'] ?? 'nao'),
            'server_resources' => isset($post_data['server_resources']) ? (array)$post_data['server_resources'] : array(),
            'maintenance_frequency' => sanitize_text_field($post_data['maintenance_frequency'] ?? 'mensal'),
            'maintenance_plan' => sanitize_text_field($post_data['maintenance_plan'] ?? 'nenhum'),
            'company_category' => sanitize_text_field($post_data['company_category'] ?? ''),
            'budget_range' => sanitize_text_field($post_data['budget_range'] ?? ''),
            'desired_deadline' => sanitize_text_field($post_data['desired_deadline'] ?? ''),
            'meeting_type' => sanitize_text_field($post_data['meeting_type'] ?? ''),
            'client_name' => sanitize_text_field($post_data['client_name']),
            'client_email' => sanitize_email($post_data['client_email']),
            'client_phone' => sanitize_text_field($post_data['client_phone']),
            'client_cnpj' => sanitize_text_field($post_data['client_cnpj'] ?? ''),
            'client_segment' => sanitize_text_field($post_data['client_segment'] ?? ''),
            'how_found_us' => sanitize_text_field($post_data['how_found_us'] ?? ''),
            'observations' => sanitize_textarea_field($post_data['observations'] ?? '')
        );
    }
    
    /**
     * Calculate internal estimates based on Sinapro table and Cloudez costs
     * 
     * @param array $data Form data
     * @return array Calculated values
     */
    private function calculate_estimates($data) {
        $base_value = $this->base_values[$data['site_category']] ?? 4000;
        $complexity_multiplier = $this->complexity_multipliers[$data['complexity']] ?? 1.0;
        
        // Base calculation with complexity multiplier
        $project_base = $base_value * $complexity_multiplier;
        
        // Pages adjustment (base includes up to 5 pages)
        $extra_pages = max(0, $data['pages_count'] - 5);
        $pages_cost = $extra_pages * 300; // R$ 300 per extra page
        
        // Add-ons cost
        $addons_total = 0;
        foreach ($data['addons_selected'] as $addon) {
            $addons_total += $this->addon_costs[$addon] ?? 0;
        }
        
        // SEO level adjustment
        $seo_cost = ($data['seo_level'] === 'avancado') ? 2000 : 500;
        
        // Development total
        $development_total = $project_base + $pages_cost + $addons_total + $seo_cost;
        
        // Hosting cost (if interested in Cloud Premium)
        $hosting_annual = 0;
        if ($data['cloud_interest'] === 'sim') {
            $hosting_tier = 'professional'; // Default tier
            if ($data['site_category'] === 'ecommerce' || $data['site_category'] === 'marketplace') {
                $hosting_tier = 'business';
            } elseif ($data['site_category'] === 'saas' || $data['site_category'] === 'portal') {
                $hosting_tier = 'enterprise';
            }
            $hosting_annual = $this->hosting_plans[$hosting_tier] ?? 1200;
        }
        
        // Maintenance cost
        $maintenance_annual = 0;
        if ($data['maintenance_plan'] !== 'nenhum') {
            $maintenance_annual = $this->maintenance_plans[$data['maintenance_plan']] ?? 0;
        }
        
        // Total first year estimate
        $total_first_year = $development_total + $hosting_annual + $maintenance_annual;
        
        return array(
            'estimated_value_internal' => round($development_total, 2),
            'hosting_cost_annual' => round($hosting_annual, 2),
            'maintenance_cost_annual' => round($maintenance_annual, 2),
            'total_estimated' => round($total_first_year, 2)
        );
    }
    
    /**
     * Send notification email to sales team
     * 
     * @param array $data Simulation data
     * @param int $submission_id Submission ID
     * @return bool Email sent successfully
     */
    private function send_notification_email($data, $submission_id) {
        $to = get_option('futturu_premium_email_destination', 'suporte@futturu.com.br');
        $subject = sprintf('Nova Simulação Premium - %s', $data['client_name']);
        
        // Build HTML email body
        $message = $this->build_email_body($data, $submission_id);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Futturu Simulator <noreply@' . $_SERVER['HTTP_HOST'] . '>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Build HTML email body
     * 
     * @param array $data Simulation data
     * @param int $submission_id Submission ID
     * @return string HTML message
     */
    private function build_email_body($data, $submission_id) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 800px; margin: 0 auto; padding: 20px; }
                .header { background: #1a73e8; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .section { margin-bottom: 25px; }
                .section h3 { color: #1a73e8; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }
                .info-row { display: flex; margin-bottom: 10px; }
                .info-label { font-weight: bold; width: 200px; }
                .info-value { flex: 1; }
                .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; border-radius: 0 0 5px 5px; font-size: 12px; }
                ul { margin: 5px 0; padding-left: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚀 Nova Simulação Premium</h1>
                    <p>ID: #<?php echo esc_html($submission_id); ?> | <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format')); ?></p>
                </div>
                
                <div class="content">
                    <div class="section">
                        <h3>📋 Dados do Cliente</h3>
                        <div class="info-row">
                            <span class="info-label">Nome:</span>
                            <span class="info-value"><?php echo esc_html($data['client_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">E-mail:</span>
                            <span class="info-value"><?php echo esc_html($data['client_email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">WhatsApp/Telefone:</span>
                            <span class="info-value"><?php echo esc_html($data['client_phone']); ?></span>
                        </div>
                        <?php if (!empty($data['client_cnpj'])): ?>
                        <div class="info-row">
                            <span class="info-label">CNPJ:</span>
                            <span class="info-value"><?php echo esc_html($data['client_cnpj']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <span class="info-label">Segmento:</span>
                            <span class="info-value"><?php echo esc_html($data['client_segment']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Como conheceu:</span>
                            <span class="info-value"><?php echo esc_html($data['how_found_us']); ?></span>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h3>🌐 Detalhes do Projeto</h3>
                        <div class="info-row">
                            <span class="info-label">Tipo de Projeto:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($data['project_type'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Categoria do Site:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($data['site_category'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Complexidade:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($data['complexity'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Número de Páginas:</span>
                            <span class="info-value"><?php echo esc_html($data['pages_count']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Idiomas:</span>
                            <span class="info-value"><?php echo esc_html($data['languages'] ?: 'Português'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Nível de SEO:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($data['seo_level'])); ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($data['addons_selected'])): ?>
                    <div class="section">
                        <h3>🔧 Recursos Adicionais (Add-ons)</h3>
                        <ul>
                            <?php foreach ($data['addons_selected'] as $addon): ?>
                                <li><?php echo esc_html(ucfirst(str_replace('_', ' ', $addon))); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div class="section">
                        <h3>☁️ Hospedagem & Infraestrutura</h3>
                        <div class="info-row">
                            <span class="info-label">Status do Domínio:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($data['domain_status'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Interesse Cloud Premium:</span>
                            <span class="info-value"><?php echo esc_html($data['cloud_interest'] === 'sim' ? '✅ Sim' : '❌ Não'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Plano de Manutenção:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($data['maintenance_plan'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h3>💰 Expectativas de Investimento</h3>
                        <div class="info-row">
                            <span class="info-label">Categoria da Empresa:</span>
                            <span class="info-value"><?php echo esc_html(ucfirst($data['company_category'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Faixa de Budget:</span>
                            <span class="info-value"><?php echo esc_html($data['budget_range']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Prazo Desejado:</span>
                            <span class="info-value"><?php echo esc_html($data['desired_deadline']); ?></span>
                        </div>
                    </div>
                    
                    <div class="highlight">
                        <h3>📊 Estimativa Interna (Não mostrar ao cliente)</h3>
                        <div class="info-row">
                            <span class="info-label">Desenvolvimento:</span>
                            <span class="info-value">R$ <?php echo number_format($data['estimated_value_internal'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Hospedagem Anual:</span>
                            <span class="info-value">R$ <?php echo number_format($data['hosting_cost_annual'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Manutenção Anual:</span>
                            <span class="info-value">R$ <?php echo number_format($data['maintenance_cost_annual'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="info-row" style="font-weight: bold; font-size: 18px; margin-top: 15px; border-top: 2px solid #333; padding-top: 10px;">
                            <span class="info-label">Total Primeiro Ano:</span>
                            <span class="info-value">R$ <?php echo number_format($data['total_estimated'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($data['observations'])): ?>
                    <div class="section">
                        <h3>📝 Observações do Cliente</h3>
                        <p><?php echo nl2br(esc_html($data['observations'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="footer">
                    <p>Este e-mail foi gerado automaticamente pelo Simulador Premium Futturu.</p>
                    <p>Acesse o painel administrativo para mais detalhes: <?php echo admin_url('admin.php?page=futturu-premium-simulations'); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
