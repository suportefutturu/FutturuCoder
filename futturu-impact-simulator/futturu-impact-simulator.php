<?php
/**
 * Plugin Name: Simulador de Impacto Online Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Converta visitantes em leads qualificados demonstrando o impacto estimado de um site profissional no negócio do usuário.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-impact-simulator
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FIS_VERSION', '1.0.0');
define('FIS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FIS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FIS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once FIS_PLUGIN_DIR . 'includes/class-fis-admin.php';
require_once FIS_PLUGIN_DIR . 'includes/class-fis-public.php';
require_once FIS_PLUGIN_DIR . 'includes/class-fis-calculator.php';
require_once FIS_PLUGIN_DIR . 'includes/class-fis-ajax.php';

/**
 * Initialize the plugin
 */
function fis_init() {
    // Load text domain
    load_plugin_textdomain('futturu-impact-simulator', false, dirname(FIS_PLUGIN_BASENAME) . '/languages');
    
    // Initialize admin
    if (is_admin()) {
        new FIS_Admin();
    }
    
    // Initialize public
    new FIS_Public();
    
    // Initialize AJAX handlers
    new FIS_Ajax();
}
add_action('plugins_loaded', 'fis_init');

/**
 * Activation hook
 */
function fis_activate() {
    // Set default options
    $default_options = array(
        'fis_enabled' => true,
        'fis_shortcode' => '[futturu_impact_simulator]',
        'fis_cta_text' => 'Falar com um Especialista da Futturu',
        'fis_cta_email' => 'suporte@futturu.com.br',
        'fis_disclaimer' => 'Esta projeção é baseada em benchmarks de negócios semelhantes e práticas recomendadas de presença digital.',
        'benchmarks' => fis_get_default_benchmarks(),
        'base_values' => fis_get_default_base_values(),
        'messages' => fis_get_default_messages()
    );
    
    if (!get_option('fis_settings')) {
        add_option('fis_settings', $default_options);
    }
    
    // Create contact form submission post type capability
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'fis_activate');

/**
 * Deactivation hook
 */
function fis_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'fis_deactivate');

/**
 * Get default benchmarks matrix
 */
function fis_get_default_benchmarks() {
    return array(
        'restaurante' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.03, 'lead_mult' => 1.8),
                'vendas' => array('traffic_mult' => 3.0, 'conversion_rate' => 0.04, 'lead_mult' => 2.2),
                'leads' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.05, 'lead_mult' => 2.5),
                'marca' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.02, 'lead_mult' => 1.5),
                'cartao_visitas' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.02, 'lead_mult' => 1.5),
                'vendas' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.03, 'lead_mult' => 1.8),
                'leads' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'marca' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.3),
                'cartao_visitas' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.01, 'lead_mult' => 1.2)
            )
        ),
        'cafe' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'vendas' => array('traffic_mult' => 2.8, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'leads' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.04, 'lead_mult' => 2.2),
                'marca' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.02, 'lead_mult' => 1.4),
                'cartao_visitas' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.2)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.3),
                'vendas' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'leads' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.8),
                'marca' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.02, 'lead_mult' => 1.2),
                'cartao_visitas' => array('traffic_mult' => 1.4, 'conversion_rate' => 0.01, 'lead_mult' => 1.1)
            )
        ),
        'floricultura' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'vendas' => array('traffic_mult' => 2.7, 'conversion_rate' => 0.04, 'lead_mult' => 1.9),
                'leads' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.04, 'lead_mult' => 2.1),
                'marca' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.02, 'lead_mult' => 1.3),
                'cartao_visitas' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.02, 'lead_mult' => 1.1)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.02, 'lead_mult' => 1.4),
                'vendas' => array('traffic_mult' => 2.1, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'leads' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.04, 'lead_mult' => 1.9),
                'marca' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.3),
                'cartao_visitas' => array('traffic_mult' => 1.4, 'conversion_rate' => 0.01, 'lead_mult' => 1.1)
            )
        ),
        'advocacia' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'vendas' => array('traffic_mult' => 2.4, 'conversion_rate' => 0.05, 'lead_mult' => 2.3),
                'leads' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.06, 'lead_mult' => 2.5),
                'marca' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'cartao_visitas' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.03, 'lead_mult' => 1.8),
                'vendas' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.04, 'lead_mult' => 2.1),
                'leads' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.05, 'lead_mult' => 2.4),
                'marca' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.02, 'lead_mult' => 1.5),
                'cartao_visitas' => array('traffic_mult' => 1.4, 'conversion_rate' => 0.02, 'lead_mult' => 1.2)
            )
        ),
        'clinica_medica' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.05, 'lead_mult' => 2.2),
                'vendas' => array('traffic_mult' => 2.7, 'conversion_rate' => 0.06, 'lead_mult' => 2.5),
                'leads' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.07, 'lead_mult' => 2.7),
                'marca' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.04, 'lead_mult' => 1.8),
                'cartao_visitas' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.03, 'lead_mult' => 1.5)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'vendas' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.04, 'lead_mult' => 1.9),
                'leads' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.05, 'lead_mult' => 2.2),
                'marca' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.03, 'lead_mult' => 1.5),
                'cartao_visitas' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            )
        ),
        'consultoria' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.1, 'conversion_rate' => 0.04, 'lead_mult' => 2.1),
                'vendas' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.05, 'lead_mult' => 2.4),
                'leads' => array('traffic_mult' => 2.6, 'conversion_rate' => 0.06, 'lead_mult' => 2.7),
                'marca' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'cartao_visitas' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.4)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'vendas' => array('traffic_mult' => 2.6, 'conversion_rate' => 0.05, 'lead_mult' => 2.5),
                'leads' => array('traffic_mult' => 2.8, 'conversion_rate' => 0.06, 'lead_mult' => 2.8),
                'marca' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'cartao_visitas' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            )
        ),
        'escola' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.4, 'conversion_rate' => 0.04, 'lead_mult' => 2.1),
                'vendas' => array('traffic_mult' => 2.8, 'conversion_rate' => 0.05, 'lead_mult' => 2.4),
                'leads' => array('traffic_mult' => 2.6, 'conversion_rate' => 0.06, 'lead_mult' => 2.6),
                'marca' => array('traffic_mult' => 2.1, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'cartao_visitas' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.5)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'vendas' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'leads' => array('traffic_mult' => 2.4, 'conversion_rate' => 0.05, 'lead_mult' => 2.3),
                'marca' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.5),
                'cartao_visitas' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            )
        ),
        'oficina_mecanica' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.04, 'lead_mult' => 1.9),
                'vendas' => array('traffic_mult' => 2.6, 'conversion_rate' => 0.05, 'lead_mult' => 2.2),
                'leads' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.05, 'lead_mult' => 2.3),
                'marca' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.03, 'lead_mult' => 1.5),
                'cartao_visitas' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.03, 'lead_mult' => 1.3)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.03, 'lead_mult' => 1.5),
                'vendas' => array('traffic_mult' => 2.1, 'conversion_rate' => 0.04, 'lead_mult' => 1.8),
                'leads' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.04, 'lead_mult' => 1.9),
                'marca' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.3),
                'cartao_visitas' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.02, 'lead_mult' => 1.2)
            )
        ),
        'aluguel_carros' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'vendas' => array('traffic_mult' => 3.0, 'conversion_rate' => 0.05, 'lead_mult' => 2.3),
                'leads' => array('traffic_mult' => 2.4, 'conversion_rate' => 0.05, 'lead_mult' => 2.4),
                'marca' => array('traffic_mult' => 2.1, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'cartao_visitas' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.4)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'vendas' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'leads' => array('traffic_mult' => 2.4, 'conversion_rate' => 0.05, 'lead_mult' => 2.2),
                'marca' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.5),
                'cartao_visitas' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            )
        ),
        'loja_roupas' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.8, 'conversion_rate' => 0.03, 'lead_mult' => 2.0),
                'vendas' => array('traffic_mult' => 3.5, 'conversion_rate' => 0.04, 'lead_mult' => 2.5),
                'leads' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.04, 'lead_mult' => 2.4),
                'marca' => array('traffic_mult' => 2.4, 'conversion_rate' => 0.03, 'lead_mult' => 1.8),
                'cartao_visitas' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.02, 'lead_mult' => 1.5)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'vendas' => array('traffic_mult' => 2.6, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'leads' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.04, 'lead_mult' => 2.1),
                'marca' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'cartao_visitas' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            )
        ),
        'ecommerce' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 3.0, 'conversion_rate' => 0.03, 'lead_mult' => 2.2),
                'vendas' => array('traffic_mult' => 4.0, 'conversion_rate' => 0.04, 'lead_mult' => 2.8),
                'leads' => array('traffic_mult' => 2.8, 'conversion_rate' => 0.04, 'lead_mult' => 2.6),
                'marca' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.03, 'lead_mult' => 1.9),
                'cartao_visitas' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.02, 'lead_mult' => 1.6)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.03, 'lead_mult' => 1.8),
                'vendas' => array('traffic_mult' => 3.0, 'conversion_rate' => 0.04, 'lead_mult' => 2.3),
                'leads' => array('traffic_mult' => 2.8, 'conversion_rate' => 0.05, 'lead_mult' => 2.5),
                'marca' => array('traffic_mult' => 2.1, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'cartao_visitas' => array('traffic_mult' => 1.9, 'conversion_rate' => 0.02, 'lead_mult' => 1.4)
            )
        ),
        'servico_profissional' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'vendas' => array('traffic_mult' => 2.6, 'conversion_rate' => 0.05, 'lead_mult' => 2.3),
                'leads' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.06, 'lead_mult' => 2.5),
                'marca' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'cartao_visitas' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.03, 'lead_mult' => 1.4)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.04, 'lead_mult' => 1.9),
                'vendas' => array('traffic_mult' => 2.5, 'conversion_rate' => 0.05, 'lead_mult' => 2.3),
                'leads' => array('traffic_mult' => 2.7, 'conversion_rate' => 0.06, 'lead_mult' => 2.6),
                'marca' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'cartao_visitas' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.3)
            )
        ),
        'outro' => array(
            'b2c' => array(
                'visibilidade' => array('traffic_mult' => 2.0, 'conversion_rate' => 0.03, 'lead_mult' => 1.7),
                'vendas' => array('traffic_mult' => 2.4, 'conversion_rate' => 0.04, 'lead_mult' => 2.0),
                'leads' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.04, 'lead_mult' => 2.2),
                'marca' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.02, 'lead_mult' => 1.4),
                'cartao_visitas' => array('traffic_mult' => 1.6, 'conversion_rate' => 0.02, 'lead_mult' => 1.2)
            ),
            'b2b' => array(
                'visibilidade' => array('traffic_mult' => 1.8, 'conversion_rate' => 0.03, 'lead_mult' => 1.6),
                'vendas' => array('traffic_mult' => 2.2, 'conversion_rate' => 0.04, 'lead_mult' => 1.9),
                'leads' => array('traffic_mult' => 2.3, 'conversion_rate' => 0.05, 'lead_mult' => 2.1),
                'marca' => array('traffic_mult' => 1.7, 'conversion_rate' => 0.02, 'lead_mult' => 1.4),
                'cartao_visitas' => array('traffic_mult' => 1.5, 'conversion_rate' => 0.02, 'lead_mult' => 1.2)
            )
        )
    );
}

/**
 * Get default base values
 */
function fis_get_default_base_values() {
    return array(
        'base_traffic' => 500,
        'base_conversion_rate' => 0.02,
        'avg_ticket_low' => 50,
        'avg_ticket_medium' => 150,
        'avg_ticket_high' => 500,
        'seo_improvement_factor' => 2.5,
        'design_improvement_factor' => 1.8,
        'hosting_performance_factor' => 1.3
    );
}

/**
 * Get default messages
 */
function fis_get_default_messages() {
    return array(
        'intro_title' => 'Descubra o Potencial do Seu Negócio Online',
        'intro_subtitle' => 'Simule o impacto que um site profissional pode ter no seu faturamento',
        'step1_title' => 'Informações do Seu Negócio',
        'step2_title' => 'Analisando Potencial...',
        'step3_title' => 'Seu Relatório de Impacto',
        'calculate_button' => 'Simular Impacto',
        'back_button' => 'Voltar',
        'current_situation' => 'Sua Situação Atual (Estimativa)',
        'with_futturu' => 'Com um Site Profissional Futturu (Projeção)',
        'visits_label' => 'Visitas/Mês',
        'leads_label' => 'Leads/Mês',
        'conversions_label' => 'Vendas/Conversões/Mês',
        'revenue_label' => 'Faturamento Adicional Anual',
        'cta_title' => 'Impressionado com o potencial?',
        'cta_subtitle' => 'Converse com um especialista da Futturu e descubra como transformar essa projeção em realidade.',
        'contact_form_title' => 'Solicite uma Consultoria Gratuita',
        'success_message' => 'Obrigado! Entraremos em contato em breve.',
        'error_message' => 'Ocorreu um erro. Por favor, tente novamente.',
        'disclaimer' => 'Esta projeção é baseada em benchmarks de negócios semelhantes e práticas recomendadas de presença digital.'
    );
}

/**
 * Get business types
 */
function fis_get_business_types() {
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
function fis_get_revenue_ranges() {
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
function fis_get_target_audiences() {
    return array(
        'b2c' => __('Consumidores Finais (B2C)', 'futturu-impact-simulator'),
        'b2b' => __('Empresas (B2B)', 'futturu-impact-simulator'),
        'both' => __('Ambos', 'futturu-impact-simulator')
    );
}

/**
 * Get objectives
 */
function fis_get_objectives() {
    return array(
        'visibilidade' => __('Aumentar Visibilidade', 'futturu-impact-simulator'),
        'vendas' => __('Gerar Mais Vendas', 'futturu-impact-simulator'),
        'leads' => __('Captação de Leads', 'futturu-impact-simulator'),
        'marca' => __('Fortalecer Marca', 'futturu-impact-simulator'),
        'cartao_visitas' => __('Substituir Cartão de Visitas Físico', 'futturu-impact-simulator')
    );
}

/**
 * Add shortcode
 */
function fis_shortcode($atts) {
    $atts = shortcode_atts(array(
        'style' => 'default'
    ), $atts, 'futturu_impact_simulator');
    
    ob_start();
    include FIS_PLUGIN_DIR . 'templates/simulator.php';
    return ob_get_clean();
}
add_shortcode('futturu_impact_simulator', 'fis_shortcode');
