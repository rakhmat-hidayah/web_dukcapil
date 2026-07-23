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
    public static function generateImage(int $width = 150, int $height = 40): string
    {
        // 1. Create a random code
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // exclude confusing chars like 1, 0, I, O
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        Session::put('captcha_image_code', strtolower($code));

        // 2. Generate GD image
        $image = imagecreatetruecolor($width, $height);
        
        // Setup colors
        $bg = imagecolorallocate($image, 243, 244, 246); // gray-100
        $textColors = [
            imagecolorallocate($image, 14, 145, 235), // primary-500
            imagecolorallocate($image, 3, 92, 163),  // primary-700
            imagecolorallocate($image, 202, 138, 4),  // accent-500
            imagecolorallocate($image, 87, 83, 78),   // secondary-600
        ];
        
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        // Add some noise (lines)
        for ($i = 0; $i < 4; $i++) {
            $lineColor = imagecolorallocate($image, rand(180, 220), rand(180, 220), rand(180, 220));
            imagesetthickness($image, rand(1, 2));
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }

        // Add some noise (dots)
        for ($i = 0; $i < 100; $i++) {
            $dotColor = imagecolorallocate($image, rand(150, 220), rand(150, 220), rand(150, 220));
            imagesetpixel($image, rand(0, $width), rand(0, $height), $dotColor);
        }

        // Draw characters
        $charWidth = $width / 6;
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $color = $textColors[array_rand($textColors)];
            
            // Draw character using PHP built-in fonts (1-5) since standard hosting might lack TTF fonts.
            // Font 5 is the largest built-in font.
            $font = 5;
            $x = ($i + 0.8) * $charWidth;
            $y = ($height - 15) / 2 + rand(-3, 3);
            
            imagechar($image, $font, $x, $y, $char, $color);
        }

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
