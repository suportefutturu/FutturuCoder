# FutturuCoder

# CrawlMD Wrapper - Raspagem Web com Comportamento Humano

Um wrapper em Python para o comando `crawlmd` (ou simulação de requisições) que implementa técnicas de humanização para evitar bloqueios por detecção de bots.

## 🚀 Funcionalidades

- **Delay Aleatório**: Simula o tempo de leitura humano entre requisições (2-5s padrão)
- **Backoff Exponencial**: Aumenta automaticamente o tempo de espera após erros (429, 403)
- **Headers Realistas**: User-Agent e cabeçalhos que imitam navegadores modernos
- **Logs Detalhados**: Monitoramento em tempo real de todas as ações
- **Modo Flexível**: Funciona com o binário `crawlmd` ou via simulação HTTP

## 📋 Pré-requisitos

- Python 3.6+
- Debian/Ubuntu (recomendado)
- Comando `crawlmd` instalado (opcional, pode rodar em modo simulação)

### Instalação de Dependências

```bash
# Instalar Python e pip (se necessário)
sudo apt update
sudo apt install python3 python3-pip -y

# Instalar biblioteca requests (opcional, mas recomendado)
pip3 install requests

# Verificar se crawlmd está disponível (opcional)
which crawlmd
# Se não estiver instalado, o script rodará em modo simulação
```

## 🔧 Como Usar

### 1. Uso Básico (Modo Simulação)

Ideal para testes sem o binário `crawlmd` instalado:

```bash
python3 crawlmd_wrapper.py https://exemplo.com
```

### 2. Uso com Binário crawlmd

Se você tem o `crawlmd` instalado no sistema:

```bash
python3 crawlmd_wrapper.py --use-crawlmd https://exemplo.com
```

### 3. Personalizando Delays

Ajuste o tempo de espera entre requisições:

```bash
# Delay mínimo de 3s e máximo de 7s
python3 crawlmd_wrapper.py --min-delay 3 --max-delay 7 https://exemplo.com

# Delay fixo de 5 segundos
python3 crawlmd_wrapper.py --min-delay 5 --max-delay 5 https://exemplo.com
```

### 4. Limitando Requisições

Controle quantas páginas serão processadas:

```bash
# Processar apenas 10 URLs
python3 crawlmd_wrapper.py --max-requests 10 https://exemplo.com

# Arquivo com lista de URLs (uma por linha)
python3 crawlmd_wrapper.py --url-file urls.txt
```

### 5. Modo Verbose

Para ver logs mais detalhados:

```bash
python3 crawlmd_wrapper.py --verbose https://exemplo.com
```

## 📁 Estrutura de Arquivos

```
/workspace/
├── crawlmd_wrapper.py    # Script principal
├── README.md             # Este arquivo
└── urls.txt              # (Opcional) Lista de URLs para processar
```

## 📝 Exemplos Práticos

### Exemplo 1: Raspagem Conservadora

Configuração ideal para sites sensíveis a bots:

```bash
python3 crawlmd_wrapper.py \
  --min-delay 5 \
  --max-delay 10 \
  --max-requests 50 \
  --use-crawlmd \
  https://site-alvo.com
```

### Exemplo 2: Lista de URLs

Crie um arquivo `urls.txt`:

```txt
https://exemplo.com/pagina1
https://exemplo.com/pagina2
https://exemplo.com/pagina3
```

Execute:

```bash
python3 crawlmd_wrapper.py --url-file urls.txt --min-delay 3 --max-delay 6
```

### Exemplo 3: Recuperação de Erros

O script automaticamente lida com erros comuns:

```bash
# Se o servidor retornar 429 (Too Many Requests),
# o script esperará progressivamente: 10s, 20s, 40s...
python3 crawlmd_wrapper.py --verbose https://site-com-rate-limit.com
```

## ⚙️ Parâmetros Disponíveis

| Parâmetro | Descrição | Padrão |
|-----------|-----------|--------|
| `url` | URL única para processar | Obrigatório (se não usar --url-file) |
| `--url-file` | Arquivo com lista de URLs | None |
| `--use-crawlmd` | Usar binário crawlmd em vez de simulação | False |
| `--min-delay` | Delay mínimo entre requisições (segundos) | 2.0 |
| `--max-delay` | Delay máximo entre requisições (segundos) | 5.0 |
| `--max-requests` | Número máximo de requisições | 100 |
| `--max-retries` | Tentativas máximas por URL | 3 |
| `--verbose` | Ativar logs detalhados | False |
| `--output` | Arquivo para salvar resultados | results.json |

## 🛡️ Boas Práticas de Scraping Ético

1. **Respeite robots.txt**: Sempre verifique `https://site.com/robots.txt`
2. **Não sobrecarregue servidores**: Use delays adequados
3. **Identifique seu bot**: Adicione informações de contato no User-Agent
4. **Cacheie resultados**: Evite requisitar a mesma página múltiplas vezes
5. **Monitore erros**: Se receber muitos 429/403, reduza a velocidade

## 🐛 Solução de Problemas

### Erro: "crawlmd: command not found"

O script funcionará em modo simulação automaticamente. Para instalar o crawlmd real, consulte a documentação oficial da ferramenta.

### Erro: Muitas respostas 429

Aumente os delays:

```bash
python3 crawlmd_wrapper.py --min-delay 10 --max-delay 20 https://site.com
```

### Erro: ModuleNotFoundError para 'requests'

Instale a biblioteca:

```bash
pip3 install requests
```

Ou use o modo puramente com bibliotecas padrão (o script detectará automaticamente).

## 📄 Licença

Este projeto é fornecido como-is para fins educacionais. Use responsavelmente e respeite os termos de serviço dos sites alvo.

## 🤝 Contribuição

Sinta-se à vontade para modificar o script conforme suas necessidades. Ajuste os headers, delays e lógica de retry no arquivo `crawlmd_wrapper.py`.

---

**Nota**: Este wrapper foi desenvolvido para uso ético. Sempre obtenha permissão antes de raspar dados de websites e respeite as leis de proteção de dados aplicáveis.
