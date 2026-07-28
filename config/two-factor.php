<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication (2FA TOTP) Configuration
    |--------------------------------------------------------------------------
    |
    | Enterprise RFC-6238 Time-based One-Time Password configuration.
    | Compatible with Google Authenticator, Microsoft Authenticator, Authy,
    | 2FAS, Aegis, and any RFC-6238 compliant authenticator application.
    |
    */

    'enabled' => env('TWO_FACTOR_ENABLED', true),

    'required' => env('TWO_FACTOR_REQUIRED', false),

    /*
    |--------------------------------------------------------------------------
    | Roles Requiring 2FA
    |--------------------------------------------------------------------------
    |
    | List of roles that MUST enable 2FA before accessing administrative areas.
    | Comma-separated in .env or array below.
    |
    */
    'required_roles' => array_filter(
        explode(',', env('TWO_FACTOR_ROLES', 'Super Administrator,Website Administrator'))
    ),

    /*
    |--------------------------------------------------------------------------
    | Issuer Name
    |--------------------------------------------------------------------------
    |
    | The issuer displayed inside authenticator apps (e.g. "Disdukcapil Dompu")
    |
    */
    'issuer' => env('TWO_FACTOR_ISSUER', 'Disdukcapil Kabupaten Dompu'),

    /*
    |--------------------------------------------------------------------------
    | OTP Window & Leeway
    |--------------------------------------------------------------------------
    |
    | Window size for clock skew tolerance. 1 = +-30 seconds (total 90s window).
    |
    */
    'window' => 1,

    /*
    |--------------------------------------------------------------------------
    | Max Failed OTP Attempts Before Lockout
    |--------------------------------------------------------------------------
    |
    | Number of failed OTP verification attempts before locking challenge for
    | 10 minutes.
    |
    */
    'max_attempts' => 5,
    'lockout_minutes' => 10,

    /*
    |--------------------------------------------------------------------------
    | Recovery Codes Count & Format
    |--------------------------------------------------------------------------
    |
    | Number of one-time recovery codes generated per setup.
    |
    */
    'recovery_codes_count' => 10,
    'recovery_code_prefix' => 'DKCP-',

];
