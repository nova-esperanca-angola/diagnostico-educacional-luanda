<?php

declare(strict_types=1);

/**
 * Componente: Card de Estatística / Big Number (Otimizado para baixo consumo)
 * 
 * @param array{
 *   title: string,
 *   value: string,
 *   subtitle: string,
 *   source: string,
 *   variant?: 'primary' | 'warning' | 'danger' | 'benchmark',
 *   badge?: string
 * } $card
 */
function renderCardStat(array $card): string
{
    $variant = $card['variant'] ?? 'primary';
    
    $colors = [
        'primary' => ['bg' => '#ffffff', 'border' => '#e2e8f0', 'text' => '#1e3a8a', 'bbg' => '#eff6ff', 'btxt' => '#1e40af'],
        'warning' => ['bg' => '#fffdfa', 'border' => '#fde68a', 'text' => '#b45309', 'bbg' => '#fef3c7', 'btxt' => '#92400e'],
        'danger'  => ['bg' => '#fffafb', 'border' => '#fecaca', 'text' => '#dc2626', 'bbg' => '#fee2e2', 'btxt' => '#991b1b'],
        'benchmark'=>['bg' => '#fbfdfc', 'border' => '#a7f3d0', 'text' => '#059669', 'bbg' => '#d1fae5', 'btxt' => '#065f46']
    ];

    $cfg = $colors[$variant] ?? $colors['primary'];
    $badgeHtml = '';
    if (!empty($card['badge'])) {
        $badgeHtml = sprintf(
            '<span class="kpi-badge" style="background:%s;color:%s;">%s</span>',
            $cfg['bbg'],
            $cfg['btxt'],
            htmlspecialchars($card['badge'])
        );
    }

    return <<<HTML
<div class="kpi-card" style="background:{$cfg['bg']};border-color:{$cfg['border']};">
  <div class="kpi-header">
    <span class="kpi-title">{$card['title']}</span>
    {$badgeHtml}
  </div>
  <div class="kpi-val" style="color:{$cfg['text']};">{$card['value']}</div>
  <div class="kpi-sub">{$card['subtitle']}</div>
  <div class="kpi-source">Fonte: {$card['source']}</div>
</div>
HTML;
}
