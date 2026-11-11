<?php

namespace App\Core;

class Validator
{
    public static function required($data, $fields)
    {
        $errors = array();
        foreach ($fields as $field => $label) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = $label . ' la bat buoc.';
            }
        }
        return $errors;
    }

    public static function positiveNumber($value, $label, $allowZero = true)
    {
        if (!is_numeric($value) || ($allowZero ? $value < 0 : $value <= 0)) {
            return $label . ($allowZero ? ' khong duoc am.' : ' phai lon hon 0.');
        }
        return null;
    }
}

