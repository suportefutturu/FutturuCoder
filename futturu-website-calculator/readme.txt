# Calculadora de Criação de Website Futturu

Plugin WordPress para cálculo interativo de orçamentos de criação de websites.

## Descrição

A **Calculadora de Criação de Website Futturu** é um plugin WordPress que permite que potenciais clientes calculem, de forma interativa, um orçamento estimado para a criação de um website, com base em suas próprias escolhas.

O resultado final é um resumo com o valor aproximado e um formulário para envio dos dados do responsável, direcionando o lead para o e-mail configurado (padrão: suporte@futturu.com.br).

## Funcionalidades

### Frontend (Calculadora)

1. **Tipo de Website** - Seleção entre:
   - Website Institucional
   - Blog
   - Landing Page
   - Hotsite

2. **Complexidade do Projeto** - Níveis:
   - Baixa (Layout padrão)
   - Média (Personalizações básicas)
   - Alta (Layout exclusivo e funcionalidades avançadas)

3. **Número de Páginas** - Input numérico para páginas internas

4. **Aplicações, Plugins e Extras** - Múltiplas seleções:
   - Formulário de Contato Avançado
   - Integração com API Externa
   - Catálogo de Produtos Estático
   - Blog Integrado
   - Área de Membros Simples
   - Chat Online
   - Integração com Redes Sociais
   - SEO Básico
   - Google Analytics Setup
   - Site Multilíngue

5. **Plano de Hospedagem Cloud** (Opcional):
   - Básico
   - Profissional
   - Empresarial

6. **Resultado em Tempo Real** - Exibição do investimento estimado

7. **Formulário de Contato** - Coleta de dados do lead:
   - Nome Completo
   - E-mail
   - Telefone/WhatsApp
   - Empresa (opcional)
   - Mensagem (opcional)

### Backend (Painel Administrativo)

- Configuração de valores para cada tipo de website
- Configuração de multiplicadores para níveis de complexidade
- Definição de valor por página adicional
- Configuração de valores para extras/plugins
- Configuração de planos de hospedagem e valores mensais
- Personalização do e-mail de destino
- Customização de mensagens de sucesso e erro

## Instalação

1. Faça o upload da pasta `futturu-website-calculator` para o diretório `/wp-content/plugins/` do seu WordPress

2. Ative o plugin através do menu 'Plugins' no WordPress

3. Acesse **Configurações > Calculadora Futturu** para configurar os valores e opções

4. Use o shortcode `[futturu_calc]` em qualquer página ou post para exibir a calculadora

## Uso

### Shortcode

```php
[futturu_calc]
```

### Em Templates PHP

```php
<?php echo do_shortcode('[futturu_calc]'); ?>
```

### Configuração no Admin

1. No menu do WordPress, vá em **Configurações > Calculadora Futturu**

2. Configure os valores em formato JSON conforme exemplos:

**Tipos de Website:**
```json
{
    "institucional": {"label": "Website Institucional", "price": 2500},
    "blog": {"label": "Blog", "price": 1800},
    "landing_page": {"label": "Landing Page", "price": 1200},
    "hotsite": {"label": "Hotsite", "price": 1500}
}
```

**Níveis de Complexidade:**
```json
{
    "baixa": {"label": "Baixa (Layout padrão)", "multiplier": 1.0},
    "media": {"label": "Média (Personalizações básicas)", "multiplier": 1.3},
    "alta": {"label": "Alta (Layout exclusivo)", "multiplier": 1.7}
}
```

**Extras:**
```json
{
    "form_contato": {"label": "Formulário de Contato", "price": 300},
    "api_integracao": {"label": "Integração API", "price": 800}
}
```

**Planos de Hospedagem:**
```json
{
    "basico": {"label": "Básico", "price": 49.90},
    "profissional": {"label": "Profissional", "price": 89.90},
    "empresarial": {"label": "Empresarial", "price": 149.90}
}
```

## Estrutura de Arquivos

```
futturu-website-calculator/
├── futturu-website-calculator.php    # Arquivo principal do plugin
├── assets/
│   ├── css/
│   │   └── style.css                 # Estilos da calculadora
│   └── js/
│       └── calculator.js             # Lógica frontend
└── readme.txt                        # Este arquivo
```

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- jQuery (incluído no WordPress)

## Personalização

### Estilos

Os estilos podem ser personalizados editando o arquivo `assets/css/style.css`. O design usa CSS puro sem dependência de frameworks externos.

### Valores Padrão

Os valores padrão configurados no plugin são sugestões baseadas em médias de mercado. Ajuste-os conforme sua política de preços através do painel administrativo.

## Segurança

- Validação de nonce em todas as submissões AJAX
- Sanitização de todos os dados de entrada
- Validação de e-mail no frontend e backend
- Proteção contra acesso direto aos arquivos

## Envio de E-mail

Ao enviar o formulário, um e-mail é enviado para o endereço configurado contendo:

- Dados completos do contato
- Resumo das opções selecionadas
- Valor estimado do desenvolvimento
- Valor mensal da hospedagem (se aplicável)
- Mensagem adicional do cliente

## Suporte

Para dúvidas ou suporte técnico, entre em contato:
- E-mail: suporte@futturu.com.br

## Licença

GPL v2 or later

## Changelog

### 1.0.0
- Lançamento inicial
- Calculadora completa com todos os campos
- Painel administrativo para configuração
- Envio de e-mail integrado
- Design responsivo e profissional
