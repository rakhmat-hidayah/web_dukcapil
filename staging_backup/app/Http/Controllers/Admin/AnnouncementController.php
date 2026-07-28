<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AnnouncementController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Announcement::with('author')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        if ($request->status === 'published' && !$request->published_at) {
            $data['published_at'] = now();
        }

        $announcement = Announcement::create($data);

        ActivityLogger::log("Membuat pengumuman: {$announcement->title}", $announcement, 'create_announcement');

        return redirect()->back()->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:low,normal,high,urgent',
            'status' => 'required|in:draft,published,archived',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update($request->except('user_id'));

        ActivityLogger::log("Memperbarui pengumuman: {$announcement->title}", $announcement, 'update_announcement');

        return redirect()->back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        ActivityLogger::log("Menghapus pengumuman: {$title}", null, 'delete_announcement');

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
