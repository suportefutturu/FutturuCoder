<?php
/**
 * Classe do frontend
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Public {
    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('website_simulator', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function render_shortcode() {
        ob_start();
        include WSMVP_PLUGIN_DIR . 'public/templates/simulator.php';
        return ob_get_clean();
    }

    public function enqueue_assets() {
        wp_enqueue_style('wsmvp-simulator', WSMVP_PLUGIN_URL . 'public/css/simulator.css', [], WSMVP_VERSION);
        wp_enqueue_script('wsmvp-simulator', WSMVP_PLUGIN_URL . 'public/js/simulator.js', ['jquery'], WSMVP_VERSION, true);
        wp_localize_script('wsmvp_simulator', 'wsmvp_config', [
            'nonce' => WSMVP_Core::generate_nonce('public'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'steps' => WSMVP_Questions::get_instance()->get_questions_by_steps(),
            'settings' => WSMVP_Settings::get_instance()->get_all()
        ]);
    }

    public function render_question($question, $responses = []) {
        $name = sanitize_title($question['title']);
        $type = $question['type'] ?? 'text';
        $required = $question['required'] ?? false;
        $value = $responses[$name] ?? '';
        $options = $question['options'] ?? [];

        if ($type === 'section') return;
        if (!WSMVP_Questions::get_instance()->is_visible($question, $responses)) return;

        echo '<div class="wsmvp-question" data-name="' . esc_attr($name) . '" data-type="' . esc_attr($type) . '">';
        echo '<h3>' . esc_html($question['title']) . ($required ? ' <span class="required">*</span>' : '') . '</h3>';
        if (!empty($question['description'])) echo '<p>' . esc_html($question['description']) . '</p>';

        switch ($type) {
            case 'radio':
                foreach ($options as $opt) {
                    $checked = ($value === $opt['value']) ? 'checked' : '';
                    echo '<label><input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($opt['value']) . '" ' . $checked . '> ' . esc_html($opt['label']) . '</label><br>';
                }
                break;
            case 'checkbox':
                foreach ($options as $opt) {
                    $checked = (is_array($value) && in_array($opt['value'], $value)) ? 'checked' : '';
                    echo '<label><input type="checkbox" name="' . esc_attr($name) . '[]" value="' . esc_attr($opt['value']) . '" ' . $checked . '> ' . esc_html($opt['label']) . '</label><br>';
                }
                break;
            case 'select':
                echo '<select name="' . esc_attr($name) . '">';
                foreach ($options as $opt) {
                    $selected = ($value === $opt['value']) ? 'selected' : '';
                    echo '<option value="' . esc_attr($opt['value']) . '" ' . $selected . '>' . esc_html($opt['label']) . '</option>';
                }
                echo '</select>';
                break;
            case 'color':
                echo '<input type="color" name="' . esc_attr($name) . '" value="' . esc_attr($value ?: '#4361ee') . '">';
                break;
            case 'textarea':
                echo '<textarea name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
                break;
            default:
                echo '<input type="' . ($type === 'email' ? 'email' : 'text') . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
        }
        echo '</div>';
    }
}
