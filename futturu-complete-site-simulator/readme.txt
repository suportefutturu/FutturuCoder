# Simulador Completo de Criação de Sites Futturu

Plugin WordPress para geração de orçamentos detalhados e personalizados para criação de websites.

## 📋 Descrição

Este plugin captura leads qualificados e automatiza a coleta de informações essenciais através de um simulador multipasso. O investimento estimado é calculado com base na tabela Sinapro e experiência Futturu.

## ✨ Funcionalidades

- **Formulário Multipasso (Wizard)**: 9 etapas intuitivas para coleta de informações
- **Cálculo de Investimento**: Estimativa baseada em Sinapro + experiência Futturu
- **Armazenamento no Banco de Dados**: Tabela personalizada `wp_futturu_simulations`
- **Envio de E-mail**: Notificação automática para a equipe
- **Painel Administrativo**: Configurações completas e visualização de leads
- **Responsivo**: Funciona em qualquer tema WordPress

## 🚀 Instalação

1. Faça upload da pasta `futturu-complete-site-simulator` para `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Configure os valores em **Configurações > Simulador Futturu**
4. Use o shortcode `[futturu_site_simulator]` em qualquer página ou post

## 📱 Uso

### Shortcode
```
[futturu_site_simulator]
```

### Etapas do Simulador

1. **Identificação do Projeto**: Tipo de projeto, tipo de site, complexidade
2. **Conteúdo e Estrutura**: Páginas, menu, idiomas, textos, imagens
3. **Recursos Adicionais**: Add-ons e funcionalidades extras
4. **Marketing Digital e SEO**: Google Marketing, SEO básico/avançado
5. **Domínio e Hospedagem**: Status do domínio, hospedagem atual, interesse Cloudez
6. **Manutenção**: Necessidade de atualização, pacotes
7. **Investimento e Expectativas**: Categoria da empresa, faixa de investimento, prazo
8. **Dados do Cliente**: Informações de contato e detalhes adicionais
9. **Resumo e Confirmação**: Revisão completa e envio

## ⚙️ Configurações

Acesse **Configurações > Simulador Futturu** para:

- Ativar/desativar o plugin
- Configurar envio de e-mail (destino: suporte@futturu.com.br)
- Editar valores base por tipo de site
- Ajustar multiplicadores de complexidade
- Configurar valores de recursos adicionais
- Definir valores de hospedagem e manutenção

## 📊 Visualização de Leads

Acesse **Futturu > Simulações Recebidas** para:

- Ver todas as simulações enviadas
- Filtrar por status, data, tipo de site
- Visualizar detalhes completos de cada lead
- Atualizar status (Novo, Contatado, Qualificado, Fechado)

## 💰 Cálculo de Investimento

O cálculo considera:

- **Valor Base**: Por tipo de site (Blog, Institucional, E-commerce, etc.)
- **Multiplicador de Complexidade**: Baixa (1.0x), Média (1.4x), Alta (1.9x)
- **Páginas Adicionais**: Valor por seção extra
- **Recursos Adicionais**: Valor fixo por add-on selecionado
- **SEO**: Básico (+R$ 800), Avançado (+R$ 2.000)
- **Hospedagem**: Valores anuais conforme plano escolhido
- **Manutenção**: Valores mensais x 12 meses

O resultado final apresenta um range de ±15% para flexibilidade.

## 🗄️ Banco de Dados

O plugin cria a tabela `wp_futturu_simulations` com os seguintes campos principais:

- `id`, `project_type`, `site_type`, `complexity_level`
- `num_pages`, `menu_pages`, `languages`
- `addons`, `google_marketing`, `seo_basic`, `seo_advanced`
- `domain_status`, `hosting_current`, `hosting_premium_interest`
- `maintenance_needed`, `maintenance_package`
- `client_name`, `client_email`, `client_phone`, `client_cnpj`
- `investment_estimated`, `investment_min`, `investment_max`
- `estimated_delivery`, `submission_date`, `status`

## 📧 E-mail de Notificação

Quando ativado, envia e-mail HTML formatado para `suporte@futturu.com.br` contendo:

- Dados completos do cliente
- Resumo das escolhas feitas
- Investimento estimado e prazo
- Link para visualização no admin

## 🎨 Personalização

### Cores e Estilos
Edite o arquivo `assets/css/futturu-simulator.css` para personalizar:
- Cores primárias e secundárias
- Tamanhos e espaçamentos
- Animações e transições

### Textos e Labels
Os textos podem ser editados via painel administrativo ou usando filtros do WordPress.

## 🔒 Segurança

- Validação rigorosa de campos obrigatórios
- Sanitização de todos os dados no backend
- Nonce verification para formulários e AJAX
- Proteção contra SQL injection
- Validação de formatos (e-mail, telefone, CNPJ)

## 📝 Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- jQuery (incluído no WordPress)

## 🛠️ Desenvolvedor

**Futturu** - https://futturu.com.br

Base de cálculo: Tabela Sinapro + Experiência Futturu + Planos Cloudez

## 📄 Licença

GPL v2 or later

## 🔄 Changelog

### Versão 1.0.0
- Lançamento inicial
- Formulário multipasso completo (9 etapas)
- Cálculo de investimento baseado em Sinapro
- Armazenamento em tabela personalizada
- Envio de e-mail automático
- Painel administrativo completo
- Visualização e gestão de leads
