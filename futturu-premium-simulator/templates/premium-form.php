<?php
/**
 * Premium Simulator Form Template
 * 9-step wizard form for website simulation
 * 
 * @package Futturu_Premium_Simulator
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="futturu-premium-simulator" id="futturuPremiumSimulator">
    <?php wp_nonce_field('futturu_premium_nonce', 'futturu_security'); ?>
    
    <!-- Progress Bar -->
    <div class="futturu-progress-bar">
        <div class="futturu-progress-steps">
            <div class="futturu-step active" data-step="1">
                <span class="step-number">1</span>
                <span class="step-label"><?php _e('Projeto', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="2">
                <span class="step-number">2</span>
                <span class="step-label"><?php _e('Conteúdo', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="3">
                <span class="step-number">3</span>
                <span class="step-label"><?php _e('Recursos', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="4">
                <span class="step-number">4</span>
                <span class="step-label"><?php _e('Marketing', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="5">
                <span class="step-number">5</span>
                <span class="step-label"><?php _e('Hospedagem', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="6">
                <span class="step-number">6</span>
                <span class="step-label"><?php _e('Manutenção', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="7">
                <span class="step-number">7</span>
                <span class="step-label"><?php _e('Investimento', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="8">
                <span class="step-number">8</span>
                <span class="step-label"><?php _e('Dados', 'futturu-premium-simulator'); ?></span>
            </div>
            <div class="futturu-step" data-step="9">
                <span class="step-number">9</span>
                <span class="step-label"><?php _e('Resumo', 'futturu-premium-simulator'); ?></span>
            </div>
        </div>
        <div class="futturu-progress-line">
            <div class="futturu-progress-fill" style="width: 0%;"></div>
        </div>
    </div>
    
    <form id="futturu-simulation-form" method="post">
        
        <!-- Step 1: Project Identification -->
        <div class="futturu-form-step active" data-step="1">
            <h2><?php _e('Identificação do Projeto', 'futturu-premium-simulator'); ?></h2>
            
            <div class="futturu-form-group">
                <label for="project_type"><?php _e('Tipo de Projeto', 'futturu-premium-simulator'); ?> *</label>
                <select id="project_type" name="project_type" required>
                    <option value=""><?php _e('Selecione...', 'futturu-premium-simulator'); ?></option>
                    <option value="novo"><?php _e('Site Novo', 'futturu-premium-simulator'); ?></option>
                    <option value="redesenho"><?php _e('Redesenho / Reformulação', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="site_category"><?php _e('Categoria do Site', 'futturu-premium-simulator'); ?> *</label>
                <select id="site_category" name="site_category" required>
                    <option value=""><?php _e('Selecione...', 'futturu-premium-simulator'); ?></option>
                    <option value="institucional"><?php _e('Institucional', 'futturu-premium-simulator'); ?></option>
                    <option value="ecommerce"><?php _e('E-commerce / Loja Virtual', 'futturu-premium-simulator'); ?></option>
                    <option value="landing_page"><?php _e('Landing Page', 'futturu-premium-simulator'); ?></option>
                    <option value="portal"><?php _e('Portal / News', 'futturu-premium-simulator'); ?></option>
                    <option value="blog"><?php _e('Blog', 'futturu-premium-simulator'); ?></option>
                    <option value="marketplace"><?php _e('Marketplace', 'futturu-premium-simulator'); ?></option>
                    <option value="saas"><?php _e('SaaS / Aplicação Web', 'futturu-premium-simulator'); ?></option>
                    <option value="outro"><?php _e('Outro', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="complexity"><?php _e('Complexidade', 'futturu-premium-simulator'); ?> *</label>
                <select id="complexity" name="complexity" required>
                    <option value=""><?php _e('Selecione...', 'futturu-premium-simulator'); ?></option>
                    <option value="baixa"><?php _e('Baixa - Layout simples, funcionalidades básicas', 'futturu-premium-simulator'); ?></option>
                    <option value="media"><?php _e('Média - Design personalizado, integrações moderadas', 'futturu-premium-simulator'); ?></option>
                    <option value="alta"><?php _e('Alta - Funcionalidades complexas, múltiplas integrações', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
        </div>
        
        <!-- Step 2: Content and Structure -->
        <div class="futturu-form-step" data-step="2">
            <h2><?php _e('Conteúdo e Estrutura', 'futturu-premium-simulator'); ?></h2>
            
            <div class="futturu-form-group">
                <label for="pages_count"><?php _e('Número estimado de páginas', 'futturu-premium-simulator'); ?> *</label>
                <input type="number" id="pages_count" name="pages_count" min="1" max="500" value="5" required>
            </div>
            
            <div class="futturu-form-group">
                <label><?php _e('Páginas Padrão', 'futturu-premium-simulator'); ?></label>
                <div class="futturu-checkbox-group">
                    <label><input type="checkbox" name="pages_checklist[]" value="home" checked> <?php _e('Home', 'futturu-premium-simulator'); ?></label>
                    <label><input type="checkbox" name="pages_checklist[]" value="sobre" checked> <?php _e('Sobre', 'futturu-premium-simulator'); ?></label>
                    <label><input type="checkbox" name="pages_checklist[]" value="contato" checked> <?php _e('Contato', 'futturu-premium-simulator'); ?></label>
                    <label><input type="checkbox" name="pages_checklist[]" value="servicos"> <?php _e('Serviços', 'futturu-premium-simulator'); ?></label>
                    <label><input type="checkbox" name="pages_checklist[]" value="produtos"> <?php _e('Produtos', 'futturu-premium-simulator'); ?></label>
                    <label><input type="checkbox" name="pages_checklist[]" value="blog"> <?php _e('Blog', 'futturu-premium-simulator'); ?></label>
                    <label><input type="checkbox" name="pages_checklist[]" value="portfolio"> <?php _e('Portfólio', 'futturu-premium-simulator'); ?></label>
                    <label><input type="checkbox" name="pages_checklist[]" value="faq"> <?php _e('FAQ', 'futturu-premium-simulator'); ?></label>
                </div>
            </div>
            
            <div class="futturu-form-group">
                <label for="languages"><?php _e('Idiomas', 'futturu-premium-simulator'); ?></label>
                <select id="languages" name="languages">
                    <option value="pt-BR"><?php _e('Português (Brasil)', 'futturu-premium-simulator'); ?></option>
                    <option value="pt-PT"><?php _e('Português (Portugal)', 'futturu-premium-simulator'); ?></option>
                    <option value="en"><?php _e('Inglês', 'futturu-premium-simulator'); ?></option>
                    <option value="es"><?php _e('Espanhol', 'futturu-premium-simulator'); ?></option>
                    <option value="multi"><?php _e('Multilíngue', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="text_origin"><?php _e('Origem dos Textos', 'futturu-premium-simulator'); ?></label>
                <select id="text_origin" name="text_origin">
                    <option value="cliente"><?php _e('Cliente fornecerá', 'futturu-premium-simulator'); ?></option>
                    <option value="copywriting"><?php _e('Preciso de Copywriting', 'futturu-premium-simulator'); ?></option>
                    <option value="ia"><?php _e('Gerar com IA', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="image_origin"><?php _e('Origem das Imagens', 'futturu-premium-simulator'); ?></label>
                <select id="image_origin" name="image_origin">
                    <option value="cliente"><?php _e('Cliente fornecerá', 'futturu-premium-simulator'); ?></option>
                    <option value="banco"><?php _e('Banco de Imagens', 'futturu-premium-simulator'); ?></option>
                    <option value="ambos"><?php _e('Ambos', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
        </div>
        
        <!-- Step 3: Additional Resources (Add-ons) -->
        <div class="futturu-form-step" data-step="3">
            <h2><?php _e('Recursos Adicionais', 'futturu-premium-simulator'); ?></h2>
            <p class="futturu-step-description"><?php _e('Selecione os recursos extras que deseja incluir:', 'futturu-premium-simulator'); ?></p>
            
            <div class="futturu-checkbox-group futturu-addons-grid">
                <label><input type="checkbox" name="addons_selected[]" value="faq"> <?php _e('FAQ', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="login_sistema"> <?php _e('Login / Área do Cliente', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="newsletter"> <?php _e('Newsletter', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="busca_avancada"> <?php _e('Busca Avançada', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="chat_online"> <?php _e('Chat Online', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="agendamento"> <?php _e('Agendamento', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="area_cliente"> <?php _e('Área do Cliente Avançada', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="multidioma"> <?php _e('Multi-idioma', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="blog_integrado"> <?php _e('Blog Integrado', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="galeria_fotos"> <?php _e('Galeria de Fotos', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="video_embed"> <?php _e('Vídeos Embed', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="redes_sociais"> <?php _e('Integração Redes Sociais', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="whatsapp_button"> <?php _e('Botão WhatsApp', 'futturu-premium-simulator'); ?></label>
                <label><input type="checkbox" name="addons_selected[]" value="analytics"> <?php _e('Google Analytics', 'futturu-premium-simulator'); ?></label>
            </div>
        </div>
        
        <!-- Step 4: Marketing and SEO -->
        <div class="futturu-form-step" data-step="4">
            <h2><?php _e('Marketing e SEO', 'futturu-premium-simulator'); ?></h2>
            
            <div class="futturu-form-group">
                <label><?php _e('Integrações Google', 'futturu-premium-simulator'); ?></label>
                <div class="futturu-checkbox-group">
                    <label><input type="checkbox" name="google_integrations[]" value="analytics"> Google Analytics</label>
                    <label><input type="checkbox" name="google_integrations[]" value="tag_manager"> Google Tag Manager</label>
                    <label><input type="checkbox" name="google_integrations[]" value="search_console"> Google Search Console</label>
                    <label><input type="checkbox" name="google_integrations[]" value="my_business"> Google Meu Negócio</label>
                </div>
            </div>
            
            <div class="futturu-form-group">
                <label for="seo_level"><?php _e('Nível de SEO', 'futturu-premium-simulator'); ?></label>
                <select id="seo_level" name="seo_level">
                    <option value="basico"><?php _e('Básico - Meta tags, sitemap, robots.txt', 'futturu-premium-simulator'); ?></option>
                    <option value="avancado"><?php _e('Avançado - Otimização completa, schema markup, performance', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
        </div>
        
        <!-- Step 5: Domain and Hosting -->
        <div class="futturu-form-step" data-step="5">
            <h2><?php _e('Domínio e Hospedagem', 'futturu-premium-simulator'); ?></h2>
            
            <div class="futturu-form-group">
                <label for="domain_status"><?php _e('Status do Domínio', 'futturu-premium-simulator'); ?></label>
                <select id="domain_status" name="domain_status">
                    <option value="nao_tenho"><?php _e('Não tenho domínio', 'futturu-premium-simulator'); ?></option>
                    <option value="ja_comprei"><?php _e('Já comprei domínio', 'futturu-premium-simulator'); ?></option>
                    <option value="preciso_registrar"><?php _e('Preciso registrar', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="current_hosting"><?php _e('Hospedagem Atual', 'futturu-premium-simulator'); ?></label>
                <select id="current_hosting" name="current_hosting">
                    <option value=""><?php _e('Não se aplica', 'futturu-premium-simulator'); ?></option>
                    <option value="shared"><?php _e('Hospedagem Compartilhada', 'futturu-premium-simulator'); ?></option>
                    <option value="vps"><?php _e('VPS', 'futturu-premium-simulator'); ?></option>
                    <option value="dedicated"><?php _e('Servidor Dedicado', 'futturu-premium-simulator'); ?></option>
                    <option value="cloud"><?php _e('Cloud', 'futturu-premium-simulator'); ?></option>
                    <option value="wp_engine"><?php _e('WP Engine / Kinsta', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="cloud_interest"><?php _e('Interesse em Cloud Premium (Cloudez)?', 'futturu-premium-simulator'); ?></label>
                <select id="cloud_interest" name="cloud_interest">
                    <option value="nao"><?php _e('Não', 'futturu-premium-simulator'); ?></option>
                    <option value="sim"><?php _e('Sim, quero saber mais', 'futturu-premium-simulator'); ?></option>
                    <option value="quero"><?php _e('Quero migrar para Cloud', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label><?php _e('Recursos de Servidor', 'futturu-premium-simulator'); ?></label>
                <div class="futturu-checkbox-group">
                    <label><input type="checkbox" name="server_resources[]" value="ssl"> SSL/HTTPS</label>
                    <label><input type="checkbox" name="server_resources[]" value="cdn"> CDN</label>
                    <label><input type="checkbox" name="server_resources[]" value="backup"> Backup Automático</label>
                    <label><input type="checkbox" name="server_resources[]" value="monitoramento"> Monitoramento 24/7</label>
                    <label><input type="checkbox" name="server_resources[]" value="escalabilidade"> Escalabilidade Automática</label>
                </div>
            </div>
        </div>
        
        <!-- Step 6: Maintenance -->
        <div class="futturu-form-step" data-step="6">
            <h2><?php _e('Manutenção', 'futturu-premium-simulator'); ?></h2>
            
            <div class="futturu-form-group">
                <label for="maintenance_frequency"><?php _e('Frequência de Atualizações', 'futturu-premium-simulator'); ?></label>
                <select id="maintenance_frequency" name="maintenance_frequency">
                    <option value="semanal"><?php _e('Semanal', 'futturu-premium-simulator'); ?></option>
                    <option value="quinzenal"><?php _e('Quinzenal', 'futturu-premium-simulator'); ?></option>
                    <option value="mensal"><?php _e('Mensal', 'futturu-premium-simulator'); ?></option>
                    <option value="sob_demanda"><?php _e('Sob Demanda', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="maintenance_plan"><?php _e('Pacote de Manutenção', 'futturu-premium-simulator'); ?></label>
                <select id="maintenance_plan" name="maintenance_plan">
                    <option value="nenhum"><?php _e('Não preciso no momento', 'futturu-premium-simulator'); ?></option>
                    <option value="basico"><?php _e('Básico - Atualizações e backups', 'futturu-premium-simulator'); ?></option>
                    <option value="padrao"><?php _e('Padrão - + Pequenas alterações', 'futturu-premium-simulator'); ?></option>
                    <option value="premium"><?php _e('Premium - + Suporte prioritário', 'futturu-premium-simulator'); ?></option>
                    <option value="empresarial"><?php _e('Empresarial - Completo 24/7', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
        </div>
        
        <!-- Step 7: Investment and Expectations -->
        <div class="futturu-form-step" data-step="7">
            <h2><?php _e('Investimento e Expectativas', 'futturu-premium-simulator'); ?></h2>
            
            <div class="futturu-form-group">
                <label for="company_category"><?php _e('Categoria da Empresa', 'futturu-premium-simulator'); ?></label>
                <select id="company_category" name="company_category">
                    <option value=""><?php _e('Selecione...', 'futturu-premium-simulator'); ?></option>
                    <option value="micro"><?php _e('Micro Empresa', 'futturu-premium-simulator'); ?></option>
                    <option value="pequena"><?php _e('Pequena Empresa', 'futturu-premium-simulator'); ?></option>
                    <option value="media"><?php _e('Média Empresa', 'futturu-premium-simulator'); ?></option>
                    <option value="grande"><?php _e('Grande Empresa', 'futturu-premium-simulator'); ?></option>
                    <option value="startup"><?php _e('Startup', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="budget_range"><?php _e('Faixa de Budget', 'futturu-premium-simulator'); ?></label>
                <select id="budget_range" name="budget_range">
                    <option value=""><?php _e('Selecione...', 'futturu-premium-simulator'); ?></option>
                    <option value="ate_5k"><?php _e('Até R$ 5.000', 'futturu-premium-simulator'); ?></option>
                    <option value="5k_10k"><?php _e('R$ 5.000 - R$ 10.000', 'futturu-premium-simulator'); ?></option>
                    <option value="10k_20k"><?php _e('R$ 10.000 - R$ 20.000', 'futturu-premium-simulator'); ?></option>
                    <option value="20k_50k"><?php _e('R$ 20.000 - R$ 50.000', 'futturu-premium-simulator'); ?></option>
                    <option value="acima_50k"><?php _e('Acima de R$ 50.000', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="desired_deadline"><?php _e('Prazo Desejado', 'futturu-premium-simulator'); ?></label>
                <select id="desired_deadline" name="desired_deadline">
                    <option value="urgente"><?php _e('Urgente (até 15 dias)', 'futturu-premium-simulator'); ?></option>
                    <option value="curto"><?php _e('Curto prazo (15-30 dias)', 'futturu-premium-simulator'); ?></option>
                    <option value="normal"><?php _e('Normal (30-60 dias)', 'futturu-premium-simulator'); ?></option>
                    <option value="flexivel"><?php _e('Flexível (60+ dias)', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="meeting_type"><?php _e('Tipo de Reunião Preferida', 'futturu-premium-simulator'); ?></label>
                <select id="meeting_type" name="meeting_type">
                    <option value="online"><?php _e('Online (Zoom/Meet)', 'futturu-premium-simulator'); ?></option>
                    <option value="presencial"><?php _e('Presencial', 'futturu-premium-simulator'); ?></option>
                    <option value="telefone"><?php _e('Telefone/WhatsApp', 'futturu-premium-simulator'); ?></option>
                    <option value="email"><?php _e('Apenas por E-mail', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
        </div>
        
        <!-- Step 8: Client Data -->
        <div class="futturu-form-step" data-step="8">
            <h2><?php _e('Dados do Cliente', 'futturu-premium-simulator'); ?></h2>
            
            <div class="futturu-form-group">
                <label for="client_name"><?php _e('Nome Completo', 'futturu-premium-simulator'); ?> *</label>
                <input type="text" id="client_name" name="client_name" required>
            </div>
            
            <div class="futturu-form-group">
                <label for="client_email"><?php _e('E-mail', 'futturu-premium-simulator'); ?> *</label>
                <input type="email" id="client_email" name="client_email" required>
            </div>
            
            <div class="futturu-form-group">
                <label for="client_phone"><?php _e('WhatsApp/Telefone', 'futturu-premium-simulator'); ?> *</label>
                <input type="tel" id="client_phone" name="client_phone" required placeholder="(00) 00000-0000">
            </div>
            
            <div class="futturu-form-group">
                <label for="client_cnpj"><?php _e('CNPJ (opcional)', 'futturu-premium-simulator'); ?></label>
                <input type="text" id="client_cnpj" name="client_cnpj" placeholder="00.000.000/0000-00">
            </div>
            
            <div class="futturu-form-group">
                <label for="client_segment"><?php _e('Segmento de Atuação', 'futturu-premium-simulator'); ?></label>
                <input type="text" id="client_segment" name="client_segment" placeholder="Ex: Tecnologia, Saúde, Educação...">
            </div>
            
            <div class="futturu-form-group">
                <label for="how_found_us"><?php _e('Como nos conheceu?', 'futturu-premium-simulator'); ?></label>
                <select id="how_found_us" name="how_found_us">
                    <option value=""><?php _e('Selecione...', 'futturu-premium-simulator'); ?></option>
                    <option value="google"><?php _e('Google', 'futturu-premium-simulator'); ?></option>
                    <option value="instagram"><?php _e('Instagram', 'futturu-premium-simulator'); ?></option>
                    <option value="linkedin"><?php _e('LinkedIn', 'futturu-premium-simulator'); ?></option>
                    <option value="facebook"><?php _e('Facebook', 'futturu-premium-simulator'); ?></option>
                    <option value="indicacao"><?php _e('Indicação', 'futturu-premium-simulator'); ?></option>
                    <option value="youtube"><?php _e('YouTube', 'futturu-premium-simulator'); ?></option>
                    <option value="outro"><?php _e('Outro', 'futturu-premium-simulator'); ?></option>
                </select>
            </div>
            
            <div class="futturu-form-group">
                <label for="observations"><?php _e('Observações Adicionais', 'futturu-premium-simulator'); ?></label>
                <textarea id="observations" name="observations" rows="4" placeholder="Conte-nos mais sobre seu projeto..."></textarea>
            </div>
        </div>
        
        <!-- Step 9: Summary -->
        <div class="futturu-form-step" data-step="9">
            <h2><?php _e('Resumo e Confirmação', 'futturu-premium-simulator'); ?></h2>
            <p class="futturu-step-description"><?php _e('Revise todas as informações antes de enviar:', 'futturu-premium-simulator'); ?></p>
            
            <div id="futturu-summary-content" class="futturu-summary">
                <!-- Summary will be dynamically generated by JavaScript -->
            </div>
            
            <div class="futturu-terms">
                <label>
                    <input type="checkbox" id="terms_acceptance" required>
                    <?php _e('Concordo com o processamento dos meus dados conforme a LGPD e autorizo o contato da equipe comercial.', 'futturu-premium-simulator'); ?> *
                </label>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="futturu-form-navigation">
            <button type="button" class="futturu-btn futturu-btn-prev" disabled>
                <?php _e('Voltar', 'futturu-premium-simulator'); ?>
            </button>
            <button type="button" class="futturu-btn futturu-btn-next">
                <?php _e('Próximo', 'futturu-premium-simulator'); ?>
            </button>
            <button type="submit" class="futturu-btn futturu-btn-submit" style="display:none;">
                <?php _e('Enviar Simulação para Análise', 'futturu-premium-simulator'); ?>
            </button>
        </div>
        
    </form>
    
    <!-- Success Message -->
    <div id="futturu-success-message" class="futturu-success-message" style="display:none;">
        <div class="futturu-success-icon">✓</div>
        <h3><?php _e('Simulação Enviada com Sucesso!', 'futturu-premium-simulator'); ?></h3>
        <p><?php _e('Obrigado! Nossa equipe analisará suas informações e entrará em contato em breve.', 'futturu-premium-simulator'); ?></p>
    </div>
    
    <!-- Loading Overlay -->
    <div id="futturu-loading" class="futturu-loading" style="display:none;">
        <div class="futturu-spinner"></div>
        <p><?php _e('Enviando...', 'futturu-premium-simulator'); ?></p>
    </div>
</div>
