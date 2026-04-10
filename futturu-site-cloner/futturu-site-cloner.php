<?php
/**
 * Plugin Name: Futturu Site Cloner
 * Plugin URI: https://futturu.com.br/site-cloner
 * Description: Interface gráfica simplificada para wget, permitindo backup, espelhamento ou clonagem de sites estáticos diretamente pelo painel do WordPress.
 * Version: 1.0.0
 * Author: Futturu
 * Author URI: https://futturu.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: futturu-site-cloner
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FSC_VERSION', '1.0.0');
define('FSC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FSC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FSC_BACKUP_DIR', WP_CONTENT_DIR . '/futturu-backups/');

/**
 * Main Plugin Class
 */
class Futturu_Site_Cloner {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_fsc_clone_site', array($this, 'ajax_clone_site'));
        add_action('wp_ajax_fsc_delete_backup', array($this, 'ajax_delete_backup'));
        add_action('wp_ajax_fsc_download_backup', array($this, 'ajax_download_backup'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create backup directory
        if (!file_exists(FSC_BACKUP_DIR)) {
            wp_mkdir_p(FSC_BACKUP_DIR);
        }
        
        // Create .htaccess to protect backup directory
        $htaccess_content = "Order Deny,Allow\nDeny from all\n";
        file_put_contents(FSC_BACKUP_DIR . '.htaccess', $htaccess_content);
        
        // Create index.php to prevent directory listing
        file_put_contents(FSC_BACKUP_DIR . 'index.php', '<?php // Silence is golden' . "\n");
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Futturu Tools', 'futturu-site-cloner'),
            __('Futturu Tools', 'futturu-site-cloner'),
            'manage_options',
            'futturu-tools',
            array($this, 'render_main_page'),
            'dashicons-admin-site',
            30
        );
        
        // Clone Site submenu
        add_submenu_page(
            'futturu-tools',
            __('Clone Site', 'futturu-site-cloner'),
            __('Clone Site', 'futturu-site-cloner'),
            'manage_options',
            'futturu-tools',
            array($this, 'render_main_page')
        );
        
        // Backups submenu
        add_submenu_page(
            'futturu-tools',
            __('Backups', 'futturu-site-cloner'),
            __('Backups', 'futturu-site-cloner'),
            'manage_options',
            'fsc-backups',
            array($this, 'render_backups_page')
        );
        
        // Help submenu
        add_submenu_page(
            'futturu-tools',
            __('Clone Site Help', 'futturu-site-cloner'),
            __('Clone Site Help', 'futturu-site-cloner'),
            'manage_options',
            'fsc-help',
            array($this, 'render_help_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'futturu-tools') === false && strpos($hook, 'fsc-') === false) {
            return;
        }
        
        wp_enqueue_style('fsc-admin-style', FSC_PLUGIN_URL . 'assets/css/admin.css', array(), FSC_VERSION);
        wp_enqueue_script('fsc-admin-script', FSC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), FSC_VERSION, true);
        
        wp_localize_script('fsc-admin-script', 'fscAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fsc_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this backup?', 'futturu-site-cloner'),
                'cloning' => __('Cloning in progress...', 'futturu-site-cloner'),
                'completed' => __('Completed!', 'futturu-site-cloner'),
                'error' => __('Error!', 'futturu-site-cloner')
            )
        ));
    }
    
    /**
     * Render main page (Clone Site)
     */
    public function render_main_page() {
        ?>
        <div class="wrap fsc-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="fsc-container">
                <div class="fsc-card">
                    <h2><?php _e('Clone Website', 'futturu-site-cloner'); ?></h2>
                    <p class="description"><?php _e('Create a static mirror of any website using wget.', 'futturu-site-cloner'); ?></p>
                    
                    <form id="fsc-clone-form" method="post">
                        <?php wp_nonce_field('fsc_clone_action', 'fsc_nonce'); ?>
                        
                        <div class="fsc-form-group">
                            <label for="fsc_url"><?php _e('Website URL', 'futturu-site-cloner'); ?> <span class="required">*</span></label>
                            <input type="url" id="fsc_url" name="fsc_url" required 
                                   placeholder="https://example.com/" 
                                   class="regular-text"
                                   pattern="https?://.+">
                            <p class="description"><?php _e('Enter the full URL including http:// or https://', 'futturu-site-cloner'); ?></p>
                        </div>
                        
                        <div class="fsc-form-group">
                            <label for="fsc_site_name"><?php _e('Backup Name (Optional)', 'futturu-site-cloner'); ?></label>
                            <input type="text" id="fsc_site_name" name="fsc_site_name" 
                                   placeholder="<?php esc_attr_e('Auto-generated from URL', 'futturu-site-cloner'); ?>" 
                                   class="regular-text">
                            <p class="description"><?php _e('Leave empty to auto-generate from domain name', 'futturu-site-cloner'); ?></p>
                        </div>
                        
                        <div class="fsc-advanced-options">
                            <button type="button" class="button-link" id="fsc-toggle-advanced">
                                <?php _e('Advanced Options', 'futturu-site-cloner'); ?> 
                                <span class="dashicons dashicons-arrow-down"></span>
                            </button>
                            
                            <div id="fsc-advanced-panel" style="display:none;">
                                <div class="fsc-form-group">
                                    <label for="fsc_ignore_files"><?php _e('Ignore File Extensions', 'futturu-site-cloner'); ?></label>
                                    <input type="text" id="fsc_ignore_files" name="fsc_ignore_files" 
                                           placeholder="zip,mp4,avi,mkv" 
                                           class="regular-text">
                                    <p class="description"><?php _e('Comma-separated list of file extensions to ignore', 'futturu-site-cloner'); ?></p>
                                </div>
                                
                                <div class="fsc-form-row">
                                    <div class="fsc-form-group">
                                        <label for="fsc_tries"><?php _e('Max Tries', 'futturu-site-cloner'); ?></label>
                                        <input type="number" id="fsc_tries" name="fsc_tries" value="10" min="1" max="100" class="small-text">
                                    </div>
                                    
                                    <div class="fsc-form-group">
                                        <label for="fsc_timeout"><?php _e('Timeout (seconds)', 'futturu-site-cloner'); ?></label>
                                        <input type="number" id="fsc_timeout" name="fsc_timeout" value="30" min="5" max="300" class="small-text">
                                    </div>
                                </div>
                                
                                <div class="fsc-form-group">
                                    <label for="fsc_user_agent"><?php _e('User Agent', 'futturu-site-cloner'); ?></label>
                                    <input type="text" id="fsc_user_agent" name="fsc_user_agent" 
                                           value="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36" 
                                           class="regular-text">
                                </div>
                                
                                <div class="fsc-form-group">
                                    <label>
                                        <input type="checkbox" id="fsc_ignore_robots" name="fsc_ignore_robots" value="1" checked>
                                        <?php _e('Ignore robots.txt', 'futturu-site-cloner'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="fsc-form-actions">
                            <button type="submit" class="button button-primary button-large" id="fsc-submit-btn">
                                <span class="dashicons dashicons-download"></span>
                                <?php _e('Clone Site', 'futturu-site-cloner'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="fsc-card">
                    <h2><?php _e('Live Log', 'futturu-site-cloner'); ?></h2>
                    <div id="fsc-log-container">
                        <div id="fsc-log" class="fsc-log">
                            <p class="fsc-log-info"><?php _e('Ready to start cloning. Enter a URL above and click "Clone Site".', 'futturu-site-cloner'); ?></p>
                        </div>
                        <div id="fsc-progress-container" style="display:none;">
                            <div class="fsc-progress-bar">
                                <div id="fsc-progress" class="fsc-progress-fill"></div>
                            </div>
                            <p id="fsc-status"><?php _e('Status: Waiting...', 'futturu-site-cloner'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render backups page
     */
    public function render_backups_page() {
        $backups = $this->get_backups_list();
        ?>
        <div class="wrap fsc-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="fsc-container">
                <div class="fsc-card">
                    <h2><?php _e('Saved Backups', 'futturu-site-cloner'); ?></h2>
                    
                    <?php if (empty($backups)) : ?>
                        <p class="description"><?php _e('No backups found. Clone a site to see it here.', 'futturu-site-cloner'); ?></p>
                    <?php else : ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Name', 'futturu-site-cloner'); ?></th>
                                    <th><?php _e('URL', 'futturu-site-cloner'); ?></th>
                                    <th><?php _e('Date', 'futturu-site-cloner'); ?></th>
                                    <th><?php _e('Size', 'futturu-site-cloner'); ?></th>
                                    <th><?php _e('Status', 'futturu-site-cloner'); ?></th>
                                    <th><?php _e('Actions', 'futturu-site-cloner'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup) : ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($backup['name']); ?></strong></td>
                                        <td><a href="<?php echo esc_url($backup['url']); ?>" target="_blank"><?php echo esc_html($backup['url']); ?></a></td>
                                        <td><?php echo esc_html($backup['date']); ?></td>
                                        <td><?php echo esc_html($backup['size']); ?></td>
                                        <td>
                                            <span class="fsc-status fsc-status-<?php echo esc_attr($backup['status']); ?>">
                                                <?php echo esc_html($backup['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="button fsc-download-btn" data-backup="<?php echo esc_attr($backup['name']); ?>">
                                                <span class="dashicons dashicons-archive"></span>
                                                <?php _e('Download ZIP', 'futturu-site-cloner'); ?>
                                            </button>
                                            <button class="button fsc-delete-btn" data-backup="<?php echo esc_attr($backup['name']); ?>">
                                                <span class="dashicons dashicons-trash"></span>
                                                <?php _e('Delete', 'futturu-site-cloner'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render help page
     */
    public function render_help_page() {
        ?>
        <div class="wrap fsc-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="fsc-container">
                <div class="fsc-card">
                    <h2><?php _e('About Futturu Site Cloner', 'futturu-site-cloner'); ?></h2>
                    <p><?php _e('Futturu Site Cloner transforms the powerful wget command-line tool into an easy-to-use WordPress interface. Perfect for web professionals who need to quickly backup, mirror, or clone static websites directly from the WordPress admin panel.', 'futturu-site-cloner'); ?></p>
                    
                    <h3><?php _e('How to Use', 'futturu-site-cloner'); ?></h3>
                    <ol>
                        <li>
                            <strong><?php _e('Navigate to Futturu Tools > Clone Site', 'futturu-site-cloner'); ?></strong>
                            <p><?php _e('Access the cloning interface from the WordPress admin menu.', 'futturu-site-cloner'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Enter the Website URL', 'futturu-site-cloner'); ?></strong>
                            <p><?php _e('Input the full URL of the site you want to clone (must include http:// or https://).', 'futturu-site-cloner'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Configure Advanced Options (Optional)', 'futturu-site-cloner'); ?></strong>
                            <p><?php _e('Expand the advanced options to customize file exclusions, timeout settings, user agent, and more.', 'futturu-site-cloner'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Click "Clone Site"', 'futturu-site-cloner'); ?></strong>
                            <p><?php _e('The process will start and you\'ll see real-time logs of the wget command execution.', 'futturu-site-cloner'); ?></p>
                        </li>
                        <li>
                            <strong><?php _e('Download Your Backup', 'futturu-site-cloner'); ?></strong>
                            <p><?php _e('Once completed, go to the Backups page to download the cloned site as a ZIP file.', 'futturu-site-cloner'); ?></p>
                        </li>
                    </ol>
                    
                    <h3><?php _e('Example wget Command', 'futturu-site-cloner'); ?></h3>
                    <div class="fsc-code-block">
                        <code>wget --mirror --convert-links --adjust-extension --page-requisites --no-parent -e robots=off -U "Mozilla/5.0" --tries=10 --timeout=30 -P "/path/to/destination/" "https://example.com/"</code>
                    </div>
                    
                    <h3><?php _e('Parameter Explanation', 'futturu-site-cloner'); ?></h3>
                    <ul>
                        <li><code>--mirror</code>: Turns on options suitable for mirroring (recursion, infinite depth, etc.)</li>
                        <li><code>--convert-links</code>: Converts links in downloaded HTML to make them work locally</li>
                        <li><code>--adjust-extension</code>: Saves files with proper extensions (.html, etc.)</li>
                        <li><code>--page-requisites</code>: Downloads all necessary files to display pages properly (CSS, images, etc.)</li>
                        <li><code>--no-parent</code>: Never ascend to the parent directory when retrieving recursively</li>
                        <li><code>-e robots=off</code>: Ignores robots.txt restrictions</li>
                        <li><code>-U "Mozilla/5.0"</code>: Sets a custom User Agent string</li>
                        <li><code>--tries=N</code>: Number of retries for failed downloads</li>
                        <li><code>--timeout=N</code>: Timeout in seconds for network operations</li>
                        <li><code>-P</code>: Directory prefix where files will be saved</li>
                    </ul>
                    
                    <h3><?php _e('Limitations', 'futturu-site-cloner'); ?></h3>
                    <ul>
                        <li><?php _e('<strong>Dynamic Content:</strong> This tool works best with static websites. Sites that rely heavily on JavaScript, databases, or server-side processing may not clone perfectly.', 'futturu-site-cloner'); ?></li>
                        <li><?php _e('<strong>Authentication:</strong> Sites requiring login credentials cannot be cloned with this tool.', 'futturu-site-cloner'); ?></li>
                        <li><?php _e('<strong>Large Sites:</strong> Very large websites may take significant time and server resources to clone.', 'futturu-site-cloner'); ?></li>
                        <li><?php _e('<strong>Server Requirements:</strong> Your hosting must have wget installed and allow PHP to execute shell commands.', 'futturu-site-cloner'); ?></li>
                    </ul>
                    
                    <h3><?php _e('Security Considerations', 'futturu-site-cloner'); ?></h3>
                    <ul>
                        <li><?php _e('All backups are stored in a protected directory (<code>/wp-content/futturu-backups/</code>) outside public access.', 'futturu-site-cloner'); ?></li>
                        <li><?php _e('The backup directory includes .htaccess protection to prevent direct browser access.', 'futturu-site-cloner'); ?></li>
                        <li><?php _e('All user inputs are sanitized and validated before use.', 'futturu-site-cloner'); ?></li>
                        <li><?php _e('Only users with administrator privileges can access this plugin.', 'futturu-site-cloner'); ?></li>
                        <li><?php _e('wget commands are executed with strict parameter validation to prevent command injection.', 'futturu-site-cloner'); ?></li>
                    </ul>
                    
                    <h3><?php _e('Support', 'futturu-site-cloner'); ?></h3>
                    <p><?php _e('For support and updates, visit:', 'futturu-site-cloner'); ?> <a href="https://futturu.com.br" target="_blank">https://futturu.com.br</a></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for cloning site
     */
    public function ajax_clone_site() {
        check_ajax_referer('fsc_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'futturu-site-cloner')));
        }
        
        // Validate and sanitize inputs
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $site_name = isset($_POST['site_name']) ? sanitize_text_field($_POST['site_name']) : '';
        $ignore_files = isset($_POST['ignore_files']) ? sanitize_text_field($_POST['ignore_files']) : '';
        $tries = isset($_POST['tries']) ? absint($_POST['tries']) : 10;
        $timeout = isset($_POST['timeout']) ? absint($_POST['timeout']) : 30;
        $user_agent = isset($_POST['user_agent']) ? sanitize_text_field($_POST['user_agent']) : 'Mozilla/5.0';
        $ignore_robots = isset($_POST['ignore_robots']) ? true : false;
        
        // Validate URL
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(array('message' => __('Invalid URL provided', 'futturu-site-cloner')));
        }
        
        // Generate site name from URL if not provided
        if (empty($site_name)) {
            $parsed_url = parse_url($url);
            $site_name = isset($parsed_url['host']) ? $parsed_url['host'] : 'unknown-site';
            $site_name = preg_replace('/[^a-zA-Z0-9_-]/', '-', $site_name);
        } else {
            $site_name = preg_replace('/[^a-zA-Z0-9_-]/', '-', $site_name);
        }
        
        // Ensure backup directory exists
        if (!file_exists(FSC_BACKUP_DIR)) {
            wp_mkdir_p(FSC_BACKUP_DIR);
        }
        
        $backup_path = trailingslashit(FSC_BACKUP_DIR) . $site_name;
        
        // Build wget command
        $command = escapeshellarg($url);
        $output_dir = escapeshellarg($backup_path);
        
        $wget_cmd = "wget --mirror --convert-links --adjust-extension --page-requisites --no-parent";
        
        if ($ignore_robots) {
            $wget_cmd .= " -e robots=off";
        }
        
        $wget_cmd .= " -U " . escapeshellarg($user_agent);
        $wget_cmd .= " --tries=" . escapeshellarg($tries);
        $wget_cmd .= " --timeout=" . escapeshellarg($timeout);
        
        if (!empty($ignore_files)) {
            $extensions = array_map('trim', explode(',', $ignore_files));
            $extensions = array_filter($extensions);
            if (!empty($extensions)) {
                $reject_list = implode(',', $extensions);
                $wget_cmd .= " --reject=" . escapeshellarg($reject_list);
            }
        }
        
        $wget_cmd .= " -P " . $output_dir . " " . $command;
        
        // Log the command
        $log = array(
            'command' => $wget_cmd,
            'status' => 'starting',
            'message' => __('Starting clone process...', 'futturu-site-cloner')
        );
        wp_send_json_success($log);
    }
    
    /**
     * Execute wget command (called via background process)
     */
    public function execute_wget($wget_cmd, $backup_name) {
        $backup_path = trailingslashit(FSC_BACKUP_DIR) . $backup_name;
        $log_file = $backup_path . '/clone.log';
        
        // Create backup subdirectory
        if (!file_exists($backup_path)) {
            wp_mkdir_p($backup_path);
        }
        
        // Execute wget command
        $output = array();
        $return_code = 0;
        
        exec($wget_cmd . ' 2>&1', $output, $return_code);
        
        // Save log
        file_put_contents($log_file, implode("\n", $output));
        
        // Calculate size
        $size = $this->get_directory_size($backup_path);
        
        // Save metadata
        $metadata = array(
            'name' => $backup_name,
            'url' => $_POST['url'] ?? '',
            'date' => current_time('mysql'),
            'size' => $size,
            'status' => $return_code === 0 ? 'completed' : 'error',
            'log' => $output,
            'return_code' => $return_code
        );
        
        file_put_contents($backup_path . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
        
        return array(
            'success' => $return_code === 0,
            'output' => $output,
            'return_code' => $return_code,
            'size' => $size
        );
    }
    
    /**
     * Get directory size
     */
    private function get_directory_size($path) {
        $size = 0;
        
        if (!file_exists($path)) {
            return '0 B';
        }
        
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $unit_index = 0;
        
        while ($size >= 1024 && $unit_index < count($units) - 1) {
            $size /= 1024;
            $unit_index++;
        }
        
        return round($size, 2) . ' ' . $units[$unit_index];
    }
    
    /**
     * Get list of backups
     */
    private function get_backups_list() {
        $backups = array();
        
        if (!file_exists(FSC_BACKUP_DIR)) {
            return $backups;
        }
        
        $directories = glob(FSC_BACKUP_DIR . '*', GLOB_ONLYDIR);
        
        foreach ($directories as $dir) {
            $dirname = basename($dir);
            $metadata_file = $dir . '/metadata.json';
            
            if (file_exists($metadata_file)) {
                $metadata = json_decode(file_get_contents($metadata_file), true);
                if ($metadata) {
                    $backups[] = $metadata;
                    continue;
                }
            }
            
            // Fallback if no metadata
            $backups[] = array(
                'name' => $dirname,
                'url' => '',
                'date' => date('Y-m-d H:i:s', filemtime($dir)),
                'size' => $this->get_directory_size($dir),
                'status' => 'unknown'
            );
        }
        
        // Sort by date descending
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * AJAX handler for deleting backup
     */
    public function ajax_delete_backup() {
        check_ajax_referer('fsc_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'futturu-site-cloner')));
        }
        
        $backup_name = isset($_POST['backup_name']) ? sanitize_text_field($_POST['backup_name']) : '';
        
        if (empty($backup_name)) {
            wp_send_json_error(array('message' => __('Invalid backup name', 'futturu-site-cloner')));
        }
        
        // Prevent directory traversal
        $backup_name = basename($backup_name);
        $backup_path = trailingslashit(FSC_BACKUP_DIR) . $backup_name;
        
        if (!file_exists($backup_path)) {
            wp_send_json_error(array('message' => __('Backup not found', 'futturu-site-cloner')));
        }
        
        // Delete directory
        $this->delete_directory($backup_path);
        
        wp_send_json_success(array('message' => __('Backup deleted successfully', 'futturu-site-cloner')));
    }
    
    /**
     * AJAX handler for downloading backup
     */
    public function ajax_download_backup() {
        check_ajax_referer('fsc_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'futturu-site-cloner')));
        }
        
        $backup_name = isset($_POST['backup_name']) ? sanitize_text_field($_POST['backup_name']) : '';
        
        if (empty($backup_name)) {
            wp_send_json_error(array('message' => __('Invalid backup name', 'futturu-site-cloner')));
        }
        
        // Prevent directory traversal
        $backup_name = basename($backup_name);
        $backup_path = trailingslashit(FSC_BACKUP_DIR) . $backup_name;
        
        if (!file_exists($backup_path)) {
            wp_send_json_error(array('message' => __('Backup not found', 'futturu-site-cloner')));
        }
        
        // Create ZIP file
        $zip_file = FSC_BACKUP_DIR . $backup_name . '.zip';
        
        $this->create_zip($backup_path, $zip_file);
        
        if (!file_exists($zip_file)) {
            wp_send_json_error(array('message' => __('Failed to create ZIP file', 'futturu-site-cloner')));
        }
        
        wp_send_json_success(array(
            'message' => __('ZIP file created successfully', 'futturu-site-cloner'),
            'download_url' => wp_nonce_url(admin_url('admin-post.php?action=fsc_download&backup=' . $backup_name), 'fsc_download_' . $backup_name)
        ));
    }
    
    /**
     * Recursively delete directory
     */
    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Create ZIP archive
     */
    private function create_zip($source, $destination) {
        if (!class_exists('ZipArchive')) {
            return false;
        }
        
        $zip = new ZipArchive();
        
        if (!$zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            return false;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $relative_path = substr($file->getPathname(), strlen($source) + 1);
                $zip->addFile($file->getPathname(), $relative_path);
            }
        }
        
        return $zip->close();
    }
}

// Initialize plugin
function futturu_site_cloner_init() {
    return Futturu_Site_Cloner::get_instance();
}

add_action('plugins_loaded', 'futturu_site_cloner_init');

// Handle ZIP download
add_action('admin_post_fsc_download', function() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized', 'futturu-site-cloner'));
    }
    
    $backup_name = isset($_GET['backup']) ? sanitize_text_field($_GET['backup']) : '';
    
    if (empty($backup_name)) {
        wp_die(__('Invalid backup name', 'futturu-site-cloner'));
    }
    
    check_admin_referer('fsc_download_' . $backup_name);
    
    $backup_name = basename($backup_name);
    $zip_file = trailingslashit(FSC_BACKUP_DIR) . $backup_name . '.zip';
    
    if (!file_exists($zip_file)) {
        wp_die(__('ZIP file not found', 'futturu-site-cloner'));
    }
    
    // Force download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $backup_name . '.zip"');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);
    exit;
});
