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
        // Create default options with practical examples
        $default_questions = array(
            // Pergunta 1: Objetivo principal
            array(
                'id' => 'q1_objetivo',
                'question' => 'Qual o principal objetivo do seu website hoje?',
                'answers' => array(
                    array(
                        'text' => 'Gerar mais vendas online',
                        'next' => 'q2_vendas'
                    ),
                    array(
                        'text' => 'Aumentar a visibilidade da marca',
                        'next' => 'q2_branding'
                    ),
                    array(
                        'text' => 'Captar leads qualificados',
                        'next' => 'q2_leads'
                    ),
                    array(
                        'text' => 'Melhorar o atendimento ao cliente',
                        'next' => 'q2_atendimento'
                    ),
                    array(
                        'text' => 'Não tenho um site ainda',
                        'cta' => 'cta_sem_site'
                    ),
                    array(
                        'text' => 'Outro objetivo',
                        'next' => 'q2_outro'
                    )
                )
            ),
            // Ramo: Vendas
            array(
                'id' => 'q2_vendas',
                'question' => 'Seu site atual converte bem os visitantes em clientes?',
                'answers' => array(
                    array(
                        'text' => 'Sim, mas quero melhorar ainda mais',
                        'cta' => 'cta_otimizacao_vendas'
                    ),
                    array(
                        'text' => 'Não, a conversão está baixa',
                        'cta' => 'cta_auditoria_conversao'
                    ),
                    array(
                        'text' => 'Não tenho site ou é muito antigo',
                        'cta' => 'cta_site_novo_vendas'
                    )
                )
            ),
            // Ramo: Branding
            array(
                'id' => 'q2_branding',
                'question' => 'Você já possui identidade visual definida (logo, cores, tipografia)?',
                'answers' => array(
                    array(
                        'text' => 'Sim, está tudo definido',
                        'next' => 'q3_branding_existente'
                    ),
                    array(
                        'text' => 'Parcialmente, mas precisa de ajustes',
                        'cta' => 'cta_refresh_branding'
                    ),
                    array(
                        'text' => 'Não, preciso criar do zero',
                        'cta' => 'cta_branding_completo'
                    )
                )
            ),
            array(
                'id' => 'q3_branding_existente',
                'question' => 'Sua marca está presente de forma consistente em todos os canais digitais?',
                'answers' => array(
                    array(
                        'text' => 'Sim, mas quero fortalecer ainda mais',
                        'cta' => 'cta_posicionamento_digital'
                    ),
                    array(
                        'text' => 'Não, está inconsistente',
                        'cta' => 'cta_unificacao_marca'
                    )
                )
            ),
            // Ramo: Leads
            array(
                'id' => 'q2_leads',
                'question' => 'Qual tipo de lead você busca captar?',
                'answers' => array(
                    array(
                        'text' => 'Empresas (B2B)',
                        'next' => 'q3_leads_b2b'
                    ),
                    array(
                        'text' => 'Consumidores finais (B2C)',
                        'next' => 'q3_leads_b2c'
                    ),
                    array(
                        'text' => 'Ambos',
                        'cta' => 'cta_estrategia_mista'
                    )
                )
            ),
            array(
                'id' => 'q3_leads_b2b',
                'question' => 'Qual o ticket médio dos seus clientes B2B?',
                'answers' => array(
                    array(
                        'text' => 'Até R$ 5.000',
                        'cta' => 'cta_leads_b2b_pequeno'
                    ),
                    array(
                        'text' => 'De R$ 5.000 a R$ 50.000',
                        'cta' => 'cta_leads_b2b_medio'
                    ),
                    array(
                        'text' => 'Acima de R$ 50.000',
                        'cta' => 'cta_leads_b2b_enterprise'
                    )
                )
            ),
            array(
                'id' => 'q3_leads_b2c',
                'question' => 'Como você atrai consumidores atualmente?',
                'answers' => array(
                    array(
                        'text' => 'Tráfego pago (Google/Facebook Ads)',
                        'cta' => 'cta_otimizacao_trafego'
                    ),
                    array(
                        'text' => 'Orgânico (SEO/Redes Sociais)',
                        'cta' => 'cta_aceleracao_organico'
                    ),
                    array(
                        'text' => 'Indicação/Boca a boca',
                        'cta' => 'cta_escalagem_indicacao'
                    ),
                    array(
                        'text' => 'Não estou atraindo leads',
                        'cta' => 'cta_estrategia_do_zero'
                    )
                )
            ),
            // Ramo: Atendimento
            array(
                'id' => 'q2_atendimento',
                'question' => 'Qual canal de atendimento você quer priorizar no site?',
                'answers' => array(
                    array(
                        'text' => 'WhatsApp/Chat',
                        'cta' => 'cta_integracao_whatsapp'
                    ),
                    array(
                        'text' => 'Formulários de contato',
                        'cta' => 'cta_formularios_inteligentes'
                    ),
                    array(
                        'text' => 'FAQ automático/Chatbot',
                        'cta' => 'cta_chatbot_inteligente'
                    ),
                    array(
                        'text' => 'Todos integrados',
                        'cta' => 'cta_omnichannel'
                    )
                )
            ),
            // Ramo: Outro
            array(
                'id' => 'q2_outro',
                'question' => 'Conte-nos mais sobre seu projeto ou necessidade específica:',
                'answers' => array(
                    array(
                        'text' => 'Quero falar com um especialista agora',
                        'cta' => 'cta_especialista'
                    ),
                    array(
                        'text' => 'Preciso de um orçamento personalizado',
                        'cta' => 'cta_orcamento'
                    ),
                    array(
                        'text' => 'Quero ver casos de sucesso primeiro',
                        'cta' => 'cta_cases_sucesso'
                    )
                )
            )
        );
        
        $default_ctas = array(
            // CTAs para quem não tem site
            'cta_sem_site' => array(
                'title' => 'Comece com um website profissional de alta performance',
                'description' => 'Desenvolvemos websites institucionais e de e-commerce focados em resultados para PMEs. Do planejamento à entrega, cuidamos de tudo.',
                'button_text' => 'Solicite um orçamento gratuito',
                'link' => '/contato#orcamento-site',
                'type' => 'link'
            ),
            'cta_site_novo_vendas' => array(
                'title' => 'Crie um site focado em conversão desde o primeiro dia',
                'description' => 'Desenvolvemos websites otimizados para vendas, com UX estratégica e integração completa com suas ferramentas de negócio.',
                'button_text' => 'Quero um site que vende',
                'link' => '/solucoes/websites-vendas',
                'type' => 'link'
            ),
            
            // CTAs para otimização de vendas
            'cta_otimizacao_vendas' => array(
                'title' => 'Leve suas vendas online ao próximo nível',
                'description' => 'Mesmo sites que convertem bem podem melhorar. Nossa consultoria identifica oportunidades ocultas de crescimento.',
                'button_text' => 'Agende uma consultoria estratégica',
                'link' => '/consultoria/conversao',
                'type' => 'link'
            ),
            'cta_auditoria_conversao' => array(
                'title' => 'Descubra por que seu site não converte',
                'description' => 'Receba uma auditoria completa de UX, performance e jornada do cliente. Identificamos os gargalos e propomos soluções práticas.',
                'button_text' => 'Solicite sua auditoria gratuita',
                'link' => '/auditoria-gratuita',
                'type' => 'link'
            ),
            
            // CTAs para branding
            'cta_refresh_branding' => array(
                'title' => 'Atualize sua identidade visual para a era digital',
                'description' => 'Modernizamos sua marca mantendo a essência. Criamos sistemas visuais flexíveis para todos os canais digitais.',
                'button_text' => 'Conheça nosso processo de refresh',
                'link' => '/servicos/refresh-branding',
                'type' => 'link'
            ),
            'cta_branding_completo' => array(
                'title' => 'Construa uma marca memorável do zero',
                'description' => 'Criamos identidades visuais completas que transmitem o valor do seu negócio e conectam emocionalmente com seu público.',
                'button_text' => 'Veja nossos pacotes de branding',
                'link' => '/servicos/branding-completo',
                'type' => 'link'
            ),
            'cta_posicionamento_digital' => array(
                'title' => 'Fortaleça o posicionamento da sua marca online',
                'description' => 'Estratégias de conteúdo, SEO e presença digital para tornar sua marca referência no segmento.',
                'button_text' => 'Fale com nosso time de estratégia',
                'link' => '/estrategia/posicionamento',
                'type' => 'link'
            ),
            'cta_unificacao_marca' => array(
                'title' => 'Unifique sua marca em todos os canais',
                'description' => 'Criamos guidelines completos e implementamos consistência visual em website, redes sociais e materiais corporativos.',
                'button_text' => 'Quero unificar minha marca',
                'link' => '/servicos/unificacao-marca',
                'type' => 'link'
            ),
            
            // CTAs para leads B2B
            'cta_leads_b2b_pequeno' => array(
                'title' => 'Gere leads B2B qualificados mesmo com ticket baixo',
                'description' => 'Estratégias específicas para volumes maiores de leads com processos de nutrição automatizados.',
                'button_text' => 'Veja como funcionam nossas campanhas B2B',
                'link' => '/cases/b2b-pequeno-ticket',
                'type' => 'link'
            ),
            'cta_leads_b2b_medio' => array(
                'title' => 'Escale sua geração de leads B2B com previsibilidade',
                'description' => 'Combinamos inbound marketing, conteúdo estratégico e automação para criar um funil constante de oportunidades.',
                'button_text' => 'Agende uma análise do seu funil',
                'link' => '/consultoria/funnel-b2b',
                'type' => 'link'
            ),
            'cta_leads_b2b_enterprise' => array(
                'title' => 'Gere oportunidades enterprise de alto valor',
                'description' => 'Estratégias account-based marketing (ABM) para engajar decisores e fechar contratos de grande porte.',
                'button_text' => 'Conheça nossa metodologia ABM',
                'link' => '/servicos/abm-enterprise',
                'type' => 'link'
            ),
            
            // CTAs para leads B2C
            'cta_otimizacao_trafego' => array(
                'title' => 'Reduza o custo por lead e aumente o ROI dos anúncios',
                'description' => 'Otimizamos suas campanhas atuais com foco em conversão. Melhoramos qualidade do tráfego e experiência de landing pages.',
                'button_text' => 'Solicite uma análise de campanhas',
                'link' => '/trafego-pago/otimizacao',
                'type' => 'link'
            ),
            'cta_aceleracao_organico' => array(
                'title' => 'Escale seus resultados orgânicos com SEO estratégico',
                'description' => 'Posicionamos seu site nas primeiras posições do Google para termos relevantes do seu negócio.',
                'button_text' => 'Veja nosso plano de SEO',
                'link' => '/seo/aceleracao',
                'type' => 'link'
            ),
            'cta_escalagem_indicacao' => array(
                'title' => 'Transforme indicações em um sistema previsível',
                'description' => 'Criamos programas estruturados de indicação com incentivos e automação para multiplicar seus leads qualificados.',
                'button_text' => 'Quero um programa de indicações',
                'link' => '/estrategia/indicacao-premium',
                'type' => 'link'
            ),
            'cta_estrategia_do_zero' => array(
                'title' => 'Construa sua máquina de geração de leads do zero',
                'description' => 'Desenvolvemos uma estratégia completa: atração, conversão e nutrição. Você foca em vender, nós trazemos os leads.',
                'button_text' => 'Quero começar agora',
                'link' => '/consultoria/estrategia-completa',
                'type' => 'link'
            ),
            'cta_estrategia_mista' => array(
                'title' => 'Estratégia integrada B2B + B2C para máximo resultado',
                'description' => 'Segmentamos mensagens e canais para atender ambos os públicos sem diluir esforços ou orçamento.',
                'button_text' => 'Fale com um estrategista',
                'link' => '/consultoria/estrategia-mista',
                'type' => 'link'
            ),
            
            // CTAs para atendimento
            'cta_integracao_whatsapp' => array(
                'title' => 'Integre WhatsApp profissional ao seu site',
                'description' => 'Botões inteligentes, mensagens automáticas e roteamento para o setor correto. Aumente o engajamento em tempo real.',
                'button_text' => 'Quero integrar WhatsApp',
                'link' => '/integracoes/whatsapp-business',
                'type' => 'link'
            ),
            'cta_formularios_inteligentes' => array(
                'title' => 'Formulários que realmente convertem',
                'description' => 'Desenvolvemos formulários estratégicos com validação, segmentação e integração direta com seu CRM.',
                'button_text' => 'Otimize meus formulários',
                'link' => '/ux/formularios-conversao',
                'type' => 'link'
            ),
            'cta_chatbot_inteligente' => array(
                'title' => 'Automate 80% do atendimento com IA',
                'description' => 'Chatbots inteligentes que qualificam leads, respondem dúvidas frequentes e agendam reuniões automaticamente.',
                'button_text' => 'Conheça nossos chatbots',
                'link' => '/automacao/chatbot-ia',
                'type' => 'link'
            ),
            'cta_omnichannel' => array(
                'title' => 'Centralize todos os canais de atendimento',
                'description' => 'Integramos WhatsApp, chat, formulário, telefone e redes sociais em uma única plataforma de gestão.',
                'button_text' => 'Quero atendimento omnichannel',
                'link' => '/plataforma/omnichannel',
                'type' => 'link'
            ),
            
            // CTAs gerais
            'cta_especialista' => array(
                'title' => 'Fale diretamente com um especialista Futturu',
                'description' => 'Nossos consultores seniores estão prontos para entender sua necessidade específica e propor a melhor solução.',
                'button_text' => 'Agende uma conversa de 15 minutos',
                'link' => '/contato/especialista',
                'type' => 'link'
            ),
            'cta_orcamento' => array(
                'title' => 'Receba um orçamento personalizado em até 24h',
                'description' => 'Analisamos seu projeto e enviamos uma proposta detalhada com escopo, prazos e investimento.',
                'button_text' => 'Solicitar orçamento agora',
                'link' => '/contato/orcamento',
                'type' => 'link'
            ),
            'cta_cases_sucesso' => array(
                'title' => 'Conheça cases reais de transformação digital',
                'description' => 'Veja como ajudamos empresas similares à sua a alcançar resultados extraordinários.',
                'button_text' => 'Ver cases de sucesso',
                'link' => '/cases',
                'type' => 'link'
            )
        );
        
        if (!get_option('fmc_questions')) {
            update_option('fmc_questions', $default_questions);
        }
        
        if (!get_option('fmc_ctas')) {
            update_option('fmc_ctas', $default_ctas);
        }
        
        // Create database table for responses
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmc_responses';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(64) NOT NULL,
            question_id varchar(64) NOT NULL,
            answer text NOT NULL,
            path_taken text DEFAULT '',
            user_ip varchar(45) DEFAULT '',
            user_agent text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default settings
        update_option('fmc_enabled', true);
        update_option('fmc_track_ip', false);
        update_option('fmc_rate_limit', 5); // submissions per minute per IP
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
