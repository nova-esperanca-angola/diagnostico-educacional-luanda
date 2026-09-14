# 🚀 Manual de Implantação e Configuração de Cron Job no hPanel (Hostinger)

> **Fase 7 do Web Development Life Cycle (WDLC)**  
> **Projeto**: Observatório do Diagnóstico Educacional de Angola & Luanda  
> **Repositório**: `nova-esperanca-angola/diagnostico-educacional-luanda`  

---

## 1. Visão Geral da Infraestrutura na Hostinger

O **Observatório do Diagnóstico Educacional de Angola & Luanda** foi concebido com uma arquitetura **estática orientada a cache**, eliminando a necessidade de bancos de dados relacionais pesados (MySQL/PostgreSQL) para renderização do portal.

### Requisitos Mínimos da Hospedagem:
- **Plano**: Hostinger Single, Premium, Business ou Cloud Hosting.
- **Versão do PHP**: PHP 8.1, 8.2 ou 8.3 (64-bit).
- **Módulos PHP Requeridos**: `cURL`, `JSON`, `OpenSSL` (todos ativados por padrão na Hostinger).
- **Servidor Web**: Apache / LiteSpeed com suporte a `.htaccess`.

---

## 2. Estrutura de Diretórios no Servidor

Após realizar o deploy via **Git**, **SSH** ou **Gerenciador de Arquivos do hPanel**, a estrutura dentro do diretório raiz do domínio ou subdomínio (`public_html`) deve ser:

```text
/home/u123456789/domains/diagnostico.novaesperancaangola.org/public_html/
├── .env.example
├── .gitignore
├── .htaccess                   # Hardening e regras de cache 3G
├── README.md
├── index.php                   # Dashboard analítico principal
├── data/
│   ├── angola-education-summary.json         # Cache consolidado em produção
│   └── angola-education-summary.sample.json  # Fallback de contingência
├── docs/
│   ├── catalogo-indicadores.md
│   ├── arquitetura-extrator-cache.md
│   ├── design-sistema-visual.md
│   └── deploy-hostinger-cron.md
├── schemas/
│   └── angola-education-summary.schema.json
├── scripts/
│   ├── fetch-indicators.php     # Extrator automatizado (alvo da Cron Job)
│   ├── validate-schema.php
│   ├── verify-worldbank-api.php
│   ├── verify-dashboard-widget.php
│   ├── test-fault-tolerance.php
│   └── verify-deploy-readiness.php
└── views/
    ├── data-loader.php
    ├── widget-impacto.php
    └── components/
```

### Permissões de Arquivos e Pastas Recomendadas:
- Diretórios: `chmod 755` (em especial a pasta `data/` para permitir gravação atômica).
- Arquivos PHP e estáticos: `chmod 644`.
- Cache JSON gerado: `chmod 644` (aplicado automaticamente pelo `fetch-indicators.php`).

---

## 3. Configuração Passo a Passo da Cron Job no hPanel

### Passo 1: Acessar o Painel de Controle (hPanel)
1. Acesse [hpanel.hostinger.com](https://hpanel.hostinger.com/) com suas credenciais.
2. Selecione a sua conta de hospedagem e o domínio/subdomínio do Observatório.
3. No menu lateral ou na barra de busca superior, vá em **Avançado (Advanced) > Tarefas Cron (Cron Jobs)**.

---

### Passo 2: Criar a Tarefa Cron

Na seção **Gerenciar Tarefas Cron**, selecione:
- **Tipo de Tarefa**: `Personalizado` (Custom).

#### A. Identificação do Caminho Absoluto do PHP
A Hostinger disponibiliza o binário PHP padrão em:
```bash
/usr/bin/php
```
*(Caso queira forçar uma versão específica, por exemplo PHP 8.2: `/opt/alt/php82/usr/bin/php`).*

#### B. Comando Completo Recomendado
Substitua `u123456789` pelo seu identificador de usuário Hostinger e `diagnostico.novaesperancaangola.org` pelo caminho real da sua hospedagem:

```bash
/usr/bin/php /home/u123456789/domains/diagnostico.novaesperancaangola.org/public_html/scripts/fetch-indicators.php >> /home/u123456789/cron-fetch.log 2>&1
```

> **Dica de Operação**:  
> O operador `>> /home/u123456789/cron-fetch.log 2>&1` mantém um histórico auditável das execuções periódicas. Caso prefira silenciar completamente a saída, use `>/dev/null 2>&1`.

---

### Passo 3: Definir a Periodicidade

Como os dados do Banco Mundial e UNESCO são atualizados anualmente ou semestralmente, uma sincronização **mensal** é ideal para garantir integridade sem desperdício de recursos.

- **Frequência**: Uma vez por mês (Dia 1º às 03:00 UTC).
- **Expressão Cron**:
  ```text
  0 3 1 * *
  ```
  | Campo | Valor | Significado |
  |---|---|---|
  | Minuto | `0` | No minuto 0 |
  | Hora | `3` | Às 03:00 da madrugada (baixa demanda) |
  | Dia do Mês | `1` | No primeiro dia de cada mês |
  | Mês | `*` | Todo mês |
  | Dia da Semana | `*` | Qualquer dia da semana |

Clique no botão **Salvar** para registrar a tarefa.

---

## 4. Segurança e Políticas de Acesso ao Extrator

O script `scripts/fetch-indicators.php` conta com uma dupla camada de proteção:

### 4.1. Execução Nativa via CLI (Cron Job)
Quando acionado pela Tarefa Cron do hPanel, a constante `php_sapi_name()` retorna `'cli'`. O script executa de forma 100% autônoma, sem exigir nenhuma chave ou parâmetro adicional.

### 4.2. Execução Remota via Web (Webhook / Navegador)
Se por algum motivo administrativo a sincronização precisar ser disparada via URL HTTP/HTTPS (por exemplo, por um serviço de monitoramento externo ou deploy hook):
- O script **rejeita chamadas sem token** com status `HTTP 403 Forbidden`.
- É obrigatório fornecer o token secreto via parâmetro GET `?token=...` ou cabeçalho HTTP `X-Cron-Token`:
  ```bash
  curl -i "https://diagnostico.novaesperancaangola.org/scripts/fetch-indicators.php?token=cne_angola_live_cron_2026"
  ```
- O token padrão é `cne_angola_live_cron_2026`, que pode ser personalizado definindo a variável de ambiente `CRON_SECRET_TOKEN` no servidor.

---

## 5. Testes de Validação e Homologação Pré-Deploy

Antes de ativar em produção, execute a suíte de verificação de prontidão para deploy no terminal SSH ou no ambiente local:

```bash
php scripts/verify-deploy-readiness.php
```

Esta suíte valida automaticamente:
1. Execução livre e bem-sucedida via CLI.
2. Bloqueio imediato com `HTTP 403 Forbidden` para chamadas HTTP simuladas sem token.
3. Autorização com sucesso (`HTTP 200 OK`) para chamadas HTTP com token válido.
4. Presença e integridade de sintaxe do arquivo `.htaccess`.
5. Permissões de escrita e integridade atômica do cache `data/angola-education-summary.json`.

---

## 6. Guia de Resolução de Problemas (Troubleshooting)

### Problema 1: `Permission denied` ao gravar o arquivo de cache
- **Causa**: O usuário do PHP não possui permissão de escrita no diretório `data/`.
- **Solução**: Via SSH ou Gerenciador de Arquivos do hPanel, aplique permissão `755` na pasta `data/`:
  ```bash
  chmod 755 /home/u123456789/public_html/data
  ```

### Problema 2: A Cron falha com `Could not open input file`
- **Causa**: Caminho relativo ou incorreto na configuração da Cron.
- **Solução**: Sempre utilize o caminho absoluto completo iniciando em `/home/u...`. Para descobrir o caminho exato do seu servidor, acesse o painel **Informações da Conta** no hPanel ou execute `pwd` via SSH.

### Problema 3: cURL timeout / Erro 28
- **Causa**: Bloqueio de conexões de saída na porta 443 pela Hostinger ou instabilidade na API do Banco Mundial.
- **Solução**: O extrator possui fallback resiliente (`angola-education-summary.sample.json`) e gravação atômica (`rename()`), garantindo que o dashboard nunca fique fora do ar mesmo durante indisponibilidades da rede externa.
