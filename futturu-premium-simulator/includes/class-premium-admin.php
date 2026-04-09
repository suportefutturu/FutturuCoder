<?php
/**
 * Admin Handler Class
 * Handles admin menu, simulations list, and settings
 * 
 * @package Futturu_Premium_Simulator
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Futturu_Premium_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_futturu_premium_get_simulation', array($this, 'ajax_get_simulation'));
        add_action('wp_ajax_futturu_premium_update_status', array($this, 'ajax_update_status'));
        add_action('wp_ajax_futturu_premium_delete_simulation', array($this, 'ajax_delete_simulation'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Futturu Premium', 'futturu-premium-simulator'),
            __('Futturu Premium', 'futturu-premium-simulator'),
            'manage_options',
            'futturu-premium',
            array($this, 'render_dashboard_page'),
            'dashicons-analytics',
            30
        );
        
        add_submenu_page(
            'futturu-premium',
            __('Simulações Recebidas', 'futturu-premium-simulator'),
            __('Simulações', 'futturu-premium-simulator'),
            'manage_options',
            'futturu-premium-simulations',
            array($this, 'render_simulations_page')
        );
        
        add_submenu_page(
            'futturu-premium',
            __('Configurações', 'futturu-premium-simulator'),
            __('Configurações', 'futturu-premium-simulator'),
            'manage_options',
            'futturu-premium-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'futturu-premium') === false) {
            return;
        }
        
        wp_enqueue_style(
            'futturu-premium-admin-css',
            FUTTURU_PREMIUM_PLUGIN_URL . 'assets/css/premium-simulator.css',
            array(),
            FUTTURU_PREMIUM_VERSION
        );
        
        wp_enqueue_script(
            'futturu-premium-admin-js',
            FUTTURU_PREMIUM_PLUGIN_URL . 'assets/js/premium-simulator.js',
            array('jquery'),
            FUTTURU_PREMIUM_VERSION,
            true
        );
        
        wp_localize_script('futturu-premium-admin-js', 'futturuPremiumAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_premium_admin_nonce')
        ));
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        ?>
        <div class="wrap futturu-premium-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="futturu-dashboard-cards">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'futturu_premium_simulations';
                
                $total_simulations = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $new_simulations = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'new'");
                $total_value = $wpdb->get_var("SELECT SUM(total_estimated) FROM $table_name");
                
                // Get last 7 days stats
                $last_7_days = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
                ));
                ?>
                
                <div class="futturu-card">
                    <div class="card-icon">📊</div>
                    <div class="card-content">
                        <h3><?php echo number_format_i18n($total_simulations); ?></h3>
                        <p><?php _e('Total de Simulações', 'futturu-premium-simulator'); ?></p>
                    </div>
                </div>
                
                <div class="futturu-card">
                    <div class="card-icon">🆕</div>
                    <div class="card-content">
                        <h3><?php echo number_format_i18n($new_simulations); ?></h3>
                        <p><?php _e('Novos Leads', 'futturu-premium-simulator'); ?></p>
                    </div>
                </div>
                
                <div class="futturu-card">
                    <div class="card-icon">📈</div>
                    <div class="card-content">
                        <h3><?php echo number_format_i18n($last_7_days); ?></h3>
                        <p><?php _e('Últimos 7 Dias', 'futturu-premium-simulator'); ?></p>
                    </div>
                </div>
                
                <div class="futturu-card">
                    <div class="card-icon">💰</div>
                    <div class="card-content">
                        <h3>R$ <?php echo number_format($total_value ?? 0, 2, ',', '.'); ?></h3>
                        <p><?php _e('Valor Total Estimado', 'futturu-premium-simulator'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="futturu-quick-links">
                <h2><?php _e('Acesso Rápido', 'futturu-premium-simulator'); ?></h2>
                <a href="<?php echo admin_url('admin.php?page=futturu-premium-simulations'); ?>" class="button button-primary">
                    <?php _e('Ver Todas as Simulações', 'futturu-premium-simulator'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=futturu-premium-settings'); ?>" class="button">
                    <?php _e('Configurações', 'futturu-premium-simulator'); ?>
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render simulations list page
     */
    public function render_simulations_page() {
        $db = Futturu_Premium_Database::get_instance();
        
        // Pagination
        $per_page = 20;
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        
        // Filters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        
        $results = $db->get_simulations($per_page, $page);
        ?>
        
        <div class="wrap futturu-premium-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="get" class="futturu-filters">
                <input type="hidden" name="page" value="futturu-premium-simulations">
                
                <select name="status">
                    <option value=""><?php _e('Todos os Status', 'futturu-premium-simulator'); ?></option>
                    <option value="new" <?php selected($status_filter, 'new'); ?>><?php _e('Novo', 'futturu-premium-simulator'); ?></option>
                    <option value="contacted" <?php selected($status_filter, 'contacted'); ?>><?php _e('Contatado', 'futturu-premium-simulator'); ?></option>
                    <option value="qualified" <?php selected($status_filter, 'qualified'); ?>><?php _e('Qualificado', 'futturu-premium-simulator'); ?></option>
                    <option value="closed" <?php selected($status_filter, 'closed'); ?>><?php _e('Fechado', 'futturu-premium-simulator'); ?></option>
                    <option value="lost" <?php selected($status_filter, 'lost'); ?>><?php _e('Perdido', 'futturu-premium-simulator'); ?></option>
                </select>
                
                <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" placeholder="<?php _e('De', 'futturu-premium-simulator'); ?>">
                <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" placeholder="<?php _e('Até', 'futturu-premium-simulator'); ?>">
                
                <button type="submit" class="button"><?php _e('Filtrar', 'futturu-premium-simulator'); ?></button>
            </form>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Data', 'futturu-premium-simulator'); ?></th>
                        <th><?php _e('Cliente', 'futturu-premium-simulator'); ?></th>
                        <th><?php _e('Contato', 'futturu-premium-simulator'); ?></th>
                        <th><?php _e('Tipo de Site', 'futturu-premium-simulator'); ?></th>
                        <th><?php _e('Complexidade', 'futturu-premium-simulator'); ?></th>
                        <th><?php _e('Valor Estimado', 'futturu-premium-simulator'); ?></th>
                        <th><?php _e('Status', 'futturu-premium-simulator'); ?></th>
                        <th><?php _e('Ações', 'futturu-premium-simulator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results['items'])): ?>
                        <tr>
                            <td colspan="8"><?php _e('Nenhuma simulação encontrada.', 'futturu-premium-simulator'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results['items'] as $simulation): ?>
                            <tr>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($simulation['created_at'])); ?></td>
                                <td><strong><?php echo esc_html($simulation['client_name']); ?></strong></td>
                                <td>
                                    <?php echo esc_html($simulation['client_email']); ?><br>
                                    <small><?php echo esc_html($simulation['client_phone']); ?></small>
                                </td>
                                <td><?php echo esc_html(ucfirst($simulation['site_category'])); ?></td>
                                <td><?php echo esc_html(ucfirst($simulation['complexity'])); ?></td>
                                <td>R$ <?php echo number_format($simulation['total_estimated'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="futturu-status futturu-status-<?php echo esc_attr($simulation['status']); ?>">
                                        <?php echo esc_html(ucfirst($simulation['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="button button-small futturu-view-detail" data-id="<?php echo esc_attr($simulation['id']); ?>">
                                        <?php _e('Ver Detalhes', 'futturu-premium-simulator'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($results['total_pages'] > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $results['total_pages'],
                            'current' => $page
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Detail Modal -->
        <div id="futturu-detail-modal" class="futturu-modal" style="display:none;">
            <div class="futturu-modal-content">
                <span class="futturu-modal-close">&times;</span>
                <div id="futturu-modal-body"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Save settings if form submitted
        if (isset($_POST['futturu_save_settings']) && check_admin_referer('futturu_premium_settings', 'futturu_nonce')) {
            if (current_user_can('manage_options')) {
                update_option('futturu_premium_active', isset($_POST['futturu_premium_active']));
                update_option('futturu_premium_email_destination', sanitize_email($_POST['futturu_premium_email_destination']));
                update_option('futturu_premium_base_values', array_map('floatval', $_POST['base_values']));
                update_option('futturu_premium_complexity_multipliers', array_map('floatval', $_POST['complexity_multipliers']));
                update_option('futturu_premium_addon_costs', array_map('floatval', $_POST['addon_costs']));
                update_option('futturu_premium_hosting_plans', array_map('floatval', $_POST['hosting_plans']));
                update_option('futturu_premium_maintenance_plans', array_map('floatval', $_POST['maintenance_plans']));
                
                echo '<div class="notice notice-success"><p>' . __('Configurações salvas com sucesso!', 'futturu-premium-simulator') . '</p></div>';
            }
        }
        
        // Load current values
        $active = get_option('futturu_premium_active', true);
        $email_dest = get_option('futturu_premium_email_destination', 'suporte@futturu.com.br');
        $base_values = get_option('futturu_premium_base_values', array(
            'institucional' => 3500, 'ecommerce' => 8000, 'landing_page' => 2000,
            'portal' => 12000, 'blog' => 3000, 'marketplace' => 15000,
            'saas' => 18000, 'outro' => 4000
        ));
        $complexity_mult = get_option('futturu_premium_complexity_multipliers', array(
            'baixa' => 1.0, 'media' => 1.4, 'alta' => 1.9
        ));
        $addon_costs = get_option('futturu_premium_addon_costs', array(
            'faq' => 300, 'login_sistema' => 1500, 'newsletter' => 500
        ));
        $hosting_plans = get_option('futturu_premium_hosting_plans', array(
            'starter' => 600, 'professional' => 1200, 'business' => 2400, 'enterprise' => 4800
        ));
        $maintenance_plans = get_option('futturu_premium_maintenance_plans', array(
            'basico' => 1200, 'padrao' => 2400, 'premium' => 4800, 'empresarial' => 9600
        ));
        ?>
        
        <div class="wrap futturu-premium-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" class="futturu-settings-form">
                <?php wp_nonce_field('futturu_premium_settings', 'futturu_nonce'); ?>
                
                <h2><?php _e('Configurações Gerais', 'futturu-premium-simulator'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="futturu_premium_active"><?php _e('Ativar Simulador', 'futturu-premium-simulator'); ?></label></th>
                        <td>
                            <input type="checkbox" id="futturu_premium_active" name="futturu_premium_active" value="1" <?php checked($active); ?>>
                            <label for="futturu_premium_active"><?php _e('Simulador ativo e visível no frontend', 'futturu-premium-simulator'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="futturu_premium_email_destination"><?php _e('E-mail de Destino', 'futturu-premium-simulator'); ?></label></th>
                        <td>
                            <input type="email" id="futturu_premium_email_destination" name="futturu_premium_email_destination" value="<?php echo esc_attr($email_dest); ?>" class="regular-text">
                            <p class="description"><?php _e('E-mail que receberá as notificações de novas simulações.', 'futturu-premium-simulator'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Valores Base por Categoria (Tabela Sinapro)', 'futturu-premium-simulator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($base_values as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst($key)); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="base_values[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Multiplicadores de Complexidade', 'futturu-premium-simulator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($complexity_mult as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst($key)); ?></label></th>
                        <td>
                            <input type="number" step="0.1" name="complexity_multipliers[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Custos de Add-ons', 'futturu-premium-simulator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($addon_costs as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="addon_costs[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Planos de Hospedagem (Cloudez - Anual)', 'futturu-premium-simulator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($hosting_plans as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst($key)); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="hosting_plans[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h2><?php _e('Planos de Manutenção (Anual)', 'futturu-premium-simulator'); ?></h2>
                <table class="form-table">
                    <?php foreach ($maintenance_plans as $key => $value): ?>
                    <tr>
                        <th><label><?php echo esc_html(ucfirst($key)); ?></label></th>
                        <td>
                            <input type="number" step="0.01" name="maintenance_plans[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <?php submit_button(__('Salvar Configurações', 'futturu-premium-simulator'), 'primary', 'futturu_save_settings'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * AJAX: Get simulation details
     */
    public function ajax_get_simulation() {
        check_ajax_referer('futturu_premium_admin_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(array('message' => 'Invalid ID'));
        }
        
        $db = Futturu_Premium_Database::get_instance();
        $simulation = $db->get_simulation($id);
        
        if (!$simulation) {
            wp_send_json_error(array('message' => 'Simulation not found'));
        }
        
        wp_send_json_success(array('data' => $simulation));
    }
    
    /**
     * AJAX: Update simulation status
     */
    public function ajax_update_status() {
        check_ajax_referer('futturu_premium_admin_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (!$id || !$status) {
            wp_send_json_error(array('message' => 'Invalid parameters'));
        }
        
        $db = Futturu_Premium_Database::get_instance();
        $result = $db->update_status($id, $status);
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Error updating status'));
        }
        
        wp_send_json_success(array('message' => 'Status updated'));
    }
    
    /**
     * AJAX: Delete simulation
     */
    public function ajax_delete_simulation() {
        check_ajax_referer('futturu_premium_admin_nonce', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error(array('message' => 'Invalid ID'));
        }
        
        $db = Futturu_Premium_Database::get_instance();
        $result = $db->delete_simulation($id);
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Error deleting'));
        }
        
        wp_send_json_success(array('message' => 'Deleted'));
    }
}
