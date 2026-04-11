<?php
/**
 * Admin Settings Class for Simple Centered Popup
 * 
 * Handles all admin settings, sections, and fields including advanced design options.
 *
 * @package Simple_Centered_Popup
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Settings Class
 */
class SCP_Admin_Settings {

    /**
     * Single instance
     *
     * @var SCP_Admin_Settings
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return SCP_Admin_Settings
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
        add_action( 'admin_init', array( $this, 'register_settings' ), 20 );
        add_action( 'admin_menu', array( $this, 'add_admin_submenu' ), 100 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'admin_footer', array( $this, 'render_media_modal_template' ) );
    }

    /**
     * Add submenu page under Settings
     */
    public function add_admin_submenu() {
        add_options_page(
            __( 'Simple Popup Settings', 'simple-centered-popup' ),
            __( 'Simple Popup', 'simple-centered-popup' ),
            'manage_options',
            'simple-centered-popup',
            array( $this, 'render_settings_page' ),
            'dashicons-megaphone',
            100
        );
    }

    /**
     * Register all settings
     */
    public function register_settings() {
        // ==========================================
        // GENERAL SETTINGS SECTION
        // ==========================================
        add_settings_section(
            'scp_general_section',
            __( 'General Settings', 'simple-centered-popup' ),
            array( $this, 'render_section_description' ),
            'simple-centered-popup',
            array( 'description' => __( 'Configure basic popup behavior.', 'simple-centered-popup' ) )
        );

        register_setting( 'scp_settings_group', 'scp_enabled', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );

        add_settings_field( 'scp_enabled', __( 'Enable Popup', 'simple-centered-popup' ), 
            array( $this, 'render_checkbox_field' ), 
            'simple-centered-popup', 
            'scp_general_section', 
            array( 
                'label_for'   => 'scp_enabled', 
                'option_name' => 'scp_enabled',
                'description' => __( 'Check to enable the popup on the front-end.', 'simple-centered-popup' )
            ) 
        );

        // ==========================================
        // CONTENT SETTINGS SECTION
        // ==========================================
        add_settings_section(
            'scp_content_section',
            __( 'Content Settings', 'simple-centered-popup' ),
            array( $this, 'render_section_description' ),
            'simple-centered-popup',
            array( 'description' => __( 'Set the content displayed in the popup.', 'simple-centered-popup' ) )
        );

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

        register_setting( 'scp_settings_group', 'scp_image_alt', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
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

        add_settings_field( 'scp_title', __( 'Title', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_content_section', 
            array( 'label_for' => 'scp_title', 'option_name' => 'scp_title' ) 
        );

        add_settings_field( 'scp_content', __( 'Content (HTML/WYSIWYG)', 'simple-centered-popup' ), 
            array( $this, 'render_editor_field' ), 
            'simple-centered-popup', 
            'scp_content_section', 
            array( 'label_for' => 'scp_content', 'option_name' => 'scp_content' ) 
        );

        add_settings_field( 'scp_image_url', __( 'Image', 'simple-centered-popup' ), 
            array( $this, 'render_image_upload_field' ), 
            'simple-centered-popup', 
            'scp_content_section', 
            array( 
                'label_for'   => 'scp_image_url', 
                'option_name' => 'scp_image_url',
                'description' => __( 'Upload or select an image to display in the popup.', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_video_embed', __( 'Video Embed Code', 'simple-centered-popup' ), 
            array( $this, 'render_textarea_field' ), 
            'simple-centered-popup', 
            'scp_content_section', 
            array( 
                'label_for'   => 'scp_video_embed', 
                'option_name' => 'scp_video_embed', 
                'description' => __( 'Paste YouTube/Vimeo embed code or HTML5 video tag.', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_button_text', __( 'Button Text', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_content_section', 
            array( 'label_for' => 'scp_button_text', 'option_name' => 'scp_button_text' ) 
        );

        add_settings_field( 'scp_button_url', __( 'Button URL', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_content_section', 
            array( 'label_for' => 'scp_button_url', 'option_name' => 'scp_button_url' ) 
        );

        add_settings_field( 'scp_button_new_tab', __( 'Open in New Tab', 'simple-centered-popup' ), 
            array( $this, 'render_checkbox_field' ), 
            'simple-centered-popup', 
            'scp_content_section', 
            array( 'label_for' => 'scp_button_new_tab', 'option_name' => 'scp_button_new_tab' ) 
        );

        // ==========================================
        // BEHAVIOR SETTINGS SECTION
        // ==========================================
        add_settings_section(
            'scp_behavior_section',
            __( 'Behavior Settings', 'simple-centered-popup' ),
            array( $this, 'render_section_description' ),
            'simple-centered-popup',
            array( 'description' => __( 'Configure how and when the popup appears.', 'simple-centered-popup' ) )
        );

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

        add_settings_field( 'scp_auto_open', __( 'Auto Open on Load', 'simple-centered-popup' ), 
            array( $this, 'render_checkbox_field' ), 
            'simple-centered-popup', 
            'scp_behavior_section', 
            array( 'label_for' => 'scp_auto_open', 'option_name' => 'scp_auto_open' ) 
        );

        add_settings_field( 'scp_delay', __( 'Delay (milliseconds)', 'simple-centered-popup' ), 
            array( $this, 'render_number_field' ), 
            'simple-centered-popup', 
            'scp_behavior_section', 
            array( 
                'label_for'   => 'scp_delay', 
                'option_name' => 'scp_delay',
                'min'         => 0,
                'step'        => 100,
                'description' => __( 'Time to wait before showing the popup after page load.', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_frequency_days', __( 'Show Again After (Days)', 'simple-centered-popup' ), 
            array( $this, 'render_number_field' ), 
            'simple-centered-popup', 
            'scp_behavior_section', 
            array( 
                'label_for'   => 'scp_frequency_days', 
                'option_name' => 'scp_frequency_days', 
                'min'         => 0,
                'description' => __( 'Set to 0 to show on every page load.', 'simple-centered-popup' )
            ) 
        );

        // ==========================================
        // DESIGN & LAYOUT SECTION
        // ==========================================
        add_settings_section(
            'scp_design_section',
            __( 'Design & Layout', 'simple-centered-popup' ),
            array( $this, 'render_section_description' ),
            'simple-centered-popup',
            array( 'description' => __( 'Customize the appearance and layout of the popup.', 'simple-centered-popup' ) )
        );

        register_setting( 'scp_settings_group', 'scp_max_width', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '600px',
        ) );

        register_setting( 'scp_settings_group', 'scp_max_height', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '90vh',
        ) );

        register_setting( 'scp_settings_group', 'scp_popup_padding', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '30px',
        ) );

        register_setting( 'scp_settings_group', 'scp_overlay_opacity', array(
            'type'              => 'number',
            'sanitize_callback' => 'floatval',
            'default'           => 0.7,
        ) );

        register_setting( 'scp_settings_group', 'scp_overlay_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#000000',
        ) );

        register_setting( 'scp_settings_group', 'scp_background_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#ffffff',
        ) );

        register_setting( 'scp_settings_group', 'scp_border_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#e0e0e0',
        ) );

        register_setting( 'scp_settings_group', 'scp_border_width', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '1px',
        ) );

        register_setting( 'scp_settings_group', 'scp_border_radius', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '8px',
        ) );

        register_setting( 'scp_settings_group', 'scp_box_shadow', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0 10px 40px rgba(0, 0, 0, 0.3)',
        ) );

        register_setting( 'scp_settings_group', 'scp_z_index', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 999999,
        ) );

        add_settings_field( 'scp_max_width', __( 'Max Width', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_max_width', 
                'option_name' => 'scp_max_width', 
                'description' => __( 'e.g., 600px, 90%, 800px', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_max_height', __( 'Max Height', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_max_height', 
                'option_name' => 'scp_max_height', 
                'description' => __( 'e.g., 90vh, 500px, auto', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_popup_padding', __( 'Popup Padding', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_popup_padding', 
                'option_name' => 'scp_popup_padding', 
                'description' => __( 'e.g., 30px, 20px 40px', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_overlay_opacity', __( 'Overlay Opacity', 'simple-centered-popup' ), 
            array( $this, 'render_range_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_overlay_opacity', 
                'option_name' => 'scp_overlay_opacity', 
                'min'         => 0,
                'max'         => 1,
                'step'        => 0.05,
                'description' => __( 'Value between 0 (transparent) and 1 (opaque).', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_overlay_color', __( 'Overlay Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 'label_for' => 'scp_overlay_color', 'option_name' => 'scp_overlay_color' ) 
        );

        add_settings_field( 'scp_background_color', __( 'Popup Background Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 'label_for' => 'scp_background_color', 'option_name' => 'scp_background_color' ) 
        );

        add_settings_field( 'scp_border_color', __( 'Border Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 'label_for' => 'scp_border_color', 'option_name' => 'scp_border_color' ) 
        );

        add_settings_field( 'scp_border_width', __( 'Border Width', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_border_width', 
                'option_name' => 'scp_border_width', 
                'description' => __( 'e.g., 0, 1px, 2px solid', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_border_radius', __( 'Border Radius', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_border_radius', 
                'option_name' => 'scp_border_radius', 
                'description' => __( 'e.g., 0, 8px, 50% for circle', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_box_shadow', __( 'Box Shadow', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_box_shadow', 
                'option_name' => 'scp_box_shadow', 
                'description' => __( 'CSS box-shadow value. Use "none" to disable.', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_z_index', __( 'Z-Index', 'simple-centered-popup' ), 
            array( $this, 'render_number_field' ), 
            'simple-centered-popup', 
            'scp_design_section', 
            array( 
                'label_for'   => 'scp_z_index', 
                'option_name' => 'scp_z_index', 
                'min'         => 1,
                'description' => __( 'Stacking order. Higher values appear on top.', 'simple-centered-popup' )
            ) 
        );

        // ==========================================
        // TYPOGRAPHY & COLORS SECTION
        // ==========================================
        add_settings_section(
            'scp_typography_section',
            __( 'Typography & Colors', 'simple-centered-popup' ),
            array( $this, 'render_section_description' ),
            'simple-centered-popup',
            array( 'description' => __( 'Customize fonts and text colors.', 'simple-centered-popup' ) )
        );

        register_setting( 'scp_settings_group', 'scp_font_family', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
        ) );

        register_setting( 'scp_settings_group', 'scp_title_font_size', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '24px',
        ) );

        register_setting( 'scp_settings_group', 'scp_title_font_weight', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '600',
        ) );

        register_setting( 'scp_settings_group', 'scp_title_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#333333',
        ) );

        register_setting( 'scp_settings_group', 'scp_content_font_size', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '16px',
        ) );

        register_setting( 'scp_settings_group', 'scp_content_line_height', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '1.6',
        ) );

        register_setting( 'scp_settings_group', 'scp_content_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#444444',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#0073aa',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_text_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#ffffff',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_hover_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#005a87',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_font_size', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '16px',
        ) );

        register_setting( 'scp_settings_group', 'scp_button_padding', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '12px 30px',
        ) );

        add_settings_field( 'scp_font_family', __( 'Font Family', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 
                'label_for'   => 'scp_font_family', 
                'option_name' => 'scp_font_family', 
                'description' => __( 'e.g., Arial, sans-serif or Google Fonts import', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_title_font_size', __( 'Title Font Size', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 
                'label_for'   => 'scp_title_font_size', 
                'option_name' => 'scp_title_font_size', 
                'description' => __( 'e.g., 24px, 1.5rem', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_title_font_weight', __( 'Title Font Weight', 'simple-centered-popup' ), 
            array( $this, 'render_select_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 
                'label_for'   => 'scp_title_font_weight', 
                'option_name' => 'scp_title_font_weight', 
                'options'     => array(
                    '300' => 'Light (300)',
                    '400' => 'Normal (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi Bold (600)',
                    '700' => 'Bold (700)',
                    '800' => 'Extra Bold (800)',
                )
            ) 
        );

        add_settings_field( 'scp_title_color', __( 'Title Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 'label_for' => 'scp_title_color', 'option_name' => 'scp_title_color' ) 
        );

        add_settings_field( 'scp_content_font_size', __( 'Content Font Size', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 
                'label_for'   => 'scp_content_font_size', 
                'option_name' => 'scp_content_font_size', 
                'description' => __( 'e.g., 16px, 1rem', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_content_line_height', __( 'Content Line Height', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 
                'label_for'   => 'scp_content_line_height', 
                'option_name' => 'scp_content_line_height', 
                'description' => __( 'e.g., 1.5, 1.6, 2', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_content_color', __( 'Content Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 'label_for' => 'scp_content_color', 'option_name' => 'scp_content_color' ) 
        );

        add_settings_field( 'scp_button_color', __( 'Button Background Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 'label_for' => 'scp_button_color', 'option_name' => 'scp_button_color' ) 
        );

        add_settings_field( 'scp_button_text_color', __( 'Button Text Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 'label_for' => 'scp_button_text_color', 'option_name' => 'scp_button_text_color' ) 
        );

        add_settings_field( 'scp_button_hover_color', __( 'Button Hover Color', 'simple-centered-popup' ), 
            array( $this, 'render_color_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 'label_for' => 'scp_button_hover_color', 'option_name' => 'scp_button_hover_color' ) 
        );

        add_settings_field( 'scp_button_font_size', __( 'Button Font Size', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 
                'label_for'   => 'scp_button_font_size', 
                'option_name' => 'scp_button_font_size', 
                'description' => __( 'e.g., 16px, 1rem', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_button_padding', __( 'Button Padding', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_typography_section', 
            array( 
                'label_for'   => 'scp_button_padding', 
                'option_name' => 'scp_button_padding', 
                'description' => __( 'e.g., 12px 30px', 'simple-centered-popup' )
            ) 
        );

        // ==========================================
        // ANIMATION SETTINGS SECTION
        // ==========================================
        add_settings_section(
            'scp_animation_section',
            __( 'Animation Settings', 'simple-centered-popup' ),
            array( $this, 'render_section_description' ),
            'simple-centered-popup',
            array( 'description' => __( 'Configure popup entrance animations.', 'simple-centered-popup' ) )
        );

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

        register_setting( 'scp_settings_group', 'scp_animation_easing', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'ease-out',
        ) );

        add_settings_field( 'scp_animation', __( 'Animation Type', 'simple-centered-popup' ), 
            array( $this, 'render_select_field' ), 
            'simple-centered-popup', 
            'scp_animation_section', 
            array( 
                'label_for'   => 'scp_animation', 
                'option_name' => 'scp_animation', 
                'options'     => array(
                    'fade'   => 'Fade In',
                    'scale'  => 'Scale/Zoom',
                    'slide'  => 'Slide Down',
                    'slide-up' => 'Slide Up',
                    'slide-left' => 'Slide from Left',
                    'slide-right' => 'Slide from Right',
                    'flip'   => 'Flip',
                    'bounce' => 'Bounce',
                )
            ) 
        );

        add_settings_field( 'scp_animation_duration', __( 'Animation Duration', 'simple-centered-popup' ), 
            array( $this, 'render_text_field' ), 
            'simple-centered-popup', 
            'scp_animation_section', 
            array( 
                'label_for'   => 'scp_animation_duration', 
                'option_name' => 'scp_animation_duration', 
                'description' => __( 'e.g., 0.3s, 300ms, 0.5s', 'simple-centered-popup' )
            ) 
        );

        add_settings_field( 'scp_animation_easing', __( 'Animation Easing', 'simple-centered-popup' ), 
            array( $this, 'render_select_field' ), 
            'simple-centered-popup', 
            'scp_animation_section', 
            array( 
                'label_for'   => 'scp_animation_easing', 
                'option_name' => 'scp_animation_easing', 
                'options'     => array(
                    'linear'     => 'Linear',
                    'ease'       => 'Ease',
                    'ease-in'    => 'Ease In',
                    'ease-out'   => 'Ease Out',
                    'ease-in-out'=> 'Ease In Out',
                )
            ) 
        );

        // ==========================================
        // VISIBILITY SETTINGS SECTION
        // ==========================================
        add_settings_section(
            'scp_visibility_section',
            __( 'Visibility Settings', 'simple-centered-popup' ),
            array( $this, 'render_section_description' ),
            'simple-centered-popup',
            array( 'description' => __( 'Control where the popup appears.', 'simple-centered-popup' ) )
        );

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

        register_setting( 'scp_settings_group', 'scp_show_on_archive', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ) );

        register_setting( 'scp_settings_group', 'scp_exclude_urls', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => '',
        ) );

        add_settings_field( 'scp_show_on_homepage', __( 'Show on Homepage', 'simple-centered-popup' ), 
            array( $this, 'render_checkbox_field' ), 
            'simple-centered-popup', 
            'scp_visibility_section', 
            array( 'label_for' => 'scp_show_on_homepage', 'option_name' => 'scp_show_on_homepage' ) 
        );

        add_settings_field( 'scp_show_on_posts', __( 'Show on Posts', 'simple-centered-popup' ), 
            array( $this, 'render_checkbox_field' ), 
            'simple-centered-popup', 
            'scp_visibility_section', 
            array( 'label_for' => 'scp_show_on_posts', 'option_name' => 'scp_show_on_posts' ) 
        );

        add_settings_field( 'scp_show_on_pages', __( 'Show on Pages', 'simple-centered-popup' ), 
            array( $this, 'render_checkbox_field' ), 
            'simple-centered-popup', 
            'scp_visibility_section', 
            array( 'label_for' => 'scp_show_on_pages', 'option_name' => 'scp_show_on_pages' ) 
        );

        add_settings_field( 'scp_show_on_archive', __( 'Show on Archive Pages', 'simple-centered-popup' ), 
            array( $this, 'render_checkbox_field' ), 
            'simple-centered-popup', 
            'scp_visibility_section', 
            array( 'label_for' => 'scp_show_on_archive', 'option_name' => 'scp_show_on_archive' ) 
        );

        add_settings_field( 'scp_exclude_urls', __( 'Exclude URLs', 'simple-centered-popup' ), 
            array( $this, 'render_textarea_field' ), 
            'simple-centered-popup', 
            'scp_visibility_section', 
            array( 
                'label_for'   => 'scp_exclude_urls', 
                'option_name' => 'scp_exclude_urls', 
                'description' => __( 'One URL path per line (e.g., /checkout/, /thank-you/). Popup will NOT show on these pages.', 'simple-centered-popup' )
            ) 
        );
    }

    /**
     * Render section description
     *
     * @param array $args Section arguments.
     */
    public function render_section_description( $args = array() ) {
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    /**
     * Render text input field
     *
     * @param array $args Field arguments.
     */
    public function render_text_field( $args ) {
        $value = get_option( $args['option_name'], '' );
        $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
        
        echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" 
              name="' . esc_attr( $args['option_name'] ) . '" 
              value="' . esc_attr( $value ) . '" 
              class="regular-text" 
              placeholder="' . esc_attr( $placeholder ) . '" />';
        
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    /**
     * Render number input field
     *
     * @param array $args Field arguments.
     */
    public function render_number_field( $args ) {
        $value = get_option( $args['option_name'], 0 );
        $min = isset( $args['min'] ) ? $args['min'] : 0;
        $max = isset( $args['max'] ) ? $args['max'] : '';
        $step = isset( $args['step'] ) ? $args['step'] : 1;
        
        echo '<input type="number" id="' . esc_attr( $args['label_for'] ) . '" 
              name="' . esc_attr( $args['option_name'] ) . '" 
              value="' . esc_attr( $value ) . '" 
              class="small-text" 
              min="' . esc_attr( $min ) . '" 
              max="' . esc_attr( $max ) . '" 
              step="' . esc_attr( $step ) . '" />';
        
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    /**
     * Render textarea field
     *
     * @param array $args Field arguments.
     */
    public function render_textarea_field( $args ) {
        $value = get_option( $args['option_name'], '' );
        $rows = isset( $args['rows'] ) ? $args['rows'] : 4;
        
        echo '<textarea id="' . esc_attr( $args['label_for'] ) . '" 
               name="' . esc_attr( $args['option_name'] ) . '" 
               rows="' . esc_attr( $rows ) . '" 
               class="large-text">' . esc_textarea( $value ) . '</textarea>';
        
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    /**
     * Render WYSIWYG editor field
     *
     * @param array $args Field arguments.
     */
    public function render_editor_field( $args ) {
        $value = get_option( $args['option_name'], '' );
        $editor_id = $args['label_for'] . '_editor';
        
        wp_editor(
            $value,
            $editor_id,
            array(
                'textarea_name' => $args['option_name'],
                'textarea_rows' => 8,
                'media_buttons' => true,
                'teeny'         => false,
                'quicktags'     => true,
            )
        );
        
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    /**
     * Render checkbox field
     *
     * @param array $args Field arguments.
     */
    public function render_checkbox_field( $args ) {
        $value = get_option( $args['option_name'], false );
        
        echo '<label for="' . esc_attr( $args['label_for'] ) . '">';
        echo '<input type="checkbox" id="' . esc_attr( $args['label_for'] ) . '" 
              name="' . esc_attr( $args['option_name'] ) . '" 
              value="1" ' . checked( $value, 1, false ) . ' />';
        
        if ( ! empty( $args['description'] ) ) {
            echo ' <span class="description">' . esc_html( $args['description'] ) . '</span>';
        }
        echo '</label>';
    }

    /**
     * Render color picker field
     *
     * @param array $args Field arguments.
     */
    public function render_color_field( $args ) {
        $value = get_option( $args['option_name'], '#ffffff' );
        
        echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" 
              name="' . esc_attr( $args['option_name'] ) . '" 
              value="' . esc_attr( $value ) . '" 
              class="scp-color-picker" 
              data-default-color="' . esc_attr( $value ) . '" />';
    }

    /**
     * Render select dropdown field
     *
     * @param array $args Field arguments.
     */
    public function render_select_field( $args ) {
        $value = get_option( $args['option_name'], '' );
        
        echo '<select id="' . esc_attr( $args['label_for'] ) . '" 
              name="' . esc_attr( $args['option_name'] ) . '" 
              class="regular-post">';
        
        foreach ( $args['options'] as $key => $label ) {
            echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' 
               . esc_html( $label ) . '</option>';
        }
        
        echo '</select>';
        
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    /**
     * Render range/slider field
     *
     * @param array $args Field arguments.
     */
    public function render_range_field( $args ) {
        $value = get_option( $args['option_name'], 0.5 );
        $min = isset( $args['min'] ) ? $args['min'] : 0;
        $max = isset( $args['max'] ) ? $args['max'] : 1;
        $step = isset( $args['step'] ) ? $args['step'] : 0.01;
        
        echo '<input type="range" id="' . esc_attr( $args['label_for'] ) . '" 
              name="' . esc_attr( $args['option_name'] ) . '" 
              value="' . esc_attr( $value ) . '" 
              min="' . esc_attr( $min ) . '" 
              max="' . esc_attr( $max ) . '" 
              step="' . esc_attr( $step ) . '" 
              class="scp-range-slider" />
              <span class="scp-range-value">' . esc_html( $value ) . '</span>';
        
        if ( ! empty( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    /**
     * Render image upload field with media library integration
     *
     * @param array $args Field arguments.
     */
    public function render_image_upload_field( $args ) {
        $image_url = get_option( $args['option_name'], '' );
        $image_alt = get_option( 'scp_image_alt', '' );
        ?>
        <div class="scp-image-upload-container">
            <div class="scp-image-preview" <?php echo empty( $image_url ) ? 'style="display:none;"' : ''; ?>>
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" style="max-width:300px;height:auto;border-radius:4px;" />
            </div>
            <div class="scp-image-controls">
                <button type="button" class="button scp-upload-button">
                    <?php esc_html_e( 'Select Image', 'simple-centered-popup' ); ?>
                </button>
                <button type="button" class="button scp-remove-button" <?php echo empty( $image_url ) ? 'style="display:none;"' : ''; ?>>
                    <?php esc_html_e( 'Remove Image', 'simple-centered-popup' ); ?>
                </button>
            </div>
            <input type="hidden" id="<?php echo esc_attr( $args['label_for'] ); ?>" 
                   name="<?php echo esc_attr( $args['option_name'] ); ?>" 
                   value="<?php echo esc_attr( $image_url ); ?>" 
                   class="scp-image-url" />
            <input type="hidden" name="scp_image_alt" value="<?php echo esc_attr( $image_alt ); ?>" class="scp-image-alt" />
            
            <?php if ( ! empty( $args['description'] ) ) : ?>
                <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render main settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap scp-settings-page">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="scp-settings-header">
                <p class="about-description">
                    <?php esc_html_e( 'Configure your popup modal with advanced design, typography, and behavior options.', 'simple-centered-popup' ); ?>
                </p>
            </div>
            
            <form action="options.php" method="post" enctype="multipart/form-data">
                <?php
                settings_fields( 'scp_settings_group' );
                
                // Render tabs navigation
                $this->render_settings_tabs();
                
                // Render all sections
                do_settings_sections( 'simple-centered-popup' );
                
                submit_button( __( 'Save All Settings', 'simple-centered-popup' ), 'primary large' );
                ?>
            </form>
            
            <hr style="margin: 40px 0;" />
            
            <div class="scp-shortcode-info">
                <h2><?php esc_html_e( 'Usage Instructions', 'simple-centered-popup' ); ?></h2>
                <div class="scp-info-grid">
                    <div class="scp-info-card">
                        <h3><?php esc_html_e( 'Shortcode', 'simple-centered-popup' ); ?></h3>
                        <p><?php esc_html_e( 'Use this shortcode in posts, pages, or widgets:', 'simple-centered-popup' ); ?></p>
                        <code>[sc_popup]</code>
                    </div>
                    <div class="scp-info-card">
                        <h3><?php esc_html_e( 'PHP Function', 'simple-centered-popup' ); ?></h3>
                        <p><?php esc_html_e( 'Use this function in theme template files:', 'simple-centered-popup' ); ?></p>
                        <code>&lt;?php sc_popup_render(); ?&gt;</code>
                    </div>
                    <div class="scp-info-card">
                        <h3><?php esc_html_e( 'JavaScript Control', 'simple-centered-popup' ); ?></h3>
                        <p><?php esc_html_e( 'Open/close popup programmatically:', 'simple-centered-popup' ); ?></p>
                        <code>window.SCPPopup.open()<br/>window.SCPPopup.close()</code>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings tabs navigation
     */
    private function render_settings_tabs() {
        $tabs = array(
            'general'     => __( 'General', 'simple-centered-popup' ),
            'content'     => __( 'Content', 'simple-centered-popup' ),
            'behavior'    => __( 'Behavior', 'simple-centered-popup' ),
            'design'      => __( 'Design & Layout', 'simple-centered-popup' ),
            'typography'  => __( 'Typography', 'simple-centered-popup' ),
            'animation'   => __( 'Animation', 'simple-centered-popup' ),
            'visibility'  => __( 'Visibility', 'simple-centered-popup' ),
        );
        ?>
        <nav class="nav-tab-wrapper scp-tabs">
            <?php foreach ( $tabs as $slug => $label ) : ?>
                <a href="#scp-tab-<?php echo esc_attr( $slug ); ?>" class="nav-tab scp-tab" data-tab="<?php echo esc_attr( $slug ); ?>">
                    <?php echo esc_html( $label ); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_simple-centered-popup' !== $hook ) {
            return;
        }
        
        // WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        
        // Media uploader
        wp_enqueue_media();
        
        // Custom admin CSS
        wp_enqueue_style( 
            'scp-admin-style', 
            SCP_PLUGIN_URL . 'assets/css/admin.css', 
            array(), 
            SCP_VERSION 
        );
        
        // Custom admin JS
        wp_enqueue_script( 
            'scp-admin-script', 
            SCP_PLUGIN_URL . 'assets/js/admin.js', 
            array( 'jquery', 'wp-color-picker', 'media-upload', 'thickbox' ), 
            SCP_VERSION, 
            true 
        );
        
        // Localize script
        wp_localize_script( 'scp-admin-script', 'scpAdminConfig', array(
            'uploadTitle'   => __( 'Select Image', 'simple-centered-popup' ),
            'uploadButton'  => __( 'Use this image', 'simple-centered-popup' ),
            'nonce'         => wp_create_nonce( 'scp_admin_nonce' ),
        ) );
    }

    /**
     * Render hidden template for media modal
     */
    public function render_media_modal_template() {
        $screen = get_current_screen();
        if ( 'settings_page_simple-centered-popup' !== $screen->id ) {
            return;
        }
        ?>
        <script type="text/template" id="tmpl-scp-media-modal">
            <div class="scp-media-modal-content">
                <p><?php esc_html_e( 'Select or upload an image for your popup.', 'simple-centered-popup' ); ?></p>
            </div>
        </script>
        <?php
    }
}

// Initialize admin settings
SCP_Admin_Settings::get_instance();
