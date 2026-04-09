<?php
/**
 * Database Handler Class
 * Handles table creation, CRUD operations for simulations
 * 
 * @package Futturu_Premium_Simulator
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Futturu_Premium_Database {
    
    private static $instance = null;
    private static $table_name;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'futturu_premium_simulations';
    }
    
    /**
     * Create database table on plugin activation
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'futturu_premium_simulations';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            project_type varchar(50) NOT NULL,
            site_category varchar(100) NOT NULL,
            complexity varchar(20) NOT NULL,
            pages_count int(11) NOT NULL DEFAULT 0,
            pages_checklist text NOT NULL,
            languages varchar(100) DEFAULT '',
            text_origin varchar(100) DEFAULT '',
            image_origin varchar(100) DEFAULT '',
            addons_selected text NOT NULL,
            google_integrations text NOT NULL,
            seo_level varchar(50) DEFAULT '',
            domain_status varchar(50) DEFAULT '',
            current_hosting varchar(100) DEFAULT '',
            cloud_interest varchar(10) DEFAULT '',
            server_resources text DEFAULT '',
            maintenance_frequency varchar(50) DEFAULT '',
            maintenance_plan varchar(50) DEFAULT '',
            company_category varchar(50) DEFAULT '',
            budget_range varchar(50) DEFAULT '',
            desired_deadline varchar(50) DEFAULT '',
            meeting_type varchar(50) DEFAULT '',
            client_name varchar(200) NOT NULL,
            client_email varchar(200) NOT NULL,
            client_phone varchar(50) NOT NULL,
            client_cnpj varchar(50) DEFAULT '',
            client_segment varchar(100) DEFAULT '',
            how_found_us varchar(100) DEFAULT '',
            observations text DEFAULT '',
            estimated_value_internal decimal(10,2) DEFAULT 0.00,
            hosting_cost_annual decimal(10,2) DEFAULT 0.00,
            maintenance_cost_annual decimal(10,2) DEFAULT 0.00,
            total_estimated decimal(10,2) DEFAULT 0.00,
            ip_address varchar(50) DEFAULT '',
            user_agent text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'new',
            PRIMARY KEY  (id),
            KEY client_email (client_email),
            KEY created_at (created_at),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Insert simulation data
     * 
     * @param array $data Simulation data
     * @return int|WP_Error Insertion ID or error
     */
    public function insert_simulation($data) {
        global $wpdb;
        
        $sanitized_data = array(
            'project_type' => sanitize_text_field($data['project_type']),
            'site_category' => sanitize_text_field($data['site_category']),
            'complexity' => sanitize_text_field($data['complexity']),
            'pages_count' => absint($data['pages_count']),
            'pages_checklist' => wp_json_encode(array_map('sanitize_text_field', $data['pages_checklist'])),
            'languages' => sanitize_text_field($data['languages']),
            'text_origin' => sanitize_text_field($data['text_origin']),
            'image_origin' => sanitize_text_field($data['image_origin']),
            'addons_selected' => wp_json_encode(array_map('sanitize_text_field', $data['addons_selected'])),
            'google_integrations' => wp_json_encode(array_map('sanitize_text_field', $data['google_integrations'])),
            'seo_level' => sanitize_text_field($data['seo_level']),
            'domain_status' => sanitize_text_field($data['domain_status']),
            'current_hosting' => sanitize_text_field($data['current_hosting']),
            'cloud_interest' => sanitize_text_field($data['cloud_interest']),
            'server_resources' => wp_json_encode(array_map('sanitize_text_field', $data['server_resources'])),
            'maintenance_frequency' => sanitize_text_field($data['maintenance_frequency']),
            'maintenance_plan' => sanitize_text_field($data['maintenance_plan']),
            'company_category' => sanitize_text_field($data['company_category']),
            'budget_range' => sanitize_text_field($data['budget_range']),
            'desired_deadline' => sanitize_text_field($data['desired_deadline']),
            'meeting_type' => sanitize_text_field($data['meeting_type']),
            'client_name' => sanitize_text_field($data['client_name']),
            'client_email' => sanitize_email($data['client_email']),
            'client_phone' => sanitize_text_field($data['client_phone']),
            'client_cnpj' => sanitize_text_field($data['client_cnpj']),
            'client_segment' => sanitize_text_field($data['client_segment']),
            'how_found_us' => sanitize_text_field($data['how_found_us']),
            'observations' => sanitize_textarea_field($data['observations']),
            'estimated_value_internal' => floatval($data['estimated_value_internal']),
            'hosting_cost_annual' => floatval($data['hosting_cost_annual']),
            'maintenance_cost_annual' => floatval($data['maintenance_cost_annual']),
            'total_estimated' => floatval($data['total_estimated']),
            'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        );
        
        $format = array(
            '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s'
        );
        
        $result = $wpdb->insert(self::$table_name, $sanitized_data, $format);
        
        if ($result === false) {
            return new WP_Error('db_insert_error', $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get all simulations with pagination
     * 
     * @param int $per_page Items per page
     * @param int $page Current page
     * @param string $orderby Order by field
     * @param string $order Order direction
     * @return array Results
     */
    public function get_simulations($per_page = 20, $page = 1, $orderby = 'created_at', $order = 'DESC') {
        global $wpdb;
        
        $offset = ($page - 1) * $per_page;
        $orderby = sanitize_sql_orderby("$orderby $order");
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " ORDER BY " . $orderby . " LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ), ARRAY_A);
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM " . self::$table_name);
        
        return array(
            'items' => $results,
            'total' => intval($total),
            'total_pages' => ceil($total / $per_page)
        );
    }
    
    /**
     * Get single simulation by ID
     * 
     * @param int $id Simulation ID
     * @return array|null Simulation data or null
     */
    public function get_simulation($id) {
        global $wpdb;
        
        $id = absint($id);
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE id = %d",
            $id
        ), ARRAY_A);
        
        return $result;
    }
    
    /**
     * Update simulation status
     * 
     * @param int $id Simulation ID
     * @param string $status New status
     * @return bool Success
     */
    public function update_status($id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            self::$table_name,
            array('status' => sanitize_text_field($status)),
            array('id' => absint($id)),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Delete simulation
     * 
     * @param int $id Simulation ID
     * @return bool Success
     */
    public function delete_simulation($id) {
        global $wpdb;
        
        return $wpdb->delete(
            self::$table_name,
            array('id' => absint($id)),
            array('%d')
        );
    }
    
    /**
     * Get table name
     * 
     * @return string Table name
     */
    public static function get_table_name() {
        return self::$table_name;
    }
}
