<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // LIST & FILTER
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request): InertiaResponse
    {
        $query = Complaint::with(['category', 'assignee', 'latestNotificationLog'])
            ->orderByRaw("FIELD(status, 'pending', 'in_review', 'in_progress', 'resolved', 'rejected')")
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('complaint_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%")
                    ->orWhere('submitter_name', 'like', "%{$q}%");
            });
        }

        $complaints  = $query->paginate(20)->withQueryString();
        $categories  = ComplaintCategory::active()->get(['id', 'name']);
        $statusCounts = Complaint::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Complaints/Index', [
            'complaints'         => $complaints,
            'categories'         => $categories,
            'statusCounts'       => $statusCounts,
            'statusLabels'       => Complaint::STATUS_LABELS,
            'statusColors'       => Complaint::STATUS_COLORS,
            'notificationConfig' => [
                'enabled'   => (bool) config('complaint.notifications.enabled', true),
                'recipient' => config('complaint.notifications.email'),
            ],
            'filters'            => $request->only(['status', 'category', 'search']),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // DETAIL & REPLY
    // ─────────────────────────────────────────────────────────────

    public function show(Complaint $complaint): InertiaResponse
    {
        // Mark as read if first time opening
        if (!$complaint->read_at) {
            $complaint->update(['read_at' => now()]);
        }

        $complaint->load(['category', 'assignee', 'replies.user']);

        return Inertia::render('Complaints/Show', [
            'complaint'    => $complaint,
            'statusLabels' => Complaint::STATUS_LABELS,
            'statusColors' => Complaint::STATUS_COLORS,
        ]);
    }

    /**
     * Securely stream private complaint attachment to authorized officers.
     */
    public function attachment(Complaint $complaint): StreamedResponse
    {
        if (!$complaint->attachment_path) {
            abort(404, 'Lampiran pengaduan tidak ditemukan.');
        }

        $path = $complaint->attachment_path;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Berkas fisik lampiran tidak ditemukan di penyimpanan server.');
        }

        $stream = Storage::disk('local')->readStream($path);
        $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';
        $size = Storage::disk('local')->size($path);
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $complaint->attachment_name ?: basename($path));

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'           => $mime,
            'Content-Length'         => $size,
            'Cache-Control'          => 'private, no-cache, no-store, must-revalidate',
            'Pragma'                 => 'no-cache',
            'Expires'                => '0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition'    => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Admin posts a reply (visible to submitter) or internal note (hidden).
     */
    public function reply(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'message'                => 'required|string|min:5|max:3000',
            'is_visible_to_submitter' => 'boolean',
        ]);

        ComplaintReply::create([
            'complaint_id'            => $complaint->id,
            'user_id'                 => auth()->id(),
            'message'                 => $data['message'],
            'type'                    => 'admin_reply',
            'is_visible_to_submitter' => $data['is_visible_to_submitter'] ?? true,
        ]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Change complaint status and auto-log a status change record.
     */
    public function changeStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,in_review,in_progress,resolved,rejected',
            'note'   => 'nullable|string|max:1000',
        ]);

        $oldStatus = $complaint->status;
        $newStatus = $data['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('error', 'Status sudah sama, tidak ada perubahan.');
        }

        $complaint->update([
            'status'      => $newStatus,
            'resolved_at' => in_array($newStatus, ['resolved', 'rejected']) ? now() : $complaint->resolved_at,
        ]);

        // Log the status change
        ComplaintReply::create([
            'complaint_id'            => $complaint->id,
            'user_id'                 => auth()->id(),
            'message'                 => $data['note'] ?? ('Status diubah dari "' . Complaint::STATUS_LABELS[$oldStatus] . '" menjadi "' . Complaint::STATUS_LABELS[$newStatus] . '"'),
            'type'                    => 'status_change',
            'old_status'              => $oldStatus,
            'new_status'              => $newStatus,
            'is_visible_to_submitter' => true,
        ]);

        return back()->with('success', 'Status pengaduan berhasil diperbarui.');
    }

    /**
     * Assign complaint to a specific admin.
     */
    public function assign(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $complaint->update(['assigned_to' => $data['assigned_to']]);

        return back()->with('success', 'Pengaduan berhasil ditugaskan.');
    }

    // ─────────────────────────────────────────────────────────────
    // CATEGORIES CRUD
    // ─────────────────────────────────────────────────────────────

    public function categories(): InertiaResponse
    {
        $categories = ComplaintCategory::withCount('complaints')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Complaints/Categories', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|max:100|unique:complaint_categories,slug',
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        ComplaintCategory::create($data);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, ComplaintCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $category->update($data);
        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroyCategory(ComplaintCategory $category): RedirectResponse
    {
        if ($category->complaints()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki pengaduan terkait.');
        }
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }
}
