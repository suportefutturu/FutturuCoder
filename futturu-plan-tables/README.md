# Tabelas Comparativas de Planos Futturu

Plugin WordPress para exibição de tabelas comparativas de planos para os serviços da Futturu.

## 📋 Descrição

Este plugin permite exibir tabelas de comparação claras e atraentes para os principais serviços da Futturu:

- **Criação de Websites** - Planos de desenvolvimento web
- **Hospedagem de Websites** - Planos de hospedagem cloud
- **Manutenção/Suporte de Websites** - Planos de manutenção e suporte técnico

O foco é facilitar a decisão do cliente ao mostrar as diferenças entre planos e destacar o valor percebido de cada opção, culminando em um CTA forte para contato com um especialista da Futturu.

## ✨ Funcionalidades

- **Shortcodes específicos** para cada categoria de serviço
- **Painel administrativo completo** para configurar planos, features, preços e CTAs
- **Design moderno e responsivo** que funciona em qualquer tema WordPress
- **Destaque visual** para planos recomendados ou mais populares
- **Badges personalizáveis** (Mais Contratado, Melhor Custo-Benefício, etc.)
- **Sistema de abas** para visualizar todas as categorias em uma única página
- **Envio de leads** por e-mail através dos CTAs

## 🚀 Instalação

1. Faça o upload da pasta `futturu-plan-tables` para o diretório `/wp-content/plugins/`
2. Ative o plugin através do menu "Plugins" no WordPress
3. Acesse **Configurações > Planos Futturu** para configurar os planos

## 📱 Shortcodes Disponíveis

Use os shortcodes abaixo para exibir as tabelas em qualquer página ou post:

```
[futturu_planos_criacao]     - Exibe tabela de Criação de Websites
[futturu_planos_hospedagem]  - Exibe tabela de Hospedagem Cloud
[futturu_planos_manutencao]  - Exibe tabela de Manutenção & Suporte
[futturu_planos_all]         - Exibe todas as tabelas com navegação por abas
```

### Exemplo de Uso

```html
<!-- Em uma página específica -->
[futturu_planos_criacao]

<!-- Ou todas as tabelas com abas -->
[futturu_planos_all]
```

## ⚙️ Configuração

### Acessando o Painel Administrativo

1. No menu do WordPress, vá em **Configurações > Planos Futturu**
2. Navegue pelas abas:
   - 🌐 **Criação de Websites**
   - ☁️ **Hospedagem Cloud**
   - 🔧 **Manutenção & Suporte**
   - ⚙️ **Configurações Gerais**

### Configurando Cada Categoria

Para cada categoria, você pode:

- **Ativar/Desativar** a exibição da categoria
- **Editar título e descrição** introdutória
- **Personalizar o texto e link do CTA**
- **Configurar cada plano**:
  - Nome do plano
  - Preço
  - Badge (etiqueta de destaque)
  - Tipo da badge (info, sucesso, aviso, perigo)
  - Proposta de valor
  - Features inclusas (marcar/desmarcar)

### Destacar Plano Recomendado

Marque a opção **"Destacar como recomendado"** em um plano para:
- Adicionar uma borda colorida no topo
- Destacar visualmente nas tabelas
- Chamar atenção para o plano mais popular

### Configurações Gerais

Na aba **Configurações Gerais**, defina:
- **E-mail para Leads**: E-mail que receberá as mensagens dos formulários

## 🎨 Personalização Visual

O plugin utiliza CSS próprio e não depende de frameworks externos. Para personalizar:

### Cores

Edite o arquivo `assets/css/futturu-plans.css`:

```css
/* Cor principal (gradiente) */
.futturu-plans-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Cor do preço */
.futturu-plan-price {
    color: #667eea;
}
```

### Tipos de Badges

- `info` - Azul (Ideal para Iniciantes)
- `success` - Verde (Mais Contratado, Recomendado)
- `warning` - Amarelo (Melhor Custo-Benefício)
- `danger` - Vermelho (Performance Máxima)

## 📧 Envio de Leads

Quando um visitante clica no CTA "Falar com um Especialista":

1. É direcionado para a página de contato configurada
2. O e-mail configurado recebe uma notificação com:
   - Categoria de interesse
   - Plano selecionado
   - Dados do cliente (quando preenchidos em formulário)

### Integração com Formulários

O plugin prepara a URL do CTA com parâmetros:
```
/contato/?interesse=criacao&plano=profissional
```

Configure seu formulário de contato para capturar esses parâmetros.

## 🔧 Estrutura de Arquivos

```
futturu-plan-tables/
├── futturu-plan-tables.php      # Arquivo principal do plugin
├── includes/
│   ├── class-futturu-plans-admin.php      # Painel administrativo
│   ├── class-futturu-plans-frontend.php   # Renderização frontend
│   └── class-futturu-plans-settings.php   # Configurações e defaults
├── assets/
│   ├── css/
│   │   └── futturu-plans.css              # Estilos frontend/admin
│   └── js/
│       ├── futturu-plans.js               # Scripts frontend
│       └── futturu-admin.js               # Scripts admin
└── README.md                              # Este arquivo
```

## 📊 Planos Incluídos (Default)

### Criação de Websites
- Website Institucional (R$ 3.500)
- Institucional Profissional (R$ 5.000) ⭐
- Landing Page Conversora (R$ 2.800)

### Hospedagem Cloud
- Cloud 1G (R$ 139/mês)
- Cloud 4G (R$ 229/mês) ⭐
- Cloud 8G (R$ 439/mês)

### Manutenção & Suporte
- Suporte Essencial (R$ 299/mês)
- Suporte Profissional (R$ 499/mês) ⭐
- Suporte Premium (R$ 799/mês)

⭐ = Plano destacado como recomendado

## 🛠️ Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- jQuery (incluído no WordPress)

## 🔒 Segurança

- Verificação de nonce em todos os formulários
- Sanitização de todos os dados de entrada
- Escape de todos os dados de saída
- Verificação de capacidades do usuário no admin

## 🌐 Tradução

O plugin está preparado para tradução. O text domain é `futturu-plan-tables`.

Arquivos de tradução devem ser colocados em:
```
/wp-content/languages/plugins/futturu-plan-tables-pt_BR.mo
```

## 📝 Changelog

### Versão 1.0.0
- Lançamento inicial
- Shortcodes para 3 categorias de planos
- Painel administrativo completo
- Design responsivo e moderno
- Sistema de badges e destaques
- Envio de leads por e-mail

## 🤝 Suporte

Para dúvidas ou suporte técnico:
- E-mail: suporte@futturu.com.br
- Website: https://futturu.com.br

## 📄 Licença

GPL v2 or later

---

**Desenvolvido com ❤️ pela Futturu**
