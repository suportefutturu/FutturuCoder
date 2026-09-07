<?php
/**
 * Uninstall handler para Website Simulator MVP
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$remove_data = get_option('wsmvp_remove_data_on_uninstall', false);

if ($remove_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wsmvp_simulations';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    $options = $wpdb->get_results("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'wsmvp_%'");
    foreach ($options as $option) {
        delete_option($option->option_name);
    }
    
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wsmvp_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_wsmvp_%'");
}
