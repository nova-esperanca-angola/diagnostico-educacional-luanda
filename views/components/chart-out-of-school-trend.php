<?php

declare(strict_types=1);

/**
 * Componente: Gráfico SVG de Tendência Histórica - Crianças Fora da Escola Primária
 */
function renderChartOutOfSchoolTrend(array $series = [2011 => 483740, 2015 => 890000, 2018 => 1420000, 2021 => 1850000, 2023 => 2281912]): string
{
    $years = array_keys($series);
    $minYear = min($years);
    $maxYear = max($years);
    $minVal = 0;
    $maxVal = 2600000;

    $width = 320;
    $height = 110;
    $paddingTop = 15;
    $paddingBottom = 25;
    $paddingLeft = 15;
    $paddingRight = 15;

    $plotWidth = $width - $paddingLeft - $paddingRight;
    $plotHeight = $height - $paddingTop - $paddingBottom;

    $points = [];
    $svgCircles = [];
    $svgLabels = [];

    foreach ($series as $yr => $val) {
        $x = $paddingLeft + (($yr - $minYear) / ($maxYear - $minYear)) * $plotWidth;
        $y = $paddingTop + $plotHeight - (($val - $minVal) / ($maxVal - $minVal)) * $plotHeight;
        $xRound = round($x, 1);
        $yRound = round($y, 1);
        $points[] = "{$xRound},{$yRound}";

        if ($yr === $minYear || $yr === $maxYear) {
            $formattedVal = ($val >= 1000000) ? (number_format($val / 1000000, 2, ',', '.') . 'M') : (number_format($val / 1000, 0, ',', '.') . 'k');
            $svgCircles[] = sprintf('<circle cx="%s" cy="%s" r="3.5" fill="#dc2626" stroke="#ffffff" stroke-width="1.5" />', $xRound, $yRound);
            $yLabelOffset = ($yr === $maxYear) ? ($yRound - 7) : ($yRound - 6);
            $svgLabels[] = sprintf(
                '<text x="%s" y="%s" fill="#0f172a" font-size="9" font-weight="bold" text-anchor="%s">%s (%d)</text>',
                $xRound,
                $yLabelOffset,
                ($yr === $minYear ? 'start' : 'end'),
                $formattedVal,
                $yr
            );
        }
    }

    $polylinePoints = implode(' ', $points);
    $firstX = $paddingLeft;
    $lastX = $paddingLeft + $plotWidth;
    $bottomY = $paddingTop + $plotHeight;
    $areaPoints = "{$firstX},{$bottomY} {$polylinePoints} {$lastX},{$bottomY}";

    $circlesHtml = implode('', $svgCircles);
    $labelsHtml = implode('', $svgLabels);

    return <<<HTML
<div class="chart-box">
  <div class="chart-header">
    <div>
      <h3 class="chart-title">Crianças Fora da Escola Primária (2011–2023)</h3>
      <p class="chart-desc">Crescimento de +372% no contingente de crianças sem escola primária</p>
    </div>
    <span class="pill-badge pill-red">+372%</span>
  </div>

  <div class="chart-body">
    <svg viewBox="0 0 {$width} {$height}" style="width:100%;height:120px;display:block;" role="img" aria-label="Linha Histórica de Crianças Fora da Escola">
      <defs>
        <linearGradient id="gradRed" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" stop-color="#fee2e2" stop-opacity="0.8"/>
          <stop offset="100%" stop-color="#fee2e2" stop-opacity="0.05"/>
        </linearGradient>
      </defs>
      <line x1="{$paddingLeft}" y1="{$bottomY}" x2="{$lastX}" y2="{$bottomY}" stroke="#e2e8f0" stroke-width="1" />
      <polygon points="{$areaPoints}" fill="url(#gradRed)" />
      <polyline points="{$polylinePoints}" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
      {$circlesHtml}
      {$labelsHtml}
    </svg>
  </div>

  <div class="chart-legend" style="justify-content:space-between;">
    <span>2011: <strong>483 mil crianças</strong></span>
    <span style="color:#dc2626;">2023: <strong>2,28 milhões de crianças</strong></span>
  </div>
</div>
HTML;
}
