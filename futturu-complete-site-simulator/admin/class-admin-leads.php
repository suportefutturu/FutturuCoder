<?php
/**
 * Admin Leads Class
 * Manages leads/simulations list and details page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Admin_Leads {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_leads_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add leads page to menu
     */
    public function add_leads_page() {
        add_menu_page(
            __('Simulações Recebidas', 'futturu-simulator'),
            __('Futturu Simulações', 'futturu-simulator'),
            'manage_options',
            'futturu-simulator-leads',
            array($this, 'render_leads_page'),
            'dashicons-analytics',
            30
        );
        
        add_submenu_page(
            'futturu-simulator-leads',
            __('Todas as Simulações', 'futturu-simulator'),
            __('Todas as Simulações', 'futturu-simulator'),
            'manage_options',
            'futturu-simulator-leads',
            array($this, 'render_leads_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('futturu-simulator_page_futturu-simulator-leads' !== $hook) {
            return;
        }
        wp_enqueue_style(
            'futturu-admin-css',
            FUTTURU_SIMULATOR_PLUGIN_URL . 'assets/css/futturu-admin.css',
            array(),
            FUTTURU_SIMULATOR_VERSION
        );
    }

    /**
     * Render leads page
     */
    public function render_leads_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $view = isset($_GET['view']) ? intval($_GET['view']) : null;
        
        if ($view) {
            $this->render_lead_details($view);
        } else {
            $this->render_leads_list();
        }
    }

    /**
     * Render leads list
     */
    private function render_leads_list() {
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;

        // Filters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

        $args = array(
            'limit' => $per_page,
            'offset' => $offset,
            'orderby' => 'submission_date',
            'order' => 'DESC'
        );

        if ($status_filter) {
            $args['status'] = $status_filter;
        }

        $leads = Futturu_Database::get_simulations($args);
        $total = Futturu_Database::get_simulation_count();

        ?>
        <div class="wrap futturu-leads-page">
            <h1><?php _e('Simulações Recebidas', 'futturu-simulator'); ?></h1>
            
            <div class="futturu-stats">
                <span class="stat-item">
                    <strong><?php echo esc_html($total); ?></strong> <?php _e('Total de Simulações', 'futturu-simulator'); ?>
                </span>
            </div>

            <!-- Filters -->
            <form method="get" action="" class="futturu-filters">
                <input type="hidden" name="page" value="futturu-simulator-leads" />
                
                <label>
                    <?php _e('Status:', 'futturu-simulator'); ?>
                    <select name="status">
                        <option value=""><?php _e('Todos', 'futturu-simulator'); ?></option>
                        <option value="new" <?php selected($status_filter, 'new'); ?>><?php _e('Novos', 'futturu-simulator'); ?></option>
                        <option value="contacted" <?php selected($status_filter, 'contacted'); ?>><?php _e('Contatados', 'futturu-simulator'); ?></option>
                        <option value="qualified" <?php selected($status_filter, 'qualified'); ?>><?php _e('Qualificados', 'futturu-simulator'); ?></option>
                        <option value="closed" <?php selected($status_filter, 'closed'); ?>><?php _e('Fechados', 'futturu-simulator'); ?></option>
                    </select>
                </label>

                <label>
                    <?php _e('De:', 'futturu-simulator'); ?>
                    <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" />
                </label>

                <label>
                    <?php _e('Até:', 'futturu-simulator'); ?>
                    <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" />
                </label>

                <button type="submit" class="button"><?php _e('Filtrar', 'futturu-simulator'); ?></button>
                <a href="<?php echo admin_url('admin.php?page=futturu-simulator-leads'); ?>" class="button"><?php _e('Limpar', 'futturu-simulator'); ?></a>
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'futturu-simulator'); ?></th>
                        <th><?php _e('Cliente', 'futturu-simulator'); ?></th>
                        <th><?php _e('E-mail', 'futturu-simulator'); ?></th>
                        <th><?php _e('Telefone', 'futturu-simulator'); ?></th>
                        <th><?php _e('Tipo de Site', 'futturu-simulator'); ?></th>
                        <th><?php _e('Investimento Est.', 'futturu-simulator'); ?></th>
                        <th><?php _e('Status', 'futturu-simulator'); ?></th>
                        <th><?php _e('Data', 'futturu-simulator'); ?></th>
                        <th><?php _e('Ações', 'futturu-simulator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="9"><?php _e('Nenhuma simulação encontrada.', 'futturu-simulator'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>#<?php echo esc_html($lead->id); ?></td>
                                <td><?php echo esc_html($lead->client_name); ?></td>
                                <td><a href="mailto:<?php echo esc_attr($lead->client_email); ?>"><?php echo esc_html($lead->client_email); ?></a></td>
                                <td><?php echo esc_html($lead->client_phone); ?></td>
                                <td><?php echo esc_html(Futturu_Calculator::get_site_type_label($lead->site_type)); ?></td>
                                <td><?php echo Futturu_Calculator::format_currency($lead->investment_estimated); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($lead->status); ?>">
                                        <?php echo esc_html(self::get_status_label($lead->status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date('d/m/Y H:i', strtotime($lead->submission_date))); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=futturu-simulator-leads&view=' . $lead->id); ?>" class="button button-small">
                                        <?php _e('Ver', 'futturu-simulator'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php
            $total_pages = ceil($total / $per_page);
            if ($total_pages > 1) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $paged
                ));
                echo '</div></div>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render lead details
     */
    private function render_lead_details($lead_id) {
        $lead = Futturu_Database::get_simulation($lead_id);

        if (!$lead) {
            echo '<div class="notice notice-error"><p>' . __('Simulação não encontrada.', 'futturu-simulator') . '</p></div>';
            return;
        }

        // Handle status update
        if (isset($_POST['update_status']) && check_admin_referer('futturu_update_lead_status')) {
            $new_status = sanitize_text_field($_POST['status']);
            Futturu_Database::update_simulation_status($lead_id, $new_status);
            $lead = Futturu_Database::get_simulation($lead_id); // Refresh
            echo '<div class="notice notice-success"><p>' . __('Status atualizado com sucesso!', 'futturu-simulator') . '</p></div>';
        }

        ?>
        <div class="wrap futturu-lead-details">
            <h1>
                <?php _e('Detalhes da Simulação', 'futturu-simulator'); ?> #<?php echo esc_html($lead->id); ?>
                <a href="<?php echo admin_url('admin.php?page=futturu-simulator-leads'); ?>" class="page-title-action">
                    <?php _e('Voltar à Lista', 'futturu-simulator'); ?>
                </a>
            </h1>

            <div class="futturu-detail-grid">
                <!-- Client Info -->
                <div class="detail-section">
                    <h2><?php _e('📋 Dados do Cliente', 'futturu-simulator'); ?></h2>
                    <table class="widefat">
                        <tr>
                            <th><?php _e('Nome', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html($lead->client_name); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('E-mail', 'futturu-simulator'); ?></th>
                            <td><a href="mailto:<?php echo esc_attr($lead->client_email); ?>"><?php echo esc_html($lead->client_email); ?></a></td>
                        </tr>
                        <tr>
                            <th><?php _e('WhatsApp/Telefone', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html($lead->client_phone); ?></td>
                        </tr>
                        <?php if (!empty($lead->client_cnpj)): ?>
                        <tr>
                            <th><?php _e('CNPJ', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html($lead->client_cnpj); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th><?php _e('Segmento', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html($lead->market_segment); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Canal Preferido', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html(implode(', ', explode(',', $lead->contact_channel))); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Como Soube', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html($lead->how_found_us); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Project Info -->
                <div class="detail-section">
                    <h2><?php _e('🌐 Informações do Projeto', 'futturu-simulator'); ?></h2>
                    <table class="widefat">
                        <tr>
                            <th><?php _e('Tipo de Projeto', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html(self::get_project_type_label($lead->project_type)); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Tipo de Site', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html(Futturu_Calculator::get_site_type_label($lead->site_type)); ?>
                                <?php if (!empty($lead->site_type_other)): ?>
                                    (<?php echo esc_html($lead->site_type_other); ?>)
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Complexidade', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html(self::get_complexity_label($lead->complexity_level)); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Páginas', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html(self::get_pages_label($lead->num_pages)); ?>
                                <?php if (!empty($lead->num_pages_custom)): ?>
                                    (<?php echo esc_html($lead->num_pages_custom); ?>)
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Idiomas', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html($lead->languages); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Textos', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html(self::get_yes_no_label($lead->texts_provided === 'fornecerei')); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Imagens', 'futturu-simulator'); ?></th>
                            <td><?php echo esc_html(self::get_yes_no_label($lead->images_provided === 'fornecerei')); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Investment -->
                <div class="detail-section highlight">
                    <h2><?php _e('💰 Investimento Estimado', 'futturu-simulator'); ?></h2>
                    <div class="investment-display">
                        <span class="investment-value"><?php echo Futturu_Calculator::format_currency($lead->investment_estimated); ?></span>
                        <span class="investment-range">
                            <?php echo Futturu_Calculator::format_currency($lead->investment_min); ?> - 
                            <?php echo Futturu_Calculator::format_currency($lead->investment_max); ?>
                        </span>
                    </div>
                    <p><strong><?php _e('Prazo de Entrega:', 'futturu-simulator'); ?></strong> <?php echo esc_html($lead->estimated_delivery); ?></p>
                    
                    <?php if (!empty($lead->specific_date)): ?>
                        <p><strong><?php _e('Data Solicitada:', 'futturu-simulator'); ?></strong> <?php echo esc_html(date('d/m/Y', strtotime($lead->specific_date))); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Features -->
                <div class="detail-section">
                    <h2><?php _e('🎯 Recursos Selecionados', 'futturu-simulator'); ?></h2>
                    <p><strong><?php _e('Menu/Páginas:', 'futturu-simulator'); ?></strong> <?php echo esc_html(implode(', ', explode(',', $lead->menu_pages))); ?></p>
                    <p><strong><?php _e('Recursos Adicionais:', 'futturu-simulator'); ?></strong> <?php echo esc_html(implode(', ', explode(',', $lead->addons))); ?>
                        <?php if (!empty($lead->addons_other)): ?>
                            (<?php echo esc_html($lead->addons_other); ?>)
                        <?php endif; ?>
                    </p>
                    <p><strong><?php _e('Google Marketing:', 'futturu-simulator'); ?></strong> <?php echo esc_html(implode(', ', explode(',', $lead->google_marketing))); ?></p>
                    <p><strong><?php _e('SEO:', 'futturu-simulator'); ?></strong> 
                        <?php 
                        $seo = array();
                        if ($lead->seo_basic) $seo[] = 'Básico';
                        if ($lead->seo_advanced) $seo[] = 'Avançado';
                        echo esc_html(implode(', ', $seo) ?: 'Nenhum');
                        ?>
                    </p>
                </div>

                <!-- Hosting & Maintenance -->
                <div class="detail-section">
                    <h2><?php _e('🖥️ Hospedagem e Manutenção', 'futturu-simulator'); ?></h2>
                    <p><strong><?php _e('Domínio:', 'futturu-simulator'); ?></strong> <?php echo esc_html(self::get_domain_status_label($lead->domain_status)); ?></p>
                    <p><strong><?php _e('Hospedagem Atual:', 'futturu-simulator'); ?></strong> <?php echo esc_html(self::get_hosting_label($lead->hosting_current)); ?></p>
                    <p><strong><?php _e('Interesse Premium:', 'futturu-simulator'); ?></strong> <?php echo esc_html(self::get_hosting_premium_label($lead->hosting_premium_interest) ?: 'Não especificado'); ?></p>
                    <p><strong><?php _e('Manutenção:', 'futturu-simulator'); ?></strong> <?php echo esc_html(self::get_maintenance_package_label($lead->maintenance_package)); ?></p>
                </div>

                <!-- Site Info -->
                <div class="detail-section">
                    <h2><?php _e('🔗 Informações do Site', 'futturu-simulator'); ?></h2>
                    <p><strong><?php _e('Endereço Pretendido:', 'futturu-simulator'); ?></strong> <?php echo esc_html($lead->site_address); ?></p>
                    <?php if (!empty($lead->design_reference)): ?>
                        <p><strong><?php _e('Referência de Design:', 'futturu-simulator'); ?></strong> <a href="<?php echo esc_url($lead->design_reference); ?>" target="_blank"><?php echo esc_html($lead->design_reference); ?></a></p>
                    <?php endif; ?>
                </div>

                <!-- Additional Info -->
                <?php if (!empty($lead->additional_info)): ?>
                <div class="detail-section">
                    <h2><?php _e('📝 Informações Adicionais', 'futturu-simulator'); ?></h2>
                    <p><?php echo nl2br(esc_html($lead->additional_info)); ?></p>
                </div>
                <?php endif; ?>

                <!-- Status Update -->
                <div class="detail-section">
                    <h2><?php _e('⚙️ Status', 'futturu-simulator'); ?></h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('futturu_update_lead_status'); ?>
                        <select name="status">
                            <option value="new" <?php selected($lead->status, 'new'); ?>><?php _e('Novo', 'futturu-simulator'); ?></option>
                            <option value="contacted" <?php selected($lead->status, 'contacted'); ?>><?php _e('Contatado', 'futturu-simulator'); ?></option>
                            <option value="qualified" <?php selected($lead->status, 'qualified'); ?>><?php _e('Qualificado', 'futturu-simulator'); ?></option>
                            <option value="closed" <?php selected($lead->status, 'closed'); ?>><?php _e('Fechado', 'futturu-simulator'); ?></option>
                        </select>
                        <input type="submit" name="update_status" class="button button-primary" value="<?php esc_attr_e('Atualizar Status', 'futturu-simulator'); ?>" />
                    </form>
                </div>

                <!-- Meta Info -->
                <div class="detail-section">
                    <h2><?php _e('ℹ️ Metadados', 'futturu-simulator'); ?></h2>
                    <p><strong><?php _e('Data de Envio:', 'futturu-simulator'); ?></strong> <?php echo esc_html(date('d/m/Y H:i:s', strtotime($lead->submission_date))); ?></p>
                    <p><strong><?php _e('IP:', 'futturu-simulator'); ?></strong> <?php echo esc_html($lead->ip_address); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Helper functions for labels
     */
    private static function get_status_label($status) {
        $labels = array(
            'new' => 'Novo',
            'contacted' => 'Contatado',
            'qualified' => 'Qualificado',
            'closed' => 'Fechado'
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    private static function get_project_type_label($type) {
        $labels = array(
            'novo' => 'Site Novo',
            'redesenho' => 'Redesenho'
        );
        return isset($labels[$type]) ? $labels[$type] : $type;
    }

    private static function get_complexity_label($level) {
        $labels = array(
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta'
        );
        return isset($labels[$level]) ? $labels[$level] : $level;
    }

    private static function get_pages_label($pages) {
        $labels = array(
            'ate_6' => 'Até 6 seções',
            'ate_10' => 'Até 10 seções',
            'ate_20' => 'Até 20 seções',
            'ate_30' => 'Até 30 seções',
            'sob_medida' => 'Sob Medida'
        );
        return isset($labels[$pages]) ? $labels[$pages] : $pages;
    }

    private static function get_yes_no_label($is_yes) {
        return $is_yes ? 'Sim' : 'Não';
    }

    private static function get_domain_status_label($status) {
        $labels = array(
            'ja_registrado' => 'Já registrado',
            'preciso_registrar' => 'Preciso registrar'
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    private static function get_hosting_label($hosting) {
        $labels = array(
            'nao_tenho' => 'Não tenho',
            'compartilhada' => 'Compartilhada',
            'cloud_preciso_avaliar' => 'Cloud (avaliar)',
            'quero_migrar_cloud' => 'Quero migrar para Cloud'
        );
        return isset($labels[$hosting]) ? $labels[$hosting] : $hosting;
    }

    private static function get_hosting_premium_label($interest) {
        $labels = array(
            'sim_quero_conhecer' => 'Sim, quero conhecer',
            'envie_apresentacao' => 'Enviar apresentação'
        );
        return isset($labels[$interest]) ? $labels[$interest] : '';
    }

    private static function get_maintenance_package_label($package) {
        $labels = array(
            'sim_quero_proposta' => 'Sim, quero proposta',
            'nao_farei_mesmo' => 'Não, farei eu mesmo'
        );
        return isset($labels[$package]) ? $labels[$package] : $package;
    }
}
