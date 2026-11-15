@extends('layouts.admin')

@section('title', 'Blog Articles')
@section('page_title', 'Research Blog Articles & Studies')

@section('content')
<div x-data="{
    deleteModal: false,
    deleteArticle: { id: null, title: '' },
    openDelete(article) {
        this.deleteArticle = { ...article };
        this.deleteModal = true;
    }
}" class="space-y-6">

    <!-- Top Filter & Action Bar -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-900/90 border border-slate-800">
            <a href="{{ route('admin.blog.articles.index') }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status->isEmpty() ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">
                All ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.blog.articles.index', ['status' => 'published']) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status->value() === 'published' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Published ({{ $counts['published'] }})
            </a>
            <a href="{{ route('admin.blog.articles.index', ['status' => 'draft']) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status->value() === 'draft' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Drafts ({{ $counts['draft'] }})
            </a>
            <a href="{{ route('admin.blog.articles.index', ['status' => 'featured']) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status->value() === 'featured' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Featured ({{ $counts['featured'] }})
            </a>
        </div>

        <!-- Search + Actions -->
        <div class="flex flex-wrap items-center gap-2.5">
            <form method="GET" action="{{ route('admin.blog.articles.index') }}" class="flex flex-wrap items-center gap-2">
                @if ($status->isNotEmpty())
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <select name="category" class="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($categoryId === $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search title, report ID..."
                       class="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none w-48">
                <button type="submit" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                    Filter
                </button>
            </form>

            <form method="POST" action="{{ route('admin.blog.articles.seed') }}">
                @csrf
                <button type="submit" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-colors">
                    Import Default Studies
                </button>
            </form>

            <a href="{{ route('admin.blog.articles.create') }}"
               class="py-2 px-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/25 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Research Article</span>
            </a>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="glass-card rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 border-b border-slate-800">
                        <th class="py-3.5 px-5 font-semibold">Article & Report ID</th>
                        <th class="py-3.5 px-5 font-semibold">Category</th>
                        <th class="py-3.5 px-5 font-semibold">Embedded Blocks</th>
                        <th class="py-3.5 px-5 font-semibold">Status</th>
                        <th class="py-3.5 px-5 font-semibold">Published</th>
                        <th class="py-3.5 px-5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($articles as $article)
                        @php
                            $chartsCount = count($article->blocks['charts'] ?? []);
                            $tablesCount = count($article->blocks['tables'] ?? []) + (count($article->comparison_rows ?? []) > 0 ? 1 : 0);
                            $quotesCount = count($article->blocks['quoted_models'] ?? []);
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="py-3.5 px-5 max-w-md">
                                <div class="font-bold text-white text-sm leading-snug">{{ $article->title }}</div>
                                <div class="flex flex-wrap items-center gap-2 mt-1 font-mono text-[11px] text-slate-400">
                                    <span>{{ $article->report_id ?: 'N/A' }}</span>
                                    <span>•</span>
                                    <span class="text-indigo-300">/blog/{{ $article->slug }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                @if ($article->category)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-200 border border-slate-700">
                                        {{ $article->category->name }}
                                    </span>
                                @else
                                    <span class="text-slate-500">Uncategorized</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                    <span class="px-2 py-0.5 rounded bg-cyan-950/60 text-cyan-300 border border-cyan-500/30">
                                        {{ $chartsCount }} Charts
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-amber-950/60 text-amber-300 border border-amber-500/30">
                                        {{ $tablesCount }} Tables
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-950/60 text-emerald-300 border border-emerald-500/30">
                                        {{ $quotesCount }} Quotes
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.blog.articles.toggle-published', $article) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold border cursor-pointer {{ $article->is_published ? 'bg-emerald-950/70 text-emerald-300 border-emerald-500/30' : 'bg-slate-900 text-slate-400 border-slate-700' }}">
                                            {{ $article->is_published ? 'Published' : 'Draft' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.blog.articles.toggle-featured', $article) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold border cursor-pointer {{ $article->is_featured ? 'bg-indigo-950/70 text-indigo-300 border-indigo-500/40' : 'bg-slate-900 text-slate-500 border-slate-800 hover:text-slate-300' }}">
                                            {{ $article->is_featured ? 'Featured' : 'Standard' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-slate-400">
                                {{ $article->published_at?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('blog.show', $article->slug) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-slate-300 hover:text-white text-xs">
                                        View
                                    </a>
                                    <a href="{{ route('admin.blog.articles.edit', $article) }}"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-xs">
                                        Edit
                                    </a>
                                    <button type="button"
                                            @click="openDelete(@js(['id' => $article->id, 'title' => $article->title]))"
                                            class="px-2.5 py-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border border-rose-500/20 font-medium text-xs">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                No research articles in the database yet. Click <strong>New Research Article</strong> or <strong>Import Default Studies</strong> above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($articles->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/80 bg-slate-900/40">
                {{ $articles->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Modal -->
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="deleteModal = false"></div>
            <div class="relative w-full max-w-sm glass-card rounded-2xl border border-slate-700/60 p-6 shadow-2xl">
                <h3 class="text-base font-bold text-white mb-2">Delete Research Article</h3>
                <p class="text-xs text-slate-400 mb-5">
                    Are you sure you want to delete <span class="text-white font-semibold" x-text="deleteArticle.title"></span>?
                </p>
                <form method="POST" :action="'{{ url('/admin/blog/articles') }}/' + deleteArticle.id" class="flex justify-end gap-2">
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

