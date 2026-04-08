# Auditoria Rápida de Website Futturu

Plugin WordPress para realização de auditoria automática local de websites, gerando relatório imediato sobre Velocidade Percebida, SEO Básico e Segurança Superficial.

## 📋 Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- Permissões de administrador para acessar o painel do plugin

## 🚀 Instalação

1. Faça o upload da pasta `futturu-quick-audit` para o diretório `/wp-content/plugins/` do seu WordPress
2. Ative o plugin através do menu "Plugins" no painel administrativo do WordPress
3. Acesse o menu "Auditoria Futturu" no sidebar do admin

## 🎯 Funcionalidades

### Análise de Velocidade Percebida
- Verificação de tags title e meta description
- Otimização de imagens (lazy loading, dimensões, srcset)
- Recursos pré-carregados (preload)
- Scripts render-blocking
- Tamanho do HTML

### Análise de SEO Básico
- Estrutura de cabeçalhos (H1-H6)
- Dados estruturados Schema.org
- URLs amigáveis
- Arquivos robots.txt e sitemap.xml
- Texto âncora descritivo

### Análise de Segurança Superficial
- Exposição da versão do WordPress
- Usuário padrão "admin"
- Plugins inativos e desatualizados
- HTTPS/SSL ativo
- Modo debug ativado
- XML-RPC acessível
- Listagem de diretórios

## 📊 Como Usar

### Pelo Painel Administrativo

1. Acesse **Auditoria Futturu > Nova Auditoria**
2. Clique em **"Executar Auditoria Agora"**
3. Aguarde a análise ser completada
4. Visualize o relatório completo com pontuações e recomendações

### Via Shortcode

Use o shortcode em qualquer página ou post:

```
[futturu_audit]
```

Parâmetros opcionais:
- `theme`: "light" ou "dark" (padrão: "light")
- `show_cta`: "true" ou "false" (padrão: "true")

Exemplo:
```
[futturu_audit theme="light" show_cta="true"]
```

## 📈 Sistema de Pontuação

O plugin gera pontuações de 0-100 para cada categoria:

- **Velocidade (35% do peso total)**: Baseado em otimizações de carregamento
- **SEO (35% do peso total)**: Baseado em melhores práticas de otimização para mecanismos de busca
- **Segurança (30% do peso total)**: Baseado em configurações de segurança básicas

**Pontuação Geral**: Média ponderada das três categorias

## 🔒 Segurança

O plugin implementa:
- Verificação de nonce para todas as requisições AJAX
- Validação de permissões (apenas usuários com `manage_options`)
- Sanitização de todos os dados de saída
- Timeout em requisições HTTP externas

## 📁 Estrutura de Arquivos

```
futturu-quick-audit/
├── futturu-quick-audit.php    # Arquivo principal do plugin
├── assets/
│   ├── css/
│   │   └── admin.css          # Estilos do painel e relatório
│   └── js/
│       └── admin.js           # JavaScript para interatividade
└── README.md                   # Este arquivo
```

## 🛠️ Personalização

### Modificar o CTA

Edite a função `generate_report_html()` no arquivo principal para personalizar:
- URL de destino do botão
- Texto do call-to-action
- Benefícios listados

### Adicionar Novos Checks

Para adicionar novas verificações:

1. Edite as funções `analyze_speed()`, `analyze_seo()` ou `analyze_security()`
2. Adicione novos items ao array `$checks` seguindo o formato:
```php
$checks[] = array(
    'name' => __('Nome do Check', 'futturu-audit'),
    'passed' => $condicao,
    'description' => __('Descrição do resultado', 'futturu-audit'),
    'recommendation' => $condicao ? '' : __('Recomendação se falhar', 'futturu-audit'),
    'weight' => 10  // Peso deste check na pontuação
);
```

## ⚠️ Limitações

Esta é uma ferramenta de auditoria **rápida e superficial**. Ela não substitui uma auditoria profissional completa porque:

- Analisa apenas a página inicial
- Não testa performance real de carregamento (apenas indicadores no HTML)
- Não analisa backlinks ou autoridade de domínio
- Não verifica questões avançadas de segurança
- Não substitui testes em ferramentas como Google PageSpeed Insights, GTmetrix, etc.

## 📞 Suporte e Serviços

Para uma auditoria completa e personalizada, entre em contato com a Futturu:

- Website: https://futturu.com.br
- Email: contato@futturu.com.br

## 📄 Licença

GPL v2 or later

## 👨‍💻 Desenvolvedor

Futturu - https://futturu.com.br

---

**Nota**: Este plugin é distribuído "como está", sem garantias de qualquer tipo.
