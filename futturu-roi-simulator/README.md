# Simulador de Economia com Suporte Futturu

Plugin WordPress para simulação de ROI (Retorno sobre Investimento) que demonstra a economia de custos e tempo ao delegar a manutenção, segurança e suporte técnico do website para a Futturu.

## Funcionalidades

- **Simulador Interativo**: Campos para coleta de informações do usuário (perfil, situação atual, custos)
- **Cálculo em Tempo Real**: Comparação entre custo atual vs. custo com suporte Futturu
- **Resultados Visuais**: Exibição clara da economia anual estimada (R$ e %) e horas liberadas
- **Formulário de Contato**: Captura de leads após a simulação com envio por e-mail
- **Painel Administrativo**: Configuração de valores, percentuais e textos
- **Responsivo**: Funciona em qualquer tema WordPress via shortcode
- **Internacionalização**: Pronto para tradução (pt-BR)

## Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- jQuery (incluído no WordPress)

## Instalação

1. Faça o upload da pasta `futturu-roi-simulator` para o diretório `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Configure as opções em **Configurações > Simulador ROI Futturu**
4. Use o shortcode `[futturu_roi_sim]` em qualquer página ou post

## Shortcode

```
[futturu_roi_sim]
```

Use este shortcode em qualquer página, post ou widget para exibir o simulador.

## Configuração

Acesse **Configurações > Simulador ROI Futturu** no painel administrativo para configurar:

### Parâmetros Principais

- **Custo Anual do Plano (R$)**: Valor anual da hospedagem cloud gerenciada + suporte técnico (padrão: R$ 2.500,00)
- **Percentual de Tempo Residual (%)**: Percentual do tempo que o cliente ainda dedicará com o suporte gerenciado (padrão: 20%)
- **E-mail de Destino**: E-mail que receberá as simulações (padrão: suporte@futturu.com.br)

### Textos Personalizáveis

- **Texto Explicativo do Cálculo**: Mensagem de transparência sobre o cálculo estimativo
- **Texto de Benefícios**: Descrição dos benefícios do suporte gerenciado
- **Mensagem de Sucesso**: Mensagem exibida após envio do formulário

## Como Funciona o Cálculo

### Custo Atual
```
Custo Mensal de Tempo = Horas/Mês × Valor/Hora
Custo Anual de Tempo = Custo Mensal de Tempo × 12
Custo Total Anual Atual = Custo Anual de Tempo + Custos de Hospedagem/Terceiros
```

### Custo com Futturu
```
Horas Residuais/Mês = Horas/Mês × (Percentual Residual / 100)
Custo Residual Mensal = Horas Residuais/Mês × Valor/Hora
Custo Residual Anual = Custo Residual Mensal × 12
Custo Total Anual Futturu = Custo do Plano Anual + Custo Residual Anual
```

### Economia
```
Economia Anual = Custo Total Anual Atual - Custo Total Anual Futturu
Percentual de Economia = (Economia Anual / Custo Total Anual Atual) × 100
Horas Liberadas/Mês = Horas/Mês - Horas Residuais/Mês
```

## Estrutura de Arquivos

```
futturu-roi-simulator/
├── futturu-roi-simulator.php    # Arquivo principal do plugin
├── assets/
│   ├── css/
│   │   └── futturu-roi.css      # Estilos do simulador
│   └── js/
│       └── futturu-roi.js       # JavaScript do frontend
└── languages/                    # Arquivos de tradução (opcional)
```

## Campos do Simulador

### Perfil do Usuário
- Setor de Atuação (Dropdown)
- Tamanho da Empresa (Dropdown)
- Nível de Experiência Técnica (Dropdown)

### Situação Atual
- Quantidade de Websites Gerenciados (Input Numérico)
- Horas/Mês em Manutenção (Slider 0-80 horas)
- Valor/Hora do Tempo (R$)
- Custo Anual com Hospedagem/Freelancers (Opcional)

### Formulário de Contato (após cálculo)
- Nome Completo *
- E-mail *
- Telefone/WhatsApp *
- Empresa (opcional)
- Mensagem (opcional)

## Segurança

- Validação de formulário no frontend e backend
- Sanitização de todos os dados de entrada
- Nonce verification para requisições AJAX
- Escaping de output para prevenir XSS
- Proteção contra acesso direto aos arquivos

## Personalização

### CSS Personalizado

O plugin usa classes com prefixo `futturu-roi-` para evitar conflitos. Você pode adicionar CSS personalizado no seu tema:

```css
.futturu-roi-simulator {
    /* Suas personalizações */
}
```

### Cores da Marca

Edite o arquivo `assets/css/futturu-roi.css` para ajustar as cores conforme a identidade visual da Futturu.

## Envio de E-mail

Os dados da simulação são enviados por e-mail usando a função `wp_mail()` do WordPress. Certifique-se de que seu servidor esteja configurado para envio de e-mails ou use um plugin de SMTP.

### Dados Incluídos no E-mail

- Informações do cliente (nome, e-mail, telefone, empresa)
- Resultados da simulação (custo atual, custo Futturu, economia, horas liberadas)
- Mensagem personalizada do cliente

## Troubleshooting

### O simulador não aparece
- Verifique se o shortcode `[futturu_roi_sim]` está correto
- Confirme se o plugin está ativo
- Limpe o cache do navegador e do WordPress

### E-mails não são enviados
- Verifique a configuração de e-mail do seu servidor
- Teste com um plugin de SMTP
- Verifique se o e-mail de destino está correto nas configurações

### Conflitos com o tema
- O plugin usa CSS isolado com prefixos específicos
- Se houver conflitos, use CSS mais específico no seu tema

## Licença

GPL v2 or later

## Suporte

Para suporte técnico ou dúvidas, entre em contato:
- E-mail: suporte@futturu.com.br
- Website: https://futturu.com.br

## Changelog

### Versão 1.0.0
- Lançamento inicial do plugin
- Simulador de ROI completo
- Painel administrativo de configurações
- Formulário de contato com envio por e-mail
- Design responsivo e moderno
