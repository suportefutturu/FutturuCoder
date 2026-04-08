<?php
/**
 * Futturu Plans Settings Class
 * Handles default settings and data structure for plans
 */

if (!defined('ABSPATH')) {
    exit;
}

class Futturu_Plans_Settings {
    
    /**
     * Get default settings for all plan categories
     */
    public static function get_default_settings() {
        $defaults = array(
            'criacao' => array(
                'enabled' => true,
                'title' => 'Planos de Criação de Websites',
                'description' => 'Desenvolvemos websites profissionais que transmitem credibilidade e convertem visitantes em clientes. Escolha o plano ideal para o seu negócio.',
                'cta_text' => 'Falar com um Especialista em Criação de Websites',
                'cta_link' => '/contato/',
                'plans' => array(
                    array(
                        'id' => 'institucional-basico',
                        'name' => 'Website Institucional',
                        'price' => 'R$ 3.500',
                        'badge' => 'Ideal para Iniciantes',
                        'badge_type' => 'info',
                        'highlight' => false,
                        'features' => array(
                            'ate_5_paginas' => array('label' => 'Até 5 páginas', 'included' => true),
                            'layout_responsivo' => array('label' => 'Layout Responsivo', 'included' => true),
                            'cms_wordpress' => array('label' => 'CMS WordPress', 'included' => true),
                            'formulario_contato' => array('label' => 'Formulário de Contato', 'included' => true),
                            'otimizacao_basica' => array('label' => 'Otimização Básica', 'included' => true),
                            'seo_on_page' => array('label' => 'SEO On-Page', 'included' => false),
                            'blog_integrado' => array('label' => 'Blog Integrado', 'included' => false),
                            'treinamento_cms' => array('label' => 'Treinamento CMS', 'included' => false),
                        ),
                        'value_proposition' => 'Ideal para quem precisa de credibilidade online.'
                    ),
                    array(
                        'id' => 'institucional-profissional',
                        'name' => 'Institucional Profissional',
                        'price' => 'R$ 5.000',
                        'badge' => 'Mais Contratado',
                        'badge_type' => 'success',
                        'highlight' => true,
                        'features' => array(
                            'ate_5_paginas' => array('label' => 'Até 5 páginas', 'included' => false),
                            'ate_10_paginas' => array('label' => 'Até 10 páginas', 'included' => true),
                            'layout_responsivo' => array('label' => 'Layout Responsivo', 'included' => true),
                            'layout_personalizado' => array('label' => 'Layout Personalizado', 'included' => true),
                            'cms_wordpress' => array('label' => 'CMS WordPress', 'included' => true),
                            'formulario_contato' => array('label' => 'Formulário de Contato', 'included' => true),
                            'seo_on_page' => array('label' => 'SEO On-Page Inicial', 'included' => true),
                            'integracao_redes_sociais' => array('label' => 'Integração com Redes Sociais', 'included' => true),
                            'blog_integrado' => array('label' => 'Blog Integrado', 'included' => true),
                            'treinamento_cms' => array('label' => 'Treinamento CMS', 'included' => true),
                            'otimizacao_basica' => array('label' => 'Otimização Básica', 'included' => true),
                        ),
                        'value_proposition' => 'Mais profissionalismo e alcance para o seu negócio.'
                    ),
                    array(
                        'id' => 'landing-page',
                        'name' => 'Landing Page Conversora',
                        'price' => 'R$ 2.800',
                        'badge' => 'Melhor Custo-Benefício',
                        'badge_type' => 'warning',
                        'highlight' => false,
                        'features' => array(
                            'uma_pagina' => array('label' => '1 Página Focada', 'included' => true),
                            'design_persuasivo' => array('label' => 'Design Persuasivo', 'included' => true),
                            'layout_responsivo' => array('label' => 'Layout Responsivo', 'included' => true),
                            'formulario_avancado' => array('label' => 'Formulário Avançado', 'included' => true),
                            'integracao_email_marketing' => array('label' => 'Integração E-mail Marketing', 'included' => true),
                            'teste_ab_basico' => array('label' => 'Teste A/B (básico)', 'included' => true),
                            'cms_wordpress' => array('label' => 'CMS WordPress', 'included' => false),
                            'blog_integrado' => array('label' => 'Blog Integrado', 'included' => false),
                        ),
                        'value_proposition' => 'Maximize conversões em campanhas e lançamentos.'
                    ),
                )
            ),
            'hospedagem' => array(
                'enabled' => true,
                'title' => 'Planos de Hospedagem de Websites',
                'description' => 'Hospedagem cloud de alta performance com infraestrutura robusta e segurança avançada para o seu site.',
                'cta_text' => 'Falar com um Especialista em Hospedagem Cloud',
                'cta_link' => '/contato/',
                'plans' => array(
                    array(
                        'id' => 'cloud-1g',
                        'name' => 'Cloud 1G',
                        'price' => 'R$ 139/mês',
                        'badge' => 'Perfeito para Sites Pequenos',
                        'badge_type' => 'info',
                        'highlight' => false,
                        'features' => array(
                            'ram_1gb' => array('label' => '1 GB RAM', 'included' => true),
                            'ssd_25gb' => array('label' => '25 GB SSD', 'included' => true),
                            'banda_1tb' => array('label' => '1 TB Banda', 'included' => true),
                            'cdn_automatica' => array('label' => 'CDN Automática', 'included' => true),
                            'ssl_gratis' => array('label' => 'SSL Grátis', 'included' => true),
                            'backups_diarios' => array('label' => 'Backups Diários', 'included' => true),
                            'monitoramento' => array('label' => 'Monitoramento 24/7', 'included' => true),
                            'firewall' => array('label' => 'Firewall', 'included' => false),
                            'suporte_prioritario' => array('label' => 'Suporte Prioritário', 'included' => false),
                        ),
                        'value_proposition' => 'Perfeito para sites pequenos com foco em desempenho.'
                    ),
                    array(
                        'id' => 'cloud-4g',
                        'name' => 'Cloud 4G',
                        'price' => 'R$ 229/mês',
                        'badge' => 'Mais Popular',
                        'badge_type' => 'success',
                        'highlight' => true,
                        'features' => array(
                            'ram_4gb' => array('label' => '4 GB RAM', 'included' => true),
                            'ssd_80gb' => array('label' => '80 GB SSD', 'included' => true),
                            'banda_2tb' => array('label' => '2 TB Banda', 'included' => true),
                            'cdn_premium' => array('label' => 'CDN Premium', 'included' => true),
                            'ssl_wildcard' => array('label' => 'SSL Wildcard', 'included' => true),
                            'backups_avancados' => array('label' => 'Backups Avançados', 'included' => true),
                            'firewall' => array('label' => 'Firewall', 'included' => true),
                            'monitoramento' => array('label' => 'Monitoramento 24/7', 'included' => true),
                            'suporte_prioritario' => array('label' => 'Suporte Prioritário', 'included' => true),
                            'anti_ddos' => array('label' => 'Anti-DDoS', 'included' => false),
                        ),
                        'value_proposition' => 'Mais velocidade e segurança para sites de médio porte.'
                    ),
                    array(
                        'id' => 'cloud-8g',
                        'name' => 'Cloud 8G',
                        'price' => 'R$ 439/mês',
                        'badge' => 'Performance Máxima',
                        'badge_type' => 'danger',
                        'highlight' => false,
                        'features' => array(
                            'ram_8gb' => array('label' => '8 GB RAM', 'included' => true),
                            'ssd_160gb' => array('label' => '160 GB SSD', 'included' => true),
                            'banda_3tb' => array('label' => '3 TB Banda', 'included' => true),
                            'cdn_global' => array('label' => 'CDN Global', 'included' => true),
                            'ssl_wildcard' => array('label' => 'SSL Wildcard', 'included' => true),
                            'backups_versionados' => array('label' => 'Backups Versionados', 'included' => true),
                            'anti_ddos' => array('label' => 'Anti-DDoS', 'included' => true),
                            'firewall' => array('label' => 'Firewall Avançado', 'included' => true),
                            'monitoramento' => array('label' => 'Monitoramento 24/7', 'included' => true),
                            'suporte_247' => array('label' => 'Suporte 24/7', 'included' => true),
                            'suporte_prioritario' => array('label' => 'Suporte Prioritário', 'included' => true),
                        ),
                        'value_proposition' => 'Performance e tranquilidade para negócios digitais exigentes.'
                    ),
                )
            ),
            'manutencao' => array(
                'enabled' => true,
                'title' => 'Planos de Manutenção e Suporte',
                'description' => 'Mantenha seu website seguro, atualizado e performático com nosso serviço especializado de manutenção e suporte técnico.',
                'cta_text' => 'Falar com um Especialista em Manutenção & Suporte',
                'cta_link' => '/contato/',
                'plans' => array(
                    array(
                        'id' => 'suporte-essencial',
                        'name' => 'Suporte Essencial',
                        'price' => 'R$ 299/mês',
                        'badge' => 'Proteção Básica',
                        'badge_type' => 'info',
                        'highlight' => false,
                        'features' => array(
                            'monitoramento_247' => array('label' => 'Monitoramento 24/7', 'included' => true),
                            'backups_semanais' => array('label' => 'Backups Semanais', 'included' => true),
                            'atualizacoes_seguranca' => array('label' => 'Atualizações de Segurança', 'included' => true),
                            'resposta_incidentes_24h' => array('label' => 'Resposta a Incidentes (SLA 24h)', 'included' => true),
                            'relatorio_mensal_basico' => array('label' => 'Relatório Mensal Básico', 'included' => true),
                            'atualizacoes_plugins' => array('label' => 'Atualizações de Plugins/Temas', 'included' => false),
                            'otimizacao_performance' => array('label' => 'Otimização de Performance', 'included' => false),
                            'suporte_ilimitado' => array('label' => 'Suporte Técnico Ilimitado', 'included' => false),
                        ),
                        'value_proposition' => 'Proteja seu site com manutenção preventiva.'
                    ),
                    array(
                        'id' => 'suporte-profissional',
                        'name' => 'Suporte Profissional',
                        'price' => 'R$ 499/mês',
                        'badge' => 'Recomendado',
                        'badge_type' => 'success',
                        'highlight' => true,
                        'features' => array(
                            'monitoramento_247' => array('label' => 'Monitoramento 24/7', 'included' => true),
                            'backups_semanais' => array('label' => 'Backups Semanais', 'included' => true),
                            'atualizacoes_seguranca' => array('label' => 'Atualizações de Segurança', 'included' => true),
                            'atualizacoes_plugins' => array('label' => 'Atualizações de Núcleo/Plugins/Temas', 'included' => true),
                            'otimizacao_performance' => array('label' => 'Otimização de Performance', 'included' => true),
                            'resposta_incidentes_4h' => array('label' => 'Resposta a Incidentes (SLA 4h)', 'included' => true),
                            'suporte_ilimitado' => array('label' => 'Suporte Técnico Ilimitado', 'included' => true),
                            'relatorio_mensal_detalhado' => array('label' => 'Relatório Mensal Detalhado', 'included' => true),
                            'consultoria_tecnica' => array('label' => 'Consultoria Técnica', 'included' => false),
                            'seo_tecnico' => array('label' => 'SEO Técnico Básico', 'included' => false),
                        ),
                        'value_proposition' => 'Mantenha seu site sempre atualizado e rápido.'
                    ),
                    array(
                        'id' => 'suporte-premium',
                        'name' => 'Suporte Premium',
                        'price' => 'R$ 799/mês',
                        'badge' => 'Solução Completa',
                        'badge_type' => 'warning',
                        'highlight' => false,
                        'features' => array(
                            'monitoramento_247' => array('label' => 'Monitoramento 24/7', 'included' => true),
                            'backups_semanais' => array('label' => 'Backups Semanais', 'included' => true),
                            'atualizacoes_seguranca' => array('label' => 'Atualizações de Segurança', 'included' => true),
                            'atualizacoes_plugins' => array('label' => 'Atualizações de Núcleo/Plugins/Temas', 'included' => true),
                            'otimizacao_performance' => array('label' => 'Otimização de Performance', 'included' => true),
                            'resposta_incidentes_4h' => array('label' => 'Resposta a Incidentes (SLA 4h)', 'included' => true),
                            'suporte_ilimitado' => array('label' => 'Suporte Técnico Ilimitado', 'included' => true),
                            'relatorio_mensal_detalhado' => array('label' => 'Relatório Mensal Detalhado', 'included' => true),
                            'suporte_proativo' => array('label' => 'Suporte Proativo', 'included' => true),
                            'consultoria_tecnica_4h' => array('label' => 'Consultoria Técnica (4h/mês)', 'included' => true),
                            'seo_tecnico' => array('label' => 'SEO Técnico Básico', 'included' => true),
                            'migracoes' => array('label' => 'Migrações', 'included' => true),
                        ),
                        'value_proposition' => 'Soluções completas e consultoria contínua para seu site.'
                    ),
                )
            )
        );
        
        // Save defaults if not already saved
        $saved = get_option('futturu_plans_settings');
        if (!$saved) {
            update_option('futturu_plans_settings', $defaults);
        }
        
        return $defaults;
    }
    
    /**
     * Get current settings
     */
    public static function get_settings() {
        $settings = get_option('futturu_plans_settings');
        if (!$settings) {
            $settings = self::get_default_settings();
        }
        return $settings;
    }
    
    /**
     * Update settings
     */
    public static function update_settings($settings) {
        update_option('futturu_plans_settings', $settings);
    }
    
    /**
     * Get features list for a category
     */
    public static function get_features_list($category) {
        $settings = self::get_settings();
        if (!isset($settings[$category]['plans']) || empty($settings[$category]['plans'])) {
            return array();
        }
        
        $all_features = array();
        foreach ($settings[$category]['plans'] as $plan) {
            if (isset($plan['features'])) {
                foreach ($plan['features'] as $key => $feature) {
                    if (!isset($all_features[$key])) {
                        $all_features[$key] = $feature['label'];
                    }
                }
            }
        }
        
        return $all_features;
    }
}
