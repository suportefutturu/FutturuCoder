<?php
/**
 * Frontend Handler Class
 * Handles shortcode rendering and form display
 * 
 * @package Futturu_Premium_Simulator
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Futturu_Premium_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        // Only enqueue on pages with the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'futturu_premium_simulator')) {
            wp_enqueue_style(
                'futturu-premium-css',
                FUTTURU_PREMIUM_PLUGIN_URL . 'assets/css/premium-simulator.css',
                array(),
                FUTTURU_PREMIUM_VERSION
            );
            
            wp_enqueue_script(
                'futturu-premium-js',
                FUTTURU_PREMIUM_PLUGIN_URL . 'assets/js/premium-simulator.js',
                array('jquery'),
                FUTTURU_PREMIUM_VERSION,
                true
            );
            
            wp_localize_script('futturu-premium-js', 'futturuPremium', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('futturu_premium_nonce'),
                'messages' => array(
                    'required' => __('Este campo é obrigatório', 'futturu-premium-simulator'),
                    'emailInvalid' => __('E-mail inválido', 'futturu-premium-simulator'),
                    'phoneInvalid' => __('Telefone/WhatsApp inválido', 'futturu-premium-simulator'),
                    'submitting' => __('Enviando...', 'futturu-premium-simulator'),
                    'success' => __('Simulação enviada com sucesso!', 'futturu-premium-simulator'),
                    'error' => __('Erro ao enviar. Tente novamente.', 'futturu-premium-simulator')
                )
            ));
        }
    }
    
    /**
     * Render the multi-step form
     * 
     * @return string HTML form
     */
    public function render_form() {
        ob_start();
        include FUTTURU_PREMIUM_PLUGIN_DIR . 'templates/premium-form.php';
        return ob_get_clean();
    }
}
