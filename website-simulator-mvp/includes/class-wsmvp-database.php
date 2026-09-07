<?php
/**
 * Classe de gerenciamento do banco de dados
 */

if (!defined('ABSPATH')) exit;

class WSMVP_Database {
    private static $instance = null;
    private $db_version = '1.0.0';
    private $table_name;

    public static function get_instance() {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . WSMVP_TABLE_SIMULATIONS;
    }

    public static function activate() {
        $self = self::get_instance();
        $self->create_table();
        $self->add_default_data();
        update_option('wsmvp_db_version', $self->db_version);
    }

    public static function deactivate() {}
    public static function uninstall() {}

    private function create_table() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table_name} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            name VARCHAR(100) NOT NULL,
            company VARCHAR(100) DEFAULT NULL,
            email VARCHAR(100) NOT NULL,
            whatsapp VARCHAR(20) DEFAULT NULL,
            responses LONGTEXT NOT NULL,
            estimated_value DECIMAL(10,2) DEFAULT NULL,
            category VARCHAR(50) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'new',
            notes TEXT DEFAULT NULL,
            consent BOOLEAN NOT NULL DEFAULT FALSE,
            ip VARCHAR(45) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    private function add_default_data() {
        $defaults = [
            'wsmvp_company_name' => get_bloginfo('name'),
            'wsmvp_company_logo' => '',
            'wsmvp_primary_color' => '#4361ee',
            'wsmvp_secondary_color' => '#3f37c9',
            'wsmvp_currency' => 'R$',
            'wsmvp_min_project_value' => '1000',
            'wsmvp_welcome_text' => __('Crie seu site ideal em minutos!', WSMVP_TEXT_DOMAIN),
            'wsmvp_cta_button_text' => __('Solicitar Proposta', WSMVP_TEXT_DOMAIN),
            'wsmvp_admin_email' => get_option('admin_email'),
            'wsmvp_whatsapp_number' => '',
            'wsmvp_default_deadline' => '30',
            'wsmvp_terms_text' => __('Termos de uso padrao...', WSMVP_TEXT_DOMAIN),
            'wsmvp_privacy_notice' => __('Aviso de privacidade padrao...', WSMVP_TEXT_DOMAIN),
            'wsmvp_enable_pdf' => '1',
            'wsmvp_enable_email_capture' => '1',
            'wsmvp_show_public_estimate' => '1',
            'wsmvp_remove_data_on_uninstall' => '0'
        ];
        foreach ($defaults as $k => $v) if (!get_option($k)) update_option($k, $v);
        $this->add_default_questions();
        $this->add_default_pricing();
    }

    private function add_default_questions() {
        $questions = [
            ['title' => __('Qual e o objetivo principal do seu site?', WSMVP_TEXT_DOMAIN), 'type' => 'radio', 'required' => true, 'order' => 1, 'options' => [
                ['label' => __('Apresentar uma empresa', WSMVP_TEXT_DOMAIN), 'value' => 'apresentar_empresa'],
                ['label' => __('Gerar contatos', WSMVP_TEXT_DOMAIN), 'value' => 'gerar_contatos'],
                ['label' => __('Vender produtos', WSMVP_TEXT_DOMAIN), 'value' => 'vender_produtos'],
                ['label' => __('Divulgar servicos', WSMVP_TEXT_DOMAIN), 'value' => 'divulgar_servicos'],
                ['label' => __('Criar um blog', WSMVP_TEXT_DOMAIN), 'value' => 'criar_blog'],
                ['label' => __('Criar uma landing page', WSMVP_TEXT_DOMAIN), 'value' => 'landing_page']
            ]],
            ['title' => __('Que tipo de site voce precisa?', WSMVP_TEXT_DOMAIN), 'type' => 'radio', 'required' => true, 'order' => 2, 'options' => [
                ['label' => __('Site institucional', WSMVP_TEXT_DOMAIN), 'value' => 'site_institucional'],
                ['label' => __('Landing page', WSMVP_TEXT_DOMAIN), 'value' => 'landing_page'],
                ['label' => __('Loja virtual', WSMVP_TEXT_DOMAIN), 'value' => 'loja_virtual'],
                ['label' => __('Blog', WSMVP_TEXT_DOMAIN), 'value' => 'blog'],
                ['label' => __('Portfolio', WSMVP_TEXT_DOMAIN), 'value' => 'portfolio'],
                ['label' => __('Area de membros', WSMVP_TEXT_DOMAIN), 'value' => 'area_membros']
            ]],
            ['title' => __('Quais paginas deseja incluir?', WSMVP_TEXT_DOMAIN), 'type' => 'checkbox', 'required' => false, 'order' => 3, 'options' => [
                ['label' => __('Pagina inicial', WSMVP_TEXT_DOMAIN), 'value' => 'pagina_inicial'],
                ['label' => __('Sobre', WSMVP_TEXT_DOMAIN), 'value' => 'sobre'],
                ['label' => __('Servicos', WSMVP_TEXT_DOMAIN), 'value' => 'servicos'],
                ['label' => __('Produtos', WSMVP_TEXT_DOMAIN), 'value' => 'produtos'],
                ['label' => __('Blog', WSMVP_TEXT_DOMAIN), 'value' => 'blog'],
                ['label' => __('Portfolio', WSMVP_TEXT_DOMAIN), 'value' => 'portfolio'],
                ['label' => __('Depoimentos', WSMVP_TEXT_DOMAIN), 'value' => 'depoimentos'],
                ['label' => __('FAQ', WSMVP_TEXT_DOMAIN), 'value' => 'faq'],
                ['label' => __('Contato', WSMVP_TEXT_DOMAIN), 'value' => 'contato']
            ]]
        ];
        if (!get_option('wsmvp_questions')) update_option('wsmvp_questions', $questions);
    }

    private function add_default_pricing() {
        $pricing = [
            'base_prices' => [
                ['label' => __('Site institucional', WSMVP_TEXT_DOMAIN), 'value' => 'site_institucional', 'price' => 1500],
                ['label' => __('Landing page', WSMVP_TEXT_DOMAIN), 'value' => 'landing_page', 'price' => 900],
                ['label' => __('Loja virtual', WSMVP_TEXT_DOMAIN), 'value' => 'loja_virtual', 'price' => 3500]
            ],
            'category_labels' => [
                'basic' => __('Projeto Basico', WSMVP_TEXT_DOMAIN),
                'intermediate' => __('Projeto Intermediario', WSMVP_TEXT_DOMAIN)
            ]
        ];
        if (!get_option('wsmvp_pricing_rules')) update_option('wsmvp_pricing_rules', $pricing);
    }

    public function get_table_name() { return $this->table_name; }

    public function insert_simulation($data) {
        global $wpdb;
        $wpdb->insert($this->table_name, [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'responses' => json_encode(WSMVP_Core::sanitize_array($data['responses'] ?? [])),
            'estimated_value' => $data['estimated_value'] ?? null,
            'status' => 'new',
            'consent' => !empty($data['consent'])
        ]);
        return $wpdb->insert_id;
    }

    public function get_simulations($args = []) {
        global $wpdb;
        $args = wp_parse_args($args, ['per_page' => 20, 'page' => 1, 'orderby' => 'created_at', 'order' => 'DESC']);
        $where = '1=1'; $params = [];
        if (!empty($args['status'])) { $where .= " AND status = %s"; $params[] = $args['status']; }
        $sql = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d", 
            array_merge($params, [intval($args['per_page']), ($args['page']-1)*$args['per_page']]));
        $results = $wpdb->get_results($sql);
        foreach ($results as &$r) $r->responses = json_decode($r->responses, true);
        return ['simulations' => $results, 'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}", $params)];
    }

    public function get_simulation($id) {
        global $wpdb;
        $r = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id));
        if ($r) $r->responses = json_decode($r->responses, true);
        return $r;
    }

    public function get_stats() {
        global $wpdb;
        return [
            'total_simulations' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}"),
            'recent_simulations' => $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT 5")
        ];
    }
}
