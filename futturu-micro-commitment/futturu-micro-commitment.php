<?php
/**
 * Plugin Name: Micro-Compromissos Guiados Futturu
 * Plugin URI: https://futturu.com.br
 * Description: Engaje visitantes do site com uma sequência interativa de micro-perguntas que conduzem a CTAs relevantes. Sem gamificação, focado em conversão de leads qualificados.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-micro-commitment
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FMC_VERSION', '1.0.0');
define('FMC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FMC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once FMC_PLUGIN_PATH . 'includes/class-fmc-admin.php';
require_once FMC_PLUGIN_PATH . 'includes/class-fmc-frontend.php';
require_once FMC_PLUGIN_PATH . 'includes/class-fmc-data.php';

/**
 * Main Plugin Class
 */
class Futturu_Micro_Commitment {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Initialize admin
        if (is_admin()) {
            FMC_Admin::get_instance();
        }
        
        // Initialize frontend
        FMC_Frontend::get_instance();
        
        // Initialize data handler
        FMC_Data::get_instance();
    }
    
    public function activate() {
        global $wpdb;
        
        // Create database table for responses
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'fmc_responses';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(64) NOT NULL,
            question_id varchar(64) NOT NULL,
            answer text NOT NULL,
            user_ip varchar(45) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY session_id (session_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Initialize default options if they don't exist
        if (!get_option('fmc_questions')) {
            update_option('fmc_questions', $this->get_default_questions());
        }
        
        if (!get_option('fmc_ctas')) {
            update_option('fmc_ctas', $this->get_default_ctas());
        }
        
        if (!get_option('fmc_enabled')) {
            update_option('fmc_enabled', true);
        }
        
        if (!get_option('fmc_track_ip')) {
            update_option('fmc_track_ip', false);
        }
        
        if (!get_option('fmc_rate_limit')) {
            update_option('fmc_rate_limit', 5);
        }
        
        flush_rewrite_rules();
    }
    
    /**
     * Get default questions with practical examples
     */
    private function get_default_questions() {
        return array(
            array(
                'id' => 'q1_objetivo',
                'question' => 'Qual o principal objetivo do seu website hoje?',
                'answers' => array(
                    array('text' => 'Gerar mais vendas online', 'next' => 'q2_vendas'),
                    array('text' => 'Aumentar a visibilidade da marca', 'next' => 'q2_branding'),
                    array('text' => 'Captar leads qualificados', 'next' => 'q2_leads'),
                    array('text' => 'Melhorar o atendimento ao cliente', 'next' => 'q2_atendimento'),
                    array('text' => 'Não tenho um site ainda', 'cta' => 'cta_sem_site'),
                    array('text' => 'Outro objetivo', 'next' => 'q2_outro')
                )
            ),
            array(
                'id' => 'q2_vendas',
                'question' => 'Seu site atual converte bem os visitantes em clientes?',
                'answers' => array(
                    array('text' => 'Sim, mas quero melhorar ainda mais', 'cta' => 'cta_otimizacao_vendas'),
                    array('text' => 'Não, a conversão está baixa', 'cta' => 'cta_auditoria_conversao'),
                    array('text' => 'Não tenho site ou é muito antigo', 'cta' => 'cta_site_novo_vendas')
                )
            ),
            array(
                'id' => 'q2_branding',
                'question' => 'Você já possui identidade visual definida (logo, cores, tipografia)?',
                'answers' => array(
                    array('text' => 'Sim, está tudo definido', 'next' => 'q3_branding_existente'),
                    array('text' => 'Parcialmente, mas precisa de ajustes', 'cta' => 'cta_refresh_branding'),
                    array('text' => 'Não, preciso criar do zero', 'cta' => 'cta_branding_completo')
                )
            ),
            array(
                'id' => 'q3_branding_existente',
                'question' => 'Sua marca está presente de forma consistente em todos os canais digitais?',
                'answers' => array(
                    array('text' => 'Sim, mas quero fortalecer ainda mais', 'cta' => 'cta_posicionamento_digital'),
                    array('text' => 'Não, está inconsistente', 'cta' => 'cta_unificacao_marca')
                )
            ),
            array(
                'id' => 'q2_leads',
                'question' => 'Qual tipo de lead você busca captar?',
                'answers' => array(
                    array('text' => 'Empresas (B2B)', 'next' => 'q3_leads_b2b'),
                    array('text' => 'Consumidores finais (B2C)', 'next' => 'q3_leads_b2c'),
                    array('text' => 'Ambos', 'cta' => 'cta_estrategia_mista')
                )
            ),
            array(
                'id' => 'q3_leads_b2b',
                'question' => 'Qual o ticket médio dos seus clientes B2B?',
                'answers' => array(
                    array('text' => 'Até R$ 5.000', 'cta' => 'cta_leads_b2b_pequeno'),
                    array('text' => 'De R$ 5.000 a R$ 50.000', 'cta' => 'cta_leads_b2b_medio'),
                    array('text' => 'Acima de R$ 50.000', 'cta' => 'cta_leads_b2b_enterprise')
                )
            ),
            array(
                'id' => 'q3_leads_b2c',
                'question' => 'Como você atrai consumidores atualmente?',
                'answers' => array(
                    array('text' => 'Tráfego pago (Google/Facebook Ads)', 'cta' => 'cta_otimizacao_trafego'),
                    array('text' => 'Orgânico (SEO/Redes Sociais)', 'cta' => 'cta_aceleracao_organico'),
                    array('text' => 'Indicação/Boca a boca', 'cta' => 'cta_escalagem_indicacao'),
                    array('text' => 'Não estou atraindo leads', 'cta' => 'cta_estrategia_do_zero')
                )
            ),
            array(
                'id' => 'q2_atendimento',
                'question' => 'Qual canal de atendimento você quer priorizar no site?',
                'answers' => array(
                    array('text' => 'WhatsApp/Chat', 'cta' => 'cta_integracao_whatsapp'),
                    array('text' => 'Formulários de contato', 'cta' => 'cta_formularios_inteligentes'),
                    array('text' => 'FAQ automático/Chatbot', 'cta' => 'cta_chatbot_inteligente'),
                    array('text' => 'Todos integrados', 'cta' => 'cta_omnichannel')
                )
            ),
            array(
                'id' => 'q2_outro',
                'question' => 'Conte-nos mais sobre seu projeto ou necessidade específica:',
                'answers' => array(
                    array('text' => 'Quero falar com um especialista agora', 'cta' => 'cta_especialista'),
                    array('text' => 'Preciso de um orçamento personalizado', 'cta' => 'cta_orcamento'),
                    array('text' => 'Quero ver casos de sucesso primeiro', 'cta' => 'cta_cases_sucesso')
                )
            )
        );
    }
    
    /**
     * Get default CTAs with practical examples
     */
    private function get_default_ctas() {
        return array(
            'cta_sem_site' => array('title' => 'Comece com um website profissional', 'description' => 'Websites focados em resultados para PMEs.', 'button_text' => 'Orçamento gratuito', 'link' => '/contato', 'type' => 'link'),
            'cta_site_novo_vendas' => array('title' => 'Site focado em conversão', 'description' => 'Websites otimizados para vendas.', 'button_text' => 'Quero um site que vende', 'link' => '/websites', 'type' => 'link'),
            'cta_otimizacao_vendas' => array('title' => 'Consultoria de conversão', 'description' => 'Identificamos oportunidades de crescimento.', 'button_text' => 'Agendar consultoria', 'link' => '/consultoria', 'type' => 'link'),
            'cta_auditoria_conversao' => array('title' => 'Auditoria gratuita', 'description' => 'Análise completa de UX e performance.', 'button_text' => 'Solicitar auditoria', 'link' => '/auditoria', 'type' => 'link'),
            'cta_refresh_branding' => array('title' => 'Refresh de marca', 'description' => 'Modernize sua identidade visual.', 'button_text' => 'Conhecer processo', 'link' => '/branding', 'type' => 'link'),
            'cta_branding_completo' => array('title' => 'Branding do zero', 'description' => 'Identidade visual completa.', 'button_text' => 'Ver pacotes', 'link' => '/branding-completo', 'type' => 'link'),
            'cta_posicionamento_digital' => array('title' => 'Posicionamento digital', 'description' => 'Estratégia de conteúdo e SEO.', 'button_text' => 'Falar com estrategista', 'link' => '/estrategia', 'type' => 'link'),
            'cta_unificacao_marca' => array('title' => 'Unificação de marca', 'description' => 'Consistência em todos os canais.', 'button_text' => 'Unificar marca', 'link' => '/unificacao', 'type' => 'link'),
            'cta_leads_b2b_pequeno' => array('title' => 'Leads B2B pequeno ticket', 'description' => 'Volume com nutrição automatizada.', 'button_text' => 'Ver campanhas', 'link' => '/b2b', 'type' => 'link'),
            'cta_leads_b2b_medio' => array('title' => 'Leads B2B médio ticket', 'description' => 'Funil constante de oportunidades.', 'button_text' => 'Análise de funil', 'link' => '/funnel', 'type' => 'link'),
            'cta_leads_b2b_enterprise' => array('title' => 'ABM Enterprise', 'description' => 'Account-based marketing.', 'button_text' => 'Metodologia ABM', 'link' => '/abm', 'type' => 'link'),
            'cta_otimizacao_trafego' => array('title' => 'Otimização de tráfego', 'description' => 'Mais ROI em anúncios.', 'button_text' => 'Análise de campanhas', 'link' => '/trafego', 'type' => 'link'),
            'cta_aceleracao_organico' => array('title' => 'SEO estratégico', 'description' => 'Primeira página do Google.', 'button_text' => 'Plano de SEO', 'link' => '/seo', 'type' => 'link'),
            'cta_escalagem_indicacao' => array('title' => 'Programa de indicações', 'description' => 'Sistema previsível de referrals.', 'button_text' => 'Criar programa', 'link' => '/indicacao', 'type' => 'link'),
            'cta_estrategia_do_zero' => array('title' => 'Estratégia do zero', 'description' => 'Máquina de leads completa.', 'button_text' => 'Começar agora', 'link' => '/estrategia', 'type' => 'link'),
            'cta_estrategia_mista' => array('title' => 'B2B + B2C integrado', 'description' => 'Abordagem híbrida.', 'button_text' => 'Conhecer abordagem', 'link' => '/hibrido', 'type' => 'link'),
            'cta_integracao_whatsapp' => array('title' => 'WhatsApp Business', 'description' => 'Integração completa.', 'button_text' => 'Integrar WhatsApp', 'link' => '/whatsapp', 'type' => 'link'),
            'cta_formularios_inteligentes' => array('title' => 'Formulários inteligentes', 'description' => 'Mais conversão com lógica condicional.', 'button_text' => 'Otimizar formulários', 'link' => '/formularios', 'type' => 'link'),
            'cta_chatbot_inteligente' => array('title' => 'Chatbot com IA', 'description' => 'Automatize 80% do atendimento.', 'button_text' => 'Implementar chatbot', 'link' => '/chatbot', 'type' => 'link'),
            'cta_omnichannel' => array('title' => 'Plataforma omnichannel', 'description' => 'Todos os canais integrados.', 'button_text' => 'Ver plataforma', 'link' => '/omnichannel', 'type' => 'link'),
            'cta_especialista' => array('title' => 'Falar com especialista', 'description' => '15 minutos sem compromisso.', 'button_text' => 'Agendar conversa', 'link' => '/contato', 'type' => 'link'),
            'cta_orcamento' => array('title' => 'Orçamento personalizado', 'description' => 'Resposta em 24h.', 'button_text' => 'Pedir orçamento', 'link' => '/orcamento', 'type' => 'link'),
            'cta_cases_sucesso' => array('title' => 'Cases de sucesso', 'description' => 'Resultados reais.', 'button_text' => 'Ver cases', 'link' => '/cases', 'type' => 'link')
        );
    }
    public function deactivate() {
        // Cleanup if needed
        delete_transient('fmc_rate_limit');
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('futturu-micro-commitment', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Initialize plugin
function futturu_micro_commitment_init() {
    return Futturu_Micro_Commitment::get_instance();
}
add_action('plugins_loaded', 'futturu_micro_commitment_init');
