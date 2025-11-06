<?php
namespace netvod\util;

class PasswordValidator
{
    public static function validate(string $password): array
    {
        $errors = [];
        if ($password === '') {
            $errors[] = "Le mot de passe est obligatoire.";
            return $errors;
        }

        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        }
        if (!preg_match('/[@#$%^&*()!,.?]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*...).";
        }
        return $errors;
    }
}

