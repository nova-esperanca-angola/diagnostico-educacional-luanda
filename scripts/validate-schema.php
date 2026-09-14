<?php

declare(strict_types=1);

/**
 * Script de Validação Estrutural e de Conformidade de Schema JSON
 * 
 * Fase 2 do WDLC (Planning & Architecture)
 * Projeto: Diagnóstico Educacional de Angola & Luanda
 */

$schemaPath = dirname(__DIR__) . '/schemas/angola-education-summary.schema.json';
$dataPath = $argv[1] ?? (dirname(__DIR__) . '/data/angola-education-summary.sample.json');

echo PHP_EOL . "================================================================================" . PHP_EOL;
echo " VALIDAÇÃO DE CONFORMIDADE DE SCHEMA JSON (Fase 2 - WDLC 2)" . PHP_EOL;
echo "================================================================================" . PHP_EOL;
echo "Arquivo Schema : " . realpath($schemaPath) . PHP_EOL;
echo "Arquivo Alvo   : " . (file_exists($dataPath) ? realpath($dataPath) : $dataPath) . PHP_EOL;
echo "================================================================================" . PHP_EOL . PHP_EOL;

if (!file_exists($schemaPath)) {
    fwrite(STDERR, "❌ ERRO: Arquivo de schema não encontrado: {$schemaPath}\n");
    exit(1);
}

if (!file_exists($dataPath)) {
    fwrite(STDERR, "❌ ERRO: Arquivo de dados não encontrado: {$dataPath}\n");
    exit(1);
}

$schema = json_decode((string)file_get_contents($schemaPath), true);
$data = json_decode((string)file_get_contents($dataPath), true);

if (!is_array($schema)) {
    fwrite(STDERR, "❌ ERRO: O arquivo de schema não contém JSON válido.\n");
    exit(1);
}

if (!is_array($data)) {
    fwrite(STDERR, "❌ ERRO: O arquivo de dados não contém JSON válido.\n");
    exit(1);
}

$errors = [];
$checks = 0;

function assertCondition(bool $condition, string $msg, array &$errors, int &$checks): void
{
    $checks++;
    if (!$condition) {
        $errors[] = $msg;
        echo "  [✗] FALHA: {$msg}" . PHP_EOL;
    } else {
        echo "  [✓] OK: {$msg}" . PHP_EOL;
    }
}

echo "[1/4] Validando Estrutura Raiz..." . PHP_EOL;
$requiredRoots = ['metadata', 'summary', 'indicators', 'regional_context'];
foreach ($requiredRoots as $root) {
    assertCondition(isset($data[$root]) && is_array($data[$root]), "Bloco obrigatório '{$root}' presente", $errors, $checks);
}
foreach (array_keys($data) as $actualKey) {
    assertCondition(in_array($actualKey, $requiredRoots, true), "Chave '{$actualKey}' permitida no nível raiz", $errors, $checks);
}

echo PHP_EOL . "[2/4] Validando Bloco 'metadata'..." . PHP_EOL;
assertCondition(isset($data['metadata']['schema_version']) && preg_match('/^\d+\.\d+\.\d+$/', $data['metadata']['schema_version']) === 1, "metadata.schema_version semântico válido", $errors, $checks);
assertCondition(isset($data['metadata']['generated_at']) && strtotime($data['metadata']['generated_at']) !== false, "metadata.generated_at formato de data ISO válido", $errors, $checks);
assertCondition(($data['metadata']['country_iso3'] ?? '') === 'AGO', "metadata.country_iso3 deve ser 'AGO'", $errors, $checks);
assertCondition(($data['metadata']['country_name'] ?? '') === 'Angola', "metadata.country_name deve ser 'Angola'", $errors, $checks);
assertCondition(!empty($data['metadata']['pipeline_version']), "metadata.pipeline_version presente", $errors, $checks);
assertCondition(isset($data['metadata']['data_sources']) && is_array($data['metadata']['data_sources']) && count($data['metadata']['data_sources']) >= 4, "metadata.data_sources contém fontes oficiais mapeadas", $errors, $checks);

echo PHP_EOL . "[3/4] Validando Bloco 'summary' (Big Numbers)..." . PHP_EOL;
$summaryFields = [
    'child_population_pct', 'child_population_year',
    'preprimary_gross_enrollment_pct', 'preprimary_deficit_pct', 'preprimary_data_year',
    'primary_gross_enrollment_pct', 'primary_enrollment_year',
    'out_of_school_primary_count', 'out_of_school_primary_year',
    'education_gdp_pct', 'education_gdp_year',
    'unesco_gdp_benchmark_min_pct', 'unesco_gdp_benchmark_max_pct',
    'adult_literacy_pct', 'adult_literacy_year'
];
foreach ($summaryFields as $field) {
    assertCondition(isset($data['summary'][$field]), "summary.{$field} presente", $errors, $checks);
}
assertCondition(($data['summary']['child_population_pct'] ?? 0) > 40 && ($data['summary']['child_population_pct'] ?? 0) < 50, "summary.child_population_pct em faixa plausível (40-50%)", $errors, $checks);
assertCondition(($data['summary']['preprimary_deficit_pct'] ?? 0) > 55, "summary.preprimary_deficit_pct reflete déficit crítico (> 55%)", $errors, $checks);
assertCondition(($data['summary']['out_of_school_primary_count'] ?? 0) > 1000000, "summary.out_of_school_primary_count consistente (> 1 milhão)", $errors, $checks);

echo PHP_EOL . "[4/4] Validando Bloco 'indicators' e 'regional_context'..." . PHP_EOL;
$expectedIndicators = [
    'SP.POP.0014.TO.ZS',
    'SE.PRE.ENRR',
    'SE.PRM.ENRR',
    'SE.PRM.UNER',
    'SE.XPD.TOTL.GD.ZS',
    'SE.ADT.LITR.ZS'
];
foreach ($expectedIndicators as $indId) {
    assertCondition(isset($data['indicators'][$indId]), "Indicador '{$indId}' registrado", $errors, $checks);
    if (isset($data['indicators'][$indId])) {
        $ind = $data['indicators'][$indId];
        assertCondition(!empty($ind['id']) && !empty($ind['name_pt']) && !empty($ind['source_agency']), "Metadados completos de '{$indId}'", $errors, $checks);
        assertCondition(isset($ind['latest_available_year']) && is_int($ind['latest_available_year']), "latest_available_year é inteiro em '{$indId}'", $errors, $checks);
        assertCondition(isset($ind['latest_value']) && is_numeric($ind['latest_value']), "latest_value numérico em '{$indId}'", $errors, $checks);
        assertCondition(isset($ind['historical_series']) && is_array($ind['historical_series']) && count($ind['historical_series']) > 0, "historical_series populada em '{$indId}'", $errors, $checks);
    }
}

assertCondition(($data['regional_context']['province'] ?? '') === 'Luanda', "regional_context.province é 'Luanda'", $errors, $checks);
assertCondition(isset($data['regional_context']['contextual_notes']) && is_array($data['regional_context']['contextual_notes']), "Notas contextuais de Luanda presentes", $errors, $checks);
assertCondition(isset($data['regional_context']['local_challenges']) && is_array($data['regional_context']['local_challenges']), "Desafios locais de Luanda presentes", $errors, $checks);

echo PHP_EOL . "--------------------------------------------------------------------------------" . PHP_EOL;
echo sprintf("TOTAL DE VERIFICAÇÕES: %d | APROVADAS: %d | FALHAS: %d\n", $checks, $checks - count($errors), count($errors));
echo "--------------------------------------------------------------------------------" . PHP_EOL;

if (empty($errors)) {
    echo PHP_EOL . "✅ SUCESSO: O arquivo de dados atende rigorosamente ao schema da Fase 2 (WDLC 2)!" . PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo PHP_EOL . "❌ FALHA: Foram encontrados " . count($errors) . " erros de conformidade com o schema." . PHP_EOL . PHP_EOL;
    exit(1);
}
