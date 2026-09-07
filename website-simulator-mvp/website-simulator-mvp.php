<?php
/**
 * Plugin Name:       Website Simulator MVP
 * Plugin URI:        https://github.com/suportefutturu/FutturuCoder
 * Description:       Simulador de criação de websites para agências e profissionais.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Futturu Coder
 * Author URI:        https://futturu.com.br
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       website-simulator-mvp
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) exit;

define('WSMVP_VERSION', '1.0.0');
define('WSMVP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WSMVP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WSMVP_PREFIX', 'wsmvp_');
define('WSMVP_TEXT_DOMAIN', 'website-simulator-mvp');
define('WSMVP_TABLE_SIMULATIONS', 'wsmvp_simulations');

spl_autoload_register(function($class) {
    $prefix = 'WSMVP_';
    $base_dir = WSMVP_PLUGIN_DIR . 'includes/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

function wsmvp_init() {
    WSMVP_Core::get_instance();
    WSMVP_Database::get_instance();
    WSMVP_Settings::get_instance();
    WSMVP_Questions::get_instance();
    WSMVP_Pricing::get_instance();
    WSMVP_Leads::get_instance();
    if (!is_admin()) {
        WSMVP_Public::get_instance();
        WSMVP_Simulator::get_instance();
        WSMVP_Proposal::get_instance();
    } else {
        WSMVP_Admin::get_instance();
        WSMVP_Ajax::get_instance();
    }
}
add_action('plugins_loaded', 'wsmvp_init');

register_activation_hook(__FILE__, ['WSMVP_Database', 'activate']);
register_deactivation_hook(__FILE__, ['WSMVP_Database', 'deactivate']);
register_uninstall_hook(__FILE__, ['WSMVP_Database', 'uninstall']);
