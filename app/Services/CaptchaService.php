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
    public static function generateImage(int $width = 180, int $height = 50): string
    {
        // 1. Create a random clear code from 100% unambiguous uppercase characters
        $characters = '23456789ABCDEFHKMNPQRSTWXYZ';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        Session::put('captcha_image_code', strtolower($code));

        // 2. Create 2x high-resolution canvas for ultra-sharp resampled rendering
        $w2 = $width * 2;
        $h2 = $height * 2;
        $highRes = imagecreatetruecolor($w2, $h2);
        
        // Pure White background
        $bg = imagecolorallocate($highRes, 255, 255, 255);
        imagefilledrectangle($highRes, 0, 0, $w2, $h2, $bg);

        // Subtle background grid
        $gridColor = imagecolorallocate($highRes, 235, 240, 245);
        for ($x = 30; $x < $w2; $x += 40) {
            imageline($highRes, $x, 0, $x, $h2, $gridColor);
        }
        for ($y = 25; $y < $h2; $y += 30) {
            imageline($highRes, 0, $y, $w2, $y, $gridColor);
        }

        // Distinct, high-contrast dark text colors
        $textColors = [
            imagecolorallocate($highRes, 15, 23, 42),   // Dark Slate
            imagecolorallocate($highRes, 30, 58, 138),  // Dark Blue
            imagecolorallocate($highRes, 6, 78, 59),    // Dark Emerald
            imagecolorallocate($highRes, 136, 19, 55),  // Dark Rose
            imagecolorallocate($highRes, 67, 56, 202),  // Dark Indigo
        ];

        // Draw characters clearly using GD font 5
        $charWidth = ($w2 - 30) / 5;
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $color = $textColors[$i % count($textColors)];
            
            $x = (int) (20 + ($i * $charWidth));
            $y = 32;
            
            imagechar($highRes, 5, $x, $y, $char, $color);
            imagechar($highRes, 5, $x + 1, $y, $char, $color);
        }

        // 3. Resample down to 180x50 for crisp anti-aliased output
        $finalImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($finalImage, $highRes, 0, 0, 0, 0, $width, $height, $w2, $h2);
        imagedestroy($highRes);

        // Draw clean border
        $borderColor = imagecolorallocate($finalImage, 203, 213, 225);
        imagerectangle($finalImage, 0, 0, $width - 1, $height - 1, $borderColor);

        // 4. Output as base64
        ob_start();
        imagepng($finalImage);
        $imageData = ob_get_clean();
        imagedestroy($finalImage);

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
