<?php
/**
 * Plugin Name: Simulador de Economia com Suporte Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Simulador de ROI para demonstrar economia de custos e tempo ao delegar manutenção, segurança e suporte técnico para a Futturu.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-roi-simulator
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FUTTURI_ROI_VERSION', '1.0.0');
define('FUTTURI_ROI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FUTTURI_ROI_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Futturi_ROI_Simulator {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('futturu_roi_sim', array($this, 'render_simulator_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_futturu_send_simulation', array($this, 'handle_simulation_submission'));
        add_action('wp_ajax_nopriv_futturu_send_simulation', array($this, 'handle_simulation_submission'));
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('futturu-roi-simulator', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Simulador ROI Futturu', 'futturu-roi-simulator'),
            __('Simulador ROI Futturu', 'futturu-roi-simulator'),
            'manage_options',
            'futturu-roi-simulator',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('futturu_roi_settings_group', 'futturu_roi_annual_plan_cost', array(
            'type' => 'number',
            'sanitize_callback' => 'floatval',
            'default' => 2500.00
        ));
        
        register_setting('futturu_roi_settings_group', 'futturu_roi_residual_time_percent', array(
            'type' => 'number',
            'sanitize_callback' => 'floatval',
            'default' => 20
        ));
        
        register_setting('futturu_roi_settings_group', 'futturu_roi_explanation_text', array(
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => 'Este é um cálculo estimativo com base nas suas respostas. Os valores podem variar conforme a complexidade real do seu projeto.'
        ));
        
        register_setting('futturu_roi_settings_group', 'futturu_roi_benefits_text', array(
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => 'Com o suporte gerenciado Futturu, você ganha: segurança reforçada, atualizações automáticas, backup diário, performance otimizada e resposta rápida a problemas.'
        ));
        
        register_setting('futturu_roi_settings_group', 'futturu_roi_email_destination', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => 'suporte@futturu.com.br'
        ));
        
        register_setting('futturu_roi_settings_group', 'futturu_roi_success_message', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Obrigado! Recebemos sua simulação e entraremos em contato em breve.'
        ));
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Configurações do Simulador ROI Futturu', 'futturu-roi-simulator'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('futturu_roi_settings_group'); ?>
                <?php do_settings_sections('futturu_roi_settings_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="futturu_roi_annual_plan_cost"><?php echo esc_html__('Custo Anual do Plano (R$)', 'futturu-roi-simulator'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="futturu_roi_annual_plan_cost" id="futturu_roi_annual_plan_cost" value="<?php echo esc_attr(get_option('futturu_roi_annual_plan_cost', 2500.00)); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__('Valor anual estimado para hospedagem cloud gerenciada + suporte técnico.', 'futturu-roi-simulator'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="futturu_roi_residual_time_percent"><?php echo esc_html__('Percentual de Tempo Residual (%)', 'futturu-roi-simulator'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="1" min="0" max="100" name="futturu_roi_residual_time_percent" id="futturu_roi_residual_time_percent" value="<?php echo esc_attr(get_option('futturu_roi_residual_time_percent', 20)); ?>" class="small-text" />
                            <p class="description"><?php echo esc_html__('Percentual do tempo que o cliente ainda dedicará com o suporte gerenciado.', 'futturu-roi-simulator'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="futturu_roi_email_destination"><?php echo esc_html__('E-mail de Destino', 'futturu-roi-simulator'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="futturu_roi_email_destination" id="futturu_roi_email_destination" value="<?php echo esc_attr(get_option('futturu_roi_email_destination', 'suporte@futturu.com.br')); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__('E-mail que receberá as simulações enviadas.', 'futturu-roi-simulator'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="futturu_roi_explanation_text"><?php echo esc_html__('Texto Explicativo do Cálculo', 'futturu-roi-simulator'); ?></label>
                        </th>
                        <td>
                            <textarea name="futturu_roi_explanation_text" id="futturu_roi_explanation_text" rows="3" class="large-text"><?php echo esc_textarea(get_option('futturu_roi_explanation_text', '')); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="futturu_roi_benefits_text"><?php echo esc_html__('Texto de Benefícios', 'futturu-roi-simulator'); ?></label>
                        </th>
                        <td>
                            <textarea name="futturu_roi_benefits_text" id="futturu_roi_benefits_text" rows="3" class="large-text"><?php echo esc_textarea(get_option('futturu_roi_benefits_text', '')); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="futturu_roi_success_message"><?php echo esc_html__('Mensagem de Sucesso', 'futturu-roi-simulator'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="futturu_roi_success_message" id="futturu_roi_success_message" value="<?php echo esc_attr(get_option('futturu_roi_success_message', '')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        wp_enqueue_style('futturu-roi-style', FUTTURI_ROI_PLUGIN_URL . 'assets/css/futturu-roi.css', array(), FUTTURI_ROI_VERSION);
        wp_enqueue_script('futturu-roi-script', FUTTURI_ROI_PLUGIN_URL . 'assets/js/futturu-roi.js', array('jquery'), FUTTURI_ROI_VERSION, true);
        
        // Pass configuration to JavaScript
        wp_localize_script('futturu-roi-script', 'futturuRoiConfig', array(
            'annualPlanCost' => floatval(get_option('futturu_roi_annual_plan_cost', 2500.00)),
            'residualPercent' => floatval(get_option('futturu_roi_residual_time_percent', 20))
        ));
        
        wp_localize_script('futturu-roi-script', 'futturuRoiAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_roi_nonce')
        ));
    }
    
    /**
     * Render simulator shortcode
     */
    public function render_simulator_shortcode($atts) {
        $annual_plan_cost = get_option('futturu_roi_annual_plan_cost', 2500.00);
        $residual_percent = get_option('futturu_roi_residual_time_percent', 20);
        $explanation_text = get_option('futturu_roi_explanation_text', '');
        $benefits_text = get_option('futturu_roi_benefits_text', '');
        
        ob_start();
        ?>
        <div class="futturu-roi-simulator" id="futturu-roi-simulator">
            <h2 class="futturu-roi-title"><?php echo esc_html__('Simulador de Economia - Suporte Futturu', 'futturu-roi-simulator'); ?></h2>
            <p class="futturu-roi-subtitle"><?php echo esc_html__('Descubra quanto você pode economizar ao delegar a manutenção do seu website para a Futturu.', 'futturu-roi-simulator'); ?></p>
            
            <form id="futturu-roi-form" class="futturu-roi-form">
                <!-- Perfil do Usuário -->
                <div class="futturu-roi-section">
                    <h3 class="futturu-roi-section-title"><?php echo esc_html__('Seu Perfil', 'futturu-roi-simulator'); ?></h3>
                    
                    <div class="futturu-roi-field">
                        <label for="futturu_sector"><?php echo esc_html__('Setor de Atuação', 'futturu-roi-simulator'); ?></label>
                        <select id="futturu_sector" name="futturu_sector" required>
                            <option value=""><?php echo esc_html__('Selecione...', 'futturu-roi-simulator'); ?></option>
                            <option value="consultoria"><?php echo esc_html__('Consultoria', 'futturu-roi-simulator'); ?></option>
                            <option value="ecommerce"><?php echo esc_html__('Comércio Eletrônico', 'futturu-roi-simulator'); ?></option>
                            <option value="servicos"><?php echo esc_html__('Serviços Profissionais', 'futturu-roi-simulator'); ?></option>
                            <option value="saude"><?php echo esc_html__('Saúde', 'futturu-roi-simulator'); ?></option>
                            <option value="educacao"><?php echo esc_html__('Educação', 'futturu-roi-simulator'); ?></option>
                            <option value="outro"><?php echo esc_html__('Outro', 'futturu-roi-simulator'); ?></option>
                        </select>
                    </div>
                    
                    <div class="futturu-roi-field">
                        <label for="futturu_company_size"><?php echo esc_html__('Tamanho da Empresa', 'futturu-roi-simulator'); ?></label>
                        <select id="futturu_company_size" name="futturu_company_size" required>
                            <option value=""><?php echo esc_html__('Selecione...', 'futturu-roi-simulator'); ?></option>
                            <option value="mei"><?php echo esc_html__('Microempresa (MEI/ME)', 'futturu-roi-simulator'); ?></option>
                            <option value="pequena"><?php echo esc_html__('Pequena (1-10 funcionários)', 'futturu-roi-simulator'); ?></option>
                            <option value="media"><?php echo esc_html__('Média (11-50 funcionários)', 'futturu-roi-simulator'); ?></option>
                            <option value="grande"><?php echo esc_html__('Grande (>50 funcionários)', 'futturu-roi-simulator'); ?></option>
                        </select>
                    </div>
                    
                    <div class="futturu-roi-field">
                        <label for="futturu_experience"><?php echo esc_html__('Nível de Experiência Técnica', 'futturu-roi-simulator'); ?></label>
                        <select id="futturu_experience" name="futturu_experience" required>
                            <option value=""><?php echo esc_html__('Selecione...', 'futturu-roi-simulator'); ?></option>
                            <option value="baixo"><?php echo esc_html__('Baixo (Necessito de ajuda constante)', 'futturu-roi-simulator'); ?></option>
                            <option value="medio"><?php echo esc_html__('Médio (Consigo resolver coisas básicas)', 'futturu-roi-simulator'); ?></option>
                            <option value="alto"><?php echo esc_html__('Alto (Tenho conhecimentos avançados)', 'futturu-roi-simulator'); ?></option>
                        </select>
                    </div>
                </div>
                
                <!-- Situação Atual -->
                <div class="futturu-roi-section">
                    <h3 class="futturu-roi-section-title"><?php echo esc_html__('Sua Situação Atual', 'futturu-roi-simulator'); ?></h3>
                    
                    <div class="futturu-roi-field">
                        <label for="futturu_websites_count"><?php echo esc_html__('Quantos websites você gerencia atualmente?', 'futturu-roi-simulator'); ?></label>
                        <input type="number" id="futturu_websites_count" name="futturu_websites_count" min="1" value="1" required />
                    </div>
                    
                    <div class="futturu-roi-field">
                        <label for="futturu_hours_month"><?php echo esc_html__('Horas por mês gastas com manutenção dos sites', 'futturu-roi-simulator'); ?></label>
                        <input type="range" id="futturu_hours_month" name="futturu_hours_month" min="0" max="80" step="5" value="10" />
                        <span id="futturu_hours_display">10</span> <?php echo esc_html__('horas/mês', 'futturu-roi-simulator'); ?>
                    </div>
                    
                    <div class="futturu-roi-field">
                        <label for="futturu_hourly_rate"><?php echo esc_html__('Valor/hora médio do seu tempo (R$)', 'futturu-roi-simulator'); ?></label>
                        <input type="number" id="futturu_hourly_rate" name="futturu_hourly_rate" min="0" step="10" value="100" required />
                    </div>
                    
                    <div class="futturu-roi-field">
                        <label for="futturu_hosting_cost"><?php echo esc_html__('Custo anual com hospedagem/freelancers (R$) - Opcional', 'futturu-roi-simulator'); ?></label>
                        <input type="number" id="futturu_hosting_cost" name="futturu_hosting_cost" min="0" step="100" value="0" />
                    </div>
                </div>
                
                <button type="submit" class="futturu-roi-button" id="futturu-calculate-btn">
                    <?php echo esc_html__('Calcular Economia', 'futturu-roi-simulator'); ?>
                </button>
            </form>
            
            <!-- Resultados -->
            <div id="futturu-roi-results" class="futturu-roi-results" style="display:none;">
                <h3 class="futturu-roi-results-title"><?php echo esc_html__('Resultado da Simulação', 'futturu-roi-simulator'); ?></h3>
                
                <div class="futturu-roi-comparison">
                    <div class="futturu-roi-card futturu-roi-card-current">
                        <h4><?php echo esc_html__('Cenário Atual', 'futturu-roi-simulator'); ?></h4>
                        <div class="futturu-roi-value">
                            <span class="futturu-roi-currency">R$</span>
                            <span id="futturu-current-cost">0</span>
                            <span class="futturu-roi-period"><?php echo esc_html__('/ano', 'futturu-roi-simulator'); ?></span>
                        </div>
                        <p class="futturu-roi-detail"><?php echo esc_html__('Tempo interno + Hospedagem/Terceiros', 'futturu-roi-simulator'); ?></p>
                    </div>
                    
                    <div class="futturu-roi-card futturu-roi-card-futturu">
                        <h4><?php echo esc_html__('Com Suporte Futturu', 'futturu-roi-simulator'); ?></h4>
                        <div class="futturu-roi-value">
                            <span class="futturu-roi-currency">R$</span>
                            <span id="futturu-futturu-cost">0</span>
                            <span class="futturu-roi-period"><?php echo esc_html__('/ano', 'futturu-roi-simulator'); ?></span>
                        </div>
                        <p class="futturu-roi-detail"><?php echo esc_html__('Hospedagem Gerenciada + Tempo Residual', 'futturu-roi-simulator'); ?></p>
                    </div>
                </div>
                
                <div class="futturu-roi-savings">
                    <h4><?php echo esc_html__('Sua Economia Anual Estimada', 'futturu-roi-simulator'); ?></h4>
                    <div class="futturu-roi-savings-amount">
                        <span class="futturu-roi-currency">R$</span>
                        <span id="futturu-savings">0</span>
                    </div>
                    <div class="futturu-roi-savings-percent" id="futturu-savings-percent">0%</div>
                    <div class="futturu-roi-hours-freed">
                        <strong id="futturu-hours-freed">0</strong> <?php echo esc_html__('horas/mês liberadas para focar no seu negócio', 'futturu-roi-simulator'); ?>
                    </div>
                </div>
                
                <div class="futturu-roi-benefits">
                    <h4><?php echo esc_html__('Benefícios do Suporte Gerenciado', 'futturu-roi-simulator'); ?></h4>
                    <p><?php echo wp_kses_post($benefits_text); ?></p>
                </div>
                
                <p class="futturu-roi-disclaimer"><?php echo wp_kses_post($explanation_text); ?></p>
                
                <!-- Formulário de Contato -->
                <div class="futturu-roi-contact">
                    <h4><?php echo esc_html__('Quer levar essa economia para a realidade?', 'futturu-roi-simulator'); ?></h4>
                    <form id="futturu-contact-form" class="futturu-contact-form">
                        <input type="hidden" id="futturu-simulation-data" name="futturu_simulation_data" />
                        
                        <div class="futturu-contact-field">
                            <label for="futturu_name"><?php echo esc_html__('Nome Completo', 'futturu-roi-simulator'); ?> *</label>
                            <input type="text" id="futturu_name" name="futturu_name" required />
                        </div>
                        
                        <div class="futturu-contact-field">
                            <label for="futturu_email"><?php echo esc_html__('E-mail', 'futturu-roi-simulator'); ?> *</label>
                            <input type="email" id="futturu_email" name="futturu_email" required />
                        </div>
                        
                        <div class="futturu-contact-field">
                            <label for="futturu_phone"><?php echo esc_html__('Telefone/WhatsApp', 'futturu-roi-simulator'); ?> *</label>
                            <input type="tel" id="futturu_phone" name="futturu_phone" required />
                        </div>
                        
                        <div class="futturu-contact-field">
                            <label for="futturu_company"><?php echo esc_html__('Empresa (opcional)', 'futturu-roi-simulator'); ?></label>
                            <input type="text" id="futturu_company" name="futturu_company" />
                        </div>
                        
                        <div class="futturu-contact-field">
                            <label for="futturu_message"><?php echo esc_html__('Mensagem (opcional)', 'futturu-roi-simulator'); ?></label>
                            <textarea id="futturu_message" name="futturu_message" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="futturu-roi-button futturu-roi-button-submit">
                            <?php echo esc_html__('Enviar e Solicitar Contato', 'futturu-roi-simulator'); ?>
                        </button>
                    </form>
                    <div id="futturu-contact-success" class="futturu-contact-success" style="display:none;">
                        <p><?php echo esc_html(get_option('futturu_roi_success_message', 'Obrigado! Recebemos sua simulação e entraremos em contato em breve.')); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle simulation submission
     */
    public function handle_simulation_submission() {
        check_ajax_referer('futturu_roi_nonce', 'nonce');
        
        // Sanitize input data
        $name = sanitize_text_field($_POST['futturu_name']);
        $email = sanitize_email($_POST['futturu_email']);
        $phone = sanitize_text_field($_POST['futturu_phone']);
        $company = sanitize_text_field($_POST['futturu_company']);
        $message = sanitize_textarea_field($_POST['futturu_message']);
        $simulation_data = json_decode(stripslashes($_POST['futturu_simulation_data']), true);
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone)) {
            wp_send_json_error(array('message' => __('Por favor, preencha todos os campos obrigatórios.', 'futturu-roi-simulator')));
        }
        
        // Prepare email content
        $to = get_option('futturu_roi_email_destination', 'suporte@futturu.com.br');
        $subject = sprintf(__('Nova Simulação ROI - %s', 'futturu-roi-simulator'), $name);
        
        $current_cost = isset($simulation_data['current_cost']) ? number_format($simulation_data['current_cost'], 2, ',', '.') : '0';
        $futturu_cost = isset($simulation_data['futturu_cost']) ? number_format($simulation_data['futturu_cost'], 2, ',', '.') : '0';
        $savings = isset($simulation_data['savings']) ? number_format($simulation_data['savings'], 2, ',', '.') : '0';
        $savings_percent = isset($simulation_data['savings_percent']) ? number_format($simulation_data['savings_percent'], 1) : '0';
        $hours_freed = isset($simulation_data['hours_freed']) ? number_format($simulation_data['hours_freed'], 1) : '0';
        
        $message_body = sprintf(
            __("Nova simulação enviada pelo site.\n\nDados do Cliente:\nNome: %s\nE-mail: %s\nTelefone: %s\nEmpresa: %s\n\nDados da Simulação:\nCusto Atual Anual: R$ %s\nCusto com Futturu Anual: R$ %s\nEconomia Anual: R$ %s (%s%%)\nHoras Liberadas/Mês: %s\n\nMensagem:\n%s", 'futturu-roi-simulator'),
            $name,
            $email,
            $phone,
            $company,
            $current_cost,
            $futturu_cost,
            $savings,
            $savings_percent,
            $hours_freed,
            $message
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        // Send email
        if (wp_mail($to, $subject, $message_body, $headers)) {
            wp_send_json_success(array('message' => get_option('futturu_roi_success_message', __('Obrigado! Recebemos sua simulação e entraremos em contato em breve.', 'futturu-roi-simulator'))));
        } else {
            wp_send_json_error(array('message' => __('Erro ao enviar simulação. Por favor, tente novamente.', 'futturu-roi-simulator')));
        }
    }
}

// Initialize plugin
Futturi_ROI_Simulator::get_instance();
