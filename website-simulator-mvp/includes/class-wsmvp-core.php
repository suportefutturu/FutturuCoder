<?php
/**
 * Classe núcleo do Website Simulator MVP
 */

if (!defined('ABSPATH')) exit;

class WSMVP_Core {
    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'load_textdomain']);
        add_filter('plugin_action_links_' . plugin_basename(WSMVP_PLUGIN_DIR . 'website-simulator-mvp.php'), [$this, 'add_plugin_action_links']);
        add_filter('body_class', [$this, 'add_body_classes']);
    }

    public function load_textdomain() {
        load_plugin_textdomain(WSMVP_TEXT_DOMAIN, false, WSMVP_PLUGIN_DIR . 'languages/');
    }

    public function add_plugin_action_links($links) {
        $links['wsmvp-dashboard'] = '<a href="' . admin_url('admin.php?page=wsmvp-dashboard') . '">Dashboard</a>';
        return $links;
    }

    public function add_body_classes($classes) {
        if (!is_admin()) $classes[] = 'wsmvp-active';
        return $classes;
    }

    public static function sanitize_array($array) {
        if (!is_array($array)) return sanitize_text_field($array);
        $sanitized = [];
        foreach ($array as $key => $value) {
            $sk = sanitize_key($key);
            $sanitized[$sk] = is_array($value) ? self::sanitize_array($value) : (is_numeric($value) ? $value : sanitize_text_field($value));
        }
        return $sanitized;
    }

    public static function generate_nonce($action = '') {
        return wp_create_nonce(WSMVP_PREFIX . $action);
    }

    public static function verify_nonce($nonce, $action = '') {
        return wp_verify_nonce($nonce, WSMVP_PREFIX . $action);
    }
}
