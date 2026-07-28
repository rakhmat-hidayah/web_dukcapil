<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CaptchaService
{
    /**
     * Generate a new Math CAPTCHA.
     * Returns an array with the question and stores the answer in session.
     */
    public static function generateMath(): array
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $operators = ['+', '-'];
        $operator = $operators[array_rand($operators)];

        if ($operator === '-') {
            // Ensure positive result
            if ($num1 < $num2) {
                $temp = $num1;
                $num1 = $num2;
                $num2 = $temp;
            }
            $answer = $num1 - $num2;
        } else {
            $answer = $num1 + $num2;
        }

        $question = "{$num1} {$operator} {$num2} = ?";
        
        // Store answer in session (hashed or plaintext since it's server-side session)
        Session::put('captcha_math_answer', (string) $answer);

        return [
            'question' => $question,
        ];
    }

    /**
     * Validate the math CAPTCHA input.
     */
    public static function validateMath(?string $input): bool
    {
        if (is_null($input)) {
            return false;
        }

        $answer = Session::pull('captcha_math_answer'); // pulls and forgets to prevent replay

        return trim($input) === (string) $answer;
    }

    /**
     * Generate a new Image CAPTCHA as a base64 encoded string.
     * Returns the base64 image data and stores the answer in session.
     */
    public static function generateImage(int $width = 200, int $height = 55): string
    {
        // 1. Create a random clear code from 100% unambiguous uppercase characters
        $characters = '23456789ABCDEFHKMNPQRSTWXYZ';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        Session::put('captcha_image_code', strtolower($code));

        // 2. Create GD image
        $image = imagecreatetruecolor($width, $height);
        
        // Pure White background
        $bg = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        // Subtle background grid
        $gridColor = imagecolorallocate($image, 238, 242, 246);
        for ($x = 25; $x < $width; $x += 30) {
            imageline($image, $x, 0, $x, $height, $gridColor);
        }
        for ($y = 15; $y < $height; $y += 15) {
            imageline($image, 0, $y, $width, $y, $gridColor);
        }

        // Distinct, high-contrast dark text colors
        $textColors = [
            imagecolorallocate($image, 15, 23, 42),    // Dark Slate
            imagecolorallocate($image, 30, 58, 138),   // Dark Blue
            imagecolorallocate($image, 6, 78, 59),     // Dark Emerald
            imagecolorallocate($image, 136, 19, 55),   // Dark Rose
            imagecolorallocate($image, 67, 56, 202),   // Dark Indigo
        ];

        $fontPath = storage_path('app/fonts/arialbd.ttf');
        $useTtf = function_exists('imagettftext') && file_exists($fontPath);

        // Draw 5 LARGE, BOLD, CRYSTAL-CLEAR characters
        $charWidth = ($width - 25) / 5;
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $color = $textColors[$i % count($textColors)];
            
            if ($useTtf) {
                $fontSize = 22; // 22pt LARGE BOLD TTF FONT
                $angle = rand(-5, 5);
                $x = (int) (12 + ($i * $charWidth) + rand(-1, 1));
                $y = (int) ($height - 15 + rand(-2, 2));
                imagettftext($image, $fontSize, $angle, $x, $y, $color, $fontPath, $char);
            } else {
                // High-visibility fallback
                $x = (int) (15 + ($i * $charWidth));
                $y = 16;
                imagechar($image, 5, $x, $y, $char, $color);
                imagechar($image, 5, $x + 1, $y, $char, $color);
            }
        }

        // Subtle background wave line
        $waveColor = imagecolorallocate($image, 203, 213, 225);
        imageline($image, 0, rand(10, 20), $width, rand(35, 45), $waveColor);

        // Outer border
        $borderColor = imagecolorallocate($image, 203, 213, 225);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

        // 3. Output as base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Validate the image CAPTCHA input.
     */
    public static function validateImage(?string $input): bool
    {
        if (is_null($input)) {
            return false;
        }

        $code = Session::pull('captcha_image_code'); // pulls and forgets to prevent replay

        return strtolower(trim($input)) === (string) $code;
    }
}
