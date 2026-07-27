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
        // 1. Create a random clear code
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // exclude confusing chars like 1, 0, I, O
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        Session::put('captcha_image_code', strtolower($code));

        // 2. Generate GD image
        $image = imagecreatetruecolor($width, $height);
        
        // Setup crisp background (Pure White)
        $bg = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        // Add subtle light gray background grid lines for security without obscuring text
        $gridColor = imagecolorallocate($image, 226, 232, 240); // slate-200
        for ($x = 15; $x < $width; $x += 25) {
            imageline($image, $x, 0, $x, $height, $gridColor);
        }
        for ($y = 12; $y < $height; $y += 15) {
            imageline($image, 0, $y, $width, $y, $gridColor);
        }

        // Setup high-contrast bold text colors (Deep Slate, Dark Blue, Dark Emerald, Deep Indigo, Rose)
        $textColors = [
            imagecolorallocate($image, 15, 23, 42),    // slate-900
            imagecolorallocate($image, 30, 58, 138),   // blue-900
            imagecolorallocate($image, 6, 78, 59),     // emerald-900
            imagecolorallocate($image, 49, 46, 129),   // indigo-900
            imagecolorallocate($image, 136, 19, 55),   // rose-900
        ];

        // Draw characters with BOLD stroke overlay
        $charWidth = ($width - 24) / 5;
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $color = $textColors[$i % count($textColors)];
            
            $font = 5; // Built-in largest font
            $x = (int) (12 + ($i * $charWidth) + rand(-1, 1));
            $y = (int) (16 + rand(-2, 2));
            
            // Draw multi-pass bold offset to make characters extra thick & readable
            imagechar($image, $font, $x, $y, $char, $color);
            imagechar($image, $font, $x + 1, $y, $char, $color);
            imagechar($image, $font, $x, $y + 1, $char, $color);
            imagechar($image, $font, $x + 1, $y + 1, $char, $color);
        }

        // Add 1 light subtle wave line that doesn't block readability
        $waveColor = imagecolorallocate($image, 203, 213, 225); // slate-300
        imagesetthickness($image, 1);
        imageline($image, 0, rand(10, 20), $width, rand(30, 40), $waveColor);

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
