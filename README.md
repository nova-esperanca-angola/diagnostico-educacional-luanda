# 📊 Observatório do Diagnóstico Educacional de Angola & Luanda

> Pipeline automatizado em PHP CLI para extração de séries temporais oficiais (**World Bank Open Data API** & **UNESCO UIS**), compilador de dados em cache estático JSON e dashboard de visualização de impacto educacional para o **Centro Educacional Nova Esperança** (Luanda, Angola).

---

## 🎯 Objetivo do Projeto

Com mais de **44% da população composta por crianças (0 a 14 anos)** e um **déficit na educação pré-escolar superior a 60%**, Angola enfrenta um gargalo histórico na primeira infância. Na Província de Luanda, onde vivem mais de 9,8 milhões de pessoas, a escassez de vagas públicas na educação infantil torna centros comunitários e filantrópicos indispensáveis.

Este projeto provê uma base técnica auditável com dados oficiais do **Banco Mundial**, **UNESCO**, **INE Angola** e **UNICEF**, fornecendo:
1. **Pipeline de Dados Autônomo**: Extração periódica via Cron Job sem custos de banco de dados SQL.
2. **Cache Estático de Alta Performance**: Armazenamento em arquivo JSON pré-compilado, garantindo tempo de resposta instantâneo e baixo consumo de banda 3G.
3. **Dashboard Analítico Completo (`index.php`)**: Interface pública com Big Numbers, gráficos comparativos SVG e contextualização de Luanda (< 20 KB).
4. **Widget Embutível (`views/widget-impacto.php`)**: Componente modular em PHP e iframe para exibição em portais institucionais e blogs parceiros (< 10 KB).

---

## 🗺️ Ciclo de Vida do Desenvolvimento Web (WDLC)

| Fase | Issue | Descrição | Status |
|---|---|---|---|
| **WDLC 1** | [#1](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/1) | Catalogação das Séries Temporais de Educação (World Bank & UNESCO) | ✅ **Concluída** |
| **WDLC 2** | [#2](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/2) | Arquitetura do Extrator PHP e Especificação do Cache Estático JSON | ✅ **Concluída** |
| **WDLC 3** | [#3](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/3) | Design de Gráficos Comparativos de Baixo Consumo de Dados (Google Stitch) | ✅ **Concluída** |
| **WDLC 4** | [#4](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/4) | Construção do Script PHP Extrator e Sanitizador (`fetch-indicators.php`) | ✅ **Concluída** |
| **WDLC 5** | [#5](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/5) | Desenvolvimento do Dashboard Analítico e Widget PHP Embutível | ✅ **Concluída** |
| **WDLC 6** | [#6](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/6) | **Validação de Dados, Checagem de Tolerância a Falhas e Testes de Conexão** | ✅ **Concluída** |
| **WDLC 7** | [#7](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/7) | Configuração de Cron Job no hPanel da Hostinger para Atualização Periódica | ⏳ A Iniciar |

---

## 🎨 Protótipos no Google Stitch (StitchMCP)

O observatório e o widget foram concebidos e integrados no Google Stitch (Projeto ID: `1164742190233504273`):
- **Desktop Dashboard**: `projects/1164742190233504273/screens/955d2de65e204e91b5d7b039ab9e1bb7`
- **Mobile / Compact Widget**: `projects/1164742190233504273/screens/f27f2f4e0cd446249637cb2ba3668e01`

---

## 📑 Documentação Técnica & Especificações

- 📖 **[Catálogo Completo de Indicadores](docs/catalogo-indicadores.md)**: Detalhamento dos 6 endpoints multilaterais homologados, histórico de dados, benchmarks da UNESCO e contextualização de Luanda (INE/UNICEF).
- 📐 **[Arquitetura do Extrator & Cache Estático](docs/arquitetura-extrator-cache.md)**: Diagramas de sequência, fluxo da gravação atômica (`rename()` do SO), desacoplamento do runtime web e política de fail-safe.
- 🎨 **[Manual do Design System & Gráficos SVG](docs/design-sistema-visual.md)**: Especificação visual, paleta semântica, integração Google Stitch e diretrizes de baixo consumo de dados.
- 📋 **[Contrato Formal JSON Schema](schemas/angola-education-summary.schema.json)**: Schema formal (Draft-07) do arquivo consolidado de dados.
- 📦 **[Cache Consolidado em Produção](data/angola-education-summary.json)**: Dados atualizados e sanitizados em tempo real pelo pipeline cURL.

---

## 🚀 Execução Local, Testes & Visualização

### 1. Iniciar o Dashboard Analítico no Navegador:
```bash
php -S 127.0.0.1:8000
# Acesse no navegador:
# - Dashboard Completo: http://127.0.0.1:8000/index.php
# - Widget Standalone : http://127.0.0.1:8000/views/widget-impacto.php
```

### 2. Como Incorporar o Widget no Site da Escola:

#### Opção A: Inclusão PHP Nativa (Recomendado para servidores PHP)
```php
<?php
// Inclui o widget diretamente no template ou página de apresentação
include_once __DIR__ . '/views/widget-impacto.php';
?>
```

#### Opção B: Incorporação via Iframe HTML (Para qualquer CMS ou site externo)
```html
<iframe 
  src="https://diagnostico.novaesperancaangola.org/views/widget-impacto.php" 
  width="100%" 
  height="480" 
  frameborder="0" 
  style="border-radius:12px;border:1px solid #e2e8f0;max-width:440px;display:block;">
</iframe>
```

---

## 🧪 Bateria de Testes Automatizados

### Tolerância a Falhas e QA (WDLC 6):
```bash
php scripts/test-fault-tolerance.php
```

### Auditoria do Dashboard e Widget (WDLC 5):
```bash
php scripts/verify-dashboard-widget.php
```

### Extração Live do Pipeline (WDLC 4):
```bash
php scripts/fetch-indicators.php
```

### Validação do Schema JSON (WDLC 2):
```bash
php scripts/validate-schema.php data/angola-education-summary.json
```

### Auditoria de Conexão com a World Bank API (WDLC 1):
```bash
php scripts/verify-worldbank-api.php
```

### Auditoria de Orçamento de Dados 3G (< 20KB) (WDLC 3):
```bash
php scripts/verify-asset-budget.php
```

---

## 📊 Orçamento de Dados & Desempenho 3G

| Componente | Teto Máximo | Peso Real | Desempenho |
|---|---|---|---|
| **Dashboard Completo (`index.php`)** | 20.0 KB | **19.72 KB** | ✅ **Conforme (Abaixo do Teto)** |
| **Widget Modular (`views/widget-impacto.php`)** | 10.0 KB | **7.92 KB** | ✅ **Excelente** |
| **Dependências JavaScript Externas** | 0 KB | **0 KB** | ✅ **Zero Overhead (100% SVG)** |
