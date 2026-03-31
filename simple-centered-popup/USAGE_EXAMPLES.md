# Simple Centered Popup - Exemplos de Uso

## Índice

1. [Shortcode](#shortcode)
2. [Função PHP](#função-php)
3. [Controle via JavaScript](#controle-via-javascript)
4. [Personalização Avançada](#personalização-avançada)
5. [Casos de Uso Comuns](#casos-de-uso-comuns)

---

## Shortcode

### Uso Básico

Adicione em qualquer post, página ou widget:

```
[sc_popup]
```

### Em Templates do Tema

```php
<?php echo do_shortcode('[sc_popup]'); ?>
```

### Com Parâmetro ID (Reservado para Futuro)

```
[sc_popup id="1"]
```

---

## Função PHP

### Uso Básico no Tema

Adicione em qualquer arquivo do tema (header.php, footer.php, single.php, etc):

```php
<?php
if ( function_exists( 'sc_popup_render' ) ) {
    sc_popup_render();
}
?>
```

### Display Condicional

**Somente na homepage:**
```php
<?php
if ( is_front_page() && function_exists( 'sc_popup_render' ) ) {
    sc_popup_render();
}
?>
```

**Somente em posts:**
```php
<?php
if ( is_single() && function_exists( 'sc_popup_render' ) ) {
    sc_popup_render();
}
?>
```

**Somente em páginas específicas:**
```php
<?php
if ( is_page( array( 'about', 'contact' ) ) && function_exists( 'sc_popup_render' ) ) {
    sc_popup_render();
}
?>
```

**Exceto na homepage:**
```php
<?php
if ( ! is_front_page() && function_exists( 'sc_popup_render' ) ) {
    sc_popup_render();
}
?>
```

**Após determinado conteúdo:**
```php
<?php
the_content();

// Mostrar popup após o conteúdo do post
if ( is_single() && function_exists( 'sc_popup_render' ) ) {
    sc_popup_render();
}
?>
```

---

## Controle via JavaScript

### Abrir Popup Manualmente

Útil para botões personalizados, timers, ou ações do usuário:

```javascript
// Abrir ao clicar em um botão
document.querySelector('.meu-botao').addEventListener('click', function(e) {
    e.preventDefault();
    if (window.SCPPopup) {
        window.SCPPopup.open();
    }
});
```

### Fechar Popup Programaticamente

```javascript
// Fechar após uma ação
function completeAction() {
    // ... fazer algo
    if (window.SCPPopup) {
        window.SCPPopup.close();
    }
}
```

### Trigger Baseado em Scroll

```javascript
// Abrir popup quando usuário rolar 50% da página
let popupShown = false;

window.addEventListener('scroll', function() {
    const scrollPercent = (window.scrollY + window.innerHeight) / document.body.scrollHeight;
    
    if (scrollPercent >= 0.5 && !popupShown && window.SCPPopup) {
        popupShown = true;
        window.SCPPopup.open();
    }
});
```

### Trigger com Delay Personalizado

```javascript
// Abrir após 5 segundos (ignora configuração do admin)
setTimeout(function() {
    if (window.SCPPopup) {
        window.SCPPopup.open();
    }
}, 5000);
```

### Trigger em Intenção de Saída (Exit Intent)

```javascript
// Detectar quando mouse sai da janela (desktop)
document.addEventListener('mouseleave', function(e) {
    if (e.clientY <= 0 && window.SCPPopup) {
        window.SCPPopup.open();
    }
});
```

---

## Personalização Avançada

### CSS Personalizado

Adicione no `style.css` do seu tema child:

```css
/* Popup maior */
.scp-popup {
    max-width: 800px !important;
}

/* Cor personalizada do botão */
.scp-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* Animação customizada */
@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
    }
}

.scp-animation-fade .scp-popup {
    animation: bounceIn 0.5s ease-out;
}

/* Overlay mais escuro */
.scp-overlay {
    background-color: rgba(0, 0, 0, 0.9) !important;
}

/* Esconder em mobile */
@media (max-width: 768px) {
    .scp-overlay {
        display: none !important;
    }
}

/* Estilo premium */
.scp-popup {
    border: 2px solid #gold;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.scp-popup-title {
    font-family: 'Playfair Display', serif;
    text-transform: uppercase;
    letter-spacing: 2px;
}
```

### Filtros PHP

**Modificar título dinamicamente:**

```php
add_filter('option_scp_title', function($title) {
    $hour = date('G');
    
    if ($hour < 12) {
        return 'Bom Dia! ' . $title;
    } elseif ($hour < 18) {
        return 'Boa Tarde! ' . $title;
    } else {
        return 'Boa Noite! ' . $title;
    }
});
```

**Adicionar conteúdo extra:**

```php
add_filter('option_scp_content', function($content) {
    $extra = '<p style="font-size: 12px; color: #999; margin-top: 20px;">
                * Oferta válida por tempo limitado
              </p>';
    return $content . $extra;
});
```

**Condicional por categoria:**

```php
add_filter('option_scp_content', function($content) {
    if ( is_category( 'promocoes' ) ) {
        $content .= '<div class="promo-badge">PROMOÇÃO ESPECIAL!</div>';
    }
    return $content;
});
```

---

## Casos de Uso Comuns

### 1. Newsletter Signup

**Configuração Admin:**
- Título: "Receba Nossas Novidades"
- Conteúdo: "Cadastre-se e ganhe 10% de desconto na primeira compra"
- Imagem: URL de imagem relacionada
- Botão: "Quero me cadastrar"
- URL do botão: Link para página de cadastro

**Código no tema (após conteúdo):**
```php
<?php
if ( is_single() && function_exists( 'sc_popup_render' ) ) {
    sc_popup_render();
}
?>
```

### 2. Anúncio de Promoção

**Configuração Admin:**
- Título: "🎉 Black Friday!"
- Conteúdo: HTML personalizado com contador
- Botão: "Ver Ofertas"
- URL: Página de promoções
- Frequência: 1 dia

**HTML no campo de conteúdo:**
```html
<div style="text-align: center;">
    <p style="font-size: 48px; margin: 20px 0;">⏰</p>
    <p>Ofertas por tempo limitado!</p>
    <p style="color: #e74c3c; font-weight: bold;">Até 70% OFF</p>
</div>
```

### 3. Aviso Importante

**Configuração Admin:**
- Título: "Aviso aos Visitantes"
- Conteúdo: Informações sobre horário de funcionamento, mudanças, etc.
- Botão: "Entendi"
- Frequência: 0 (mostra sempre)

**Display condicional (somente homepage):**
```php
<?php
if ( is_front_page() && function_exists( 'sc_popup_render' ) ) {
    add_filter('scp_should_show', '__return_true');
    sc_popup_render();
}
?>
```

### 4. Vídeo Promocional

**Configuração Admin:**
- Título: "Conheça Nossa Empresa"
- Vídeo Embed: Código do YouTube/Vimeo
- Botão: "Saiba Mais"
- URL: Página "Sobre Nós"

**Embed do YouTube:**
```html
<iframe src="https://www.youtube.com/embed/SEU_VIDEO_ID" 
        frameborder="0" 
        allowfullscreen>
</iframe>
```

### 5. Pesquisa de Satisfação

**Configuração Admin:**
- Título: "Sua Opinião Importa!"
- Conteúdo: Formulário ou link para pesquisa
- Botão: "Participar da Pesquisa"
- URL: Google Forms/Typeform
- Nova aba: Sim

### 6. Cookie Consent (LGPD/GDPR)

**Configuração Admin:**
- Título: "Privacidade de Dados"
- Conteúdo: Texto sobre cookies
- Botão: "Aceitar"
- Frequência: 30 dias

**CSS adicional:**
```css
.scp-popup {
    max-width: 400px !important;
    bottom: 20px !important;
    top: auto !important;
}
```

---

## Integrações

### Google Analytics

```javascript
// No script.js ou via Google Tag Manager
document.addEventListener('scpPopupOpened', function() {
    gtag('event', 'popup_opened', {
        'event_category': 'engagement',
        'event_label': 'centered_popup'
    });
});

document.addEventListener('scpPopupClosed', function() {
    gtag('event', 'popup_closed', {
        'event_category': 'engagement',
        'event_label': 'centered_popup'
    });
});

document.querySelector('.scp-button').addEventListener('click', function() {
    gtag('event', 'popup_conversion', {
        'event_category': 'conversion',
        'event_label': 'popup_button_click'
    });
});
```

### Facebook Pixel

```javascript
// Disparar evento quando popup abrir
fbq('track', 'ViewContent', {
    content_name: 'Simple Centered Popup',
    content_type: 'popup'
});
```

### Hotjar

```javascript
// Taggear sessões com popup
if (window.SCPPopup) {
    hj('trigger', 'popup_shown');
}
```

---

## Dicas de Performance

1. **Minifique assets em produção:**
   ```bash
   # CSS
   cssnano assets/css/style.css assets/css/style.min.css
   
   # JS
   terser assets/js/script.js -o assets/js/script.min.js
   ```

2. **Use CDN para imagens:**
   ```
   https://seu-cdn.com/imagens/popup.jpg
   ```

3. **Lazy load para vídeos:**
   ```html
   <iframe data-src="https://youtube.com/embed/ID" loading="lazy"></iframe>
   ```

4. **Defer JavaScript:**
   ```php
   wp_script_add_data('scp-script', 'defer', true);
   ```

---

## Suporte

Para dúvidas e suporte:
- Documentação completa: `README.md`
- WordPress.org Forum
- GitHub Issues (se open source)
