<?php
/**
 * Classe do simulador
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Simulator {
    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_nopriv_wsmvp_get_preview', [$this, 'get_preview']);
        add_action('wp_ajax_wsmvp_get_preview', [$this, 'get_preview']);
    }

    public function get_preview() {
        check_ajax_referer('public', 'nonce');
        $responses = $_POST['responses'] ?? [];
        $settings = WSMVP_Settings::get_instance();
        $primary = $responses['primary_color'] ?? $settings->get('primary_color');
        $secondary = $responses['secondary_color'] ?? $settings->get('secondary_color');
        
        $html = '<div class="wsmvp-preview" style="--preview-primary:' . esc_attr($primary) . ';--preview-secondary:' . esc_attr($secondary) . ';">';
        $html .= '<div class="wsmvp-preview-header"><h3>' . esc_html($responses['company_name'] ?? $settings->get('company_name')) . '</h3></div>';
        $html .= '<div class="wsmvp-preview-hero"><h2>' . esc_html($responses['main_title'] ?? 'Seu Site') . '</h2>';
        $html .= '<p>' . esc_html($responses['subtitle'] ?? 'Criado para você') . '</p></div>';
        
        if (!empty($responses['pages'])) {
            $html .= '<div class="wsmvp-preview-pages"><h4>Páginas:</h4><ul>';
            foreach ($responses['pages'] as $page) {
                $labels = ['pagina_inicial' => 'Início', 'sobre' => 'Sobre', 'contato' => 'Contato'];
                $html .= '<li>' . esc_html($labels[$page] ?? $page) . '</li>';
            }
            $html .= '</ul></div>';
        }
        $html .= '</div>';
        
        wp_send_json_success(['html' => $html]);
    }
}
