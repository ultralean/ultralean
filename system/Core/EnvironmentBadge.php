<?php

declare(strict_types=1);

namespace System\Core;

final class EnvironmentBadge
{
    public static function render(): string
    {
        $env = (string) config('app.env', config('app.mode', 'production'));
        $debug = (bool) config('app.debug', false);

        if ($env === 'production' && !$debug) {
            return '';
        }

        $label = strtoupper($env ?: 'development');
        if ($debug) {
            $label .= ' / DEBUG';
        }

        $color = match ($env) {
            'development' => '#d32f2f',
            'staging' => '#ff9800',
            default => '#792e2e',
        };

        return '<div style="position:fixed;right:12px;bottom:12px;z-index:999999;padding:6px 10px;background:'
            . e($color) . ';color:#fff;font:12px Arial,sans-serif;border-radius:4px;opacity:.85;pointer-events:none;user-select:none">'
            . e($label) . '</div>';
    }
}
