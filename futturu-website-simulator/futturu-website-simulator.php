<?php
/**
 * Plugin Name: Simulador de WebSite Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Gerador de protótipo visual interativo de hotsites profissionais para captura de leads qualificados.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-website-simulator
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FUTTURU_SIMULATOR_VERSION', '1.0.0');
define('FUTTURU_SIMULATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FUTTURU_SIMULATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Futturu_Website_Simulator {
    
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
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin Menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Shortcode
        add_shortcode('futturu_hotsite_simulator', array($this, 'render_simulator'));
        
        // Enqueue Scripts and Styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX Handlers
        add_action('wp_ajax_futturu_send_lead', array($this, 'handle_send_lead'));
        add_action('wp_ajax_nopriv_futturu_send_lead', array($this, 'handle_send_lead'));
    }
    
    public function activate() {
        // Default options
        $default_options = array(
            'futturu_enabled' => 1,
            'futturu_site_types' => array(
                array('id' => 'institutional', 'name' => 'Local na Rede (Website Institucional)'),
                array('id' => 'ecommerce', 'name' => 'Loja Online (E-commerce Básico - WooCommerce)'),
                array('id' => 'blog', 'name' => 'Blog Profissional'),
                array('id' => 'landing', 'name' => 'Página de Destino (Landing Page Conversora)'),
            ),
            'futturu_categories' => array(
                'Restaurante', 'Café', 'Floricultura', 'Advocacia', 'Clínica Médica',
                'Consultoria', 'Escola', 'Oficina Mecânica', 'Aluguel de Carros',
                'Loja de Roupas', 'Academia', 'Salão de Beleza', 'Hotel', 'Imobiliária',
                'Contabilidade', 'Arquitetura', 'Veterinária', 'Farmácia', 'Mercado', 'Outros'
            ),
            'futturu_templates' => $this->get_default_templates(),
            'futturu_email_to' => 'suporte@futturu.com.br',
            'futturu_cta_text' => 'Solicite uma Proposta Personalizada',
            'futturu_email_subject' => 'Novo Lead - Simulador Futturu',
        );
        
        if (!get_option('futturu_simulator_options')) {
            add_option('futturu_simulator_options', $default_options);
        }
        
        // Create lead table
        global $wpdb;
        $table_name = $wpdb->prefix . 'futturu_leads';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            site_type varchar(50) NOT NULL,
            business_name varchar(200) NOT NULL,
            category varchar(100) NOT NULL,
            locality varchar(100) NOT NULL,
            services text NOT NULL,
            target_audience text NOT NULL,
            differential text NOT NULL,
            generated_description text NOT NULL,
            full_name varchar(200) NOT NULL,
            phone varchar(20) DEFAULT '' NOT NULL,
            email varchar(100) NOT NULL,
            preview_data longtext NOT NULL,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY submission_date (submission_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
    
    private function get_default_templates() {
        return array(
            array(
                'id' => 1,
                'template' => 'Transforme seu {categoria} em referência absoluta em {localidade}! O {nome} se destaca por oferecer {servicos}, sempre com o diferencial de {diferencial}. A escolha perfeita para {publico} que exige qualidade, confiança e excelência no atendimento.'
            ),
            array(
                'id' => 2,
                'template' => 'Descubra o {nome}, o {categoria} que está revolucionando o mercado em {localidade}. Especializado em {servicos}, nosso maior orgulho é {diferencial}. Atendemos {publico} com dedicação, profissionalismo e resultados comprovados.'
            ),
            array(
                'id' => 3,
                'template' => '{nome}: Muito mais que um {categoria}, uma experiência completa em {localidade}. Oferecemos {servicos} com {diferencial}, garantindo a satisfação total de {publico} que busca o melhor custo-benefício da região.'
            ),
            array(
                'id' => 4,
                'template' => 'Em {localidade}, o {nome} é sinônimo de confiança e qualidade como {categoria}. Com {servicos} e {diferencial}, somos a solução ideal para {publico} que valoriza atendimento personalizado e resultados excepcionais.'
            ),
            array(
                'id' => 5,
                'template' => 'Conheça o {nome}, a nova referência em {categoria} em {localidade}. Nossos serviços incluem {servicos}, todos entregues com {diferencial}. A escolha certa para {publico} que não abre mão de excelência.'
            ),
            array(
                'id' => 6,
                'template' => 'O {nome} é exatamente o {categoria} que {publico} de {localidade} estava procurando. Oferecemos {servicos} com {diferencial}, proporcionando uma experiência única e memorável para cada cliente.'
            ),
            array(
                'id' => 7,
                'template' => 'Procurando um {categoria} de verdade em {localidade}? O {nome} entrega {servicos} com o incomparável {diferencial}. Desenvolvido especialmente para {publico} exigente que conhece qualidade quando vê.'
            ),
            array(
                'id' => 8,
                'template' => '{nome}: Excelência e tradição em {categoria} em {localidade}. Contamos com {servicos} e {diferencial} para superar as expectativas de {publico} mais criteriosos.'
            ),
            array(
                'id' => 9,
                'template' => 'Seu {categoria} premium em {localidade} chegou: {nome}. Oferecemos {servicos}, tendo {diferencial} como nosso compromisso diário. Tudo pensado nos mínimos detalhes para {publico} que merece o melhor.'
            ),
            array(
                'id' => 10,
                'template' => 'O {nome} estabelece novo padrão como {categoria} em {localidade}. Com {servicos} e {diferencial}, atendemos {publico} com maestria, transformando necessidades em soluções reais e duradouras.'
            ),
        );
    }
    
    /**
     * Get default templates as a simple array (for JS fallback)
     */
    private function get_default_templates_array() {
        $templates = $this->get_default_templates();
        return array_column($templates, 'template');
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Simulador WebSite Futturu', 'futturu-website-simulator'),
            __('Simulador Futturu', 'futturu-website-simulator'),
            'manage_options',
            'futturu-simulator',
            array($this, 'render_admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('futturu_simulator_group', 'futturu_simulator_options', array(
            'sanitize_callback' => array($this, 'sanitize_options')
        ));
    }
    
    public function sanitize_options($input) {
        $sanitized = array();
        $sanitized['futturu_enabled'] = isset($input['futturu_enabled']) ? 1 : 0;
        $sanitized['futturu_email_to'] = sanitize_email($input['futturu_email_to']);
        $sanitized['futturu_cta_text'] = sanitize_text_field($input['futturu_cta_text']);
        $sanitized['futturu_email_subject'] = sanitize_text_field($input['futturu_email_subject']);
        
        // Sanitize site types
        if (isset($input['futturu_site_types'])) {
            $sanitized['futturu_site_types'] = array_map(function($type) {
                return array(
                    'id' => sanitize_text_field($type['id']),
                    'name' => sanitize_text_field($type['name'])
                );
            }, $input['futturu_site_types']);
        }
        
        // Sanitize categories
        if (isset($input['futturu_categories'])) {
            $categories = explode("\n", $input['futturu_categories']);
            $sanitized['futturu_categories'] = array_map('sanitize_text_field', array_filter($categories));
        }
        
        // Sanitize templates
        if (isset($input['futturu_templates'])) {
            $sanitized['futturu_templates'] = array_map(function($template) {
                return array(
                    'id' => intval($template['id']),
                    'template' => sanitize_textarea_field($template['template'])
                );
            }, $input['futturu_templates']);
        }
        
        return $sanitized;
    }
    
    public function render_admin_page() {
        $options = get_option('futturu_simulator_options', array());
        
        // Prepare categories as textarea
        $categories_text = '';
        if (isset($options['futturu_categories']) && is_array($options['futturu_categories'])) {
            $categories_text = implode("\n", $options['futturu_categories']);
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Configurações do Simulador Futturu', 'futturu-website-simulator'); ?></h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('futturu_simulator_group');
                do_settings_sections('futturu_simulator_group');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Ativar Plugin', 'futturu-website-simulator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="futturu_simulator_options[futturu_enabled]" value="1" <?php checked(isset($options['futturu_enabled']) && $options['futturu_enabled']); ?> />
                                <?php _e('Habilitar simulador no frontend', 'futturu-website-simulator'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('E-mail para Leads', 'futturu-website-simulator'); ?></th>
                        <td>
                            <input type="email" name="futturu_simulator_options[futturu_email_to]" value="<?php echo esc_attr(isset($options['futturu_email_to']) ? $options['futturu_email_to'] : 'suporte@futturu.com.br'); ?>" class="regular-text" required />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Texto do CTA', 'futturu-website-simulator'); ?></th>
                        <td>
                            <input type="text" name="futturu_simulator_options[futturu_cta_text]" value="<?php echo esc_attr(isset($options['futturu_cta_text']) ? $options['futturu_cta_text'] : 'Solicite uma Proposta Personalizada'); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Assunto do E-mail', 'futturu-website-simulator'); ?></th>
                        <td>
                            <input type="text" name="futturu_simulator_options[futturu_email_subject]" value="<?php echo esc_attr(isset($options['futturu_email_subject']) ? $options['futturu_email_subject'] : 'Novo Lead - Simulador Futturu'); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Tipos de Site', 'futturu-website-simulator'); ?></th>
                        <td>
                            <p class="description"><?php _e('Defina os tipos de site disponíveis no Passo 1:', 'futturu-website-simulator'); ?></p>
                            <div id="site-types-container">
                                <?php
                                $site_types = isset($options['futturu_site_types']) ? $options['futturu_site_types'] : array();
                                if (empty($site_types)) {
                                    $site_types = array(
                                        array('id' => 'institutional', 'name' => 'Local na Rede (Website Institucional)'),
                                        array('id' => 'ecommerce', 'name' => 'Loja Online (E-commerce Básico - WooCommerce)'),
                                        array('id' => 'blog', 'name' => 'Blog Profissional'),
                                        array('id' => 'landing', 'name' => 'Página de Destino (Landing Page Conversora)'),
                                    );
                                }
                                foreach ($site_types as $index => $type) {
                                    ?>
                                    <div class="site-type-row" style="margin-bottom: 10px;">
                                        <input type="text" name="futturu_simulator_options[futturu_site_types][<?php echo $index; ?>][id]" value="<?php echo esc_attr($type['id']); ?>" placeholder="ID" style="width: 150px;" />
                                        <input type="text" name="futturu_simulator_options[futturu_site_types][<?php echo $index; ?>][name]" value="<?php echo esc_attr($type['name']); ?>" placeholder="Nome" style="width: 400px;" />
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Categorias de Negócio', 'futturu-website-simulator'); ?></th>
                        <td>
                            <textarea name="futturu_simulator_options[futturu_categories]" rows="10" cols="50" class="large-text"><?php echo esc_textarea($categories_text); ?></textarea>
                            <p class="description"><?php _e('Uma categoria por linha', 'futturu-website-simulator'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Templates de Descrição', 'futturu-website-simulator'); ?></th>
                        <td>
                            <p class="description"><?php _e('Use os placeholders: {nome}, {categoria}, {localidade}, {servicos}, {publico}, {diferencial}', 'futturu-website-simulator'); ?></p>
                            <div id="templates-container">
                                <?php
                                $templates = isset($options['futturu_templates']) ? $options['futturu_templates'] : $this->get_default_templates();
                                foreach ($templates as $index => $template) {
                                    ?>
                                    <div class="template-row" style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;">
                                        <label>Template #<?php echo $template['id']; ?>:</label><br/>
                                        <input type="hidden" name="futturu_simulator_options[futturu_templates][<?php echo $index; ?>][id]" value="<?php echo esc_attr($template['id']); ?>" />
                                        <textarea name="futturu_simulator_options[futturu_templates][<?php echo $index; ?>][template]" rows="3" cols="80" class="large-text"><?php echo esc_textarea($template['template']); ?></textarea>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr/>
            
            <h2><?php _e('Instruções de Uso', 'futturu-website-simulator'); ?></h2>
            <p><?php _e('Para usar o simulador em qualquer página ou post, utilize o shortcode:', 'futturu-website-simulator'); ?></p>
            <code>[futturu_hotsite_simulator]</code>
            <p><?php _e('Você também pode usar em templates PHP com:', 'futturu-website-simulator'); ?></p>
            <code>&lt;?php echo do_shortcode('[futturu_hotsite_simulator]'); ?&gt;</code>
        </div>
        <?php
    }
    
    public function enqueue_assets() {
        $options = get_option('futturu_simulator_options', array());
        
        wp_enqueue_style(
            'futturu-simulator-css',
            FUTTURU_SIMULATOR_PLUGIN_URL . 'assets/css/simulator.css',
            array(),
            FUTTURU_SIMULATOR_VERSION
        );
        
        wp_enqueue_script(
            'futturu-simulator-js',
            FUTTURU_SIMULATOR_PLUGIN_URL . 'assets/js/simulator.js',
            array('jquery'),
            FUTTURU_SIMULATOR_VERSION,
            true
        );
        
        $templates = array();
        if (!empty($options['futturu_templates']) && is_array($options['futturu_templates'])) {
            foreach ($options['futturu_templates'] as $template_item) {
                if (isset($template_item['template'])) {
                    $templates[] = $template_item['template'];
                }
            }
        }
        
        // Fallback to default templates if none configured
        if (empty($templates)) {
            $templates = $this->get_default_templates_array();
        }
        
        wp_localize_script('futturu-simulator-js', 'futturuSimulator', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_simulator_nonce'),
            'templates' => $templates,
            'strings' => array(
                'step1' => __('Escolha o Tipo de Site', 'futturu-website-simulator'),
                'step2' => __('Informações do Negócio', 'futturu-website-simulator'),
                'step3' => __('Formulário de Contato', 'futturu-website-simulator'),
                'next' => __('Avançar', 'futturu-website-simulator'),
                'back' => __('Voltar', 'futturu-website-simulator'),
                'submit' => __('Solicite uma Proposta Personalizada', 'futturu-website-simulator'),
                'generating' => __('Gerando descrição...', 'futturu-website-simulator'),
                'sending' => __('Enviando...', 'futturu-website-simulator'),
                'error' => __('Ocorreu um erro. Tente novamente.', 'futturu-website-simulator'),
                'success' => __('Obrigado! Sua solicitação foi enviada com sucesso.', 'futturu-website-simulator'),
                'required' => __('Este campo é obrigatório', 'futturu-website-simulator'),
                'invalidEmail' => __('E-mail inválido', 'futturu-website-simulator'),
                'invalidPhone' => __('Telefone inválido', 'futturu-website-simulator'),
                'fillRequiredFields' => __('Preencha pelo menos Nome e Categoria para gerar a descrição', 'futturu-website-simulator'),
                'descriptionGenerated' => __('Descrição gerada com sucesso!', 'futturu-website-simulator'),
            )
        ));
    }
    
    public function render_simulator($atts) {
        $options = get_option('futturu_simulator_options', array());
        
        if (!isset($options['futturu_enabled']) || !$options['futturu_enabled']) {
            return '<p>' . __('Simulador temporariamente indisponível.', 'futturu-website-simulator') . '</p>';
        }
        
        // Ensure scripts and styles are loaded
        wp_enqueue_style('futturu-simulator-css');
        wp_enqueue_script('futturu-simulator-js');
        
        $site_types = isset($options['futturu_site_types']) ? $options['futturu_site_types'] : array();
        $categories = isset($options['futturu_categories']) ? $options['futturu_categories'] : array();
        $cta_text = isset($options['futturu_cta_text']) ? $options['futturu_cta_text'] : 'Solicite uma Proposta Personalizada';
        
        ob_start();
        ?>
        <div id="futturu-simulator" class="futturu-simulator-container">
            <div class="futturu-simulator-header">
                <h2><?php _e('Simulador de WebSite Futturu', 'futturu-website-simulator'); ?></h2>
                <p><?php _e('Descubra como seria o hotsite profissional do seu negócio', 'futturu-website-simulator'); ?></p>
            </div>
            
            <!-- Progress Steps -->
            <div class="futturu-progress-steps">
                <div class="futturu-step active" data-step="1">
                    <span class="step-number">1</span>
                    <span class="step-label"><?php _e('Tipo de Site', 'futturu-website-simulator'); ?></span>
                </div>
                <div class="futturu-step" data-step="2">
                    <span class="step-number">2</span>
                    <span class="step-label"><?php _e('Informações', 'futturu-website-simulator'); ?></span>
                </div>
                <div class="futturu-step" data-step="3">
                    <span class="step-number">3</span>
                    <span class="step-label"><?php _e('Contato', 'futturu-website-simulator'); ?></span>
                </div>
            </div>
            
            <form id="futturu-simulator-form" method="post">
                <!-- Step 1: Site Type Selection -->
                <div class="futturu-step-content active" data-step="1">
                    <h3><?php _e('Escolha o Tipo de Site', 'futturu-website-simulator'); ?></h3>
                    <div class="futturu-site-types-grid">
                        <?php foreach ($site_types as $type): ?>
                        <label class="futturu-site-type-card">
                            <input type="radio" name="site_type" value="<?php echo esc_attr($type['id']); ?>" required />
                            <div class="card-content">
                                <span class="card-icon">
                                    <?php
                                    switch($type['id']) {
                                        case 'institutional':
                                            echo '🏢';
                                            break;
                                        case 'ecommerce':
                                            echo '🛒';
                                            break;
                                        case 'blog':
                                            echo '📝';
                                            break;
                                        case 'landing':
                                            echo '🎯';
                                            break;
                                        default:
                                            echo '🌐';
                                    }
                                    ?>
                                </span>
                                <span class="card-title"><?php echo esc_html($type['name']); ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="futturu-form-actions">
                        <button type="button" class="futturu-btn futturu-btn-next" data-next="2">
                            <?php _e('Avançar', 'futturu-website-simulator'); ?> →
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Business Information -->
                <div class="futturu-step-content" data-step="2">
                    <h3><?php _e('Informações do Negócio', 'futturu-website-simulator'); ?></h3>
                    
                    <div class="futturu-form-grid">
                        <div class="futturu-form-group">
                            <label for="business_category"><?php _e('Categoria', 'futturu-website-simulator'); ?> *</label>
                            <select id="business_category" name="category" required>
                                <option value=""><?php _e('Selecione uma categoria', 'futturu-website-simulator'); ?></option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="futturu-form-group">
                            <label for="business_name"><?php _e('Nome do Negócio', 'futturu-website-simulator'); ?> *</label>
                            <input type="text" id="business_name" name="business_name" placeholder="<?php _e('Ex: Café do João', 'futturu-website-simulator'); ?>" required />
                        </div>
                        
                        <div class="futturu-form-group">
                            <label for="business_locality"><?php _e('Localidade', 'futturu-website-simulator'); ?> *</label>
                            <input type="text" id="business_locality" name="locality" placeholder="<?php _e('Ex: Belém, PA', 'futturu-website-simulator'); ?>" required />
                        </div>
                        
                        <div class="futturu-form-group futturu-full-width">
                            <label for="business_services"><?php _e('Serviços Oferecidos', 'futturu-website-simulator'); ?> *</label>
                            <textarea id="business_services" name="services" rows="2" placeholder="<?php _e('Ex: Cafés especiais, Bolos caseiros, Wi-Fi grátis', 'futturu-website-simulator'); ?>" required></textarea>
                        </div>
                        
                        <div class="futturu-form-group">
                            <label for="business_audience"><?php _e('Público-Alvo', 'futturu-website-simulator'); ?> *</label>
                            <input type="text" id="business_audience" name="audience" placeholder="<?php _e('Ex: Moradores do bairro, Trabalhadores', 'futturu-website-simulator'); ?>" required />
                        </div>
                        
                        <div class="futturu-form-group">
                            <label for="business_differential"><?php _e('Diferencial', 'futturu-website-simulator'); ?> *</label>
                            <input type="text" id="business_differential" name="differential" placeholder="<?php _e('Ex: Ingredientes frescos, Atendimento personalizado', 'futturu-website-simulator'); ?>" required />
                        </div>
                    </div>
                    
                    <!-- Generated Description Preview -->
                    <div class="futturu-generated-description">
                        <h4><?php _e('Descrição Gerada Automaticamente', 'futturu-website-simulator'); ?></h4>
                        <div id="generated-description-text" class="description-content">
                            <em><?php _e('Preencha os campos acima e clique em "Gerar Descrição" para criar seu texto...', 'futturu-website-simulator'); ?></em>
                        </div>
                        <button type="button" id="btn-generate-description" class="futturu-btn-generate">
                            <span class="dashicons dashicons-admin-media"></span>
                            <?php _e('Gerar Descrição', 'futturu-website-simulator'); ?>
                        </button>
                    </div>
                    
                    <!-- Website Preview Mockup -->
                    <div class="futturu-preview-section">
                        <h4><?php _e('Prévia do Seu Website', 'futturu-website-simulator'); ?></h4>
                        <div id="website-preview" class="website-preview-mockup">
                            <div class="preview-browser-bar">
                                <span class="browser-dot red"></span>
                                <span class="browser-dot yellow"></span>
                                <span class="browser-dot green"></span>
                                <span class="browser-url">www.seunegocio.com.br</span>
                            </div>
                            <div class="preview-content">
                                <div class="preview-hero">
                                    <h1 id="preview-business-name">Seu Negócio</h1>
                                    <p id="preview-description" class="preview-tagline">Sua descrição aparecerá aqui</p>
                                    <button class="preview-cta"><?php echo esc_html($cta_text); ?></button>
                                </div>
                                <div class="preview-sections">
                                    <div class="preview-services">
                                        <h3><?php _e('Nossos Serviços', 'futturu-website-simulator'); ?></h3>
                                        <p id="preview-services">Seus serviços serão exibidos aqui</p>
                                    </div>
                                    <div class="preview-about">
                                        <h3><?php _e('Sobre Nós', 'futturu-website-simulator'); ?></h3>
                                        <p id="preview-about">Informações sobre seu negócio</p>
                                    </div>
                                    <div class="preview-contact">
                                        <h3><?php _e('Entre em Contato', 'futturu-website-simulator'); ?></h3>
                                        <p><?php _e('Estamos prontos para atender você!', 'futturu-website-simulator'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="futturu-form-actions">
                        <button type="button" class="futturu-btn futturu-btn-back" data-prev="1">
                            ← <?php _e('Voltar', 'futturu-website-simulator'); ?>
                        </button>
                        <button type="button" class="futturu-btn futturu-btn-next" data-next="3">
                            <?php _e('Avançar', 'futturu-website-simulator'); ?> →
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Contact Form -->
                <div class="futturu-step-content" data-step="3">
                    <h3><?php _e('Finalize sua Solicitação', 'futturu-website-simulator'); ?></h3>
                    <p class="futturu-step-intro"><?php _e('Preencha seus dados para receber uma proposta personalizada baseada nas informações do seu negócio.', 'futturu-website-simulator'); ?></p>
                    
                    <div class="futturu-form-grid">
                        <div class="futturu-form-group">
                            <label for="contact_name"><?php _e('Nome Completo', 'futturu-website-simulator'); ?> *</label>
                            <input type="text" id="contact_name" name="full_name" required />
                        </div>
                        
                        <div class="futturu-form-group">
                            <label for="contact_phone"><?php _e('Telefone', 'futturu-website-simulator'); ?></label>
                            <input type="tel" id="contact_phone" name="phone" placeholder="+55 XX XXXXX-XXXX" />
                        </div>
                        
                        <div class="futturu-form-group futturu-full-width">
                            <label for="contact_email"><?php _e('E-mail', 'futturu-website-simulator'); ?> *</label>
                            <input type="email" id="contact_email" name="email" required />
                        </div>
                    </div>
                    
                    <!-- Summary of selections -->
                    <div class="futturu-summary">
                        <h4><?php _e('Resumo da Sua Solicitação', 'futturu-website-simulator'); ?></h4>
                        <ul id="summary-list">
                            <li><strong><?php _e('Tipo de Site:', 'futturu-website-simulator'); ?></strong> <span id="summary-site-type">-</span></li>
                            <li><strong><?php _e('Negócio:', 'futturu-website-simulator'); ?></strong> <span id="summary-business">-</span></li>
                            <li><strong><?php _e('Categoria:', 'futturu-website-simulator'); ?></strong> <span id="summary-category">-</span></li>
                            <li><strong><?php _e('Localidade:', 'futturu-website-simulator'); ?></strong> <span id="summary-locality">-</span></li>
                        </ul>
                    </div>
                    
                    <div id="futturu-message" class="futturu-message"></div>
                    
                    <div class="futturu-form-actions">
                        <button type="button" class="futturu-btn futturu-btn-back" data-prev="2">
                            ← <?php _e('Voltar', 'futturu-website-simulator'); ?>
                        </button>
                        <button type="submit" class="futturu-btn futturu-btn-submit">
                            <?php echo esc_html($cta_text); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function handle_send_lead() {
        check_ajax_referer('futturu_simulator_nonce', 'nonce');
        
        // Validate and sanitize input
        $site_type = sanitize_text_field($_POST['site_type'] ?? '');
        $business_name = sanitize_text_field($_POST['business_name'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        $locality = sanitize_text_field($_POST['locality'] ?? '');
        $services = sanitize_textarea_field($_POST['services'] ?? '');
        $audience = sanitize_text_field($_POST['audience'] ?? '');
        $differential = sanitize_text_field($_POST['differential'] ?? '');
        $generated_description = sanitize_textarea_field($_POST['generated_description'] ?? '');
        $full_name = sanitize_text_field($_POST['full_name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        
        // Validation
        $errors = array();
        if (empty($site_type)) $errors[] = 'site_type';
        if (empty($business_name)) $errors[] = 'business_name';
        if (empty($category)) $errors[] = 'category';
        if (empty($locality)) $errors[] = 'locality';
        if (empty($services)) $errors[] = 'services';
        if (empty($audience)) $errors[] = 'audience';
        if (empty($differential)) $errors[] = 'differential';
        if (empty($full_name)) $errors[] = 'full_name';
        if (empty($email) || !is_email($email)) $errors[] = 'email';
        
        if (!empty($errors)) {
            wp_send_json_error(array(
                'message' => __('Campos obrigatórios faltando', 'futturu-website-simulator'),
                'errors' => $errors
            ));
        }
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'futturu_leads';
        
        $preview_data = json_encode(array(
            'site_type' => $site_type,
            'business_name' => $business_name,
            'category' => $category,
            'locality' => $locality,
            'services' => $services,
            'audience' => $audience,
            'differential' => $differential,
            'generated_description' => $generated_description
        ));
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'site_type' => $site_type,
                'business_name' => $business_name,
                'category' => $category,
                'locality' => $locality,
                'services' => $services,
                'target_audience' => $audience,
                'differential' => $differential,
                'generated_description' => $generated_description,
                'full_name' => $full_name,
                'phone' => $phone,
                'email' => $email,
                'preview_data' => $preview_data
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array(
                'message' => __('Erro ao salvar dados', 'futturu-website-simulator')
            ));
        }
        
        // Send email
        $options = get_option('futturu_simulator_options', array());
        $to = isset($options['futturu_email_to']) ? $options['futturu_email_to'] : 'suporte@futturu.com.br';
        $subject = isset($options['futturu_email_subject']) ? $options['futturu_email_subject'] : 'Novo Lead - Simulador Futturu';
        
        $message = sprintf(
            __("Novo lead capturado pelo Simulador Futturu!\n\n" .
               "=== DADOS DO NEGÓCIO ===\n" .
               "Tipo de Site: %s\n" .
               "Nome do Negócio: %s\n" .
               "Categoria: %s\n" .
               "Localidade: %s\n" .
               "Serviços: %s\n" .
               "Público-Alvo: %s\n" .
               "Diferencial: %s\n" .
               "Descrição Gerada: %s\n\n" .
               "=== DADOS DE CONTATO ===\n" .
               "Nome: %s\n" .
               "Telefone: %s\n" .
               "E-mail: %s\n\n" .
               "=== AÇÃO NECESSÁRIA ===\n" .
               "Entrar em contato para proposta personalizada.",
               'futturu-website-simulator'),
            $site_type,
            $business_name,
            $category,
            $locality,
            $services,
            $audience,
            $differential,
            $generated_description,
            $full_name,
            $phone,
            $email
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        wp_mail($to, $subject, $message, $headers);
        
        wp_send_json_success(array(
            'message' => __('Obrigado! Sua solicitação foi enviada com sucesso. Entraremos em contato em breve.', 'futturu-website-simulator')
        ));
    }
}

// Initialize the plugin
Futturu_Website_Simulator::get_instance();
