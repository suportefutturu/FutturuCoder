# Changelog - Simple Centered Popup

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-15

### Adicionado

#### Funcionalidades Principais
- ✅ Pop-up modal centralizado com fundo escurecido e efeito blur
- ✅ Conteúdo editável via admin (título, HTML/WYSIWYG, imagem, vídeo)
- ✅ Botão de ação personalizável (texto, URL, nova aba)
- ✅ Controle de frequência com cookie/LocalStorage (X dias)
- ✅ Auto-open configurável no carregamento da página
- ✅ Delay configurável antes de mostrar (em ms)
- ✅ Fechamento por overlay, botão X ou tecla ESC
- ✅ Shortcode `[sc_popup]` para uso em posts/páginas
- ✅ Função PHP `sc_popup_render()` para temas
- ✅ Suporte a exibição condicional (homepage, posts, páginas)

#### Personalização
- ✅ Dimensão máxima configurável (largura)
- ✅ Opacidade do overlay ajustável
- ✅ Cores personalizadas (fundo do popup, botão)
- ✅ Border radius configurável
- ✅ 3 tipos de animação: Fade, Scale, Slide
- ✅ Duração da animação ajustável

#### Acessibilidade
- ✅ Navegação completa por teclado (Tab, Shift+Tab, ESC)
- ✅ ARIA roles e labels implementados
- ✅ Focus trapping dentro do popup
- ✅ Anúncios para screen readers
- ✅ Respeita preferência "reduced motion"
- ✅ Contraste de cores adequado

#### Performance
- ✅ JavaScript vanilla (sem dependência de jQuery)
- ✅ Assets carregados apenas quando necessário
- ✅ LocalStorage com fallback para cookies
- ✅ CSS otimizado com variáveis CSS
- ✅ Sem queries de banco desnecessárias

#### Segurança
- ✅ Sanitização de todas as entradas (sanitize_text_field, wp_kses_post, esc_url_raw)
- ✅ Escape de todas as saídas (esc_html, esc_attr, esc_url)
- ✅ Nonce verification em requisições AJAX
- ✅ Capability checks em páginas admin
- ✅ Prevenção de acesso direto aos arquivos
- ✅ SameSite attribute em cookies

#### Internacionalização
- ✅ Text domain: `simple-centered-popup`
- ✅ Todas as strings traduzíveis usando __() e _e()
- ✅ load_plugin_textdomain() implementado
- ✅ Pasta /languages preparada

#### Admin
- ✅ Painel de configurações completo via Settings API
- ✅ Seções organizadas (Geral, Conteúdo, Comportamento, Design, Visibilidade)
- ✅ Color pickers nativos do WordPress
- ✅ Editor WYSIWYG para conteúdo HTML
- ✅ Instruções de uso na página de admin

#### Documentação
- ✅ readme.txt formato WordPress.org
- ✅ README.md com documentação técnica completa
- ✅ USAGE_EXAMPLES.md com exemplos práticos
- ✅ CHANGELOG.md (este arquivo)
- ✅ Comentários inline no código
- ✅ Exemplos de testes PHPUnit e Jest

### Técnico

#### Estrutura de Arquivos
```
simple-centered-popup/
├── simple-centered-popup.php    # Main plugin file
├── readme.txt                    # WordPress.org readme
├── README.md                     # Developer docs
├── USAGE_EXAMPLES.md            # Usage examples
├── CHANGELOG.md                 # This file
├── assets/
│   ├── css/
│   │   └── style.css            # Frontend styles
│   └── js/
│       └── script.js            # Frontend JavaScript
├── templates/
│   └── popup.php                # Popup HTML template
└── includes/                    # Reserved for future use
```

#### Requisitos
- WordPress 5.8+
- PHP 7.4+
- Navegadores modernos (ES6+ support)

#### Compatibilidade Testada
- ✅ WordPress 5.8, 5.9, 6.0, 6.1, 6.2, 6.3, 6.4
- ✅ PHP 7.4, 8.0, 8.1, 8.2
- ✅ Chrome, Firefox, Safari, Edge (últimas versões)
- ✅ Mobile Safari (iOS), Chrome Mobile (Android)

---

## [Planejado] - Futuro

### Versão 2.0 (Planejada)

#### Novas Funcionalidades
- [ ] Suporte a múltiplos popups (Custom Post Type)
- [ ] Agendamento de data/hora para exibição
- [ ] Testes A/B integrados
- [ ] Analytics dashboard no admin
- [ ] Templates pré-prontos
- [ ] Regras avançadas de exibição (URLs específicas, categorias, tags)
- [ ] Integração com email marketing (Mailchimp, ConvertKit, etc.)
- [ ] Geolocalização para conteúdo personalizado
- [ ] Dispositivo específico (mobile/desktop)

#### Melhorias Técnicas
- [ ] REST API endpoints
- [ ] GraphQL support
- [ ] Web Components version
- [ ] Gutenberg block nativo
- [ ] Elementor widget
- [ ] WPBakery addon
- [ ] Hooks e filters extensivos
- [ ] CLI commands (WP-CLI)

#### Performance
- [ ] Asset loading assíncrono
- [ ] Critical CSS inline
- [ ] Service Worker cache
- [ ] Preload hints

### Versão 1.1.0 (Próxima)

#### Melhorias
- [ ] Mais opções de animação
- [ ] Controles de tipografia
- [ ] Shadow customization
- [ ] Position options (bottom, top-right, etc.)
- [ ] Trigger por scroll percentage
- [ ] Exit intent detection nativo

#### Correções
- [ ] Bug fixes reportados pela comunidade
- [ ] Melhorias de acessibilidade
- [ ] Otimizações de performance

---

## Notas de Versão

### 1.0.0 - Lançamento Inicial

Esta é a versão inicial do Simple Centered Popup, desenvolvida com foco em:

1. **Simplicidade**: Configuração fácil e intuitiva
2. **Performance**: Código leve e otimizado
3. **Acessibilidade**: WCAG 2.1 AA compliant
4. **Segurança**: Segue todas as best practices do WordPress
5. **Documentação**: Completa e em português

#### Decisões de Arquitetura

**Settings API vs Custom Post Type:**
Optamos por usar Settings API para esta versão inicial porque:
- A maioria dos sites precisa de apenas 1 popup
- Menor complexidade de código
- Melhor performance (menos queries)
- UX mais simples para usuários não-técnicos

Para múltiplos popups, planejamos migrar para CPT na versão 2.0.

**Vanilla JavaScript:**
Sem dependência de jQuery para:
- Reduzir tamanho do bundle
- Melhor performance
- Modern ES6+ features
- Compatibilidade com WordPress moderno (que incentiva remoção do jQuery)

**LocalStorage + Cookie Fallback:**
Usamos LocalStorage como primary e cookies como fallback para:
- Maior capacidade de armazenamento
- Melhor performance
- Compatibilidade com navegadores antigos
- Funcionamento com caching plugins

---

## Upgrade Guide

### De versões anteriores

Não há versões anteriores. Esta é a versão 1.0.0 inicial.

### Para versões futuras

Sempre faça backup antes de atualizar. Leia o changelog da nova versão para verificar breaking changes.

---

## Known Issues

### Versão 1.0.0

Nenhum issue conhecido no lançamento.

Para reportar bugs ou solicitar features:
- WordPress.org Support Forum
- GitHub Issues (se open source)

---

## Credits

### Desenvolvedor
- Your Name - Initial work

### Contributors
- WordPress Community - Best practices and guidelines
- Automattic - WordPress Coding Standards

### Libraries & Tools
- WordPress Settings API
- WordPress Scripts & Styles API
- ES6+ Native JavaScript
- CSS Custom Properties

---

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

---

## Links

- [WordPress Plugin Page](https://wordpress.org/plugins/simple-centered-popup/)
- [GitHub Repository](https://github.com/yourusername/simple-centered-popup)
- [Support Forum](https://wordpress.org/support/plugin/simple-centered-popup/)
- [Documentation](README.md)
- [Usage Examples](USAGE_EXAMPLES.md)
