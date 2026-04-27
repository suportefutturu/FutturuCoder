# MVP Ferramentas SEO - 15 Ferramentas Online Grátis

Produto Mínimo Viável (MVP) de uma aplicação web com 15 ferramentas online gratuitas, otimizadas para SEO e monetização via Google AdSense.

## 🚀 Instalação Rápida

### 1. Clone ou baixe o projeto

```bash
cd mvp-ferramentas-seo
```

### 2. Crie um ambiente virtual (recomendado)

```bash
python -m venv venv

# Windows
venv\Scripts\activate

# Linux/Mac
source venv/bin/activate
```

### 3. Instale as dependências

```bash
pip install -r requirements.txt
```

### 4. Execute a aplicação

```bash
python app.py
```

### 5. Acesse no navegador

```
http://localhost:5000
```

---

## 📁 Estrutura do Projeto

```
mvp-ferramentas-seo/
├── app.py                 # Aplicação Flask principal
├── tools.py               # Lógica das 15 ferramentas
├── requirements.txt       # Dependências Python
├── templates/
│   ├── base.html          # Template base com header/footer
│   ├── index.html         # Página inicial com lista de ferramentas
│   └── tool_template.html # Template reutilizável para cada ferramenta
├── static/
│   ├── css/
│   │   └── style.css      # Estilos customizados
│   └── js/
│       └── main.js        # JavaScript comum
└── README.md              # Este arquivo
```

---

## 🛠️ 15 Ferramentas Incluídas

| # | Ferramenta | URL Slug | Palavras-chave Principais |
|---|------------|----------|---------------------------|
| 1 | Contador de Palavras | `/contador-de-palavras` | contador de palavras, word counter |
| 2 | Removedor de Acentos | `/remover-acentos` | remover acentos online, tirar acentos |
| 3 | Inverter Texto | `/inverter-texto` | inverter texto, reverse text |
| 4 | Gerador de Números Aleatórios | `/gerador-numeros-aleatorios` | gerar número aleatório, sorteio |
| 5 | Ordenar Lista | `/ordenar-lista` | ordenar lista, sort text lines |
| 6 | Conversor de Maiúsculas/Minúsculas | `/conversor-maiusculas-minusculas` | converter maiúsculas, case converter |
| 7 | Verificador de Palíndromo | `/verificador-palindromo` | verificar palíndromo, palindrome checker |
| 8 | Gerador de QR Code | `/gerador-qr-code` | gerar qr code grátis, qr code generator |
| 9 | Contador de Caracteres sem Espaços | `/contador-caracteres-sem-espacos` | contar caracteres sem espaço |
| 10 | Remover Linhas em Branco | `/remover-linhas-em-branco` | remover linhas vazias, clean text |
| 11 | Corretor de Caps Lock | `/corretor-caps-lock` | corrigir caps lock, fix uppercase |
| 12 | Contador de Vogais e Consoantes | `/contador-vogais-consoantes` | contar vogais, vowels counter |
| 13 | Remover Palavras Repetidas | `/remover-palavras-repetidas` | remover palavras duplicadas |
| 14 | Separador de Texto por Vírgula | `/separador-texto-virgula` | separar por vírgula, csv para linhas |
| 15 | Conversor de Texto para Slug | `/texto-para-slug` | gerar slug, url amigável |

---

## 💰 Configuração do Google AdSense

### Onde colocar o código do AdSense

Os espaços para anúncios já estão identificados no código com IDs específicos:

#### 1. **Header (topo de todas as páginas)**
Arquivo: `templates/base.html`
```html
<div id="div-gpt-ad-header" class="ad-banner">
    <!-- Cole seu código AdSense aqui -->
</div>
```

#### 2. **Footer (rodapé de todas as páginas)**
Arquivo: `templates/base.html`
```html
<div id="div-gpt-ad-footer" class="ad-banner">
    <!-- Cole seu código AdSense aqui -->
</div>
```

#### 3. **Entre ferramentas na home (a cada 3 ferramentas)**
Arquivo: `templates/index.html`
```html
<div id="div-gpt-ad-between-tools-{{ i // 3 }}" class="ad-banner">
    <!-- Cole seu código AdSense aqui -->
</div>
```

#### 4. **Sidebar (barra lateral nas páginas de ferramentas)**
Arquivo: `templates/tool_template.html`
```html
<div id="div-gpt-ad-sidebar-top" class="ad-banner">
    <!-- Cole seu código AdSense aqui -->
</div>

<div id="div-gpt-ad-sidebar-bottom" class="ad-banner">
    <!-- Cole seu código AdSense aqui -->
</div>
```

#### 5. **In-Content (dentro do conteúdo da ferramenta)**
Arquivo: `templates/tool_template.html`
```html
<div id="div-gpt-ad-in-content-top" class="ad-banner">
    <!-- Cole seu código AdSense aqui -->
</div>

<div id="div-gpt-ad-after-result" class="ad-banner">
    <!-- Cole seu código AdSense aqui -->
</div>
```

### Exemplo de código AdSense para substituir

```html
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXXXX" crossorigin="anonymous"></script>
<!-- Anúncio Responsivo -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-XXXXXXXXXX"
     data-ad-slot="1234567890"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>
```

**Substitua `ca-pub-XXXXXXXXXX` pelo seu ID do AdSense e `1234567890` pelo ID do slot de anúncio.**

---

## 🔧 Modo Desenvolvimento vs Produção

### Desenvolvimento (atual)

No arquivo `app.py`:
```python
if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
```

### Produção

Para produção, modifique `app.py`:

```python
if __name__ == '__main__':
    app.run(
        debug=False,
        host='0.0.0.0',
        port=int(os.environ.get('PORT', 5000))
    )
```

E use um servidor WSGI como Gunicorn:

```bash
pip install gunicorn
gunicorn -w 4 -b 0.0.0.0:5000 app:app
```

---

## 🌐 Deploy em Hospedagens Gratuitas

### PythonAnywhere

1. Crie conta em https://www.pythonanywhere.com
2. Upload dos arquivos via dashboard
3. Configure o Web App apontando para `app.py`
4. Instale dependências via console:
   ```bash
   pip install -r requirements.txt
   ```

### Render

1. Crie conta em https://render.com
2. Novo "Web Service" conectado ao seu repositório Git
3. Build Command: `pip install -r requirements.txt`
4. Start Command: `gunicorn app:app`

### Railway

1. Crie conta em https://railway.app
2. Deploy a partir do GitHub
3. Adicione variável de ambiente `PORT=5000`
4. Railway detecta automaticamente o Python

### Heroku (alternativa paga)

Crie um arquivo `Procfile`:
```
web: gunicorn app:app
```

E faça deploy normalmente.

---

## 📊 SEO Otimizado para 2026

### Recursos Implementados

✅ **URLs amigáveis**: `/contador-de-palavras`, `/remover-acentos`, etc.

✅ **Meta tags únicas** por ferramenta:
- Title tag rica em palavras-chave
- Meta description única
- Open Graph para redes sociais

✅ **Schema.org JSON-LD**:
- SoftwareApplication para cada ferramenta
- WebSite para a home

✅ **Sitemap.xml automático**: Acesse `/sitemap.xml`

✅ **Robots.txt configurado**: Acesse `/robots.txt`

✅ **Mobile-first**: Bootstrap 5 responsivo

✅ **Core Web Vitals**:
- CSS e JS mínimos
- Sem processamento pesado
- Carregamento rápido

✅ **Links internos**: Ferramentas relacionadas em cada página

✅ **Conteúdo rico**: Descrições de 100+ palavras em cada ferramenta

---

## 🎯 Dicas de Monetização

1. **Posicionamento de anúncios**: Os melhores lugares são:
   - Após o resultado da ferramenta (alta atenção)
   - Sidebar em desktop
   - Entre ferramentas na home

2. **Formatos recomendados**:
   - Header: 728x90 (desktop) / 320x100 (mobile)
   - Sidebar: 300x250 ou 300x600
   - In-content: Responsivo automático

3. **Densidade de anúncios**: Não exagere. Comece com 2-3 por página e teste.

4. **AdSense Auto Ads**: Pode ser ativado como complemento aos espaços manuais.

---

## 📈 Próximos Passos (Opcionais)

- [ ] Adicionar Google Analytics 4
- [ ] Implementar cache Redis para produção
- [ ] Criar PWA (Progressive Web App)
- [ ] Adicionar mais ferramentas baseado em buscas
- [ ] Implementar sistema de favoritos/salvos
- [ ] Adicionar API REST para desenvolvedores
- [ ] Traduzir para inglês e espanhol
- [ ] Criar backlinks através de guest posts

---

## 📝 Licença

Este projeto é open source e pode ser usado livremente para fins comerciais.

---

## 🤝 Suporte

Para dúvidas ou problemas, abra uma issue no repositório.

**Boa sorte com seu MVP! 🚀**
