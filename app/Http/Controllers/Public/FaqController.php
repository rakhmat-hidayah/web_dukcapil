<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(Request $request): Response
    {
        $faqs = Faq::where('is_published', true)
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('question', 'like', '%' . $request->search . '%')
                   ->orWhere('answer', 'like', '%' . $request->search . '%');
            }))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        $categories = Faq::where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return Inertia::render('Public/Faq', [
            'faqs'       => $faqs,
            'categories' => $categories,
            'filters'    => $request->only(['category', 'search']),
        ]);
    }
}
