<?php
/**
 * Plugin Name: Auditoria Rápida Futturu (Versão Lead Magnet)
 * Description: Ferramenta de auditoria externa para visitantes analisarem seus próprios sites e gerarem leads para a Futturu.
 * Version: 2.0
 * Author: Futturu
 * Text Domain: futturu-audit
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Futturu_External_Audit {

    private $target_url;
    private $html_content;

    public function __construct() {
        add_shortcode('futturu_audit', [$this, 'render_audit_form']);
        add_action('wp_ajax_futturu_run_external_audit', [$this, 'ajax_run_audit']);
        add_action('wp_ajax_nopriv_futturu_run_external_audit', [$this, 'ajax_run_audit']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('futturu-audit-css', plugins_url('assets/css/admin.css', __FILE__), [], '2.0');
        wp_enqueue_script('futturu-audit-js', plugins_url('assets/js/admin.js', __FILE__), ['jquery'], '2.0', true);
        
        wp_localize_script('futturu-audit-js', 'futturuAuditAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('futturu_audit_nonce')
        ]);
    }

    public function render_audit_form($atts) {
        $a = shortcode_atts([
            'title' => 'Auditoria Gratuita do Seu Site',
            'cta_text' => 'Quero minha consultoria gratuita',
            'theme' => 'light'
        ], $atts);

        ob_start();
        ?>
        <div class="futturu-audit-wrapper <?php echo esc_attr($a['theme']); ?>">
            <div class="futturu-audit-card">
                <div class="futturu-header">
                    <h2><?php echo esc_html($a['title']); ?></h2>
                    <p>Descubra problemas de Velocidade, SEO e Segurança em segundos.</p>
                </div>

                <div id="futturu-input-section">
                    <form id="futturu-audit-form" onsubmit="return false;">
                        <div class="futturu-form-group">
                            <label for="futturu_site_url">Digite a URL do seu site:</label>
                            <input type="url" id="futturu_site_url" name="site_url" placeholder="https://www.seusite.com.br" required pattern="https?://.+">
                            <small>Ex: https://www.suaempresa.com.br</small>
                        </div>
                        <button type="submit" id="futturu-submit-btn" class="futturu-btn-primary">
                            <span class="btn-text">Analisar Site Agora</span>
                            <span class="btn-loader" style="display:none;">Analisando...</span>
                        </button>
                    </form>
                    <div id="futturu-error-msg" class="futturu-error" style="display:none;"></div>
                </div>

                <div id="futturu-loading" style="display:none;" class="futturu-loading-container">
                    <div class="futturu-spinner"></div>
                    <p>Escaneando código-fonte e configurações...</p>
                </div>

                <div id="futturu-results" style="display:none;">
                    <div class="futturu-score-overview">
                        <div class="score-card speed">
                            <span class="score-number" id="score-speed">0</span>
                            <span class="score-label">Velocidade</span>
                        </div>
                        <div class="score-card seo">
                            <span class="score-number" id="score-seo">0</span>
                            <span class="score-label">SEO</span>
                        </div>
                        <div class="score-card security">
                            <span class="score-number" id="score-security">0</span>
                            <span class="score-label">Segurança</span>
                        </div>
                    </div>

                    <div class="futturu-detailed-report">
                        <h3>Detalhamento da Análise</h3>
                        <div id="futturu-report-content"></div>
                    </div>

                    <div class="futturu-cta-box">
                        <h3>🚀 Leve seu site para o próximo nível</h3>
                        <p>Esta foi uma análise automatizada superficial. Nossos especialistas podem identificar gargalos profundos no servidor, banco de dados e arquitetura que robôs não veem.</p>
                        <ul class="futturu-benefits-list">
                            <li>✅ Plano de ação personalizado</li>
                            <li>✅ Correção de vulnerabilidades críticas</li>
                            <li>✅ Otimização avançada de Core Web Vitals</li>
                        </ul>
                        <a href="/contato" class="futturu-btn-cta"><?php echo esc_html($a['cta_text']); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function ajax_run_audit() {
        check_ajax_referer('futturu_audit_nonce', 'nonce');

        $url = isset($_POST['site_url']) ? sanitize_text_field($_POST['site_url']) : '';

        if (empty($url)) {
            wp_send_json_error(['message' => 'Por favor, informe uma URL válida.']);
        }

        if (!$this->is_safe_url($url)) {
            wp_send_json_error(['message' => 'URL inválida ou acesso a redes internas não permitido.']);
        }

        $this->target_url = esc_url_raw($url);

        $response = wp_remote_get($this->target_url, [
            'timeout' => 15,
            'user-agent' => 'Futturu Audit Bot (WordPress Plugin)',
            'sslverify' => false
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Não foi possível acessar o site. Verifique se a URL está correta e se o site está online.']);
        }

        $this->html_content = wp_remote_retrieve_body($response);
        
        if (empty($this->html_content)) {
            wp_send_json_error(['message' => 'O site não retornou conteúdo HTML válido.']);
        }

        $speed_checks = $this->analyze_speed();
        $seo_checks = $this->analyze_seo();
        $security_checks = $this->analyze_security();

        $scores = [
            'speed' => $this->calculate_score($speed_checks),
            'seo' => $this->calculate_score($seo_checks),
            'security' => $this->calculate_score($security_checks)
        ];

        $overall_score = round(($scores['speed'] + $scores['seo'] + $scores['security']) / 3);

        wp_send_json_success([
            'scores' => $scores,
            'overall' => $overall_score,
            'details' => [
                'speed' => $speed_checks,
                'seo' => $seo_checks,
                'security' => $security_checks
            ]
        ]);
    }

    private function is_safe_url($url) {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) return false;
        
        $host = $parsed['host'];
        $ip = gethostbyname($host);

        $private_ips = [
            '/^10\./',
            '/^172\.(1[6-9]|2[0-9]|3[0-1])\./',
            '/^192\.168\./',
            '/^127\./',
            '/^0\.0\.0\.0/',
            '/^::1/',
            '/^fc00:/i',
            '/^fe80:/i'
        ];

        foreach ($private_ips as $pattern) {
            if (preg_match($pattern, $ip)) {
                return false;
            }
        }

        return filter_var($url, FILTER_VALIDATE_URL) && 
               (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0);
    }

    private function analyze_speed() {
        $checks = [];
        $html = $this->html_content;

        preg_match('/<title>(.*?)<\/title>/si', $html, $title_matches);
        $has_title = !empty($title_matches[1]);
        $checks[] = [
            'name' => 'Tag Title Presente',
            'pass' => $has_title,
            'msg' => $has_title ? 'Title tag encontrada.' : 'Falta a tag <title>. Isso é crítico para SEO.',
            'fix' => 'Adicione uma tag <title> descritiva no <head>.'
        ];

        preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/si', $html, $desc_matches);
        $has_desc = !empty($desc_matches[1]);
        $checks[] = [
            'name' => 'Meta Description',
            'pass' => $has_desc,
            'msg' => $has_desc ? 'Meta description encontrada.' : 'Sem meta description. Seus resultados no Google ficarão menos atraentes.',
            'fix' => 'Adicione <meta name="description" content="...">'
        ];

        preg_match_all('/<img[^>]+>/i', $html, $images);
        $missing_dims = 0;
        foreach ($images[0] as $img) {
            if (!preg_match('/width=["\']/i', $img) || !preg_match('/height=["\']/i', $img)) {
                $missing_dims++;
            }
        }
        $checks[] = [
            'name' => 'Dimensões de Imagem',
            'pass' => ($missing_dims == 0),
            'msg' => ($missing_dims == 0) ? 'Todas as imagens têm dimensões definidas.' : "$missing_dims imagens sem width/height.",
            'fix' => 'Defina width e height em todas as tags <img> para evitar Layout Shift.'
        ];

        $lazy_count = substr_count(strtolower($html), 'loading="lazy"');
        $total_imgs = count($images[0]);
        $needs_lazy = ($total_imgs > 3 && $lazy_count === 0);
        
        $checks[] = [
            'name' => 'Lazy Loading',
            'pass' => !$needs_lazy,
            'msg' => !$needs_lazy ? 'Lazy loading OK ou poucas imagens.' : 'Muitas imagens sem lazy loading.',
            'fix' => 'Adicione loading="lazy" em imagens off-screen.'
        ];

        preg_match_all('/<head>(.*?)<\/head>/si', $html, $head_matches);
        $head_content = !empty($head_matches[1]) ? $head_matches[1][0] : '';
        $blocking_scripts = 0;
        if (!empty($head_content)) {
            preg_match_all('/<script(?![^>]*async)(?![^>]*defer)[^>]*src=["\'][^"\']+["\'][^>]*>/i', $head_content, $blocking);
            $blocking_scripts = count($blocking[0]);
        }

        $checks[] = [
            'name' => 'Scripts Bloqueantes',
            'pass' => ($blocking_scripts == 0),
            'msg' => ($blocking_scripts == 0) ? 'Nenhum script bloqueante no head.' : "$blocking_scripts scripts podem atrasar a renderização.",
            'fix' => 'Mova scripts para o footer ou use defer/async.'
        ];

        return $checks;
    }

    private function analyze_seo() {
        $checks = [];
        $html = $this->html_content;

        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/si', $html, $h1_matches);
        $h1_count = count($h1_matches[0]);
        $checks[] = [
            'name' => 'Tag H1 Única',
            'pass' => ($h1_count === 1),
            'msg' => ($h1_count === 1) ? 'Estrutura de H1 perfeita.' : "Encontrados $h1_count tags H1.",
            'fix' => 'Garanta exatamente uma tag <h1> por página.'
        ];

        $has_schema = (strpos($html, 'application/ld+json') !== false);
        $checks[] = [
            'name' => 'Dados Estruturados (Schema)',
            'pass' => $has_schema,
            'msg' => $has_schema ? 'Schema.org detectado.' : 'Nenhum dado estruturado JSON-LD encontrado.',
            'fix' => 'Implemente Schema.org para rich snippets.'
        ];

        preg_match_all('/<a[^>]*>(.*?)<\/a>/si', $html, $links);
        $bad_anchors = 0;
        foreach ($links[1] as $anchor) {
            $text = strip_tags($anchor);
            if (preg_match('/(clique aqui|saiba mais|leia mais|aqui)/i', $text)) {
                $bad_anchors++;
            }
        }
        $checks[] = [
            'name' => 'Texto Âncora Descritivo',
            'pass' => ($bad_anchors === 0),
            'msg' => ($bad_anchors === 0) ? 'Âncoras descritivas.' : "$bad_anchors links com texto genérico.",
            'fix' => 'Use textos descritivos nos links.'
        ];

        $noindex = (strpos($html, 'noindex') !== false);
        $checks[] = [
            'name' => 'Indexabilidade',
            'pass' => !$noindex,
            'msg' => !$noindex ? 'Página indexável.' : 'Tag noindex detectada!',
            'fix' => 'Remova noindex se quiser aparecer no Google.'
        ];

        return $checks;
    }

    private function analyze_security() {
        $checks = [];
        $html = $this->html_content;

        $wp_generator = (strpos($html, 'name="generator" content="WordPress') !== false);
        $checks[] = [
            'name' => 'Versão do WordPress Oculta',
            'pass' => !$wp_generator,
            'msg' => !$wp_generator ? 'Versão do WP oculta.' : 'Versão do WordPress exposta.',
            'fix' => 'Remova a tag generator.'
        ];

        $is_https = (stripos($this->target_url, 'https://') === 0);
        $mixed_content = (strpos($html, 'src="http://') !== false || strpos($html, "src='http://") !== false);
        
        $checks[] = [
            'name' => 'SSL / HTTPS',
            'pass' => ($is_https && !$mixed_content),
            'msg' => $is_https ? 'Site usando HTTPS.' : 'Site sem HTTPS ou com conteúdo misto.',
            'fix' => 'Instale SSL e force HTTPS.'
        ];

        $db_error = (strpos($html, 'WordPress database error') !== false);
        $checks[] = [
            'name' => 'Erros de DB Expostos',
            'pass' => !$db_error,
            'msg' => !$db_error ? 'Nenhum erro de DB visível.' : 'Erros de banco de dados expostos!',
            'fix' => 'Desative WP_DEBUG_DISPLAY.'
        ];

        return $checks;
    }

    private function calculate_score($checks) {
        $total = count($checks);
        $passed = 0;
        foreach ($checks as $check) {
            if ($check['pass']) $passed++;
        }
        return $total === 0 ? 0 : round(($passed / $total) * 100);
    }
}

new Futturu_External_Audit();
