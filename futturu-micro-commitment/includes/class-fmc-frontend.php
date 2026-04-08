<?php
/**
 * Frontend Class for Futturu Micro-Commitment Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class FMC_Frontend {
    
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
        add_shortcode('futturu_micro_engage', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_fmc_get_question', array($this, 'ajax_get_question'));
        add_action('wp_ajax_nopriv_fmc_get_question', array($this, 'ajax_get_question'));
        add_action('wp_ajax_fmc_submit_answer', array($this, 'ajax_submit_answer'));
        add_action('wp_ajax_nopriv_fmc_submit_answer', array($this, 'ajax_submit_answer'));
    }
    
    public function enqueue_assets() {
        if (!get_option('fmc_enabled', true)) {
            return;
        }
        
        wp_enqueue_style('fmc-frontend-css', FMC_PLUGIN_URL . 'assets/css/frontend.css', array(), FMC_VERSION);
        wp_enqueue_script('fmc-frontend-js', FMC_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), FMC_VERSION, true);
        
        wp_localize_script('fmc-frontend-js', 'fmcFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fmc_frontend_nonce'),
            'strings' => array(
                'loading' => __('Carregando...', 'futturu-micro-commitment'),
                'error' => __('Ocorreu um erro. Tente novamente.', 'futturu-micro-commitment')
            )
        ));
    }
    
    public function render_shortcode($atts) {
        if (!get_option('fmc_enabled', true)) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'style' => 'default',
            'show_progress' => 'true'
        ), $atts);
        
        $questions = get_option('fmc_questions', array());
        
        if (empty($questions)) {
            return '<p>' . __('Nenhuma pergunta configurada.', 'futturu-micro-commitment') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="fmc-widget" data-style="<?php echo esc_attr($atts['style']); ?>" data-show-progress="<?php echo esc_attr($atts['show_progress']); ?>">
            <div class="fmc-container">
                <?php if ($atts['show_progress'] === 'true') : ?>
                    <div class="fmc-progress-bar">
                        <div class="fmc-progress-fill"></div>
                    </div>
                <?php endif; ?>
                
                <div class="fmc-content">
                    <div class="fmc-question-wrapper">
                        <div class="fmc-question-text"></div>
                        <div class="fmc-answers-container"></div>
                    </div>
                    
                    <div class="fmc-cta-wrapper" style="display: none;">
                        <h3 class="fmc-cta-title"></h3>
                        <p class="fmc-cta-description"></p>
                        <a href="#" class="fmc-cta-button"></a>
                    </div>
                </div>
                
                <div class="fmc-footer">
                    <span class="fmc-powered-by"><?php echo esc_html__('Por Futturu', 'futturu-micro-commitment'); ?></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_get_question() {
        check_ajax_referer('fmc_frontend_nonce', 'nonce');
        
        $question_id = isset($_POST['question_id']) ? sanitize_text_field($_POST['question_id']) : '';
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        
        if (empty($session_id)) {
            $session_id = $this->generate_session_id();
        }
        
        $questions = get_option('fmc_questions', array());
        
        if (empty($question_id)) {
            // Get first question
            $question = !empty($questions) ? $questions[0] : null;
        } else {
            // Find specific question
            foreach ($questions as $q) {
                if ($q['id'] === $question_id) {
                    $question = $q;
                    break;
                }
            }
        }
        
        if (!$question) {
            wp_send_json_error(array('message' => 'Pergunta não encontrada'));
        }
        
        wp_send_json_success(array(
            'question_id' => $question['id'],
            'question_text' => $question['question'],
            'answers' => $question['answers'],
            'session_id' => $session_id,
            'total_questions' => count($questions),
            'current_index' => $this->get_question_index($question['id'], $questions)
        ));
    }
    
    public function ajax_submit_answer() {
        check_ajax_referer('fmc_frontend_nonce', 'nonce');
        
        // Rate limiting
        if (!$this->check_rate_limit()) {
            wp_send_json_error(array('message' => 'Muitas tentativas. Aguarde um momento.'));
        }
        
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        $question_id = isset($_POST['question_id']) ? sanitize_text_field($_POST['question_id']) : '';
        $answer_text = isset($_POST['answer_text']) ? sanitize_text_field($_POST['answer_text']) : '';
        $path_taken = isset($_POST['path_taken']) ? sanitize_text_field($_POST['path_taken']) : '';
        
        if (empty($session_id) || empty($question_id) || empty($answer_text)) {
            wp_send_json_error(array('message' => 'Dados inválidos'));
        }
        
        // Save response
        FMC_Data::save_response($session_id, $question_id, $answer_text, $path_taken);
        
        // Find the answer and determine next step
        $questions = get_option('fmc_questions', array());
        $ctas = get_option('fmc_ctas', array());
        
        $next_step = null;
        $current_question = null;
        
        foreach ($questions as $q) {
            if ($q['id'] === $question_id) {
                $current_question = $q;
                break;
            }
        }
        
        if ($current_question) {
            foreach ($current_question['answers'] as $answer) {
                if ($answer['text'] === $answer_text) {
                    if (isset($answer['next'])) {
                        $next_step = array(
                            'type' => 'question',
                            'question_id' => $answer['next']
                        );
                    } elseif (isset($answer['cta'])) {
                        $cta_id = $answer['cta'];
                        if (isset($ctas[$cta_id])) {
                            $next_step = array(
                                'type' => 'cta',
                                'cta' => $ctas[$cta_id]
                            );
                        }
                    }
                    break;
                }
            }
        }
        
        if (!$next_step) {
            wp_send_json_error(array('message' => 'Próximo passo não encontrado'));
        }
        
        wp_send_json_success(array(
            'next_step' => $next_step,
            'path_taken' => $path_taken . $question_id . ':' . $answer_text . ';'
        ));
    }
    
    private function generate_session_id() {
        return bin2hex(random_bytes(16));
    }
    
    private function get_question_index($question_id, $questions) {
        foreach ($questions as $index => $question) {
            if ($question['id'] === $question_id) {
                return $index;
            }
        }
        return 0;
    }
    
    private function check_rate_limit() {
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $transient_key = 'fmc_rate_limit_' . md5($user_ip);
        $attempts = get_transient($transient_key);
        
        if ($attempts === false) {
            set_transient($transient_key, 1, 60); // 1 minute window
            return true;
        }
        
        $max_attempts = get_option('fmc_rate_limit', 5);
        
        if ($attempts >= $max_attempts) {
            return false;
        }
        
        set_transient($transient_key, $attempts + 1, 60);
        return true;
    }
}
