<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private static string $viewsPath;

    public static function init(string $viewsPath): void
    {
        self::$viewsPath = rtrim($viewsPath, '/');
    }

    /**
     * Render a view template inside a layout.
     * $template uses dot notation, e.g. "admin/pillars/index".
     */
    public static function render(string $template, array $data = [], ?string $layout = 'admin'): void
    {
        // Admin content templates render before the layout runs, so inject the
        // current admin user here too (not just in the layout) — views like the
        // dashboard greeting need it directly.
        if ($layout === 'admin' && !array_key_exists('adminUser', $data)) {
            $data['adminUser'] = Auth::user();
        }

        $content = self::renderPartial($template, $data);

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = self::$viewsPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layout}");
        }

        extract($data, EXTR_SKIP);
        require $layoutFile;
    }

    /**
     * Render a template to a string without a layout wrapper (used for partials/includes).
     */
    public static function renderPartial(string $template, array $data = []): string
    {
        $file = self::$viewsPath . '/' . $template . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
