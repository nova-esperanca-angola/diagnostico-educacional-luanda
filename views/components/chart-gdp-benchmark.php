<?php

declare(strict_types=1);

/**
 * Componente: Gráfico Comparativo SVG - Investimento Público (% do PIB) vs. Benchmark UNESCO
 */
function renderChartGdpBenchmark(float $angolaGdpPercent = 2.51, float $minBenchmark = 4.0, float $maxBenchmark = 6.0): string
{
    $scaleMax = 7.0;
    $angolaWidth = round(($angolaGdpPercent / $scaleMax) * 100, 2);
    $minPos = round(($minBenchmark / $scaleMax) * 100, 2);
    $maxPos = round(($maxBenchmark / $scaleMax) * 100, 2);
    $benchZoneWidth = round($maxPos - $minPos, 2);

    $angolaFormatted = number_format($angolaGdpPercent, 2, ',', '.') . '%';
    $minFormatted = number_format($minBenchmark, 1, ',', '.') . '%';
    $maxFormatted = number_format($maxBenchmark, 1, ',', '.') . '%';

    return <<<HTML
<div class="chart-box">
  <div class="chart-header">
    <div>
      <h3 class="chart-title">Investimento Público em Educação (% do PIB)</h3>
      <p class="chart-desc">Angola vs. Referencial Internacional da UNESCO (Declaração de Incheon)</p>
    </div>
    <span class="pill-badge pill-amber">Subfinanciamento</span>
  </div>

  <div class="chart-body">
    <svg viewBox="0 0 100 24" class="svg-bar" role="img" aria-label="Comparativo PIB Angola vs UNESCO">
      <rect x="{$minPos}" y="0" width="{$benchZoneWidth}" height="24" fill="#d1fae5" />
      <rect x="0" y="0" width="{$angolaWidth}" height="24" fill="#1e3a8a" rx="3" />
      <line x1="{$minPos}" y1="0" x2="{$minPos}" y2="24" stroke="#059669" stroke-width="1.2" stroke-dasharray="2,1.5" />
      <line x1="{$maxPos}" y1="0" x2="{$maxPos}" y2="24" stroke="#047857" stroke-width="1.2" stroke-dasharray="2,1.5" />
      <text x="3" y="15" fill="#ffffff" font-size="7.5" font-weight="bold">{$angolaFormatted}</text>
    </svg>
  </div>

  <div class="chart-legend">
    <span class="legend-item"><span class="dot" style="background:#1e3a8a;"></span>Angola: <strong>{$angolaFormatted}</strong> do PIB</span>
    <span class="legend-item"><span class="dot" style="background:#059669;"></span>Faixa UNESCO: <strong>{$minFormatted} a {$maxFormatted}</strong></span>
  </div>

  <div class="insight-box insight-amber">
    <strong>Diagnóstico:</strong> Com ~2,51% do PIB, Angola aplica metade do piso recomendado pela UNESCO para universalização do ensino.
  </div>
</div>
HTML;
}
