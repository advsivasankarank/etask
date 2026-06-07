<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $viewPath, array $data = [], ?string $layout = 'main'): string
    {
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$viewPath}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        if ($layout === null) {
            return $content;
        }

        $layoutFile = base_path('layouts/' . $layout . '.php');

        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layoutFile}");
        }

        ob_start();
        require $layoutFile;

        return (string) ob_get_clean();
    }
}
