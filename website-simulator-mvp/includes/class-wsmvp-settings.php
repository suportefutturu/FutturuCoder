<?php
/**
 * Classe de configurações
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Settings {
    private static $instance = null;
    private $options = [];

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->options = [
            'company_name' => get_option('wsmvp_company_name', get_bloginfo('name')),
            'company_logo' => get_option('wsmvp_company_logo', ''),
            'primary_color' => get_option('wsmvp_primary_color', '#4361ee'),
            'secondary_color' => get_option('wsmvp_secondary_color', '#3f37c9'),
            'currency' => get_option('wsmvp_currency', 'R$'),
            'min_project_value' => get_option('wsmvp_min_project_value', '1000'),
            'welcome_text' => get_option('wsmvp_welcome_text', __('Crie seu site ideal em minutos!', WSMVP_TEXT_DOMAIN)),
            'cta_button_text' => get_option('wsmvp_cta_button_text', __('Solicitar Proposta', WSMVP_TEXT_DOMAIN)),
            'admin_email' => get_option('wsmvp_admin_email', get_option('admin_email')),
            'whatsapp_number' => get_option('wsmvp_whatsapp_number', ''),
            'enable_pdf' => get_option('wsmvp_enable_pdf', '1'),
            'show_public_estimate' => get_option('wsmvp_show_public_estimate', '1')
        ];
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('wsmvp_settings', 'wsmvp_company_name', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('wsmvp_settings', 'wsmvp_company_logo', ['sanitize_callback' => 'esc_url_raw']);
        register_setting('wsmvp_settings', 'wsmvp_primary_color', ['sanitize_callback' => [$this, 'sanitize_color']]);
        register_setting('wsmvp_settings', 'wsmvp_secondary_color', ['sanitize_callback' => [$this, 'sanitize_color']]);
        register_setting('wsmvp_settings', 'wsmvp_currency', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('wsmvp_settings', 'wsmvp_min_project_value', ['sanitize_callback' => 'floatval']);
        register_setting('wsmvp_settings', 'wsmvp_welcome_text', ['sanitize_callback' => 'wp_kses_post']);
        register_setting('wsmvp_settings', 'wsmvp_cta_button_text', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('wsmvp_settings', 'wsmvp_admin_email', ['sanitize_callback' => 'sanitize_email']);
        register_setting('wsmvp_settings', 'wsmvp_whatsapp_number', ['sanitize_callback' => 'sanitize_text_field']);
    }

    public function sanitize_color($color) {
        return preg_match('/^[a-f0-9]{3,6}$/i', ltrim($color, '#')) ? '#' . ltrim($color, '#') : '#4361ee';
    }

    public function get($key, $default = null) {
        return $this->options[$key] ?? $default;
    }

    public function get_all() {
        return $this->options;
    }

    public function format_currency($value) {
        return $this->get('currency') . ' ' . number_format(floatval($value), 2, ',', '.');
    }

    public function get_whatsapp_link($message = '') {
        $number = $this->get('whatsapp_number');
        if (empty($number)) return '';
        return "https://wa.me/{$number}?text=" . rawurlencode($message);
    }
}
