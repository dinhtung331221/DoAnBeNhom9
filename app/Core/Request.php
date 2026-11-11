<?php

namespace App\Core;

class Request
{
    private $body;

    public function method()
    {
        return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }

    public function input($key, $default = null)
    {
        $data = $this->all();
        return isset($data[$key]) ? $data[$key] : $default;
    }

    public function all()
    {
        if ($this->body !== null) {
            return $this->body;
        }
        $type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (stripos($type, 'application/json') !== false) {
            $decoded = json_decode(file_get_contents('php://input'), true);
            $this->body = is_array($decoded) ? $decoded : array();
        } else {
            $this->body = $_POST;
        }
        return $this->body;
    }

    public function validateCsrf()
    {
        $header = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
        $token = $header !== '' ? $header : $this->input('_csrf', '');
        return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], (string) $token);
    }
}

