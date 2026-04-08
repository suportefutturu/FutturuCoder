# 📋 Exemplos Práticos - Micro-Compromissos Futturu

Este documento detalha todos os exemplos práticos de **Perguntas**, **CTAs**, **Respostas** e **Configurações** que vêm pré-configurados no plugin após ativação.

---

## 🎯 PERGUNTAS CONFIGURADAS (9 perguntas em 5 fluxos)

### FLUXO 1: Objetivo Principal (Pergunta de Entrada)

**ID:** `q1_objetivo`  
**Texto:** "Qual o principal objetivo do seu website hoje?"

| Opção de Resposta | Próximo Passo |
|---|---|
| Gerar mais vendas online | → `q2_vendas` |
| Aumentar a visibilidade da marca | → `q2_branding` |
| Captar leads qualificados | → `q2_leads` |
| Melhorar o atendimento ao cliente | → `q2_atendimento` |
| Não tenho um site ainda | → CTA `cta_sem_site` |
| Outro objetivo | → `q2_outro` |

---

### FLUXO 2: Vendas

**ID:** `q2_vendas`  
**Texto:** "Seu site atual converte bem os visitantes em clientes?"

| Opção de Resposta | Próximo Passo |
|---|---|
| Sim, mas quero melhorar ainda mais | → CTA `cta_otimizacao_vendas` |
| Não, a conversão está baixa | → CTA `cta_auditoria_conversao` |
| Não tenho site ou é muito antigo | → CTA `cta_site_novo_vendas` |

---

### FLUXO 3: Branding (2 perguntas)

#### Pergunta 3a
**ID:** `q2_branding`  
**Texto:** "Você já possui identidade visual definida (logo, cores, tipografia)?"

| Opção de Resposta | Próximo Passo |
|---|---|
| Sim, está tudo definido | → `q3_branding_existente` |
| Parcialmente, mas precisa de ajustes | → CTA `cta_refresh_branding` |
| Não, preciso criar do zero | → CTA `cta_branding_completo` |

#### Pergunta 3b (subsequente)
**ID:** `q3_branding_existente`  
**Texto:** "Sua marca está presente de forma consistente em todos os canais digitais?"

| Opção de Resposta | Próximo Passo |
|---|---|
| Sim, mas quero fortalecer ainda mais | → CTA `cta_posicionamento_digital` |
| Não, está inconsistente | → CTA `cta_unificacao_marca` |

---

### FLUXO 4: Leads (3 perguntas)

#### Pergunta 4a
**ID:** `q2_leads`  
**Texto:** "Qual tipo de lead você busca captar?"

| Opção de Resposta | Próximo Passo |
|---|---|
| Empresas (B2B) | → `q3_leads_b2b` |
| Consumidores finais (B2C) | → `q3_leads_b2c` |
| Ambos | → CTA `cta_estrategia_mista` |

#### Pergunta 4b (B2B)
**ID:** `q3_leads_b2b`  
**Texto:** "Qual o ticket médio dos seus clientes B2B?"

| Opção de Resposta | Próximo Passo |
|---|---|
| Até R$ 5.000 | → CTA `cta_leads_b2b_pequeno` |
| De R$ 5.000 a R$ 50.000 | → CTA `cta_leads_b2b_medio` |
| Acima de R$ 50.000 | → CTA `cta_leads_b2b_enterprise` |

#### Pergunta 4c (B2C)
**ID:** `q3_leads_b2c`  
**Texto:** "Como você atrai consumidores atualmente?"

| Opção de Resposta | Próximo Passo |
|---|---|
| Tráfego pago (Google/Facebook Ads) | → CTA `cta_otimizacao_trafego` |
| Orgânico (SEO/Redes Sociais) | → CTA `cta_aceleracao_organico` |
| Indicação/Boca a boca | → CTA `cta_escalagem_indicacao` |
| Não estou atraindo leads | → CTA `cta_estrategia_do_zero` |

---

### FLUXO 5: Atendimento

**ID:** `q2_atendimento`  
**Texto:** "Qual canal de atendimento você quer priorizar no site?"

| Opção de Resposta | Próximo Passo |
|---|---|
| WhatsApp/Chat | → CTA `cta_integracao_whatsapp` |
| Formulários de contato | → CTA `cta_formularios_inteligentes` |
| FAQ automático/Chatbot | → CTA `cta_chatbot_inteligente` |
| Todos integrados | → CTA `cta_omnichannel` |

---

### FLUXO 6: Outro

**ID:** `q2_outro`  
**Texto:** "Conte-nos mais sobre seu projeto ou necessidade específica:"

| Opção de Resposta | Próximo Passo |
|---|---|
| Quero falar com um especialista agora | → CTA `cta_especialista` |
| Preciso de um orçamento personalizado | → CTA `cta_orcamento` |
| Quero ver casos de sucesso primeiro | → CTA `cta_cases_sucesso` |

---

## 📣 CTAs CONFIGURADOS (23 CTAs)

### 🏗️ Para quem não tem site (2 CTAs)

| ID | Título | Descrição | Botão | Link |
|---|---|---|---|---|
| `cta_sem_site` | Comece com um website profissional de alta performance | Desenvolvemos websites institucionais e de e-commerce focados em resultados para PMEs. Do planejamento à entrega, cuidamos de tudo. | Solicite um orçamento gratuito | `/contato#orcamento-site` |
| `cta_site_novo_vendas` | Crie um site focado em conversão desde o primeiro dia | Desenvolvemos websites otimizados para vendas, com UX estratégica e integração completa com suas ferramentas de negócio. | Quero um site que vende | `/solucoes/websites-vendas` |

### 📈 Otimização de Vendas (2 CTAs)

| ID | Título | Descrição | Botão | Link |
|---|---|---|---|---|
| `cta_otimizacao_vendas` | Leve suas vendas online ao próximo nível | Mesmo sites que convertem bem podem melhorar. Nossa consultoria identifica oportunidades ocultas de crescimento. | Agende uma consultoria estratégica | `/consultoria/conversao` |
| `cta_auditoria_conversao` | Descubra por que seu site não converte | Receba uma auditoria completa de UX, performance e jornada do cliente. Identificamos os gargalos e propomos soluções práticas. | Solicite sua auditoria gratuita | `/auditoria-gratuita` |

### 🎨 Branding (4 CTAs)

| ID | Título | Descrição | Botão | Link |
|---|---|---|---|---|
| `cta_refresh_branding` | Atualize sua identidade visual para a era digital | Modernizamos sua marca mantendo a essência. Criamos sistemas visuais flexíveis para todos os canais digitais. | Conheça nosso processo de refresh | `/servicos/refresh-branding` |
| `cta_branding_completo` | Construa uma marca memorável do zero | Criamos identidades visuais completas que transmitem o valor do seu negócio e conectam emocionalmente com seu público. | Veja nossos pacotes de branding | `/servicos/branding-completo` |
| `cta_posicionamento_digital` | Fortaleça o posicionamento da sua marca online | Estratégias de conteúdo, SEO e presença digital para tornar sua marca referência no segmento. | Fale com nosso time de estratégia | `/estrategia/posicionamento` |
| `cta_unificacao_marca` | Unifique sua marca em todos os canais | Criamos guidelines completos e implementamos consistência visual em website, redes sociais e materiais corporativos. | Quero unificar minha marca | `/servicos/unificacao-marca` |

### 💼 Leads B2B (3 CTAs)

| ID | Título | Descrição | Botão | Link |
|---|---|---|---|---|
| `cta_leads_b2b_pequeno` | Gere leads B2B qualificados mesmo com ticket baixo | Estratégias específicas para volumes maiores de leads com processos de nutrição automatizados. | Veja como funcionam nossas campanhas B2B | `/cases/b2b-pequeno-ticket` |
| `cta_leads_b2b_medio` | Escale sua geração de leads B2B com previsibilidade | Combinamos inbound marketing, conteúdo estratégico e automação para criar um funil constante de oportunidades. | Agende uma análise do seu funil | `/consultoria/funnel-b2b` |
| `cta_leads_b2b_enterprise` | Gere oportunidades enterprise de alto valor | Estratégias account-based marketing (ABM) para engajar decisores e fechar contratos de grande porte. | Conheça nossa metodologia ABM | `/servicos/abm-enterprise` |

### 🛒 Leads B2C (5 CTAs)

| ID | Título | Descrição | Botão | Link |
|---|---|---|---|---|
| `cta_otimizacao_trafego` | Reduza o custo por lead e aumente o ROI dos anúncios | Otimizamos suas campanhas atuais com foco em conversão. Melhoramos qualidade do tráfego e experiência de landing pages. | Solicite uma análise de campanhas | `/trafego-pago/otimizacao` |
| `cta_aceleracao_organico` | Escale seus resultados orgânicos com SEO estratégico | Posicionamos seu site nas primeiras posições do Google para termos relevantes do seu negócio. | Veja nosso plano de SEO | `/seo/aceleracao` |
| `cta_escalagem_indicacao` | Transforme indicações em um sistema previsível | Criamos programas estruturados de indicação com incentivos e automação para multiplicar seus leads qualificados. | Quero um programa de indicações | `/estrategia/indicacao-premium` |
| `cta_estrategia_do_zero` | Construa sua máquina de geração de leads do zero | Desenvolvemos uma estratégia completa: atração, conversão e nutrição. Você foca em vender, nós trazemos os leads. | Quero começar agora | `/consultoria/estrategia-completa` |
| `cta_estrategia_mista` | Estratégia integrada B2B + B2C para máximo resultado | Segmentamos mensagens e canais para atender ambos os públicos sem diluir esforços ou orçamento. | Fale com um estrategista | `/consultoria/estrategia-mista` |

### 💬 Atendimento (4 CTAs)

| ID | Título | Descrição | Botão | Link |
|---|---|---|---|---|
| `cta_integracao_whatsapp` | Integre WhatsApp profissional ao seu site | Botões inteligentes, mensagens automáticas e roteamento para o setor correto. Aumente o engajamento em tempo real. | Quero integrar WhatsApp | `/integracoes/whatsapp-business` |
| `cta_formularios_inteligentes` | Formulários que realmente convertem | Desenvolvemos formulários estratégicos com validação, segmentação e integração direta com seu CRM. | Otimize meus formulários | `/ux/formularios-conversao` |
| `cta_chatbot_inteligente` | Automate 80% do atendimento com IA | Chatbots inteligentes que qualificam leads, respondem dúvidas frequentes e agendam reuniões automaticamente. | Conheça nossos chatbots | `/automacao/chatbot-ia` |
| `cta_omnichannel` | Centralize todos os canais de atendimento | Integramos WhatsApp, chat, formulário, telefone e redes sociais em uma única plataforma de gestão. | Quero atendimento omnichannel | `/plataforma/omnichannel` |

### 🔹 Gerais (3 CTAs)

| ID | Título | Descrição | Botão | Link |
|---|---|---|---|---|
| `cta_especialista` | Fale diretamente com um especialista Futturu | Nossos consultores seniores estão prontos para entender sua necessidade específica e propor a melhor solução. | Agende uma conversa de 15 minutos | `/contato/especialista` |
| `cta_orcamento` | Receba um orçamento personalizado em até 24h | Analisamos seu projeto e enviamos uma proposta detalhada com escopo, prazos e investimento. | Solicitar orçamento agora | `/contato/orcamento` |
| `cta_cases_sucesso` | Conheça cases reais de transformação digital | Veja como ajudamos empresas similares à sua a alcançar resultados extraordinários. | Ver cases de sucesso | `/cases` |

---

## 🔄 FLUXOS DE NAVEGAÇÃO COMPLETOS (Exemplos Práticos)

### Exemplo 1: Visitante quer aumentar vendas
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Gerar mais vendas online"
   ↓
PERGUNTA 2: "Seu site atual converte bem os visitantes em clientes?"
   ↓
RESPOSTA: "Não, a conversão está baixa"
   ↓
CTA FINAL: "Descubra por que seu site não converte"
         → "Receba uma auditoria completa de UX, performance e jornada do cliente."
         → Botão: "Solicite sua auditoria gratuita"
         → Link: /auditoria-gratuita
```

### Exemplo 2: Empresa sem website
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Não tenho um site ainda"
   ↓
CTA FINAL: "Comece com um website profissional de alta performance"
         → "Desenvolvemos websites institucionais e de e-commerce focados em resultados."
         → Botão: "Solicite um orçamento gratuito"
         → Link: /contato#orcamento-site
```

### Exemplo 3: Lead B2B Enterprise (fluxo de 3 perguntas)
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Captar leads qualificados"
   ↓
PERGUNTA 2: "Qual tipo de lead você busca captar?"
   ↓
RESPOSTA: "Empresas (B2B)"
   ↓
PERGUNTA 3: "Qual o ticket médio dos seus clientes B2B?"
   ↓
RESPOSTA: "Acima de R$ 50.000"
   ↓
CTA FINAL: "Gere oportunidades enterprise de alto valor"
         → "Estratégias account-based marketing (ABM) para engajar decisores."
         → Botão: "Conheça nossa metodologia ABM"
         → Link: /servicos/abm-enterprise
```

### Exemplo 4: Branding do zero (fluxo de 2 perguntas)
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Aumentar a visibilidade da marca"
   ↓
PERGUNTA 2: "Você já possui identidade visual definida (logo, cores, tipografia)?"
   ↓
RESPOSTA: "Não, preciso criar do zero"
   ↓
CTA FINAL: "Construa uma marca memorável do zero"
         → "Criamos identidades visuais completas que transmitem o valor do seu negócio."
         → Botão: "Veja nossos pacotes de branding"
         → Link: /servicos/branding-completo
```

### Exemplo 5: Branding com inconsistência (fluxo de 3 perguntas)
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Aumentar a visibilidade da marca"
   ↓
PERGUNTA 2: "Você já possui identidade visual definida?"
   ↓
RESPOSTA: "Sim, está tudo definido"
   ↓
PERGUNTA 3: "Sua marca está presente de forma consistente em todos os canais digitais?"
   ↓
RESPOSTA: "Não, está inconsistente"
   ↓
CTA FINAL: "Unifique sua marca em todos os canais"
         → "Criamos guidelines completos e implementamos consistência visual."
         → Botão: "Quero unificar minha marca"
         → Link: /servicos/unificacao-marca
```

### Exemplo 6: Lead B2C com tráfego pago
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Captar leads qualificados"
   ↓
PERGUNTA 2: "Qual tipo de lead você busca captar?"
   ↓
RESPOSTA: "Consumidores finais (B2C)"
   ↓
PERGUNTA 3: "Como você atrai consumidores atualmente?"
   ↓
RESPOSTA: "Tráfego pago (Google/Facebook Ads)"
   ↓
CTA FINAL: "Reduza o custo por lead e aumente o ROI dos anúncios"
         → "Otimizamos suas campanhas atuais com foco em conversão."
         → Botão: "Solicite uma análise de campanhas"
         → Link: /trafego-pago/otimizacao
```

### Exemplo 7: Omnichannel
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Melhorar o atendimento ao cliente"
   ↓
PERGUNTA 2: "Qual canal de atendimento você quer priorizar no site?"
   ↓
RESPOSTA: "Todos integrados"
   ↓
CTA FINAL: "Centralize todos os canais de atendimento"
         → "Integramos WhatsApp, chat, formulário, telefone e redes sociais."
         → Botão: "Quero atendimento omnichannel"
         → Link: /plataforma/omnichannel
```

### Exemplo 8: Especialista (fluxo direto)
```
PERGUNTA 1: "Qual o principal objetivo do seu website hoje?"
   ↓
RESPOSTA: "Outro objetivo"
   ↓
PERGUNTA 2: "Conte-nos mais sobre seu projeto ou necessidade específica:"
   ↓
RESPOSTA: "Quero falar com um especialista agora"
   ↓
CTA FINAL: "Fale diretamente com um especialista Futturu"
         → "Nossos consultores seniores estão prontos para entender sua necessidade."
         → Botão: "Agende uma conversa de 15 minutos"
         → Link: /contato/especialista
```

---

## ⚙️ CONFIGURAÇÕES PADRÃO

### Configurações Gerais (após ativação)

| Configuração | Valor Padrão | Descrição |
|---|---|---|
| **Plugin Ativo** | ✅ SIM | Habilita exibição no frontend |
| **Rastrear IP** | ❌ NÃO | Armazena IP do usuário (LGPD - desativado por padrão) |
| **Rate Limit** | 5 submissões/min | Máximo de respostas por minuto por IP |
| **Shortcode** | `[futturu_micro_engage]` | Use em qualquer página/post |

### Onde Acessar as Configurações

1. No WordPress Admin, vá em **Configurações** → **Micro-Engajamento Futturu**
2. Clique na aba **Configurações** (última aba)
3. Ajuste conforme necessário e clique em **Salvar Configurações**

---

## 📊 RESUMO DA CONFIGURAÇÃO INICIAL

| Item | Quantidade |
|---|---|
| **Perguntas Totais** | 9 perguntas |
| **Fluxos Temáticos** | 5 fluxos (Vendas, Branding, Leads, Atendimento, Outro) |
| **CTAs Configurados** | 23 CTAs |
| **Categorias de CTA** | 7 categorias |
| **Profundidade Máxima** | 3 perguntas (fluxo Leads B2B/B2C) |
| **Profundidade Mínima** | 1 pergunta (resposta direta para CTA) |

---

## 🎯 COMO PERSONALIZAR

### Adicionar Nova Pergunta

1. Acesse **Configurações** → **Micro-Engajamento Futturu**
2. Clique na aba **Perguntas**
3. Clique em **+ Adicionar Pergunta**
4. Preencha:
   - ID único (ex: `minha_pergunta`)
   - Texto da pergunta
   - Opções de resposta
   - Para cada resposta: selecione próxima pergunta OU CTA
5. Clique em **Salvar Perguntas**

### Adicionar Novo CTA

1. Acesse a aba **CTAs**
2. Clique em **+ Adicionar CTA**
3. Preencha:
   - ID único (ex: `meu_cta`)
   - Título persuasivo
   - Descrição clara
   - Texto do botão
   - Link de destino
   - Tipo (link ou modal)
4. Clique em **Salvar CTAs**

### Editar Links dos CTAs

⚠️ **Importante:** Os links configurados nos exemplos usam caminhos relativos (`/contato`, `/servicos`, etc). Substitua pelos URLs reais do seu site:

- `/contato#orcamento-site` → `https://seusite.com.br/contato`
- `/auditoria-gratuita` → `https://seusite.com.br/auditoria`
- `/cases` → `https://seusite.com.br/portfolio`

---

## 📝 NOTAS IMPORTANTES

1. **IDs Únicos**: Cada pergunta e CTA deve ter um ID único no sistema
2. **Referências Cruzadas**: Ao criar respostas, certifique-se de que o ID da próxima pergunta ou CTA existe
3. **CTAs Órfãos**: Um CTA só será exibido se estiver vinculado a pelo menos uma resposta
4. **Perguntas Órfãs**: Evite criar perguntas que não sejam alcançáveis através de nenhuma resposta
5. **Teste os Fluxos**: Após configurar, teste todos os caminhos possíveis no frontend

---

**Documentação criada para facilitar a configuração inicial do plugin Micro-Compromissos Guiados Futturu.**
