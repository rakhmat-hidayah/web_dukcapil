<?php

namespace App\Services;

use App\Models\User;

class RoleRedirectService
{
    /**
     * Determine the appropriate landing dashboard URL for a given user based on their role.
     */
    public static function getHomeUrl(?User $user): string
    {
        if (!$user) {
            return route('admin.login');
        }

        // Administrators with full system overview access
        if ($user->hasAnyRole(['Administrator Utama', 'Super Administrator', 'Administrator Website', 'Website Administrator'])) {
            return route('admin.dashboard');
        }

        // 1. Petugas Pengambilan KTP-el
        if ($user->hasRole('Petugas Pengambilan KTP-el') || $user->hasRole('petugas_pengambilan_ktpel')) {
            return route('admin.handover.dashboard');
        }

        // 2. Petugas Verifikasi / Registrasi KTP-el
        if ($user->hasRole('Petugas Verifikasi / Registrasi KTP-el') || $user->hasRole('Queue Officer') || $user->hasRole('Queue Verifier')) {
            return route('admin.queue.dashboard');
        }

        // 3. Petugas Pengarsipan
        if ($user->hasRole('Petugas Pengarsipan') || $user->hasRole('operator_arsip')) {
            return route('admin.archive.dashboard');
        }

        // 4. Petugas Pengaduan
        if ($user->hasRole('Petugas Pengaduan') || $user->hasRole('Complaint Officer')) {
            return route('admin.complaints.index');
        }

        // 5. Admin PPID
        if ($user->hasRole('Admin PPID') || $user->hasRole('PPID Admin')) {
            return route('admin.ppid.pages.index');
        }

        // 6. Editor Konten / Operator Berita
        if ($user->hasRole('Editor Konten') || $user->hasRole('Content Editor') || $user->hasRole('Operator Berita') || $user->hasRole('News Operator')) {
            return route('admin.news.index');
        }

        // 7. Pengelola Media
        if ($user->hasRole('Pengelola Media') || $user->hasRole('Media Manager')) {
            return route('admin.files.index');
        }

        // 8. Operator Publikasi
        if ($user->hasRole('Operator Publikasi') || $user->hasRole('Publication Operator')) {
            return route('admin.downloads.index');
        }

        return route('admin.dashboard');
    }
}
