<?php
/**
 * Plugin Name: Tabelas Comparativas de Planos Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Exibe tabelas de comparação claras e atraentes para os principais serviços da Futturu: Criação de Websites, Hospedagem de Websites e Manutenção/Suporte de Websites.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-plan-tables
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FUTTURT_PLANS_VERSION', '1.0.0');
define('FUTTURT_PLANS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FUTTURT_PLANS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once FUTTURT_PLANS_PLUGIN_DIR . 'includes/class-futturu-plans-admin.php';
require_once FUTTURT_PLANS_PLUGIN_DIR . 'includes/class-futturu-plans-frontend.php';
require_once FUTTURT_PLANS_PLUGIN_DIR . 'includes/class-futturu-plans-settings.php';

// Initialize plugin
function futturu_plans_init() {
    // Load text domain
    load_plugin_textdomain('futturu-plan-tables', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize admin
    if (is_admin()) {
        new Futturu_Plans_Admin();
    }
    
    // Initialize frontend
    new Futturu_Plans_Frontend();
}
add_action('plugins_loaded', 'futturu_plans_init');

// Activation hook
register_activation_hook(__FILE__, 'futturu_plans_activate');
function futturu_plans_activate() {
    // Set default settings on activation
    Futturu_Plans_Settings::get_default_settings();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'futturu_plans_deactivate');
function futturu_plans_deactivate() {
    flush_rewrite_rules();
}
