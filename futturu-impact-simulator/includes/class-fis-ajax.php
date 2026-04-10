<?php
/**
 * AJAX handler class for Futturu Impact Simulator
 */
if (!defined('ABSPATH')) {
    exit;
}

class FIS_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_fis_calculate_impact', array($this, 'calculate_impact'));
        add_action('wp_ajax_nopriv_fis_calculate_impact', array($this, 'calculate_impact'));
        add_action('wp_ajax_fis_submit_contact', array($this, 'submit_contact'));
        add_action('wp_ajax_nopriv_fis_submit_contact', array($this, 'submit_contact'));
    }
    
    /**
     * Calculate impact via AJAX
     */
    public function calculate_impact() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fis_nonce')) {
            wp_send_json_error(array('message' => __('Erro de segurança. Por favor, recarregue a página.', 'futturu-impact-simulator')));
        }
        
        // Sanitize inputs
        $business_type = isset($_POST['business_type']) ? sanitize_text_field($_POST['business_type']) : 'outro';
        $revenue_range = isset($_POST['revenue_range']) ? sanitize_text_field($_POST['revenue_range']) : 'low';
        $target_audience = isset($_POST['target_audience']) ? sanitize_text_field($_POST['target_audience']) : 'b2c';
        $objective = isset($_POST['objective']) ? sanitize_text_field($_POST['objective']) : 'visibilidade';
        
        // Validate inputs
        $valid_business_types = array_keys(fis_get_default_benchmarks());
        if (!in_array($business_type, $valid_business_types)) {
            $business_type = 'outro';
        }
        
        $valid_revenue_ranges = array('low', 'medium', 'high', 'very_high');
        if (!in_array($revenue_range, $valid_revenue_ranges)) {
            $revenue_range = 'low';
        }
        
        $valid_audiences = array('b2c', 'b2b', 'both');
        if (!in_array($target_audience, $valid_audiences)) {
            $target_audience = 'b2c';
        }
        
        $valid_objectives = array('visibilidade', 'vendas', 'leads', 'marca', 'cartao_visitas');
        if (!in_array($objective, $valid_objectives)) {
            $objective = 'visibilidade';
        }
        
        // Calculate impact
        require_once FIS_PLUGIN_DIR . 'includes/class-fis-calculator.php';
        $result = FIS_Calculator::calculate($business_type, $revenue_range, $target_audience, $objective);
        
        // Add business info to result
        $result['business_info'] = array(
            'business_type' => $business_type,
            'revenue_range' => $revenue_range,
            'target_audience' => $target_audience,
            'objective' => $objective
        );
        
        wp_send_json_success($result);
    }
    
    /**
     * Submit contact form via AJAX
     */
    public function submit_contact() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fis_nonce')) {
            wp_send_json_error(array('message' => __('Erro de segurança. Por favor, recarregue a página.', 'futturu-impact-simulator')));
        }
        
        // Sanitize inputs
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $business_type = isset($_POST['business_type']) ? sanitize_text_field($_POST['business_type']) : '';
        
        // Validate required fields
        if (empty($name) || empty($email)) {
            wp_send_json_error(array('message' => __('Por favor, preencha todos os campos obrigatórios.', 'futturu-impact-simulator')));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Por favor, informe um email válido.', 'futturu-impact-simulator')));
        }
        
        // Get settings
        $settings = get_option('fis_settings');
        $to_email = isset($settings['fis_cta_email']) ? $settings['fis_cta_email'] : 'suporte@futturu.com.br';
        $messages = isset($settings['messages']) ? $settings['messages'] : fis_get_default_messages();
        
        // Prepare email
        $subject = sprintf(__('Novo Lead do Simulador - %s', 'futturu-impact-simulator'), ucfirst($business_type));
        
        $body = sprintf(
            __("Novo lead capturado pelo Simulador de Impacto Futturu:\n\n" .
            "Nome: %s\n" .
            "Email: %s\n" .
            "Telefone: %s\n" .
            "Tipo de Negócio: %s\n" .
            "Mensagem: %s\n\n" .
            "---\n" .
            "Este lead foi gerado através do simulador de impacto online.", 'futturu-impact-simulator'),
            $name,
            $email,
            $phone,
            $business_type,
            !empty($message) ? $message : __('Nenhuma mensagem adicional.', 'futturu-impact-simulator')
        );
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $email,
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        // Send email
        $sent = wp_mail($to_email, $subject, nl2br($body), $headers);
        
        if ($sent) {
            wp_send_json_success(array('message' => isset($messages['success_message']) ? $messages['success_message'] : __('Obrigado! Entraremos em contato em breve.', 'futturu-impact-simulator')));
        } else {
            wp_send_json_error(array('message' => isset($messages['error_message']) ? $messages['error_message'] : __('Ocorreu um erro ao enviar. Por favor, tente novamente.', 'futturu-impact-simulator')));
        }
    }
}
