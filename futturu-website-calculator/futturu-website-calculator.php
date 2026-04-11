<?php
/**
 * Plugin Name: Calculadora de Criação de Website Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Calculadora interativa para orçamentos de websites com painel administrativo completo e personalização de cores/fontes.
 * Version: 2.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-calculator
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FUTTURU_CALC_VERSION', '2.0.0');
define('FUTTURU_CALC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FUTTURU_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));

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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_futturu_send_quote', array($this, 'handle_quote_submission'));
        add_action('wp_ajax_nopriv_futturu_send_quote', array($this, 'handle_quote_submission'));
        add_shortcode('futturu_calc', array($this, 'render_calculator'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Calculadora Futturu', 'futturu-calculator'),
            __('Calculadora Futturu', 'futturu-calculator'),
            'manage_options',
            'futturu-calculator',
            array($this, 'render_admin_page'),
            'dashicons-calculator',
            100
        );
    }
    
    public function register_settings() {
        // Website Types
        register_setting('futturu_calc_group', 'futturu_website_types', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_prices_array')
        ));
        
        // Complexity Levels
        register_setting('futturu_calc_group', 'futturu_complexity_levels', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_multipliers_array')
        ));
        
        // Page Price
        register_setting('futturu_calc_group', 'futturu_page_price', array(
            'type' => 'number',
            'sanitize_callback' => 'floatval'
        ));
        
        // Extras
        register_setting('futturu_calc_group', 'futturu_extras', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_prices_array')
        ));
        
        // Hosting Plans
        register_setting('futturu_calc_group', 'futturu_hosting_plans', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_prices_array')
        ));
        
        // Email Settings
        register_setting('futturu_calc_group', 'futturu_email_to', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email'
        ));
        
        register_setting('futturu_calc_group', 'futturu_email_subject', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        register_setting('futturu_calc_group', 'futturu_success_message', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));
        
        // Appearance Settings (NEW)
        register_setting('futturu_calc_group', 'futturu_primary_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color'
        ));
        
        register_setting('futturu_calc_group', 'futturu_secondary_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color'
        ));
        
        register_setting('futturu_calc_group', 'futturu_accent_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color'
        ));
        
        register_setting('futturu_calc_group', 'futturu_background_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color'
        ));
        
        register_setting('futturu_calc_group', 'futturu_text_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color'
        ));
        
        register_setting('futturu_calc_group', 'futturu_font_family', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        register_setting('futturu_calc_group', 'futturu_border_radius', array(
            'type' => 'number',
            'sanitize_callback' => 'absint'
        ));
        
        register_setting('futturu_calc_group', 'futturu_button_style', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));
    }
    
    public function sanitize_prices_array($input) {
        if (!is_array($input)) return array();
        $sanitized = array();
        foreach ($input as $key => $value) {
            $sanitized[sanitize_key($key)] = floatval($value);
        }
        return $sanitized;
    }
    
    public function sanitize_multipliers_array($input) {
        if (!is_array($input)) return array();
        $sanitized = array();
        foreach ($input as $key => $value) {
            $sanitized[sanitize_key($key)] = floatval(str_replace('%', '', $value)) / 100;
        }
        return $sanitized;
    }
    
    public function enqueue_assets() {
        wp_enqueue_style(
            'futturu-calc-style',
            FUTTURU_CALC_PLUGIN_URL . 'assets/css/style.css',
            array(),
            FUTTURU_CALC_VERSION
        );
        
        // Add inline CSS for custom colors
        $custom_css = $this->get_custom_css();
        wp_add_inline_style('futturu-calc-style', $custom_css);
        
        wp_enqueue_script(
            'futturu-calc-script',
            FUTTURU_CALC_PLUGIN_URL . 'assets/js/calculator.js',
            array('jquery'),
            FUTTURU_CALC_VERSION,
            true
        );
        
        wp_localize_script('futturu-calc-script', 'futturuCalcAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_calc_nonce')
        ));
    }
    
    private function get_custom_css() {
        $primary = get_option('futturu_primary_color', '#2563eb');
        $secondary = get_option('futturu_secondary_color', '#1e40af');
        $accent = get_option('futturu_accent_color', '#10b981');
        $background = get_option('futturu_background_color', '#ffffff');
        $text = get_option('futturu_text_color', '#1f2937');
        $font = get_option('futturu_font_family', "'Inter', system-ui, -apple-system, sans-serif");
        $radius = get_option('futturu_border_radius', 8);
        $button_style = get_option('futturu_button_style', 'solid');
        
        $button_gradient = '';
        if ($button_style === 'gradient') {
            $button_gradient = sprintf('
                background: linear-gradient(135deg, %s 0%%, %s 100%%);
                border: none;',
                esc_attr($primary),
                esc_attr($secondary)
            );
        } else {
            $button_gradient = sprintf('
                background-color: %s;
                border: 2px solid %s;',
                esc_attr($primary),
                esc_attr($primary)
            );
        }
        
        return "
            .futturu-calculator-wrapper {
                --futturu-primary: {$primary};
                --futturu-secondary: {$secondary};
                --futturu-accent: {$accent};
                --futturu-background: {$background};
                --futturu-text: {$text};
                --futturu-radius: {$radius}px;
                font-family: {$font};
            }
            
            .futturu-calculator-wrapper .futturu-btn-primary {
                {$button_gradient}
                color: #ffffff;
            }
            
            .futturu-calculator-wrapper .futturu-btn-primary:hover {
                background-color: {$secondary};
                border-color: {$secondary};
            }
            
            .futturu-calculator-wrapper .futturu-result-box {
                border-left: 4px solid {$accent};
            }
        ";
    }
    
    private function get_default_options() {
        return array(
            'website_types' => array(
                'institucional' => 2500,
                'blog' => 1800,
                'landing_page' => 1200,
                'hotsite' => 2000
            ),
            'complexity_levels' => array(
                'baixa' => 0,
                'media' => 0.25,
                'alta' => 0.50
            ),
            'page_price' => 150,
            'extras' => array(
                'contato_avancado' => 300,
                'api_externa' => 800,
                'catalogo' => 600,
                'blog_integrado' => 400,
                'area_membros' => 700,
                'chat_online' => 250,
                'redes_sociais' => 200,
                'seo_basico' => 350,
                'analytics' => 150,
                'multidioma' => 500
            ),
            'hosting_plans' => array(
                'basico' => 49,
                'profissional' => 99,
                'empresarial' => 199
            )
        );
    }
    
    public function render_admin_page() {
        $defaults = $this->get_default_options();
        
        $website_types = get_option('futturu_website_types', $defaults['website_types']);
        $complexity_levels = get_option('futturu_complexity_levels', $defaults['complexity_levels']);
        $page_price = get_option('futturu_page_price', $defaults['page_price']);
        $extras = get_option('futturu_extras', $defaults['extras']);
        $hosting_plans = get_option('futturu_hosting_plans', $defaults['hosting_plans']);
        $email_to = get_option('futturu_email_to', 'suporte@futturu.com.br');
        $email_subject = get_option('futturu_email_subject', 'Novo Orçamento - Calculadora Futturu');
        $success_message = get_option('futturu_success_message', 'Obrigado! Recebemos sua solicitação de orçamento. Entraremos em contato em até 24 horas úteis.');
        
        // Appearance settings
        $primary_color = get_option('futturu_primary_color', '#2563eb');
        $secondary_color = get_option('futturu_secondary_color', '#1e40af');
        $accent_color = get_option('futturu_accent_color', '#10b981');
        $background_color = get_option('futturu_background_color', '#ffffff');
        $text_color = get_option('futturu_text_color', '#1f2937');
        $font_family = get_option('futturu_font_family', "'Inter', system-ui, -apple-system, sans-serif");
        $border_radius = get_option('futturu_border_radius', 8);
        $button_style = get_option('futturu_button_style', 'solid');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Configurações da Calculadora Futturu', 'futturu-calculator'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('futturu_calc_group'); ?>
                <?php do_settings_sections('futturu_calc_group'); ?>
                
                <h2><?php _e('Valores dos Tipos de Website', 'futturu-calculator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($defaults['website_types'] as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="futturu_website_types[<?php echo esc_attr($key); ?>]" 
                                   value="<?php echo esc_attr($website_types[$key] ?? $value); ?>" class="regular-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Multiplicadores de Complexidade (%)', 'futturu-calculator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($defaults['complexity_levels'] as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="futturu_complexity_levels[<?php echo esc_attr($key); ?>]" 
                                   value="<?php echo esc_attr(round(($complexity_levels[$key] ?? $value) * 100, 2)); ?>" class="small-text"> %
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Valor por Página Adicional', 'futturu-calculator'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Preço por página (R$)', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="futturu_page_price" value="<?php echo esc_attr($page_price); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Extras e Aplicações', 'futturu-calculator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($defaults['extras'] as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="futturu_extras[<?php echo esc_attr($key); ?>]" 
                                   value="<?php echo esc_attr($extras[$key] ?? $value); ?>" class="regular-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Planos de Hospedagem (Mensal)', 'futturu-calculator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($defaults['hosting_plans'] as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="futturu_hosting_plans[<?php echo esc_attr($key); ?>]" 
                                   value="<?php echo esc_attr($hosting_plans[$key] ?? $value); ?>" class="regular-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Configurações de E-mail', 'futturu-calculator'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('E-mail de Destino', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="email" name="futturu_email_to" value="<?php echo esc_attr($email_to); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Assunto do E-mail', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="text" name="futturu_email_subject" value="<?php echo esc_attr($email_subject); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Mensagem de Sucesso', 'futturu-calculator'); ?></label></th>
                        <td>
                            <textarea name="futturu_success_message" rows="3" class="large-text"><?php echo esc_textarea($success_message); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Aparência e Personalização', 'futturu-calculator'); ?></h2>
                <p class="description"><?php _e('Personalize as cores, fontes e estilos da calculadora para combinar com a identidade visual da Futturu.', 'futturu-calculator'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Cor Primária', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="color" name="futturu_primary_color" value="<?php echo esc_attr($primary_color); ?>" class="futturu-color-picker">
                            <span class="description"><?php echo esc_html($primary_color); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Cor Secundária', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="color" name="futturu_secondary_color" value="<?php echo esc_attr($secondary_color); ?>" class="futturu-color-picker">
                            <span class="description"><?php echo esc_html($secondary_color); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Cor de Destaque', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="color" name="futturu_accent_color" value="<?php echo esc_attr($accent_color); ?>" class="futturu-color-picker">
                            <span class="description"><?php echo esc_html($accent_color); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Cor de Fundo', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="color" name="futturu_background_color" value="<?php echo esc_attr($background_color); ?>" class="futturu-color-picker">
                            <span class="description"><?php echo esc_html($background_color); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Cor do Texto', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="color" name="futturu_text_color" value="<?php echo esc_attr($text_color); ?>" class="futturu-color-picker">
                            <span class="description"><?php echo esc_html($text_color); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Fonte (Font Family)', 'futturu-calculator'); ?></label></th>
                        <td>
                            <select name="futturu_font_family" class="regular-text">
                                <option value="'Inter', system-ui, -apple-system, sans-serif" <?php selected($font_family, "'Inter', system-ui, -apple-system, sans-serif"); ?>>Inter (Padrão)</option>
                                <option value="'Roboto', Arial, sans-serif" <?php selected($font_family, "'Roboto', Arial, sans-serif"); ?>>Roboto</option>
                                <option value="'Open Sans', Arial, sans-serif" <?php selected($font_family, "'Open Sans', Arial, sans-serif"); ?>>Open Sans</option>
                                <option value="'Lato', Arial, sans-serif" <?php selected($font_family, "'Lato', Arial, sans-serif"); ?>>Lato</option>
                                <option value="'Montserrat', Arial, sans-serif" <?php selected($font_family, "'Montserrat', Arial, sans-serif"); ?>>Montserrat</option>
                                <option value="'Poppins', Arial, sans-serif" <?php selected($font_family, "'Poppins', Arial, sans-serif"); ?>>Poppins</option>
                                <option value="Georgia, serif" <?php selected($font_family, "Georgia, serif"); ?>>Georgia (Serif)</option>
                                <option value="'Courier New', monospace" <?php selected($font_family, "'Courier New', monospace"); ?>>Courier New (Monospace)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Arredondamento de Bordas (px)', 'futturu-calculator'); ?></label></th>
                        <td>
                            <input type="number" name="futturu_border_radius" value="<?php echo esc_attr($border_radius); ?>" min="0" max="50" step="1" class="small-text"> px
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Estilo do Botão', 'futturu-calculator'); ?></label></th>
                        <td>
                            <select name="futturu_button_style" class="regular-text">
                                <option value="solid" <?php selected($button_style, 'solid'); ?>><?php _e('Sólido', 'futturu-calculator'); ?></option>
                                <option value="gradient" <?php selected($button_style, 'gradient'); ?>><?php _e('Gradiente', 'futturu-calculator'); ?></option>
                                <option value="outline" <?php selected($button_style, 'outline'); ?>><?php _e('Contorno', 'futturu-calculator'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Salvar Configurações', 'futturu-calculator')); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Shortcode de Uso', 'futturu-calculator'); ?></h2>
            <p><?php _e('Para exibir a calculadora em qualquer página ou post, utilize o shortcode:', 'futturu-calculator'); ?></p>
            <code style="background: #f0f0f1; padding: 10px 15px; display: inline-block; font-size: 14px;">[futturu_calc]</code>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.futturu-color-picker').on('input change', function() {
                $(this).next('.description').text($(this).val());
            });
        });
        </script>
        <?php
    }
    
    public function render_calculator() {
        ob_start();
        
        $website_types = get_option('futturu_website_types', $this->get_default_options()['website_types']);
        $complexity_levels = get_option('futturu_complexity_levels', $this->get_default_options()['complexity_levels']);
        $extras = get_option('futturu_extras', $this->get_default_options()['extras']);
        $hosting_plans = get_option('futturu_hosting_plans', $this->get_default_options()['hosting_plans']);
        
        ?>
        <div class="futturu-calculator-wrapper">
            <div class="futturu-calculator-header">
                <h2><?php _e('Calculadora de Website Futturu', 'futturu-calculator'); ?></h2>
                <p><?php _e('Descubra o investimento ideal para o seu projeto digital. Preencha as opções abaixo e receba uma estimativa personalizada.', 'futturu-calculator'); ?></p>
            </div>
            
            <form id="futturu-calculator-form" class="futturu-calculator-form">
                <!-- Tipo de Website -->
                <div class="futturu-form-section">
                    <h3><?php _e('1. Tipo de Website', 'futturu-calculator'); ?></h3>
                    <select name="website_type" id="website_type" class="futturu-input" required>
                        <option value=""><?php _e('Selecione o tipo...', 'futturu-calculator'); ?></option>
                        <?php foreach ($website_types as $key => $price): ?>
                        <option value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($price); ?>">
                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?> - R$ <?php echo number_format($price, 2, ',', '.'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Complexidade -->
                <div class="futturu-form-section">
                    <h3><?php _e('2. Nível de Complexidade', 'futturu-calculator'); ?></h3>
                    <select name="complexity_level" id="complexity_level" class="futturu-input" required>
                        <option value=""><?php _e('Selecione a complexidade...', 'futturu-calculator'); ?></option>
                        <?php foreach ($complexity_levels as $key => $multiplier): ?>
                        <option value="<?php echo esc_attr($key); ?>" data-multiplier="<?php echo esc_attr($multiplier); ?>">
                            <?php 
                            $labels = array(
                                'baixa' => __('Baixa - Layout padrão', 'futturu-calculator'),
                                'media' => __('Média - Personalizações básicas', 'futturu-calculator'),
                                'alta' => __('Alta - Layout exclusivo e funcionalidades avançadas', 'futturu-calculator')
                            );
                            echo esc_html($labels[$key] ?? ucfirst(str_replace('_', ' ', $key)));
                            ?> (<?php echo esc_html(round($multiplier * 100)); ?>% adicional)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Número de Páginas -->
                <div class="futturu-form-section">
                    <h3><?php _e('3. Número de Páginas', 'futturu-calculator'); ?></h3>
                    <p class="futturu-description"><?php _e('Quantas páginas internas seu site terá? (Home já inclusa)', 'futturu-calculator'); ?></p>
                    <input type="number" name="num_pages" id="num_pages" min="0" max="100" value="0" class="futturu-input" required>
                </div>
                
                <!-- Extras -->
                <div class="futturu-form-section">
                    <h3><?php _e('4. Aplicações, Plugins e Extras', 'futturu-calculator'); ?></h3>
                    <p class="futturu-description"><?php _e('Selecione os adicionais que deseja incluir:', 'futturu-calculator'); ?></p>
                    <div class="futturu-checkboxes-grid">
                        <?php foreach ($extras as $key => $price): ?>
                        <label class="futturu-checkbox-label">
                            <input type="checkbox" name="extras[]" value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($price); ?>" class="futturu-checkbox">
                            <span class="futturu-checkbox-text">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>
                                <span class="futturu-checkbox-price">+ R$ <?php echo number_format($price, 2, ',', '.'); ?></span>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Hospedagem -->
                <div class="futturu-form-section">
                    <h3><?php _e('5. Plano de Hospedagem Cloud (Opcional)', 'futturu-calculator'); ?></h3>
                    <p class="futturu-description"><?php _e('Este valor é mensal e não incluso no total do desenvolvimento.', 'futturu-calculator'); ?></p>
                    <select name="hosting_plan" id="hosting_plan" class="futturu-input">
                        <option value=""><?php _e('Não necessito de hospedagem agora', 'futturu-calculator'); ?></option>
                        <?php foreach ($hosting_plans as $key => $price): ?>
                        <option value="<?php echo esc_attr($key); ?>" data-price="<?php echo esc_attr($price); ?>">
                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?> - R$ <?php echo number_format($price, 2, ',', '.'); ?>/mês
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Resultado -->
                <div class="futturu-result-section">
                    <div class="futturu-result-box">
                        <h3><?php _e('Previsão de Investimento Aproximado', 'futturu-calculator'); ?></h3>
                        <div class="futturu-result-value">
                            <span class="futturu-currency">R$</span>
                            <span id="total_estimate">0,00</span>
                        </div>
                        <div class="futturu-result-breakdown">
                            <p><strong><?php _e('Desenvolvimento:', 'futturu-calculator'); ?></strong> <span id="development_total">R$ 0,00</span></p>
                            <p id="hosting_row" style="display:none;"><strong><?php _e('Hospedagem Mensal:', 'futturu-calculator'); ?></strong> <span id="hosting_total">R$ 0,00</span>/mês</p>
                        </div>
                        <p class="futturu-disclaimer"><?php _e('* Esta é uma estimativa inicial. O valor final pode variar mediante análise detalhada do projeto.', 'futturu-calculator'); ?></p>
                    </div>
                </div>
                
                <!-- Formulário de Contato -->
                <div class="futturu-contact-section">
                    <h3><?php _e('Pronto! Preencha seus dados e entraremos em contato com uma proposta detalhada.', 'futturu-calculator'); ?></h3>
                    
                    <div class="futturu-form-row">
                        <div class="futturu-form-group">
                            <label for="contact_name"><?php _e('Nome Completo *', 'futturu-calculator'); ?></label>
                            <input type="text" name="contact_name" id="contact_name" class="futturu-input" required>
                        </div>
                        <div class="futturu-form-group">
                            <label for="contact_email"><?php _e('E-mail *', 'futturu-calculator'); ?></label>
                            <input type="email" name="contact_email" id="contact_email" class="futturu-input" required>
                        </div>
                    </div>
                    
                    <div class="futturu-form-row">
                        <div class="futturu-form-group">
                            <label for="contact_phone"><?php _e('Telefone/WhatsApp *', 'futturu-calculator'); ?></label>
                            <input type="tel" name="contact_phone" id="contact_phone" class="futturu-input" required>
                        </div>
                        <div class="futturu-form-group">
                            <label for="contact_company"><?php _e('Empresa', 'futturu-calculator'); ?></label>
                            <input type="text" name="contact_company" id="contact_company" class="futturu-input">
                        </div>
                    </div>
                    
                    <div class="futturu-form-group">
                        <label for="contact_message"><?php _e('Mensagem (Opcional)', 'futturu-calculator'); ?></label>
                        <textarea name="contact_message" id="contact_message" rows="4" class="futturu-input"></textarea>
                    </div>
                    
                    <div id="futturu-message-container"></div>
                    
                    <button type="submit" class="futturu-btn-primary" id="futturu-submit-btn">
                        <span class="btn-text"><?php _e('Enviar Solicitação de Orçamento', 'futturu-calculator'); ?></span>
                        <span class="btn-loading" style="display:none;"><?php _e('Enviando...', 'futturu-calculator'); ?></span>
                    </button>
                </div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    public function handle_quote_submission() {
        check_ajax_referer('futturu_calc_nonce', 'nonce');
        
        $required_fields = array('website_type', 'complexity_level', 'num_pages', 'contact_name', 'contact_email', 'contact_phone');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => sprintf(__('Campo %s é obrigatório.', 'futturu-calculator'), $field)));
            }
        }
        
        $website_type = sanitize_text_field($_POST['website_type']);
        $complexity_level = sanitize_text_field($_POST['complexity_level']);
        $num_pages = intval($_POST['num_pages']);
        $extras = isset($_POST['extras']) ? array_map('sanitize_text_field', $_POST['extras']) : array();
        $hosting_plan = sanitize_text_field($_POST['hosting_plan']);
        
        $contact_name = sanitize_text_field($_POST['contact_name']);
        $contact_email = sanitize_email($_POST['contact_email']);
        $contact_phone = sanitize_text_field($_POST['contact_phone']);
        $contact_company = sanitize_text_field($_POST['contact_company']);
        $contact_message = sanitize_textarea_field($_POST['contact_message']);
        
        // Calculate totals
        $defaults = $this->get_default_options();
        $website_types = get_option('futturu_website_types', $defaults['website_types']);
        $complexity_levels = get_option('futturu_complexity_levels', $defaults['complexity_levels']);
        $page_price = get_option('futturu_page_price', $defaults['page_price']);
        $extras_prices = get_option('futturu_extras', $defaults['extras']);
        $hosting_plans = get_option('futturu_hosting_plans', $defaults['hosting_plans']);
        
        $base_price = $website_types[$website_type] ?? 0;
        $multiplier = $complexity_levels[$complexity_level] ?? 0;
        $pages_total = $num_pages * $page_price;
        $extras_total = 0;
        foreach ($extras as $extra) {
            $extras_total += $extras_prices[$extra] ?? 0;
        }
        
        $subtotal = $base_price + $pages_total + $extras_total;
        $complexity_additional = $subtotal * $multiplier;
        $development_total = $subtotal + $complexity_additional;
        
        $hosting_monthly = 0;
        if ($hosting_plan && isset($hosting_plans[$hosting_plan])) {
            $hosting_monthly = $hosting_plans[$hosting_plan];
        }
        
        // Prepare email content
        $to = get_option('futturu_email_to', 'suporte@futturu.com.br');
        $subject = get_option('futturu_email_subject', 'Novo Orçamento - Calculadora Futturu');
        
        $labels = array(
            'website_types' => array(
                'institucional' => 'Website Institucional',
                'blog' => 'Blog',
                'landing_page' => 'Landing Page',
                'hotsite' => 'Hotsite'
            ),
            'complexity_levels' => array(
                'baixa' => 'Baixa (Layout padrão)',
                'media' => 'Média (Personalizações básicas)',
                'alta' => 'Alta (Layout exclusivo)'
            ),
            'extras' => array(
                'contato_avancado' => 'Formulário de Contato Avançado',
                'api_externa' => 'Integração com API Externa',
                'catalogo' => 'Catálogo de Produtos Estático',
                'blog_integrado' => 'Blog Integrado',
                'area_membros' => 'Área de Membros Simples',
                'chat_online' => 'Chat Online',
                'redes_sociais' => 'Integração com Redes Sociais',
                'seo_basico' => 'SEO Básico',
                'analytics' => 'Google Analytics',
                'multidioma' => 'Multi-idioma'
            ),
            'hosting_plans' => array(
                'basico' => 'Básico',
                'profissional' => 'Profissional',
                'empresarial' => 'Empresarial'
            )
        );
        
        $extras_list = '';
        if (!empty($extras)) {
            foreach ($extras as $extra) {
                $extras_list .= '- ' . ($labels['extras'][$extra] ?? ucfirst(str_replace('_', ' ', $extra))) . "\n";
            }
        } else {
            $extras_list = '- Nenhum selecionado';
        }
        
        $message = sprintf(
            __("Novo Orçamento Solicitado via Calculadora Futturu\n\n" .
            "DADOS DO CLIENTE:\n" .
            "Nome: %s\n" .
            "E-mail: %s\n" .
            "Telefone/WhatsApp: %s\n" .
            "Empresa: %s\n" .
            "Mensagem: %s\n\n" .
            "DETALHES DO PROJETO:\n" .
            "Tipo de Website: %s\n" .
            "Nível de Complexidade: %s\n" .
            "Número de Páginas: %d\n" .
            "Extras Selecionados:\n%s\n" .
            "Plano de Hospedagem: %s\n\n" .
            "VALORES ESTIMADOS:\n" .
            "Investimento em Desenvolvimento: R$ %.2f\n" .
            "Custo Mensal de Hospedagem: R$ %.2f\n\n" .
            "Enviado em: %s\n" .
            "IP: %s", 'futturu-calculator'),
            $contact_name,
            $contact_email,
            $contact_phone,
            !empty($contact_company) ? $contact_company : 'Não informada',
            !empty($contact_message) ? $contact_message : 'Nenhuma mensagem adicional',
            $labels['website_types'][$website_type] ?? ucfirst(str_replace('_', ' ', $website_type)),
            $labels['complexity_levels'][$complexity_level] ?? ucfirst(str_replace('_', ' ', $complexity_level)),
            $num_pages,
            $extras_list,
            $hosting_plan ? ($labels['hosting_plans'][$hosting_plan] ?? ucfirst(str_replace('_', ' ', $hosting_plan))) : 'Não selecionado',
            $development_total,
            $hosting_monthly,
            current_time('mysql'),
            $_SERVER['REMOTE_ADDR'] ?? 'N/A'
        );
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $contact_name . ' <' . $contact_email . '>'
        );
        
        if (wp_mail($to, $subject, $message, $headers)) {
            $success_msg = get_option('futturu_success_message', 'Obrigado! Recebemos sua solicitação.');
            wp_send_json_success(array(
                'message' => $success_msg,
                'development_total' => number_format($development_total, 2, ',', '.'),
                'hosting_monthly' => number_format($hosting_monthly, 2, ',', '.')
            ));
        } else {
            wp_send_json_error(array('message' => __('Erro ao enviar e-mail. Por favor, tente novamente ou entre em contato diretamente.', 'futturu-calculator')));
        }
    }
}

// Initialize plugin
function futturu_website_calculator_init() {
    return Futturu_Website_Calculator::get_instance();
}
add_action('plugins_loaded', 'futturu_website_calculator_init');
