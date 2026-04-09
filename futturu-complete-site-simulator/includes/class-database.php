<?php
/**
 * Database Handler Class
 * Manages custom table creation and data operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Database {

    private static $table_name = 'futturu_simulations';

    public static function init() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::$table_name;

        if (get_option('futturu_simulator_db_version') !== FUTTURU_SIMULATOR_DB_VERSION) {
            self::create_table($table_name, $charset_collate);
        }
    }

    private static function create_table($table_name, $charset_collate) {
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            project_type varchar(50) DEFAULT '',
            site_type varchar(100) DEFAULT '',
            site_type_other varchar(255) DEFAULT '',
            complexity_level varchar(20) DEFAULT '',
            num_pages varchar(50) DEFAULT '',
            num_pages_custom varchar(255) DEFAULT '',
            menu_pages text DEFAULT '',
            languages int(2) DEFAULT 1,
            texts_provided varchar(50) DEFAULT '',
            images_provided varchar(50) DEFAULT '',
            addons text DEFAULT '',
            addons_other varchar(255) DEFAULT '',
            google_marketing text DEFAULT '',
            google_marketing_other varchar(255) DEFAULT '',
            seo_basic tinyint(1) DEFAULT 0,
            seo_advanced tinyint(1) DEFAULT 0,
            domain_status varchar(50) DEFAULT '',
            hosting_current varchar(100) DEFAULT '',
            hosting_problems text DEFAULT '',
            hosting_premium_interest varchar(100) DEFAULT '',
            hosting_features text DEFAULT '',
            maintenance_needed varchar(50) DEFAULT '',
            maintenance_importance varchar(50) DEFAULT '',
            maintenance_package varchar(50) DEFAULT '',
            company_category varchar(50) DEFAULT '',
            investment_range varchar(100) DEFAULT '',
            proposal_type text DEFAULT '',
            delivery_time varchar(50) DEFAULT '',
            specific_date date DEFAULT NULL,
            preferred_time varchar(50) DEFAULT '',
            client_name varchar(255) NOT NULL,
            client_email varchar(255) NOT NULL,
            client_phone varchar(50) NOT NULL,
            client_cnpj varchar(50) DEFAULT '',
            contact_channel text DEFAULT '',
            site_address varchar(255) DEFAULT '',
            design_reference varchar(255) DEFAULT '',
            market_segment varchar(255) DEFAULT '',
            how_found_us varchar(100) DEFAULT '',
            additional_info text DEFAULT '',
            investment_estimated decimal(10,2) DEFAULT 0.00,
            investment_min decimal(10,2) DEFAULT 0.00,
            investment_max decimal(10,2) DEFAULT 0.00,
            estimated_delivery varchar(100) DEFAULT '',
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(50) DEFAULT 'new',
            ip_address varchar(50) DEFAULT '',
            user_agent varchar(255) DEFAULT '',
            PRIMARY KEY  (id),
            KEY client_email (client_email),
            KEY submission_date (submission_date),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option('futturu_simulator_db_version', FUTTURU_SIMULATOR_DB_VERSION);
    }

    public static function activate() {
        self::init();
        // Set default options
        self::set_default_options();
    }

    public static function deactivate() {
        // Optionally clean up, but we'll keep the data
    }

    private static function set_default_options() {
        $defaults = array(
            'futturu_simulator_enabled' => 1,
            'futturu_simulator_email_enabled' => 1,
            'futturu_simulator_email_to' => 'suporte@futturu.com.br',
            'futturu_simulator_base_values' => self::get_default_base_values(),
            'futturu_simulator_complexity_multipliers' => array(
                'low' => 1.0,
                'medium' => 1.4,
                'high' => 1.9
            ),
            'futturu_simulator_addon_values' => self::get_default_addon_values(),
            'futturu_simulator_hosting_values' => self::get_default_hosting_values(),
            'futturu_simulator_maintenance_values' => self::get_default_maintenance_values(),
            'futturu_simulator_texts' => self::get_default_texts()
        );

        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                update_option($option, $value);
            }
        }
    }

    public static function get_default_base_values() {
        return array(
            'blog' => 2500,
            'news' => 3500,
            'portfolio' => 3000,
            'hotsite' => 4000,
            'institutional' => 5000,
            'ecommerce' => 8000,
            'other' => 5000
        );
    }

    public static function get_default_addon_values() {
        return array(
            'faq_page' => 300,
            'event_calendar' => 500,
            'registration_form' => 400,
            'login_area' => 600,
            'product_search' => 700,
            'ecommerce' => 3000,
            'sitemap' => 200,
            'custom_menu' => 300,
            'newsletter' => 400,
            'reviews' => 500,
            'quizzes' => 600,
            'tutorial_videos' => 500,
            'ads' => 400,
            'budget_calculator' => 800,
            'career_pages' => 500,
            'corporate_videos' => 600,
            'phone_support' => 300,
            'booking_system' => 1000,
            'vfaq' => 700,
            'translations' => 800,
            'comparison_tool' => 900
        );
    }

    public static function get_default_hosting_values() {
        return array(
            'shared_annual' => 360,
            'cloud_basic_annual' => 1200,
            'cloud_pro_annual' => 2400,
            'cloud_premium_annual' => 4800,
            'domain_registration' => 50
        );
    }

    public static function get_default_maintenance_values() {
        return array(
            'monthly_basic' => 150,
            'monthly_standard' => 300,
            'monthly_premium' => 600
        );
    }

    public static function get_default_texts() {
        return array(
            'simulator_title' => 'Simulador de Criação de Sites Futturu',
            'simulator_subtitle' => 'Descubra o investimento ideal para o seu projeto web',
            'step_labels' => array(
                'Identificação do Projeto',
                'Conteúdo e Estrutura',
                'Recursos Adicionais',
                'Marketing Digital e SEO',
                'Domínio e Hospedagem',
                'Manutenção',
                'Investimento e Expectativas',
                'Dados do Cliente',
                'Resumo e Confirmação'
            )
        );
    }

    public static function insert_simulation($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    public static function get_simulation($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }

    public static function get_simulations($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $defaults = array(
            'limit' => 50,
            'offset' => 0,
            'status' => null,
            'orderby' => 'submission_date',
            'order' => 'DESC'
        );

        $args = wp_parse_args($args, $defaults);

        $where = '1=1';
        if ($args['status']) {
            $where .= $wpdb->prepare(' AND status = %s', $args['status']);
        }

        $sql = "SELECT * FROM $table_name WHERE $where 
                ORDER BY {$args['orderby']} {$args['order']} 
                LIMIT %d OFFSET %d";

        return $wpdb->get_results($wpdb->prepare($sql, $args['limit'], $args['offset']));
    }

    public static function update_simulation_status($id, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        return $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id)
        );
    }

    public static function get_simulation_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
}
