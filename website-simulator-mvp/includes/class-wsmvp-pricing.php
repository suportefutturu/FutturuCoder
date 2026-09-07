<?php
/**
 * Classe de preços
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Pricing {
    private static $instance = null;
    private $rules = [];

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->rules = get_option('wsmvp_pricing_rules', []);
    }

    public function get_all() { return $this->rules; }
    public function get_base_prices() { return $this->rules['base_prices'] ?? []; }
    public function get_category_labels() { return $this->rules['category_labels'] ?? []; }

    public function calculate_estimate($responses) {
        $base = 0; $total = 0;
        foreach ($this->get_base_prices() as $bp) {
            if (($responses['site_type'] ?? '') === $bp['value']) {
                $base = $bp['price']; break;
            }
        }
        $total = $base;
        $category = $total < 3000 ? 'basic' : 'intermediate';
        return [
            'total' => $total,
            'category' => $category,
            'category_label' => $this->get_category_labels()[$category] ?? $category,
            'estimated_pages' => count($responses['pages'] ?? []),
            'estimated_deadline' => ['min' => 30, 'max' => 45, 'label' => '30 a 45 dias']
        ];
    }

    public function get_estimate_text($data) {
        $currency = WSMVP_Settings::get_instance()->get('currency');
        $min = $data['total'] * 0.9; $max = $data['total'] * 1.1;
        return sprintf(__('Projeto classificado como <strong>%s</strong>. Investimento: <strong>%s %.2f a %s %.2f</strong>', WSMVP_TEXT_DOMAIN),
            $data['category_label'], $currency, $min, $currency, $max);
    }
}
