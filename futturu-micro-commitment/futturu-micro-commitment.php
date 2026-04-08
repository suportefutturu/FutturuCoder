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
        // Create default options
        $default_questions = array(
            array(
                'id' => 'q1',
                'question' => 'Qual o principal objetivo do seu website hoje?',
                'answers' => array(
                    array(
                        'text' => 'Gerar mais vendas',
                        'next' => 'q2_sales'
                    ),
                    array(
                        'text' => 'Aumentar a visibilidade da marca',
                        'next' => 'q2_branding'
                    ),
                    array(
                        'text' => 'Captação de Leads',
                        'next' => 'q2_leads'
                    ),
                    array(
                        'text' => 'Não tenho um site ainda',
                        'cta' => 'cta_no_website'
                    ),
                    array(
                        'text' => 'Outro',
                        'next' => 'q2_other'
                    )
                )
            ),
            array(
                'id' => 'q2_sales',
                'question' => 'Seu site atual converte bem?',
                'answers' => array(
                    array(
                        'text' => 'Sim',
                        'cta' => 'cta_optimization'
                    ),
                    array(
                        'text' => 'Não',
                        'cta' => 'cta_conversion'
                    )
                )
            ),
            array(
                'id' => 'q2_branding',
                'question' => 'Você já possui identidade visual definida?',
                'answers' => array(
                    array(
                        'text' => 'Sim',
                        'cta' => 'cta_branding_existing'
                    ),
                    array(
                        'text' => 'Não',
                        'cta' => 'cta_branding_new'
                    )
                )
            ),
            array(
                'id' => 'q2_leads',
                'question' => 'Qual tipo de lead você busca?',
                'answers' => array(
                    array(
                        'text' => 'B2B (Empresas)',
                        'cta' => 'cta_b2b_leads'
                    ),
                    array(
                        'text' => 'B2C (Consumidores)',
                        'cta' => 'cta_b2c_leads'
                    )
                )
            ),
            array(
                'id' => 'q2_other',
                'question' => 'Conte-nos mais sobre seu projeto',
                'answers' => array(
                    array(
                        'text' => 'Falar com especialista',
                        'cta' => 'cta_specialist'
                    )
                )
            )
        );
        
        $default_ctas = array(
            'cta_no_website' => array(
                'title' => 'Comece com um website profissional',
                'description' => 'Desenvolvemos websites institucionais e de alta performance para PMEs.',
                'button_text' => 'Solicite um orçamento gratuito',
                'link' => '#contato',
                'type' => 'link'
            ),
            'cta_conversion' => array(
                'title' => 'Aumente suas conversões',
                'description' => 'Descubra como otimizar seu site para converter mais visitantes em clientes.',
                'button_text' => 'Solicite sua auditoria de performance gratuita',
                'link' => '#auditoria',
                'type' => 'link'
            ),
            'cta_optimization' => array(
                'title' => 'Leve suas vendas ao próximo nível',
                'description' => 'Mesmo sites que convertem bem podem melhorar. Veja quanto pode crescer.',
                'button_text' => 'Agende uma consultoria estratégica',
                'link' => '#consultoria',
                'type' => 'link'
            ),
            'cta_branding_existing' => array(
                'title' => 'Fortaleça sua presença digital',
                'description' => 'Com sua identidade definida, podemos criar experiências digitais impactantes.',
                'button_text' => 'Fale com nosso time de design',
                'link' => '#design',
                'type' => 'link'
            ),
            'cta_branding_new' => array(
                'title' => 'Construa uma marca memorável',
                'description' => 'Criamos identidades visuais completas que transmitem o valor do seu negócio.',
                'button_text' => 'Conheça nossos pacotes de branding',
                'link' => '#branding',
                'type' => 'link'
            ),
            'cta_b2b_leads' => array(
                'title' => 'Gere leads qualificados B2B',
                'description' => 'Estratégias específicas para atrair e converter empresas.',
                'button_text' => 'Veja casos de sucesso B2B',
                'link' => '#cases-b2b',
                'type' => 'link'
            ),
            'cta_b2c_leads' => array(
                'title' => 'Escale sua captação B2C',
                'description' => 'Soluções comprovadas para gerar volume e qualidade em leads de consumo.',
                'button_text' => 'Descubra nossa metodologia',
                'link' => '#metodologia',
                'type' => 'link'
            ),
            'cta_specialist' => array(
                'title' => 'Fale com um especialista',
                'description' => 'Nossos consultores estão prontos para entender sua necessidade específica.',
                'button_text' => 'Agende uma conversa',
                'link' => '#contato',
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
