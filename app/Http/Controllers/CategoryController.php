<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the user's income and expense categories.
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->forUser($request->user()->id)
            ->withCount('transactions')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Category::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? 'tag',
            'color' => $validated['color'] ?? ($validated['type'] === 'income' ? '#10B981' : '#F43F5E'),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? $category->icon,
            'color' => $validated['color'] ?? $category->color,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category if it has no linked transactions.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        if ($category->transactions()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori yang sudah memiliki riwayat transaksi tidak dapat dihapus.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori berhasil dihapus.');
    }
}
