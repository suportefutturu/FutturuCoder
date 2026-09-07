<?php
/**
 * Classe de leads
 */
if (!defined('ABSPATH')) exit;

class WSMVP_Leads {
    private static $instance = null;
    private $db;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->db = WSMVP_Database::get_instance();
    }

    public function save_simulation($data) {
        if (empty($data['email']) || !is_email($data['email'])) {
            return new WP_Error('invalid_email', __('E-mail inválido', WSMVP_TEXT_DOMAIN));
        }
        if (empty($data['consent']) || !$data['consent']) {
            return new WP_Error('no_consent', __('Consentimento obrigatório', WSMVP_TEXT_DOMAIN));
        }
        $pricing = WSMVP_Pricing::get_instance();
        $estimate = $pricing->calculate_estimate($data['responses'] ?? []);
        $simulation = [
            'name' => $data['name'] ?? '',
            'email' => $data['email'],
            'responses' => $data['responses'] ?? [],
            'estimated_value' => $estimate['total'],
            'category' => $estimate['category'],
            'status' => 'new',
            'consent' => true
        ];
        $id = $this->db->insert_simulation($simulation);
        if ($id) return ['id' => $id, 'estimate' => $estimate];
        return new WP_Error('save_error', __('Erro ao salvar', WSMVP_TEXT_DOMAIN));
    }

    public function get_simulations($args = []) { return $this->db->get_simulations($args); }
    public function get_simulation($id) { return $this->db->get_simulation($id); }
    public function get_stats() { return $this->db->get_stats(); }
}
