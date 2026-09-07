<?php
/**
 * Classe AJAX
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Ajax {
    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_wsmvp_save_simulation', [$this, 'save_simulation']);
        add_action('wp_ajax_nopriv_wsmvp_save_simulation', [$this, 'save_simulation']);
        add_action('wp_ajax_wsmvp_calculate_estimate', [$this, 'calculate_estimate']);
        add_action('wp_ajax_nopriv_wsmvp_calculate_estimate', [$this, 'calculate_estimate']);
    }

    private function verify_nonce($action = '') {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], WSMVP_PREFIX . $action)) {
            wp_send_json_error(['message' => __('Nonce inválido', WSMVP_TEXT_DOMAIN)], 403);
            exit;
        }
    }

    public function save_simulation() {
        $this->verify_nonce('save_simulation');
        $data = WSMVP_Core::sanitize_array($_POST['data'] ?? []);
        $result = WSMVP_Leads::get_instance()->save_simulation($data);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        } else {
            wp_send_json_success(['simulation_id' => $result['id'], 'estimate' => $result['estimate']]);
        }
    }

    public function calculate_estimate() {
        $this->verify_nonce('calculate_estimate');
        $responses = $_POST['responses'] ?? [];
        $estimate = WSMVP_Pricing::get_instance()->calculate_estimate($responses);
        wp_send_json_success(['estimate' => $estimate, 'text' => WSMVP_Pricing::get_instance()->get_estimate_text($estimate)]);
    }
}
