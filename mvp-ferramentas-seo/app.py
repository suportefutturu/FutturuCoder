"""
Aplicação Flask MVP - 15 Ferramentas Online Grátis
Para executar: python app.py
Acesse: http://localhost:5000
"""

import os
from flask import Flask, render_template, request, redirect, url_for, Response
from tools import TOOLS

app = Flask(__name__)
app.secret_key = os.environ.get('SECRET_KEY', 'dev-secret-key-change-in-production')

# Cache simples para ferramentas estáticas (em produção use Redis ou similar)
tool_cache = {}


@app.route('/')
def index():
    """Página inicial com lista de todas as ferramentas"""
    tools_list = list(TOOLS.values())
    return render_template('index.html', tools_list=tools_list, enumerate=enumerate)


@app.route('/<slug>', methods=['GET', 'POST'])
def tool_page(slug):
    """Página de cada ferramenta"""
    if slug not in TOOLS:
        return redirect(url_for('index'))
    
    tool = TOOLS[slug]
    result = None
    
    if request.method == 'POST':
        # Processa a ferramenta baseada no tipo
        if slug == 'contador-de-palavras':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'remover-acentos':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'inverter-texto':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'gerador-numeros-aleatorios':
            min_val = request.form.get('min_val', 1)
            max_val = request.form.get('max_val', 100)
            result = tool['function'](min_val, max_val)
        
        elif slug == 'ordenar-lista':
            text = request.form.get('text', '')
            order = request.form.get('order', 'asc')
            sort_type = request.form.get('sort_type', 'alpha')
            result = tool['function'](text, order, sort_type)
        
        elif slug == 'conversor-maiusculas-minusculas':
            text = request.form.get('text', '')
            case_type = request.form.get('case_type', 'upper')
            result = tool['function'](text, case_type)
        
        elif slug == 'verificador-palindromo':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'gerador-qr-code':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'contador-caracteres-sem-espacos':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'remover-linhas-em-branco':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'corretor-caps-lock':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'contador-vogais-consoantes':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'remover-palavras-repetidas':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'separador-texto-virgula':
            text = request.form.get('text', '')
            result = tool['function'](text)
        
        elif slug == 'texto-para-slug':
            text = request.form.get('text', '')
            result = tool['function'](text)
    
    # Define ferramentas relacionadas (baseado em categorias similares)
    related_slugs = list(TOOLS.keys())
    if slug in related_slugs:
        related_slugs.remove(slug)
    # Pega 5 ferramentas aleatórias como relacionadas
    import random
    random.shuffle(related_slugs)
    related_tools = related_slugs[:5]
    
    return render_template(
        'tool_template.html',
        tool=tool,
        result=result,
        related_tools=related_tools,
        tools_dict=TOOLS
    )


@app.route('/sitemap.xml')
def sitemap():
    """Gera sitemap.xml dinamicamente para SEO"""
    xml = '<?xml version="1.0" encoding="UTF-8"?>\n'
    xml += '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
    
    # URL da home
    xml += '  <url>\n'
    xml += '    <loc>https://seusite.com/</loc>\n'
    xml += '    <changefreq>daily</changefreq>\n'
    xml += '    <priority>1.0</priority>\n'
    xml += '  </url>\n'
    
    # URLs das ferramentas
    for slug, tool in TOOLS.items():
        xml += f'  <url>\n'
        xml += f'    <loc>https://seusite.com/{slug}</loc>\n'
        xml += f'    <changefreq>weekly</changefreq>\n'
        xml += f'    <priority>0.8</priority>\n'
        xml += f'  </url>\n'
    
    xml += '</urlset>'
    
    return Response(xml, mimetype='application/xml')


@app.route('/robots.txt')
def robots():
    """Robots.txt para permitir indexação total"""
    content = """User-agent: *
Allow: /

Sitemap: https://seusite.com/sitemap.xml

# Permitir todos os bots de busca
Googlebot: *
Allow: /

Bingbot: *
Allow: /
"""
    return Response(content, mimetype='text/plain')


if __name__ == '__main__':
    # Em produção, use: app.run(host='0.0.0.0', port=int(os.environ.get('PORT', 5000)))
    # Para desenvolvimento local:
    app.run(debug=True, host='0.0.0.0', port=5000)
