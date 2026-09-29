<?php

namespace ShieldLayer\Core;

use RuntimeException;

class View
{
    public static function render(string $viewPath, array $data = [], string $layout = 'layouts/main'): void
    {
        Response::setSecurityHeaders();
        
        $baseDir = dirname(__DIR__, 2) . '/views/';
        $viewFile = $baseDir . $viewPath . '.php';
        $layoutFile = $baseDir . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: {$viewPath}");
        }

        // Extract variables for view scope
        extract($data, EXTR_SKIP);

        // Capture view content buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render inside layout if requested
        if ($layout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}
