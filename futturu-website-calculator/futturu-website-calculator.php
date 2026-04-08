<?php
/**
 * Plugin Name: Calculadora de Criação de Website Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Calculadora interativa para orçamento estimado de criação de websites. Permite que potenciais clientes calculem um orçamento com base em suas escolhas e enviem os dados para a equipe Futturu.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-calculator
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FUTTURU_CALC_VERSION', '1.0.0');
define('FUTTURU_CALC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FUTTURU_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Futturu_Website_Calculator {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Shortcode
        add_shortcode('futturu_calc', array($this, 'render_calculator'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_futturu_send_quote', array($this, 'handle_send_quote'));
        add_action('wp_ajax_nopriv_futturu_send_quote', array($this, 'handle_send_quote'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Calculadora Futturu', 'futturu-calculator'),
            __('Calculadora Futturu', 'futturu-calculator'),
            'manage_options',
            'futturu-calculator',
            array($this, 'render_admin_page'),
            'dashicons-calculator',
            30
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Website Types
        register_setting('futturu_calc_settings', 'futturu_calc_website_types');
        
        // Complexity Levels
        register_setting('futturu_calc_settings', 'futturu_calc_complexity_levels');
        
        // Page Price
        register_setting('futturu_calc_settings', 'futturu_calc_page_price');
        
        // Extras
        register_setting('futturu_calc_settings', 'futturu_calc_extras');
        
        // Hosting Plans
        register_setting('futturu_calc_settings', 'futturu_calc_hosting_plans');
        
        // Email Settings
        register_setting('futturu_calc_settings', 'futturu_calc_email_to');
        register_setting('futturu_calc_settings', 'futturu_calc_email_subject');
        
        // Messages
        register_setting('futturu_calc_settings', 'futturu_calc_success_message');
        register_setting('futturu_calc_settings', 'futturu_calc_error_message');
    }
    
    /**
     * Get default website types
     */
    public function get_default_website_types() {
        return array(
            'institucional' => array('label' => 'Website Institucional', 'price' => 2500),
            'blog' => array('label' => 'Blog', 'price' => 1800),
            'landing_page' => array('label' => 'Landing Page', 'price' => 1200),
            'hotsite' => array('label' => 'Hotsite', 'price' => 1500),
        );
    }
    
    /**
     * Get default complexity levels
     */
    public function get_default_complexity_levels() {
        return array(
            'baixa' => array('label' => 'Baixa (Layout padrão)', 'multiplier' => 1.0),
            'media' => array('label' => 'Média (Personalizações básicas)', 'multiplier' => 1.3),
            'alta' => array('label' => 'Alta (Layout exclusivo e funcionalidades avançadas)', 'multiplier' => 1.7),
        );
    }
    
    /**
     * Get default extras
     */
    public function get_default_extras() {
        return array(
            'form_contato' => array('label' => 'Formulário de Contato Avançado', 'price' => 300),
            'api_integracao' => array('label' => 'Integração com API Externa', 'price' => 800),
            'catalogo' => array('label' => 'Catálogo de Produtos Estático', 'price' => 600),
            'blog_integrado' => array('label' => 'Blog Integrado', 'price' => 500),
            'area_membros' => array('label' => 'Área de Membros Simples', 'price' => 900),
            'chat_online' => array('label' => 'Chat Online', 'price' => 400),
            'redes_sociais' => array('label' => 'Integração com Redes Sociais', 'price' => 350),
            'seo_basico' => array('label' => 'SEO Básico', 'price' => 450),
            'analytics' => array('label' => 'Google Analytics Setup', 'price' => 250),
            'multidioma' => array('label' => 'Site Multilíngue', 'price' => 700),
        );
    }
    
    /**
     * Get default hosting plans
     */
    public function get_default_hosting_plans() {
        return array(
            'basico' => array('label' => 'Básico (Até 10GB, 5 sites)', 'price' => 49.90),
            'profissional' => array('label' => 'Profissional (Até 50GB, 10 sites)', 'price' => 89.90),
            'empresarial' => array('label' => 'Empresarial (100GB+, Sites ilimitados)', 'price' => 149.90),
        );
    }
    
    /**
     * Get settings with defaults
     */
    public function get_settings() {
        $settings = array();
        
        $website_types = get_option('futturu_calc_website_types');
        $settings['website_types'] = !empty($website_types) ? json_decode($website_types, true) : $this->get_default_website_types();
        
        $complexity = get_option('futturu_calc_complexity_levels');
        $settings['complexity_levels'] = !empty($complexity) ? json_decode($complexity, true) : $this->get_default_complexity_levels();
        
        $settings['page_price'] = floatval(get_option('futturu_calc_page_price', 150));
        
        $extras = get_option('futturu_calc_extras');
        $settings['extras'] = !empty($extras) ? json_decode($extras, true) : $this->get_default_extras();
        
        $hosting = get_option('futturu_calc_hosting_plans');
        $settings['hosting_plans'] = !empty($hosting) ? json_decode($hosting, true) : $this->get_default_hosting_plans();
        
        $settings['email_to'] = get_option('futturu_calc_email_to', 'suporte@futturu.com.br');
        $settings['email_subject'] = get_option('futturu_calc_email_subject', 'Novo Orçamento - Calculadora Futturu');
        
        $settings['success_message'] = get_option('futturu_calc_success_message', 'Obrigado! Recebemos sua solicitação e entraremos em contato em breve com uma proposta detalhada.');
        $settings['error_message'] = get_option('futturu_calc_error_message', 'Ocorreu um erro ao enviar. Por favor, tente novamente ou entre em contato diretamente.');
        
        return $settings;
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        $settings = $this->get_settings();
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Configurações da Calculadora Futturu', 'futturu-calculator'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('futturu_calc_settings'); ?>
                <?php do_settings_sections('futturu_calc_settings'); ?>
                
                <table class="form-table">
                    <!-- Website Types -->
                    <tr>
                        <th scope="row"><?php _e('Tipos de Website', 'futturu-calculator'); ?></th>
                        <td>
                            <textarea name="futturu_calc_website_types" rows="6" class="large-text code"><?php 
                                echo esc_textarea(json_encode($settings['website_types'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
                            ?></textarea>
                            <p class="description">Formato JSON: {"chave": {"label": "Nome", "price": valor}}</p>
                        </td>
                    </tr>
                    
                    <!-- Complexity Levels -->
                    <tr>
                        <th scope="row"><?php _e('Níveis de Complexidade', 'futturu-calculator'); ?></th>
                        <td>
                            <textarea name="futturu_calc_complexity_levels" rows="6" class="large-text code"><?php 
                                echo esc_textarea(json_encode($settings['complexity_levels'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
                            ?></textarea>
                            <p class="description">Formato JSON: {"chave": {"label": "Nome", "multiplier": multiplicador}}</p>
                        </td>
                    </tr>
                    
                    <!-- Page Price -->
                    <tr>
                        <th scope="row"><?php _e('Valor por Página Adicional', 'futturu-calculator'); ?></th>
                        <td>
                            <input type="number" name="futturu_calc_page_price" value="<?php echo esc_attr($settings['page_price']); ?>" class="small-text" step="0.01" min="0">
                            <p class="description">Valor cobrado por cada página além da home page.</p>
                        </td>
                    </tr>
                    
                    <!-- Extras -->
                    <tr>
                        <th scope="row"><?php _e('Extras/Plugins/Aplicações', 'futturu-calculator'); ?></th>
                        <td>
                            <textarea name="futturu_calc_extras" rows="10" class="large-text code"><?php 
                                echo esc_textarea(json_encode($settings['extras'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
                            ?></textarea>
                            <p class="description">Formato JSON: {"chave": {"label": "Nome", "price": valor}}</p>
                        </td>
                    </tr>
                    
                    <!-- Hosting Plans -->
                    <tr>
                        <th scope="row"><?php _e('Planos de Hospedagem', 'futturu-calculator'); ?></th>
                        <td>
                            <textarea name="futturu_calc_hosting_plans" rows="6" class="large-text code"><?php 
                                echo esc_textarea(json_encode($settings['hosting_plans'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
                            ?></textarea>
                            <p class="description">Formato JSON: {"chave": {"label": "Nome", "price": valor_mensal}}</p>
                        </td>
                    </tr>
                    
                    <!-- Email Settings -->
                    <tr>
                        <th scope="row"><?php _e('E-mail de Destino', 'futturu-calculator'); ?></th>
                        <td>
                            <input type="email" name="futturu_calc_email_to" value="<?php echo esc_attr($settings['email_to']); ?>" class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Assunto do E-mail', 'futturu-calculator'); ?></th>
                        <td>
                            <input type="text" name="futturu_calc_email_subject" value="<?php echo esc_attr($settings['email_subject']); ?>" class="regular-text">
                        </td>
                    </tr>
                    
                    <!-- Messages -->
                    <tr>
                        <th scope="row"><?php _e('Mensagem de Sucesso', 'futturu-calculator'); ?></th>
                        <td>
                            <textarea name="futturu_calc_success_message" rows="3" class="large-text"><?php echo esc_textarea($settings['success_message']); ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Mensagem de Erro', 'futturu-calculator'); ?></th>
                        <td>
                            <textarea name="futturu_calc_error_message" rows="3" class="large-text"><?php echo esc_textarea($settings['error_message']); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Como usar', 'futturu-calculator'); ?></h2>
            <p><?php _e('Use o shortcode <code>[futturu_calc]</code> em qualquer página ou post para exibir a calculadora.', 'futturu-calculator'); ?></p>
            <p><?php _e('Você também pode usar em templates PHP com: <code>&lt;?php echo do_shortcode(\'[futturu_calc]\'); ?&gt;</code>', 'futturu-calculator'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        wp_enqueue_style('futturu-calc-style', FUTTURU_CALC_PLUGIN_URL . 'assets/css/style.css', array(), FUTTURU_CALC_VERSION);
        wp_enqueue_script('futturu-calc-script', FUTTURU_CALC_PLUGIN_URL . 'assets/js/calculator.js', array('jquery'), FUTTURU_CALC_VERSION, true);
        
        wp_localize_script('futturu-calc-script', 'futturuCalcAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_calc_nonce'),
            'success_message' => $this->get_settings()['success_message'],
            'error_message' => $this->get_settings()['error_message'],
        ));
    }
    
    /**
     * Render calculator shortcode
     */
    public function render_calculator() {
        $settings = $this->get_settings();
        
        ob_start();
        ?>
        <div class="futturu-calculator-wrapper">
            <div class="futturu-calculator">
                <h2 class="futturu-calc-title"><?php _e('Calculadora de Criação de Website', 'futturu-calculator'); ?></h2>
                <p class="futturu-calc-subtitle"><?php _e('Descubra o investimento ideal para o seu projeto. Preencha as opções abaixo e receba uma estimativa personalizada.', 'futturu-calculator'); ?></p>
                
                <form id="futturu-calculator-form">
                    <!-- A. Tipo de Website -->
                    <div class="futturu-calc-section">
                        <h3 class="futturu-calc-section-title"><?php _e('1. Tipo de Website', 'futturu-calculator'); ?></h3>
                        <select name="website_type" id="website_type" class="futturu-calc-input futturu-calc-select" required>
                            <option value=""><?php _e('Selecione o tipo de website...', 'futturu-calculator'); ?></option>
                            <?php foreach ($settings['website_types'] as $key => $type): ?>
                                <option value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($type['price']); ?>">
                                    <?php echo esc_html($type['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- B. Complexidade -->
                    <div class="futturu-calc-section">
                        <h3 class="futturu-calc-section-title"><?php _e('2. Complexidade do Projeto', 'futturu-calculator'); ?></h3>
                        <select name="complexity" id="complexity" class="futturu-calc-input futturu-calc-select" required>
                            <option value=""><?php _e('Selecione a complexidade...', 'futturu-calculator'); ?></option>
                            <?php foreach ($settings['complexity_levels'] as $key => $level): ?>
                                <option value="<?php echo esc_attr($key); ?>" data-multiplier="<?php echo esc_attr($level['multiplier']); ?>">
                                    <?php echo esc_html($level['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- C. Número de Páginas -->
                    <div class="futturu-calc-section">
                        <h3 class="futturu-calc-section-title"><?php _e('3. Número de Páginas', 'futturu-calculator'); ?></h3>
                        <div class="futturu-calc-input-group">
                            <input type="number" name="num_pages" id="num_pages" class="futturu-calc-input futturu-calc-number" min="1" max="100" value="1" required>
                            <span class="futturu-calc-help"><?php _e('Número de páginas internas (a home page está inclusa)', 'futturu-calculator'); ?></span>
                        </div>
                    </div>
                    
                    <!-- D. Extras -->
                    <div class="futturu-calc-section">
                        <h3 class="futturu-calc-section-title"><?php _e('4. Aplicações, Plugins e Extras', 'futturu-calculator'); ?></h3>
                        <div class="futturu-calc-checkboxes">
                            <?php foreach ($settings['extras'] as $key => $extra): ?>
                                <label class="futturu-calc-checkbox">
                                    <input type="checkbox" name="extras[]" value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($extra['price']); ?>">
                                    <span class="futturu-calc-checkbox-label">
                                        <?php echo esc_html($extra['label']); ?>
                                        <span class="futturu-calc-price-tag">+ R$ <?php echo number_format($extra['price'], 2, ',', '.'); ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- E. Hospedagem -->
                    <div class="futturu-calc-section">
                        <h3 class="futturu-calc-section-title"><?php _e('5. Plano de Hospedagem Cloud (Opcional)', 'futturu-calculator'); ?></h3>
                        <select name="hosting" id="hosting" class="futturu-calc-input futturu-calc-select">
                            <option value=""><?php _e('Não necessito de hospedagem neste momento', 'futturu-calculator'); ?></option>
                            <?php foreach ($settings['hosting_plans'] as $key => $plan): ?>
                                <option value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($plan['price']); ?>">
                                    <?php echo esc_html($plan['label']); ?> - R$ <?php echo number_format($plan['price'], 2, ',', '.'); ?>/mês
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- F. Resultado -->
                    <div class="futturu-calc-section futturu-calc-result-section">
                        <h3 class="futturu-calc-section-title"><?php _e('Previsão de Investimento Aproximado', 'futturu-calculator'); ?></h3>
                        <div class="futturu-calc-result">
                            <div class="futturu-calc-total">
                                <span class="futturu-calc-total-label"><?php _e('Investimento Inicial Estimado:', 'futturu-calculator'); ?></span>
                                <span class="futturu-calc-total-value" id="development_total">R$ 0,00</span>
                            </div>
                            <div class="futturu-calc-hosting" id="hosting_display" style="display: none;">
                                <span class="futturu-calc-hosting-label"><?php _e('Custo Mensal Estimado (Hospedagem):', 'futturu-calculator'); ?></span>
                                <span class="futturu-calc-hosting-value" id="hosting_total">R$ 0,00/mês</span>
                            </div>
                        </div>
                        <p class="futturu-calc-disclaimer"><?php _e('* Esta é uma estimativa inicial. O valor final pode variar mediante análise detalhada dos requisitos.', 'futturu-calculator'); ?></p>
                    </div>
                    
                    <button type="button" id="futturu-calc-calculate" class="futturu-calc-btn futturu-calc-btn-primary">
                        <?php _e('Calcular Orçamento', 'futturu-calculator'); ?>
                    </button>
                    
                    <!-- Formulário de Contato -->
                    <div id="futturu-contact-form" class="futturu-calc-section" style="display: none;">
                        <h3 class="futturu-calc-section-title"><?php _e('Pronto! Agora preencha seus dados', 'futturu-calculator'); ?></h3>
                        <p class="futturu-calc-cta"><?php _e('Preencha seus dados e entraremos em contato com uma proposta detalhada.', 'futturu-calculator'); ?></p>
                        
                        <div class="futturu-calc-form-row">
                            <label for="contact_name"><?php _e('Nome Completo *', 'futturu-calculator'); ?></label>
                            <input type="text" name="contact_name" id="contact_name" class="futturu-calc-input" required>
                        </div>
                        
                        <div class="futturu-calc-form-row">
                            <label for="contact_email"><?php _e('E-mail *', 'futturu-calculator'); ?></label>
                            <input type="email" name="contact_email" id="contact_email" class="futturu-calc-input" required>
                        </div>
                        
                        <div class="futturu-calc-form-row">
                            <label for="contact_phone"><?php _e('Telefone/WhatsApp *', 'futturu-calculator'); ?></label>
                            <input type="tel" name="contact_phone" id="contact_phone" class="futturu-calc-input" required>
                        </div>
                        
                        <div class="futturu-calc-form-row">
                            <label for="contact_company"><?php _e('Empresa', 'futturu-calculator'); ?></label>
                            <input type="text" name="contact_company" id="contact_company" class="futturu-calc-input">
                        </div>
                        
                        <div class="futturu-calc-form-row">
                            <label for="contact_message"><?php _e('Mensagem', 'futturu-calculator'); ?></label>
                            <textarea name="contact_message" id="contact_message" class="futturu-calc-input futturu-calc-textarea" rows="4"></textarea>
                        </div>
                        
                        <input type="hidden" name="calc_summary" id="calc_summary">
                        <input type="hidden" name="calc_development_total" id="calc_development_total">
                        <input type="hidden" name="calc_hosting_total" id="calc_hosting_total">
                        
                        <button type="submit" id="futturu-calc-submit" class="futturu-calc-btn futturu-calc-btn-success">
                            <?php _e('Enviar Solicitação', 'futturu-calculator'); ?>
                        </button>
                    </div>
                    
                    <!-- Mensagens -->
                    <div id="futturu-calc-message" class="futturu-calc-message" style="display: none;"></div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle form submission
     */
    public function handle_send_quote() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'futturu_calc_nonce')) {
            wp_send_json_error(array('message' => 'Erro de segurança. Tente novamente.'));
        }
        
        // Sanitize inputs
        $name = sanitize_text_field($_POST['contact_name']);
        $email = sanitize_email($_POST['contact_email']);
        $phone = sanitize_text_field($_POST['contact_phone']);
        $company = sanitize_text_field($_POST['contact_company']);
        $message = sanitize_textarea_field($_POST['contact_message']);
        $calc_summary = sanitize_textarea_field($_POST['calc_summary']);
        $development_total = sanitize_text_field($_POST['calc_development_total']);
        $hosting_total = sanitize_text_field($_POST['calc_hosting_total']);
        
        // Validation
        if (empty($name) || empty($email) || empty($phone)) {
            wp_send_json_error(array('message' => 'Por favor, preencha todos os campos obrigatórios.'));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Por favor, informe um e-mail válido.'));
        }
        
        $settings = $this->get_settings();
        
        // Prepare email content
        $subject = $settings['email_subject'];
        
        $email_body = sprintf(
            __("Novo Orçamento Solicitado - Calculadora Futturu\n\nDADOS DO CONTATO:\nNome: %s\nE-mail: %s\nTelefone/WhatsApp: %s\nEmpresa: %s\n\nRESUMO DO PROJETO:\n%s\n\nINVESTIMENTO ESTIMADO:\nDesenvolvimento: %s\nHospedagem Mensal: %s\n\nMENSAGEM ADICIONAL:\n%s\n\n---\nEnviado via Calculadora Futturu em %s", 'futturu-calculator'),
            $name,
            $email,
            $phone,
            $company,
            $calc_summary,
            $development_total,
            $hosting_total,
            $message,
            current_time('d/m/Y H:i:s')
        );
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $name . ' <' . $email . '>',
        );
        
        // Send email
        $sent = wp_mail($settings['email_to'], $subject, $email_body, $headers);
        
        if ($sent) {
            wp_send_json_success(array('message' => $settings['success_message']));
        } else {
            wp_send_json_error(array('message' => $settings['error_message']));
        }
    }
}

// Initialize plugin
Futturu_Website_Calculator::get_instance();
