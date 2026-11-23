<?php

namespace App\Mail;

use App\Models\Applicant\ApplicantAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantLoginApologyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ApplicantAccount|array $account,
        public ?string $temporaryPassword = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Akun Aktif & Informasi Akses Masuk Portal - Disdukcapil Kabupaten Dompu',
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
        $displayName = is_array($this->account)
            ? ($this->account['name'] ?? 'Warga / Pemohon')
            : ($this->account->name ?: 'Warga / Pemohon');

        $maskedNik = is_array($this->account)
            ? ($this->account['masked_nik'] ?? '5205************')
            : ($this->account->masked_nik ?: '5205************');

        $portalLoginUrl = 'https://dukcapil.dompukab.go.id/layanan/pemohon/masuk';
        $forgotPasswordUrl = 'https://dukcapil.dompukab.go.id/layanan/pemohon/pemulihan';

        $tempPasswordSection = '';
        if ($this->temporaryPassword) {
            $tempPasswordSection = <<<HTML
            <div style="margin: 20px 0; background-color: #fefce8; border: 1px solid #fde047; border-radius: 12px; padding: 14px 18px;">
                <p style="margin: 0 0 6px; font-size: 12px; font-weight: 800; color: #854d0e;">🔑 Kata Sandi Sementara Anda:</p>
                <p style="margin: 0 0 6px; font-size: 16px; font-family: monospace; font-weight: 800; color: #713f12; background: #ffffff; padding: 6px 12px; border-radius: 8px; display: inline-block; border: 1px dashed #ca8a04;">{$this->temporaryPassword}</p>
                <p style="margin: 6px 0 0; font-size: 12px; color: #854d0e; line-height: 1.5;">Silakan masuk menggunakan kata sandi sementara di atas, kemudian segera ubah kata sandi baru demi keamanan akun Anda.</p>
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
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
  <div style="max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    
    <!-- Header Dinas -->
    <div style="background-color: #1e3a8a; padding: 24px; text-align: center; color: #ffffff;">
      <h2 style="margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">DISDUKCAPIL KABUPATEN DOMPU</h2>
      <p style="margin: 4px 0 0; font-size: 13px; color: #bfdbfe;">Pelayanan Administrasi Kependudukan Online</p>
    </div>

    <!-- Isi Email -->
    <div style="padding: 32px 24px; font-size: 14px; line-height: 1.6;">
      <p style="margin-top: 0;">Yth. Bapak/Ibu <strong>{$displayName}</strong>,</p>

      <div style="margin: 18px 0; text-align: center;">
        <span style="display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: 800; letter-spacing: 0.5px;">
          ✓ AKUN AKTIF & TERVERIFIKASI SAH
        </span>
      </div>

      <!-- Permohonan Maaf & Info Perbaikan -->
      <div style="margin: 20px 0; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px 18px; color: #166534;">
        <p style="margin: 0 0 8px; font-size: 13px; font-weight: 800; color: #15803d;">
          🙏 Permohonan Maaf & Informasi Pembaruan Sistem:
        </p>
        <p style="margin: 0; font-size: 13px; line-height: 1.55; color: #14532d;">
          Kami dari Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu menyampaikan permohonan maaf yang sebesar-besarnya atas kendala teknis yang sempat terjadi saat Anda mencoba masuk (login) ke Portal Pemohon sebelumnya. Kendala teknis tersebut <strong>telah berhasil diperbaiki dan diselesaikan</strong> oleh Tim IT Disdukcapil Kabupaten Dompu.
        </p>
      </div>

      <p style="margin: 16px 0; color: #334155;">
        Akun pemohon Anda saat ini telah <strong>berstatus aktif penuh</strong> dan data kependudukan Anda telah <strong>diverifikasi sah</strong> oleh Petugas Disdukcapil Kabupaten Dompu. Anda kini dapat segera masuk (login) untuk mengajukan pelayanan administrasi kependudukan (KTP-el Online) maupun pemesanan nomor antrean loket.
      </p>

      <!-- Rincian Akun -->
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

      {$tempPasswordSection}

      <!-- Petunjuk Login -->
      <div style="margin: 22px 0; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px 18px;">
        <p style="margin: 0 0 10px; font-size: 13px; font-weight: 800; color: #1e293b;">
          📱 Panduan Masuk ke Portal Pemohon:
        </p>
        <ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #334155; line-height: 1.6;">
          <li style="margin-bottom: 6px;">Akses laman resmi masuk pemohon melalui ponsel pintar (smartphone) Anda: <a href="{$portalLoginUrl}" style="color: #2563eb; font-weight: 700; word-break: break-all;">dukcapil.dompukab.go.id</a></li>
          <li style="margin-bottom: 6px;">Masukkan 16 digit NIK dan kata sandi (password) Anda.</li>
          <li>
            <em>Catatan:</em> Apabila Anda lupa kata sandi atau mengalami kendala kata sandi lama, silakan klik menu <strong>"Ganti HP / Lupa Password?"</strong> pada halaman login untuk mengatur ulang kata sandi baru secara instan melalui kode OTP yang dikirimkan ke email Anda.
          </li>
        </ol>
      </div>

      <!-- Tombol CTA -->
      <div style="margin: 30px 0; text-align: center;">
        <a href="{$portalLoginUrl}" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 13px 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.25);">
          Masuk ke Portal Layanan Sekarang &rarr;
        </a>
      </div>

      <!-- Banner Bebas Pungli & Calo -->
      <div style="background-color: #eff6ff; border-radius: 10px; padding: 12px 16px; border: 1px solid #bfdbfe; font-size: 12px; color: #1e40af; line-height: 1.5; text-align: center;">
        🛡️ <strong>Layanan 100% GRATIS (Rp 0):</strong> Seluruh pelayanan administrasi kependudukan di Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu bebas dari biaya apa pun dan bebas dari percaloan.
      </div>

      <p style="font-size: 12px; color: #94a3b8; margin-top: 26px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
        Email ini dikirimkan secara resmi oleh Sistem Informasi Administrasi Kependudukan Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu.
      </p>
    </div>

    <!-- Footer -->
    <div style="background-color: #f8fafc; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
      Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu<br>
      Jl. Soekarno-Hatta No. 09, Dompu, Nusa Tenggara Barat 84211<br>
      Laman Resmi: <a href="https://dukcapil.dompukab.go.id" style="color: #64748b; text-decoration: none;">https://dukcapil.dompukab.go.id</a>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
