<?php
/**
 * Data Handler Class for Futturu Micro-Commitment Plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class FMC_Data {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Initialization if needed
    }
    
    /**
     * Save user response to database
     */
    public static function save_response($session_id, $question_id, $answer, $path_taken = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmc_responses';
        
        $track_ip = get_option('fmc_track_ip', false);
        $user_ip = $track_ip ? sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '') : '';
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        $wpdb->insert(
            $table_name,
            array(
                'session_id' => sanitize_text_field($session_id),
                'question_id' => sanitize_text_field($question_id),
                'answer' => sanitize_textarea_field($answer),
                'path_taken' => sanitize_text_field($path_taken),
                'user_ip' => $user_ip,
                'user_agent' => $user_agent,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get responses by session ID
     */
    public static function get_session_responses($session_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmc_responses';
        
        $responses = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE session_id = %s ORDER BY created_at ASC",
            $session_id
        ), ARRAY_A);
        
        return $responses;
    }
    
    /**
     * Get all responses with pagination
     */
    public static function get_all_responses($limit = 100, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmc_responses';
        
        $responses = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ), ARRAY_A);
        
        return $responses;
    }
    
    /**
     * Get response statistics
     */
    public static function get_statistics() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmc_responses';
        
        $stats = array();
        
        // Total responses
        $stats['total_responses'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Unique sessions
        $stats['unique_sessions'] = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM $table_name");
        
        // Responses per question
        $stats['per_question'] = $wpdb->get_results(
            "SELECT question_id, COUNT(*) as count FROM $table_name GROUP BY question_id",
            ARRAY_A
        );
        
        // Recent activity (last 7 days)
        $stats['recent_activity'] = $wpdb->get_results(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM $table_name 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            ARRAY_A
        );
        
        return $stats;
    }
    
    /**
     * Export responses to CSV
     */
    public static function export_to_csv() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fmc_responses';
        
        $responses = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fmc-responses-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, array('ID', 'Session ID', 'Question ID', 'Answer', 'Path Taken', 'User IP', 'Created At'));
        
        // Data
        foreach ($responses as $response) {
            fputcsv($output, $response);
        }
        
        fclose($output);
        exit;
    }
}
