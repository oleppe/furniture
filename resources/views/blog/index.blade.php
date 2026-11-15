@extends('layouts.public')

@php
    $pageUrl = route('blog.index');
    $blogSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        '@id' => $pageUrl.'#blog',
        'name' => 'المدونة والأبحاث التقنية — إدراك Lab',
        'description' => 'دراسات تطبيقية وأبحاث هندسية في كفاءة التوكن العربي، زمن الاستجابة، اقتصاديات النماذج، وأتمتة المستندات (Doc AI).',
        'url' => $pageUrl,
        'inLanguage' => 'ar',
        'publisher' => ['@id' => route('home').'#organization'],
        'blogPost' => collect($posts)->map(fn (array $item): array => [
            '@type' => 'BlogPosting',
            'headline' => $item['title'],
            'url' => route('blog.show', $item['slug']),
            'datePublished' => $item['published_at'],
            'author' => ['@type' => 'Organization', 'name' => $item['author']],
        ])->values()->all(),
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'المدونة والأبحاث', 'item' => $pageUrl],
        ],
    ];
@endphp

@section('title', 'المدونة والأبحاث التقنية في الذكاء الاصطناعي العربي | '.config('seo.site_name'))
@section('meta_description', 'تحليلات هندسية وأبحاث قياسية في كفاءة التوكن العربي، مقارنة النماذج، وأتمتة المستندات (Doc AI) لمساعدة فرق التطوير على خفض التكاليف ورفع الدقة.')
@section('canonical', $pageUrl)

@push('head')
    <script type="application/ld+json">
        @json($blogSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <script type="application/ld+json">
        @json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
@endpush

@section('content')
<div x-data="blogHub(@js($posts))">

    <!-- ==================== HERO & FILTER HEADER ==================== -->
    <section class="w-full px-gutter md:px-gutter-desktop pt-space-lg pb-space-md" aria-labelledby="blog-heading">
        <div class="max-w-7xl mx-auto">
            <div class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg md:p-space-xl relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-primary/10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl space-y-space-md">
                    <div class="inline-flex items-center gap-2 px-space-sm py-1 rounded-full bg-primary/10 border border-primary/20 font-label-code text-label-code text-primary">
                        <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                        IDRAAK LAB • الأبحاث والدراسات التطبيقية
                    </div>

                    <h1 id="blog-heading" class="font-display-hero-mobile md:font-display-hero text-display-hero-mobile md:text-display-hero text-text-primary leading-tight">
                        المدونة والأبحاث التقنية
                    </h1>

                    <p class="font-body-lg text-body-lg text-text-secondary leading-relaxed">
                        دراسات هندسية مبسطة ومبنية على قياسات حية تساعدك على اختيار النموذج الأنسب، تقليص تكلفة التوكن العربي، وتوظيف أدوات وإضافة إدراك Lab في بيئة الإنتاج.
                    </p>

                    <!-- Search + Category Filter Bar -->
                    <div class="pt-space-sm space-y-space-sm">
                        <div class="relative max-w-xl">
                            <span class="material-symbols-outlined text-[18px] text-text-tertiary absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" aria-hidden="true">search</span>
                            <input type="search"
                                   x-model.trim="query"
                                   placeholder="ابحث في الأبحاث، النماذج، أو خدمات Doc AI..."
                                   aria-label="ابحث في الأبحاث التقنية"
                                   class="w-full pr-10 pl-4 py-space-sm rounded-xl bg-surface-panel border border-border-subtle text-text-primary placeholder:text-text-tertiary font-body-sm text-body-sm focus:outline-none focus:border-primary transition-colors">
                        </div>

                        <div class="flex items-center gap-space-xs overflow-x-auto pb-1" role="tablist" aria-label="تصنيفات الأبحاث">
                            @foreach ($categories as $category)
                                <button type="button"
                                        role="tab"
                                        @click="activeCategory = '{{ $category['key'] }}'"
                                        :aria-selected="activeCategory === '{{ $category['key'] }}'"
                                        :class="activeCategory === '{{ $category['key'] }}' ? 'bg-primary-container text-on-primary-container font-semibold' : 'bg-surface-panel border border-border-subtle text-text-secondary hover:text-text-primary hover:border-border-highlight'"
                                        class="px-space-md py-space-xs rounded-full font-body-sm text-body-sm whitespace-nowrap transition-colors cursor-pointer">
                                    {{ $category['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== MAIN 12-COL CONTENT + SERVICES SIDEBAR ==================== -->
    <section class="w-full px-gutter md:px-gutter-desktop pb-space-xl">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">

            <!-- Right / Main Column: Featured Study & Articles Grid -->
            <div class="lg:col-span-8 space-y-space-lg">

                <!-- Featured Research Spotlight -->
                <article x-show="matchesFeatured()"
                         class="bg-surface-subtle border border-border-subtle hover:border-primary/40 rounded-2xl p-space-lg transition-colors relative overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-space-xs mb-space-sm">
                        <div class="flex flex-wrap items-center gap-space-xs">
                            <span class="px-space-sm py-0.5 rounded-full bg-secondary/15 text-secondary font-label-code text-label-code">دراسة مميزة</span>
                            <span class="px-space-sm py-0.5 rounded-full bg-surface-panel border border-border-subtle text-text-secondary font-body-sm text-body-sm">{{ $featuredPost['category_label'] }}</span>
                        </div>
                        <span class="font-label-code text-label-code text-text-tertiary" dir="ltr">{{ $featuredPost['report_id'] }}</span>
                    </div>

                    <h2 class="font-headline-lg text-headline-lg text-text-primary leading-snug mb-space-sm">
                        <a href="{{ route('blog.show', $featuredPost['slug']) }}" class="hover:text-primary transition-colors">
                            {{ $featuredPost['title'] }}
                        </a>
                    </h2>

                    <p class="font-body-md text-body-md text-text-secondary leading-relaxed mb-space-md">
                        {{ $featuredPost['excerpt'] }}
                    </p>

                    <!-- 3 Key Metrics Strip -->
                    @if (! empty($featuredPost['hero_metrics']))
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm mb-space-md">
                            @foreach ($featuredPost['hero_metrics'] as $metric)
                                <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-sm">
                                    <div class="font-metric-display text-metric-display text-primary" dir="ltr">{{ $metric['value'] }}</div>
                                    <div class="font-body-sm text-body-sm text-text-tertiary mt-0.5">{{ $metric['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-space-sm pt-space-sm border-t border-border-subtle">
                        <div class="flex items-center gap-space-md font-label-code text-label-code text-text-tertiary">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]" aria-hidden="true">schedule</span>
                                {{ $featuredPost['read_time'] }}
                            </span>
                            <span>•</span>
                            <time datetime="{{ $featuredPost['published_at'] }}">{{ $featuredPost['published_label'] }}</time>
                        </div>
                        <a href="{{ route('blog.show', $featuredPost['slug']) }}"
                           class="inline-flex items-center gap-1.5 px-space-md py-space-sm rounded-xl bg-primary-container text-on-primary-container font-headline-sm text-headline-sm hover:opacity-90 transition-opacity">
                            قراءة الدراسة الكاملة
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                        </a>
                    </div>
                </article>

                <!-- Filtered Research Articles Grid -->
                <div>
                    <div class="flex items-center justify-between mb-space-md">
                        <h2 class="font-headline-md text-headline-md text-text-primary">أحدث الأبحاث والتقارير</h2>
                        <span class="font-label-code text-label-code text-text-tertiary" x-text="filteredPosts().length + ' مقالات'"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        @foreach ($posts as $item)
                            @continue($item['slug'] === $featuredPost['slug'])
                            <article x-show="matchesPost(@js($item))"
                                     class="bg-surface-subtle border border-border-subtle hover:border-primary/40 rounded-xl p-space-md flex flex-col justify-between gap-space-md transition-colors group">
                                <div class="space-y-space-sm">
                                    <div class="flex items-center justify-between gap-space-xs">
                                        <span class="px-space-sm py-0.5 rounded-full bg-surface-panel border border-border-subtle text-primary font-body-sm text-body-sm">
                                            {{ $item['category_label'] }}
                                        </span>
                                        <span class="px-space-xs py-0.5 rounded bg-secondary/10 text-secondary font-label-code text-label-code" dir="ltr">
                                            {{ $item['key_metric_value'] }}
                                        </span>
                                    </div>

                                    <h3 class="font-headline-sm text-headline-sm text-text-primary group-hover:text-primary transition-colors leading-snug">
                                        <a href="{{ route('blog.show', $item['slug']) }}">
                                            {{ $item['title'] }}
                                        </a>
                                    </h3>

                                    <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed line-clamp-3">
                                        {{ $item['excerpt'] }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between pt-space-sm border-t border-border-subtle/70 font-label-code text-label-code text-text-tertiary">
                                    <span>{{ $item['read_time'] }} • {{ $item['published_label'] }}</span>
                                    <a href="{{ route('blog.show', $item['slug']) }}" class="text-primary hover:underline flex items-center gap-1">
                                        اقرأ التقرير
                                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">arrow_back</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div x-show="filteredPosts().length === 0" x-cloak class="bg-surface-subtle border border-border-subtle rounded-xl p-space-xl text-center space-y-space-sm mt-space-md">
                        <span class="material-symbols-outlined text-[32px] text-text-tertiary" aria-hidden="true">manage_search</span>
                        <p class="font-body-md text-body-md text-text-secondary">لم يتم العثور على أبحاث تطابق كلمة البحث الحالية.</p>
                        <button type="button" @click="activeCategory = 'all'; query = ''" class="px-space-md py-space-xs rounded-lg bg-surface-panel border border-border-subtle text-primary font-body-sm text-body-sm cursor-pointer">
                            عرض جميع الأبحاث
                        </button>
                    </div>
                </div>
            </div>

            <!-- Left / Sidebar Column: Focused on Services & Plugin Offerings -->
            <aside class="lg:col-span-4 space-y-space-md lg:sticky lg:top-32">

                <!-- 1. Plugin & Services Spotlight (Primary Focus) -->
                <div class="bg-surface-subtle border border-primary/30 rounded-2xl p-space-md space-y-space-md shadow-lg">
                    <div class="flex items-center justify-between gap-space-xs">
                        <span class="px-space-sm py-0.5 rounded bg-primary/15 text-primary font-label-code text-label-code">PLUGIN & SERVICES</span>
                        <span class="material-symbols-outlined text-[20px] text-primary" aria-hidden="true">rocket_launch</span>
                    </div>

                    <div>
                        <h2 class="font-headline-md text-headline-md text-text-primary">خدمات وإضافة إدراك Lab</h2>
                        <p class="font-body-sm text-body-sm text-text-secondary mt-1 leading-relaxed">
                            حوّل نتائج هذه الأبحاث إلى توفير فوري في تطبيقاتك عبر أدواتنا المتخصصة للمحتوى العربي.
                        </p>
                    </div>

                    <div class="space-y-space-sm">
                        @foreach ($services as $service)
                            <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-sm space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-secondary" aria-hidden="true">{{ $service['icon'] }}</span>
                                    <span class="font-label-code text-label-code text-secondary">{{ $service['badge'] }}</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-text-primary">{{ $service['title'] }}</h3>
                                <p class="font-body-sm text-body-sm text-text-tertiary leading-relaxed">{{ $service['description'] }}</p>
                                <a href="{{ route('home').$service['anchor'] }}" class="inline-flex items-center gap-1 font-body-sm text-body-sm text-primary hover:underline pt-0.5">
                                    {{ $service['cta_label'] }}
                                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">arrow_back</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Quick Token Cost Estimator Teaser -->
                <div class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-md space-y-space-sm" x-data="{ words: 250000 }">
                    <div class="flex items-center justify-between">
                        <h2 class="font-headline-sm text-headline-sm text-text-primary">تقدير سريع لوفر التوكن</h2>
                        <span class="material-symbols-outlined text-[18px] text-metric-cost" aria-hidden="true">calculate</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-text-tertiary">اختر حجم المحتوى العربي الشهري لقياس أثر كفاءة التقطيع:</p>

                    <div class="grid grid-cols-3 gap-space-xs">
                        <button type="button" @click="words = 100000" :class="words === 100000 ? 'bg-primary-container text-on-primary-container' : 'bg-surface-panel text-text-secondary border-border-subtle'" class="py-1.5 rounded-lg border font-label-code text-label-code cursor-pointer">100K</button>
                        <button type="button" @click="words = 250000" :class="words === 250000 ? 'bg-primary-container text-on-primary-container' : 'bg-surface-panel text-text-secondary border-border-subtle'" class="py-1.5 rounded-lg border font-label-code text-label-code cursor-pointer">250K</button>
                        <button type="button" @click="words = 1000000" :class="words === 1000000 ? 'bg-primary-container text-on-primary-container' : 'bg-surface-panel text-text-secondary border-border-subtle'" class="py-1.5 rounded-lg border font-label-code text-label-code cursor-pointer">1M</button>
                    </div>

                    <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-sm flex items-center justify-between">
                        <span class="font-body-sm text-body-sm text-text-secondary">الوفر التقديري شهرياً</span>
                        <span class="font-metric-display text-metric-display text-metric-efficiency" dir="ltr" x-text="'$' + ((words / 1000000) * 14.5).toFixed(2)"></span>
                    </div>

                    <a href="{{ route('home') }}#calculator-widget"
                       class="w-full inline-flex items-center justify-center gap-2 py-space-sm rounded-xl bg-surface-elevated border border-border-subtle hover:border-primary/50 text-text-primary font-body-sm text-body-sm transition-colors">
                        فتح حاسبة التكاليف الكاملة
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">open_in_new</span>
                    </a>
                </div>

                <!-- 3. Top Live Arabic Models -->
                @if (count($topModels))
                    <div class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-md space-y-space-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="font-headline-sm text-headline-sm text-text-primary">أعلى النماذج في الاختبار العربي</h2>
                            <a href="{{ route('home') }}#leaderboard-section" class="font-label-code text-label-code text-primary hover:underline">الكل</a>
                        </div>
                        <div class="space-y-space-xs">
                            @foreach ($topModels as $topModel)
                                <a href="{{ route('models.show', $topModel['slug']) }}"
                                   class="flex items-center justify-between gap-space-sm p-space-sm rounded-xl bg-surface-panel border border-border-subtle hover:border-primary/40 transition-colors">
                                    <div class="min-w-0">
                                        <div class="font-body-sm text-body-sm text-text-primary truncate">{{ $topModel['name'] }}</div>
                                        <div class="font-label-code text-label-code text-text-tertiary">{{ $topModel['provider'] }}</div>
                                    </div>
                                    <div class="text-left shrink-0 font-label-code text-label-code">
                                        <div class="text-metric-efficiency" dir="ltr">{{ $topModel['score'] !== null ? $topModel['score'].'/'.\App\Services\ModelRankingService::ARABIC_SCORE_MAX : '—' }}</div>
                                        <div class="text-text-tertiary" dir="ltr">{{ $topModel['tpw'] !== null ? number_format($topModel['tpw'], 2).' T/W' : '' }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    function blogHub(posts) {
        return {
            posts: posts,
            activeCategory: 'all',
            query: '',

            matchesPost(post) {
                const categoryOk = this.activeCategory === 'all' || post.category_key === this.activeCategory;
                if (!categoryOk) {
                    return false;
                }

                if (this.query === '') {
                    return true;
                }

                const q = this.query.toLowerCase();
                return (post.title || '').toLowerCase().includes(q)
                    || (post.excerpt || '').toLowerCase().includes(q)
                    || (post.category_label || '').toLowerCase().includes(q);
            },

            matchesFeatured() {
                const featured = this.posts.find((item) => item.featured) || this.posts[0];
                return featured ? this.matchesPost(featured) : false;
            },

            filteredPosts() {
                const featured = this.posts.find((item) => item.featured) || this.posts[0];
                const featuredSlug = featured ? featured.slug : null;
                return this.posts.filter((item) => item.slug !== featuredSlug && this.matchesPost(item));
            },
        };
    }
</script>
@endpush

