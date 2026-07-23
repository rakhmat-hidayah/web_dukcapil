<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = Faq::orderBy('sort_order');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('question', 'like', "%{$request->search}%")
                  ->orWhere('answer', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $faqs = $query->paginate(20)->withQueryString();

        // Get unique categories for filtering
        $categories = Faq::distinct()->whereNotNull('category')->pluck('category')->toArray();

        return Inertia::render('Admin/Faqs/Index', [
            'faqs' => $faqs,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'is_published' => 'required|boolean',
        ]);

        $maxSort = Faq::max('sort_order') ?? 0;

        $faq = Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category,
            'is_published' => $request->is_published,
            'sort_order' => $maxSort + 1,
        ]);

        ActivityLogger::log("Membuat FAQ baru: {$faq->question}", $faq, 'create_faq');

        return redirect()->back()->with('success', 'FAQ berhasil ditambahkan.');
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'is_published' => 'required|boolean',
        ]);

        $faq->update($request->only(['question', 'answer', 'category', 'is_published']));

        ActivityLogger::log("Memperbarui FAQ: {$faq->question}", $faq, 'update_faq');

        return redirect()->back()->with('success', 'FAQ berhasil diperbarui.');
    }

    /**
     * Reorder FAQs.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:faqs,id',
            'order.*.sort' => 'required|integer',
        ]);

        foreach ($request->order as $item) {
            Faq::where('id', $item['id'])->update(['sort_order' => $item['sort']]);
        }

        return redirect()->back()->with('success', 'Urutan FAQ berhasil diperbarui.');
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $question = $faq->question;
        $faq->delete();

        ActivityLogger::log("Menghapus FAQ: {$question}", null, 'delete_faq');

        return redirect()->back()->with('success', 'FAQ berhasil dihapus.');
    }
}
