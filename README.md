# 📊 Observatório do Diagnóstico Educacional de Angola & Luanda

> Pipeline automatizado em PHP CLI para extração de séries temporais oficiais (**World Bank Open Data API** & **UNESCO UIS**), compilador de dados em cache estático JSON e dashboard de visualização de impacto educacional para o **Centro Educacional Nova Esperança** (Luanda, Angola).

---

## 🎯 Objetivo do Projeto

Com mais de **44% da população composta por crianças (0 a 14 anos)** e um **déficit na educação pré-escolar superior a 60%**, Angola enfrenta um gargalo histórico na primeira infância. Na Província de Luanda, onde vivem mais de 8,5 milhões de pessoas, a escassez de vagas públicas na educação infantil torna centros comunitários e filantrópicos indispensáveis.

Este projeto provê uma base técnica auditável com dados oficiais do **Banco Mundial**, **UNESCO**, **INE Angola** e **UNICEF**, fornecendo:
1. **Pipeline de Dados Autônomo**: Extração periódica via Cron Job sem custos de banco de dados SQL.
2. **Cache Estático de Alta Performance**: Armazenamento em arquivo JSON pré-compilado, garantindo tempo de resposta instantâneo e baixo consumo de banda 3G.
3. **Widget Embutível**: Componente modular em PHP para exibição no portal institucional da escola.

---

## 🗺️ Ciclo de Vida do Desenvolvimento Web (WDLC)

| Fase | Issue | Descrição | Status |
|---|---|---|---|
| **WDLC 1** | [#1](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/1) | Catalogação das Séries Temporais de Educação (World Bank & UNESCO) | ✅ **Concluída** |
| **WDLC 2** | [#2](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/2) | Arquitetura do Extrator PHP e Especificação do Cache Estático JSON | ✅ **Concluída** |
| **WDLC 3** | [#3](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/3) | Design de Gráficos Comparativos de Baixo Consumo de Dados | ✅ **Concluída** |
| **WDLC 4** | [#4](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/4) | **Construção do Script PHP Extrator e Sanitizador (`fetch-indicators.php`)** | ✅ **Concluída** |
| **WDLC 5** | [#5](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/5) | Desenvolvimento do Dashboard Analítico e Widget PHP Embutível | ⏳ A Iniciar |
| **WDLC 6** | [#6](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/6) | Validação de Dados, Checagem de Tolerância a Falhas e Testes de Conexão | ⏳ A Iniciar |
| **WDLC 7** | [#7](https://github.com/nova-esperanca-angola/diagnostico-educacional-luanda/issues/7) | Configuração de Cron Job no hPanel da Hostinger para Atualização Periódica | ⏳ A Iniciar |

---

## 📑 Documentação Técnica & Especificações

- 📖 **[Catálogo Completo de Indicadores](docs/catalogo-indicadores.md)**: Detalhamento dos 6 endpoints multilaterais homologados, histórico de dados, benchmarks da UNESCO e contextualização de Luanda (INE/UNICEF).
- 📐 **[Arquitetura do Extrator & Cache Estático](docs/arquitetura-extrator-cache.md)**: Diagramas de sequência, fluxo da gravação atômica (`rename()` do SO), desacoplamento do runtime web e política de fail-safe.
- 🎨 **[Manual do Design System & Gráficos SVG](docs/design-sistema-visual.md)**: Especificação visual, paleta semântica, integração Google Stitch (Projeto `1164742190233504273`) e diretrizes de baixo consumo de dados.
- 📋 **[Contrato Formal JSON Schema](schemas/angola-education-summary.schema.json)**: Schema formal (Draft-07) do arquivo consolidado de dados.
- 📦 **[Cache Consolidado em Produção](data/angola-education-summary.json)**: Dados atualizados e sanitizados em tempo real pelo pipeline cURL.

---

## 🚀 Pipeline de Dados, Scripts & Protótipo

### 1. Extração ao Vivo e Atualização do Cache (WDLC 4):
```bash
php scripts/fetch-indicators.php
```

### 2. Validação Estrutural e de Conformidade do Schema (WDLC 2):
```bash
php scripts/validate-schema.php data/angola-education-summary.json
```

### 3. Auditoria dos Endpoints da API do Banco Mundial (WDLC 1):
```bash
php scripts/verify-worldbank-api.php
```

### 4. Auditoria de Orçamento de Dados 3G (< 20KB) (WDLC 3):
```bash
php scripts/verify-asset-budget.php
```

### 5. Visualização do Protótipo no Navegador:
```bash
# Iniciar servidor local embutido do PHP
php -S 127.0.0.1:8000 prototypes/preview-components.php
# Acesse http://127.0.0.1:8000 no navegador
```

### 4. Visualização do Protótipo de Componentes no Navegador:
```bash
# Iniciar servidor local embutido do PHP
php -S 127.0.0.1:8000 prototypes/preview-components.php
# Acesse http://127.0.0.1:8000 no navegador
```

### Exemplo de Saída:
```text
================================================================================
 AUDITORIA DOS ENDPOINTS DA WORLD BANK API (País: AGO - Angola)
 Fase 1 (WDLC 1) - Catalogação das Séries Temporais de Educação
================================================================================

[>] Testando endpoint: SP.POP.0014.TO.ZS (População de 0 a 14 anos (% total))... OK (HTTP 200)
[>] Testando endpoint: SE.PRE.ENRR (Taxa matrícula pré-primária (% bruta))... OK (HTTP 200)
[>] Testando endpoint: SE.PRM.ENRR (Taxa matrícula primária (% bruta))... OK (HTTP 200)
[>] Testando endpoint: SE.PRM.UNER (Crianças fora da escola (primária))... OK (HTTP 200)
[>] Testando endpoint: SE.XPD.TOTL.GD.ZS (Gasto público em educação (% PIB))... OK (HTTP 200)
[>] Testando endpoint: SE.ADT.LITR.ZS (Taxa de alfabetização de adultos (% 15+))... OK (HTTP 200)

--------------------------------------------------------------------------------
 RESUMO CONSOLIDADO DOS DADOS OBTIDOS:
--------------------------------------------------------------------------------
INDICADOR ID         | DESCRIÇÃO                      | ANO    | ÚLTIMO VALOR  
--------------------------------------------------------------------------------
SP.POP.0014.TO.ZS    | População de 0 a 14 anos (% total) | 2025   | 44,10%         
SE.PRE.ENRR          | Taxa matrícula pré-primária (% bruta) | 2016   | 39,61%         
SE.PRM.ENRR          | Taxa matrícula primária (% bruta) | 2023   | 86,74%         
SE.PRM.UNER          | Crianças fora da escola (primária) | 2023   | 2.281.912      
SE.XPD.TOTL.GD.ZS    | Gasto público em educação (% PIB) | 2023   | 2,51%          
SE.ADT.LITR.ZS       | Taxa de alfabetização de adultos (% 15+) | 2023   | 68,18%         
--------------------------------------------------------------------------------

✅ SUCESSO: Todos os 6 endpoints foram validados e responderam com integridade!
```
