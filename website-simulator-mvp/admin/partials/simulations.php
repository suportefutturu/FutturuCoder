<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>Simulações</h1>
    <table class="wp-list-table widefat">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['simulations'] as $s): ?>
            <tr>
                <td><?php echo $s->id; ?></td>
                <td><?php echo esc_html($s->name); ?></td>
                <td><?php echo esc_html($s->email); ?></td>
                <td><?php echo WSMVP_Settings::get_instance()->format_currency($s->estimated_value); ?></td>
                <td><?php echo date_i18n('d/m/Y', strtotime($s->created_at)); ?></td>
                <td><a href="?page=wsmvp-simulations&view=<?php echo $s->id; ?>">Ver</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
