<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TwoFactorController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Display the Security management page (2FA, Password, Sessions).
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();

        // Count remaining recovery codes
        $recoveryCodes = $user->getRecoveryCodesDecrypted();
        $recoveryCodesRemaining = count($recoveryCodes);

        return Inertia::render('Admin/Security/Index', [
            'twoFactorEnabled'       => $user->hasTwoFactorEnabled(),
            'twoFactorConfirmedAt'   => $user->two_factor_confirmed_at?->format('d M Y H:i'),
            'twoFactorLastUsedAt'    => $user->two_factor_last_used_at?->format('d M Y H:i') ?? '—',
            'requiresTwoFactor'      => $user->requiresTwoFactorByRole(),
            'recoveryCodesRemaining' => $recoveryCodesRemaining,
            'userRole'               => $user->roles->first()?->name ?? 'Editor Konten',
            'issuer'                 => config('two-factor.issuer', 'Disdukcapil Kabupaten Dompu'),
        ]);
    }

    /**
     * Step 1: Generate 2FA Secret Key & QR Code SVG for user setup modal.
     */
    public function setup(Request $request)
    {
        $user = $request->user();

        // If already enabled, prevent regenerating secret without disabling first
        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => '2FA sudah aktif pada akun Anda.',
            ], 422);
        }

        $secret     = $this->twoFactorService->generateSecretKey();
        $qrCodeSvg  = $this->twoFactorService->getQrCodeSvg($user, $secret);
        $otpauthUrl = $this->twoFactorService->getOtpAuthUrl($user, $secret);

        ActivityLogger::log(
            "Membuka dialog konfigurasi 2FA TOTP (QR Code dibuat)",
            $user,
            '2fa_qr_generated',
            ['username' => $user->username]
        );

        return response()->json([
            'secret'     => $secret,
            'qrCodeSvg'  => $qrCodeSvg,
            'otpauthUrl' => $otpauthUrl,
            'issuer'     => config('two-factor.issuer', 'Disdukcapil Kabupaten Dompu'),
        ]);
    }

    /**
     * Step 2: Confirm first 6-digit OTP code & activate 2FA.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'secret' => 'required|string',
            'otp'    => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP 6 digit wajib diisi.',
            'otp.size'     => 'Kode OTP harus tepat 6 digit.',
        ]);

        $user = $request->user();

        $recoveryCodes = $this->twoFactorService->enableTwoFactor(
            $user,
            $request->secret,
            $request->otp
        );

        // Store recovery codes transiently in session so Vue modal can display them once
        return redirect()->route('admin.security.index')->with([
            'success'       => 'Autentikasi Dua Faktor (2FA) berhasil diaktifkan!',
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable 2FA after password + current OTP verification.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
            'otp'      => 'required|string',
        ], [
            'password.required' => 'Kata sandi saat ini wajib diisi.',
            'otp.required'      => 'Kode OTP atau Kode Pemulihan wajib diisi.',
        ]);

        $user = $request->user();

        $this->twoFactorService->disableTwoFactor(
            $user,
            $request->password,
            $request->otp
        );

        Cookie::queue(Cookie::forget(AuthController::TWO_FACTOR_REMEMBER_COOKIE));

        return redirect()->route('admin.security.index')->with(
            'success',
            'Autentikasi Dua Faktor (2FA) berhasil dinonaktifkan.'
        );
    }

    /**
     * Regenerate 10 new recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return redirect()->back()->withErrors([
                'password' => 'Kata sandi saat ini tidak valid.',
            ]);
        }

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->back()->with('error', '2FA belum diaktifkan.');
        }

        $codes = $user->generateRecoveryCodes();

        ActivityLogger::log(
            "Memperbarui (regenerasi) 10 Kode Pemulihan 2FA",
            $user,
            '2fa_recovery_regenerated',
            ['username' => $user->username]
        );

        return redirect()->route('admin.security.index')->with([
            'success'       => '10 Kode Pemulihan baru berhasil dibuat.',
            'recoveryCodes' => $codes,
        ]);
    }

    /**
     * Super Admin resets 2FA for another user.
     */
    public function adminReset(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'admin_password' => 'required|string',
            'admin_otp'      => 'nullable|string',
            'reason'         => 'required|string|max:500',
        ], [
            'admin_password.required' => 'Kata sandi Anda wajib diisi.',
            'reason.required'         => 'Alasan pembatalan 2FA wajib diisi.',
        ]);

        $adminUser = $request->user();

        $this->twoFactorService->adminResetTwoFactor(
            $adminUser,
            $user,
            $request->admin_password,
            $request->admin_otp ?? '',
            $request->reason
        );

        return redirect()->back()->with(
            'success',
            "2FA untuk operator '@{$user->username}' berhasil di-reset oleh Super Admin."
        );
    }
}
