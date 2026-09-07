<?php if (!defined('ABSPATH')) exit;
$settings = WSMVP_Settings::get_instance();
$pricing = WSMVP_Pricing::get_instance();
$estimate = $pricing->calculate_estimate($simulation->responses ?? []);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Proposta #<?php echo $simulation->id; ?></title>
    <style>
        body { font-family: Arial; margin: 20px; color: #333; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; }
        .info { text-align: right; }
        .section { margin-bottom: 30px; }
        .section h2 { border-bottom: 2px solid #4361ee; padding-bottom: 10px; }
        .total { font-size: 24px; font-weight: bold; color: #4361ee; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><?php echo esc_html($settings->get('company_name')); ?></div>
        <div class="info">
            <div>Proposta #<?php echo $simulation->id; ?></div>
            <div><?php echo date_i18n('d/m/Y', strtotime($simulation->created_at)); ?></div>
        </div>
    </div>
    
    <div class="section">
        <h2>Cliente</h2>
        <p><strong>Nome:</strong> <?php echo esc_html($simulation->name); ?></p>
        <p><strong>E-mail:</strong> <?php echo esc_html($simulation->email); ?></p>
    </div>
    
    <div class="section">
        <h2>Resumo</h2>
        <p><strong>Tipo:</strong> <?php echo esc_html($estimate['category_label'] ?? $estimate['category']); ?></p>
        <p><strong>Valor:</strong> <?php echo $settings->format_currency($estimate['total']); ?></p>
    </div>
    
    <div class="section">
        <h2>Respostas</h2>
        <?php foreach ($simulation->responses as $key => $value): ?>
            <p><strong><?php echo esc_html($key); ?>:</strong> <?php echo is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value); ?></p>
        <?php endforeach; ?>
    </div>
    
    <div class="section">
        <p><?php echo $pricing->get_estimate_text($estimate); ?></p>
    </div>
</body>
</html>
