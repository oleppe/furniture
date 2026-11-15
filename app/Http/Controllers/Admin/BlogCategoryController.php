<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of blog categories.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim();

        $query = BlogCategory::query()->withCount('articles');

        if ($search->isNotEmpty()) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog.categories.index', [
            'categories' => $categories,
            'search' => $search,
            'totalCategories' => BlogCategory::count(),
            'activeCategories' => BlogCategory::where('is_active', true)->count(),
        ]);
    }

    /**
     * Store a newly created blog category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', Rule::in(['primary', 'secondary', 'emerald', 'amber', 'indigo', 'rose'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $this->resolveSlug($validated['slug'] ?? null, $validated['name']);

        if (BlogCategory::where('slug', $slug)->exists()) {
            $slug .= '-'.substr((string) now()->timestamp, -4);
        }

        BlogCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', "Category '{$validated['name']}' was successfully created.");
    }

    /**
     * Update the specified blog category.
     */
    public function update(Request $request, BlogCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', Rule::in(['primary', 'secondary', 'emerald', 'amber', 'indigo', 'rose'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $this->resolveSlug($validated['slug'] ?? null, $validated['name']);

        if (BlogCategory::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            return back()->withErrors(['slug' => 'This slug is already taken by another category.']);
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', "Category '{$category->name}' was successfully updated.");
    }

    /**
     * Delete the specified blog category.
     */
    public function destroy(BlogCategory $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', "Category '{$name}' was deleted.");
    }

    /**
     * Resolve a clean URL slug from user input or category name.
     */
    private function resolveSlug(?string $providedSlug, string $fallbackName): string
    {
        $raw = trim((string) ($providedSlug ?: $fallbackName));
        $ascii = Str::slug($raw);

        if ($ascii !== '') {
            return $ascii;
        }

        $normalized = preg_replace('/\s+/u', '-', $raw) ?? '';
        $normalized = trim($normalized, '-');

        return $normalized !== '' ? $normalized : 'category-'.now()->timestamp;
    }
}
