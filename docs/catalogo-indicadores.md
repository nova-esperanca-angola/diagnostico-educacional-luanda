# Catálogo Técnico de Indicadores Educacionais: Angola e Província de Luanda

> **Fase 1 do WDLC (Information Gathering & Data Analysis)**  
> Projeto: Observatório de Diagnóstico Educacional de Angola & Luanda  
> Instituição Beneficiária: Centro Educacional Nova Esperança (Luanda, Angola)  
> Data da Catalogação: Setembro de 2026  
> Fontes Oficiais: **Banco Mundial (World Development Indicators - WDI)**, **UNESCO Institute for Statistics (UIS)**, **Instituto Nacional de Estatística de Angola (INE)** e **UNICEF Angola**.

---

## 1. Visão Geral & Contextualização

Angola possui uma das estruturas demográficas mais jovens do continente africano, onde **mais de 44% da população total tem entre 0 e 14 anos**. Esse perfil demográfico pressiona intensamente os serviços públicos básicos, com destaque crítico para o subsistema de **educação pré-escolar (primeira infância)** e o **ensino primário**.

A **Província de Luanda**, concentrando mais de um quarto de toda a população nacional (estimada entre 8,5 e 9,5 milhões de habitantes segundo projeções do INE Angola), abriga os maiores contrastes e desafios educacionais do país:
- Alta densidade demográfica nas zonas periurbanas e bairros periféricos;
- Rede de ensino primário e pré-escolar público insuficiente para absorver a demanda populacional;
- Dependência crucial de centros comunitários, iniciativas filantrópicas e instituições religiosas para prover educação e acolhimento na primeira infância.

Este documento consolida a catalogação técnica, os endpoints oficiais da **World Bank Open Data API**, os dados apurados e as notas metodológicas para embasar o pipeline de dados automatizado do observatório.

---

## 2. Matriz de Indicadores Multilaterais (World Bank & UNESCO UIS)

Todos os indicadores foram testados, validados e homologados contra a API pública do Banco Mundial (`country: AGO`).

| Código do Indicador | Nome Oficial (API) | Nome em Português | Fonte Primária | Periodicidade | Último Ano Válido | Valor Reportado | Benchmark / Referencial | Status API |
|---|---|---|---|---|---|---|---|---|
| `SP.POP.0014.TO.ZS` | Population ages 0-14 (% of total population) | População de 0 a 14 anos (% da população total) | World Bank / UN Population Division | Anual | **2025** | **44,10%** | Média Mundial: ~25% / África Subsaariana: ~42% | ✅ Ativo (200) |
| `SE.PRE.ENRR` | School enrollment, preprimary (% gross) | Taxa bruta de matrícula na educação pré-primária | UNESCO UIS / World Bank WDI | Periódica | **2016** | **39,61%** | **Déficit > 60%** / Meta ODS 4: 100% universal | ✅ Ativo (200) |
| `SE.PRM.ENRR` | School enrollment, primary (% gross) | Taxa bruta de matrícula no ensino primário | UNESCO UIS / World Bank WDI | Periódica | **2023** | **86,74%** | Universalização: 100% (Queda vs. 120% em 2015) | ✅ Ativo (200) |
| `SE.PRM.UNER` | Children out of school, primary | Crianças fora da escola em idade primária | UNESCO UIS / World Bank WDI | Periódica | **2023** | **2.281.912** | Meta: 0 crianças fora da escola | ✅ Ativo (200) |
| `SE.XPD.TOTL.GD.ZS` | Government expenditure on education, total (% of GDP) | Gasto público em educação (% do PIB) | UNESCO UIS / World Bank WDI | Anual | **2023** | **2,51%** | **Benchmark UNESCO: 4,0% a 6,0% do PIB** | ✅ Ativo (200) |
| `SE.ADT.LITR.ZS` | Literacy rate, adult total (% of people ages 15 and above) | Taxa de alfabetização de adultos (15+ anos) | UNESCO UIS / World Bank WDI | Periódica | **2023** | **68,18%** | Média Mundial: ~87% | ✅ Ativo (200) |

---

## 3. Fichas Técnicas dos Indicadores

### 3.1. População Infantil (0 a 14 anos)
- **Identificador**: `SP.POP.0014.TO.ZS`
- **Nome Oficial**: *Population ages 0-14 (% of total population)*
- **Endpoint**:  
  `https://api.worldbank.org/v2/country/AGO/indicator/SP.POP.0014.TO.ZS?format=json&date=2015:2025`
- **Série Histórica Recente**:
  - 2025: **44,10%**
  - 2024: **44,35%**
  - 2023: **44,57%**
  - 2022: **44,76%**
  - 2021: **44,91%**
- **Relevância Narrativa**:  
  Quase metade de Angola é composta por crianças. Em Luanda, isso se traduz em centenas de milhares de crianças que necessitam anualmente de vagas de creche, iniciação e alfabetização. Mostra a magnitude do público-alvo da escola.

---

### 3.2. Taxa de Matrícula na Educação Pré-Primária (Déficit na Primeira Infância)
- **Identificador**: `SE.PRE.ENRR`
- **Nome Oficial**: *School enrollment, preprimary (% gross)*
- **Endpoint**:  
  `https://api.worldbank.org/v2/country/AGO/indicator/SE.PRE.ENRR?format=json&date=2010:2025`
- **Série Histórica Recente**:
  - 2016: **39,61%**
  - 2011: **73,33%**
  - Anos 2017 a 2025: Dados não consolidados multilateralmente (`null` no repositório WDI).
- **Diagnóstico Crítico**:  
  Com apenas ~39,6% de taxa bruta de matrícula reportada na pré-escola, **mais de 60% das crianças em idade pré-escolar não têm acesso a creches ou jardins de infância formais**. O deficit é ainda mais agudo nas classes de menor poder aquisitivo em Luanda, onde creches privadas são inacessíveis para famílias de baixa renda.

---

### 3.3. Taxa Bruta de Matrícula no Ensino Primário
- **Identificador**: `SE.PRM.ENRR`
- **Nome Oficial**: *School enrollment, primary (% gross)*
- **Endpoint**:  
  `https://api.worldbank.org/v2/country/AGO/indicator/SE.PRM.ENRR?format=json&date=2010:2025`
- **Série Histórica Recente**:
  - 2023: **86,74%**
  - 2021: **89,23%**
  - 2015: **120,58%** *(taxa superior a 100% decorrente da inclusão de alunos com distorção idade-série)*
- **Diagnóstico Crítico**:  
  A retração de 120% (em 2015) para 86,74% (em 2023) reflete a pressão demográfica e as dificuldades de expansão de salas de aula e contratação de professores para acompanhar a explosão de natalidade pós-2010.

---

### 3.4. Crianças Fora da Escola em Idade Primária
- **Identificador**: `SE.PRM.UNER`
- **Nome Oficial**: *Children out of school, primary*
- **Endpoint**:  
  `https://api.worldbank.org/v2/country/AGO/indicator/SE.PRM.UNER?format=json&date=2010:2025`
- **Série Histórica Recente**:
  - 2023: **2.281.912 crianças**
  - 2011: **483.740 crianças**
- **Diagnóstico Crítico**:  
  O número de crianças fora da escola em Angola quadruplicou na última década, ultrapassando **2,28 milhões de crianças**. Luanda concentra a maior parcela absoluta desse contingente devido à migração campo-cidade e ao crescimento desordenado dos subúrbios.

---

### 3.5. Investimento Público em Educação (% do PIB)
- **Identificador**: `SE.XPD.TOTL.GD.ZS`
- **Nome Oficial**: *Government expenditure on education, total (% of GDP)*
- **Endpoint**:  
  `https://api.worldbank.org/v2/country/AGO/indicator/SE.XPD.TOTL.GD.ZS?format=json&date=2010:2025`
- **Série Histórica Recente**:
  - 2023: **2,51% do PIB**
  - 2022: **2,39% do PIB**
  - 2021: **2,30% do PIB**
  - 2020: **2,67% do PIB**
  - 2015: **3,49% do PIB**
  - 2013: **4,44% do PIB**
- **Benchmark Internacional (Declaração de Incheon / UNESCO)**:
  - Mínimo recomendado: **4,0% a 6,0% do PIB** (ou 15% a 20% do Orçamento Geral do Estado - OGE).
  - Com apenas **~2,51%**, Angola investe aproximadamente **metade do parâmetro mínimo internacional**, justificando a carência estrutural de escolas públicas e a necessidade vital de projetos comunitários.

---

### 3.6. Taxa de Alfabetização de Adultos (15+ anos)
- **Identificador**: `SE.ADT.LITR.ZS`
- **Nome Oficial**: *Literacy rate, adult total (% of people ages 15 and above)*
- **Endpoint**:  
  `https://api.worldbank.org/v2/country/AGO/indicator/SE.ADT.LITR.ZS?format=json&date=2010:2025`
- **Série Histórica Recente**:
  - 2023: **68,18%**
  - 2015: **66,24%**
  - 2014: **66,03%**
- **Relevância Narrativa**:  
  Aproximadamente **31,8% dos adultos em Angola não são alfabetizados**, com índices de analfabetismo ainda maiores entre as mulheres. Apoiar a criança na primeira infância quebra o ciclo multigeracional de exclusão e analfabetismo funcional familiar.

---

## 4. Mapeamento Contextual: INE Angola e UNICEF Angola (Província de Luanda)

Além dos dados multilaterais agregados a nível de país pelo Banco Mundial, foram compiladas as evidências das pesquisas de campo locais:

### 4.1. Dados Demográficos de Luanda (INE Angola)
1. **Concentração Populacional**:
   - Luanda é a província mais populosa de Angola, com mais de **8,5 milhões de habitantes**, correspondendo a cerca de **27% da população do país**.
   - O crescimento populacional da capital supera 3,5% ao ano, impulsionado por alta taxa de fertilidade (~5,4 filhos por mulher a nível nacional) e atração migratória interna.
2. **Pressão no Subsistema de Ensino**:
   - Conforme dados dos Anuários Estatísticos do INE e do Ministério da Educação (MED), o número médio de alunos por sala de aula em Luanda frequentemente ultrapassa 50 a 60 crianças no ensino público.
   - O sistema de turnos (manhã, tarde e por vezes vespertino) reduz o tempo de permanência pedagógica diária dos alunos para 3 a 3,5 horas.

### 4.2. Diagnósticos de Primeira Infância e Financiamento (UNICEF Angola)
1. **Relatório de Análise do Orçamento Social (UNICEF Angola)**:
   - A dotação orçamental para a educação pré-escolar representa uma fração marginal (inferior a 2%) de todo o orçamento público alocado à educação, priorizando-se o ensino primário e secundário.
   - Consequência: O Estado praticamente não provê creches públicas para crianças de 0 a 5 anos nos bairros de Luanda, deixando a quase totalidade dessa faixa etária desassistida ou dependente do setor informal/filantrópico.
2. **Inquérito de Indicadores Múltiplos e de Saúde (IIMS / INE & UNICEF)**:
   - Aponta que apenas uma minoria de crianças de 36 a 59 meses de famílias dos dois primeiros quintis de renda participa de programas organizados de aprendizagem pré-escolar.
   - A falta de estímulo cognitivo precoce e alimentação adequada nos primeiros anos de vida reflete-se em taxas elevadas de repetência e abandono precoce ao ingressarem na 1ª classe primária.

---

## 5. Diretrizes Técnicas para o Pipeline de Dados (Fases 2 e 4 do WDLC)

Para a implementação do script extrator em PHP (`fetch-indicators.php` na Fase 4) e definição do cache (`angola-education-summary.json` na Fase 2), devem ser observadas as seguintes regras de negócio validadas:

1. **Estratégia de Varredura `latestAvailableYear`**:
   - Devido à defasagem no preenchimento de séries históricas por organismos internacionais (como a taxa de pré-escola `SE.PRE.ENRR` cujo dado mais recente consolidado é de 2016), o algoritmo não pode simplesmente assumir o ano corrente. Deve percorrer o array do ano mais recente em ordem decrescente até encontrar o primeiro `value !== null`.
2. **Tratamento do Array de Resposta da API do Banco Mundial**:
   - A API retorna sempre um array de dois elementos:
     - Elemento `[0]`: Objeto de metadados de paginação (`page`, `pages`, `per_page`, `total`, `lastupdated`).
     - Elemento `[1]`: Array de registros anuais com os objetos de observação.
   - O extrator deve verificar se `is_array($response) && count($response) >= 2 && is_array($response[1])`.
3. **Resiliência e Timeout**:
   - Timeout máximo de 10 segundos por requisição cURL (`CURLOPT_TIMEOUT => 10`).
   - Armazenamento em cache estático JSON com permissão `0644`.

---

## 6. Conclusão da Fase 1

Com os endpoints testados e homologados, as séries históricas catalogadas e as notas metodológicas devidamente documentadas, a **Fase 1 (Catalogação das Séries Temporais)** encontra-se **integralmente cumprida e documentada**, fornecendo o embasamento de dados necessário para as etapas de arquitetura (WDLC 2) e extração (WDLC 4).
