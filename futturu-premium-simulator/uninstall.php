<?php
/**
 * Uninstall Handler
 * Safely removes plugin data when uninstalling
 * 
 * @package Futturu_Premium_Simulator
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Security check - only allow users with manage_options capability
if (!current_user_can('manage_options')) {
    exit;
}

global $wpdb;

// Drop the custom table
$table_name = $wpdb->prefix . 'futturu_premium_simulations';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// Delete all plugin options
delete_option('futturu_premium_active');
delete_option('futturu_premium_email_destination');
delete_option('futturu_premium_base_values');
delete_option('futturu_premium_complexity_multipliers');
delete_option('futturu_premium_addon_costs');
delete_option('futturu_premium_hosting_plans');
delete_option('futturu_premium_maintenance_plans');

// Clear any scheduled hooks (if any)
wp_clear_scheduled_hook('futturu_premium_daily_cleanup');

// Note: We intentionally do NOT delete transients or other temporary data
// to avoid affecting other plugins that might use similar transient names.

// Log the uninstallation for audit purposes (optional)
error_log('Futturu Premium Simulator plugin has been uninstalled. Table and options removed.');
