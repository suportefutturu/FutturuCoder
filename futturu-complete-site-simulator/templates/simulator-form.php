<?php
/**
 * Simulator Form Template
 * Multi-step wizard form for site simulation
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="futturu-simulator-wrapper" id="futturuSimulator">
    <div class="futturu-simulator-header">
        <h2><?php echo esc_html(get_option('futturu_simulator_texts[simulator_title]', __('Simulador de Criação de Sites Futturu', 'futturu-simulator'))); ?></h2>
        <p><?php echo esc_html(get_option('futturu_simulator_texts[simulator_subtitle]', __('Descubra o investimento ideal para o seu projeto web', 'futturu-simulator'))); ?></p>
    </div>

    <!-- Progress Steps -->
    <div class="futturu-progress-bar">
        <?php 
        $step_labels = get_option('futturu_simulator_texts[step_labels]', array(
            'Identificação do Projeto',
            'Conteúdo e Estrutura',
            'Recursos Adicionais',
            'Marketing Digital e SEO',
            'Domínio e Hospedagem',
            'Manutenção',
            'Investimento e Expectativas',
            'Dados do Cliente',
            'Resumo e Confirmação'
        ));
        foreach ($step_labels as $index => $label): ?>
            <div class="progress-step <?php echo $index === 0 ? 'active' : ''; ?>" data-step="<?php echo esc_attr($index); ?>">
                <span class="step-number"><?php echo esc_html($index + 1); ?></span>
                <span class="step-label"><?php echo esc_html($label); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <form id="futturu-simulator-form" method="post">
        <?php wp_nonce_field('futturu_simulator_nonce', 'futturu_nonce'); ?>
        
        <!-- Step 1: Project Identification -->
        <div class="simulator-step active" data-step="1">
            <h3><?php _e('Identificação do Projeto', 'futturu-simulator'); ?></h3>
            
            <div class="form-group">
                <label><?php _e('Tipo de Projeto', 'futturu-simulator'); ?> *</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="project_type" value="novo" checked />
                        <span class="radio-label"><?php _e('Site Novo (Versão 1.0)', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="project_type" value="redesenho" />
                        <span class="radio-label"><?php _e('Redesenho (Novo Design)', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="site_type"><?php _e('Tipo de Site', 'futturu-simulator'); ?> *</label>
                <select id="site_type" name="site_type" required>
                    <option value="blog"><?php _e('Blog', 'futturu-simulator'); ?></option>
                    <option value="news"><?php _e('Notícias', 'futturu-simulator'); ?></option>
                    <option value="portfolio"><?php _e('Portfólio', 'futturu-simulator'); ?></option>
                    <option value="hotsite"><?php _e('Hotsite', 'futturu-simulator'); ?></option>
                    <option value="institutional" selected><?php _e('Institucional', 'futturu-simulator'); ?></option>
                    <option value="ecommerce"><?php _e('E-commerce', 'futturu-simulator'); ?></option>
                    <option value="other"><?php _e('Outro', 'futturu-simulator'); ?></option>
                </select>
                <input type="text" name="site_type_other" id="site_type_other" placeholder="<?php _e('Especifique o tipo de site', 'futturu-simulator'); ?>" style="display:none; margin-top:10px;" />
            </div>

            <div class="form-group">
                <label><?php _e('Complexidade do Projeto', 'futturu-simulator'); ?> *</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="complexity_level" value="baixa" />
                        <span class="radio-label">
                            <strong><?php _e('Baixa', 'futturu-simulator'); ?></strong>
                            <small><?php _e('Site simples, poucas páginas.', 'futturu-simulator'); ?></small>
                        </span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="complexity_level" value="media" checked />
                        <span class="radio-label">
                            <strong><?php _e('Média', 'futturu-simulator'); ?></strong>
                            <small><?php _e('Site com mais páginas e funcionalidades.', 'futturu-simulator'); ?></small>
                        </span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="complexity_level" value="alta" />
                        <span class="radio-label">
                            <strong><?php _e('Alta', 'futturu-simulator'); ?></strong>
                            <small><?php _e('Site altamente personalizado e complexo.', 'futturu-simulator'); ?></small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 2: Content and Structure -->
        <div class="simulator-step" data-step="2">
            <h3><?php _e('Conteúdo e Estrutura', 'futturu-simulator'); ?></h3>

            <div class="form-group">
                <label for="num_pages"><?php _e('Quantidade de Páginas/Seções', 'futturu-simulator'); ?> *</label>
                <select id="num_pages" name="num_pages" required>
                    <option value="ate_6"><?php _e('Até 6 seções', 'futturu-simulator'); ?></option>
                    <option value="ate_10" selected><?php _e('Até 10 seções', 'futturu-simulator'); ?></option>
                    <option value="ate_20"><?php _e('Até 20 seções', 'futturu-simulator'); ?></option>
                    <option value="ate_30"><?php _e('Até 30 seções', 'futturu-simulator'); ?></option>
                    <option value="sob_medida"><?php _e('Sob Medida', 'futturu-simulator'); ?></option>
                </select>
                <input type="text" name="num_pages_custom" id="num_pages_custom" placeholder="<?php _e('Descreva a quantidade necessária', 'futturu-simulator'); ?>" style="display:none; margin-top:10px;" />
            </div>

            <div class="form-group">
                <label><?php _e('Menu do Site (Páginas Comuns)', 'futturu-simulator'); ?></label>
                <div class="checkbox-grid">
                    <?php 
                    $menu_pages = array(
                        'pagina_inicial' => 'Página Inicial',
                        'sobre_nos' => 'Sobre Nós',
                        'produtos_servicos' => 'Produtos/Serviços',
                        'portfolio' => 'Portfólio',
                        'depoimentos' => 'Depoimentos',
                        'blog' => 'Blog',
                        'contato' => 'Contato',
                        'faq' => 'FAQ',
                        'politica_privacidade' => 'Política de Privacidade',
                        'termos_servico' => 'Termos de Serviço',
                        'equipe' => 'Equipe',
                        'carreira' => 'Carreira',
                        'localizacao' => 'Localização',
                        'redes_sociais' => 'Redes Sociais',
                        'newsletter' => 'Newsletter',
                        'cta' => 'Chamada para Ação',
                        'testemunhos' => 'Testemunhos',
                        'videos' => 'Vídeos',
                        'galeria' => 'Galeria',
                        'parceiros' => 'Parceiros'
                    );
                    foreach ($menu_pages as $key => $label): ?>
                        <label class="checkbox-option">
                            <input type="checkbox" name="menu_pages[]" value="<?php echo esc_attr($key); ?>" />
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                    <label class="checkbox-option">
                        <input type="checkbox" name="menu_pages[]" value="outra" id="menu_outra_check" />
                        <?php _e('Outra', 'futturu-simulator'); ?>
                        <input type="text" name="menu_pages_other" id="menu_pages_other" placeholder="<?php _e('Especifique', 'futturu-simulator'); ?>" style="display:inline-block; width:150px; margin-left:10px;" />
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="languages"><?php _e('Idiomas', 'futturu-simulator'); ?></label>
                <select id="languages" name="languages">
                    <option value="1" selected><?php _e('1 Idioma', 'futturu-simulator'); ?></option>
                    <option value="2"><?php _e('2 Idiomas', 'futturu-simulator'); ?></option>
                    <option value="3"><?php _e('3 Idiomas', 'futturu-simulator'); ?></option>
                </select>
            </div>

            <div class="form-group">
                <label><?php _e('Textos', 'futturu-simulator'); ?></label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="texts_provided" value="fornecerei" checked />
                        <span class="radio-label"><?php _e('Fornecerei todos os textos', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="texts_provided" value="preciso_ajuda" />
                        <span class="radio-label"><?php _e('Preciso de ajuda profissional na criação de conteúdos', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label><?php _e('Imagens / Logotipo', 'futturu-simulator'); ?></label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="images_provided" value="fornecerei" checked />
                        <span class="radio-label"><?php _e('Fornecerei todas as fotos/imagens', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="images_provided" value="preciso_ajuda" />
                        <span class="radio-label"><?php _e('Preciso de ajuda profissional para imagens e/ou logotipo', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 3: Additional Resources -->
        <div class="simulator-step" data-step="3">
            <h3><?php _e('Recursos Adicionais / Add-ons', 'futturu-simulator'); ?></h3>
            
            <div class="form-group">
                <div class="checkbox-grid">
                    <?php 
                    $addons = array(
                        'faq_page' => 'Página FAQ',
                        'event_calendar' => 'Calendário de Eventos',
                        'registration_form' => 'Formulário de Inscrição',
                        'login_area' => 'Área de Login',
                        'product_search' => 'Busca de Produtos/Serviços',
                        'ecommerce' => 'E-commerce',
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
                    foreach ($addons as $key => $label): ?>
                        <label class="checkbox-option">
                            <input type="checkbox" name="addons[]" value="<?php echo esc_attr($key); ?>" />
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                    <label class="checkbox-option">
                        <input type="checkbox" name="addons[]" value="outro" id="addon_outro_check" />
                        <?php _e('Outro', 'futturu-simulator'); ?>
                        <input type="text" name="addons_other" id="addons_other" placeholder="<?php _e('Especifique', 'futturu-simulator'); ?>" style="display:inline-block; width:150px; margin-left:10px;" />
                    </label>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 4: Marketing and SEO -->
        <div class="simulator-step" data-step="4">
            <h3><?php _e('Marketing Digital e SEO', 'futturu-simulator'); ?></h3>

            <div class="form-group">
                <label><?php _e('Google Marketing', 'futturu-simulator'); ?></label>
                <div class="checkbox-grid">
                    <label class="checkbox-option">
                        <input type="checkbox" name="google_marketing[]" value="analytics" />
                        Google Analytics
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="google_marketing[]" value="tag_manager" />
                        Google Tag Manager
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="google_marketing[]" value="search_console" />
                        Search Console
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="google_marketing[]" value="my_business" />
                        Google My Business/Maps
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="google_marketing[]" value="google_ads" />
                        Google Ads
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="google_marketing[]" value="outro" id="google_outro_check" />
                        <?php _e('Outro', 'futturu-simulator'); ?>
                        <input type="text" name="google_marketing_other" id="google_marketing_other" placeholder="<?php _e('Especifique', 'futturu-simulator'); ?>" style="display:inline-block; width:150px; margin-left:10px;" />
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label><?php _e('SEO', 'futturu-simulator'); ?></label>
                <div class="checkbox-group">
                    <label class="checkbox-option">
                        <input type="checkbox" name="seo_basic" value="1" />
                        <span class="radio-label">
                            <strong><?php _e('SEO Básico', 'futturu-simulator'); ?></strong>
                            <small><?php _e('Otimização básica para mecanismos de busca', 'futturu-simulator'); ?></small>
                        </span>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="seo_advanced" value="1" />
                        <span class="radio-label">
                            <strong><?php _e('SEO Avançado', 'futturu-simulator'); ?></strong>
                            <small><?php _e('Estratégia completa de SEO e conteúdo', 'futturu-simulator'); ?></small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 5: Domain and Hosting -->
        <div class="simulator-step" data-step="5">
            <h3><?php _e('Domínio e Hospedagem', 'futturu-simulator'); ?></h3>

            <div class="form-group">
                <label><?php _e('Domínio (.com.br)', 'futturu-simulator'); ?></label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="domain_status" value="ja_registrado" checked />
                        <span class="radio-label"><?php _e('Já registrado', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="domain_status" value="preciso_registrar" />
                        <span class="radio-label"><?php _e('Preciso registrar', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="hosting_current"><?php _e('Hospedagem Atual', 'futturu-simulator'); ?></label>
                <select id="hosting_current" name="hosting_current">
                    <option value="nao_tenho"><?php _e('Não tenho hospedagem', 'futturu-simulator'); ?></option>
                    <option value="compartilhada"><?php _e('Hospedagem Compartilhada', 'futturu-simulator'); ?></option>
                    <option value="cloud_preciso_avaliar"><?php _e('Cloud (Preciso avaliar)', 'futturu-simulator'); ?></option>
                    <option value="quero_migrar_cloud"><?php _e('Quero migrar para Cloud', 'futturu-simulator'); ?></option>
                </select>
            </div>

            <div class="form-group">
                <label><?php _e('Problemas com Hospedagem Atual', 'futturu-simulator'); ?></label>
                <div class="checkbox-group">
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_problems[]" value="sites_caiem" />
                        <?php _e('Sites caem com frequência', 'futturu-simulator'); ?>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_problems[]" value="emails_lentos" />
                        <?php _e('E-mails lentos', 'futturu-simulator'); ?>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_problems[]" value="lentidao" />
                        <?php _e('Sinto lentidão', 'futturu-simulator'); ?>
                    </label>
                </div>
            </div>

            <div class="form-group highlight-box">
                <label><?php _e('Interesse em Hospedagem Premium Cloudez', 'futturu-simulator'); ?></label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="hosting_premium_interest" value="sim_quero_conhecer" />
                        <span class="radio-label"><?php _e('Sim, quero conhecer', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="hosting_premium_interest" value="envie_apresentacao" />
                        <span class="radio-label"><?php _e('Agora não, mas envie a apresentação', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label><?php _e('Recursos Desejados na Hospedagem', 'futturu-simulator'); ?></label>
                <div class="checkbox-grid">
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="https" />
                        HTTPS/SSL
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="firewall" />
                        Firewall
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="suporte_rapido" />
                        Suporte Rápido
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="backup" />
                        Backup
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="anti_spam" />
                        Anti-Spam
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="pagespeed" />
                        PageSpeed
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="cdn" />
                        CDN
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="dns" />
                        DNS
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="nf_automatica" />
                        NF Automática
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="painel_facil" />
                        Painel Fácil
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="hosting_features[]" value="php_recente" />
                        PHP Recente
                    </label>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 6: Maintenance -->
        <div class="simulator-step" data-step="6">
            <h3><?php _e('Manutenção', 'futturu-simulator'); ?></h3>

            <div class="form-group">
                <label><?php _e('Necessidade de Atualização', 'futturu-simulator'); ?></label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="maintenance_needed" value="sim_mensalmente" checked />
                        <span class="radio-label"><?php _e('Sim, atualizo mensalmente', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="maintenance_needed" value="nao_estatico" />
                        <span class="radio-label"><?php _e('Não, site estático', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label><?php _e('Importância da Manutenção', 'futturu-simulator'); ?></label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="maintenance_importance" value="sim" checked />
                        <span class="radio-label"><?php _e('Sim, é importante', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="maintenance_importance" value="quero_saber_mais" />
                        <span class="radio-label"><?php _e('Não, quero saber mais', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group highlight-box">
                <label><?php _e('Pacote Manutenção + Hospedagem', 'futturu-simulator'); ?></label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="maintenance_package" value="sim_quero_proposta" checked />
                        <span class="radio-label"><?php _e('Sim, quero uma proposta', 'futturu-simulator'); ?></span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="maintenance_package" value="nao_farei_mesmo" />
                        <span class="radio-label"><?php _e('Não, farei eu mesmo', 'futturu-simulator'); ?></span>
                    </label>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 7: Investment and Expectations -->
        <div class="simulator-step" data-step="7">
            <h3><?php _e('Investimento e Expectativas', 'futturu-simulator'); ?></h3>

            <div class="form-group">
                <label for="company_category"><?php _e('Categoria da Empresa', 'futturu-simulator'); ?></label>
                <select id="company_category" name="company_category">
                    <option value="microempresa"><?php _e('Microempresa', 'futturu-simulator'); ?></option>
                    <option value="pequena"><?php _e('Pequena', 'futturu-simulator'); ?></option>
                    <option value="media"><?php _e('Média', 'futturu-simulator'); ?></option>
                    <option value="grande"><?php _e('Grande', 'futturu-simulator'); ?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="investment_range"><?php _e('Faixa de Investimento Desejada', 'futturu-simulator'); ?></label>
                <select id="investment_range" name="investment_range">
                    <option value="ate_3000"><?php _e('Até R$ 3.000', 'futturu-simulator'); ?></option>
                    <option value="ate_5000"><?php _e('Até R$ 5.000', 'futturu-simulator'); ?></option>
                    <option value="ate_10000" selected><?php _e('Até R$ 10.000', 'futturu-simulator'); ?></option>
                    <option value="ate_15000"><?php _e('Até R$ 15.000', 'futturu-simulator'); ?></option>
                    <option value="acima_15000"><?php _e('Acima de R$ 15.000', 'futturu-simulator'); ?></option>
                </select>
            </div>

            <div class="form-group">
                <label><?php _e('Proposta Sob Medida', 'futturu-simulator'); ?></label>
                <div class="checkbox-group">
                    <label class="checkbox-option">
                        <input type="checkbox" name="proposal_type[]" value="reuniao_presencial" />
                        <?php _e('Reunião Presencial', 'futturu-simulator'); ?>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="proposal_type[]" value="reuniao_virtual" />
                        <?php _e('Reunião Virtual', 'futturu-simulator'); ?>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="proposal_type[]" value="ligacao" />
                        <?php _e('Ligação', 'futturu-simulator'); ?>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="proposal_type[]" value="pesquisa_preco" />
                        <?php _e('Pesquisa de Preço', 'futturu-simulator'); ?>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="proposal_type[]" value="cobrir_proposta" />
                        <?php _e('Cobrir Proposta de Outra Empresa', 'futturu-simulator'); ?>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="delivery_time"><?php _e('Prazo de Entrega', 'futturu-simulator'); ?></label>
                <select id="delivery_time" name="delivery_time">
                    <option value="30-45"><?php _e('30-45 dias úteis', 'futturu-simulator'); ?></option>
                    <option value="45-60"><?php _e('45-60 dias úteis', 'futturu-simulator'); ?></option>
                    <option value="60-90"><?php _e('60-90 dias úteis', 'futturu-simulator'); ?></option>
                    <option value="flexivel"><?php _e('Flexível', 'futturu-simulator'); ?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="specific_date"><?php _e('Data Específica (Opcional)', 'futturu-simulator'); ?></label>
                <input type="date" id="specific_date" name="specific_date" />
            </div>

            <div class="form-group">
                <label for="preferred_time"><?php _e('Horário Preferido (Opcional)', 'futturu-simulator'); ?></label>
                <select id="preferred_time" name="preferred_time">
                    <option value=""><?php _e('Selecione...', 'futturu-simulator'); ?></option>
                    <option value="manha"><?php _e('Manhã', 'futturu-simulator'); ?></option>
                    <option value="tarde"><?php _e('Tarde', 'futturu-simulator'); ?></option>
                    <option value="noite"><?php _e('Noite', 'futturu-simulator'); ?></option>
                </select>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 8: Client Data -->
        <div class="simulator-step" data-step="8">
            <h3><?php _e('Dados do Cliente e Informações Adicionais', 'futturu-simulator'); ?></h3>

            <div class="form-group">
                <label for="client_name"><?php _e('Nome Completo', 'futturu-simulator'); ?> *</label>
                <input type="text" id="client_name" name="client_name" required />
            </div>

            <div class="form-group">
                <label for="client_email"><?php _e('E-mail', 'futturu-simulator'); ?> *</label>
                <input type="email" id="client_email" name="client_email" required />
            </div>

            <div class="form-group">
                <label for="client_phone"><?php _e('WhatsApp', 'futturu-simulator'); ?> *</label>
                <input type="tel" id="client_phone" name="client_phone" required placeholder="(00) 00000-0000" />
            </div>

            <div class="form-group">
                <label for="client_cnpj"><?php _e('CNPJ (Opcional)', 'futturu-simulator'); ?></label>
                <input type="text" id="client_cnpj" name="client_cnpj" placeholder="00.000.000/0000-00" />
            </div>

            <div class="form-group">
                <label><?php _e('Canal de Atendimento Preferido', 'futturu-simulator'); ?></label>
                <div class="checkbox-group">
                    <label class="checkbox-option">
                        <input type="checkbox" name="contact_channel[]" value="whatsapp" checked />
                        WhatsApp
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="contact_channel[]" value="telefone" />
                        Telefone
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="contact_channel[]" value="email" />
                        E-mail
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="site_address"><?php _e('Endereço do Futuro Site', 'futturu-simulator'); ?> *</label>
                <input type="text" id="site_address" name="site_address" required placeholder="meunegocio.com.br" />
            </div>

            <div class="form-group">
                <label for="design_reference"><?php _e('Referência de Design', 'futturu-simulator'); ?></label>
                <input type="url" id="design_reference" name="design_reference" placeholder="www.gostodestesite.com.br" />
            </div>

            <div class="form-group">
                <label for="market_segment"><?php _e('Segmento de Mercado', 'futturu-simulator'); ?> *</label>
                <input type="text" id="market_segment" name="market_segment" required placeholder="Qual área?" />
            </div>

            <div class="form-group">
                <label for="how_found_us"><?php _e('Como Soube da Futturu?', 'futturu-simulator'); ?></label>
                <select id="how_found_us" name="how_found_us">
                    <option value="indicacao"><?php _e('Indicação', 'futturu-simulator'); ?></option>
                    <option value="comunidade_cloudez"><?php _e('Comunidade Cloudez', 'futturu-simulator'); ?></option>
                    <option value="redes_sociais"><?php _e('Redes Sociais', 'futturu-simulator'); ?></option>
                    <option value="google"><?php _e('Google', 'futturu-simulator'); ?></option>
                    <option value="outro"><?php _e('Outro', 'futturu-simulator'); ?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="additional_info"><?php _e('Informações Adicionais', 'futturu-simulator'); ?></label>
                <textarea id="additional_info" name="additional_info" rows="4" placeholder="<?php _e('Detalhes do projeto, observações, etc.', 'futturu-simulator'); ?>"></textarea>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="button" class="btn-next"><?php _e('Próximo', 'futturu-simulator'); ?></button>
            </div>
        </div>

        <!-- Step 9: Summary and Confirmation -->
        <div class="simulator-step" data-step="9">
            <h3><?php _e('Resumo e Confirmação', 'futturu-simulator'); ?></h3>
            
            <div class="summary-container" id="summaryContainer">
                <p><?php _e('Carregando resumo...', 'futturu-simulator'); ?></p>
            </div>

            <div class="investment-display" id="investmentDisplay">
                <h4><?php _e('Investimento Estimado', 'futturu-simulator'); ?></h4>
                <div class="investment-value" id="investmentValue">-</div>
                <div class="investment-range" id="investmentRange">-</div>
                <p class="investment-note"><?php _e('Valores baseados na tabela Sinapro + experiência Futturu', 'futturu-simulator'); ?></p>
            </div>

            <div class="delivery-display" id="deliveryDisplay">
                <h4><?php _e('Prazo Estimado', 'futturu-simulator'); ?></h4>
                <div id="deliveryValue">-</div>
            </div>

            <div class="step-navigation">
                <button type="button" class="btn-prev"><?php _e('Anterior', 'futturu-simulator'); ?></button>
                <button type="submit" class="btn-submit" id="btnSubmit"><?php _e('Enviar Simulação para Análise', 'futturu-simulator'); ?></button>
            </div>
        </div>
    </form>

    <!-- Success Message -->
    <div class="futturu-success-message" id="successMessage" style="display:none;">
        <div class="success-icon">✓</div>
        <h3><?php _e('Simulação Enviada com Sucesso!', 'futturu-simulator'); ?></h3>
        <p><?php _e('Obrigado pelas informações. Nossa equipe entrará em contato em breve através do seu canal preferido.', 'futturu-simulator'); ?></p>
    </div>

    <!-- Loading Overlay -->
    <div class="futturu-loading-overlay" id="loadingOverlay" style="display:none;">
        <div class="spinner"></div>
        <p id="loadingText"><?php _e('Enviando...', 'futturu-simulator'); ?></p>
    </div>
</div>
