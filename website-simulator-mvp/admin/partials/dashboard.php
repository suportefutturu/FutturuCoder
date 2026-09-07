<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>Website Simulator - Dashboard</h1>
    
    <div class="wsmvp-stats">
        <div class="wsmvp-stat-box">
            <h3><?php echo $stats['total_simulations']; ?></h3>
            <p>Simulações</p>
        </div>
    </div>
    
    <div class="wsmvp-recent">
        <h2>Últimas Simulações</h2>
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Valor</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['recent_simulations'] as $s): ?>
                <tr>
                    <td><?php echo $s->id; ?></td>
                    <td><?php echo esc_html($s->name); ?></td>
                    <td><?php echo esc_html($s->email); ?></td>
                    <td><?php echo WSMVP_Settings::get_instance()->format_currency($s->estimated_value); ?></td>
                    <td><?php echo date_i18n('d/m/Y', strtotime($s->created_at)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
