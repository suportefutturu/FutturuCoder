<?php
/**
 * Calculator Class
 * Handles investment estimation based on Sinapro guidelines and Futturu experience
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Calculator {

    private $base_values;
    private $complexity_multipliers;
    private $addon_values;
    private $hosting_values;
    private $maintenance_values;

    public function __construct() {
        $this->base_values = get_option('futturu_simulator_base_values', Futturu_Database::get_default_base_values());
        $this->complexity_multipliers = get_option('futturu_simulator_complexity_multipliers', array(
            'low' => 1.0,
            'medium' => 1.4,
            'high' => 1.9
        ));
        $this->addon_values = get_option('futturu_simulator_addon_values', Futturu_Database::get_default_addon_values());
        $this->hosting_values = get_option('futturu_simulator_hosting_values', Futturu_Database::get_default_hosting_values());
        $this->maintenance_values = get_option('futturu_simulator_maintenance_values', Futturu_Database::get_default_maintenance_values());
    }

    /**
     * Calculate investment estimate based on form data
     * @param array $data Form submission data
     * @return array Investment range and details
     */
    public function calculate($data) {
        $result = array(
            'base_value' => 0,
            'complexity_multiplier' => 1.0,
            'pages_value' => 0,
            'addons_value' => 0,
            'seo_value' => 0,
            'hosting_value' => 0,
            'maintenance_value' => 0,
            'subtotal' => 0,
            'min' => 0,
            'max' => 0,
            'estimated' => 0,
            'breakdown' => array()
        );

        // 1. Base value by site type
        $site_type = isset($data['site_type']) ? $data['site_type'] : 'institutional';
        $result['base_value'] = isset($this->base_values[$site_type]) ? $this->base_values[$site_type] : 5000;

        // 2. Complexity multiplier
        $complexity = isset($data['complexity_level']) ? $data['complexity_level'] : 'medium';
        $complexity_map = array(
            'low' => 'low',
            'baixa' => 'low',
            'medium' => 'medium',
            'media' => 'medium',
            'média' => 'medium',
            'high' => 'high',
            'alta' => 'high'
        );
        $complexity_key = isset($complexity_map[$complexity]) ? $complexity_map[$complexity] : 'medium';
        $result['complexity_multiplier'] = isset($this->complexity_multipliers[$complexity_key]) ? $this->complexity_multipliers[$complexity_key] : 1.4;

        // Apply complexity to base
        $base_with_complexity = $result['base_value'] * $result['complexity_multiplier'];

        // 3. Pages calculation
        $num_pages = isset($data['num_pages']) ? $data['num_pages'] : 'ate_10';
        $page_values = array(
            'ate_6' => 0,      // Base includes up to 6 sections
            'ate_10' => 800,   // +4 pages
            'ate_20' => 2000,  // +14 pages
            'ate_30' => 3500,  // +24 pages
            'sob_medida' => 5000 // Custom
        );
        $result['pages_value'] = isset($page_values[$num_pages]) ? $page_values[$num_pages] : 0;

        // 4. Add-ons
        $addons = isset($data['addons']) && is_array($data['addons']) ? $data['addons'] : array();
        $addons_total = 0;
        foreach ($addons as $addon) {
            if (isset($this->addon_values[$addon])) {
                $addons_total += $this->addon_values[$addon];
            }
        }
        $result['addons_value'] = $addons_total;

        // 5. SEO
        $seo_value = 0;
        if (!empty($data['seo_basic'])) {
            $seo_value += 800;
        }
        if (!empty($data['seo_advanced'])) {
            $seo_value += 2000;
        }
        $result['seo_value'] = $seo_value;

        // 6. Google Marketing tools (basic setup)
        $google_marketing = isset($data['google_marketing']) && is_array($data['google_marketing']) ? $data['google_marketing'] : array();
        $google_setup_value = count($google_marketing) * 200; // R$200 per tool setup
        if ($google_setup_value > 0) {
            $result['google_value'] = $google_setup_value;
        } else {
            $result['google_value'] = 0;
        }

        // 7. Hosting (first year)
        $hosting_current = isset($data['hosting_current']) ? $data['hosting_current'] : '';
        $hosting_premium = isset($data['hosting_premium_interest']) ? $data['hosting_premium_interest'] : '';
        
        $hosting_total = 0;
        if ($hosting_premium === 'sim_quero_conhecer' || $hosting_premium === 'envie_apresentacao') {
            $hosting_total = isset($this->hosting_values['cloud_pro_annual']) ? $this->hosting_values['cloud_pro_annual'] : 2400;
        } elseif ($hosting_current === 'quero_migrar_cloud') {
            $hosting_total = isset($this->hosting_values['cloud_basic_annual']) ? $this->hosting_values['cloud_basic_annual'] : 1200;
        } elseif ($hosting_current === 'nao_tenho') {
            $hosting_total = isset($this->hosting_values['shared_annual']) ? $this->hosting_values['shared_annual'] : 360;
        }

        // Domain registration if needed
        if (isset($data['domain_status']) && $data['domain_status'] === 'preciso_registrar') {
            $hosting_total += isset($this->hosting_values['domain_registration']) ? $this->hosting_values['domain_registration'] : 50;
        }
        $result['hosting_value'] = $hosting_total;

        // 8. Maintenance (first year)
        $maintenance_package = isset($data['maintenance_package']) ? $data['maintenance_package'] : '';
        $maintenance_total = 0;
        
        if ($maintenance_package === 'sim_quero_proposta') {
            $maintenance_needed = isset($data['maintenance_needed']) ? $data['maintenance_needed'] : '';
            if ($maintenance_needed === 'sim_mensalmente') {
                $maintenance_total = isset($this->maintenance_values['monthly_standard']) ? $this->maintenance_values['monthly_standard'] * 12 : 3600;
            } else {
                $maintenance_total = isset($this->maintenance_values['monthly_basic']) ? $this->maintenance_values['monthly_basic'] * 12 : 1800;
            }
        }
        $result['maintenance_value'] = $maintenance_total;

        // Calculate subtotal (project only, without recurring)
        $result['subtotal'] = $base_with_complexity + $result['pages_value'] + $result['addons_value'] + 
                              $result['seo_value'] + (isset($result['google_value']) ? $result['google_value'] : 0);

        // Calculate total with hosting and maintenance (first year)
        $total_first_year = $result['subtotal'] + $result['hosting_value'] + $result['maintenance_value'];

        // Create a range (±15% for flexibility)
        $result['min'] = round($total_first_year * 0.85, 2);
        $result['max'] = round($total_first_year * 1.15, 2);
        $result['estimated'] = round($total_first_year, 2);

        // Build breakdown for display
        $result['breakdown'] = array(
            array(
                'label' => 'Valor Base (' . ucfirst($site_type) . ')',
                'value' => $result['base_value'],
                'note' => 'Complexidade: ' . $this->get_complexity_label($complexity_key)
            ),
            array(
                'label' => 'Páginas Adicionais',
                'value' => $result['pages_value']
            ),
            array(
                'label' => 'Recursos Adicionais',
                'value' => $result['addons_value']
            ),
            array(
                'label' => 'SEO',
                'value' => $result['seo_value']
            ),
            array(
                'label' => 'Ferramentas Google',
                'value' => isset($result['google_value']) ? $result['google_value'] : 0
            ),
            array(
                'label' => 'Hospedagem + Domínio (1º ano)',
                'value' => $result['hosting_value'],
                'recurring' => true
            ),
            array(
                'label' => 'Manutenção (1º ano)',
                'value' => $result['maintenance_value'],
                'recurring' => true
            )
        );

        return $result;
    }

    /**
     * Get estimated delivery time
     * @param array $data Form data
     * @return string Estimated delivery
     */
    public function get_delivery_estimate($data) {
        $delivery_time = isset($data['delivery_time']) ? $data['delivery_time'] : '30-45';
        
        $delivery_map = array(
            '30-45' => '30-45 dias úteis',
            '45-60' => '45-60 dias úteis',
            '60-90' => '60-90 dias úteis',
            'flexivel' => 'A combinar'
        );

        $base_delivery = isset($delivery_map[$delivery_time]) ? $delivery_map[$delivery_time] : '30-45 dias úteis';

        // Adjust based on complexity
        $complexity = isset($data['complexity_level']) ? $data['complexity_level'] : 'medium';
        if ($complexity === 'alta' || $complexity === 'high') {
            $base_delivery .= ' (prazo estimado para alta complexidade)';
        }

        // Check if specific date was requested
        if (!empty($data['specific_date'])) {
            $base_delivery .= ' - Data solicitada: ' . date('d/m/Y', strtotime($data['specific_date']));
        }

        return $base_delivery;
    }

    /**
     * Format currency for display
     * @param float $value
     * @return string Formatted currency
     */
    public static function format_currency($value) {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Get complexity label
     * @param string $key
     * @return string
     */
    private function get_complexity_label($key) {
        $labels = array(
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta'
        );
        return isset($labels[$key]) ? $labels[$key] : 'Média';
    }

    /**
     * Get site type label
     * @param string $key
     * @return string
     */
    public static function get_site_type_label($key) {
        $labels = array(
            'blog' => 'Blog',
            'news' => 'Notícias',
            'portfolio' => 'Portfólio',
            'hotsite' => 'Hotsite',
            'institutional' => 'Institucional',
            'ecommerce' => 'E-commerce',
            'other' => 'Outro'
        );
        return isset($labels[$key]) ? $labels[$key] : $key;
    }
}
