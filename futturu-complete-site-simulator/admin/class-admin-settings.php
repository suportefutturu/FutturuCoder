<?php
/**
 * Admin Settings Class
 * Manages plugin configuration page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Admin_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add settings page to menu
     */
    public function add_settings_page() {
        add_options_page(
            __('Simulador Sites Futturu', 'futturu-simulator'),
            __('Simulador Futturu', 'futturu-simulator'),
            'manage_options',
            'futturu-simulator-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // General settings
        register_setting('futturu_simulator_settings', 'futturu_simulator_enabled', array('sanitize_callback' => 'absint'));
        register_setting('futturu_simulator_settings', 'futturu_simulator_email_enabled', array('sanitize_callback' => 'absint'));
        register_setting('futturu_simulator_settings', 'futturu_simulator_email_to', array('sanitize_callback' => 'sanitize_email'));
        register_setting('futturu_simulator_settings', 'futturu_simulator_email_client_copy', array('sanitize_callback' => 'absint'));

        // Base values
        register_setting('futturu_simulator_settings', 'futturu_simulator_base_values', array('sanitize_callback' => array($this, 'sanitize_values_array')));
        register_setting('futturu_simulator_settings', 'futturu_simulator_complexity_multipliers', array('sanitize_callback' => array($this, 'sanitize_values_array')));
        register_setting('futturu_simulator_settings', 'futturu_simulator_addon_values', array('sanitize_callback' => array($this, 'sanitize_values_array')));
        register_setting('futturu_simulator_settings', 'futturu_simulator_hosting_values', array('sanitize_callback' => array($this, 'sanitize_values_array')));
        register_setting('futturu_simulator_settings', 'futturu_simulator_maintenance_values', array('sanitize_callback' => array($this, 'sanitize_values_array')));
    }

    /**
     * Sanitize array of values
     */
    public function sanitize_values_array($input) {
        if (!is_array($input)) {
            return array();
        }
        $sanitized = array();
        foreach ($input as $key => $value) {
            $sanitized[sanitize_key($key)] = floatval($value);
        }
        return $sanitized;
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('settings_page_futturu-simulator-settings' !== $hook) {
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
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle reset to defaults
        if (isset($_POST['futturu_reset_defaults']) && check_admin_referer('futturu_reset_defaults_nonce')) {
            delete_option('futturu_simulator_base_values');
            delete_option('futturu_simulator_complexity_multipliers');
            delete_option('futturu_simulator_addon_values');
            delete_option('futturu_simulator_hosting_values');
            delete_option('futturu_simulator_maintenance_values');
            echo '<div class="notice notice-success"><p>' . __('Configurações resetadas para os valores padrão.', 'futturu-simulator') . '</p></div>';
        }
        ?>
        <div class="wrap futturu-settings-page">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="futturu-settings-intro">
                <p><?php _e('Configure os valores base, multiplicadores e opções do Simulador de Sites Futturu. Os valores são baseados na tabela Sinapro e experiência Futturu.', 'futturu-simulator'); ?></p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('futturu_simulator_settings'); ?>
                <?php do_settings_sections('futturu_simulator_settings'); ?>
                
                <h2><?php _e('Configurações Gerais', 'futturu-simulator'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Plugin Ativo', 'futturu-simulator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="futturu_simulator_enabled" value="1" <?php checked(get_option('futturu_simulator_enabled', 1), 1); ?> />
                                <?php _e('Ativar simulador', 'futturu-simulator'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Enviar E-mail', 'futturu-simulator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="futturu_simulator_email_enabled" value="1" <?php checked(get_option('futturu_simulator_email_enabled', 1), 1); ?> />
                                <?php _e('Enviar e-mail para a equipe ao receber simulação', 'futturu-simulator'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('E-mail de Destino', 'futturu-simulator'); ?></th>
                        <td>
                            <input type="email" name="futturu_simulator_email_to" value="<?php echo esc_attr(get_option('futturu_simulator_email_to', 'suporte@futturu.com.br')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cópia para Cliente', 'futturu-simulator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="futturu_simulator_email_client_copy" value="1" <?php checked(get_option('futturu_simulator_email_client_copy', 0), 1); ?> />
                                <?php _e('Enviar confirmação por e-mail para o cliente', 'futturu-simulator'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h2><?php _e('Valores Base por Tipo de Site (R$)', 'futturu-simulator'); ?></h2>
                <p class="description"><?php _e('Valores iniciais baseados no tipo de site (base Sinapro).', 'futturu-simulator'); ?></p>
                <table class="form-table futturu-values-table">
                    <?php 
                    $base_values = get_option('futturu_simulator_base_values', Futturu_Database::get_default_base_values());
                    $site_types = array(
                        'blog' => 'Blog',
                        'news' => 'Notícias',
                        'portfolio' => 'Portfólio',
                        'hotsite' => 'Hotsite',
                        'institutional' => 'Institucional',
                        'ecommerce' => 'E-commerce',
                        'other' => 'Outro'
                    );
                    foreach ($site_types as $key => $label): 
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($label); ?></th>
                        <td>
                            <input type="number" name="futturu_simulator_base_values[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($base_values[$key]); ?>" step="100" min="0" class="small-text" />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <h2><?php _e('Multiplicadores de Complexidade', 'futturu-simulator'); ?></h2>
                <p class="description"><?php _e('Multiplicadores aplicados ao valor base conforme a complexidade.', 'futturu-simulator'); ?></p>
                <table class="form-table futturu-values-table">
                    <?php 
                    $complexity_multipliers = get_option('futturu_simulator_complexity_multipliers', array('low' => 1.0, 'medium' => 1.4, 'high' => 1.9));
                    $complexity_labels = array(
                        'low' => 'Baixa (Site simples)',
                        'medium' => 'Média (Mais páginas/funcionalidades)',
                        'high' => 'Alta (Altamente personalizado)'
                    );
                    foreach ($complexity_labels as $key => $label): 
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($label); ?></th>
                        <td>
                            <input type="number" name="futturu_simulator_complexity_multipliers[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($complexity_multipliers[$key]); ?>" step="0.1" min="0.5" max="3.0" class="small-text" />
                            <span class="description">(ex: 1.4 = +40%)</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <h2><?php _e('Valores de Recursos Adicionais (R$)', 'futturu-simulator'); ?></h2>
                <p class="description"><?php _e('Valores para cada recurso adicional selecionado.', 'futturu-simulator'); ?></p>
                <table class="form-table futturu-values-table">
                    <?php 
                    $addon_values = get_option('futturu_simulator_addon_values', Futturu_Database::get_default_addon_values());
                    $addon_labels = array(
                        'faq_page' => 'Página FAQ',
                        'event_calendar' => 'Calendário de Eventos',
                        'registration_form' => 'Formulário de Inscrição',
                        'login_area' => 'Área de Login',
                        'product_search' => 'Busca de Produtos',
                        'ecommerce' => 'E-commerce (adicional)',
                        'sitemap' => 'Mapa do Site',
                        'custom_menu' => 'Menu Personalizado',
                        'newsletter' => 'Newsletter',
                        'reviews' => 'Avaliações',
                        'quizzes' => 'Questionários',
                        'tutorial_videos' => 'Vídeos Tutoriais',
                        'ads' => 'Anúncios',
                        'budget_calculator' => 'Calculadora de Orçamento',
                        'career_pages' => 'Páginas de Carreira',
                        'corporate_videos' => 'Vídeos Corporativos',
                        'phone_support' => 'Atendimento Telefônico',
                        'booking_system' => 'Sistema de Reservas',
                        'vfaq' => 'VFAQ',
                        'translations' => 'Traduções',
                        'comparison_tool' => 'Ferramenta de Comparação'
                    );
                    foreach ($addon_labels as $key => $label): 
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($label); ?></th>
                        <td>
                            <input type="number" name="futturu_simulator_addon_values[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($addon_values[$key]); ?>" step="50" min="0" class="small-text" />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <h2><?php _e('Valores de Hospedagem (R$/ano)', 'futturu-simulator'); ?></h2>
                <p class="description"><?php _e('Valores anuais para hospedagem e domínio.', 'futturu-simulator'); ?></p>
                <table class="form-table futturu-values-table">
                    <?php 
                    $hosting_values = get_option('futturu_simulator_hosting_values', Futturu_Database::get_default_hosting_values());
                    $hosting_labels = array(
                        'shared_annual' => 'Hospedagem Compartilhada (anual)',
                        'cloud_basic_annual' => 'Cloud Básico (anual)',
                        'cloud_pro_annual' => 'Cloud Pro (anual)',
                        'cloud_premium_annual' => 'Cloud Premium (anual)',
                        'domain_registration' => 'Registro de Domínio (.com.br)'
                    );
                    foreach ($hosting_labels as $key => $label): 
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($label); ?></th>
                        <td>
                            <input type="number" name="futturu_simulator_hosting_values[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($hosting_values[$key]); ?>" step="50" min="0" class="small-text" />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <h2><?php _e('Valores de Manutenção (R$/mês)', 'futturu-simulator'); ?></h2>
                <p class="description"><?php _e('Valores mensais para pacotes de manutenção.', 'futturu-simulator'); ?></p>
                <table class="form-table futturu-values-table">
                    <?php 
                    $maintenance_values = get_option('futturu_simulator_maintenance_values', Futturu_Database::get_default_maintenance_values());
                    $maintenance_labels = array(
                        'monthly_basic' => 'Manutenção Básica (mensal)',
                        'monthly_standard' => 'Manutenção Standard (mensal)',
                        'monthly_premium' => 'Manutenção Premium (mensal)'
                    );
                    foreach ($maintenance_labels as $key => $label): 
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($label); ?></th>
                        <td>
                            <input type="number" name="futturu_simulator_maintenance_values[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($maintenance_values[$key]); ?>" step="50" min="0" class="small-text" />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <?php submit_button(__('Salvar Configurações', 'futturu-simulator')); ?>
            </form>

            <hr />

            <h2><?php _e('Resetar Configurações', 'futturu-simulator'); ?></h2>
            <p class="description"><?php _e('Isso resetará todos os valores para os padrões do plugin.', 'futturu-simulator'); ?></p>
            <form method="post" action="" onsubmit="return confirm('<?php esc_attr_e('Tem certeza? Esta ação não pode ser desfeita.', 'futturu-simulator'); ?>');">
                <?php wp_nonce_field('futturu_reset_defaults_nonce'); ?>
                <input type="submit" name="futturu_reset_defaults" class="button button-secondary" value="<?php esc_attr_e('Resetar para Padrões', 'futturu-simulator'); ?>" />
            </form>

            <hr />

            <h2><?php _e('Como Usar', 'futturu-simulator'); ?></h2>
            <div class="futturu-help-section">
                <h3><?php _e('Shortcode', 'futturu-simulator'); ?></h3>
                <p><?php _e('Use o shortcode abaixo em qualquer página ou post:', 'futturu-simulator'); ?></p>
                <code>[futturu_site_simulator]</code>

                <h3><?php _e('Visualizar Leads', 'futturu-simulator'); ?></h3>
                <p><?php _e('Acesse', 'futturu-simulator'); ?> <a href="<?php echo admin_url('admin.php?page=futturu-simulator-leads'); ?>"><?php _e('Futturu > Simulações Recebidas', 'futturu-simulator'); ?></a> <?php _e('para ver todas as simulações enviadas.', 'futturu-simulator'); ?></p>
            </div>
        </div>
        <?php
    }
}
