<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ComplaintController extends Controller
{
    /**
     * Show the public complaint submission form.
     */
    public function create(): InertiaResponse
    {
        $categories = ComplaintCategory::active()->get(['id', 'name', 'slug', 'icon', 'color', 'description']);

        $num1 = rand(1, 15);
        $num2 = rand(1, 15);
        $answer = $num1 + $num2;
        $hash = hash('sha256', $answer . config('app.key'));

        return Inertia::render('Public/Complaint', [
            'categories' => $categories,
            'captcha'    => [
                'num1' => $num1,
                'num2' => $num2,
                'hash' => $hash,
            ],
        ]);
    }

    /**
     * Process and store a new complaint submission.
     */
    public function store(Request $request): RedirectResponse|InertiaResponse
    {
        $data = $request->validate([
            'submitter_name'        => 'required|string|max:150',
            'submitter_phone'       => 'nullable|string|max:20',
            'submitter_email'       => 'nullable|email|max:150',
            'is_anonymous'          => 'boolean',
            'complaint_category_id' => 'nullable|exists:complaint_categories,id',
            'subject'               => 'required|string|max:255',
            'message'               => 'required|string|min:20|max:5000',
            'attachment'            => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'captcha_answer'        => 'required|integer',
            'captcha_hash'          => 'required|string',
        ]);

        // Verify math CAPTCHA — hash is sha256(answer + APP_KEY salt)
        $expectedHash = hash('sha256', $data['captcha_answer'] . config('app.key'));
        if ($data['captcha_hash'] !== $expectedHash) {
            return back()->withErrors(['captcha_answer' => 'Jawaban CAPTCHA salah. Silakan coba lagi.'])->withInput();
        }

        // Handle optional attachment
        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file           = $request->file('attachment');
            $attachmentPath = $file->store('complaints/attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $complaint = Complaint::create([
            'submitter_name'        => $data['is_anonymous'] ? 'Anonim' : $data['submitter_name'],
            'submitter_phone'       => $data['is_anonymous'] ? null : ($data['submitter_phone'] ?? null),
            'submitter_email'       => $data['is_anonymous'] ? null : ($data['submitter_email'] ?? null),
            'is_anonymous'          => $data['is_anonymous'] ?? false,
            'complaint_category_id' => $data['complaint_category_id'] ?? null,
            'subject'               => $data['subject'],
            'message'               => $data['message'],
            'attachment_path'       => $attachmentPath,
            'attachment_name'       => $attachmentName,
            'status'                => 'pending',
            'ip_address'            => $request->ip(),
            'user_agent'            => $request->userAgent(),
        ]);

        return redirect()->route('public.complaint.success', $complaint->ticket_number);
    }

    /**
     * Show the submission success page with the ticket number.
     */
    public function success(string $ticket): InertiaResponse
    {
        $complaint = Complaint::where('ticket_number', $ticket)->firstOrFail();

        return Inertia::render('Public/ComplaintSuccess', [
            'ticket'  => $complaint->ticket_number,
            'subject' => $complaint->subject,
        ]);
    }

    /**
     * Show the public ticket tracking search form.
     */
    public function trackForm(): InertiaResponse
    {
        return Inertia::render('Public/ComplaintTrack');
    }

    /**
     * Look up a ticket and redirect to status page.
     */
    public function trackSearch(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_number' => 'required|string|max:20',
        ]);

        $ticket = strtoupper(trim($request->ticket_number));
        $complaint = Complaint::where('ticket_number', $ticket)->first();

        if (!$complaint) {
            return back()->withErrors(['ticket_number' => 'Nomor tiket tidak ditemukan. Periksa kembali format penulisan (contoh: DKP-2024-ABCDEF).'])->withInput();
        }

        return redirect()->route('public.complaint.status', $ticket);
    }

    /**
     * Show public-facing complaint status and admin replies timeline.
     */
    public function status(string $ticket): InertiaResponse
    {
        $complaint = Complaint::with(['category', 'publicReplies.user'])
            ->where('ticket_number', strtoupper($ticket))
            ->firstOrFail();

        return Inertia::render('Public/ComplaintStatus', [
            'complaint'    => [
                'ticket_number'   => $complaint->ticket_number,
                'subject'         => $complaint->subject,
                'message'         => $complaint->message,
                'status'          => $complaint->status,
                'status_label'    => $complaint->status_label,
                'status_color'    => $complaint->status_color,
                'category'        => $complaint->category ? $complaint->category->only('name', 'icon', 'color') : null,
                'is_anonymous'    => $complaint->is_anonymous,
                'submitter_name'  => $complaint->is_anonymous ? 'Anonim' : $complaint->submitter_name,
                'created_at'      => $complaint->created_at,
                'resolved_at'     => $complaint->resolved_at,
            ],
            'replies'      => $complaint->publicReplies->map(fn ($r) => [
                'id'         => $r->id,
                'message'    => $r->message,
                'type'       => $r->type,
                'old_status' => $r->old_status,
                'new_status' => $r->new_status,
                'user_name'  => $r->user ? $r->user->name : 'Sistem',
                'created_at' => $r->created_at,
            ]),
            'statusLabels' => Complaint::STATUS_LABELS,
        ]);
    }
}
