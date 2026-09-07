<?php
/**
 * Classe de perguntas
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Questions {
    private static $instance = null;
    private $questions = [];

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->questions = get_option('wsmvp_questions', []);
        usort($this->questions, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));
    }

    public function get_all() { return $this->questions; }
    public function get_active() { return array_filter($this->questions, fn($q) => ($q['status'] ?? 'active') === 'active'); }

    public function get_field_types() {
        return [
            'text' => __('Texto curto', WSMVP_TEXT_DOMAIN),
            'textarea' => __('Texto longo', WSMVP_TEXT_DOMAIN),
            'radio' => __('Seleção única', WSMVP_TEXT_DOMAIN),
            'checkbox' => __('Seleção múltipla', WSMVP_TEXT_DOMAIN),
            'select' => __('Dropdown', WSMVP_TEXT_DOMAIN),
            'color' => __('Cor', WSMVP_TEXT_DOMAIN),
            'section' => __('Seção', WSMVP_TEXT_DOMAIN)
        ];
    }

    public function get_questions_by_steps() {
        $steps = []; $current = null;
        foreach ($this->get_active() as $q) {
            if ($q['type'] === 'section') {
                $current = sanitize_title($q['title']);
                $steps[$current] = ['title' => $q['title'], 'questions' => []];
            } elseif ($current !== null) {
                $steps[$current]['questions'][] = $q;
            }
        }
        if (empty($steps)) {
            $steps['step_1'] = ['title' => __('Questionário', WSMVP_TEXT_DOMAIN), 'questions' => $this->get_active()];
        }
        return $steps;
    }

    public function is_visible($question, $responses) {
        if (empty($question['conditional_logic'])) return true;
        foreach ($question['conditional_logic'] as $rule) {
            $field = $rule['field'] ?? '';
            $value = $rule['value'] ?? '';
            $compare = $rule['compare'] ?? '=';
            $user_value = $responses[$field] ?? '';
            switch ($compare) {
                case '=': if ($user_value === $value) return true; break;
                case '!=': if ($user_value !== $value) return true; break;
                case 'contains': if (is_array($user_value) && in_array($value, $user_value)) return true; break;
            }
        }
        return false;
    }
}
