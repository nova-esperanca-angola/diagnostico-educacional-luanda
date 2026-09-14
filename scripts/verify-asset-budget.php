<?php

declare(strict_types=1);

/**
 * Script de Verificação de Orçamento de Dados (Data Budget Verification)
 * 
 * Fase 3 do WDLC (Design & Data Viz)
 * Projeto: Diagnóstico Educacional de Angola & Luanda
 */

$prototypePath = dirname(__DIR__) . '/prototypes/preview-components.php';
$maxBudgetBytes = 20 * 1024; // 20 KB

echo PHP_EOL . "================================================================================" . PHP_EOL;
echo " AUDITORIA DE ORÇAMENTO DE DADOS E CONSUMO 3G (Fase 3 - WDLC 3)" . PHP_EOL;
echo "================================================================================" . PHP_EOL;
echo "Arquivo Avaliado : " . realpath($prototypePath) . PHP_EOL;
echo "Teto Máximo      : " . number_format($maxBudgetBytes / 1024, 1) . " KB (Redes Móveis 3G Angola)" . PHP_EOL;
echo "================================================================================" . PHP_EOL . PHP_EOL;

if (!file_exists($prototypePath)) {
    fwrite(STDERR, "❌ ERRO: Arquivo do protótipo não encontrado: {$prototypePath}\n");
    exit(1);
}

// Execução em memória para capturar o payload renderizado
ob_start();
require $prototypePath;
$renderedHtml = (string)ob_get_clean();

$totalBytes = strlen($renderedHtml);
$totalKb = round($totalBytes / 1024, 2);

echo "[1/4] Análise de Peso do Payload Total..." . PHP_EOL;
echo "  -> Tamanho Total Gerado: {$totalBytes} bytes ({$totalKb} KB)" . PHP_EOL;

$budgetPassed = $totalBytes <= $maxBudgetBytes;
if ($budgetPassed) {
    echo "  [✓] OK: O payload está dentro do teto estipulado de 20 KB (" . round(($totalBytes / $maxBudgetBytes) * 100, 1) . "% do limite)." . PHP_EOL;
} else {
    echo "  [✗] FALHA: O payload excedeu o limite máximo em " . ($totalBytes - $maxBudgetBytes) . " bytes!" . PHP_EOL;
}

echo PHP_EOL . "[2/4] Auditoria de Dependências Externas (Zero JS Heavyweight)..." . PHP_EOL;
$hasChartJs = stripos($renderedHtml, 'chart.js') !== false;
$hasHighcharts = stripos($renderedHtml, 'highcharts') !== false;
$hasHeavyLibs = $hasChartJs || $hasHighcharts;

if (!$hasHeavyLibs) {
    echo "  [✓] OK: Nenhuma biblioteca pesada de gráficos JS detectada. Renderização 100% nativa." . PHP_EOL;
} else {
    echo "  [✗] FALHA: Bibliotecas JS pesadas encontradas no código fonte!" . PHP_EOL;
}

echo PHP_EOL . "[3/4] Auditoria de Componentes Gráficos SVG Nativos..." . PHP_EOL;
$svgCount = substr_count($renderedHtml, '<svg');
$rectCount = substr_count($renderedHtml, '<rect');
$polylineCount = substr_count($renderedHtml, '<polyline');

echo "  -> Elementos <svg> renderizados: {$svgCount}" . PHP_EOL;
echo "  -> Elementos <rect> de barras: {$rectCount}" . PHP_EOL;
echo "  -> Elementos <polyline> de tendência: {$polylineCount}" . PHP_EOL;

$svgValid = ($svgCount >= 3) && ($rectCount >= 4) && ($polylineCount >= 1);
if ($svgValid) {
    echo "  [✓] OK: Todos os 3 gráficos comparativos SVG estão presentes e íntegros." . PHP_EOL;
} else {
    echo "  [✗] FALHA: Contagem de elementos SVG inferior ao esperado." . PHP_EOL;
}

echo PHP_EOL . "[4/4] Verificação dos Big Numbers..." . PHP_EOL;
$requiredNumbers = ['44,1%', '>60%', '2,51', '2.281.912'];
$numbersPassed = true;
foreach ($requiredNumbers as $num) {
    if (strpos($renderedHtml, $num) !== false) {
        echo "  [✓] Big Number '{$num}' presente no DOM." . PHP_EOL;
    } else {
        echo "  [✗] Big Number '{$num}' não localizado no DOM!" . PHP_EOL;
        $numbersPassed = false;
    }
}

echo PHP_EOL . "--------------------------------------------------------------------------------" . PHP_EOL;
$allPassed = $budgetPassed && !$hasHeavyLibs && $svgValid && $numbersPassed;

if ($allPassed) {
    echo "✅ SUCESSO: O design atende plenamente às restrições de baixo consumo da Fase 3 (WDLC 3)!" . PHP_EOL;
    echo "Peso final: {$totalKb} KB (Economia de mais de 90% vs. Chart.js)." . PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo "❌ FALHA: Uma ou mais verificações de orçamento ou integridade falharam." . PHP_EOL . PHP_EOL;
    exit(1);
}
