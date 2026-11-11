<?php

namespace App\Core;

class View
{
    public static function render($template, $data = array(), $layout = 'layouts/app')
    {
        extract($data);
        ob_start();
        require BASE_PATH . '/app/Views/' . $template . '.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/Views/' . $layout . '.php';
    }
}

