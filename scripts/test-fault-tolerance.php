<?php

declare(strict_types=1);

/**
 * Suíte de Testes de Tolerância a Falhas, Conexão e Integridade (Fase 6 - WDLC 6)
 * 
 * Validações:
 * 1. Simulação de falha de conexão / queda de rede na World Bank API (fail-safe do cache ativo).
 * 2. Simulação de timeout / porta fechada.
 * 3. Validação matemática de todas as métricas agregadas (déficit, proporções, crescimento).
 * 4. Linting sintático estrito (php -l) de todos os arquivos PHP do projeto.
 * 5. Auditoria de codificação UTF-8 pura (zero BOM) e permissões de arquivo.
 */

$rootDir = dirname(__DIR__);
$dataPath = $rootDir . '/data/angola-education-summary.json';
$samplePath = $rootDir . '/data/angola-education-summary.sample.json';
$fetchScript = $rootDir . '/scripts/fetch-indicators.php';

echo "================================================================================\n";
echo " SUÍTE DE TESTES DE TOLERÂNCIA A FALHAS E QA (Fase 6 - WDLC 6)\n";
echo "================================================================================\n\n";

$checksPassed = 0;
$checksFailed = 0;

function assertCheck(bool $condition, string $msg): void {
    global $checksPassed, $checksFailed;
    if ($condition) {
        echo "  [✓] OK: {$msg}\n";
        $checksPassed++;
    } else {
        echo "  [✗] FALHA: {$msg}\n";
        $checksFailed++;
    }
}

// -----------------------------------------------------------------------------
// [1/5] SIMULAÇÃO DE QUEDA DE REDE & FAIL-SAFE DO CACHE
// -----------------------------------------------------------------------------
echo "[1/5] Testando Tolerância a Falha de Rede / Queda de Internet na API...\n";

if (!file_exists($dataPath)) {
    copy($samplePath, $dataPath);
}

$originalHash = hash_file('sha256', $dataPath);
$originalMtime = filemtime($dataPath);

// Simular endpoint inacessível / DNS inexistente
$currentEnv = [];
foreach (array_merge($_SERVER, getenv()) as $k => $v) {
    if (is_scalar($v)) {
        $currentEnv[$k] = (string)$v;
    }
}
$offlineEnv = array_merge($currentEnv, ['WORLDBANK_API_BASE_URL' => 'http://dominio-inexistente-para-teste-de-queda-de-rede.local:9999/v2']);
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];

$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($fetchScript);
$process = proc_open($cmd, $descriptors, $pipes, $rootDir, $offlineEnv);

$stdout = '';
$stderr = '';
$exitCode = -1;

if (is_resource($process)) {
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
}

assertCheck($exitCode !== 0, "Script fetch-indicators.php detectou falha de rede e abortou com código de erro ({$exitCode})");
assertCheck(str_contains($stderr, 'Falha crítica') || str_contains($stderr, 'cURL'), "Mensagem explícita de erro cURL emitida para STDERR");

$afterHash = hash_file('sha256', $dataPath);
assertCheck($originalHash === $afterHash, "Cache ativo mantido 100% INTACTO (hash SHA-256 inalterado após falha de rede)");

// Verificar se não há arquivos temporários órfãos (.tmp)
$tmpFiles = glob($rootDir . '/data/*.tmp*');
assertCheck(empty($tmpFiles), "Nenhum arquivo temporário órfão deixado no diretório data/ (Total: " . count($tmpFiles) . ")");


// -----------------------------------------------------------------------------
// [2/5] SIMULAÇÃO DE TIMEOUT / PORTA FECHADA LOCAL
// -----------------------------------------------------------------------------
$timeoutEnv = array_merge($currentEnv, ['WORLDBANK_API_BASE_URL' => 'http://127.0.0.1:54321/v2']);
$processTimeout = proc_open($cmd, $descriptors, $pipes, $rootDir, $timeoutEnv);

if (is_resource($processTimeout)) {
    fclose($pipes[0]);
    $toStdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $toStderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $toExitCode = proc_close($processTimeout);
}

assertCheck($toExitCode !== 0, "Tentativa de conexão a porta fechada rejeitada corretamente ({$toExitCode})");
$afterTimeoutHash = hash_file('sha256', $dataPath);
assertCheck($originalHash === $afterTimeoutHash, "Cache ativo permanece intacto após tentativa de timeout");


// -----------------------------------------------------------------------------
// [3/5] INTEGRIDADE MATEMÁTICA DOS CÁLCULOS E REGRAS DE NEGÓCIO
// -----------------------------------------------------------------------------
echo "\n[3/5] Testando Integridade dos Cálculos Estatísticos...\n";

$data = json_decode((string)file_get_contents($dataPath), true);
$summary = $data['summary'];

// 1. Regra de partição da pré-escola: Matrícula + Déficit = 100%
$enrolled = (float)$summary['preprimary_gross_enrollment_pct'];
$deficit = (float)$summary['preprimary_deficit_pct'];
$sumPre = round($enrolled + $deficit, 2);
assertCheck($sumPre === 100.0, "Soma de matrícula ({$enrolled}%) e déficit pré-escolar ({$deficit}%) é exatamente 100.0%");

// 2. Cálculo do déficit pré-escolar exato
$calculatedDeficit = round(100.0 - $enrolled, 2);
assertCheck($calculatedDeficit === $deficit, "Cálculo 100 - gross_enrollment bate exatamente com {$deficit}%");

// 3. Crescimento de crianças fora da escola (2011 a 2023)
$base2011 = 483740.0;
$latestOutOfSchool = (float)$summary['out_of_school_primary_count'];
$growthPct = round((($latestOutOfSchool - $base2011) / $base2011) * 100);
assertCheck($growthPct === 372.0, "Crescimento de crianças fora da escola entre 2011 e 2023 é exatamente +372% ({$growthPct}%)");

// 4. Subfinanciamento frente à meta da UNESCO
$gdpPct = (float)$summary['education_gdp_pct'];
$unescoMin = (float)$summary['unesco_gdp_benchmark_min_pct'];
$unescoMax = (float)$summary['unesco_gdp_benchmark_max_pct'];
assertCheck($gdpPct < $unescoMin, "Gasto em educação ({$gdpPct}%) está abaixo do piso UNESCO ({$unescoMin}%), confirmando alerta de subfinanciamento");
assertCheck($unescoMin === 4.0 && $unescoMax === 6.0, "Faixa de benchmark da UNESCO calibrada em 4.0% a 6.0% do PIB");

// 5. Proporção populacional de Luanda
$regional = $data['regional_context'];
$sharePct = (float)$regional['population_share_national_pct'];
assertCheck($sharePct >= 25.0 && $sharePct <= 35.0, "Proporção da população de Luanda em relação a Angola é plausível ({$sharePct}%)");


// -----------------------------------------------------------------------------
// [4/5] LINTING SINTÁTICO ESTRITO (PHP 8.1 / 8.2 / 8.3)
// -----------------------------------------------------------------------------
echo "\n[4/5] Executando Linter Sintático em Todos os Arquivos PHP...\n";

$phpFiles = [];
$directory = new RecursiveDirectoryIterator($rootDir);
$iterator = new RecursiveIteratorIterator($directory);
foreach ($iterator as $info) {
    if ($info->isFile() && $info->getExtension() === 'php') {
        $path = $info->getRealPath();
        // Ignorar caminhos de sistema/temp
        if (!str_contains($path, '.git') && !str_contains($path, 'vendor')) {
            $phpFiles[] = $path;
        }
    }
}

sort($phpFiles);
$allLintPassed = true;

foreach ($phpFiles as $file) {
    $relPath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file);
    $lintCmd = 'php -l ' . escapeshellarg($file) . ' 2>&1';
    $output = (string)shell_exec($lintCmd);

    if (str_contains($output, 'No syntax errors detected')) {
        assertCheck(true, "Linter OK: {$relPath}");
    } else {
        assertCheck(false, "Erro de sintaxe em {$relPath}: {$output}");
        $allLintPassed = false;
    }
}


// -----------------------------------------------------------------------------
// [5/5] AUDITORIA DE CODIFICAÇÃO UTF-8 (ZERO BOM) E PERMISSÕES
// -----------------------------------------------------------------------------
echo "\n[5/5] Auditando Codificação de Arquivos (Zero BOM) e Permissões...\n";

$bomChecked = true;
foreach ($phpFiles as $file) {
    $handle = fopen($file, 'rb');
    $bytes = fread($handle, 3);
    fclose($handle);
    if ($bytes === "\xEF\xBB\xBF") {
        $relPath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file);
        assertCheck(false, "Arquivo {$relPath} contém UTF-8 BOM!");
        $bomChecked = false;
    }
}

// Checar JSONs
$jsonFiles = [$dataPath, $samplePath, $rootDir . '/schemas/angola-education-summary.schema.json'];
foreach ($jsonFiles as $jf) {
    if (file_exists($jf)) {
        $handle = fopen($jf, 'rb');
        $bytes = fread($handle, 3);
        fclose($handle);
        $relPath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $jf);
        if ($bytes === "\xEF\xBB\xBF") {
            assertCheck(false, "Arquivo {$relPath} contém UTF-8 BOM!");
            $bomChecked = false;
        } else {
            assertCheck(true, "UTF-8 puro sem BOM: {$relPath}");
        }
    }
}

if ($bomChecked) {
    assertCheck(true, "Todos os arquivos PHP e JSON auditados estão em UTF-8 puro (sem BOM)");
}

// Permissões de leitura e gravação no cache
assertCheck(is_readable($dataPath), "Cache live data/angola-education-summary.json possui permissão de leitura");
assertCheck(is_writable($dataPath), "Cache live data/angola-education-summary.json possui permissão de gravação");
assertCheck(is_readable($samplePath), "Cache sample data/angola-education-summary.sample.json possui permissão de leitura");

// -----------------------------------------------------------------------------
// RESUMO FINAL
// -----------------------------------------------------------------------------
$totalChecks = $checksPassed + $checksFailed;
echo "\n--------------------------------------------------------------------------------\n";
echo "TOTAL DE VERIFICAÇÕES: {$totalChecks} | APROVADAS: {$checksPassed} | FALHAS: {$checksFailed}\n";
echo "--------------------------------------------------------------------------------\n";

if ($checksFailed === 0) {
    echo "\n✅ SUCESSO: Todos os testes de tolerância a falhas e QA da Fase 6 foram aprovados!\n";
    exit(0);
} else {
    echo "\n❌ ERRO: Existem {$checksFailed} testes reprovados.\n";
    exit(1);
}
