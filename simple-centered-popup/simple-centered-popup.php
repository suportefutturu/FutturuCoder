<?php
/**
 * Plugin Name: Simple Centered Popup
 * Plugin URI: https://example.com/simple-centered-popup
 * Description: A reusable, customizable modal popup plugin with advanced admin controls for design, colors, fonts, formats, cookie-based frequency settings, and accessibility features.
 * Version: 2.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-centered-popup
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Simple_Centered_Popup
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'SCP_VERSION', '2.0.0' );
define( 'SCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SCP_PLUGIN_INC', SCP_PLUGIN_DIR . 'includes/' );

/**
 * Main plugin class
 */
class Simple_Centered_Popup {

    /**
     * Single instance of the class
     *
     * @var Simple_Centered_Popup
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Simple_Centered_Popup
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_textdomain();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Include admin files
        if ( is_admin() ) {
            require_once SCP_PLUGIN_INC . 'class-admin-settings.php';
        }

        // Front-end hooks
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_popup' ) );

        // AJAX handlers
        add_action( 'wp_ajax_scp_dismiss_popup', array( $this, 'ajax_dismiss_popup' ) );
        add_action( 'wp_ajax_nopriv_scp_dismiss_popup', array( $this, 'ajax_dismiss_popup' ) );

        // Shortcode
        add_shortcode( 'sc_popup', array( $this, 'shortcode_render' ) );

        // Activation/Deactivation
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Load text domain for translations
     */
    private function load_textdomain() {
        load_plugin_textdomain(
            'simple-centered-popup',
            false,
            dirname( SCP_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Check if popup should be shown on current page
     *
     * @return bool
     */
    private function should_show_popup() {
        if ( ! get_option( 'scp_enabled', true ) ) {
            return false;
        }

        // Check visibility settings
        if ( is_front_page() && ! get_option( 'scp_show_on_homepage', true ) ) {
            return false;
        }

        if ( is_single() && ! get_option( 'scp_show_on_posts', true ) ) {
            return false;
        }

        if ( is_page() && ! get_option( 'scp_show_on_pages', true ) ) {
            return false;
        }

        if ( is_archive() && ! get_option( 'scp_show_on_archive', false ) ) {
            return false;
        }

        // Check excluded URLs
        $exclude_urls = get_option( 'scp_exclude_urls', '' );
        if ( ! empty( $exclude_urls ) ) {
            $current_url = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
            $exclude_list = array_filter( array_map( 'trim', explode( "\n", $exclude_urls ) ) );
            foreach ( $exclude_list as $exclude ) {
                if ( ! empty( $exclude ) && strpos( $current_url, $exclude ) !== false ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if ( ! $this->should_show_popup() ) {
            return;
        }

        wp_enqueue_style(
            'scp-style',
            SCP_PLUGIN_URL . 'assets/css/style.css',
            array(),
            SCP_VERSION
        );

        wp_enqueue_script(
            'scp-script',
            SCP_PLUGIN_URL . 'assets/js/script.js',
            array(),
            SCP_VERSION,
            true
        );

        // Get all design options
        $font_family = sanitize_text_field( get_option( 'scp_font_family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" ) );
        $title_font_size = sanitize_text_field( get_option( 'scp_title_font_size', '24px' ) );
        $title_font_weight = sanitize_text_field( get_option( 'scp_title_font_weight', '600' ) );
        $title_color = sanitize_hex_color( get_option( 'scp_title_color', '#333333' ) );
        $content_font_size = sanitize_text_field( get_option( 'scp_content_font_size', '16px' ) );
        $content_line_height = sanitize_text_field( get_option( 'scp_content_line_height', '1.6' ) );
        $content_color = sanitize_hex_color( get_option( 'scp_content_color', '#444444' ) );
        $button_color = sanitize_hex_color( get_option( 'scp_button_color', '#0073aa' ) );
        $button_text_color = sanitize_hex_color( get_option( 'scp_button_text_color', '#ffffff' ) );
        $button_hover_color = sanitize_hex_color( get_option( 'scp_button_hover_color', '#005a87' ) );
        $button_font_size = sanitize_text_field( get_option( 'scp_button_font_size', '16px' ) );
        $button_padding = sanitize_text_field( get_option( 'scp_button_padding', '12px 30px' ) );
        $max_height = sanitize_text_field( get_option( 'scp_max_height', '90vh' ) );
        $popup_padding = sanitize_text_field( get_option( 'scp_popup_padding', '30px' ) );
        $overlay_color = sanitize_hex_color( get_option( 'scp_overlay_color', '#000000' ) );
        $border_color = sanitize_hex_color( get_option( 'scp_border_color', '#e0e0e0' ) );
        $border_width = sanitize_text_field( get_option( 'scp_border_width', '1px' ) );
        $box_shadow = sanitize_text_field( get_option( 'scp_box_shadow', '0 10px 40px rgba(0, 0, 0, 0.3)' ) );
        $z_index = absint( get_option( 'scp_z_index', 999999 ) );
        $animation_easing = sanitize_text_field( get_option( 'scp_animation_easing', 'ease-out' ) );

        wp_localize_script( 'scp-script', 'scpConfig', array(
            'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
            'nonce'             => wp_create_nonce( 'scp_nonce' ),
            'autoOpen'          => get_option( 'scp_auto_open', true ),
            'delay'             => absint( get_option( 'scp_delay', 1000 ) ),
            'frequencyDays'     => absint( get_option( 'scp_frequency_days', 7 ) ),
            'cookieName'        => 'scp_popup_shown',
            'animation'         => sanitize_text_field( get_option( 'scp_animation', 'fade' ) ),
            'animationDuration' => sanitize_text_field( get_option( 'scp_animation_duration', '0.3s' ) ),
            'animationEasing'   => $animation_easing,
            'cssVars'           => array(
                '--scp-font-family'       => $font_family,
                '--scp-title-font-size'   => $title_font_size,
                '--scp-title-font-weight' => $title_font_weight,
                '--scp-title-color'       => $title_color,
                '--scp-content-font-size' => $content_font_size,
                '--scp-content-line-height' => $content_line_height,
                '--scp-content-color'     => $content_color,
                '--scp-button-color'      => $button_color,
                '--scp-button-text-color' => $button_text_color,
                '--scp-button-hover-color'=> $button_hover_color,
                '--scp-button-font-size'  => $button_font_size,
                '--scp-button-padding'    => $button_padding,
                '--scp-max-width'         => sanitize_text_field( get_option( 'scp_max_width', '600px' ) ),
                '--scp-max-height'        => $max_height,
                '--scp-popup-padding'     => $popup_padding,
                '--scp-overlay-opacity'   => floatval( get_option( 'scp_overlay_opacity', 0.7 ) ),
                '--scp-overlay-color'     => $overlay_color,
                '--scp-background-color'  => sanitize_hex_color( get_option( 'scp_background_color', '#ffffff' ) ),
                '--scp-border-color'      => $border_color,
                '--scp-border-width'      => $border_width,
                '--scp-border-radius'     => sanitize_text_field( get_option( 'scp_border_radius', '8px' ) ),
                '--scp-box-shadow'        => $box_shadow,
                '--scp-z-index'           => $z_index,
            ),
        ) );
    }

    /**
     * Render popup HTML
     */
    public function render_popup() {
        if ( ! $this->should_show_popup() ) {
            return;
        }

        $title              = sanitize_text_field( get_option( 'scp_title', '' ) );
        $content            = wp_kses_post( get_option( 'scp_content', '' ) );
        $image_url          = esc_url( get_option( 'scp_image_url', '' ) );
        $image_alt          = sanitize_text_field( get_option( 'scp_image_alt', '' ) );
        $video_embed        = wp_kses_post( get_option( 'scp_video_embed', '' ) );
        $button_text        = sanitize_text_field( get_option( 'scp_button_text', __( 'Close', 'simple-centered-popup' ) ) );
        $button_url         = esc_url( get_option( 'scp_button_url', '' ) );
        $button_new_tab     = get_option( 'scp_button_new_tab', false );
        $animation          = sanitize_text_field( get_option( 'scp_animation', 'fade' ) );

        include SCP_PLUGIN_DIR . 'templates/popup.php';
    }

    /**
     * AJAX handler for dismissing popup
     */
    public function ajax_dismiss_popup() {
        check_ajax_referer( 'scp_nonce', 'nonce' );
        wp_send_json_success();
    }

    /**
     * Shortcode handler
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function shortcode_render( $atts ) {
        $atts = shortcode_atts( array(
            'id' => '',
        ), $atts, 'sc_popup' );

        // For now, we only support one popup, so ID is ignored
        ob_start();
        $this->render_popup();
        return ob_get_clean();
    }

    /**
     * Activation hook
     */
    public function activate() {
        // Set default options if not exist
        if ( false === get_option( 'scp_enabled' ) ) {
            update_option( 'scp_enabled', true );
        }
        flush_rewrite_rules();
    }

    /**
     * Deactivation hook
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

/**
 * Public function to render popup programmatically
 *
 * @param string $id Popup ID (currently unused, reserved for future multi-popup support).
 */
function sc_popup_render( $id = '' ) {
    echo Simple_Centered_Popup::get_instance()->shortcode_render( array( 'id' => $id ) );
}

// Initialize plugin
Simple_Centered_Popup::get_instance();
