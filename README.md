# Simulador de Impacto Online Futturu - Plugin WordPress

Plugin WordPress completo para um MVP chamado **"Simulador de Impacto Online Futturu"**. Este plugin converte visitantes em leads qualificados ao demonstrar, de forma quantitativa e visualmente impactante, o impacto estimado que um site profissional, otimizado e focado em conversão pode ter no negócio do usuário.

## 📋 Índice

- [Funcionalidades](#-funcionalidades)
- [Requisitos](#-requisitos)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Uso](#-uso)
- [Estrutura do Plugin](#-estrutura-do-plugin)
- [Personalização](#-personalização)
- [Shortcode](#-shortcode)
- [FAQ](#-faq)
- [Suporte](#-suporte)

## ✨ Funcionalidades

### Frontend (Simulador)

- **Questionário Interativo**: Coleta informações sobre o negócio do usuário
  - Tipo de Negócio (13 opções pré-definidas)
  - Faturamento Atual Aproximado (4 faixas)
  - Público-Alvo Principal (B2C, B2B, Ambos)
  - Objetivo com Presença Online (5 opções)

- **Cálculo de Impacto**: Gera projeções baseadas em benchmarks
  - Visitas/Mês Potenciais
  - Leads/Mês Potenciais
  - Vendas/Conversões/Mês
  - Faturamento Adicional Anual

- **Relatório Visual "Antes x Depois"**
  - Painéis comparativos
  - Gráficos interativos (Chart.js)
  - Cards de destaque com aumentos percentuais
  - Justificativas para cada projeção

- **CTA Efetivo**
  - Modal com formulário de contato
  - Pré-preenchimento com dados do negócio
  - Envio de email automático para a Futturu

### Backend (Painel Administrativo)

- **Configurações Gerais**
  - Ativar/desativar plugin
  - Shortcode padrão
  - Aviso legal/disclaimer

- **Matriz de Benchmarks**
  - Coeficientes configuráveis por tipo de negócio
  - Fatores de multiplicação para tráfego, conversão e leads

- **Mensagens Personalizáveis**
  - Títulos e subtítulos
  - Rótulos de métricas
  - Textos de CTA
  - Mensagens de sucesso/erro

- **Configurações de CTA**
  - Texto do botão
  - Email de destino

## 🛠 Requisitos

- **WordPress**: 5.0 ou superior
- **PHP**: 7.4 ou superior
- **jQuery**: Incluído no WordPress core
- **Chart.js**: Carregado via CDN (versão 4.4.0)

## 📦 Instalação

### Método 1: Upload Manual

1. Baixe o plugin e extraia o arquivo ZIP
2. Acesse o painel administrativo do WordPress
3. Navegue até **Plugins > Adicionar Novo**
4. Clique em **Enviar Plugin**
5. Selecione o arquivo ZIP do plugin
6. Clique em **Instalar Agora**
7. Após a instalação, clique em **Ativar**

### Método 2: FTP/SFTP

1. Extraia o arquivo ZIP do plugin
2. Conecte-se ao seu servidor via FTP/SFTP
3. Navegue até `/wp-content/plugins/`
4. Faça upload da pasta `futturu-impact-simulator`
5. Acesse o painel do WordPress
6. Navegue até **Plugins**
7. Encontre "Simulador de Impacto Online Futturu" e clique em **Ativar**

### Método 3: Linha de Comando (WP-CLI)

```bash
cd wp-content/plugins
git clone https://github.com/futturu/futturu-impact-simulator.git
wp plugin activate futturu-impact-simulator
```

## ⚙️ Configuração

Após ativar o plugin:

1. Acesse **Configurações > Simulador Impacto Futturu**
2. Configure as opções desejadas:
   - **Ativar Plugin**: Marque para habilitar o simulador
   - **Shortcode**: Use `[futturu_impact_simulator]` em páginas/posts
   - **Texto do CTA**: Personalize o texto do botão final
   - **Email de Destino**: Defina onde os leads serão enviados
   - **Mensagens**: Personalize todos os textos exibidos
3. Clique em **Salvar Configurações**

## 🚀 Uso

### Adicionar o Simulador a uma Página

1. Crie ou edite uma página no WordPress
2. Adicione o shortcode: `[futturu_impact_simulator]`
3. Publique ou atualize a página

## 📁 Estrutura do Plugin

```
futturu-impact-simulator/
├── assets/
│   ├── css/
│   │   ├── public.css          # Estilos do frontend
│   │   └── admin.css           # Estilos do backend
│   ├── js/
│   │   └── public.js           # JavaScript do simulador
│   └── images/                 # Imagens e ícones
├── includes/
│   ├── class-fis-admin.php     # Classe de administração
│   ├── class-fis-public.php    # Classe pública
│   ├── class-fis-calculator.php # Lógica de cálculo
│   └── class-fis-ajax.php      # Handlers AJAX
├── templates/
│   └── simulator.php           # Template do simulador
├── languages/                  # Arquivos de tradução
├── futturu-impact-simulator.php # Arquivo principal do plugin
└── README.md                   # Esta documentação
```

## 🔤 Shortcode

Use o shortcode em qualquer página ou post do WordPress:

```
[futturu_impact_simulator]
```

## ❓ FAQ

### O simulador funciona com qualquer tema WordPress?

Sim! O plugin foi desenvolvido para ser compatível com qualquer tema WordPress.

### Os dados do simulador são precisos?

As projeções são baseadas em benchmarks históricos da indústria. São estimativas fundamentadas, não garantias.

### Como recebo os leads capturados?

Os leads são enviados por email para o endereço configurado no painel administrativo.

### O plugin é responsivo?

Sim! Totalmente responsivo para dispositivos móveis, tablets e desktops.

## 📄 Licença

GPL v2 ou posterior.

## 🤝 Suporte

- **Email**: suporte@futturu.com.br
- **Website**: https://futturu.com.br

---

**© 2024 Futturu. Todos os direitos reservados.**
