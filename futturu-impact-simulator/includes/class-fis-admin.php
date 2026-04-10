<?php
/**
 * Admin class for Futturu Impact Simulator
 */
if (!defined('ABSPATH')) {
    exit;
}

class FIS_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Simulador Impacto Futturu', 'futturu-impact-simulator'),
            __('Simulador Impacto Futturu', 'futturu-impact-simulator'),
            'manage_options',
            'futturu-impact-simulator',
            array($this, 'render_admin_page'),
            'dashicons-chart-line',
            30
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('fis_settings_group', 'fis_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
        
        // Settings sections
        add_settings_section(
            'fis_general_section',
            __('Configurações Gerais', 'futturu-impact-simulator'),
            array($this, 'render_general_section'),
            'futturu-impact-simulator'
        );
        
        add_settings_section(
            'fis_benchmarks_section',
            __('Matriz de Benchmarks', 'futturu-impact-simulator'),
            array($this, 'render_benchmarks_section'),
            'futturu-impact-simulator'
        );
        
        add_settings_section(
            'fis_messages_section',
            __('Mensagens e Textos', 'futturu-impact-simulator'),
            array($this, 'render_messages_section'),
            'futturu-impact-simulator'
        );
        
        add_settings_section(
            'fis_cta_section',
            __('Configurações do CTA', 'futturu-impact-simulator'),
            array($this, 'render_cta_section'),
            'futturu-impact-simulator'
        );
        
        // General settings fields
        add_settings_field(
            'fis_enabled',
            __('Ativar Plugin', 'futturu-impact-simulator'),
            array($this, 'render_enabled_field'),
            'futturu-impact-simulator',
            'fis_general_section'
        );
        
        add_settings_field(
            'fis_shortcode',
            __('Shortcode', 'futturu-impact-simulator'),
            array($this, 'render_shortcode_field'),
            'futturu-impact-simulator',
            'fis_general_section'
        );
        
        add_settings_field(
            'fis_disclaimer',
            __('Aviso Legal', 'futturu-impact-simulator'),
            array($this, 'render_disclaimer_field'),
            'futturu-impact-simulator',
            'fis_general_section'
        );
        
        // CTA fields
        add_settings_field(
            'fis_cta_text',
            __('Texto do Botão CTA', 'futturu-impact-simulator'),
            array($this, 'render_cta_text_field'),
            'futturu-impact-simulator',
            'fis_cta_section'
        );
        
        add_settings_field(
            'fis_cta_email',
            __('Email de Destino', 'futturu-impact-simulator'),
            array($this, 'render_cta_email_field'),
            'futturu-impact-simulator',
            'fis_cta_section'
        );
        
        // Messages fields
        $messages = fis_get_default_messages();
        foreach ($messages as $key => $default_value) {
            add_settings_field(
                'fis_msg_' . $key,
                __(ucwords(str_replace('_', ' ', $key)), 'futturu-impact-simulator'),
                array($this, 'render_message_field'),
                'futturu-impact-simulator',
                'fis_messages_section',
                array('key' => $key, 'default' => $default_value)
            );
        }
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['fis_enabled'] = isset($input['fis_enabled']) ? true : false;
        $sanitized['fis_shortcode'] = sanitize_text_field($input['fis_shortcode']);
        $sanitized['fis_cta_text'] = sanitize_text_field($input['fis_cta_text']);
        $sanitized['fis_cta_email'] = sanitize_email($input['fis_cta_email']);
        $sanitized['fis_disclaimer'] = sanitize_textarea_field($input['fis_disclaimer']);
        
        // Sanitize messages
        if (isset($input['messages'])) {
            $sanitized['messages'] = array();
            foreach ($input['messages'] as $key => $value) {
                $sanitized['messages'][$key] = sanitize_text_field($value);
            }
        }
        
        // Sanitize benchmarks
        if (isset($input['benchmarks'])) {
            $sanitized['benchmarks'] = $input['benchmarks']; // Already validated in render
        }
        
        return $sanitized;
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('fis_settings_group');
                do_settings_sections('futturu-impact-simulator');
                submit_button(__('Salvar Configurações', 'futturu-impact-simulator'));
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render sections
     */
    public function render_general_section() {
        echo '<p>' . __('Configure as opções gerais do simulador.', 'futturu-impact-simulator') . '</p>';
    }
    
    public function render_benchmarks_section() {
        echo '<p>' . __('Configure a matriz de benchmarks para cálculos de impacto. (Edição avançada via código ou export/import)', 'futturu-impact-simulator') . '</p>';
    }
    
    public function render_messages_section() {
        echo '<p>' . __('Personalize as mensagens exibidas no simulador.', 'futturu-impact-simulator') . '</p>';
    }
    
    public function render_cta_section() {
        echo '<p>' . __('Configure a chamada para ação final.', 'futturu-impact-simulator') . '</p>';
    }
    
    /**
     * Render fields
     */
    public function render_enabled_field() {
        $options = get_option('fis_settings');
        $enabled = isset($options['fis_enabled']) ? $options['fis_enabled'] : true;
        ?>
        <label>
            <input type="checkbox" name="fis_settings[fis_enabled]" value="1" <?php checked($enabled, true); ?>>
            <?php _e('Ativar simulador', 'futturu-impact-simulator'); ?>
        </label>
        <?php
    }
    
    public function render_shortcode_field() {
        $options = get_option('fis_settings');
        $shortcode = isset($options['fis_shortcode']) ? $options['fis_shortcode'] : '[futturu_impact_simulator]';
        ?>
        <code><?php echo esc_html($shortcode); ?></code>
        <p class="description"><?php _e('Use este shortcode em qualquer página ou post.', 'futturu-impact-simulator'); ?></p>
        <?php
    }
    
    public function render_disclaimer_field() {
        $options = get_option('fis_settings');
        $disclaimer = isset($options['fis_disclaimer']) ? $options['fis_disclaimer'] : fis_get_default_messages()['disclaimer'];
        ?>
        <textarea name="fis_settings[fis_disclaimer]" rows="3" class="large-text"><?php echo esc_textarea($disclaimer); ?></textarea>
        <?php
    }
    
    public function render_cta_text_field() {
        $options = get_option('fis_settings');
        $cta_text = isset($options['fis_cta_text']) ? $options['fis_cta_text'] : 'Falar com um Especialista da Futturu';
        ?>
        <input type="text" name="fis_settings[fis_cta_text]" value="<?php echo esc_attr($cta_text); ?>" class="regular-text">
        <?php
    }
    
    public function render_cta_email_field() {
        $options = get_option('fis_settings');
        $cta_email = isset($options['fis_cta_email']) ? $options['fis_cta_email'] : 'suporte@futturu.com.br';
        ?>
        <input type="email" name="fis_settings[fis_cta_email]" value="<?php echo esc_attr($cta_email); ?>" class="regular-text">
        <?php
    }
    
    public function render_message_field($args) {
        $options = get_option('fis_settings');
        $messages = isset($options['messages']) ? $options['messages'] : fis_get_default_messages();
        $value = isset($messages[$args['key']]) ? $messages[$args['key']] : $args['default'];
        ?>
        <input type="text" name="fis_settings[messages][<?php echo esc_attr($args['key']); ?>]" value="<?php echo esc_attr($value); ?>" class="large-text">
        <?php
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_futturu-impact-simulator') {
            return;
        }
        
        wp_enqueue_style('fis-admin-css', FIS_PLUGIN_URL . 'assets/css/admin.css', array(), FIS_VERSION);
    }
}
