<?php
/**
 * Classe de proposta
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Proposal {
    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {}

    public function generate_html($simulation) {
        $settings = WSMVP_Settings::get_instance();
        $pricing = WSMVP_Pricing::get_instance();
        $estimate = $pricing->calculate_estimate($simulation->responses);
        
        ob_start();
        include WSMVP_PLUGIN_DIR . 'public/templates/proposal.php';
        return ob_get_clean();
    }
}
