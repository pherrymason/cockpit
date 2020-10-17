<?php

namespace Cockpit\Framework;

class InputValidationHelpers
{
    public static function isEmail($email)
    {
        if (\function_exists('idn_to_ascii')) {
            $email = @\idn_to_ascii($email);
        }

        return (bool)\filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}