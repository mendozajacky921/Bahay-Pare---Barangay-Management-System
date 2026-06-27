<?php

declare(strict_types=1);

namespace Core;

class View
{
    private static string $layout = 'public';

    /**
     * Render a view with a layout.
     *
     * @param string $view    Dot-notation path, e.g. 'public/home' or 'staff/dashboard'
     * @param array  $data    Variables to extract into the view
     * @param string $layout  Layout file name (without .php)
     */
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $viewPath   = ROOT_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        $layoutName = $layout ?? self::resolveLayout($view);
        $layoutPath = ROOT_PATH . '/views/layouts/' . $layoutName . '.php';

        if (!file_exists($viewPath)) {
            Response::abort(404, "View not found: {$view}");
        }

        // Extract data into local scope
        extract($data, EXTR_SKIP);

        // Capture view content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Inject into layout
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Render a partial (no layout).
     */
    public static function partial(string $partial, array $data = []): void
    {
        $path = ROOT_PATH . '/views/partials/' . $partial . '.php';
        if (file_exists($path)) {
            extract($data, EXTR_SKIP);
            require $path;
        }
    }

    /**
     * Escape output for safe HTML rendering.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Resolve layout from view path prefix.
     */
    private static function resolveLayout(string $view): string
    {
        if (str_starts_with($view, 'staff/')) {
            return 'staff';
        }
        if (str_starts_with($view, 'resident/')) {
            return 'resident';
        }
        if (str_starts_with($view, 'auth/')) {
            return 'auth';
        }
        return 'public';
    }

    /**
     * Generate a URL for an asset.
     */
    public static function asset(string $path): string
    {
        return APP_URL . '/public/assets/' . ltrim($path, '/');
    }

    /**
     * Generate a URL.
     */
    public static function url(string $path = ''): string
    {
        return APP_URL . '/' . ltrim($path, '/');
    }
}
