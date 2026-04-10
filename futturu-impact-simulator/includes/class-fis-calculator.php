<?php
/**
 * Calculator class for Futturu Impact Simulator
 */
if (!defined('ABSPATH')) {
    exit;
}

class FIS_Calculator {
    
    /**
     * Calculate impact based on user input
     */
    public static function calculate($business_type, $revenue_range, $target_audience, $objective) {
        $settings = get_option('fis_settings');
        $benchmarks = isset($settings['benchmarks']) ? $settings['benchmarks'] : fis_get_default_benchmarks();
        $base_values = isset($settings['base_values']) ? $settings['base_values'] : fis_get_default_base_values();
        
        // Normalize target audience
        if ($target_audience === 'both') {
            $target_audience = 'b2c'; // Default to B2C for calculation
        }
        
        // Get benchmark coefficients
        $coefficients = self::get_coefficients($benchmarks, $business_type, $target_audience, $objective);
        
        // Get base traffic based on revenue range
        $base_traffic = self::get_base_traffic($revenue_range, $base_values);
        
        // Calculate current situation (WITHOUT professional website - realistic baseline)
        // Current traffic is a fraction of potential (business relying on word-of-mouth, physical presence only)
        $current_traffic = round($base_traffic * 0.25); // 25% of potential without professional site
        $current_conversion_rate = $base_values['base_conversion_rate'] * 0.4; // Lower conversion without optimization
        $current_leads = max(10, round($current_traffic * $current_conversion_rate)); // Minimum 10 leads
        $current_conversions = max(2, round($current_leads * 0.25)); // At least 2 conversions
        
        // Calculate projected situation (WITH Futturu professional website)
        // Full potential with SEO, optimized design, high performance hosting
        $projected_traffic = round($base_traffic * $coefficients['traffic_mult']);
        $projected_conversion_rate = min(0.15, $base_values['base_conversion_rate'] * $coefficients['conversion_rate']);
        $projected_leads = round($projected_traffic * $projected_conversion_rate * $coefficients['lead_mult']);
        $projected_conversions = round($projected_leads * 0.50); // 50% conversion with proper nurturing
        
        // Ensure projected values are always higher than current
        $projected_traffic = max($projected_traffic, $current_traffic + 100);
        $projected_leads = max($projected_leads, $current_leads + 5);
        $projected_conversions = max($projected_conversions, $current_conversions + 2);
        
        // Calculate average ticket based on revenue range
        $avg_ticket = self::get_avg_ticket($revenue_range, $base_values);
        
        // Calculate additional annual revenue
        $monthly_revenue_increase = ($projected_conversions - $current_conversions) * $avg_ticket;
        $annual_revenue_increase = round($monthly_revenue_increase * 12);
        
        // Generate justifications
        $justifications = self::generate_justifications($objective, $coefficients);
        
        return array(
            'current' => array(
                'traffic' => $current_traffic,
                'leads' => $current_leads,
                'conversions' => $current_conversions,
                'revenue' => round($current_conversions * $avg_ticket * 12)
            ),
            'projected' => array(
                'traffic' => $projected_traffic,
                'leads' => $projected_leads,
                'conversions' => $projected_conversions,
                'revenue' => round($projected_conversions * $avg_ticket * 12)
            ),
            'increase' => array(
                'traffic' => $projected_traffic - $current_traffic,
                'leads' => $projected_leads - $current_leads,
                'conversions' => $projected_conversions - $current_conversions,
                'revenue' => $annual_revenue_increase
            ),
            'justifications' => $justifications,
            'coefficients' => $coefficients
        );
    }
    
    /**
     * Get coefficients from benchmarks matrix
     */
    private static function get_coefficients($benchmarks, $business_type, $target_audience, $objective) {
        // Fallback to 'outro' if business type not found
        if (!isset($benchmarks[$business_type])) {
            $business_type = 'outro';
        }
        
        // Fallback to 'b2c' if target audience not found
        if (!isset($benchmarks[$business_type][$target_audience])) {
            $target_audience = 'b2c';
        }
        
        // Fallback to 'visibilidade' if objective not found
        if (!isset($benchmarks[$business_type][$target_audience][$objective])) {
            $objective = 'visibilidade';
        }
        
        return $benchmarks[$business_type][$target_audience][$objective];
    }
    
    /**
     * Get base traffic based on revenue range
     */
    private static function get_base_traffic($revenue_range, $base_values) {
        switch ($revenue_range) {
            case 'low':
                return $base_values['base_traffic'];
            case 'medium':
                return $base_values['base_traffic'] * 2;
            case 'high':
                return $base_values['base_traffic'] * 3;
            case 'very_high':
                return $base_values['base_traffic'] * 5;
            default:
                return $base_values['base_traffic'];
        }
    }
    
    /**
     * Get average ticket based on revenue range
     */
    private static function get_avg_ticket($revenue_range, $base_values) {
        switch ($revenue_range) {
            case 'low':
                return $base_values['avg_ticket_low'];
            case 'medium':
                return $base_values['avg_ticket_medium'];
            case 'high':
                return $base_values['avg_ticket_high'];
            case 'very_high':
                return $base_values['avg_ticket_high'] * 2;
            default:
                return $base_values['avg_ticket_low'];
        }
    }
    
    /**
     * Generate justifications for projections
     */
    private static function generate_justifications($objective, $coefficients) {
        $justifications = array();
        
        switch ($objective) {
            case 'visibilidade':
                $justifications['traffic'] = __('Com SEO bem estruturado, seu site pode aparecer nas primeiras posições do Google para termos relevantes do seu setor, aumentando organicamente o tráfego qualificado.', 'futturu-impact-simulator');
                break;
            case 'vendas':
                $justifications['traffic'] = __('Um site otimizado para conversão, com chamadas claras e jornada do usuário bem definida, maximiza as oportunidades de venda.', 'futturu-impact-simulator');
                break;
            case 'leads':
                $justifications['leads'] = __('Formulários estratégicos, landing pages otimizadas e iscas digitais podem aumentar significativamente sua captação de leads qualificados.', 'futturu-impact-simulator');
                break;
            case 'marca':
                $justifications['traffic'] = __('Um design profissional e consistente fortalece o reconhecimento da sua marca e transmite credibilidade aos visitantes.', 'futturu-impact-simulator');
                break;
            case 'cartao_visitas':
                $justifications['traffic'] = __('Ter um site profissional como cartão de visitas digital expande seu alcance muito além do networking presencial.', 'futturu-impact-simulator');
                break;
        }
        
        // Add general justifications
        $justifications['design'] = sprintf(
            __('Com um design focado em conversão, podemos melhorar sua taxa de conversão em até %d%%.', 'futturu-impact-simulator'),
            round($coefficients['conversion_rate'] * 100)
        );
        
        $justifications['performance'] = __('Com hospedagem de alta performance, seu site carrega rapidamente, melhorando a experiência do usuário e o ranqueamento no Google.', 'futturu-impact-simulator');
        
        return $justifications;
    }
}
