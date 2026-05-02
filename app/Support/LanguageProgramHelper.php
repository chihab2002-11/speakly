<?php

namespace App\Support;

class LanguageProgramHelper
{
    /**
     * Generate a short code from a program name.
     *
     * @param  string  $name  The program name (e.g., "English", "French", "Spanish Arabic")
     * @return string The generated code in uppercase (e.g., "EN", "FR", "SA")
     */
    public static function generateFlagCode(string $name): string
    {
        $words = explode(' ', trim($name));
        $code = '';

        foreach ($words as $word) {
            if ($word) {
                $code .= strtoupper($word[0]);
            }
        }

        return $code ?: strtoupper(substr($name, 0, 2));
    }
}
