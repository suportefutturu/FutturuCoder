<?php
/**
 * AJAX Handler Class
 * Handles form submission and calculation requests
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Ajax_Handler {

    public static function init() {
        add_action('wp_ajax_futturu_calculate', array(__CLASS__, 'handle_calculate'));
        add_action('wp_ajax_nopriv_futturu_calculate', array(__CLASS__, 'handle_calculate'));
        
        add_action('wp_ajax_futturu_submit', array(__CLASS__, 'handle_submit'));
        add_action('wp_ajax_nopriv_futturu_submit', array(__CLASS__, 'handle_submit'));
    }

    /**
     * Handle investment calculation request
     */
    public static function handle_calculate() {
        check_ajax_referer('futturu_simulator_nonce', 'nonce');

        $data = self::sanitize_form_data($_POST);
        $calculator = new Futturu_Calculator();
        $calculation = $calculator->calculate($data);

        wp_send_json_success(array(
            'calculation' => $calculation,
            'formatted' => array(
                'estimated' => Futturu_Calculator::format_currency($calculation['estimated']),
                'min' => Futturu_Calculator::format_currency($calculation['min']),
                'max' => Futturu_Calculator::format_currency($calculation['max'])
            )
        ));
    }

    /**
     * Handle form submission
     */
    public static function handle_submit() {
        check_ajax_referer('futturu_simulator_nonce', 'nonce');

        $data = self::sanitize_form_data($_POST);
        
        // Validate required fields
        $errors = self::validate_form_data($data);
        if (!empty($errors)) {
            wp_send_json_error(array('errors' => $errors));
        }

        // Calculate investment
        $calculator = new Futturu_Calculator();
        $calculation = $calculator->calculate($data);
        $delivery_estimate = $calculator->get_delivery_estimate($data);

        // Prepare data for database
        $db_data = array_merge($data, array(
            'investment_estimated' => $calculation['estimated'],
            'investment_min' => $calculation['min'],
            'investment_max' => $calculation['max'],
            'estimated_delivery' => $delivery_estimate,
            'ip_address' => self::get_client_ip(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ));

        // Serialize array fields
        foreach ($db_data as $key => $value) {
            if (is_array($value)) {
                $db_data[$key] = implode(',', $value);
            }
        }

        // Insert into database
        $simulation_id = Futturu_Database::insert_simulation($db_data);

        if (!$simulation_id) {
            wp_send_json_error(array('message' => __('Erro ao salvar simulação.', 'futturu-simulator')));
        }

        // Send email
        $email_sent = Futturu_Email::send_simulation_email($simulation_id, $data, $calculation);

        wp_send_json_success(array(
            'simulation_id' => $simulation_id,
            'calculation' => $calculation,
            'formatted' => array(
                'estimated' => Futturu_Calculator::format_currency($calculation['estimated']),
                'min' => Futturu_Calculator::format_currency($calculation['min']),
                'max' => Futturu_Calculator::format_currency($calculation['max'])
            ),
            'delivery' => $delivery_estimate,
            'email_sent' => $email_sent
        ));
    }

    /**
     * Sanitize form data
     */
    private static function sanitize_form_data($data) {
        $sanitized = array();

        // Text fields
        $text_fields = array(
            'project_type', 'site_type', 'site_type_other', 'complexity_level',
            'num_pages', 'num_pages_custom', 'texts_provided', 'images_provided',
            'addons_other', 'google_marketing_other', 'domain_status', 'hosting_current',
            'hosting_premium_interest', 'maintenance_needed', 'maintenance_importance',
            'maintenance_package', 'company_category', 'investment_range', 'delivery_time',
            'specific_date', 'preferred_time', 'client_name', 'client_email', 'client_phone',
            'client_cnpj', 'site_address', 'design_reference', 'market_segment',
            'how_found_us', 'additional_info'
        );

        foreach ($text_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'additional_info') {
                    $sanitized[$field] = sanitize_textarea_field($data[$field]);
                } elseif ($field === 'client_email') {
                    $sanitized[$field] = sanitize_email($data[$field]);
                } else {
                    $sanitized[$field] = sanitize_text_field($data[$field]);
                }
            }
        }

        // Numeric field
        if (isset($data['languages'])) {
            $sanitized['languages'] = intval($data['languages']);
        }

        // Boolean fields
        $bool_fields = array('seo_basic', 'seo_advanced');
        foreach ($bool_fields as $field) {
            $sanitized[$field] = isset($data[$field]) ? 1 : 0;
        }

        // Array fields (checkboxes)
        $array_fields = array('menu_pages', 'addons', 'google_marketing', 'hosting_problems', 
                              'hosting_features', 'proposal_type', 'contact_channel');
        foreach ($array_fields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $sanitized[$field] = array_map('sanitize_text_field', $data[$field]);
            } else {
                $sanitized[$field] = array();
            }
        }

        return $sanitized;
    }

    /**
     * Validate form data
     */
    private static function validate_form_data($data) {
        $errors = array();

        // Required fields
        if (empty($data['client_name'])) {
            $errors['client_name'] = __('Nome é obrigatório.', 'futturu-simulator');
        }

        if (empty($data['client_email'])) {
            $errors['client_email'] = __('E-mail é obrigatório.', 'futturu-simulator');
        } elseif (!is_email($data['client_email'])) {
            $errors['client_email'] = __('E-mail inválido.', 'futturu-simulator');
        }

        if (empty($data['client_phone'])) {
            $errors['client_phone'] = __('WhatsApp/Telefone é obrigatório.', 'futturu-simulator');
        } else {
            // Basic phone validation (Brazilian format)
            $phone = preg_replace('/[^0-9]/', '', $data['client_phone']);
            if (strlen($phone) < 10 || strlen($phone) > 11) {
                $errors['client_phone'] = __('Telefone/WhatsApp inválido.', 'futturu-simulator');
            }
        }

        if (empty($data['site_address'])) {
            $errors['site_address'] = __('Endereço do site é obrigatório.', 'futturu-simulator');
        }

        if (empty($data['market_segment'])) {
            $errors['market_segment'] = __('Segmento de mercado é obrigatório.', 'futturu-simulator');
        }

        // CNPJ validation if provided
        if (!empty($data['client_cnpj'])) {
            $cnpj = preg_replace('/[^0-9]/', '', $data['client_cnpj']);
            if (strlen($cnpj) !== 14) {
                $errors['client_cnpj'] = __('CNPJ inválido.', 'futturu-simulator');
            }
        }

        return $errors;
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                         'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return 'unknown';
    }
}

// Initialize AJAX handlers
Futturu_Ajax_Handler::init();
