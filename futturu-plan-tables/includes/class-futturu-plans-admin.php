<?php
/**
 * Futturu Plans Admin Class
 * Handles admin panel for managing plan settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Plans_Admin {
    
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Handle AJAX save
        add_action('wp_ajax_futturu_save_settings', array($this, 'ajax_save_settings'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_options_page(
            'Planos Futturu',
            'Planos Futturu',
            'manage_options',
            'futturu-plan-tables',
            array($this, 'render_admin_page'),
            'dashicons-portfolio',
            30
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('futturu_plans_group', 'futturu_plans_settings');
        register_setting('futturu_plans_group', 'futturu_plans_contact_email');
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_futturu-plan-tables') {
            return;
        }
        
        wp_enqueue_style(
            'futturu-admin-style',
            FUTTURT_PLANS_PLUGIN_URL . 'assets/css/futturu-plans.css',
            array(),
            FUTTURT_PLANS_VERSION
        );
        
        wp_enqueue_script(
            'futturu-admin-script',
            FUTTURT_PLANS_PLUGIN_URL . 'assets/js/futturu-admin.js',
            array('jquery'),
            FUTTURT_PLANS_VERSION,
            true
        );
        
        wp_localize_script('futturu-admin-script', 'futturuAdminAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('futturu_admin_nonce'),
            'saveSuccess' => __('Configurações salvas com sucesso!', 'futturu-plan-tables'),
            'saveError' => __('Erro ao salvar configurações.', 'futturu-plan-tables')
        ));
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        $settings = Futturu_Plans_Settings::get_settings();
        $contact_email = get_option('futturu_plans_contact_email', get_option('admin_email'));
        
        ?>
        <div class="wrap futturu-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="futturu-admin-tabs">
                <nav class="futturu-admin-nav-tabs">
                    <a href="#tab-criacao" class="futturu-admin-tab active" data-tab="criacao">
                        🌐 Criação de Websites
                    </a>
                    <a href="#tab-hospedagem" class="futturu-admin-tab" data-tab="hospedagem">
                        ☁️ Hospedagem Cloud
                    </a>
                    <a href="#tab-manutencao" class="futturu-admin-tab" data-tab="manutencao">
                        🔧 Manutenção & Suporte
                    </a>
                    <a href="#tab-config" class="futturu-admin-tab" data-tab="config">
                        ⚙️ Configurações Gerais
                    </a>
                </nav>
                
                <form id="futturu-settings-form" method="post" action="options.php">
                    <?php settings_fields('futturu_plans_group'); ?>
                    
                    <!-- Criação Tab -->
                    <div class="futturu-admin-panel active" id="panel-criacao">
                        <?php $this->render_category_panel('criacao', $settings['criacao']); ?>
                    </div>
                    
                    <!-- Hospedagem Tab -->
                    <div class="futturu-admin-panel" id="panel-hospedagem">
                        <?php $this->render_category_panel('hospedagem', $settings['hospedagem']); ?>
                    </div>
                    
                    <!-- Manutenção Tab -->
                    <div class="futturu-admin-panel" id="panel-manutencao">
                        <?php $this->render_category_panel('manutencao', $settings['manutencao']); ?>
                    </div>
                    
                    <!-- Config Tab -->
                    <div class="futturu-admin-panel" id="panel-config">
                        <?php $this->render_config_panel($contact_email); ?>
                    </div>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large">
                            <?php _e('Salvar Configurações', 'futturu-plan-tables'); ?>
                        </button>
                        <span class="spinner"></span>
                    </p>
                </form>
            </div>
            
            <div class="futturu-shortcodes-info">
                <h2><?php _e('Shortcodes Disponíveis', 'futturu-plan-tables'); ?></h2>
                <p><?php _e('Use os shortcodes abaixo para exibir as tabelas em qualquer página ou post:', 'futturu-plan-tables'); ?></p>
                <ul>
                    <li><code>[futturu_planos_criacao]</code> - <?php _e('Exibe tabela de Criação de Websites', 'futturu-plan-tables'); ?></li>
                    <li><code>[futturu_planos_hospedagem]</code> - <?php _e('Exibe tabela de Hospedagem Cloud', 'futturu-plan-tables'); ?></li>
                    <li><code>[futturu_planos_manutencao]</code> - <?php _e('Exibe tabela de Manutenção & Suporte', 'futturu-plan-tables'); ?></li>
                    <li><code>[futturu_planos_all]</code> - <?php _e('Exibe todas as tabelas com navegação por abas', 'futturu-plan-tables'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render category panel
     */
    private function render_category_panel($category, $data) {
        ?>
        <h2><?php echo esc_html($this->get_category_title($category)); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="futturu_<?php echo $category; ?>_enabled"><?php _e('Ativar Categoria', 'futturu-plan-tables'); ?></label>
                </th>
                <td>
                    <input type="checkbox" 
                           id="futturu_<?php echo $category; ?>_enabled" 
                           name="futturu_plans_settings[<?php echo $category; ?>][enabled]" 
                           value="1" 
                           <?php checked(!empty($data['enabled']), true); ?> />
                    <label for="futturu_<?php echo $category; ?>_enabled"><?php _e('Exibir esta categoria nas tabelas', 'futturu-plan-tables'); ?></label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="futturu_<?php echo $category; ?>_title"><?php _e('Título', 'futturu-plan-tables'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="futturu_<?php echo $category; ?>_title" 
                           name="futturu_plans_settings[<?php echo $category; ?>][title]" 
                           value="<?php echo esc_attr($data['title']); ?>" 
                           class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="futturu_<?php echo $category; ?>_description"><?php _e('Descrição', 'futturu-plan-tables'); ?></label>
                </th>
                <td>
                    <textarea id="futturu_<?php echo $category; ?>_description" 
                              name="futturu_plans_settings[<?php echo $category; ?>][description]" 
                              rows="3" 
                              class="large-text"><?php echo esc_textarea($data['description']); ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="futturu_<?php echo $category; ?>_cta_text"><?php _e('Texto do CTA', 'futturu-plan-tables'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="futturu_<?php echo $category; ?>_cta_text" 
                           name="futturu_plans_settings[<?php echo $category; ?>][cta_text]" 
                           value="<?php echo esc_attr($data['cta_text']); ?>" 
                           class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="futturu_<?php echo $category; ?>_cta_link"><?php _e('Link do CTA', 'futturu-plan-tables'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="futturu_<?php echo $category; ?>_cta_link" 
                           name="futturu_plans_settings[<?php echo $category; ?>][cta_link]" 
                           value="<?php echo esc_attr($data['cta_link']); ?>" 
                           class="regular-text" 
                           placeholder="/contato/" />
                </td>
            </tr>
        </table>
        
        <h3><?php _e('Planos', 'futturu-plan-tables'); ?></h3>
        <div class="futturu-plans-editor">
            <?php 
            if (!empty($data['plans'])):
                foreach ($data['plans'] as $plan_index => $plan): 
            ?>
                <div class="futturu-plan-card <?php echo !empty($plan['highlight']) ? 'futturu-highlighted-plan' : ''; ?>">
                    <div class="futturu-plan-card-header">
                        <h4><?php echo esc_html($plan['name']); ?></h4>
                        <label>
                            <input type="checkbox" 
                                   name="futturu_plans_settings[<?php echo $category; ?>][plans][<?php echo $plan_index; ?>][highlight]" 
                                   value="1" 
                                   <?php checked(!empty($plan['highlight']), true); ?> />
                            <?php _e('Destacar como recomendado', 'futturu-plan-tables'); ?>
                        </label>
                    </div>
                    
                    <div class="futturu-plan-fields">
                        <p>
                            <label><?php _e('Nome do Plano:', 'futturu-plan-tables'); ?></label>
                            <input type="text" 
                                   name="futturu_plans_settings[<?php echo $category; ?>][plans][<?php echo $plan_index; ?>][name]" 
                                   value="<?php echo esc_attr($plan['name']); ?>" 
                                   class="regular-text" />
                        </p>
                        
                        <p>
                            <label><?php _e('Preço:', 'futturu-plan-tables'); ?></label>
                            <input type="text" 
                                   name="futturu_plans_settings[<?php echo $category; ?>][plans][<?php echo $plan_index; ?>][price]" 
                                   value="<?php echo esc_attr($plan['price']); ?>" 
                                   class="small-text" />
                        </p>
                        
                        <p>
                            <label><?php _e('Badge:', 'futturu-plan-tables'); ?></label>
                            <input type="text" 
                                   name="futturu_plans_settings[<?php echo $category; ?>][plans][<?php echo $plan_index; ?>][badge]" 
                                   value="<?php echo esc_attr($plan['badge']); ?>" 
                                   class="regular-text" 
                                   placeholder="Ex: Mais Contratado" />
                        </p>
                        
                        <p>
                            <label><?php _e('Tipo da Badge:', 'futturu-plan-tables'); ?></label>
                            <select name="futturu_plans_settings[<?php echo $category; ?>][plans][<?php echo $plan_index; ?>][badge_type]">
                                <option value="info" <?php selected($plan['badge_type'], 'info'); ?>><?php _e('Info (Azul)', 'futturu-plan-tables'); ?></option>
                                <option value="success" <?php selected($plan['badge_type'], 'success'); ?>><?php _e('Sucesso (Verde)', 'futturu-plan-tables'); ?></option>
                                <option value="warning" <?php selected($plan['badge_type'], 'warning'); ?>><?php _e('Aviso (Amarelo)', 'futturu-plan-tables'); ?></option>
                                <option value="danger" <?php selected($plan['badge_type'], 'danger'); ?>><?php _e('Perigo (Vermelho)', 'futturu-plan-tables'); ?></option>
                            </select>
                        </p>
                        
                        <p>
                            <label><?php _e('Proposta de Valor:', 'futturu-plan-tables'); ?></label>
                            <textarea name="futturu_plans_settings[<?php echo $category; ?>][plans][<?php echo $plan_index; ?>][value_proposition]" 
                                      rows="2" 
                                      class="large-text"><?php echo esc_textarea($plan['value_proposition']); ?></textarea>
                        </p>
                        
                        <h5><?php _e('Features Inclusas', 'futturu-plan-tables'); ?></h5>
                        <div class="futturu-features-list">
                            <?php if (!empty($plan['features'])): ?>
                                <ul>
                                    <?php foreach ($plan['features'] as $feat_key => $feature): ?>
                                        <li>
                                            <label>
                                                <input type="checkbox" 
                                                       name="futturu_plans_settings[<?php echo $category; ?>][plans][<?php echo $plan_index; ?>][features][<?php echo $feat_key; ?>][included]" 
                                                       value="1" 
                                                       <?php checked(!empty($feature['included']), true); ?> />
                                                <?php echo esc_html($feature['label']); ?>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach;
            endif; 
            ?>
        </div>
        <?php
    }
    
    /**
     * Render config panel
     */
    private function render_config_panel($contact_email) {
        ?>
        <h2><?php _e('Configurações Gerais', 'futturu-plan-tables'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="futturu_plans_contact_email"><?php _e('E-mail para Leads', 'futturu-plan-tables'); ?></label>
                </th>
                <td>
                    <input type="email" 
                           id="futturu_plans_contact_email" 
                           name="futturu_plans_contact_email" 
                           value="<?php echo esc_attr($contact_email); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php _e('E-mail que receberá as mensagens dos formulários de contato.', 'futturu-plan-tables'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h3><?php _e('Instruções de Uso', 'futturu-plan-tables'); ?></h3>
        <div class="futturu-instructions">
            <ol>
                <li><?php _e('Configure cada categoria de planos nas abas acima.', 'futturu-plan-tables'); ?></li>
                <li><?php _e('Marque um plano como "Destacar como recomendado" para dar ênfase visual.', 'futturu-plan-tables'); ?></li>
                <li><?php _e('Use os shortcodes disponíveis para exibir as tabelas em suas páginas.', 'futturu-plan-tables'); ?></li>
                <li><?php _e('O e-mail configurado receberá todas as mensagens dos CTAs.', 'futturu-plan-tables'); ?></li>
            </ol>
        </div>
        <?php
    }
    
    /**
     * Get category title
     */
    private function get_category_title($category) {
        $titles = array(
            'criacao' => 'Criação de Websites',
            'hospedagem' => 'Hospedagem Cloud',
            'manutencao' => 'Manutenção & Suporte'
        );
        return isset($titles[$category]) ? $titles[$category] : $category;
    }
    
    /**
     * AJAX save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('futturu_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permissão negada.'));
        }
        
        // Settings are saved automatically by WordPress via options.php
        wp_send_json_success(array('message' => __('Configurações salvas com sucesso!', 'futturu-plan-tables')));
    }
}
