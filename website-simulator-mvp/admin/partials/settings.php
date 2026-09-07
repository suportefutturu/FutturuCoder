<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>Configurações</h1>
    <form method="post" action="options.php">
        <?php settings_fields('wsmvp_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th><label for="wsmvp_company_name">Nome da Empresa</label></th>
                <td><input type="text" name="wsmvp_company_name" id="wsmvp_company_name" value="<?php echo esc_attr($settings['company_name']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="wsmvp_currency">Moeda</label></th>
                <td><input type="text" name="wsmvp_currency" id="wsmvp_currency" value="<?php echo esc_attr($settings['currency']); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="wsmvp_admin_email">E-mail Admin</label></th>
                <td><input type="email" name="wsmvp_admin_email" id="wsmvp_admin_email" value="<?php echo esc_attr($settings['admin_email']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="wsmvp_whatsapp_number">WhatsApp</label></th>
                <td><input type="text" name="wsmvp_whatsapp_number" id="wsmvp_whatsapp_number" value="<?php echo esc_attr($settings['whatsapp_number']); ?>" class="regular-text"></td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
