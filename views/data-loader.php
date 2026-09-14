<?php

declare(strict_types=1);

/**
 * Utilitário de Carregamento Resiliente do Cache JSON Educacional
 * 
 * Fornece fallback transparente para o cache amostral se o cache live
 * não estiver disponível ou contiver dados corrompidos.
 */
if (!function_exists('loadEducationData')) {
    function loadEducationData(?string $customPath = null): array
    {
        $baseDir = dirname(__DIR__);
        $primaryPath = $customPath ?? ($baseDir . '/data/angola-education-summary.json');
        $samplePath = $baseDir . '/data/angola-education-summary.sample.json';

        $sourceUsed = 'live';
        $content = false;

        if (file_exists($primaryPath)) {
            $content = @file_get_contents($primaryPath);
        }

        if ($content === false || trim($content) === '') {
            $sourceUsed = 'sample';
            if (file_exists($samplePath)) {
                $content = @file_get_contents($samplePath);
            }
        }

        if ($content === false || trim($content) === '') {
            throw new RuntimeException("Falha crítica: Base de dados educacionais não encontrada em {$primaryPath} nem no sample {$samplePath}");
        }

        $decoded = json_decode((string)$content, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            if ($sourceUsed !== 'sample' && file_exists($samplePath)) {
                $contentSample = @file_get_contents($samplePath);
                $decoded = json_decode((string)$contentSample, true);
                $sourceUsed = 'sample_after_corruption';
            }
        }

        if (!is_array($decoded)) {
            throw new RuntimeException("Falha de parsing JSON nos dados educacionais: " . json_last_error_msg());
        }

        $decoded['_source_used'] = $sourceUsed;
        return $decoded;
    }
}
