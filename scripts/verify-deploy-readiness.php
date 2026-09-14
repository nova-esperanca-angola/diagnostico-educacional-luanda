<?php

declare(strict_types=1);

/**
 * Suíte de Testes Automatizados de Prontidão para Deploy & Automação Cron
 * 
 * Fase 7 do WDLC (Deployment & Automation)
 * Projeto: Observatório do Diagnóstico Educacional de Angola & Luanda
 * 
 * Valida:
 * 1. Execução desimpedida via CLI (modo nativo Cron do hPanel da Hostinger).
 * 2. Rejeição com HTTP 403 Forbidden para requisições Web sem token.
 * 3. Rejeição com HTTP 403 Forbidden para requisições Web com token incorreto.
 * 4. Autorização com HTTP 200 OK para requisições Web autenticadas com token válido.
 * 5. Integridade das diretivas de segurança do .htaccess (Options -Indexes, bloqueios sensíveis, cache 3G).
 * 6. Integridade do .env.example e .gitignore.
 * 7. Integridade técnica do guia de implantação docs/deploy-hostinger-cron.md.
 * 8. Conformidade do cache gerado com o schema formal e integridade de permissões.
 */

date_default_timezone_set('UTC');

echo "========================================================================\n";
echo "🔍 Suíte de Validação de Prontidão para Deploy & Cron (WDLC Fase 7)\n";
echo "========================================================================\n\n";

$baseDir = dirname(__DIR__);
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertCondition(bool $condition, string $description, string $details = ''): void
{
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;

    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$description}\n";
        if ($details !== '') {
            echo "         Detalhes: {$details}\n";
        }
    }
}

// -----------------------------------------------------------------------------
// GRUPO 1: Validação de Sintaxe PHP e Execução CLI
// -----------------------------------------------------------------------------
echo "[1/5] Testando Sintaxe e Execução CLI do Extrator...\n";

$phpBinary = PHP_BINARY;
$extratorScript = $baseDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'fetch-indicators.php';

// 1.1 Linting
$lintOutput = [];
$lintExit = -1;
exec('"' . $phpBinary . '" -l "' . $extratorScript . '"', $lintOutput, $lintExit);
assertCondition($lintExit === 0, 'Sintaxe limpa de scripts/fetch-indicators.php', implode("\n", $lintOutput));

// 1.2 Execução CLI nativa (como Cron do hPanel)
$cliOutput = [];
$cliExit = -1;
exec('"' . $phpBinary . '" "' . $extratorScript . '"', $cliOutput, $cliExit);
assertCondition($cliExit === 0, 'Execução CLI nativa autorizada e finalizada com sucesso (Exit 0)', "Exit code: {$cliExit}");

$cacheFile = $baseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'angola-education-summary.json';
assertCondition(file_exists($cacheFile), 'Cache compilado data/angola-education-summary.json gerado via CLI');

// -----------------------------------------------------------------------------
// GRUPO 2: Validação de Segurança HTTP (Proteção contra Chamadas Não Autorizadas)
// -----------------------------------------------------------------------------
echo "\n[2/5] Testando Camada de Segurança HTTP e Autenticação por Token...\n";

// Helper para fazer requisição HTTP usando cURL
function httpGet(string $url, array $headers = []): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr((string)$response, $headerSize);
    $headerStr = substr((string)$response, 0, $headerSize);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $body,
    ];
}

// Inicia servidor PHP embutido temporário para testar a camada Web de forma 100% isolada
$testPort = 8899;
$serverCmd = sprintf('"%s" -S 127.0.0.1:%d -t "%s"', $phpBinary, $testPort, $baseDir);

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

// Monta ambiente preservando variáveis de sistema essenciais no Windows/Linux
$env = [];
foreach (array_merge($_SERVER, getenv()) as $k => $v) {
    if (is_scalar($v)) {
        $env[$k] = (string)$v;
    }
}

$serverProcess = proc_open($serverCmd, $descriptorSpec, $pipes, $baseDir, $env);
usleep(400000); // 400ms para o servidor iniciar

$serverBaseUrl = "http://127.0.0.1:{$testPort}";

try {
    // 2.1 Requisição sem token (deve retornar 403 Forbidden)
    $resNoToken = httpGet("{$serverBaseUrl}/scripts/fetch-indicators.php");
    assertCondition(
        $resNoToken['code'] === 403,
        'Requisição Web sem token é bloqueada com HTTP 403 Forbidden',
        "Recebido status: {$resNoToken['code']} | Body: " . substr($resNoToken['body'], 0, 100)
    );
    assertCondition(
        str_contains($resNoToken['body'], '403 Forbidden'),
        'Mensagem de erro 403 Forbidden explícita no corpo da resposta'
    );

    // 2.2 Requisição com token inválido (deve retornar 403 Forbidden)
    $resWrongToken = httpGet("{$serverBaseUrl}/scripts/fetch-indicators.php?token=token_invalido_hacker");
    assertCondition(
        $resWrongToken['code'] === 403,
        'Requisição Web com token inválido é bloqueada com HTTP 403 Forbidden',
        "Recebido status: {$resWrongToken['code']}"
    );

    // 2.3 Requisição com token válido via GET (?token=cne_angola_live_cron_2026)
    $resValidTokenGet = httpGet("{$serverBaseUrl}/scripts/fetch-indicators.php?token=cne_angola_live_cron_2026");
    assertCondition(
        $resValidTokenGet['code'] === 200,
        'Requisição Web com token válido via GET é autorizada com HTTP 200 OK',
        "Recebido status: {$resValidTokenGet['code']}"
    );
    assertCondition(
        str_contains($resValidTokenGet['body'], 'Cache de produção atualizado') || str_contains($resValidTokenGet['body'], 'success'),
        'Execução via Web autorizada conclui o pipeline e retorna confirmação de sucesso'
    );

    // 2.4 Requisição com token válido via Header HTTP (X-Cron-Token)
    $resValidTokenHeader = httpGet("{$serverBaseUrl}/scripts/fetch-indicators.php", [
        'X-Cron-Token: cne_angola_live_cron_2026'
    ]);
    assertCondition(
        $resValidTokenHeader['code'] === 200,
        'Requisição Web com token válido via Header X-Cron-Token é autorizada com HTTP 200 OK',
        "Recebido status: {$resValidTokenHeader['code']}"
    );

} finally {
    // Encerra o servidor web de testes
    if (is_resource($serverProcess)) {
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_terminate($serverProcess);
        proc_close($serverProcess);
    }
}

// -----------------------------------------------------------------------------
// GRUPO 3: Hardening Web (.htaccess e .gitignore)
// -----------------------------------------------------------------------------
echo "\n[3/5] Testando Hardening do Servidor Web e Proteção de Arquivos...\n";

$htaccessFile = $baseDir . DIRECTORY_SEPARATOR . '.htaccess';
assertCondition(file_exists($htaccessFile), 'Arquivo .htaccess de configuração do servidor presente');

if (file_exists($htaccessFile)) {
    $htaccessContent = file_get_contents($htaccessFile);

    assertCondition(
        str_contains($htaccessContent, 'Options -Indexes'),
        'Desativação de listagem de diretórios presente (Options -Indexes)'
    );

    assertCondition(
        str_contains($htaccessContent, '^\.') || str_contains($htaccessContent, '\.(env'),
        'Bloqueio de arquivos ocultos e arquivos de configuração (.env, etc.) presente'
    );

    assertCondition(
        str_contains($htaccessContent, 'X-Content-Type-Options "nosniff"'),
        'Cabeçalho de segurança nosniff configurado'
    );

    assertCondition(
        str_contains($htaccessContent, 'Cache-Control') && str_contains($htaccessContent, '1800'),
        'Políticas de cache HTTP de 30 minutos configuradas para o JSON de dados'
    );

    assertCondition(
        str_contains($htaccessContent, 'mod_deflate.c') || str_contains($htaccessContent, 'DEFLATE'),
        'Compressão Gzip/Deflate ativa para otimização de banda 3G'
    );
}

$gitignoreFile = $baseDir . DIRECTORY_SEPARATOR . '.gitignore';
assertCondition(file_exists($gitignoreFile), 'Arquivo .gitignore presente');
if (file_exists($gitignoreFile)) {
    $gitIgnoreContent = file_get_contents($gitignoreFile);
    assertCondition(str_contains($gitIgnoreContent, '.env'), 'Proteção de segredos: .env listado no .gitignore');
}

$envExampleFile = $baseDir . DIRECTORY_SEPARATOR . '.env.example';
assertCondition(file_exists($envExampleFile), 'Arquivo modelo .env.example presente');
if (file_exists($envExampleFile)) {
    $envContent = file_get_contents($envExampleFile);
    assertCondition(str_contains($envContent, 'CRON_SECRET_TOKEN'), 'Variável CRON_SECRET_TOKEN documentada no .env.example');
    assertCondition(str_contains($envContent, 'WORLDBANK_API_BASE_URL'), 'Variável WORLDBANK_API_BASE_URL documentada no .env.example');
}

// -----------------------------------------------------------------------------
// GRUPO 4: Validação do Guia Operacional docs/deploy-hostinger-cron.md
// -----------------------------------------------------------------------------
echo "\n[4/5] Testando Especificação e Guia Operacional para hPanel Hostinger...\n";

$deployDoc = $baseDir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'deploy-hostinger-cron.md';
assertCondition(file_exists($deployDoc), 'Manual docs/deploy-hostinger-cron.md presente');

if (file_exists($deployDoc)) {
    $docContent = file_get_contents($deployDoc);
    assertCondition(str_contains($docContent, 'hPanel'), 'Menção e contextualização clara do hPanel da Hostinger');
    assertCondition(str_contains($docContent, '/usr/bin/php'), 'Caminho absoluto canônico do binário PHP especificado');
    assertCondition(str_contains($docContent, '0 3 1 * *'), 'Periodicidade canônica da Cron especificada (0 3 1 * *)');
    assertCondition(str_contains($docContent, 'fetch-indicators.php'), 'Referência correta ao script extrator');
    assertCondition(str_contains($docContent, 'Troubleshooting'), 'Seção de diagnóstico e resolução de problemas incluída');
}

// -----------------------------------------------------------------------------
// GRUPO 5: Validação do Cache em Produção e Conformidade do Schema
// -----------------------------------------------------------------------------
echo "\n[5/5] Testando Conformidade do Cache Consolidado...\n";

$validatorScript = $baseDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'validate-schema.php';
$schemaOutput = [];
$schemaExit = -1;
exec('"' . $phpBinary . '" "' . $validatorScript . '"', $schemaOutput, $schemaExit);
assertCondition($schemaExit === 0, 'Validação formal do JSON Schema Draft-07 aprovada com 100%');

$cacheSize = filesize($cacheFile);
assertCondition(
    $cacheSize > 0 && $cacheSize < 40000,
    sprintf('Tamanho do cache em produção altamente compacto para 3G: %.2f KB (< 40 KB)', $cacheSize / 1024)
);

// -----------------------------------------------------------------------------
// RESUMO FINAL
// -----------------------------------------------------------------------------
echo "\n========================================================================\n";
echo "📊 RESUMO DA VALIDAÇÃO DE DEPLOY & CRON (WDLC FASE 7)\n";
echo "========================================================================\n";
echo "Total de Verificações: {$totalTests}\n";
echo "Aprovadas (PASS):       {$passedTests}\n";
echo "Falhas (FAIL):          {$failedTests}\n";

if ($failedTests === 0) {
    echo "\n🎉 SUCESSO TOTAL: Todos os critérios de deploy e segurança da Fase 7 foram atendidos!\n";
    echo "Pronto para produção no hPanel da Hostinger.\n\n";
    exit(0);
} else {
    echo "\n❌ ATENÇÃO: Foram identificadas falhas de prontidão para deploy. Verifique os logs acima.\n\n";
    exit(1);
}
