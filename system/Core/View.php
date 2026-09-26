<?php

declare(strict_types=1);

namespace System\Core;

use RuntimeException;

final class View
{
    private array $sections = [];
    private ?string $layout = null;
    private ?string $currentSection = null;

    public static function render(string $view, array $data = []): string
    {
        return (new self())->renderView($view, $data);
    }

    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    public function section(string $name): void
    {
        if ($this->currentSection !== null) {
            throw new RuntimeException('A view section is already open.');
        }

        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection === null) {
            throw new RuntimeException('No view section is open.');
        }

        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function partial(string $view, array $data = []): string
    {
        return $this->includeView($view, $data);
    }

    private function renderView(string $view, array $data): string
    {
        $this->includeView($view, $data);

        if ($this->layout === null) {
            return $this->sections['content'] ?? '';
        }

        return $this->includeView($this->layout, $data);
    }

    private function includeView(string $view, array $data): string
    {
        $path = app_path('Views/' . trim($view, '/\\') . '.php');

        if (!is_file($path)) {
            throw new RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $path;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
