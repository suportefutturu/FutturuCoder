# Simulador de WebSite Futturu - Plugin WordPress

## Descrição

Plugin WordPress completo para o MVP "Simulador de WebSite Futturu". Este plugin gera um protótipo visual interativo e personalizado de como seria um hotsite profissional criado pela Futturu, baseado nas escolhas e informações fornecidas pelo usuário.

**Objetivo:** Demonstrar valor, gerar engajamento e capturar leads qualificados interessados em ter um hotsite real, conduzindo-os a um CTA final.

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- jQuery (incluído no WordPress)

## Instalação

1. Faça upload da pasta `futturu-website-simulator` para o diretório `/wp-content/plugins/` do seu WordPress
2. Acesse o painel administrativo do WordPress
3. Vá em **Plugins** → **Plugins Instalados**
4. Localize "Simulador de WebSite Futturu" e clique em **Ativar**

## Configuração

Após ativar o plugin:

1. Acesse **Configurações** → **Simulador Futturu**
2. Configure as seguintes opções:

   - **Ativar Plugin:** Marque para habilitar o simulador no frontend
   - **E-mail para Leads:** E-mail que receberá as solicitações (padrão: suporte@futturu.com.br)
   - **Texto do CTA:** Texto do botão final (padrão: "Solicite uma Proposta Personalizada")
   - **Assunto do E-mail:** Assunto dos e-mails enviados
   - **Tipos de Site:** Edite os tipos de site disponíveis no Passo 1
   - **Categorias de Negócio:** Lista de categorias disponíveis (uma por linha)
   - **Templates de Descrição:** 10 templates configuráveis com placeholders

### Placeholders nos Templates

Use os seguintes placeholders nos templates de descrição:
- `{nome}` - Nome do negócio
- `{categoria}` - Categoria do negócio
- `{localidade}` - Localização
- `{servicos}` - Serviços oferecidos
- `{publico}` - Público-alvo
- `{diferencial}` - Diferencial do negócio

## Uso

### Shortcode

Para usar o simulador em qualquer página ou post, utilize o shortcode:

```
[futturu_hotsite_simulator]
```

### Em Templates PHP

Você também pode usar em templates PHP com:

```php
<?php echo do_shortcode('[futturu_hotsite_simulator]'); ?>
```

## Funcionalidades

### Passo 1: Escolha o Tipo de Site
- Interface com cards clicáveis
- Opções: Website Institucional, Loja Online, Blog Profissional, Landing Page
- Ícones visuais para cada tipo

### Passo 2: Informações do Negócio
- Categoria (dropdown configurável)
- Nome do Negócio
- Localidade
- Serviços Oferecidos
- Público-Alvo
- Diferencial
- **Descrição Gerada Automaticamente** baseada em templates
- **Prévia do Hotsite** em tempo real

### Passo 3: Formulário de Contato
- Nome Completo (obrigatório)
- Telefone (opcional, com máscara automática)
- E-mail (obrigatório, com validação)
- Resumo das seleções anteriores
- Envio por e-mail e salvamento no banco de dados

## Estrutura do Plugin

```
futturu-website-simulator/
├── futturu-website-simulator.php    # Arquivo principal do plugin
├── assets/
│   ├── css/
│   │   └── simulator.css            # Estilos do simulador
│   └── js/
│       └── simulator.js             # JavaScript do frontend
└── README.md                        # Este arquivo
```

## Banco de Dados

O plugin cria automaticamente uma tabela `wp_futturu_leads` para armazenar todos os leads capturados com:
- Dados do negócio
- Descrição gerada
- Informações de contato
- Data de submission

## Validações

- Validação de campos obrigatórios em cada passo
- Validação de formato de e-mail
- Validação de formato de telefone
- Sanitização de todos os dados no backend
- Nonce verification para segurança AJAX

## Design Responsivo

O simulador é totalmente responsivo e funciona em:
- Desktop
- Tablet
- Mobile

## Segurança

- Verificação de nonce em todas as requisições AJAX
- Sanitização de entrada em todos os campos
- Escaping de saída em todo o HTML
- Preparação de queries SQL
- Verificação de capacidades administrativas

## Personalização

### Cores e Estilos

Edite o arquivo `assets/css/simulator.css` para personalizar:
- Cores do gradiente
- Tamanhos de fonte
- Espaçamentos
- Bordas e sombras

### Templates

Os templates podem ser editados no painel administrativo em **Configurações** → **Simulador Futturu**.

### Tipos de Site e Categorias

Adicione, remova ou edite tipos de site e categorias diretamente no painel administrativo.

## Troubleshooting

### O simulador não aparece na página
- Verifique se o plugin está ativado
- Confirme se o shortcode está correto: `[futturu_hotsite_simulator]`
- Verifique se há conflitos com outros plugins

### E-mails não estão sendo enviados
- Verifique as configurações de e-mail do WordPress
- Confirme o e-mail de destino nas configurações do plugin
- Verifique a caixa de spam

### Erros no console JavaScript
- Certifique-se de que o jQuery está carregado
- Verifique se há conflitos com outros scripts

## Suporte

Para suporte técnico ou dúvidas, entre em contato:
- E-mail: suporte@futturu.com.br

## Licença

GPL v2 or later
https://www.gnu.org/licenses/gpl-2.0.html

## Autor

Futturu
https://futturu.com.br

---

**Versão:** 1.0.0
**Última atualização:** 2024
