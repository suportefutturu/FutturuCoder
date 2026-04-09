<?php
/**
 * Email Handler Class
 * Sends simulation results via email
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Email {

    /**
     * Send simulation email
     * @param int $simulation_id Simulation ID
     * @param array $data Simulation data
     * @param array $calculation Calculation results
     * @return bool Success status
     */
    public static function send_simulation_email($simulation_id, $data, $calculation) {
        $email_to = get_option('futturu_simulator_email_to', 'suporte@futturu.com.br');
        $email_enabled = get_option('futturu_simulator_email_enabled', 1);

        if (!$email_enabled) {
            return false;
        }

        $subject = sprintf(
            __('Nova Simulação de Site - %s', 'futturu-simulator'),
            sanitize_text_field($data['client_name'])
        );

        $message = self::build_email_body($simulation_id, $data, $calculation);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . sanitize_email($data['client_email']),
            'From: Simulador Futturu <noreply@' . self::get_domain() . '>'
        );

        $sent = wp_mail($email_to, $subject, $message, $headers);

        // Optionally send confirmation to client
        $send_client_copy = get_option('futturu_simulator_email_client_copy', 0);
        if ($send_client_copy) {
            self::send_client_confirmation($data, $calculation);
        }

        return $sent;
    }

    /**
     * Build HTML email body
     */
    private static function build_email_body($simulation_id, $data, $calculation) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 800px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #3498db; }
                .section h3 { margin-top: 0; color: #2c3e50; }
                .highlight { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .investment { font-size: 24px; color: #27ae60; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                td, th { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                .label { font-weight: bold; width: 40%; }
                .cta-button { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Nova Simulação Recebida</h1>
                    <p>ID: #<?php echo esc_html($simulation_id); ?></p>
                </div>

                <div class="section">
                    <h3>📋 Dados do Cliente</h3>
                    <table>
                        <tr>
                            <td class="label">Nome:</td>
                            <td><?php echo esc_html($data['client_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">E-mail:</td>
                            <td><?php echo esc_html($data['client_email']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">WhatsApp/Telefone:</td>
                            <td><?php echo esc_html($data['client_phone']); ?></td>
                        </tr>
                        <?php if (!empty($data['client_cnpj'])): ?>
                        <tr>
                            <td class="label">CNPJ:</td>
                            <td><?php echo esc_html($data['client_cnpj']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="label">Segmento:</td>
                            <td><?php echo esc_html($data['market_segment']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Canal Preferido:</td>
                            <td><?php echo esc_html(implode(', ', isset($data['contact_channel']) ? $data['contact_channel'] : array())); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="section">
                    <h3>🌐 Informações do Site</h3>
                    <table>
                        <tr>
                            <td class="label">Endereço Pretendido:</td>
                            <td><?php echo esc_html($data['site_address']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Tipo de Projeto:</td>
                            <td><?php echo esc_html(self::get_project_type_label($data['project_type'])); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Tipo de Site:</td>
                            <td><?php echo esc_html(Futturu_Calculator::get_site_type_label($data['site_type'])); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Complexidade:</td>
                            <td><?php echo esc_html(self::get_complexity_label($data['complexity_level'])); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Páginas:</td>
                            <td><?php echo esc_html(self::get_pages_label($data['num_pages'])); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Idiomas:</td>
                            <td><?php echo esc_html($data['languages']); ?></td>
                        </tr>
                    </table>
                </div>

                <?php if (!empty($calculation['breakdown'])): ?>
                <div class="highlight">
                    <h3>💰 Investimento Estimado</h3>
                    <p class="investment"><?php echo Futturu_Calculator::format_currency($calculation['estimated']); ?></p>
                    <p>Range: <?php echo Futturu_Calculator::format_currency($calculation['min']); ?> - <?php echo Futturu_Calculator::format_currency($calculation['max']); ?></p>
                    
                    <h4>Detalhamento:</h4>
                    <table>
                        <?php foreach ($calculation['breakdown'] as $item): ?>
                        <tr>
                            <td><?php echo esc_html($item['label']); ?></td>
                            <td><?php echo Futturu_Calculator::format_currency($item['value']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <div class="section">
                    <h3>⏱️ Prazo de Entrega</h3>
                    <p><strong><?php echo esc_html(self::get_delivery_label($data['delivery_time'])); ?></strong></p>
                    <?php if (!empty($data['specific_date'])): ?>
                    <p>Data solicitada: <?php echo esc_html(date('d/m/Y', strtotime($data['specific_date']))); ?></p>
                    <?php endif; ?>
                </div>

                <div class="section">
                    <h3>🎯 Recursos Selecionados</h3>
                    <p><strong>Menu/Páginas:</strong> <?php echo esc_html(implode(', ', isset($data['menu_pages']) ? $data['menu_pages'] : array())); ?></p>
                    <p><strong>Recursos Adicionais:</strong> <?php echo esc_html(implode(', ', isset($data['addons']) ? $data['addons'] : array())); ?></p>
                    <p><strong>Ferramentas Google:</strong> <?php echo esc_html(implode(', ', isset($data['google_marketing']) ? $data['google_marketing'] : array())); ?></p>
                    <p><strong>SEO:</strong> 
                        <?php 
                        $seo = array();
                        if (!empty($data['seo_basic'])) $seo[] = 'Básico';
                        if (!empty($data['seo_advanced'])) $seo[] = 'Avançado';
                        echo esc_html(implode(', ', $seo));
                        ?>
                    </p>
                </div>

                <div class="section">
                    <h3>🖥️ Hospedagem e Manutenção</h3>
                    <p><strong>Domínio:</strong> <?php echo esc_html(self::get_domain_status_label($data['domain_status'])); ?></p>
                    <p><strong>Hospedagem Atual:</strong> <?php echo esc_html(self::get_hosting_label($data['hosting_current'])); ?></p>
                    <p><strong>Interesse Hospedagem Premium:</strong> <?php echo esc_html(self::get_hosting_premium_label($data['hosting_premium_interest'])); ?></p>
                    <p><strong>Manutenção:</strong> <?php echo esc_html(self::get_maintenance_package_label($data['maintenance_package'])); ?></p>
                </div>

                <?php if (!empty($data['additional_info'])): ?>
                <div class="section">
                    <h3>📝 Informações Adicionais</h3>
                    <p><?php echo nl2br(esc_html($data['additional_info'])); ?></p>
                </div>
                <?php endif; ?>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="<?php echo admin_url('admin.php?page=futturu-simulator-leads&view=' . $simulation_id); ?>" class="cta-button">
                        Ver Detalhes no Admin
                    </a>
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee; font-size: 12px; color: #666;">
                    <p>Esta simulação foi gerada automaticamente pelo Simulador Futturu.</p>
                    <p>Base de cálculo: Tabela Sinapro + Experiência Futturu + Planos Cloudez</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Send confirmation email to client
     */
    private static function send_client_confirmation($data, $calculation) {
        $subject = __('Sua Simulação Futturu - Recebemos seu projeto!', 'futturu-simulator');
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .investment { font-size: 20px; color: #27ae60; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Obrigado pela sua simulação!</h1>
                </div>
                
                <p>Olá <?php echo esc_html($data['client_name']); ?>,</p>
                
                <p>Recebemos as informações do seu projeto e nossa equipe já está analisando!</p>
                
                <div style="background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Investimento Estimado:</strong></p>
                    <p class="investment"><?php echo Futturu_Calculator::format_currency($calculation['estimated']); ?></p>
                    <p>(Range: <?php echo Futturu_Calculator::format_currency($calculation['min']); ?> - <?php echo Futturu_Calculator::format_currency($calculation['max']); ?>)</p>
                </div>
                
                <p>Em breve entraremos em contato através do seu canal preferido (<strong><?php echo esc_html(implode(', ', isset($data['contact_channel']) ? $data['contact_channel'] : array())); ?></strong>) para discutir os detalhes do seu projeto.</p>
                
                <p>Atenciosamente,<br>Equipe Futturu</p>
            </div>
        </body>
        </html>
        <?php
        $message = ob_get_clean();
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Futturu <contato@futturu.com.br>'
        );
        
        wp_mail($data['client_email'], $subject, $message, $headers);
    }

    /**
     * Helper functions for labels
     */
    private static function get_project_type_label($type) {
        $labels = array(
            'novo' => 'Site Novo (Versão 1.0)',
            'redesenho' => 'Redesenho (Novo Design)'
        );
        return isset($labels[$type]) ? $labels[$type] : $type;
    }

    private static function get_complexity_label($level) {
        $labels = array(
            'baixa' => 'Baixa - Site simples, poucas páginas',
            'media' => 'Média - Site com mais páginas e funcionalidades',
            'alta' => 'Alta - Site altamente personalizado e complexo'
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

    private static function get_delivery_label($time) {
        $labels = array(
            '30-45' => '30-45 dias úteis',
            '45-60' => '45-60 dias úteis',
            '60-90' => '60-90 dias úteis',
            'flexivel' => 'A combinar'
        );
        return isset($labels[$time]) ? $labels[$time] : $time;
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
            'nao_tenho' => 'Não tenho hospedagem',
            'compartilhada' => 'Hospedagem Compartilhada',
            'cloud_preciso_avaliar' => 'Cloud (Preciso avaliar)',
            'quero_migrar_cloud' => 'Quero migrar para Cloud'
        );
        return isset($labels[$hosting]) ? $labels[$hosting] : $hosting;
    }

    private static function get_hosting_premium_label($interest) {
        $labels = array(
            'sim_quero_conhecer' => 'Sim, quero conhecer',
            'envie_apresentacao' => 'Agora não, mas envie a apresentação'
        );
        return isset($labels[$interest]) ? $labels[$interest] : '';
    }

    private static function get_maintenance_package_label($package) {
        $labels = array(
            'sim_quero_proposta' => 'Sim, quero uma proposta',
            'nao_farei_mesmo' => 'Não, farei eu mesmo'
        );
        return isset($labels[$package]) ? $labels[$package] : $package;
    }

    private static function get_domain() {
        $url = get_site_url();
        $parsed = parse_url($url);
        return isset($parsed['host']) ? $parsed['host'] : 'futturu.com.br';
    }
}
