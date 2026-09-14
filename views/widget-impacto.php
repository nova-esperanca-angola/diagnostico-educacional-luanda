<?php

declare(strict_types=1);

/**
 * Widget Modular de Impacto Educacional - Angola & Luanda
 * Fase 5 do WDLC (Development)
 * 
 * Pode ser incluído diretamente em qualquer template PHP:
 *   include 'views/widget-impacto.php';
 * Ou invocado via função:
 *   renderWidgetImpacto(['show_embed_code' => true]);
 * 
 * Também funciona de modo autônomo (standalone/iframe) quando acessado via URL direta.
 */

require_once __DIR__ . '/data-loader.php';

if (!function_exists('renderWidgetImpacto')) {
    function renderWidgetImpacto(array $options = []): string
    {
        $data = loadEducationData();
        $summary = $data['summary'];
        $regional = $data['regional_context'];

        $enrolledPct = (float)($summary['preprimary_gross_enrollment_pct'] ?? 39.61);
        $deficitPct = (float)($summary['preprimary_deficit_pct'] ?? 60.39);
        $enrolledWidth = round($enrolledPct, 1);
        $deficitWidth = round($deficitPct, 1);
        $enrolledFormatted = number_format($enrolledPct, 1, ',', '.') . '%';
        $deficitFormatted = number_format($deficitPct, 1, ',', '.') . '%';

        $outOfSchool = number_format((float)($summary['out_of_school_primary_count'] ?? 2281912), 0, ',', '.');
        $gdpPct = number_format((float)($summary['education_gdp_pct'] ?? 2.51), 2, ',', '.') . '%';

        $showEmbedCode = $options['show_embed_code'] ?? true;
        $ctaUrl = $options['cta_url'] ?? '#apoiar';
        $fullReportUrl = $options['full_report_url'] ?? 'index.php';

        $embedIframeCode = htmlspecialchars(
            '<iframe src="https://diagnostico.novaesperancaangola.org/views/widget-impacto.php" width="100%" height="480" frameborder="0" style="border-radius:12px;border:1px solid #e2e8f0;max-width:480px;display:block;"></iframe>'
        );

        $html = <<<HTML
<div class="edangola-widget-card" id="edangola-widget">
  <style>
    .edangola-widget-card {
      --ew-navy: #1e3a8a;
      --ew-slate: #0f172a;
      --ew-muted: #64748b;
      --ew-border: #e2e8f0;
      --ew-bg: #ffffff;
      --ew-red: #dc2626;
      --ew-amber: #d97706;
      --ew-green: #059669;
      --ew-yellow: #f59e0b;
      
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--ew-bg);
      border: 1px solid var(--ew-border);
      border-radius: 14px;
      padding: 18px;
      max-width: 440px;
      box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.08);
      color: var(--ew-slate);
      box-sizing: border-box;
      line-height: 1.45;
    }
    .edangola-widget-card * { box-sizing: border-box; margin: 0; padding: 0; }
    
    .edangola-widget-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f1f5f9;
      gap: 6px;
    }
    .edangola-widget-badge {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      background: #eff6ff;
      color: var(--ew-navy);
      padding: 3px 8px;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .edangola-widget-badge::before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--ew-navy);
    }
    .edangola-widget-source {
      font-size: 9.5px;
      font-weight: 600;
      color: var(--ew-muted);
    }
    
    .edangola-widget-hero {
      background: #fffafb;
      border: 1px solid #fee2e2;
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 12px;
    }
    .edangola-widget-hero-top {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
    }
    .edangola-widget-hero-val {
      font-size: 2rem;
      font-weight: 800;
      color: var(--ew-red);
      line-height: 1;
      font-variant-numeric: tabular-nums;
    }
    .edangola-widget-tag-danger {
      font-size: 9px;
      font-weight: 800;
      text-transform: uppercase;
      background: #fee2e2;
      color: #991b1b;
      padding: 2px 6px;
      border-radius: 4px;
    }
    .edangola-widget-hero-title {
      font-size: 12px;
      font-weight: 700;
      color: #991b1b;
      margin-top: 4px;
    }
    .edangola-widget-hero-desc {
      font-size: 10.5px;
      color: #475569;
      margin-top: 2px;
    }

    .edangola-widget-bar {
      margin: 8px 0 4px 0;
    }
    .edangola-widget-bar svg {
      width: 100%;
      height: 14px;
      border-radius: 4px;
      display: block;
    }
    .edangola-widget-bar-legend {
      display: flex;
      justify-content: space-between;
      font-size: 9.5px;
      color: var(--ew-muted);
      margin-top: 4px;
    }
    
    .edangola-widget-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-bottom: 12px;
    }
    .edangola-widget-mini-stat {
      background: #f8fafc;
      border: 1px solid var(--ew-border);
      border-radius: 8px;
      padding: 8px 10px;
    }
    .edangola-widget-mini-label {
      font-size: 9.5px;
      font-weight: 600;
      color: var(--ew-muted);
      text-transform: uppercase;
    }
    .edangola-widget-mini-val {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--ew-slate);
      margin-top: 2px;
      font-variant-numeric: tabular-nums;
    }
    .edangola-widget-mini-sub {
      font-size: 9px;
      color: #64748b;
      margin-top: 1px;
    }

    .edangola-widget-local {
      background: #fffbeb;
      border-left: 3px solid var(--ew-amber);
      border-radius: 0 6px 6px 0;
      padding: 8px 10px;
      font-size: 10.5px;
      color: #78350f;
      line-height: 1.35;
      margin-bottom: 14px;
    }

    .edangola-widget-actions {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .edangola-widget-btn-cta {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: #0f172a;
      text-decoration: none;
      font-size: 11.5px;
      font-weight: 800;
      text-align: center;
      padding: 9px 12px;
      border-radius: 7px;
      transition: opacity 0.15s ease;
      display: block;
    }
    .edangola-widget-btn-cta:hover {
      opacity: 0.92;
    }
    .edangola-widget-link-full {
      text-align: center;
      font-size: 10px;
      color: var(--ew-navy);
      text-decoration: none;
      font-weight: 600;
      display: block;
      margin-top: 2px;
    }
    .edangola-widget-link-full:hover {
      text-decoration: underline;
    }

    .edangola-widget-embed-toggle {
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px dashed var(--ew-border);
      font-size: 9px;
      color: var(--ew-muted);
    }
    .edangola-widget-embed-toggle summary {
      cursor: pointer;
      user-select: none;
    }
    .edangola-widget-embed-toggle textarea {
      width: 100%;
      height: 48px;
      font-size: 8.5px;
      font-family: monospace;
      margin-top: 4px;
      padding: 4px;
      border: 1px solid var(--ew-border);
      border-radius: 4px;
      background: #f8fafc;
      resize: none;
    }
  </style>

  <div class="edangola-widget-header">
    <span class="edangola-widget-badge">Diagnóstico Angola</span>
    <span class="edangola-widget-source">Banco Mundial & UNESCO UIS</span>
  </div>

  <div class="edangola-widget-hero">
    <div class="edangola-widget-hero-top">
      <div class="edangola-widget-hero-val">>60%</div>
      <span class="edangola-widget-tag-danger">Urgência</span>
    </div>
    <div class="edangola-widget-hero-title">Déficit Crítico na Pré-Escola</div>
    <p class="edangola-widget-hero-desc">
      Apenas {$enrolledFormatted} das crianças de 0 a 5 anos têm matrícula na educação pré-escolar em Angola.
    </p>
    
    <div class="edangola-widget-bar">
      <svg viewBox="0 0 100 14" preserveAspectRatio="none" role="img" aria-label="Acesso Pré-Escolar vs Déficit">
        <rect x="0" y="0" width="{$enrolledWidth}" height="14" fill="#059669" rx="2" />
        <rect x="{$enrolledWidth}" y="0" width="{$deficitWidth}" height="14" fill="#dc2626" rx="2" />
      </svg>
      <div class="edangola-widget-bar-legend">
        <span>Matriculados: <strong>{$enrolledFormatted}</strong></span>
        <span style="color:#dc2626;">Desassistidos: <strong>{$deficitFormatted}</strong></span>
      </div>
    </div>
  </div>

  <div class="edangola-widget-grid">
    <div class="edangola-widget-mini-stat">
      <div class="edangola-widget-mini-label">Fora da Escola</div>
      <div class="edangola-widget-mini-val" style="color:#dc2626;">{$outOfSchool}</div>
      <div class="edangola-widget-mini-sub">Idade primária (+372%)</div>
    </div>
    <div class="edangola-widget-mini-stat">
      <div class="edangola-widget-mini-label">Gasto Educação</div>
      <div class="edangola-widget-mini-val" style="color:#b45309;">{$gdpPct} PIB</div>
      <div class="edangola-widget-mini-sub">Meta UNESCO: 4%–6%</div>
    </div>
  </div>

  <div class="edangola-widget-local">
    <strong>Alerta Luanda (Cacuaco):</strong> Déficit pré-escolar atinge mais de 85% na periferia. O Centro Educacional Nova Esperança garante creche, alimentação e proteção gratuitas.
  </div>

  <div class="edangola-widget-actions">
    <a href="{$ctaUrl}" class="edangola-widget-btn-cta">Apoiar Nossa Creche em Luanda</a>
    <a href="{$fullReportUrl}" class="edangola-widget-link-full">Ver Diagnóstico Analítico Completo &rarr;</a>
  </div>

HTML;

        if ($showEmbedCode) {
            return $html . <<<HTML
  <details class="edangola-widget-embed-toggle">
    <summary>Incorporar este widget no seu site (&lt;/&gt;)</summary>
    <textarea readonly onclick="this.select()">{$embedIframeCode}</textarea>
  </details>
</div>
HTML;
        }

        return $html . "\n</div>";
    }
}

// Detecção de execução autônoma (standalone ou iframe)
$isStandalone = false;
if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $isStandalone = (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__));
}

if ($isStandalone) {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Widget de Impacto Educacional | Angola & Luanda</title>
  <style>
    body {
      background: transparent;
      margin: 0;
      padding: 10px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
  </style>
</head>
<body>
  <?= renderWidgetImpacto(['show_embed_code' => true]) ?>
</body>
</html>
<?php
}
