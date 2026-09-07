<?php
/**
 * Classe do admin
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Admin {
    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu() {
        add_menu_page('Website Simulator', 'Website Simulator', 'manage_options', 'wsmvp-dashboard', [$this, 'render_dashboard'], 'dashicons-admin-site-alt3', 58);
        add_submenu_page('wsmvp-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'wsmvp-dashboard', [$this, 'render_dashboard']);
        add_submenu_page('wsmvp-dashboard', 'Simulações', 'Simulações', 'manage_options', 'wsmvp-simulations', [$this, 'render_simulations']);
        add_submenu_page('wsmvp-dashboard', 'Configurações', 'Configurações', 'manage_options', 'wsmvp-settings', [$this, 'render_settings']);
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'wsmvp-') === false) return;
        wp_enqueue_style('wsmvp-admin', WSMVP_PLUGIN_URL . 'admin/css/admin.css', [], WSMVP_VERSION);
        wp_enqueue_script('wsmvp-admin', WSMVP_PLUGIN_URL . 'admin/js/admin.js', ['jquery'], WSMVP_VERSION, true);
        wp_localize_script('wsmvp-admin', 'wsmvp_admin', ['nonce' => WSMVP_Core::generate_nonce(), 'ajax_url' => admin_url('admin-ajax.php')]);
    }

    public function render_dashboard() {
        $stats = WSMVP_Leads::get_instance()->get_stats();
        include WSMVP_PLUGIN_DIR . 'admin/partials/dashboard.php';
    }

    public function render_simulations() {
        $data = WSMVP_Leads::get_instance()->get_simulations();
        include WSMVP_PLUGIN_DIR . 'admin/partials/simulations.php';
    }

    public function render_settings() {
        $settings = WSMVP_Settings::get_instance()->get_all();
        include WSMVP_PLUGIN_DIR . 'admin/partials/settings.php';
    }
}
