<?php
/**
 * Public class for Futturu Impact Simulator
 */
if (!defined('ABSPATH')) {
    exit;
}

class FIS_Public {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }
    
    /**
     * Enqueue public assets
     */
    public function enqueue_public_assets() {
        // Only load on pages with the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'futturu_impact_simulator')) {
            // Chart.js from CDN
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
            
            // Plugin CSS
            wp_enqueue_style('fis-public-css', FIS_PLUGIN_URL . 'assets/css/public.css', array(), FIS_VERSION);
            
            // Plugin JS
            wp_enqueue_script('fis-public-js', FIS_PLUGIN_URL . 'assets/js/public.js', array('jquery', 'chartjs'), FIS_VERSION, true);
            
            // Localize script
            $settings = get_option('fis_settings');
            wp_localize_script('fis-public-js', 'fisData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fis_nonce'),
                'settings' => array(
                    'ctaText' => isset($settings['fis_cta_text']) ? $settings['fis_cta_text'] : 'Falar com um Especialista da Futturu',
                    'ctaEmail' => isset($settings['fis_cta_email']) ? $settings['fis_cta_email'] : 'suporte@futturu.com.br',
                    'disclaimer' => isset($settings['fis_disclaimer']) ? $settings['fis_disclaimer'] : fis_get_default_messages()['disclaimer'],
                    'messages' => isset($settings['messages']) ? $settings['messages'] : fis_get_default_messages()
                ),
                'businessTypes' => $this->get_business_types(),
                'revenueRanges' => $this->get_revenue_ranges(),
                'targetAudiences' => $this->get_target_audiences(),
                'objectives' => $this->get_objectives()
            ));
        }
    }
    
    /**
     * Get business types
     */
    public function get_business_types() {
        return array(
            'restaurante' => __('Restaurante', 'futturu-impact-simulator'),
            'cafe' => __('Café', 'futturu-impact-simulator'),
            'floricultura' => __('Floricultura', 'futturu-impact-simulator'),
            'advocacia' => __('Advocacia', 'futturu-impact-simulator'),
            'clinica_medica' => __('Clínica Médica', 'futturu-impact-simulator'),
            'consultoria' => __('Consultoria', 'futturu-impact-simulator'),
            'escola' => __('Escola', 'futturu-impact-simulator'),
            'oficina_mecanica' => __('Oficina Mecânica', 'futturu-impact-simulator'),
            'aluguel_carros' => __('Aluguel de Carros', 'futturu-impact-simulator'),
            'loja_roupas' => __('Loja de Roupas', 'futturu-impact-simulator'),
            'ecommerce' => __('E-commerce', 'futturu-impact-simulator'),
            'servico_profissional' => __('Serviço Profissional', 'futturu-impact-simulator'),
            'outro' => __('Outro', 'futturu-impact-simulator')
        );
    }
    
    /**
     * Get revenue ranges
     */
    public function get_revenue_ranges() {
        return array(
            'low' => __('Até R$ 10.000/mês', 'futturu-impact-simulator'),
            'medium' => __('R$ 10.001 - R$ 25.000/mês', 'futturu-impact-simulator'),
            'high' => __('R$ 25.001 - R$ 50.000/mês', 'futturu-impact-simulator'),
            'very_high' => __('Acima de R$ 50.000/mês', 'futturu-impact-simulator')
        );
    }
    
    /**
     * Get target audiences
     */
    public function get_target_audiences() {
        return array(
            'b2c' => __('Consumidores Finais (B2C)', 'futturu-impact-simulator'),
            'b2b' => __('Empresas (B2B)', 'futturu-impact-simulator'),
            'both' => __('Ambos', 'futturu-impact-simulator')
        );
    }
    
    /**
     * Get objectives
     */
    public function get_objectives() {
        return array(
            'visibilidade' => __('Aumentar Visibilidade', 'futturu-impact-simulator'),
            'vendas' => __('Gerar Mais Vendas', 'futturu-impact-simulator'),
            'leads' => __('Captação de Leads', 'futturu-impact-simulator'),
            'marca' => __('Fortalecer Marca', 'futturu-impact-simulator'),
            'cartao_visitas' => __('Substituir Cartão de Visitas Físico', 'futturu-impact-simulator')
        );
    }
}
