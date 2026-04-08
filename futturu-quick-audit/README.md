# Auditoria Rápida Futturu - Versão Lead Magnet

Plugin WordPress que permite visitantes analisarem seus próprios sites através de um formulário, gerando leads qualificados para a Futturu.

## 🚀 Funcionalidades

- **Formulário de Auditoria Externa**: Visitantes inserem a URL de seu site para análise
- **Análise em 3 Categorias**:
  - Velocidade (5 checks)
  - SEO (4 checks)
  - Segurança (3 checks)
- **Relatório Visual**: Scores de 0-100 com detalhamento de problemas
- **CTA Integrado**: Chamada persuasiva para serviços pagos da Futturu
- **Segurança**: Proteção contra SSRF (acesso a redes internas)

## 📦 Instalação

1. Copie a pasta `futturu-quick-audit` para `/wp-content/plugins/`
2. Ative o plugin via menu "Plugins" no WordPress admin
3. Use o shortcode em qualquer página/post

## 🎯 Uso

### Shortcode Básico
```
[futturu_audit]
```

### Shortcode Personalizado
```
[futturu_audit title="Analise Seu Site Grátis" cta_text="Falar com Especialista"]
```

### Parâmetros do Shortcode
- `title`: Título do formulário (padrão: "Auditoria Gratuita do Seu Site")
- `cta_text`: Texto do botão de CTA (padrão: "Quero minha consultoria gratuita")
- `theme`: Tema visual (padrão: "light")

## 🔍 Critérios de Análise

### Velocidade
- Tag Title presente
- Meta description
- Dimensões de imagem (width/height)
- Lazy loading em imagens
- Scripts render-blocking

### SEO
- Tag H1 única
- Dados estruturados (Schema.org)
- Texto âncora descritivo
- Indexabilidade (noindex)

### Segurança
- Versão do WordPress oculta
- SSL/HTTPS ativo
- Erros de banco de dados expostos

## 🔒 Segurança

O plugin implementa:
- Validação de nonce AJAX
- Bloqueio de IPs privados/localhost (prevenção SSRF)
- Sanitização de URLs
- Timeout em requisições HTTP

## 📝 Requisitos

- WordPress 5.0+
- PHP 7.4+
- jQuery (incluído no WordPress)

## 📄 Licença

Uso proprietário - Futturu
