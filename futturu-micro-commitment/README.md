# Micro-Compromissos Guiados Futturu

Plugin WordPress para engajar visitantes através de sequências interativas de micro-perguntas que conduzem a CTAs relevantes.

## 🚀 Instalação Rápida

1. **Copie a pasta** `futturu-micro-commitment` para `/wp-content/plugins/`
2. **Ative o plugin** no menu Plugins do WordPress
3. **Automaticamente**, 9 perguntas e 23 CTAs serão carregados
4. Use o shortcode `[futturu_micro_engage]` em qualquer página

## ⚠️ Solução de Problemas

### "Nenhuma pergunta configurada" aparece no frontend

**Solução 1: Reative o plugin**
```
1. Vá em Plugins > Plugins Instalados
2. Desative "Micro-Compromissos Guiados Futturu"
3. Ative novamente
```

**Solução 2: Verifique se as opções foram salvas**
```sql
SELECT option_value FROM wp_options WHERE option_name IN ('fmc_questions', 'fmc_ctas');
```

**Solução 3: Limpe cache**
- Limpe cache do WordPress (se usar plugin de cache)
- Limpe cache do navegador
- Recarregue a página com Ctrl+F5

### Painel Admin não carrega ou botões não funcionam

**Verifique:**
1. Console do navegador (F12) por erros JavaScript
2. Se jQuery está carregado no admin
3. Permissões de usuário (precisa ser administrador)

**Solução:**
```
1. Vá em Configurações > Micro-Engajamento Futturu
2. Clique nas abas "Perguntas", "CTAs", etc.
3. Se não carregar, desative e ative o plugin novamente
```

### Perguntas/CTAs não salvam

**Verifique:**
1. Console do navegador por erros AJAX
2. nonce expirado (recarregue a página)
3. Permissão de usuário (manage_options)

## 📋 Estrutura do Plugin

```
futturu-micro-commitment/
├── futturu-micro-commitment.php    # Arquivo principal
├── includes/
│   ├── class-fmc-admin.php         # Painel administrativo
│   ├── class-fmc-frontend.php      # Shortcode e AJAX
│   └── class-fmc-data.php          # Banco de dados
├── assets/
│   ├── css/
│   │   ├── admin.css               # Estilos do admin
│   │   └── frontend.css            # Estilos do widget
│   └── js/
│       ├── admin.js                # Lógica do admin
│       └── frontend.js             # Lógica do widget
├── README.md                       # Este arquivo
└── EXEMPLOS_PRATICOS.md            # Exemplos detalhados
```

## 🎯 Uso Básico

### Shortcode

```php
[futturu_micro_engage]
```

Ou com parâmetros:

```php
[futturu_micro_engage style="default" show_progress="true"]
```

### No código PHP

```php
<?php echo do_shortcode('[futturu_micro_engage]'); ?>
```

## 🔧 Configuração

Acesse **Configurações > Micro-Engajamento Futturu** para:

- **Perguntas**: Adicionar/editar/remover perguntas e respostas
- **CTAs**: Configurar calls-to-action finais
- **Respostas**: Visualizar dados coletados
- **Configurações**: Ativar/desativar, rate limit, privacidade

## 📊 Dados Pré-configurados

O plugin já vem com:

- **9 perguntas** em 6 fluxos diferentes
- **23 CTAs** segmentados por tipo de cliente
- **8 fluxos de navegação** completos

Veja todos os exemplos em `EXEMPLOS_PRATICOS.md`.

## 🗄️ Banco de Dados

Tabela criada: `wp_fmc_responses`

Campos:
- `id`: ID único da resposta
- `session_id`: Sessão do usuário
- `question_id`: ID da pergunta
- `answer`: Resposta do usuário
- `user_ip`: IP (opcional, conforme configuração)
- `user_agent`: User agent do navegador
- `path_taken`: Caminho percorrido
- `created_at`: Data/hora da resposta

## 🔒 Privacidade e LGPD

- Rastreamento de IP é **desativado por padrão**
- Ative em Configurações se necessário (respeite a LGPD)
- Dados podem ser exportados em CSV pela aba "Respostas"

## 🛡️ Segurança

- Sanitização de todos os inputs
- Nonce verification em AJAX
- Rate limiting (5 submissões/minuto por IP)
- Proteção contra spam

## 📝 Changelog

### 1.0.1
- ✅ Correção: Inicialização automática de dados padrão
- ✅ Correção: Detecção melhorada da página admin
- ✅ Correção: CSS do admin com !important para tabs
- ✅ Melhoria: Tabela DB com campo user_agent
- ✅ Melhoria: Dados sempre carregados na ativação

### 1.0.0
- Lançamento inicial

## 🤝 Suporte

Para dúvidas ou problemas:

1. Verifique este README e `EXEMPLOS_PRATICOS.md`
2. Confira o console do navegador por erros
3. Desative e ative o plugin para recarregar dados
4. Contate: suporte@futturu.com.br

## 📄 Licença

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

---

**Desenvolvido por Futturu** - https://futturu.com.br
