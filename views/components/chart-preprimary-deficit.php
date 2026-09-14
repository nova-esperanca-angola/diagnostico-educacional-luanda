<?php

declare(strict_types=1);

/**
 * Componente: Gráfico SVG de Cobertura da Primeira Infância e Déficit Pré-Escolar
 */
function renderChartPreprimaryDeficit(float $enrolledPct = 39.61, float $deficitPct = 60.39): string
{
    $enrolledWidth = round($enrolledPct, 1);
    $deficitWidth = round($deficitPct, 1);
    $enrolledFormatted = number_format($enrolledPct, 1, ',', '.') . '%';
    $deficitFormatted = number_format($deficitPct, 1, ',', '.') . '%';

    return <<<HTML
<div class="chart-box">
  <div class="chart-header">
    <div>
      <h3 class="chart-title">Acesso à Educação Pré-Escolar (0 a 5 anos)</h3>
      <p class="chart-desc">Taxa Bruta de Matrícula vs. Crianças Desassistidas em Angola</p>
    </div>
    <span class="pill-badge pill-red">Déficit Crítico</span>
  </div>

  <div class="chart-body">
    <svg viewBox="0 0 100 24" class="svg-bar" role="img" aria-label="Partição de Vagas Pré-Escolares">
      <rect x="0" y="0" width="{$enrolledWidth}" height="24" fill="#059669" />
      <rect x="{$enrolledWidth}" y="0" width="{$deficitWidth}" height="24" fill="#dc2626" />
      <text x="3" y="15" fill="#ffffff" font-size="7.5" font-weight="bold">{$enrolledFormatted}</text>
      <text x="97" y="15" fill="#ffffff" font-size="7.5" font-weight="bold" text-anchor="end">Déficit: {$deficitFormatted}</text>
    </svg>
  </div>

  <div class="chart-legend">
    <span class="legend-item"><span class="dot" style="background:#059669;"></span>Com Matrícula: <strong>{$enrolledFormatted}</strong></span>
    <span class="legend-item"><span class="dot" style="background:#dc2626;"></span>Sem Creche/Fora da Rede: <strong>{$deficitFormatted}</strong></span>
  </div>

  <div class="insight-box insight-red">
    <strong>Urgência:</strong> Mais de 6 em cada 10 crianças chegam à 1ª classe primária sem qualquer estímulo pré-escolar prévio.
  </div>
</div>
HTML;
}
