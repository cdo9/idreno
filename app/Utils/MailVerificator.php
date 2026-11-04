<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

class MailVerificator
{
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function logInvalidEmail(string $email): void
    {
        Log::warning("Invalid email attempted: {$email}");
    }
}
