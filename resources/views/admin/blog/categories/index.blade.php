@extends('layouts.admin')

@section('title', 'Blog Categories')
@section('page_title', 'Research Blog Categories')

@section('content')
<div x-data="{
    createModal: false,
    editModal: false,
    deleteModal: false,
    editCategory: { id: null, name: '', slug: '', description: '', color: 'primary', sort_order: 0, is_active: true },
    deleteCategory: { id: null, name: '' },
    openEdit(category) {
        this.editCategory = { ...category };
        this.editModal = true;
    },
    openDelete(category) {
        this.deleteCategory = { ...category };
        this.deleteModal = true;
    }
}" class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-xs text-slate-300">
            <span class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 font-semibold">
                Total: {{ $totalCategories }}
            </span>
            <span class="px-3 py-1.5 rounded-lg bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 font-semibold">
                Active: {{ $activeCategories }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('admin.blog.categories.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search category name or slug..."
                       class="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none w-56">
                <button type="submit" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors">
                    Search
                </button>
            </form>

            <a href="{{ route('admin.blog.articles.index') }}"
               class="py-2 px-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors">
                Manage Articles
            </a>

            <button @click="createModal = true"
                    type="button"
                    class="py-2 px-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 transition-all duration-200 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Category</span>
            </button>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="glass-card rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 border-b border-slate-800">
                        <th class="py-3.5 px-5 font-semibold">Category</th>
                        <th class="py-3.5 px-5 font-semibold">Slug</th>
                        <th class="py-3.5 px-5 font-semibold">Color</th>
                        <th class="py-3.5 px-5 font-semibold">Articles</th>
                        <th class="py-3.5 px-5 font-semibold">Order</th>
                        <th class="py-3.5 px-5 font-semibold">Status</th>
                        <th class="py-3.5 px-5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-white text-sm">{{ $category->name }}</div>
                                @if ($category->description)
                                    <div class="text-xs text-slate-400 mt-0.5 max-w-md truncate">{{ $category->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 font-mono text-indigo-300">{{ $category->slug }}</td>
                            <td class="py-3.5 px-5">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-200 border border-slate-700">
                                    {{ $category->color }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-semibold text-slate-200">{{ $category->articles_count }}</td>
                            <td class="py-3.5 px-5 text-slate-400">{{ $category->sort_order }}</td>
                            <td class="py-3.5 px-5">
                                @if ($category->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-950/70 text-emerald-300 border border-emerald-500/30">Active</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-900 text-slate-400 border border-slate-700">Hidden</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button"
                                            @click="openEdit(@js([
                                                'id' => $category->id,
                                                'name' => $category->name,
                                                'slug' => $category->slug,
                                                'description' => $category->description ?? '',
                                                'color' => $category->color,
                                                'sort_order' => $category->sort_order,
                                                'is_active' => $category->is_active,
                                            ]))"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-xs transition-colors">
                                        Edit
                                    </button>
                                    <button type="button"
                                            @click="openDelete(@js(['id' => $category->id, 'name' => $category->name]))"
                                            class="px-2.5 py-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border border-rose-500/20 font-medium text-xs transition-colors">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                No blog categories found. Create one above or import defaults from the Blog Articles page.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/80 bg-slate-900/40">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="createModal = false"></div>
            <div class="relative w-full max-w-md glass-card rounded-2xl border border-slate-700/60 p-6 shadow-2xl">
                <h3 class="text-base font-bold text-white mb-4">Create Blog Category</h3>
                <form method="POST" action="{{ route('admin.blog.categories.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Category Name (Arabic / Display)</label>
                        <input type="text" name="name" required placeholder="e.g. التوكن العربي"
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Slug (Optional — auto-generated if empty)</label>
                        <input type="text" name="slug" placeholder="e.g. tokenomics"
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Badge Color</label>
                            <select name="color" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                                <option value="primary">Primary (Cyan)</option>
                                <option value="secondary">Secondary (Teal)</option>
                                <option value="emerald">Emerald</option>
                                <option value="amber">Amber</option>
                                <option value="indigo">Indigo</option>
                                <option value="rose">Rose</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded bg-slate-900 border-slate-700 text-indigo-600">
                        <span>Visible on public blog filters</span>
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editModal = false"></div>
            <div class="relative w-full max-w-md glass-card rounded-2xl border border-slate-700/60 p-6 shadow-2xl">
                <h3 class="text-base font-bold text-white mb-4">Edit Blog Category</h3>
                <form method="POST" :action="'{{ url('/admin/blog/categories') }}/' + editCategory.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Category Name</label>
                        <input type="text" name="name" x-model="editCategory.name" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Slug</label>
                        <input type="text" name="slug" x-model="editCategory.slug"
                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
                        <textarea name="description" rows="2" x-model="editCategory.description"
                                  class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Badge Color</label>
                            <select name="color" x-model="editCategory.color" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                                <option value="primary">Primary (Cyan)</option>
                                <option value="secondary">Secondary (Teal)</option>
                                <option value="emerald">Emerald</option>
                                <option value="amber">Amber</option>
                                <option value="indigo">Indigo</option>
                                <option value="rose">Rose</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" x-model="editCategory.sort_order" min="0"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="editCategory.is_active" class="rounded bg-slate-900 border-slate-700 text-indigo-600">
                        <span>Visible on public blog filters</span>
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="deleteModal = false"></div>
            <div class="relative w-full max-w-sm glass-card rounded-2xl border border-slate-700/60 p-6 shadow-2xl">
                <h3 class="text-base font-bold text-white mb-2">Delete Category</h3>
                <p class="text-xs text-slate-400 mb-5">
                    Are you sure you want to delete <span class="text-white font-semibold" x-text="deleteCategory.name"></span>? Articles in this category will remain intact without a category assignment.
                </p>
                <form method="POST" :action="'{{ url('/admin/blog/categories') }}/' + deleteCategory.id" class="flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

