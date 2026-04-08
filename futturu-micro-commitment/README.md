# Micro-Compromissos Guiados Futturu

Plugin WordPress para engajar visitantes através de uma sequência interativa de micro-perguntas que conduzem a CTAs relevantes.

## Características

- **Sem Gamificação**: Foco total em conversão de leads, sem pontos, placares ou elementos de jogo
- **Sequência Inteligente**: Árvore de decisões baseada nas respostas do usuário
- **CTAs Personalizados**: Call-to-actions altamente relevantes baseados no perfil construído
- **Design Limpo**: Interface moderna que se integra a qualquer tema WordPress
- **LGPD Compliance**: Opção de não rastrear IP dos usuários
- **Proteção Anti-Spam**: Rate limiting configurável

## Instalação

1. Faça upload da pasta `futturu-micro-commitment` para o diretório `/wp-content/plugins/`
2. Ative o plugin através do menu "Plugins" no WordPress
3. Acesse **Configurações > Micro-Engajamento Futturu** para configurar

## Uso

### Shortcode

Use o shortcode em qualquer página ou post:

```
[futturu_micro_engage]
```

### Parâmetros Opcionais

```
[futturu_micro_engage style="default" show_progress="true"]
```

- `style`: Estilo visual (padrão: "default")
- `show_progress`: Exibir barra de progresso (padrão: "true")

## Configuração

### 1. Perguntas

Acesse a aba **Perguntas** para:

- Adicionar/editar perguntas
- Definir opções de resposta
- Associar cada resposta a uma próxima pergunta ou CTA final

**Exemplo de Fluxo:**

```
Pergunta 1: "Qual o principal objetivo do seu website hoje?"
  ├─ "Gerar mais vendas" → Pergunta 2
  ├─ "Não tenho um site ainda" → CTA: Website Profissional
  └─ "Outro" → CTA: Falar com Especialista

Pergunta 2: "Seu site atual converte bem?"
  ├─ "Sim" → CTA: Otimização
  └─ "Não" → CTA: Auditoria de Conversão
```

### 2. CTAs

Na aba **CTAs**, configure:

- Título persuasivo
- Descrição clara do valor oferecido
- Texto do botão
- Link de destino
- Tipo (link externo ou modal)

### 3. Respostas

Visualize as respostas coletadas:

- Data e hora de cada resposta
- Session ID do usuário
- Caminho percorrido (sequência de respostas)
- IP (se habilitado)

### 4. Configurações Gerais

- **Ativar Plugin**: Habilita/desabilita o widget no frontend
- **Rastrear IP**: Armazena IP do usuário (considere a LGPD)
- **Rate Limit**: Máximo de submissões por minuto por IP

## Estrutura de Arquivos

```
futturu-micro-commitment/
├── futturu-micro-commitment.php    # Arquivo principal do plugin
├── includes/
│   ├── class-fmc-admin.php         # Lógica do painel administrativo
│   ├── class-fmc-frontend.php      # Lógica do frontend e shortcode
│   └── class-fmc-data.php          # Manipulação de dados e banco
├── assets/
│   ├── css/
│   │   ├── frontend.css            # Estilos do widget
│   │   └── admin.css               # Estilos do admin
│   └── js/
│       ├── frontend.js             # JavaScript do widget
│       └── admin.js                # JavaScript do admin
└── README.md                       # Este arquivo
```

## Banco de Dados

O plugin cria a tabela `wp_fmc_responses` com os seguintes campos:

- `id`: ID único da resposta
- `session_id`: Identificador da sessão do usuário
- `question_id`: ID da pergunta respondida
- `answer`: Resposta do usuário
- `path_taken`: Caminho percorrido até esta resposta
- `user_ip`: IP do usuário (opcional)
- `user_agent`: User agent do navegador
- `created_at`: Data/hora da resposta

## Personalização

### CSS Personalizado

O plugin usa classes prefixadas com `.fmc-`. Você pode sobrescrever os estilos no seu tema:

```css
.fmc-widget {
    /* Seu CSS personalizado */
}
```

### Novas Perguntas via Código

Você pode adicionar perguntas programaticamente:

```php
$questions = get_option('fmc_questions', array());
$questions[] = array(
    'id' => 'nova_pergunta',
    'question' => 'Sua pergunta aqui?',
    'answers' => array(
        array(
            'text' => 'Resposta 1',
            'cta' => 'cta_id_destino'
        )
    )
);
update_option('fmc_questions', $questions);
```

## Boas Práticas

1. **Mantenha o fluxo curto**: 2-4 perguntas são suficientes
2. **Perguntas relevantes**: Cada pergunta deve agregar valor
3. **CTAs claros**: Seja específico sobre o que o usuário receberá
4. **Teste os fluxos**: Verifique todos os caminhos possíveis
5. **Respeite a privacidade**: Considere desativar o rastreamento de IP

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- jQuery (incluído no WordPress)

## Licença

GPL v2 ou posterior

## Suporte

Para dúvidas ou sugestões, visite: https://futturu.com.br

---

**Futturu** - Transformando visitantes em leads qualificados
