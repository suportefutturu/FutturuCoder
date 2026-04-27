"""
Módulo com todas as 15 ferramentas do MVP
Cada ferramenta é uma função que recebe dados e retorna resultados
"""

import random
import re
import unicodedata
from unidecode import unidecode
import qrcode
from io import BytesIO
import base64


def count_words(text):
    """Contador de Palavras Online"""
    if not text:
        return {"words": 0, "characters": 0, "characters_no_spaces": 0, "lines": 0, "paragraphs": 0}
    
    words = len(text.split())
    characters = len(text)
    characters_no_spaces = len(text.replace(" ", "").replace("\t", "").replace("\n", ""))
    lines = len(text.splitlines()) if text.strip() else 0
    paragraphs = len([p for p in text.split('\n\n') if p.strip()])
    
    return {
        "words": words,
        "characters": characters,
        "characters_no_spaces": characters_no_spaces,
        "lines": lines,
        "paragraphs": paragraphs
    }


def remove_accents(text):
    """Removedor de Acentos"""
    if not text:
        return ""
    return unidecode(text)


def reverse_text(text):
    """Inverter Texto"""
    if not text:
        return ""
    return text[::-1]


def generate_random_number(min_val, max_val):
    """Gerador de Números Aleatórios"""
    try:
        min_val = int(min_val)
        max_val = int(max_val)
        if min_val > max_val:
            min_val, max_val = max_val, min_val
        return random.randint(min_val, max_val)
    except (ValueError, TypeError):
        return None


def sort_list(text, order="asc", sort_type="alpha"):
    """Ordenar Lista Online"""
    if not text:
        return ""
    
    lines = [line for line in text.splitlines() if line.strip()]
    
    if sort_type == "numeric":
        try:
            lines = sorted(lines, key=lambda x: float(x.strip()) if x.strip() else 0, reverse=(order == "desc"))
        except ValueError:
            lines = sorted(lines, reverse=(order == "desc"))
    else:
        lines = sorted(lines, reverse=(order == "desc"))
    
    return "\n".join(lines)


def convert_case(text, case_type="upper"):
    """Conversor de Maiúsculas e Minúsculas"""
    if not text:
        return ""
    
    if case_type == "upper":
        return text.upper()
    elif case_type == "lower":
        return text.lower()
    elif case_type == "title":
        return text.title()
    elif case_type == "sentence":
        sentences = re.split(r'([.!?]\s*)', text)
        result = ""
        for i, sentence in enumerate(sentences):
            if i % 2 == 0:
                result += sentence.capitalize()
            else:
                result += sentence
        return result
    return text


def check_palindrome(text):
    """Verificador de Palíndromo"""
    if not text:
        return False
    
    cleaned = re.sub(r'[^a-zA-Z0-9]', '', text).lower()
    return cleaned == cleaned[::-1]


def generate_qr_code(data):
    """Gerador de QR Code simples"""
    if not data:
        return None
    
    qr = qrcode.QRCode(version=1, box_size=10, border=5)
    qr.add_data(data)
    qr.make(fit=True)
    
    img = qr.make_image(fill_color="black", back_color="white")
    
    buffered = BytesIO()
    img.save(buffered, format="PNG")
    img_str = base64.b64encode(buffered.getvalue()).decode()
    
    return f"data:image/png;base64,{img_str}"


def count_characters_no_spaces(text):
    """Contador de Caracteres sem Espaços"""
    if not text:
        return {"total": 0, "no_spaces": 0}
    
    total = len(text)
    no_spaces = len(text.replace(" ", "").replace("\t", "").replace("\n", ""))
    
    return {"total": total, "no_spaces": no_spaces}


def remove_empty_lines(text):
    """Remover Linhas em Branco"""
    if not text:
        return ""
    
    lines = text.splitlines()
    filtered = [line for line in lines if line.strip()]
    return "\n".join(filtered)


def fix_caps_lock(text):
    """Corretor de Caixa Alta"""
    if not text:
        return ""
    
    # Detecta se está todo em maiúsculo (ignorando espaços e pontuação)
    letters_only = re.sub(r'[^a-zA-Z]', '', text)
    if letters_only and letters_only.isupper():
        return text.capitalize()
    return text


def count_vowels_consonants(text):
    """Contador de Vogais e Consoantes"""
    if not text:
        return {"vowels": 0, "consonants": 0, "numbers": 0, "symbols": 0}
    
    vowels = set('aeiouAEIOUáéíóúàèìòùâêîôûãõäëïöü')
    consonants = set('bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ')
    
    vowel_count = 0
    consonant_count = 0
    number_count = 0
    symbol_count = 0
    
    for char in text:
        if char in vowels:
            vowel_count += 1
        elif char in consonants:
            consonant_count += 1
        elif char.isdigit():
            number_count += 1
        elif char not in ' \t\n':
            symbol_count += 1
    
    return {
        "vowels": vowel_count,
        "consonants": consonant_count,
        "numbers": number_count,
        "symbols": symbol_count
    }


def remove_duplicate_words(text):
    """Remover Palavras Repetidas"""
    if not text:
        return ""
    
    words = text.split()
    seen = set()
    result = []
    
    for word in words:
        word_lower = word.lower()
        if word_lower not in seen:
            seen.add(word_lower)
            result.append(word)
    
    return " ".join(result)


def split_by_comma(text):
    """Separador de Texto por Vírgula"""
    if not text:
        return ""
    
    items = [item.strip() for item in text.split(',')]
    return "\n".join(items)


def text_to_slug(text):
    """Conversor de Texto para Slug"""
    if not text:
        return ""
    
    # Remove acentos
    slug = unidecode(text)
    # Converte para minúsculo
    slug = slug.lower()
    # Substitui espaços e caracteres especiais por hífens
    slug = re.sub(r'[^a-z0-9]+', '-', slug)
    # Remove hífens extras no início e fim
    slug = slug.strip('-')
    
    return slug


# Dicionário com todas as ferramentas
TOOLS = {
    "contador-de-palavras": {
        "name": "Contador de Palavras Online",
        "slug": "contador-de-palavras",
        "keywords": ["contador de palavras", "word counter", "quantas palavras tem meu texto"],
        "description": "Conte palavras, caracteres, linhas e parágrafos em tempo real. Ferramenta gratuita para escritores e estudantes.",
        "function": count_words,
        "input_type": "textarea",
        "button_text": "Contar",
        "icon": "📝"
    },
    "remover-acentos": {
        "name": "Removedor de Acentos Online",
        "slug": "remover-acentos",
        "keywords": ["remover acentos online", "tirar acentos do texto", "remover acentos para SEO"],
        "description": "Remova todos os acentos do seu texto instantaneamente. Ideal para URLs amigáveis e SEO.",
        "function": remove_accents,
        "input_type": "textarea",
        "button_text": "Remover Acentos",
        "icon": "✨"
    },
    "inverter-texto": {
        "name": "Inverter Texto Online",
        "slug": "inverter-texto",
        "keywords": ["inverter texto", "espelhar palavras", "reverse text generator"],
        "description": "Inverta qualquer texto completamente. Ferramenta simples e rápida para espelhar palavras.",
        "function": reverse_text,
        "input_type": "textarea",
        "button_text": "Inverter",
        "icon": "🔄"
    },
    "gerador-numeros-aleatorios": {
        "name": "Gerador de Números Aleatórios",
        "slug": "gerador-numeros-aleatorios",
        "keywords": ["gerar número aleatório", "random number between 1 and 100", "sorteio de números"],
        "description": "Gere números aleatórios entre dois valores. Perfeito para sorteios e decisões rápidas.",
        "function": generate_random_number,
        "input_type": "range",
        "button_text": "Gerar Número",
        "icon": "🎲"
    },
    "ordenar-lista": {
        "name": "Ordenar Lista Online",
        "slug": "ordenar-lista",
        "keywords": ["colocar lista em ordem alfabética", "ordenar palavras online", "sort text lines"],
        "description": "Ordene suas listas alfabética ou numericamente. Crescente ou decrescente, você escolhe.",
        "function": sort_list,
        "input_type": "textarea",
        "button_text": "Ordenar",
        "icon": "📋"
    },
    "conversor-maiusculas-minusculas": {
        "name": "Conversor de Maiúsculas e Minúsculas",
        "slug": "conversor-maiusculas-minusculas",
        "keywords": ["converter texto para maiúsculas", "deixar tudo minusculo", "transformar cada palavra com primeira letra maiúscula"],
        "description": "Converta texto para maiúsculo, minúsculo, título ou frase. Múltiplas opções de formatação.",
        "function": convert_case,
        "input_type": "textarea",
        "button_text": "Converter",
        "icon": "🔠"
    },
    "verificador-palindromo": {
        "name": "Verificador de Palíndromo Online",
        "slug": "verificador-palindromo",
        "keywords": ["é um palíndromo?", "verificar palíndromo online", "frases que se leem igual ao contrário"],
        "description": "Descubra se uma palavra ou frase é um palíndromo. Verificação instantânea e gratuita.",
        "function": check_palindrome,
        "input_type": "text",
        "button_text": "Verificar",
        "icon": "🔍"
    },
    "gerador-qr-code": {
        "name": "Gerador de QR Code Grátis",
        "slug": "gerador-qr-code",
        "keywords": ["gerar qr code grátis", "criar qr code do texto", "qr code generator online"],
        "description": "Crie QR Codes gratuitos para textos e URLs. Download imediato em formato PNG.",
        "function": generate_qr_code,
        "input_type": "text",
        "button_text": "Gerar QR Code",
        "icon": "📱"
    },
    "contador-caracteres-sem-espacos": {
        "name": "Contador de Caracteres sem Espaços",
        "slug": "contador-caracteres-sem-espacos",
        "keywords": ["contar caracteres sem espaço", "quantos caracteres tem meu texto"],
        "description": "Conte caracteres totais e sem espaços. Essencial para tweets, meta descriptions e SMS.",
        "function": count_characters_no_spaces,
        "input_type": "textarea",
        "button_text": "Contar",
        "icon": "🔢"
    },
    "remover-linhas-em-branco": {
        "name": "Remover Linhas em Branco Online",
        "slug": "remover-linhas-em-branco",
        "keywords": ["remover linhas vazias", "eliminar linhas em branco de texto", "clean text lines"],
        "description": "Elimine automaticamente todas as linhas vazias do seu texto. Limpeza rápida e eficiente.",
        "function": remove_empty_lines,
        "input_type": "textarea",
        "button_text": "Remover Linhas",
        "icon": "🧹"
    },
    "corretor-caps-lock": {
        "name": "Corretor de Caps Lock Online",
        "slug": "corretor-caps-lock",
        "keywords": ["corrigir caps lock", "texto escrito com caps lock sem querer", "fix uppercase text"],
        "description": "Corrija textos escritos acidentalmente em CAPS LOCK. Conversão automática para formato normal.",
        "function": fix_caps_lock,
        "input_type": "textarea",
        "button_text": "Corrigir",
        "icon": "🔧"
    },
    "contador-vogais-consoantes": {
        "name": "Contador de Vogais e Consoantes",
        "slug": "contador-vogais-consoantes",
        "keywords": ["quantas vogais tem meu texto", "contar consoantes online"],
        "description": "Conte vogais, consoantes, números e símbolos no seu texto. Análise completa de caracteres.",
        "function": count_vowels_consonants,
        "input_type": "textarea",
        "button_text": "Contar",
        "icon": "🅰️"
    },
    "remover-palavras-repetidas": {
        "name": "Remover Palavras Repetidas Online",
        "slug": "remover-palavras-repetidas",
        "keywords": ["remover palavras duplicadas", "eliminar repetições no texto"],
        "description": "Elimine palavras duplicadas mantendo a primeira ocorrência. Texto limpo e sem repetições.",
        "function": remove_duplicate_words,
        "input_type": "textarea",
        "button_text": "Remover Duplicatas",
        "icon": "🗑️"
    },
    "separador-texto-virgula": {
        "name": "Separador de Texto por Vírgula",
        "slug": "separador-texto-virgula",
        "keywords": ["transformar lista separada por vírgula em linhas", "csv para linhas"],
        "description": "Converta listas separadas por vírgula em linhas individuais. Ideal para CSV e dados.",
        "function": split_by_comma,
        "input_type": "textarea",
        "button_text": "Separar",
        "icon": "✂️"
    },
    "texto-para-slug": {
        "name": "Conversor de Texto para Slug",
        "slug": "texto-para-slug",
        "keywords": ["gerar slug amigável", "texto para url amigável", "slug generator online"],
        "description": "Transforme títulos em URLs amigáveis. Slugs otimizados para SEO automaticamente.",
        "function": text_to_slug,
        "input_type": "text",
        "button_text": "Gerar Slug",
        "icon": "🔗"
    }
}
