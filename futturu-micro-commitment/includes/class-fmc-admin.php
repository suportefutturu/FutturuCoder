<?php
/**
 * Admin Class for Futturu Micro-Commitment Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class FMC_Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_fmc_save_questions', array($this, 'ajax_save_questions'));
        add_action('wp_ajax_fmc_save_ctas', array($this, 'ajax_save_ctas'));
        add_action('wp_ajax_fmc_get_responses', array($this, 'ajax_get_responses'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Micro-Engajamento Futturu', 'futturu-micro-commitment'),
            __('Micro-Engajamento Futturu', 'futturu-micro-commitment'),
            'manage_options',
            'futturu-micro-commitment',
            array($this, 'render_admin_page'),
            'dashicons-feedback',
            30
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_futturu-micro-commitment') {
            return;
        }
        
        wp_enqueue_style('fmc-admin-css', FMC_PLUGIN_URL . 'assets/css/admin.css', array(), FMC_VERSION);
        wp_enqueue_script('fmc-admin-js', FMC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), FMC_VERSION, true);
        
        wp_localize_script('fmc-admin-js', 'fmcAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fmc_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('Tem certeza que deseja remover este item?', 'futturu-micro-commitment'),
                'saveSuccess' => __('Configurações salvas com sucesso!', 'futturu-micro-commitment'),
                'saveError' => __('Erro ao salvar. Tente novamente.', 'futturu-micro-commitment')
            )
        ));
    }
    
    public function render_admin_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle form submissions
        if (isset($_POST['fmc_save_settings']) && check_admin_referer('fmc_settings_nonce')) {
            update_option('fmc_enabled', isset($_POST['fmc_enabled']) ? true : false);
            update_option('fmc_track_ip', isset($_POST['fmc_track_ip']) ? true : false);
            update_option('fmc_rate_limit', intval($_POST['fmc_rate_limit']));
            
            echo '<div class="notice notice-success"><p>' . __('Configurações gerais salvas!', 'futturu-micro-commitment') . '</p></div>';
        }
        
        $questions = get_option('fmc_questions', array());
        $ctas = get_option('fmc_ctas', array());
        $enabled = get_option('fmc_enabled', true);
        $track_ip = get_option('fmc_track_ip', false);
        $rate_limit = get_option('fmc_rate_limit', 5);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Micro-Compromissos Guiados Futturu', 'futturu-micro-commitment'); ?></h1>
            
            <div class="fmc-admin-tabs">
                <button class="fmc-tab-btn active" data-tab="questions"><?php esc_html_e('Perguntas', 'futturu-micro-commitment'); ?></button>
                <button class="fmc-tab-btn" data-tab="ctas"><?php esc_html_e('CTAs', 'futturu-micro-commitment'); ?></button>
                <button class="fmc-tab-btn" data-tab="responses"><?php esc_html_e('Respostas', 'futturu-micro-commitment'); ?></button>
                <button class="fmc-tab-btn" data-tab="settings"><?php esc_html_e('Configurações', 'futturu-micro-commitment'); ?></button>
            </div>
            
            <!-- Questions Tab -->
            <div id="fmc-tab-questions" class="fmc-tab-content active">
                <h2><?php esc_html_e('Sequência de Perguntas', 'futturu-micro-commitment'); ?></h2>
                <p class="description"><?php esc_html_e('Configure as perguntas e suas ramificações. Cada resposta pode levar a outra pergunta ou a um CTA final.', 'futturu-micro-commitment'); ?></p>
                
                <div id="fmc-questions-container">
                    <?php foreach ($questions as $index => $question) : ?>
                        <div class="fmc-question-item" data-id="<?php echo esc_attr($question['id']); ?>">
                            <div class="fmc-question-header">
                                <span class="fmc-question-title"><?php echo esc_html($question['question']); ?></span>
                                <button class="fmc-remove-question button button-small"><?php esc_html_e('Remover', 'futturu-micro-commitment'); ?></button>
                            </div>
                            <div class="fmc-question-body">
                                <label><?php esc_html_e('ID da Pergunta:', 'futturu-micro-commitment'); ?></label>
                                <input type="text" class="fmc-question-id" value="<?php echo esc_attr($question['id']); ?>" readonly>
                                
                                <label><?php esc_html_e('Texto da Pergunta:', 'futturu-micro-commitment'); ?></label>
                                <input type="text" class="fmc-question-text" value="<?php echo esc_attr($question['question']); ?>">
                                
                                <h4><?php esc_html_e('Opções de Resposta:', 'futturu-micro-commitment'); ?></h4>
                                <div class="fmc-answers-container">
                                    <?php foreach ($question['answers'] as $answer) : ?>
                                        <div class="fmc-answer-item">
                                            <input type="text" class="fmc-answer-text" placeholder="Texto da resposta" value="<?php echo esc_attr($answer['text']); ?>">
                                            <select class="fmc-answer-next">
                                                <option value="">-- Selecione --</option>
                                                <optgroup label="Outras Perguntas">
                                                    <?php foreach ($questions as $q) : ?>
                                                        <?php if ($q['id'] !== $question['id']) : ?>
                                                            <option value="<?php echo esc_attr($q['id']); ?>" <?php selected(isset($answer['next']) ? $answer['next'] : '', $q['id']); ?>>
                                                                <?php echo esc_html($q['question']); ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <optgroup label="CTAs Finais">
                                                    <?php foreach ($ctas as $cta_id => $cta) : ?>
                                                        <option value="<?php echo esc_attr($cta_id); ?>" <?php selected(isset($answer['cta']) ? $answer['cta'] : '', $cta_id); ?>>
                                                            <?php echo esc_html($cta['title']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            </select>
                                            <button class="fmc-remove-answer button button-small"><?php esc_html_e('X', 'futturu-micro-commitment'); ?></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button class="fmc-add-answer button"><?php esc_html_e('+ Adicionar Resposta', 'futturu-micro-commitment'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button id="fmc-add-question" class="button button-primary"><?php esc_html_e('+ Adicionar Pergunta', 'futturu-micro-commitment'); ?></button>
                <button id="fmc-save-questions" class="button button-primary button-large"><?php esc_html_e('Salvar Perguntas', 'futturu-micro-commitment'); ?></button>
            </div>
            
            <!-- CTAs Tab -->
            <div id="fmc-tab-ctas" class="fmc-tab-content">
                <h2><?php esc_html_e('Call-to-Actions (CTAs)', 'futturu-micro-commitment'); ?></h2>
                <p class="description"><?php esc_html_e('Configure os CTAs finais que serão exibidos após a sequência de perguntas.', 'futturu-micro-commitment'); ?></p>
                
                <div id="fmc-ctas-container">
                    <?php foreach ($ctas as $cta_id => $cta) : ?>
                        <div class="fmc-cta-item" data-id="<?php echo esc_attr($cta_id); ?>">
                            <div class="fmc-cta-header">
                                <span class="fmc-cta-title"><?php echo esc_html($cta['title']); ?></span>
                                <button class="fmc-remove-cta button button-small"><?php esc_html_e('Remover', 'futturu-micro-commitment'); ?></button>
                            </div>
                            <div class="fmc-cta-body">
                                <label><?php esc_html_e('ID do CTA:', 'futturu-micro-commitment'); ?></label>
                                <input type="text" class="fmc-cta-id" value="<?php echo esc_attr($cta_id); ?>">
                                
                                <label><?php esc_html_e('Título:', 'futturu-micro-commitment'); ?></label>
                                <input type="text" class="fmc-cta-title-input" value="<?php echo esc_attr($cta['title']); ?>">
                                
                                <label><?php esc_html_e('Descrição:', 'futturu-micro-commitment'); ?></label>
                                <textarea class="fmc-cta-description"><?php echo esc_textarea($cta['description']); ?></textarea>
                                
                                <label><?php esc_html_e('Texto do Botão:', 'futturu-micro-commitment'); ?></label>
                                <input type="text" class="fmc-cta-button-text" value="<?php echo esc_attr($cta['button_text']); ?>">
                                
                                <label><?php esc_html_e('Link de Destino:', 'futturu-micro-commitment'); ?></label>
                                <input type="text" class="fmc-cta-link" value="<?php echo esc_attr($cta['link']); ?>">
                                
                                <label><?php esc_html_e('Tipo:', 'futturu-micro-commitment'); ?></label>
                                <select class="fmc-cta-type">
                                    <option value="link" <?php selected($cta['type'], 'link'); ?>><?php esc_html_e('Link Externo', 'futturu-micro-commitment'); ?></option>
                                    <option value="modal" <?php selected($cta['type'], 'modal'); ?>><?php esc_html_e('Modal/Formulário', 'futturu-micro-commitment'); ?></option>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button id="fmc-add-cta" class="button button-primary"><?php esc_html_e('+ Adicionar CTA', 'futturu-micro-commitment'); ?></button>
                <button id="fmc-save-ctas" class="button button-primary button-large"><?php esc_html_e('Salvar CTAs', 'futturu-micro-commitment'); ?></button>
            </div>
            
            <!-- Responses Tab -->
            <div id="fmc-tab-responses" class="fmc-tab-content">
                <h2><?php esc_html_e('Respostas dos Usuários', 'futturu-micro-commitment'); ?></h2>
                <p class="description"><?php esc_html_e('Visualize as respostas coletadas e os caminhos percorridos pelos usuários.', 'futturu-micro-commitment'); ?></p>
                
                <div id="fmc-responses-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Data', 'futturu-micro-commitment'); ?></th>
                                <th><?php esc_html_e('Session ID', 'futturu-micro-commitment'); ?></th>
                                <th><?php esc_html_e('Pergunta', 'futturu-micro-commitment'); ?></th>
                                <th><?php esc_html_e('Resposta', 'futturu-micro-commitment'); ?></th>
                                <th><?php esc_html_e('IP', 'futturu-micro-commitment'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="fmc-responses-body">
                            <tr>
                                <td colspan="5"><?php esc_html_e('Carregando...', 'futturu-micro-commitment'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button id="fmc-refresh-responses" class="button"><?php esc_html_e('Atualizar', 'futturu-micro-commitment'); ?></button>
            </div>
            
            <!-- Settings Tab -->
            <div id="fmc-tab-settings" class="fmc-tab-content">
                <h2><?php esc_html_e('Configurações Gerais', 'futturu-micro-commitment'); ?></h2>
                
                <form method="post" action="">
                    <?php wp_nonce_field('fmc_settings_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Ativar Plugin', 'futturu-micro-commitment'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fmc_enabled" value="1" <?php checked($enabled, true); ?>>
                                    <?php esc_html_e('Habilitar micro-compromissos no frontend', 'futturu-micro-commitment'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Rastrear IP', 'futturu-micro-commitment'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fmc_track_ip" value="1" <?php checked($track_ip, true); ?>>
                                    <?php esc_html_e('Armazenar IP do usuário (respeite a LGPD)', 'futturu-micro-commitment'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Rate Limit', 'futturu-micro-commitment'); ?></th>
                            <td>
                                <input type="number" name="fmc_rate_limit" value="<?php echo esc_attr($rate_limit); ?>" min="1" max="100">
                                <p class="description"><?php esc_html_e('Máximo de submissões por minuto por IP (prevenção de spam)', 'futturu-micro-commitment'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Shortcode', 'futturu-micro-commitment'); ?></th>
                            <td>
                                <code>[futturu_micro_engage]</code>
                                <p class="description"><?php esc_html_e('Use este shortcode em qualquer página ou post para exibir o widget.', 'futturu-micro-commitment'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <input type="submit" name="fmc_save_settings" class="button button-primary" value="<?php esc_attr_e('Salvar Configurações', 'futturu-micro-commitment'); ?>">
                </form>
            </div>
        </div>
        <?php
    }
    
    public function ajax_save_questions() {
        check_ajax_referer('fmc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permissão negada'));
        }
        
        $questions = isset($_POST['questions']) ? $_POST['questions'] : array();
        
        // Sanitize questions
        $sanitized_questions = array();
        foreach ($questions as $question) {
            $sanitized_question = array(
                'id' => sanitize_text_field($question['id']),
                'question' => sanitize_text_field($question['question']),
                'answers' => array()
            );
            
            foreach ($question['answers'] as $answer) {
                $sanitized_answer = array(
                    'text' => sanitize_text_field($answer['text'])
                );
                
                if (!empty($answer['next'])) {
                    $sanitized_answer['next'] = sanitize_text_field($answer['next']);
                } elseif (!empty($answer['cta'])) {
                    $sanitized_answer['cta'] = sanitize_text_field($answer['cta']);
                }
                
                $sanitized_question['answers'][] = $sanitized_answer;
            }
            
            $sanitized_questions[] = $sanitized_question;
        }
        
        update_option('fmc_questions', $sanitized_questions);
        wp_send_json_success(array('message' => 'Perguntas salvas com sucesso'));
    }
    
    public function ajax_save_ctas() {
        check_ajax_referer('fmc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permissão negada'));
        }
        
        $ctas = isset($_POST['ctas']) ? $_POST['ctas'] : array();
        
        // Sanitize CTAs
        $sanitized_ctas = array();
        foreach ($ctas as $cta) {
            $cta_id = sanitize_text_field($cta['id']);
            $sanitized_ctas[$cta_id] = array(
                'title' => sanitize_text_field($cta['title']),
                'description' => sanitize_textarea_field($cta['description']),
                'button_text' => sanitize_text_field($cta['button_text']),
                'link' => esc_url_raw($cta['link']),
                'type' => sanitize_text_field($cta['type'])
            );
        }
        
        update_option('fmc_ctas', $sanitized_ctas);
        wp_send_json_success(array('message' => 'CTAs salvos com sucesso'));
    }
    
    public function ajax_get_responses() {
        check_ajax_referer('fmc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permissão negada'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmc_responses';
        
        $responses = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 100", ARRAY_A);
        
        wp_send_json_success(array('responses' => $responses));
    }
}
