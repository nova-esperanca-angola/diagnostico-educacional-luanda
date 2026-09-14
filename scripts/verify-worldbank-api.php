<?php

declare(strict_types=1);

/**
 * Script de Verificação e Auditoria de Endpoints da World Bank Open Data API
 * 
 * Fase 1 do WDLC (Information Gathering)
 * Projeto: Diagnóstico Educacional de Angola & Luanda
 */

$indicators = [
    [
        'id' => 'SP.POP.0014.TO.ZS',
        'name_pt' => 'População de 0 a 14 anos (% total)',
        'expected_min_year' => 2020,
    ],
    [
        'id' => 'SE.PRE.ENRR',
        'name_pt' => 'Taxa matrícula pré-primária (% bruta)',
        'expected_min_year' => 2010,
    ],
    [
        'id' => 'SE.PRM.ENRR',
        'name_pt' => 'Taxa matrícula primária (% bruta)',
        'expected_min_year' => 2020,
    ],
    [
        'id' => 'SE.PRM.UNER',
        'name_pt' => 'Crianças fora da escola (primária)',
        'expected_min_year' => 2020,
    ],
    [
        'id' => 'SE.XPD.TOTL.GD.ZS',
        'name_pt' => 'Gasto público em educação (% PIB)',
        'expected_min_year' => 2020,
    ],
    [
        'id' => 'SE.ADT.LITR.ZS',
        'name_pt' => 'Taxa de alfabetização de adultos (% 15+)',
        'expected_min_year' => 2020,
    ],
];

$country = 'AGO';
$baseUrl = 'https://api.worldbank.org/v2/country';

echo PHP_EOL . "================================================================================" . PHP_EOL;
echo " AUDITORIA DOS ENDPOINTS DA WORLD BANK API (País: AGO - Angola)" . PHP_EOL;
echo " Fase 1 (WDLC 1) - Catalogação das Séries Temporais de Educação" . PHP_EOL;
echo "================================================================================" . PHP_EOL . PHP_EOL;

$allPassed = true;
$results = [];

// Busca automática por bundle de certificados CA (essencial para Windows dev e Linux Hostinger)
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

foreach ($indicators as $ind) {
    $id = $ind['id'];
    $url = sprintf('%s/%s/indicator/%s?format=json&per_page=25', $baseUrl, $country, $id);
    
    echo sprintf("[>] Testando endpoint: %s (%s)... ", $id, $ind['name_pt']);

    $ch = curl_init();
    $curlOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Diagnostico-Educacional-Luanda/1.0',
    ];

    if ($caInfo !== null) {
        $curlOptions[CURLOPT_CAINFO] = $caInfo;
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
    } else {
        // Fallback para desenvolvimento local caso nenhum bundle CA esteja instalado no SO
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
    }

    curl_setopt_array($ch, $curlOptions);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || $response === false) {
        echo "FALHOU! (HTTP: $httpCode, Erro: $curlError)" . PHP_EOL;
        $allPassed = false;
        continue;
    }

    $data = json_decode((string)$response, true);
    if (!is_array($data) || count($data) < 2 || !is_array($data[1])) {
        echo "FALHOU! (Estrutura JSON inválida)" . PHP_EOL;
        $allPassed = false;
        continue;
    }

    // Varredura para encontrar o latestAvailableYear
    $latestYear = null;
    $latestValue = null;
    foreach ($data[1] as $entry) {
        if (isset($entry['value']) && $entry['value'] !== null) {
            $latestYear = $entry['date'];
            $latestValue = $entry['value'];
            break;
        }
    }

    echo "OK (HTTP 200)" . PHP_EOL;
    $results[] = [
        'id' => $id,
        'name' => $ind['name_pt'],
        'latest_year' => $latestYear ?? 'N/D',
        'latest_value' => $latestValue !== null ? (is_numeric($latestValue) ? round((float)$latestValue, 2) : $latestValue) : 'N/D',
        'raw_value' => $latestValue,
        'total_records' => $data[0]['total'] ?? count($data[1]),
    ];
}

echo PHP_EOL . "--------------------------------------------------------------------------------" . PHP_EOL;
echo " RESUMO CONSOLIDADO DOS DADOS OBTIDOS:" . PHP_EOL;
echo "--------------------------------------------------------------------------------" . PHP_EOL;
printf("%-20s | %-32s | %-6s | %-15s\n", "INDICADOR ID", "DESCRIÇÃO", "ANO", "ÚLTIMO VALOR");
echo str_repeat("-", 80) . PHP_EOL;

foreach ($results as $res) {
    $formattedVal = $res['latest_value'];
    if ($res['id'] === 'SE.PRM.UNER') {
        $formattedVal = number_format((float)$res['raw_value'], 0, ',', '.');
    } elseif (is_numeric($res['latest_value'])) {
        $formattedVal = number_format((float)$res['latest_value'], 2, ',', '.') . '%';
    }

    printf("%-20s | %-32s | %-6s | %-15s\n", $res['id'], $res['name'], $res['latest_year'], $formattedVal);
}

echo "--------------------------------------------------------------------------------" . PHP_EOL;

if ($allPassed && count($results) === count($indicators)) {
    echo PHP_EOL . "✅ SUCESSO: Todos os 6 endpoints foram validados e responderam com integridade!" . PHP_EOL;
    echo "Critérios de Aceitação da Fase 1 (WDLC 1) APROVADOS." . PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo PHP_EOL . "❌ ERRO: Um ou mais endpoints apresentaram falhas de conexão ou integridade." . PHP_EOL . PHP_EOL;
    exit(1);
}
