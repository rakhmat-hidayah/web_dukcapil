<?php

namespace App\Mail;

use App\Models\Applicant\ApplicantAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantVerificationResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ApplicantAccount $account,
        public string $decision,
        public ?string $notes = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $isVerified = ($this->decision === ApplicantAccount::IDENTITY_VERIFIED);
        $subject = $isVerified
            ? 'Akun Pemohon Anda Telah Terverifikasi! - Disdukcapil Kabupaten Dompu'
            : 'Pemberitahuan Status Verifikasi Akun Pemohon - Disdukcapil Kabupaten Dompu';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml()
        );
    }

    /**
     * Generate HTML body.
     */
    protected function buildHtml(): string
    {
        $isVerified = ($this->decision === ApplicantAccount::IDENTITY_VERIFIED);
        $displayName = $this->account->name ?: 'Warga / Pemohon';
        $maskedNik = $this->account->masked_nik;
        $portalUrl = route('applicant.login');
        $notesText = $this->notes ? htmlspecialchars($this->notes, ENT_QUOTES, 'UTF-8') : null;

        $badgeBg = $isVerified ? '#ecfdf5' : '#fef2f2';
        $badgeBorder = $isVerified ? '#a7f3d0' : '#fecaca';
        $badgeColor = $isVerified ? '#047857' : '#b91c1c';
        $badgeText = $isVerified ? '✓ IDENTITAS TERVERIFIKASI & AKTIF' : '✕ PERLU PERBAIKAN / DITOLAK';

        $actionButton = $isVerified
            ? '<a href="' . $portalUrl . '" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.2);">Masuk ke Portal Layanan &rarr;</a>'
            : '<a href="' . $portalUrl . '" style="display: inline-block; background-color: #475569; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 12px;">Buka Portal Pemohon</a>';

        $messageContent = $isVerified
            ? '<p style="font-size: 14px; color: #1e293b; margin-top: 0;">Kabar baik! Pendaftaran dan verifikasi identitas kependudukan untuk akun Anda telah <strong>berhasil disetujui</strong> oleh Petugas Disdukcapil Kabupaten Dompu. Akun Anda kini berstatus aktif penuh dan dapat langsung digunakan untuk mengajukan layanan kependudukan dan antrian online.</p>'
            : '<p style="font-size: 14px; color: #1e293b; margin-top: 0;">Pemberitahuan: Proses verifikasi berkas pendaftaran akun pemohon Anda <strong>belum dapat disetujui</strong> oleh Petugas Disdukcapil Kabupaten Dompu.</p>';

        $notesSection = '';
        if ($notesText) {
            $notesSection = <<<HTML
            <div style="margin: 20px 0; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 18px;">
                <p style="margin: 0 0 6px; font-size: 12px; font-weight: 700; color: #92400e;">Catatan Petugas Verifikator:</p>
                <p style="margin: 0; font-size: 13px; color: #78350f; line-height: 1.5;">{$notesText}</p>
            </div>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Pemberitahuan Disdukcapil Kabupaten Dompu</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px;">
  <div style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div style="background-color: #1e3a8a; padding: 24px; text-align: center; color: #ffffff;">
      <h2 style="margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">DISDUKCAPIL KABUPATEN DOMPU</h2>
      <p style="margin: 4px 0 0; font-size: 13px; color: #bfdbfe;">Pelayanan Administrasi Kependudukan Online</p>
    </div>
    <div style="padding: 32px 24px; color: #1e293b; line-height: 1.6;">
      <p style="margin-top: 0; font-size: 14px;">Halo <strong>{$displayName}</strong>,</p>
      
      <div style="margin: 18px 0; text-align: center;">
        <span style="display: inline-block; background-color: {$badgeBg}; border: 1px solid {$badgeBorder}; color: {$badgeColor}; padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: 800; letter-spacing: 0.5px;">
          {$badgeText}
        </span>
      </div>

      {$messageContent}

      <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin: 20px 0; font-size: 13px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
          <span style="color: #64748b;">Nama Pemohon:</span>
          <strong style="color: #0f172a;">{$displayName}</strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: #64748b;">NIK:</span>
          <strong style="color: #0f172a; font-family: monospace;">{$maskedNik}</strong>
        </div>
      </div>

      {$notesSection}

      <div style="margin: 28px 0; text-align: center;">
        {$actionButton}
      </div>

      <p style="font-size: 12px; color: #94a3b8; margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
        Email ini dikirimkan secara otomatis oleh Sistem Informasi Administrasi Kependudukan Disdukcapil Kabupaten Dompu.
      </p>
    </div>
    <div style="background-color: #f8fafc; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
      Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu<br>
      Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211
    </div>
  </div>
</body>
</html>
HTML;
    }
}
