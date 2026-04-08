<?php
/**
 * Plugin Name: Auditoria Rápida de Website Futturu
 * Plugin URI: https://futturu.com.br/auditoria-rapida
 * Description: Realiza uma auditoria automática local de websites WordPress, gerando relatório imediato sobre Velocidade Percebida, SEO Básico e Segurança Superficial.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-audit
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FUTTURU_AUDIT_VERSION', '1.0.0');
define('FUTTURU_AUDIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FUTTURU_AUDIT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Futuru_Quick_Audit {
    
    private static $instance = null;
    private $audit_results = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_shortcode('futturu_audit', array($this, 'render_shortcode'));
        add_action('wp_ajax_futturu_run_audit', array($this, 'ajax_run_audit'));
        add_action('wp_ajax_nopriv_futturu_run_audit', array($this, 'ajax_run_audit'));
        
        // Remove generator tag for security (optional - can be disabled)
        remove_action('wp_head', 'wp_generator');
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Auditoria Futturu', 'futturu-audit'),
            __('Auditoria Futturu', 'futturu-audit'),
            'manage_options',
            'futturu-audit',
            array($this, 'render_admin_page'),
            'dashicons-analytics',
            80
        );
        
        add_submenu_page(
            'futturu-audit',
            __('Nova Auditoria', 'futturu-audit'),
            __('Nova Auditoria', 'futturu-audit'),
            'manage_options',
            'futturu-audit',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'futturu-audit',
            __('Histórico', 'futturu-audit'),
            __('Histórico', 'futturu-audit'),
            'manage_options',
            'futturu-audit-history',
            array($this, 'render_history_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'futturu-audit') === false) {
            return;
        }
        
        wp_enqueue_style(
            'futturu-audit-admin',
            FUTTURU_AUDIT_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FUTTURU_AUDIT_VERSION
        );
        
        wp_enqueue_script(
            'futturu-audit-admin',
            FUTTURU_AUDIT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            FUTTURU_AUDIT_VERSION,
            true
        );
        
        wp_localize_script('futturu-audit-admin', 'futturuAudit', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_audit_nonce'),
            'runningText' => __('Executando auditoria...', 'futturu-audit'),
            'completeText' => __('Auditoria completa!', 'futturu-audit')
        ));
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_cta' => 'true',
            'theme' => 'light'
        ), $atts);
        
        ob_start();
        ?>
        <div class="futturu-audit-shortcode futturu-audit-<?php echo esc_attr($atts['theme']); ?>">
            <div class="futturu-audit-container">
                <div class="futturu-audit-header">
                    <h2><?php _e('Auditoria Rápida de Website', 'futturu-audit'); ?></h2>
                    <p><?php _e('Analise a performance, SEO e segurança do seu site em instantes.', 'futturu-audit'); ?></p>
                </div>
                
                <button type="button" class="futturu-audit-btn" id="futturu-run-audit-shortcode">
                    <?php _e('Executar Auditoria', 'futturu-audit'); ?>
                </button>
                
                <div class="futturu-audit-progress" id="futturu-progress-shortcode" style="display:none;">
                    <div class="futturu-spinner"></div>
                    <p><?php _e('Analisando seu website...', 'futturu-audit'); ?></p>
                </div>
                
                <div class="futturu-audit-results" id="futturu-results-shortcode"></div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#futturu-run-audit-shortcode').on('click', function() {
                var btn = $(this);
                var progress = $('#futturu-progress-shortcode');
                var results = $('#futturu-results-shortcode');
                
                btn.prop('disabled', true);
                progress.show();
                results.html('');
                
                $.post(futturuAudit.ajaxUrl, {
                    action: 'futturu_run_audit',
                    nonce: futturuAudit.nonce
                }, function(response) {
                    progress.hide();
                    btn.prop('disabled', false);
                    
                    if (response.success) {
                        results.html(response.data.html);
                    } else {
                        results.html('<div class="futturu-error">' + response.data.message + '</div>');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap futturu-audit-wrap">
            <h1><?php _e('Auditoria Rápida de Website Futturu', 'futturu-audit'); ?></h1>
            
            <div class="futturu-audit-card">
                <div class="futturu-audit-header">
                    <h2><?php _e('Executar Nova Auditoria', 'futturu-audit'); ?></h2>
                    <p><?php _e('Clique no botão abaixo para analisar seu site atual. A auditoria verificará:', 'futturu-audit'); ?></p>
                    <ul class="futturu-checklist">
                        <li><?php _e('✓ Velocidade Percebida e Otimização', 'futturu-audit'); ?></li>
                        <li><?php _e('✓ SEO Básico e Estrutura', 'futturu-audit'); ?></li>
                        <li><?php _e('✓ Segurança Superficial', 'futturu-audit'); ?></li>
                    </ul>
                </div>
                
                <button type="button" class="button button-primary button-large" id="futturu-run-audit">
                    <?php _e('Executar Auditoria Agora', 'futturu-audit'); ?>
                </button>
                
                <div class="futturu-audit-progress" id="futturu-progress" style="display:none;">
                    <div class="futturu-spinner"></div>
                    <p><?php _e('Analisando seu website... Isso pode levar alguns segundos.', 'futturu-audit'); ?></p>
                </div>
                
                <div class="futturu-audit-results" id="futturu-results"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render history page
     */
    public function render_history_page() {
        $history = get_option('futturu_audit_history', array());
        
        ?>
        <div class="wrap futturu-audit-wrap">
            <h1><?php _e('Histórico de Auditorias', 'futturu-audit'); ?></h1>
            
            <?php if (empty($history)) : ?>
                <div class="notice notice-info">
                    <p><?php _e('Nenhuma auditoria realizada ainda. Execute sua primeira auditoria na página "Nova Auditoria".', 'futturu-audit'); ?></p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Data', 'futturu-audit'); ?></th>
                            <th><?php _e('Pontuação Geral', 'futturu-audit'); ?></th>
                            <th><?php _e('Velocidade', 'futturu-audit'); ?></th>
                            <th><?php _e('SEO', 'futturu-audit'); ?></th>
                            <th><?php _e('Segurança', 'futturu-audit'); ?></th>
                            <th><?php _e('Ações', 'futturu-audit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($history) as $index => $audit) : ?>
                            <tr>
                                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $audit['timestamp']); ?></td>
                                <td><strong><?php echo esc_html($audit['overall_score']); ?>/100</strong></td>
                                <td><?php echo esc_html($audit['speed_score']); ?>/100</td>
                                <td><?php echo esc_html($audit['seo_score']); ?>/100</td>
                                <td><?php echo esc_html($audit['security_score']); ?>/100</td>
                                <td>
                                    <button type="button" class="button button-small" onclick="futturuShowHistory(<?php echo $index; ?>)">
                                        <?php _e('Ver Detalhes', 'futturu-audit'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div id="futturu-history-detail" style="display:none; margin-top:20px;"></div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for running audit
     */
    public function ajax_run_audit() {
        check_ajax_referer('futturu_audit_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permissão negada.', 'futturu-audit')));
        }
        
        // Run the audit
        $results = $this->run_audit();
        
        // Save to history
        $this->save_to_history($results);
        
        // Generate HTML report
        $html = $this->generate_report_html($results);
        
        wp_send_json_success(array('html' => $html, 'results' => $results));
    }
    
    /**
     * Run the complete audit
     */
    public function run_audit() {
        $results = array(
            'timestamp' => time(),
            'site_url' => home_url(),
            'speed' => array(),
            'seo' => array(),
            'security' => array(),
            'scores' => array()
        );
        
        // Get homepage HTML
        $html = $this->get_homepage_html();
        
        if (!$html) {
            $results['error'] = __('Não foi possível obter o código-fonte da página inicial.', 'futturu-audit');
            return $results;
        }
        
        // Run speed analysis
        $results['speed'] = $this->analyze_speed($html);
        
        // Run SEO analysis
        $results['seo'] = $this->analyze_seo($html);
        
        // Run security analysis
        $results['security'] = $this->analyze_security($html);
        
        // Calculate scores
        $results['scores'] = $this->calculate_scores($results);
        
        return $results;
    }
    
    /**
     * Get homepage HTML
     */
    private function get_homepage_html() {
        $url = home_url();
        $args = array(
            'timeout' => 15,
            'user-agent' => 'Futturu Audit Bot/' . FUTTURU_AUDIT_VERSION,
            'sslverify' => false
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        return wp_remote_retrieve_body($response);
    }
    
    /**
     * Analyze speed factors
     */
    private function analyze_speed($html) {
        $checks = array();
        
        // Check for title tag
        preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $title_matches);
        $has_title = !empty($title_matches[1]) && strlen(trim($title_matches[1])) > 0;
        $checks[] = array(
            'name' => __('Tag Title Presente', 'futturu-audit'),
            'passed' => $has_title,
            'description' => $has_title 
                ? __('A tag title está presente e não vazia.', 'futturu-audit')
                : __('A tag title está ausente ou vazia. Isso afeta a velocidade percebida e SEO.', 'futturu-audit'),
            'recommendation' => $has_title ? '' : __('Adicione uma tag <title> descritiva e única para cada página.', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check for meta description
        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $desc_matches);
        $has_description = !empty($desc_matches[1]) && strlen(trim($desc_matches[1])) > 0;
        $checks[] = array(
            'name' => __('Meta Description Presente', 'futturu-audit'),
            'passed' => $has_description,
            'description' => $has_description
                ? __('A meta description está presente.', 'futturu-audit')
                : __('A meta description está ausente ou vazia.', 'futturu-audit'),
            'recommendation' => $has_description ? '' : __('Adicione uma meta description única e atraente (150-160 caracteres).', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check for lazy loading images
        preg_match_all('/<img[^>]*>/i', $html, $img_matches);
        $total_images = count($img_matches[0]);
        $lazy_images = 0;
        $missing_dimensions = 0;
        
        foreach ($img_matches[0] as $img_tag) {
            if (strpos($img_tag, 'loading="lazy"') !== false || strpos($img_tag, "loading='lazy'") !== false) {
                $lazy_images++;
            }
            if (strpos($img_tag, 'width=') === false && strpos($img_tag, 'height=') === false) {
                $missing_dimensions++;
            }
        }
        
        $lazy_percentage = $total_images > 0 ? ($lazy_images / $total_images) * 100 : 100;
        $checks[] = array(
            'name' => __('Imagens com Lazy Loading', 'futturu-audit'),
            'passed' => $lazy_percentage >= 50 || $total_images == 0,
            'description' => sprintf(
                __('%d de %d imagens usam lazy loading (%.0f%%).', 'futturu-audit'),
                $lazy_images,
                $total_images,
                $lazy_percentage
            ),
            'recommendation' => $lazy_percentage >= 50 
                ? '' 
                : __('Adicione loading="lazy" às tags <img> que estão abaixo da dobra para melhorar o carregamento inicial.', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check for image dimensions
        $checks[] = array(
            'name' => __('Dimensões de Imagem Definidas', 'futturu-audit'),
            'passed' => $missing_dimensions == 0 || $total_images == 0,
            'description' => $missing_dimensions == 0
                ? __('Todas as imagens possuem width e height definidos.', 'futturu-audit')
                : sprintf(__('%d imagens sem dimensões definidas (causa Layout Shift).', 'futturu-audit'), $missing_dimensions),
            'recommendation' => $missing_dimensions == 0
                ? ''
                : __('Sempre defina width e height nas tags <img> para evitar Cumulative Layout Shift (CLS).', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check for srcset
        $has_srcset = strpos($html, 'srcset=') !== false;
        $checks[] = array(
            'name' => __('Uso de srcset para Imagens Responsivas', 'futturu-audit'),
            'passed' => $has_srcset,
            'description' => $has_srcset
                ? __('O site utiliza srcset para imagens responsivas.', 'futturu-audit')
                : __('Nenhum uso de srcset detectado.', 'futturu-audit'),
            'recommendation' => $has_srcset
                ? ''
                : __('Implemente srcset nas imagens para servir tamanhos apropriados para cada dispositivo.', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check for preload
        $has_preload = strpos($html, 'rel="preload"') !== false;
        $checks[] = array(
            'name' => __('Recursos Pré-carregados (preload)', 'futturu-audit'),
            'passed' => $has_preload,
            'description' => $has_preload
                ? __('Existem recursos sendo pré-carregados.', 'futturu-audit')
                : __('Nenhum recurso pré-carregado detectado.', 'futturu-audit'),
            'recommendation' => $has_preload
                ? ''
                : __('Use <link rel="preload"> para recursos críticos como fontes e CSS importante.', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check for render-blocking scripts in head
        preg_match('/<head[^>]*>(.*?)<\/head>/is', $html, $head_matches);
        $head_content = !empty($head_matches[1]) ? $head_matches[1] : '';
        $blocking_scripts = 0;
        
        preg_match_all('/<script[^>]*src=["\'][^"\']+["\'][^>]*>/i', $head_content, $script_matches);
        foreach ($script_matches[0] as $script_tag) {
            if (strpos($script_tag, 'defer') === false && strpos($script_tag, 'async') === false) {
                $blocking_scripts++;
            }
        }
        
        $checks[] = array(
            'name' => __('Scripts Render-Blocking no Head', 'futturu-audit'),
            'passed' => $blocking_scripts == 0,
            'description' => $blocking_scripts == 0
                ? __('Nenhum script bloqueante detectado no <head>.', 'futturu-audit')
                : sprintf(__('%d scripts sem defer/async no <head> podem atrasar a renderização.', 'futturu-audit'), $blocking_scripts),
            'recommendation' => $blocking_scripts == 0
                ? ''
                : __('Adicione defer ou async aos scripts no <head>, ou mova-os para antes de </body>.', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check HTML size
        $html_size = strlen($html);
        $html_size_kb = round($html_size / 1024, 2);
        $checks[] = array(
            'name' => __('Tamanho do HTML', 'futturu-audit'),
            'passed' => $html_size_kb < 100,
            'description' => sprintf(__('O HTML da página tem %.2f KB.', 'futturu-audit'), $html_size_kb),
            'recommendation' => $html_size_kb < 100
                ? ''
                : __('HTML muito grande (>100KB) pode indicar excesso de conteúdo ou código não otimizado. Considere minificar e remover elementos desnecessários.', 'futturu-audit'),
            'weight' => 15
        );
        
        return $checks;
    }
    
    /**
     * Analyze SEO factors
     */
    private function analyze_seo($html) {
        $checks = array();
        
        // Check H1 tag
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1_matches);
        $h1_count = count($h1_matches[0]);
        $checks[] = array(
            'name' => __('Tag H1 Única', 'futturu-audit'),
            'passed' => $h1_count == 1,
            'description' => $h1_count == 1
                ? __('Existe exatamente uma tag <h1> na página.', 'futturu-audit')
                : sprintf(__('Encontradas %d tags <h1>. O ideal é apenas uma por página.', 'futturu-audit'), $h1_count),
            'recommendation' => $h1_count == 1
                ? ''
                : __('Use apenas uma tag <h1> por página, contendo a palavra-chave principal.', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check heading hierarchy
        preg_match_all('/<h[1-6][^>]*>/i', $html, $heading_matches);
        $has_headings = count($heading_matches[0]) > 0;
        $checks[] = array(
            'name' => __('Estrutura de Cabeçalhos', 'futturu-audit'),
            'passed' => $has_headings,
            'description' => $has_headings
                ? __('A página possui estrutura de cabeçalhos (<h1>-<h6>).', 'futturu-audit')
                : __('Nenhum cabeçalho (<h1>-<h6>) detectado.', 'futturu-audit'),
            'recommendation' => $has_headings
                ? ''
                : __('Utilize cabeçalhos hierárquicos para estruturar o conteúdo da página.', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check for JSON-LD Schema
        $has_schema = strpos($html, 'application/ld+json') !== false || strpos($html, 'itemscope') !== false;
        $checks[] = array(
            'name' => __('Schema.org (Dados Estruturados)', 'futturu-audit'),
            'passed' => $has_schema,
            'description' => $has_schema
                ? __('Dados estruturados Schema.org detectados.', 'futturu-audit')
                : __('Nenhum dado estruturado Schema.org detectado.', 'futturu-audit'),
            'recommendation' => $has_schema
                ? ''
                : __('Implemente Schema.org (JSON-LD) para ajudar mecanismos de busca a entenderem seu conteúdo.', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check permalink structure
        $permalink_structure = get_option('permalink_structure');
        $has_friendly_urls = !empty($permalink_structure) && strpos($permalink_structure, '?p=') === false;
        $checks[] = array(
            'name' => __('URLs Amigáveis', 'futturu-audit'),
            'passed' => $has_friendly_urls,
            'description' => $has_friendly_urls
                ? __('URLs amigáveis estão configuradas.', 'futturu-audit')
                : __('URLs amigáveis não estão configuradas (usando ?p=123).', 'futturu-audit'),
            'recommendation' => $has_friendly_urls
                ? ''
                : __('Configure URLs amigáveis em Configurações > Links Permanentes.', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check robots.txt
        $robots_url = home_url('/robots.txt');
        $robots_response = wp_remote_get($robots_url, array('timeout' => 5, 'sslverify' => false));
        $has_robots = !is_wp_error($robots_response) && wp_remote_retrieve_response_code($robots_response) == 200;
        $checks[] = array(
            'name' => __('Arquivo robots.txt', 'futturu-audit'),
            'passed' => $has_robots,
            'description' => $has_robots
                ? __('O arquivo robots.txt está acessível.', 'futturu-audit')
                : __('O arquivo robots.txt não está acessível.', 'futturu-audit'),
            'recommendation' => $has_robots
                ? ''
                : __('Crie um arquivo robots.txt na raiz do seu site para orientar crawlers.', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check sitemap.xml
        $sitemap_url = home_url('/sitemap.xml');
        $sitemap_response = wp_remote_get($sitemap_url, array('timeout' => 5, 'sslverify' => false));
        $has_sitemap = !is_wp_error($sitemap_response) && wp_remote_retrieve_response_code($sitemap_response) == 200;
        
        // Also check for common sitemap plugin sitemaps
        if (!$has_sitemap) {
            $sitemap_url = home_url('/sitemap_index.xml');
            $sitemap_response = wp_remote_get($sitemap_url, array('timeout' => 5, 'sslverify' => false));
            $has_sitemap = !is_wp_error($sitemap_response) && wp_remote_retrieve_response_code($sitemap_response) == 200;
        }
        
        $checks[] = array(
            'name' => __('Sitemap XML', 'futturu-audit'),
            'passed' => $has_sitemap,
            'description' => $has_sitemap
                ? __('O sitemap.xml está acessível.', 'futturu-audit')
                : __('O sitemap.xml não está acessível.', 'futturu-audit'),
            'recommendation' => $has_sitemap
                ? ''
                : __('Gere um sitemap XML usando um plugin de SEO e submeta ao Google Search Console.', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check for generic anchor text
        preg_match_all('/<a[^>]*>(.*?)<\/a>/i', $html, $link_matches);
        $generic_anchors = 0;
        $generic_terms = array('clique aqui', 'saiba mais', 'leia mais', 'aqui', 'more', 'click here');
        
        foreach ($link_matches[1] as $anchor_text) {
            $anchor_lower = strtolower(trim(strip_tags($anchor_text)));
            if (in_array($anchor_lower, $generic_terms)) {
                $generic_anchors++;
            }
        }
        
        $checks[] = array(
            'name' => __('Texto Âncora Descritivo', 'futturu-audit'),
            'passed' => $generic_anchors < 5,
            'description' => $generic_anchors < 5
                ? sprintf(__('Poucos links genéricos encontrados (%d).', 'futturu-audit'), $generic_anchors)
                : sprintf(__('%d links com texto âncora genérico detectados.', 'futturu-audit'), $generic_anchors),
            'recommendation' => $generic_anchors < 5
                ? ''
                : __('Use textos âncora descritivos que indiquem o destino do link, evitando "clique aqui" ou "saiba mais".', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check meta description length
        preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $desc_matches);
        if (!empty($desc_matches[1])) {
            $desc_length = strlen($desc_matches[1]);
            $checks[] = array(
                'name' => __('Comprimento da Meta Description', 'futturu-audit'),
                'passed' => $desc_length >= 120 && $desc_length <= 160,
                'description' => sprintf(__('Meta description com %d caracteres.', 'futturu-audit'), $desc_length),
                'recommendation' => ($desc_length >= 120 && $desc_length <= 160)
                    ? ''
                    : sprintf(__('Ideal: 120-160 caracteres. Atualmente: %d caracteres.', 'futturu-audit'), $desc_length),
                'weight' => 10
            );
        }
        
        return $checks;
    }
    
    /**
     * Analyze security factors
     */
    private function analyze_security($html) {
        $checks = array();
        
        // Check for WordPress generator tag
        $has_generator = strpos($html, '<meta name="generator"') !== false || strpos($html, "content=\"WordPress") !== false;
        $checks[] = array(
            'name' => __('Remoção da Tag Generator', 'futturu-audit'),
            'passed' => !$has_generator,
            'description' => !$has_generator
                ? __('A tag generator do WordPress foi removida.', 'futturu-audit')
                : __('A tag generator do WordPress está exposta (revela versão).', 'futturu-audit'),
            'recommendation' => !$has_generator
                ? ''
                : __('Remova a tag generator para não expor a versão do WordPress. Adicione ao functions.php: remove_action(\'wp_head\', \'wp_generator\');', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check for default admin user
        global $wpdb;
        $admin_exists = $wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE user_login = 'admin'");
        $checks[] = array(
            'name' => __('Usuário "admin" Padrão', 'futturu-audit'),
            'passed' => !$admin_exists,
            'description' => !$admin_exists
                ? __('O usuário padrão "admin" não existe.', 'futturu-audit')
                : __('ALERTA: O usuário padrão "admin" ainda existe!', 'futturu-audit'),
            'recommendation' => !$admin_exists
                ? ''
                : __('Remova ou renomeie o usuário "admin" imediatamente. Este é um alvo comum de ataques.', 'futturu-audit'),
            'weight' => 25
        );
        
        // Check for inactive plugins
        $inactive_plugins = 0;
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            if (!in_array($plugin_file, $active_plugins)) {
                $inactive_plugins++;
            }
        }
        
        $checks[] = array(
            'name' => __('Plugins Inativos', 'futturu-audit'),
            'passed' => $inactive_plugins == 0,
            'description' => $inactive_plugins == 0
                ? __('Nenhum plugin inativo encontrado.', 'futturu-audit')
                : sprintf(__('%d plugins inativos detectados.', 'futturu-audit'), $inactive_plugins),
            'recommendation' => $inactive_plugins == 0
                ? ''
                : __('Desative e remova plugins não utilizados para reduzir superfície de ataque.', 'futturu-audit'),
            'weight' => 15
        );
        
        // Check for outdated plugins
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/update.php';
        
        $outdated_plugins = 0;
        $plugin_updates = get_site_transient('update_plugins');
        
        if (!empty($plugin_updates->response)) {
            $outdated_plugins = count($plugin_updates->response);
        }
        
        $checks[] = array(
            'name' => __('Plugins Desatualizados', 'futturu-audit'),
            'passed' => $outdated_plugins == 0,
            'description' => $outdated_plugins == 0
                ? __('Todos os plugins estão atualizados.', 'futturu-audit')
                : sprintf(__('%d plugins com atualizações disponíveis.', 'futturu-audit'), $outdated_plugins),
            'recommendation' => $outdated_plugins == 0
                ? ''
                : __('Atualize todos os plugins imediatamente para corrigir vulnerabilidades conhecidas.', 'futturu-audit'),
            'weight' => 20
        );
        
        // Check for SSL/HTTPS
        $is_https = strpos(home_url(), 'https://') === 0;
        $checks[] = array(
            'name' => __('HTTPS/SSL Ativo', 'futturu-audit'),
            'passed' => $is_https,
            'description' => $is_https
                ? __('O site está usando HTTPS.', 'futturu-audit')
                : __('ALERTA: O site NÃO está usando HTTPS!', 'futturu-audit'),
            'recommendation' => $is_https
                ? ''
                : __('Instale um certificado SSL imediatamente. Use Let\'s Encrypt ou similar para HTTPS gratuito.', 'futturu-audit'),
            'weight' => 25
        );
        
        // Check for debug mode
        $debug_mode = defined('WP_DEBUG') && WP_DEBUG;
        $checks[] = array(
            'name' => __('Modo Debug Desativado', 'futturu-audit'),
            'passed' => !$debug_mode,
            'description' => !$debug_mode
                ? __('O modo debug do WordPress está desativado.', 'futturu-audit')
                : __('ALERTA: WP_DEBUG está ativado! Isso pode expor informações sensíveis.', 'futturu-audit'),
            'recommendation' => !$debug_mode
                ? ''
                : __('Desative WP_DEBUG em produção. Edite wp-config.php e defina: define(\'WP_DEBUG\', false);', 'futturu-audit'),
            'weight' => 20
        );
        
        // Check for XML-RPC
        $xmlrpc_url = home_url('/xmlrpc.php');
        $xmlrpc_response = wp_remote_get($xmlrpc_url, array('timeout' => 5, 'sslverify' => false));
        $xmlrpc_accessible = !is_wp_error($xmlrpc_response) && wp_remote_retrieve_response_code($xmlrpc_response) == 200;
        
        $checks[] = array(
            'name' => __('XML-RPC', 'futturu-audit'),
            'passed' => !$xmlrpc_accessible,
            'description' => !$xmlrpc_accessible
                ? __('O endpoint XML-RPC não está acessível.', 'futturu-audit')
                : __('O endpoint XML-RPC está acessível (pode ser vetor de ataque).', 'futturu-audit'),
            'recommendation' => !$xmlrpc_accessible
                ? ''
                : __('Considere desativar XML-RPC se não estiver usando. Adicione ao .htaccess ou use plugin de segurança.', 'futturu-audit'),
            'weight' => 10
        );
        
        // Check for directory listing prevention
        $uploads_dir = wp_upload_dir();
        $uploads_url = $uploads_dir['baseurl'] . '/';
        $uploads_response = wp_remote_get($uploads_url, array('timeout' => 5, 'sslverify' => false));
        $directory_listing = wp_remote_retrieve_response_code($uploads_response) == 200 && 
                             strpos(wp_remote_retrieve_body($uploads_response), 'Index of') !== false;
        
        $checks[] = array(
            'name' => __('Listagem de Diretórios', 'futturu-audit'),
            'passed' => !$directory_listing,
            'description' => !$directory_listing
                ? __('A listagem de diretórios parece estar desativada.', 'futturu-audit')
                : __('Possível listagem de diretórios habilitada.', 'futturu-audit'),
            'recommendation' => !$directory_listing
                ? ''
                : __('Desative a listagem de diretórios adicionando "Options -Indexes" ao .htaccess.', 'futturu-audit'),
            'weight' => 10
        );
        
        return $checks;
    }
    
    /**
     * Calculate scores
     */
    private function calculate_scores($results) {
        $scores = array();
        
        foreach (array('speed', 'seo', 'security') as $category) {
            if (empty($results[$category])) {
                $scores[$category] = 0;
                continue;
            }
            
            $total_weight = 0;
            $earned_points = 0;
            
            foreach ($results[$category] as $check) {
                $weight = isset($check['weight']) ? $check['weight'] : 10;
                $total_weight += $weight;
                
                if ($check['passed']) {
                    $earned_points += $weight;
                }
            }
            
            $scores[$category] = $total_weight > 0 ? round(($earned_points / $total_weight) * 100) : 0;
        }
        
        // Overall score (weighted average)
        $scores['overall'] = round(
            ($scores['speed'] * 0.35 + $scores['seo'] * 0.35 + $scores['security'] * 0.30)
        );
        
        return $scores;
    }
    
    /**
     * Generate HTML report
     */
    public function generate_report_html($results) {
        ob_start();
        ?>
        <div class="futturu-report">
            <div class="futturu-report-header">
                <h3><?php _e('Relatório de Auditoria', 'futturu-audit'); ?></h3>
                <p class="futturu-report-date"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $results['timestamp']); ?></p>
                <p class="futturu-report-url"><?php echo esc_url($results['site_url']); ?></p>
            </div>
            
            <?php if (!empty($results['error'])) : ?>
                <div class="futturu-error">
                    <strong><?php _e('Erro:', 'futturu-audit'); ?></strong> <?php echo esc_html($results['error']); ?>
                </div>
            <?php else : ?>
                <!-- Score Cards -->
                <div class="futturu-score-cards">
                    <div class="futturu-score-card futturu-overall">
                        <div class="futturu-score-value"><?php echo $results['scores']['overall']; ?></div>
                        <div class="futturu-score-label"><?php _e('Geral', 'futturu-audit'); ?></div>
                    </div>
                    <div class="futturu-score-card futturu-speed">
                        <div class="futturu-score-value"><?php echo $results['scores']['speed']; ?></div>
                        <div class="futturu-score-label"><?php _e('Velocidade', 'futturu-audit'); ?></div>
                    </div>
                    <div class="futturu-score-card futturu-seo">
                        <div class="futturu-score-value"><?php echo $results['scores']['seo']; ?></div>
                        <div class="futturu-score-label"><?php _e('SEO', 'futturu-audit'); ?></div>
                    </div>
                    <div class="futturu-score-card futturu-security">
                        <div class="futturu-score-value"><?php echo $results['scores']['security']; ?></div>
                        <div class="futturu-score-label"><?php _e('Segurança', 'futturu-audit'); ?></div>
                    </div>
                </div>
                
                <!-- Detailed Results -->
                <div class="futturu-detailed-results">
                    <!-- Speed Section -->
                    <div class="futturu-section">
                        <h4 class="futturu-section-title">
                            <span class="futturu-icon">⚡</span> <?php _e('Velocidade Percebida', 'futturu-audit'); ?>
                        </h4>
                        <div class="futturu-items">
                            <?php foreach ($results['speed'] as $check) : ?>
                                <div class="futturu-item futturu-<?php echo $check['passed'] ? 'pass' : 'fail'; ?>">
                                    <div class="futturu-item-header">
                                        <span class="futturu-status-icon"><?php echo $check['passed'] ? '✓' : '✗'; ?></span>
                                        <strong><?php echo esc_html($check['name']); ?></strong>
                                    </div>
                                    <div class="futturu-item-content">
                                        <p><?php echo esc_html($check['description']); ?></p>
                                        <?php if (!empty($check['recommendation'])) : ?>
                                            <div class="futturu-recommendation">
                                                <strong><?php _e('Recomendação:', 'futturu-audit'); ?></strong>
                                                <?php echo esc_html($check['recommendation']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- SEO Section -->
                    <div class="futturu-section">
                        <h4 class="futturu-section-title">
                            <span class="futturu-icon">🔍</span> <?php _e('SEO Básico', 'futturu-audit'); ?>
                        </h4>
                        <div class="futturu-items">
                            <?php foreach ($results['seo'] as $check) : ?>
                                <div class="futturu-item futturu-<?php echo $check['passed'] ? 'pass' : 'fail'; ?>">
                                    <div class="futturu-item-header">
                                        <span class="futturu-status-icon"><?php echo $check['passed'] ? '✓' : '✗'; ?></span>
                                        <strong><?php echo esc_html($check['name']); ?></strong>
                                    </div>
                                    <div class="futturu-item-content">
                                        <p><?php echo esc_html($check['description']); ?></p>
                                        <?php if (!empty($check['recommendation'])) : ?>
                                            <div class="futturu-recommendation">
                                                <strong><?php _e('Recomendação:', 'futturu-audit'); ?></strong>
                                                <?php echo esc_html($check['recommendation']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Security Section -->
                    <div class="futturu-section">
                        <h4 class="futturu-section-title">
                            <span class="futturu-icon">🔒</span> <?php _e('Segurança Superficial', 'futturu-audit'); ?>
                        </h4>
                        <div class="futturu-items">
                            <?php foreach ($results['security'] as $check) : ?>
                                <div class="futturu-item futturu-<?php echo $check['passed'] ? 'pass' : 'fail'; ?>">
                                    <div class="futturu-item-header">
                                        <span class="futturu-status-icon"><?php echo $check['passed'] ? '✓' : '✗'; ?></span>
                                        <strong><?php echo esc_html($check['name']); ?></strong>
                                    </div>
                                    <div class="futturu-item-content">
                                        <p><?php echo esc_html($check['description']); ?></p>
                                        <?php if (!empty($check['recommendation'])) : ?>
                                            <div class="futturu-recommendation">
                                                <strong><?php _e('Recomendação:', 'futturu-audit'); ?></strong>
                                                <?php echo esc_html($check['recommendation']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- CTA Section -->
                <div class="futturu-cta">
                    <div class="futturu-cta-content">
                        <h3><?php _e('Leve seu site para o próximo nível!', 'futturu-audit'); ?></h3>
                        <p><?php _e('Esta foi uma análise rápida e superficial. Descubra o verdadeiro potencial do seu site com uma <strong>auditoria completa e personalizada da Futturu</strong>.', 'futturu-audit'); ?></p>
                        <ul class="futturu-cta-benefits">
                            <li><?php _e('✓ Análise profunda de backend e banco de dados', 'futturu-audit'); ?></li>
                            <li><?php _e('✓ Identificação de oportunidades ocultas de otimização', 'futturu-audit'); ?></li>
                            <li><?php _e('✓ Relatório detalhado com plano de ação personalizado', 'futturu-audit'); ?></li>
                            <li><?php _e('✓ Consultoria especializada em SEO, segurança e performance', 'futturu-audit'); ?></li>
                            <li><?php _e('✓ Acompanhamento e suporte na implementação', 'futturu-audit'); ?></li>
                        </ul>
                        <p class="futturu-cta-disclaimer"><?php _e('Corrija os problemas identificados e veja melhorias reais em ranqueamento, conversões e segurança.', 'futturu-audit'); ?></p>
                        <a href="https://futturu.com.br/contato" target="_blank" rel="noopener" class="futturu-cta-button">
                            <?php _e('Solicitar Auditoria Completa →', 'futturu-audit'); ?>
                        </a>
                    </div>
                </div>
                
                <!-- Disclaimer -->
                <div class="futturu-disclaimer">
                    <p><?php _e('<strong>Nota:</strong> Esta é uma análise inicial automatizada. Uma avaliação manual profissional pode revelar questões adicionais não detectadas por ferramentas automáticas.', 'futturu-audit'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Save audit to history
     */
    private function save_to_history($results) {
        $history = get_option('futturu_audit_history', array());
        
        // Keep only last 10 audits
        if (count($history) >= 10) {
            array_shift($history);
        }
        
        $history_entry = array(
            'timestamp' => $results['timestamp'],
            'site_url' => $results['site_url'],
            'overall_score' => $results['scores']['overall'],
            'speed_score' => $results['scores']['speed'],
            'seo_score' => $results['scores']['seo'],
            'security_score' => $results['scores']['security'],
            'results' => $results
        );
        
        $history[] = $history_entry;
        update_option('futturu_audit_history', $history, false);
    }
}

// Initialize plugin
function futturu_quick_audit_init() {
    return Futuru_Quick_Audit::get_instance();
}

add_action('plugins_loaded', 'futturu_quick_audit_init');
