<?php

declare(strict_types=1);

/**
 * Script Extrator, Sanitizador e Compilador de Cache Estático JSON
 * 
 * Fase 4 do WDLC (Data Engineering)
 * Projeto: Observatório do Diagnóstico Educacional de Angola & Luanda
 * 
 * Executável via CLI (Terminal / Cron Job) ou via Navegador Web.
 */

// Configurações de Ambiente
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', '1');

$isCli = (php_sapi_name() === 'cli');

// WDLC 7: Restrição de Segurança para Execução Web/HTTP
if (!$isCli) {
    $cronToken = getenv('CRON_SECRET_TOKEN') ?: 'cne_angola_live_cron_2026';
    $providedToken = $_GET['token'] ?? $_SERVER['HTTP_X_CRON_TOKEN'] ?? '';

    if (!is_string($providedToken) || !hash_equals($cronToken, $providedToken)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "403 Forbidden: Acesso restrito a execução CLI (Cron Job) ou requisições autorizadas com chave de segurança válida (?token=...).\n";
        exit(1);
    }
    header('Content-Type: text/plain; charset=UTF-8');
}

function logMessage(string $msg, bool $isError = false): void
{
    global $isCli;
    $timestamp = date('Y-m-d H:i:s');
    $prefix = $isError ? '[ERRO]' : '[INFO]';
    $line = "{$timestamp} {$prefix} {$msg}" . PHP_EOL;

    if ($isCli) {
        if ($isError) {
            fwrite(STDERR, $line);
        } else {
            echo $line;
        }
    } else {
        echo nl2br(htmlspecialchars($line));
    }
}

logMessage("Iniciando Pipeline de Extração e Sanitização de Indicadores (Fase 4)");

// 1. Mapeamento dos Indicadores Oficiais
$indicatorsConfig = [
    'SP.POP.0014.TO.ZS' => [
        'name_pt' => 'População de 0 a 14 anos (% da população total)',
        'name_en' => 'Population ages 0-14 (% of total population)',
        'source_agency' => 'World Bank / UN Population Division',
        'unit' => 'percent',
        'benchmark_reference' => 'Média Mundial: ~25% | África Subsaariana: ~42%',
    ],
    'SE.PRE.ENRR' => [
        'name_pt' => 'Taxa bruta de matrícula na educação pré-primária',
        'name_en' => 'School enrollment, preprimary (% gross)',
        'source_agency' => 'UNESCO UIS / World Bank WDI',
        'unit' => 'percent',
        'benchmark_reference' => 'Meta ODS 4: 100% universal | Déficit atual > 60%',
    ],
    'SE.PRM.ENRR' => [
        'name_pt' => 'Taxa bruta de matrícula no ensino primário',
        'name_en' => 'School enrollment, primary (% gross)',
        'source_agency' => 'UNESCO UIS / World Bank WDI',
        'unit' => 'percent',
        'benchmark_reference' => 'Universalização: 100% (Queda vs. 120% em 2015 por distorção idade-série)',
    ],
    'SE.PRM.UNER' => [
        'name_pt' => 'Crianças fora da escola em idade primária',
        'name_en' => 'Children out of school, primary',
        'source_agency' => 'UNESCO UIS / World Bank WDI',
        'unit' => 'count',
        'benchmark_reference' => 'Meta ODS 4: 0 crianças fora da escola',
    ],
    'SE.XPD.TOTL.GD.ZS' => [
        'name_pt' => 'Gasto público do governo em educação (% do PIB)',
        'name_en' => 'Government expenditure on education, total (% of GDP)',
        'source_agency' => 'UNESCO UIS / World Bank WDI',
        'unit' => 'percent',
        'benchmark_reference' => 'Benchmark Internacional UNESCO (Declaração de Incheon): 4,0% a 6,0% do PIB',
    ],
    'SE.ADT.LITR.ZS' => [
        'name_pt' => 'Taxa de alfabetização de adultos (% 15+ anos)',
        'name_en' => 'Literacy rate, adult total (% of people ages 15 and above)',
        'source_agency' => 'UNESCO UIS / World Bank WDI',
        'unit' => 'percent',
        'benchmark_reference' => 'Média Mundial: ~87%',
    ],
];

// 2. Localização de Bundle de Certificados CA para cURL
$caBundleCandidates = [
    ini_get('curl.cainfo'),
    ini_get('openssl.cafile'),
    'C:/Program Files/Git/mingw64/etc/ssl/certs/ca-bundle.crt',
    'C:/Program Files/Git/usr/ssl/certs/ca-bundle.crt',
    '/etc/ssl/certs/ca-certificates.crt',
    '/etc/pki/tls/certs/ca-bundle.crt',
    '/etc/ssl/ca-bundle.pem',
];

$caInfo = null;
foreach ($caBundleCandidates as $candidate) {
    if (!empty($candidate) && file_exists($candidate)) {
        $caInfo = $candidate;
        break;
    }
}

if ($caInfo !== null) {
    logMessage("Certificados CA detectados: {$caInfo}");
} else {
    logMessage("Nenhum bundle CA detectado. Utilizando fallback local.", true);
}

// 3. Cliente cURL para consulta à API
$country = 'AGO';
$dateRange = '2000:2025';
$extractedIndicators = [];

foreach ($indicatorsConfig as $indId => $meta) {
    $apiBaseUrl = getenv('WORLDBANK_API_BASE_URL') ?: 'https://api.worldbank.org/v2';
    $apiUrl = sprintf(
        '%s/country/%s/indicator/%s?format=json&date=%s&per_page=50',
        rtrim($apiBaseUrl, '/'),
        $country,
        $indId,
        $dateRange
    );

    logMessage("Consultando indicador: {$indId}...");

    $ch = curl_init();
    $curlOptions = [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Diagnostico-Educacional-Luanda/1.0',
    ];

    if ($caInfo !== null) {
        $curlOptions[CURLOPT_CAINFO] = $caInfo;
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
    } else {
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
    }

    curl_setopt_array($ch, $curlOptions);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || $response === false) {
        logMessage("Falha crítica ao consultar {$indId}! Código HTTP: {$httpCode}. Erro cURL: {$curlError}", true);
        logMessage("Processo abortado. O cache anterior permanece intacto (Fail-Safe).", true);
        exit(1);
    }

    $parsed = json_decode((string)$response, true);
    if (!is_array($parsed) || count($parsed) < 2 || !is_array($parsed[1])) {
        logMessage("Resposta inesperada ou malformada da API para {$indId}.", true);
        logMessage("Processo abortado. Cache anterior preservado.", true);
        exit(1);
    }

    $observations = $parsed[1];
    $latestYear = null;
    $latestValue = null;
    $historicalSeries = [];

    // Processamento da série histórica e identificação do latestAvailableYear
    foreach ($observations as $obs) {
        $yr = (int)($obs['date'] ?? 0);
        $val = isset($obs['value']) && $obs['value'] !== null ? (float)$obs['value'] : null;

        if ($yr > 0) {
            $historicalSeries[] = [
                'year' => $yr,
                'value' => $val !== null ? round($val, 2) : null
            ];

            if ($val !== null && $latestYear === null) {
                $latestYear = $yr;
                $latestValue = round($val, 2);
            }
        }
    }

    if ($latestYear === null || $latestValue === null) {
        logMessage("Nenhum dado numérico válido encontrado para {$indId} no período {$dateRange}.", true);
        exit(1);
    }

    logMessage("  -> {$indId}: Ano mais recente: {$latestYear} com valor: {$latestValue}");

    $extractedIndicators[$indId] = [
        'id' => $indId,
        'name_pt' => $meta['name_pt'],
        'name_en' => $meta['name_en'],
        'source_agency' => $meta['source_agency'],
        'unit' => $meta['unit'],
        'latest_available_year' => $latestYear,
        'latest_value' => $latestValue,
        'benchmark_reference' => $meta['benchmark_reference'],
        'historical_series' => $historicalSeries,
    ];
}

// 4. Compilação do Bloco 'summary' com Big Numbers e Métricas Derivadas
$childPop = $extractedIndicators['SP.POP.0014.TO.ZS']['latest_value'];
$childYear = $extractedIndicators['SP.POP.0014.TO.ZS']['latest_available_year'];

$preGross = $extractedIndicators['SE.PRE.ENRR']['latest_value'];
$preYear = $extractedIndicators['SE.PRE.ENRR']['latest_available_year'];
$preDeficit = round(100.0 - $preGross, 2);

$primGross = $extractedIndicators['SE.PRM.ENRR']['latest_value'];
$primYear = $extractedIndicators['SE.PRM.ENRR']['latest_available_year'];

$outOfSchool = (int)$extractedIndicators['SE.PRM.UNER']['latest_value'];
$outYear = $extractedIndicators['SE.PRM.UNER']['latest_available_year'];

$eduGdp = $extractedIndicators['SE.XPD.TOTL.GD.ZS']['latest_value'];
$gdpYear = $extractedIndicators['SE.XPD.TOTL.GD.ZS']['latest_available_year'];

$adultLit = $extractedIndicators['SE.ADT.LITR.ZS']['latest_value'];
$litYear = $extractedIndicators['SE.ADT.LITR.ZS']['latest_available_year'];

$summary = [
    'child_population_pct' => $childPop,
    'child_population_year' => $childYear,
    'preprimary_gross_enrollment_pct' => $preGross,
    'preprimary_deficit_pct' => $preDeficit,
    'preprimary_data_year' => $preYear,
    'primary_gross_enrollment_pct' => $primGross,
    'primary_enrollment_year' => $primYear,
    'out_of_school_primary_count' => $outOfSchool,
    'out_of_school_primary_year' => $outYear,
    'education_gdp_pct' => $eduGdp,
    'education_gdp_year' => $gdpYear,
    'unesco_gdp_benchmark_min_pct' => 4.0,
    'unesco_gdp_benchmark_max_pct' => 6.0,
    'adult_literacy_pct' => $adultLit,
    'adult_literacy_year' => $litYear,
];

// 5. Montagem do Payload Final Consolidado
$payload = [
    'metadata' => [
        'schema_version' => '1.0.0',
        'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
        'country_iso3' => 'AGO',
        'country_name' => 'Angola',
        'pipeline_version' => '1.0.0',
        'data_sources' => [
            [
                'name' => 'World Bank Open Data API (World Development Indicators)',
                'url' => 'https://api.worldbank.org/v2/country/AGO',
                'type' => 'multilateral_api'
            ],
            [
                'name' => 'UNESCO Institute for Statistics (UIS)',
                'url' => 'http://data.uis.unesco.org',
                'type' => 'multilateral_api'
            ],
            [
                'name' => 'Instituto Nacional de Estatística de Angola (INE)',
                'url' => 'https://www.ine.gov.ao',
                'type' => 'national_institute'
            ],
            [
                'name' => 'UNICEF Angola - Relatórios de Educação e Orçamento Social',
                'url' => 'https://www.unicef.org/angola',
                'type' => 'un_agency'
            ]
        ]
    ],
    'summary' => $summary,
    'indicators' => $extractedIndicators,
    'regional_context' => [
        'province' => 'Luanda',
        'capital_city' => 'Luanda',
        'estimated_population' => '8.5 a 9.5 milhões',
        'population_share_national_pct' => 27.0,
        'fertility_rate' => 5.4,
        'contextual_notes' => [
            'Concentração de mais de 27% de toda a população de Angola na Província de Luanda.',
            'Crescimento populacional anual superior a 3,5% impulsionado pela taxa de fertilidade e migração campo-cidade.',
            'Mais de 50 a 60 alunos por sala de aula nas escolas públicas da capital segundo dados do MED e INE.',
            'Educação pré-escolar pública com dotação orçamental inferior a 2% do orçamento setorial de educação segundo a UNICEF Angola.'
        ],
        'local_challenges' => [
            [
                'topic' => 'Déficit de Vagas na Educação Pré-Escolar',
                'description' => 'Menos de 2% do orçamento público educacional é destinado à educação pré-escolar. O Estado praticamente não provê creches e jardins de infância públicos nas periferias de Luanda.',
                'source' => 'UNICEF Angola - Análise do Orçamento da Educação'
            ],
            [
                'topic' => 'Superlotação e Sistema de Turnos',
                'description' => 'Salas de aula públicas operam com mais de 50 alunos e até três turnos diários, reduzindo a permanência escolar para apenas 3 a 3,5 horas por dia.',
                'source' => 'INE Angola - Anuário de Estatísticas Sociais'
            ],
            [
                'topic' => 'Vulnerabilidade Multigeracional',
                'description' => 'Taxa de analfabetismo adulto acima de 31% reforça a necessidade de apoio comunitário e reforço escolar precoce para romper o ciclo de pobreza familiar.',
                'source' => 'INE Angola / Banco Mundial'
            ]
        ]
    ]
];

// 6. Gravação Atômica com Validação
$targetPath = dirname(__DIR__) . '/data/angola-education-summary.json';
$tempPath = $targetPath . '.tmp.' . bin2hex(random_bytes(4));

$jsonEncoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($jsonEncoded === false) {
    logMessage("Falha ao codificar JSON: " . json_last_error_msg(), true);
    exit(1);
}

if (file_put_contents($tempPath, $jsonEncoded, LOCK_EX) === false) {
    logMessage("Falha ao escrever no arquivo temporário: {$tempPath}", true);
    exit(1);
}

// Verificação de integridade no arquivo temporário
$verification = json_decode((string)file_get_contents($tempPath), true);
if (!is_array($verification) || !isset($verification['metadata'], $verification['summary'], $verification['indicators'])) {
    logMessage("Verificação de integridade falhou no arquivo temporário.", true);
    @unlink($tempPath);
    exit(1);
}

// Substituição atômica no sistema de arquivos
if (!rename($tempPath, $targetPath)) {
    logMessage("Falha no rename atômico de {$tempPath} para {$targetPath}.", true);
    @unlink($tempPath);
    exit(1);
}

@chmod($targetPath, 0644);

logMessage("SUCESSO: Cache de produção atualizado com sucesso em {$targetPath}!");
logMessage("Tamanho do arquivo: " . strlen($jsonEncoded) . " bytes.");
logMessage("Timestamp de geração: {$payload['metadata']['generated_at']}");

if (!$isCli) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'success', 'generated_at' => $payload['metadata']['generated_at'], 'bytes' => strlen($jsonEncoded)]);
}

exit(0);
