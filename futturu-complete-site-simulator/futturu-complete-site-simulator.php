<?php
/**
 * Plugin Name: Simulador Completo de Criação de Sites Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Gerador de orçamentos detalhados e personalizados para criação de websites. Captura leads qualificados e automatiza a coleta de informações essenciais.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-simulator
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants (with checks to prevent redefinition)
if (!defined('FUTTURU_SIMULATOR_VERSION')) {
    define('FUTTURU_SIMULATOR_VERSION', '1.0.0');
}

if (!defined('FUTTURU_SIMULATOR_PLUGIN_DIR')) {
    define('FUTTURU_SIMULATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('FUTTURU_SIMULATOR_PLUGIN_URL')) {
    define('FUTTURU_SIMULATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('FUTTURU_SIMULATOR_DB_VERSION')) {
    define('FUTTURU_SIMULATOR_DB_VERSION', '1.0');
}

// Include required files
require_once FUTTURU_SIMULATOR_PLUGIN_DIR . 'includes/class-database.php';
require_once FUTTURU_SIMULATOR_PLUGIN_DIR . 'includes/class-calculator.php';
require_once FUTTURU_SIMULATOR_PLUGIN_DIR . 'includes/class-email.php';
require_once FUTTURU_SIMULATOR_PLUGIN_DIR . 'includes/class-ajax-handler.php';
require_once FUTTURU_SIMULATOR_PLUGIN_DIR . 'admin/class-admin-settings.php';
require_once FUTTURU_SIMULATOR_PLUGIN_DIR . 'admin/class-admin-leads.php';

/**
 * Activation hook - must be defined before the class is used
 */
function futturu_simulator_activate() {
    Futturu_Database::activate();
}
register_activation_hook(__FILE__, 'futturu_simulator_activate');

/**
 * Deactivation hook
 */
function futturu_simulator_deactivate() {
    Futturu_Database::deactivate();
}
register_deactivation_hook(__FILE__, 'futturu_simulator_deactivate');

/**
 * Main Plugin Class
 */
class Futturu_Simulator_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_shortcode('futturu_site_simulator', array($this, 'render_simulator'));
        
        // Admin hooks
        if (is_admin()) {
            $admin_settings = new Futturu_Admin_Settings();
            $admin_leads = new Futturu_Admin_Leads();
        }
    }

    public function init() {
        load_plugin_textdomain('futturu-simulator', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize database
        Futturu_Database::init();
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'futturu-simulator-style',
            FUTTURU_SIMULATOR_PLUGIN_URL . 'assets/css/futturu-simulator.css',
            array(),
            FUTTURU_SIMULATOR_VERSION
        );

        wp_enqueue_script(
            'futturu-simulator-script',
            FUTTURU_SIMULATOR_PLUGIN_URL . 'assets/js/futturu-simulator.js',
            array('jquery'),
            FUTTURU_SIMULATOR_VERSION,
            true
        );

        wp_localize_script('futturu-simulator-script', 'futturuSimulator', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_simulator_nonce'),
            'strings' => array(
                'next' => __('Próximo', 'futturu-simulator'),
                'previous' => __('Anterior', 'futturu-simulator'),
                'submit' => __('Enviar Simulação para Análise', 'futturu-simulator'),
                'error' => __('Por favor, corrija os erros antes de continuar.', 'futturu-simulator'),
                'required' => __('Este campo é obrigatório.', 'futturu-simulator'),
                'invalidEmail' => __('E-mail inválido.', 'futturu-simulator'),
                'invalidPhone' => __('Telefone/WhatsApp inválido.', 'futturu-simulator'),
                'calculating' => __('Calculando...', 'futturu-simulator'),
                'sending' => __('Enviando...', 'futturu-simulator'),
                'success' => __('Simulação enviada com sucesso! Entraremos em contato em breve.', 'futturu-simulator'),
                'errorSubmit' => __('Erro ao enviar simulação. Tente novamente.', 'futturu-simulator'),
            )
        ));
    }

    public function render_simulator($atts) {
        ob_start();
        include FUTTURU_SIMULATOR_PLUGIN_DIR . 'templates/simulator-form.php';
        return ob_get_clean();
    }
}

// Initialize plugin
function futturu_simulator_init() {
    return Futturu_Simulator_Plugin::get_instance();
}
add_action('plugins_loaded', 'futturu_simulator_init');
