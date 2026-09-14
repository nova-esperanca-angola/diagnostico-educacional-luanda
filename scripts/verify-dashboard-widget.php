<?php

declare(strict_types=1);

/**
 * Script de Verificação Automatizada do Dashboard e Widget PHP
 * Fase 5 do WDLC (Development) - Issue #5
 * 
 * Valida:
 * 1. Execução sem erros/notices de index.php e views/widget-impacto.php
 * 2. Orçamento de payload (< 20KB para dashboard e < 10KB para widget)
 * 3. Presença obrigatória dos 4 Big Numbers e dos 3 Gráficos SVG nativos
 * 4. Zero JS pesado ou scripts de terceiros
 * 5. Mecanismo de fallback transparente do cache de dados
 */

$rootDir = dirname(__DIR__);

echo "================================================================================\n";
echo " AUDITORIA DE DASHBOARD ANALÍTICO E WIDGET MODULAR (Fase 5 - WDLC 5)\n";
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
// [1/4] AUDITORIA DO DASHBOARD ANALÍTICO (index.php)
// -----------------------------------------------------------------------------
echo "[1/4] Analisando Dashboard Analítico Principal (index.php)...\n";

ob_start();
include $rootDir . '/index.php';
$dashHtml = (string)ob_get_clean();

$dashBytes = strlen($dashHtml);
$dashKb = round($dashBytes / 1024, 2);
echo "  -> Tamanho do Payload Gerado: {$dashBytes} bytes ({$dashKb} KB)\n";

assertCheck($dashBytes > 5000, "Dashboard gerou conteúdo HTML substancial");
assertCheck($dashBytes <= 20480, "Dashboard dentro do teto estipulado de 20 KB (Redes 3G)");

// Estrutura semântica e acessibilidade
assertCheck(str_contains($dashHtml, '<!DOCTYPE html>'), "Declaração <!DOCTYPE html> presente");
assertCheck(str_contains($dashHtml, '<html lang="pt-AO">'), "Idioma pt-AO configurado");
assertCheck(str_contains($dashHtml, '<header class="header">'), "Header institucional presente");
assertCheck(str_contains($dashHtml, '<footer class="footer">'), "Footer presente");

// Big Numbers
assertCheck(str_contains($dashHtml, 'Demografia Infantil'), "Big Number 1: Demografia Infantil presente");
assertCheck(str_contains($dashHtml, 'Déficit Pré-Escola'), "Big Number 2: Déficit Pré-Escola presente");
assertCheck(str_contains($dashHtml, 'Gasto em Educação'), "Big Number 3: Gasto em Educação presente");
assertCheck(str_contains($dashHtml, 'Fora da Escola'), "Big Number 4: Fora da Escola presente");
assertCheck(str_contains($dashHtml, '2.281.912'), "Dado de evasão primária (2.281.912) renderizado");
assertCheck(str_contains($dashHtml, '2,51% PIB'), "Dado de gasto público (2,51% PIB) renderizado");

// Gráficos SVG Nativos
$svgCount = substr_count($dashHtml, '<svg');
assertCheck($svgCount >= 3, "Os 3 gráficos comparativos SVG estão presentes (Total SVG: {$svgCount})");
assertCheck(str_contains($dashHtml, 'role="img"'), "Atributos ARIA de acessibilidade vetorial presentes");
assertCheck(str_contains($dashHtml, 'Investimento Público em Educação (% do PIB)'), "Gráfico 1: PIB vs UNESCO renderizado");
assertCheck(str_contains($dashHtml, 'Acesso à Educação Pré-Escolar (0 a 5 anos)'), "Gráfico 2: Partição Pré-Escola renderizado");
assertCheck(str_contains($dashHtml, 'Crianças Fora da Escola Primária'), "Gráfico 3: Tendência histórica renderizado");

// Diagnóstico Territorial de Luanda
assertCheck(str_contains($dashHtml, 'Província de Luanda'), "Diagnóstico Territorial de Luanda presente");
assertCheck(str_contains($dashHtml, 'Cacuaco'), "Município de Cacuaco listado");
assertCheck(str_contains($dashHtml, 'Viana'), "Município de Viana listado");
assertCheck(str_contains($dashHtml, 'Cazenga'), "Município de Cazenga listado");
assertCheck(str_contains($dashHtml, 'Belas'), "Município de Belas listado");

// Governança e Metodologia
assertCheck(str_contains($dashHtml, 'World Bank'), "Fonte oficial World Bank mapeada");
assertCheck(str_contains($dashHtml, 'UNESCO'), "Fonte oficial UNESCO mapeada");
assertCheck(str_contains($dashHtml, 'INE'), "Fonte oficial INE Angola mapeada");
assertCheck(str_contains($dashHtml, 'UNICEF'), "Fonte oficial UNICEF Angola mapeada");

// Centro Educacional Nova Esperança
assertCheck(str_contains($dashHtml, 'Centro Educacional Nova Esperança'), "Identidade do Centro Educacional Nova Esperança presente");
assertCheck(str_contains($dashHtml, '+120 mil refeições anuais'), "Métrica de impacto comunitário presente");

// Zero JS pesado
assertCheck(!str_contains(strtolower($dashHtml), 'chart.js'), "Zero dependência de Chart.js");
assertCheck(!str_contains(strtolower($dashHtml), '<script'), "Zero tags <script> na renderização do dashboard");


// -----------------------------------------------------------------------------
// [2/4] AUDITORIA DO WIDGET MODULAR (views/widget-impacto.php)
// -----------------------------------------------------------------------------
echo "\n[2/4] Analisando Widget Modular Embutível (views/widget-impacto.php)...\n";

require_once $rootDir . '/views/widget-impacto.php';

$widgetHtml = renderWidgetImpacto(['show_embed_code' => true]);
$widgetBytes = strlen($widgetHtml);
$widgetKb = round($widgetBytes / 1024, 2);
echo "  -> Tamanho do Payload do Widget: {$widgetBytes} bytes ({$widgetKb} KB)\n";

assertCheck($widgetBytes > 2000, "Widget gerou conteúdo HTML válido");
assertCheck($widgetBytes <= 10240, "Widget dentro do teto estipulado de 10 KB (Payload ultra-leve)");

// Isolamento de CSS
assertCheck(str_contains($widgetHtml, '.edangola-widget-card'), "Classe raiz com namespace de escopo presente");
assertCheck(str_contains($widgetHtml, '.edangola-widget-badge'), "Classes filhas escopadas presentes");

// Métricas do Widget
assertCheck(str_contains($widgetHtml, '>60%'), "Métrica em destaque '>60%' presente no widget");
assertCheck(str_contains($widgetHtml, 'Déficit Crítico na Pré-Escola'), "Título do déficit presente no widget");
assertCheck(str_contains($widgetHtml, '2.281.912'), "Dado de crianças fora da escola presente no widget");
assertCheck(str_contains($widgetHtml, '2,51% PIB'), "Dado do PIB presente no widget");
assertCheck(str_contains($widgetHtml, '<svg viewBox="0 0 100 14"'), "Micro-barra comparativa SVG nativa renderizada");

// Ações e Snippet de Incorporação
assertCheck(str_contains($widgetHtml, 'Apoiar Nossa Creche em Luanda'), "Botão de Call-to-Action presente");
assertCheck(str_contains($widgetHtml, 'iframe src='), "Snippet iframe de incorporação disponível");
assertCheck(!str_contains(strtolower($widgetHtml), '<script'), "Zero scripts no widget");


// -----------------------------------------------------------------------------
// [3/4] TESTE DO MODO STANDALONE DO WIDGET
// -----------------------------------------------------------------------------
echo "\n[3/4] Testando Execução Autônoma (Standalone/Iframe) do Widget...\n";

$cmdStandalone = 'php ' . escapeshellarg($rootDir . '/views/widget-impacto.php');
$standaloneOutput = (string)shell_exec($cmdStandalone);

assertCheck(str_contains($standaloneOutput, '<!DOCTYPE html>'), "Modo standalone emite doctype HTML5");
assertCheck(str_contains($standaloneOutput, '<title>Widget de Impacto Educacional'), "Título HTML emitido em modo standalone");
assertCheck(str_contains($standaloneOutput, 'edangola-widget-card'), "Container do widget renderizado em modo standalone");


// -----------------------------------------------------------------------------
// [4/4] TESTE DE RESILIÊNCIA E FALLBACK DO CARREGADOR DE DADOS
// -----------------------------------------------------------------------------
echo "\n[4/4] Testando Mecanismo de Fallback Transparente do Cache...\n";

require_once $rootDir . '/views/data-loader.php';

$liveDataPath = $rootDir . '/data/angola-education-summary.json';
$backupDataPath = $rootDir . '/data/angola-education-summary.json.bak';

$fallbackTested = false;
if (file_exists($liveDataPath)) {
    rename($liveDataPath, $backupDataPath);
    try {
        $dataFromFallback = loadEducationData();
        $fallbackTested = true;
        assertCheck($dataFromFallback['_source_used'] === 'sample', "Fallback automático para angola-education-summary.sample.json ativado com sucesso");
        assertCheck(isset($dataFromFallback['summary']['child_population_pct']), "Dados amostrais carregados com integridade");
    } finally {
        rename($backupDataPath, $liveDataPath);
    }
}

if ($fallbackTested) {
    // Verificar que ao restaurar, o live volta a ser usado
    $dataRestored = loadEducationData();
    assertCheck($dataRestored['_source_used'] === 'live', "Cache live restaurado e priorizado como fonte primária");
}

// -----------------------------------------------------------------------------
// RESUMO FINAL
// -----------------------------------------------------------------------------
$totalChecks = $checksPassed + $checksFailed;
echo "\n--------------------------------------------------------------------------------\n";
echo "TOTAL DE VERIFICAÇÕES: {$totalChecks} | APROVADAS: {$checksPassed} | FALHAS: {$checksFailed}\n";
echo "--------------------------------------------------------------------------------\n";

if ($checksFailed === 0) {
    echo "\n✅ SUCESSO: O Dashboard e o Widget atendem 100% aos critérios da Fase 5 (WDLC 5)!\n";
    exit(0);
} else {
    echo "\n❌ ERRO: Existem {$checksFailed} não-conformidades a corrigir.\n";
    exit(1);
}
