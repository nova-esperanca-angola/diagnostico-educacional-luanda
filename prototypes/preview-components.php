<?php

declare(strict_types=1);

/**
 * Protótipo Standalone de Homologação Visual
 * Fase 3 do WDLC (Design & Data Viz)
 * Projeto Stitch: 1164742190233504273 (Observatório Nova Esperança)
 */

$dataPath = dirname(__DIR__) . '/data/angola-education-summary.sample.json';
if (!file_exists($dataPath)) {
    die("Erro: Base de dados amostral não encontrada em {$dataPath}");
}

$data = json_decode((string)file_get_contents($dataPath), true);
$summary = $data['summary'];
$metadata = $data['metadata'];
$regional = $data['regional_context'];

require_once dirname(__DIR__) . '/views/components/card-stat.php';
require_once dirname(__DIR__) . '/views/components/chart-gdp-benchmark.php';
require_once dirname(__DIR__) . '/views/components/chart-preprimary-deficit.php';
require_once dirname(__DIR__) . '/views/components/chart-out-of-school-trend.php';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Observatório do Diagnóstico Educacional de Angola & Luanda | Protótipo Visual</title>
  <style>
    :root {
      --c-navy: #1e3a8a;
      --c-slate: #0f172a;
      --c-amber: #d97706;
      --c-red: #dc2626;
      --c-green: #059669;
      --c-bg: #f8fafc;
      --c-card: #ffffff;
      --c-border: #e2e8f0;
      --c-muted: #64748b;
      --font: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font); background: var(--c-bg); color: var(--c-slate); line-height: 1.5; padding: 16px; }
    .container { max-width: 1160px; margin: 0 auto; }
    
    /* Header */
    .header { background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #fff; padding: 24px; border-radius: 16px; margin-bottom: 20px; }
    .header-top { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .badge-top { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; }
    .header h1 { font-size: clamp(1.3rem, 2.5vw, 1.8rem); font-weight: 800; letter-spacing: -0.02em; }
    .header p { font-size: 12px; color: #cbd5e1; margin-top: 4px; max-width: 760px; }

    /* Grids */
    .grid-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 20px; }
    .grid-charts { display: grid; grid-template-columns: repeat(auto-fit, minmax(330px, 1fr)); gap: 16px; margin-bottom: 20px; }
    .grid-context { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px; }
    @media (max-width: 800px) { .grid-context { grid-template-columns: 1fr; } }

    /* KPI Component Styles */
    .kpi-card { border: 1px solid var(--c-border); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; }
    .kpi-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
    .kpi-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--c-muted); }
    .kpi-badge { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; }
    .kpi-val { font-size: 1.9rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
    .kpi-sub { font-size: 12px; color: #475569; margin-top: 4px; line-height: 1.35; font-weight: 500; }
    .kpi-source { font-size: 10px; color: #94a3b8; padding-top: 8px; border-top: 1px solid #f1f5f9; margin-top: 8px; }

    /* Chart Components Styles */
    .chart-box { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 12px; padding: 18px; }
    .chart-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; gap: 8px; }
    .chart-title { font-size: 13px; font-weight: 700; color: var(--c-slate); }
    .chart-desc { font-size: 11px; color: var(--c-muted); margin-top: 2px; }
    .chart-body { margin: 12px 0 6px 0; }
    .chart-legend { display: flex; align-items: center; gap: 12px; font-size: 11px; padding-top: 6px; border-top: 1px solid #f8fafc; flex-wrap: wrap; }
    .legend-item { display: inline-flex; align-items: center; gap: 5px; color: var(--c-slate); }
    .dot { width: 9px; height: 9px; border-radius: 2px; display: inline-block; }
    .svg-bar { width: 100%; height: 34px; border-radius: 6px; overflow: hidden; display: block; }
    
    .pill-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; white-space: nowrap; }
    .pill-amber { background: #fef3c7; color: #92400e; }
    .pill-red { background: #fee2e2; color: #991b1b; }
    .insight-box { padding: 8px 10px; border-radius: 6px; margin-top: 10px; font-size: 11px; line-height: 1.4; border-left: 3px solid transparent; }
    .insight-amber { background: #fffbeb; color: #78350f; border-color: var(--c-amber); }
    .insight-red { background: #fef2f2; color: #991b1b; border-color: var(--c-red); }

    /* Content Cards */
    .card-info { background: var(--c-card); border: 1px solid var(--c-border); border-radius: 12px; padding: 18px; }
    .card-info h2 { font-size: 15px; font-weight: 700; margin-bottom: 8px; }
    .card-info p { font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 8px; }
    .list-ch { list-style: none; margin-top: 8px; }
    .list-ch li { font-size: 11px; color: #334155; padding: 6px 0; border-bottom: 1px solid #f1f5f9; display: flex; gap: 6px; }
    .list-ch li:last-child { border-bottom: none; }
    .b-red { color: var(--c-red); font-weight: bold; }

    .card-cta { background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); color: #fff; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; }
    .card-cta h3 { font-size: 15px; font-weight: 800; color: #fde68a; margin-bottom: 6px; }
    .card-cta p { font-size: 11px; color: #e2e8f0; line-height: 1.45; margin-bottom: 14px; }
    .btn-cta { background: #f59e0b; color: #0f172a; text-decoration: none; text-align: center; font-weight: 800; font-size: 12px; padding: 10px; border-radius: 8px; display: block; }
    .footer { text-align: center; font-size: 11px; color: var(--c-muted); padding: 16px 0; border-top: 1px solid var(--c-border); margin-top: 20px; }
  </style>
</head>
<body>

<div class="container">
  
  <header class="header">
    <div class="header-top">
      <span class="badge-top">Dados Auditados Banco Mundial & UNESCO UIS</span>
      <span style="font-size:11px;color:#93c5fd;font-weight:600;">Província de Luanda • 2026</span>
    </div>
    <h1>Observatório do Diagnóstico Educacional de Angola</h1>
    <p>
      Plataforma de evidências estatísticas para embasamento institucional e fortalecimento da educação comunitária na primeira infância (Centro Educacional Nova Esperança).
    </p>
  </header>

  <!-- Big Numbers da Issue #3 -->
  <section class="grid-kpis">
    <?php
      echo renderCardStat([
        'title' => 'Demografia Infantil',
        'value' => '44,1%',
        'subtitle' => 'População de Angola composta por crianças de 0 a 14 anos (~16 milhões).',
        'source' => 'Banco Mundial / INE (2025)',
        'variant' => 'primary',
        'badge' => 'Demografia'
      ]);

      echo renderCardStat([
        'title' => 'Déficit Pré-Escola',
        'value' => '>60%',
        'subtitle' => 'Taxa de matrícula bruta é de apenas 39,6%, deixando a maioria desassistida.',
        'source' => 'UNESCO UIS (2016)',
        'variant' => 'danger',
        'badge' => 'Urgência'
      ]);

      echo renderCardStat([
        'title' => 'Gasto em Educação',
        'value' => '2,51% PIB',
        'subtitle' => 'Abaixo do referencial da UNESCO (meta de 4,0% a 6,0% do PIB nacional).',
        'source' => 'UNESCO UIS (2023)',
        'variant' => 'warning',
        'badge' => 'Subfinanciamento'
      ]);

      echo renderCardStat([
        'title' => 'Fora da Escola',
        'value' => '2.281.912',
        'subtitle' => 'Crianças em idade primária fora das salas de aula em Angola.',
        'source' => 'UNESCO UIS (2023)',
        'variant' => 'danger',
        'badge' => 'Exclusão'
      ]);
    ?>
  </section>

  <!-- Gráficos Comparativos SVG Nativos -->
  <section class="grid-charts">
    <div><?php echo renderChartGdpBenchmark(2.51, 4.0, 6.0); ?></div>
    <div><?php echo renderChartPreprimaryDeficit(39.61, 60.39); ?></div>
    <div><?php echo renderChartOutOfSchoolTrend([2011 => 483740, 2015 => 890000, 2018 => 1420000, 2021 => 1850000, 2023 => 2281912]); ?></div>
  </section>

  <!-- Contexto Local Luanda & Resposta da Escola -->
  <section class="grid-context">
    <div class="card-info">
      <h2>📍 Diagnóstico Territorial: Província de Luanda</h2>
      <p>
        Com mais de <strong><?= htmlspecialchars($regional['estimated_population']) ?> de habitantes</strong> (<?= $regional['population_share_national_pct'] ?>% do país), Luanda concentra a maior densidade de crianças fora da escola. A taxa de fecundidade é de <?= $regional['fertility_rate'] ?> filhos por mulher.
      </p>
      
      <ul class="list-ch">
        <?php foreach ($regional['local_challenges'] as $c): ?>
          <li>
            <span class="b-red">•</span>
            <div>
              <strong><?= htmlspecialchars($c['topic']) ?>:</strong> <?= htmlspecialchars($c['description']) ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card-cta">
      <div>
        <span style="font-size:10px;font-weight:700;text-transform:uppercase;color:#93c5fd;">Resposta no Terreno</span>
        <h3 style="margin-top:4px;">Centro Educacional Nova Esperança</h3>
        <p>
          Atuamos na periferia de Luanda com creche e pré-escola gratuita, alimentação diária e acompanhamento pedagógico para acolher a primeira infância.
        </p>
      </div>
      <a href="#apoiar" class="btn-cta">Apoiar Nossa Escola Infantil</a>
    </div>
  </section>

  <footer class="footer">
    <p>Observatório Educacional Angola • Banco Mundial, UNESCO UIS, INE e UNICEF • Renderização SVG nativa (&lt; 20KB)</p>
  </footer>

</div>

</body>
</html>
