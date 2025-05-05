<?php

namespace App\Validation;

class LoginValidator {
    public static function validate(array $data): array {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        if (empty($data['password'])) {
            $errors[] = "Password is required.";
        }

        return $errors;
    }
}
