<?php

// Controller.php

namespace App\Core;

abstract class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        require_once __DIR__ . "/../Views/{$view}.php";
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

