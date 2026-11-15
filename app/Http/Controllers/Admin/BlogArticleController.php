<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use Database\Seeders\BlogSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogArticleController extends Controller
{
    /**
     * Display a listing of research blog articles.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim();
        $status = $request->string('status')->trim();
        $categoryId = $request->integer('category');

        $query = BlogArticle::query()->with(['category', 'spotlightModel']);

        if ($search->isNotEmpty()) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('report_id', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($categoryId > 0) {
            $query->where('blog_category_id', $categoryId);
        }

        if ($status->value() === 'published') {
            $query->where('is_published', true);
        } elseif ($status->value() === 'draft') {
            $query->where('is_published', false);
        } elseif ($status->value() === 'featured') {
            $query->where('is_featured', true);
        }

        $articles = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => BlogArticle::count(),
            'published' => BlogArticle::where('is_published', true)->count(),
            'draft' => BlogArticle::where('is_published', false)->count(),
            'featured' => BlogArticle::where('is_featured', true)->count(),
        ];

        $categories = BlogCategory::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.blog.articles.index', compact(
            'articles',
            'counts',
            'categories',
            'search',
            'status',
            'categoryId'
        ));
    }

    /**
     * Show the interactive article composer for creating a new article.
     */
    public function create(): View
    {
        return view('admin.blog.articles.form', [
            'article' => new BlogArticle([
                'read_time' => '8 دقائق قراءة',
                'author_name' => 'فريق أبحاث إدراك Lab',
                'author_role' => 'مختبر اقتصاديات النماذج العربية',
                'is_published' => true,
                'published_at' => now(),
                'hero_metrics' => [
                    ['value' => '', 'label' => '', 'tone' => 'efficiency'],
                ],
                'takeaways' => [
                    ['num' => '01', 'title' => '', 'body' => ''],
                ],
                'sections' => [
                    ['id' => 'context', 'title' => '1. مقدمة الدراسة والسياق', 'paragraphs' => ['']],
                ],
                'comparison_rows' => [],
                'blocks' => [
                    'charts' => [],
                    'tables' => [],
                    'quoted_models' => [],
                ],
            ]),
            'categories' => BlogCategory::orderBy('sort_order')->orderBy('name')->get(),
            'catalogModels' => $this->catalogModels(),
            'isEditing' => false,
        ]);
    }

    /**
     * Persist a newly created blog article.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);
        $slug = $this->resolveUniqueSlug($validated['slug'] ?? null, $validated['title']);

        if ($request->boolean('is_featured')) {
            BlogArticle::where('is_featured', true)->update(['is_featured' => false]);
        }

        $article = BlogArticle::create($this->buildPayload($request, $validated, $slug));

        return redirect()->route('admin.blog.articles.edit', $article)
            ->with('success', "Article '{$article->title}' was created successfully.");
    }

    /**
     * Show the interactive article composer for editing an existing article.
     */
    public function edit(BlogArticle $article): View
    {
        return view('admin.blog.articles.form', [
            'article' => $article,
            'categories' => BlogCategory::orderBy('sort_order')->orderBy('name')->get(),
            'catalogModels' => $this->catalogModels(),
            'isEditing' => true,
        ]);
    }

    /**
     * Update an existing research article.
     */
    public function update(Request $request, BlogArticle $article): RedirectResponse
    {
        $validated = $this->validateArticle($request, $article);
        $slug = $this->resolveUniqueSlug($validated['slug'] ?? null, $validated['title'], $article->id);

        if ($request->boolean('is_featured')) {
            BlogArticle::where('id', '!=', $article->id)
                ->where('is_featured', true)
                ->update(['is_featured' => false]);
        }

        $article->update($this->buildPayload($request, $validated, $slug));

        return redirect()->route('admin.blog.articles.edit', $article)
            ->with('success', "Article '{$article->title}' was updated successfully.");
    }

    /**
     * Delete a research article.
     */
    public function destroy(BlogArticle $article): RedirectResponse
    {
        $title = $article->title;
        $article->delete();

        return redirect()->route('admin.blog.articles.index')
            ->with('success', "Article '{$title}' was deleted.");
    }

    /**
     * Toggle whether an article is publicly visible.
     */
    public function togglePublished(BlogArticle $article): RedirectResponse
    {
        $article->update(['is_published' => ! $article->is_published]);

        $label = $article->is_published ? 'published' : 'moved to drafts';

        return back()->with('success', "Article '{$article->title}' {$label}.");
    }

    /**
     * Mark or unmark an article as the featured blog study.
     */
    public function toggleFeatured(BlogArticle $article): RedirectResponse
    {
        if (! $article->is_featured) {
            BlogArticle::where('id', '!=', $article->id)->update(['is_featured' => false]);
        }

        $article->update(['is_featured' => ! $article->is_featured]);

        return back()->with('success', "Featured status updated for '{$article->title}'.");
    }

    /**
     * Import default Stitch research articles and categories into the database.
     */
    public function seedDefaults(BlogSeeder $seeder): RedirectResponse
    {
        $seeder->run();

        return redirect()->route('admin.blog.articles.index')
            ->with('success', 'Default research categories and curated articles were imported.');
    }

    /**
     * Validate request input for storing or updating an article.
     *
     * @return array<string, mixed>
     */
    private function validateArticle(Request $request, ?BlogArticle $article = null): array
    {
        return $request->validate([
            'blog_category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'spotlight_model_id' => ['nullable', 'integer', 'exists:models,id'],
            'report_id' => ['nullable', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('blog_articles', 'slug')->ignore($article?->id),
            ],
            'excerpt' => ['required', 'string', 'max:2000'],
            'read_time' => ['nullable', 'string', 'max:64'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'author_role' => ['nullable', 'string', 'max:120'],
            'key_metric_value' => ['nullable', 'string', 'max:64'],
            'key_metric_label' => ['nullable', 'string', 'max:120'],
            'published_at' => ['nullable', 'date'],
            'code_snippet' => ['nullable', 'string'],
            'hero_metrics_json' => ['nullable'],
            'takeaways_json' => ['nullable'],
            'sections_json' => ['nullable'],
            'comparison_rows_json' => ['nullable'],
            'charts_json' => ['nullable'],
            'tables_json' => ['nullable'],
            'quoted_models_json' => ['nullable'],
        ]);
    }

    /**
     * Build the persistence payload from validated fields and structured JSON builders.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildPayload(Request $request, array $validated, string $slug): array
    {
        $heroMetrics = $this->decodeList($request->input('hero_metrics_json', $request->input('hero_metrics')));
        $takeaways = $this->decodeList($request->input('takeaways_json', $request->input('takeaways')));
        $sections = $this->normalizeSections(
            $this->decodeList($request->input('sections_json', $request->input('sections')))
        );
        $comparisonRows = $this->decodeList($request->input('comparison_rows_json', $request->input('comparison_rows')));
        $charts = $this->normalizeCharts(
            $this->decodeList($request->input('charts_json', $request->input('charts')))
        );
        $tables = $this->normalizeTables(
            $this->decodeList($request->input('tables_json', $request->input('tables')))
        );
        $quotedModels = $this->normalizeQuotedModels(
            $this->decodeList($request->input('quoted_models_json', $request->input('quoted_models')))
        );

        return [
            'blog_category_id' => $validated['blog_category_id'] ?? null,
            'spotlight_model_id' => $validated['spotlight_model_id'] ?? null,
            'report_id' => $validated['report_id'] ?? ('EL-TR-'.now()->format('Y-md')),
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'read_time' => ($validated['read_time'] ?? null) ?: '8 دقائق قراءة',
            'author_name' => ($validated['author_name'] ?? null) ?: 'فريق أبحاث إدراك Lab',
            'author_role' => ($validated['author_role'] ?? null) ?: 'مختبر اقتصاديات النماذج العربية',
            'key_metric_value' => $validated['key_metric_value'] ?? null,
            'key_metric_label' => $validated['key_metric_label'] ?? null,
            'hero_metrics' => $heroMetrics,
            'takeaways' => $takeaways,
            'sections' => $sections,
            'comparison_rows' => $comparisonRows,
            'blocks' => [
                'charts' => $charts,
                'tables' => $tables,
                'quoted_models' => $quotedModels,
            ],
            'code_snippet' => $validated['code_snippet'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published', true),
            'published_at' => ! empty($validated['published_at'])
                ? Carbon::parse($validated['published_at'])
                : now(),
        ];
    }

    /**
     * Decode an array or JSON string input safely.
     *
     * @return list<array<string, mixed>>
     */
    private function decodeList(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter($raw, 'is_array'));
        }

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    /**
     * Ensure sections store a clean list of non-empty paragraphs.
     *
     * @param  list<array<string, mixed>>  $sections
     * @return list<array{id: string, title: string, paragraphs: list<string>}>
     */
    private function normalizeSections(array $sections): array
    {
        $normalized = [];

        foreach ($sections as $index => $section) {
            $title = trim((string) ($section['title'] ?? ''));
            $paragraphsRaw = $section['paragraphs'] ?? [];

            if (is_string($paragraphsRaw)) {
                $paragraphs = array_values(array_filter(
                    array_map('trim', preg_split('/\R{2,}|\R/', $paragraphsRaw) ?: []),
                    fn (string $line): bool => $line !== ''
                ));
            } elseif (is_array($paragraphsRaw)) {
                $paragraphs = array_values(array_filter(
                    array_map(fn (mixed $line): string => trim((string) $line), $paragraphsRaw),
                    fn (string $line): bool => $line !== ''
                ));
            } else {
                $paragraphs = [];
            }

            if ($title === '' && empty($paragraphs)) {
                continue;
            }

            $id = trim((string) ($section['id'] ?? ''));
            if ($id === '') {
                $id = 'section-'.($index + 1);
            }

            $normalized[] = [
                'id' => $id,
                'title' => $title !== '' ? $title : 'قسم '.($index + 1),
                'paragraphs' => $paragraphs,
            ];
        }

        return $normalized;
    }

    /**
     * Normalize custom bar/comparison charts.
     *
     * @param  list<array<string, mixed>>  $charts
     * @return list<array<string, mixed>>
     */
    private function normalizeCharts(array $charts): array
    {
        $normalized = [];

        foreach ($charts as $chart) {
            $title = trim((string) ($chart['title'] ?? ''));
            $bars = [];

            foreach ($chart['bars'] ?? [] as $bar) {
                if (! is_array($bar)) {
                    continue;
                }
                $label = trim((string) ($bar['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $val = (float) ($bar['value'] ?? 0);
                $bars[] = [
                    'label' => $label,
                    'value' => $val,
                    'display_value' => trim((string) ($bar['display_value'] ?? '')) ?: (string) $val,
                    'color' => trim((string) ($bar['color'] ?? 'primary')) ?: 'primary',
                    'highlight' => (bool) ($bar['highlight'] ?? false),
                ];
            }

            if ($title === '' && empty($bars)) {
                continue;
            }

            $normalized[] = [
                'title' => $title !== '' ? $title : 'رسم بياني للمقارنة',
                'subtitle' => trim((string) ($chart['subtitle'] ?? '')),
                'unit' => trim((string) ($chart['unit'] ?? '')),
                'bars' => $bars,
            ];
        }

        return $normalized;
    }

    /**
     * Normalize custom multi-column tables.
     *
     * @param  list<array<string, mixed>>  $tables
     * @return list<array<string, mixed>>
     */
    private function normalizeTables(array $tables): array
    {
        $normalized = [];

        foreach ($tables as $table) {
            $title = trim((string) ($table['title'] ?? ''));
            $headers = array_values(array_filter(
                array_map(fn (mixed $h): string => trim((string) $h), (array) ($table['headers'] ?? [])),
                fn (string $h): bool => $h !== ''
            ));

            $rows = [];
            foreach ((array) ($table['rows'] ?? []) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $cells = array_map(fn (mixed $cell): string => trim((string) $cell), array_values($row));
                if (count(array_filter($cells, fn (string $c): bool => $c !== '')) > 0) {
                    $rows[] = $cells;
                }
            }

            if ($title === '' && empty($headers) && empty($rows)) {
                continue;
            }

            $normalized[] = [
                'title' => $title !== '' ? $title : 'جدول بيانات مخصص',
                'headers' => $headers,
                'rows' => $rows,
            ];
        }

        return $normalized;
    }

    /**
     * Normalize quoted AI model embeds.
     *
     * @param  list<array<string, mixed>>  $quoted
     * @return list<array<string, mixed>>
     */
    private function normalizeQuotedModels(array $quoted): array
    {
        $normalized = [];

        foreach ($quoted as $item) {
            $slug = trim((string) ($item['slug'] ?? ''));
            $name = trim((string) ($item['name'] ?? ''));
            if ($slug === '' && $name === '') {
                continue;
            }

            $normalized[] = [
                'model_id' => ! empty($item['model_id']) ? (int) $item['model_id'] : null,
                'slug' => $slug,
                'name' => $name,
                'provider' => trim((string) ($item['provider'] ?? '')),
                'badge' => trim((string) ($item['badge'] ?? 'اقتباس مرجعي')),
                'quote' => trim((string) ($item['quote'] ?? '')),
                'placement' => trim((string) ($item['placement'] ?? 'after_section_1')),
            ];
        }

        return $normalized;
    }

    /**
     * Load active AI models with live pricing and Arabic metrics for the Admin composer.
     *
     * @return list<array<string, mixed>>
     */
    private function catalogModels(): array
    {
        return AiModel::query()
            ->with(['provider', 'latestSpec', 'benchmarkArabic', 'benchmarkGeneral'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (AiModel $model): array {
                $tpw = $model->benchmarkArabic?->tokens_per_word !== null
                    ? (float) $model->benchmarkArabic->tokens_per_word
                    : null;
                $inPrice = $model->latestSpec?->input_cost_per_1m !== null
                    ? (float) $model->latestSpec->input_cost_per_1m
                    : null;
                $outPrice = $model->latestSpec?->output_cost_per_1m !== null
                    ? (float) $model->latestSpec->output_cost_per_1m
                    : null;

                $effectiveRatio = $tpw ?? 1.65;
                $cost1mWordsUsd = $inPrice !== null
                    ? round($effectiveRatio * (($inPrice * 0.75) + (($outPrice ?? $inPrice) * 0.25)), 2)
                    : null;

                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'provider' => $model->provider?->name ?? 'OpenRouter',
                    'arabic_score' => $model->benchmarkArabic?->total_score,
                    'tokens_per_word' => $tpw,
                    'tps' => $model->benchmarkArabic?->tokens_per_second !== null
                        ? (float) $model->benchmarkArabic->tokens_per_second
                        : null,
                    'intelligence' => $model->benchmarkGeneral?->intelligence_index !== null
                        ? (float) $model->benchmarkGeneral->intelligence_index
                        : null,
                    'input_price' => $inPrice,
                    'output_price' => $outPrice,
                    'cost_1m_words_usd' => $cost1mWordsUsd,
                    'cost_1m_words_sar' => $cost1mWordsUsd !== null ? round($cost1mWordsUsd * 3.75, 2) : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Resolve a unique URL slug for the article.
     */
    private function resolveUniqueSlug(?string $providedSlug, string $title, ?int $ignoreId = null): string
    {
        $raw = trim((string) ($providedSlug ?: $title));
        $base = Str::slug($raw);

        if ($base === '') {
            $base = trim((string) preg_replace('/\s+/u', '-', $raw), '-');
        }

        if ($base === '') {
            $base = 'article-'.now()->timestamp;
        }

        $candidate = $base;
        $counter = 2;

        while (
            BlogArticle::query()
                ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = "{$base}-{$counter}";
            $counter++;
        }

        return $candidate;
    }
}
