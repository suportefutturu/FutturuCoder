<?php
/**
 * Plugin Name: Simple Centered Popup
 * Plugin URI: https://example.com/simple-centered-popup
 * Description: A reusable, customizable modal popup plugin with admin controls, cookie-based frequency settings, and accessibility features.
 * Version: 1.0.0
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
define( 'SCP_VERSION', '1.0.0' );
define( 'SCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

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
        // Admin hooks
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

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
     * Register admin menu
     */
    public function register_admin_menu() {
        add_menu_page(
            __( 'Popup Settings', 'simple-centered-popup' ),
            __( 'Simple Popup', 'simple-centered-popup' ),
            'manage_options',
            'simple-centered-popup',
            array( $this, 'render_admin_page' ),
            'dashicons-megaphone',
            100
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'scp_settings_group', 'scp_enabled', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );

        register_setting( 'scp_settings_group', 'scp_title', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => __( 'Welcome!', 'simple-centered-popup' ),
        ) );

        register_setting( 'scp_settings_group', 'scp_content', array(
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default'           => '',
        ) );

        register_setting( 'scp_settings_group', 'scp_image_url', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );

        register_setting( 'scp_settings_group', 'scp_video_embed', array(
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default'           => '',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_text', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => __( 'Close', 'simple-centered-popup' ),
        ) );

        register_setting( 'scp_settings_group', 'scp_button_url', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_new_tab', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ) );

        register_setting( 'scp_settings_group', 'scp_auto_open', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );

        register_setting( 'scp_settings_group', 'scp_delay', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 1000,
        ) );

        register_setting( 'scp_settings_group', 'scp_frequency_days', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 7,
        ) );

        register_setting( 'scp_settings_group', 'scp_max_width', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '600px',
        ) );

        register_setting( 'scp_settings_group', 'scp_overlay_opacity', array(
            'type'              => 'number',
            'sanitize_callback' => 'floatval',
            'default'           => 0.7,
        ) );

        register_setting( 'scp_settings_group', 'scp_background_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#ffffff',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#0073aa',
        ) );

        register_setting( 'scp_settings_group', 'scp_border_radius', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '8px',
        ) );

        register_setting( 'scp_settings_group', 'scp_animation', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'fade',
        ) );

        register_setting( 'scp_settings_group', 'scp_animation_duration', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0.3s',
        ) );

        register_setting( 'scp_settings_group', 'scp_show_on_homepage', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );

        register_setting( 'scp_settings_group', 'scp_show_on_posts', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );

        register_setting( 'scp_settings_group', 'scp_show_on_pages', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );

        add_settings_section(
            'scp_general_section',
            __( 'General Settings', 'simple-centered-popup' ),
            array( $this, 'render_general_section' ),
            'simple-centered-popup'
        );

        add_settings_section(
            'scp_content_section',
            __( 'Content Settings', 'simple-centered-popup' ),
            array( $this, 'render_content_section' ),
            'simple-centered-popup'
        );

        add_settings_section(
            'scp_behavior_section',
            __( 'Behavior Settings', 'simple-centered-popup' ),
            array( $this, 'render_behavior_section' ),
            'simple-centered-popup'
        );

        add_settings_section(
            'scp_design_section',
            __( 'Design Settings', 'simple-centered-popup' ),
            array( $this, 'render_design_section' ),
            'simple-centered-popup'
        );

        add_settings_section(
            'scp_visibility_section',
            __( 'Visibility Settings', 'simple-centered-popup' ),
            array( $this, 'render_visibility_section' ),
            'simple-centered-popup'
        );

        // General fields
        add_settings_field( 'scp_enabled', __( 'Enable Popup', 'simple-centered-popup' ), array( $this, 'render_checkbox_field' ), 'simple-centered-popup', 'scp_general_section', array( 'label_for' => 'scp_enabled', 'option_name' => 'scp_enabled' ) );

        // Content fields
        add_settings_field( 'scp_title', __( 'Title', 'simple-centered-popup' ), array( $this, 'render_text_field' ), 'simple-centered-popup', 'scp_content_section', array( 'label_for' => 'scp_title', 'option_name' => 'scp_title' ) );
        add_settings_field( 'scp_content', __( 'Content (HTML)', 'simple-centered-popup' ), array( $this, 'render_textarea_field' ), 'simple-centered-popup', 'scp_content_section', array( 'label_for' => 'scp_content', 'option_name' => 'scp_content' ) );
        add_settings_field( 'scp_image_url', __( 'Image URL', 'simple-centered-popup' ), array( $this, 'render_text_field' ), 'simple-centered-popup', 'scp_content_section', array( 'label_for' => 'scp_image_url', 'option_name' => 'scp_image_url', 'description' => __( 'Upload an image via Media Library and paste the URL here.', 'simple-centered-popup' ) ) );
        add_settings_field( 'scp_video_embed', __( 'Video Embed Code', 'simple-centered-popup' ), array( $this, 'render_textarea_field' ), 'simple-centered-popup', 'scp_content_section', array( 'label_for' => 'scp_video_embed', 'option_name' => 'scp_video_embed', 'description' => __( 'Paste YouTube/Vimeo embed code or HTML video tag.', 'simple-centered-popup' ) ) );
        add_settings_field( 'scp_button_text', __( 'Button Text', 'simple-centered-popup' ), array( $this, 'render_text_field' ), 'simple-centered-popup', 'scp_content_section', array( 'label_for' => 'scp_button_text', 'option_name' => 'scp_button_text' ) );
        add_settings_field( 'scp_button_url', __( 'Button URL', 'simple-centered-popup' ), array( $this, 'render_text_field' ), 'simple-centered-popup', 'scp_content_section', array( 'label_for' => 'scp_button_url', 'option_name' => 'scp_button_url' ) );
        add_settings_field( 'scp_button_new_tab', __( 'Open in New Tab', 'simple-centered-popup' ), array( $this, 'render_checkbox_field' ), 'simple-centered-popup', 'scp_content_section', array( 'label_for' => 'scp_button_new_tab', 'option_name' => 'scp_button_new_tab' ) );

        // Behavior fields
        add_settings_field( 'scp_auto_open', __( 'Auto Open on Load', 'simple-centered-popup' ), array( $this, 'render_checkbox_field' ), 'simple-centered-popup', 'scp_behavior_section', array( 'label_for' => 'scp_auto_open', 'option_name' => 'scp_auto_open' ) );
        add_settings_field( 'scp_delay', __( 'Delay (ms)', 'simple-centered-popup' ), array( $this, 'render_number_field' ), 'simple-centered-popup', 'scp_behavior_section', array( 'label_for' => 'scp_delay', 'option_name' => 'scp_delay' ) );
        add_settings_field( 'scp_frequency_days', __( 'Show Again After (Days)', 'simple-centered-popup' ), array( $this, 'render_number_field' ), 'simple-centered-popup', 'scp_behavior_section', array( 'label_for' => 'scp_frequency_days', 'option_name' => 'scp_frequency_days', 'description' => __( 'Set to 0 to show on every page load.', 'simple-centered-popup' ) ) );

        // Design fields
        add_settings_field( 'scp_max_width', __( 'Max Width', 'simple-centered-popup' ), array( $this, 'render_text_field' ), 'simple-centered-popup', 'scp_design_section', array( 'label_for' => 'scp_max_width', 'option_name' => 'scp_max_width', 'description' => __( 'e.g., 600px, 90%, etc.', 'simple-centered-popup' ) ) );
        add_settings_field( 'scp_overlay_opacity', __( 'Overlay Opacity', 'simple-centered-popup' ), array( $this, 'render_number_field' ), 'simple-centered-popup', 'scp_design_section', array( 'label_for' => 'scp_overlay_opacity', 'option_name' => 'scp_overlay_opacity', 'description' => __( 'Value between 0 and 1.', 'simple-centered-popup' ) ) );
        add_settings_field( 'scp_background_color', __( 'Popup Background Color', 'simple-centered-popup' ), array( $this, 'render_color_field' ), 'simple-centered-popup', 'scp_design_section', array( 'label_for' => 'scp_background_color', 'option_name' => 'scp_background_color' ) );
        add_settings_field( 'scp_button_color', __( 'Button Color', 'simple-centered-popup' ), array( $this, 'render_color_field' ), 'simple-centered-popup', 'scp_design_section', array( 'label_for' => 'scp_button_color', 'option_name' => 'scp_button_color' ) );
        add_settings_field( 'scp_border_radius', __( 'Border Radius', 'simple-centered-popup' ), array( $this, 'render_text_field' ), 'simple-centered-popup', 'scp_design_section', array( 'label_for' => 'scp_border_radius', 'option_name' => 'scp_border_radius' ) );
        add_settings_field( 'scp_animation', __( 'Animation Type', 'simple-centered-popup' ), array( $this, 'render_select_field' ), 'simple-centered-popup', 'scp_design_section', array( 'label_for' => 'scp_animation', 'option_name' => 'scp_animation', 'options' => array( 'fade' => 'Fade', 'scale' => 'Scale', 'slide' => 'Slide' ) ) );
        add_settings_field( 'scp_animation_duration', __( 'Animation Duration', 'simple-centered-popup' ), array( $this, 'render_text_field' ), 'simple-centered-popup', 'scp_design_section', array( 'label_for' => 'scp_animation_duration', 'option_name' => 'scp_animation_duration', 'description' => __( 'e.g., 0.3s, 500ms', 'simple-centered-popup' ) ) );

        // Visibility fields
        add_settings_field( 'scp_show_on_homepage', __( 'Show on Homepage', 'simple-centered-popup' ), array( $this, 'render_checkbox_field' ), 'simple-centered-popup', 'scp_visibility_section', array( 'label_for' => 'scp_show_on_homepage', 'option_name' => 'scp_show_on_homepage' ) );
        add_settings_field( 'scp_show_on_posts', __( 'Show on Posts', 'simple-centered-popup' ), array( $this, 'render_checkbox_field' ), 'simple-centered-popup', 'scp_visibility_section', array( 'label_for' => 'scp_show_on_posts', 'option_name' => 'scp_show_on_posts' ) );
        add_settings_field( 'scp_show_on_pages', __( 'Show on Pages', 'simple-centered-popup' ), array( $this, 'render_checkbox_field' ), 'simple-centered-popup', 'scp_visibility_section', array( 'label_for' => 'scp_show_on_pages', 'option_name' => 'scp_show_on_pages' ) );
    }

    /**
     * Render section descriptions
     */
    public function render_general_section() {
        echo '<p>' . esc_html__( 'Configure general popup settings.', 'simple-centered-popup' ) . '</p>';
    }

    public function render_content_section() {
        echo '<p>' . esc_html__( 'Set the content displayed in the popup.', 'simple-centered-popup' ) . '</p>';
    }

    public function render_behavior_section() {
        echo '<p>' . esc_html__( 'Control when and how often the popup appears.', 'simple-centered-popup' ) . '</p>';
    }

    public function render_design_section() {
        echo '<p>' . esc_html__( 'Customize the appearance of the popup.', 'simple-centered-popup' ) . '</p>';
    }

    public function render_visibility_section() {
        echo '<p>' . esc_html__( 'Choose where the popup should be displayed.', 'simple-centered-popup' ) . '</p>';
    }

    /**
     * Render field types
     */
    public function render_text_field( $args ) {
        $value = get_option( $args['option_name'], '' );
        echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['option_name'] ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    public function render_number_field( $args ) {
        $value = get_option( $args['option_name'], 0 );
        echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['option_name'] ) . '" value="' . esc_attr( $value ) . '" class="small-text" />';
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    public function render_textarea_field( $args ) {
        $value = get_option( $args['option_name'], '' );
        echo '<textarea id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['option_name'] ) . '" rows="5" class="large-text">' . esc_textarea( $value ) . '</textarea>';
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    public function render_checkbox_field( $args ) {
        $value = get_option( $args['option_name'], false );
        echo '<input type="checkbox" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['option_name'] ) . '" value="1" ' . checked( 1, $value, false ) . ' />';
    }

    public function render_color_field( $args ) {
        $value = get_option( $args['option_name'], '#ffffff' );
        echo '<input type="color" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['option_name'] ) . '" value="' . esc_attr( $value ) . '" class="color-picker" />';
    }

    public function render_select_field( $args ) {
        $value = get_option( $args['option_name'], 'fade' );
        echo '<select id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['option_name'] ) . '">';
        foreach ( $args['options'] as $key => $label ) {
            echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'scp_settings_group' );
                do_settings_sections( 'simple-centered-popup' );
                submit_button( __( 'Save Settings', 'simple-centered-popup' ) );
                ?>
            </form>
            <hr />
            <h2><?php esc_html_e( 'Usage Instructions', 'simple-centered-popup' ); ?></h2>
            <p><?php esc_html_e( 'Use the shortcode [sc_popup] to display the popup programmatically in your posts or pages.', 'simple-centered-popup' ); ?></p>
            <p><?php esc_html_e( 'Or use the PHP function sc_popup_render() in your theme files.', 'simple-centered-popup' ); ?></p>
            <h3><?php esc_html_e( 'Example:', 'simple-centered-popup' ); ?></h3>
            <pre><code>[sc_popup]</code></pre>
            <pre><code>&lt;?php sc_popup_render(); ?&gt;</code></pre>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_simple-centered-popup' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
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

        // Check if already shown (cookie/localStorage check done in JS)
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

        wp_localize_script( 'scp-script', 'scpConfig', array(
            'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
            'nonce'            => wp_create_nonce( 'scp_nonce' ),
            'autoOpen'         => get_option( 'scp_auto_open', true ),
            'delay'            => absint( get_option( 'scp_delay', 1000 ) ),
            'frequencyDays'    => absint( get_option( 'scp_frequency_days', 7 ) ),
            'cookieName'       => 'scp_popup_shown',
            'animation'        => sanitize_text_field( get_option( 'scp_animation', 'fade' ) ),
            'animationDuration'=> sanitize_text_field( get_option( 'scp_animation_duration', '0.3s' ) ),
        ) );
    }

    /**
     * Render popup HTML
     */
    public function render_popup() {
        if ( ! $this->should_show_popup() ) {
            return;
        }

        $title            = sanitize_text_field( get_option( 'scp_title', '' ) );
        $content          = wp_kses_post( get_option( 'scp_content', '' ) );
        $image_url        = esc_url( get_option( 'scp_image_url', '' ) );
        $video_embed      = wp_kses_post( get_option( 'scp_video_embed', '' ) );
        $button_text      = sanitize_text_field( get_option( 'scp_button_text', __( 'Close', 'simple-centered-popup' ) ) );
        $button_url       = esc_url( get_option( 'scp_button_url', '' ) );
        $button_new_tab   = get_option( 'scp_button_new_tab', false );
        $max_width        = sanitize_text_field( get_option( 'scp_max_width', '600px' ) );
        $overlay_opacity  = floatval( get_option( 'scp_overlay_opacity', 0.7 ) );
        $background_color = sanitize_hex_color( get_option( 'scp_background_color', '#ffffff' ) );
        $button_color     = sanitize_hex_color( get_option( 'scp_button_color', '#0073aa' ) );
        $border_radius    = sanitize_text_field( get_option( 'scp_border_radius', '8px' ) );
        $animation        = sanitize_text_field( get_option( 'scp_animation', 'fade' ) );
        $animation_duration = sanitize_text_field( get_option( 'scp_animation_duration', '0.3s' ) );

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
