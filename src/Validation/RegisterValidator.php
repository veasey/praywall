<?php
// src/Validation/RegisterValidator.php

namespace App\Validation;

class RegisterValidator {
    public static function validate(array $data): array {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = "Name is required.";
        }

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email required.";
        }

        if (strlen($data['password'] ?? '') < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
            $errors[] = "Passwords do not match.";
        }

        return $errors;
    }
}
