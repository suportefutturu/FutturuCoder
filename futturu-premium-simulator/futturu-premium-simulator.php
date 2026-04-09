<?php
/**
 * Plugin Name: Simulador Premium de Criação de Websites Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Captura leads qualificados através de um formulário multipasso que simula a criação de um site, calcula orçamentos internos e envia para a equipe de vendas.
 * Version: 1.0.0
 * Author: Futturu Development Team
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-premium-simulator
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FUTTURU_PREMIUM_VERSION', '1.0.0');
define('FUTTURU_PREMIUM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FUTTURU_PREMIUM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FUTTURU_PREMIUM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class - Singleton Pattern
 */
final class Futturu_Premium_Simulator {
    
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
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init_classes'));
        add_shortcode('futturu_premium_simulator', array($this, 'render_shortcode'));
    }
    
    public function activate() {
        // Include database class for table creation
        require_once FUTTURU_PREMIUM_PLUGIN_DIR . 'includes/class-premium-database.php';
        Futturu_Premium_Database::create_table();
        
        // Set default options
        add_option('futturu_premium_active', true);
        add_option('futturu_premium_email_destination', 'suporte@futturu.com.br');
        
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('futturu-premium-simulator', false, dirname(FUTTURU_PREMIUM_PLUGIN_BASENAME) . '/languages');
    }
    
    public function init_classes() {
        // Load all required classes
        require_once FUTTURU_PREMIUM_PLUGIN_DIR . 'includes/class-premium-database.php';
        require_once FUTTURU_PREMIUM_PLUGIN_DIR . 'includes/class-premium-admin.php';
        require_once FUTTURU_PREMIUM_PLUGIN_DIR . 'includes/class-premium-frontend.php';
        require_once FUTTURU_PREMIUM_PLUGIN_DIR . 'includes/class-premium-ajax-handler.php';
        
        // Initialize classes
        Futturu_Premium_Database::get_instance();
        Futturu_Premium_Admin::get_instance();
        Futturu_Premium_Frontend::get_instance();
        Futturu_Premium_Ajax_Handler::get_instance();
    }
    
    public function render_shortcode($atts) {
        // Check if plugin is active
        if (!get_option('futturu_premium_active', true)) {
            return '<p class="futturu-simulator-disabled">Simulador temporariamente indisponível.</p>';
        }
        
        return Futturu_Premium_Frontend::get_instance()->render_form();
    }
}

// Initialize the plugin
function futturu_premium_init() {
    return Futturu_Premium_Simulator::get_instance();
}

futturu_premium_init();
