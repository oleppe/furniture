@extends('layouts.public')

@php
    $provider = $model->provider;
    $providerName = $provider?->name ?? 'مزود غير معروف';
    $general = $model->benchmarkGeneral;
    $benchmark = $model->benchmarkArabic;
    $spec = $model->latestSpec;

    $placeholder = fn (mixed $value, string $fallback = '—'): string => $value === null || $value === ''
        ? $fallback
        : (string) $value;

    $plainDescription = trim(preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', (string) $model->description));
    $metaDescription = Str::limit($plainDescription !== '' ? $plainDescription : "بيانات أداء وتكلفة {$model->name} من {$providerName} على المحتوى العربي: كفاءة تقطيع التوكن، نقاط الاختبار العربي، السرعة، والأسعار.", 155);

    $intelligence = $general?->intelligence_index !== null ? (float) $general->intelligence_index : null;
    $compression = $benchmark?->tokens_per_word !== null ? (float) $benchmark->tokens_per_word : null;
    $arabicScore = $benchmark?->total_score;
    $scorePercent = $arabicScore !== null ? round($arabicScore / \App\Services\ModelRankingService::ARABIC_SCORE_MAX * 100, 1) : null;
    $tps = $benchmark?->tokens_per_second !== null ? (float) $benchmark->tokens_per_second : null;
    $latency = $benchmark?->latency_seconds !== null ? (float) $benchmark->latency_seconds : null;
    $context = $spec?->context_window;
    $maxOutput = $spec?->max_output_tokens;
    $releaseDate = $model->release_date?->format('Y-m-d');

    $formatContext = fn (?int $value): string => match (true) {
        $value === null => '—',
        $value >= 1_000_000 => rtrim(rtrim(number_format($value / 1_000_000, 2), '0'), '.').'M',
        $value >= 1_000 => number_format($value / 1_000, 0).'K',
        default => number_format($value),
    };

    $pageUrl = route('models.show', $model);
    $initials = mb_strtoupper(mb_substr($providerName, 0, 2));
    $detailsUrl = $model->openrouter_details_url
        ? (str_starts_with($model->openrouter_details_url, 'http') ? $model->openrouter_details_url : 'https://openrouter.ai'.$model->openrouter_details_url)
        : null;

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'لوحة المتصدرين', 'item' => route('home').'#leaderboard-section'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $model->name, 'item' => $pageUrl],
        ],
    ];

    $applicationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        '@id' => $pageUrl.'#application',
        'name' => $model->name,
        'description' => $metaDescription,
        'applicationCategory' => 'DeveloperApplication',
        'applicationSubCategory' => 'نموذج ذكاء اصطناعي توليدي',
        'operatingSystem' => 'Cloud',
        'url' => $pageUrl,
        'inLanguage' => 'ar',
        'author' => ['@type' => 'Organization', 'name' => $providerName],
        'isPartOf' => ['@id' => route('home').'#website'],
        'featureList' => $capabilities,
        'dateModified' => $model->updated_at?->toIso8601String(),
    ];

    if ($releaseDate) {
        $applicationSchema['datePublished'] = $model->release_date->toDateString();
    }

    if ($pricing['input'] !== null || $pricing['output'] !== null) {
        $applicationSchema['offers'] = [
            '@type' => 'Offer',
            'price' => number_format((float) ($pricing['input'] ?? $pricing['output']), 6, '.', ''),
            'priceCurrency' => 'USD',
            'description' => 'سعر المليون توكن إدخال بالدولار الأمريكي',
            'availability' => 'https://schema.org/OnlineOnly',
            'url' => $pageUrl,
        ];
    }
@endphp

@section('title', $model->name.' — الأداء العربي والأسعار والسرعة | '.config('seo.site_name'))

@section('meta_description', $metaDescription)

@section('canonical', $pageUrl)

@section('meta_updated_at', $model->updated_at?->toIso8601String())

@push('head')
    <script type="application/ld+json">
        @json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <script type="application/ld+json">
        @json($applicationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
@endpush

@section('content')
<div x-data="modelProfile(@js([
        'input' => $pricing['input'],
        'output' => $pricing['output'],
        'tokensPerWord' => $pricing['tokens_per_word'],
        'openrouter' => $model->openrouter_id,
        'name' => $model->name,
    ]))">

    <!-- ==================== BREADCRUMB ==================== -->
    <div class="w-full px-gutter-desktop pt-space-md">
        <div class="max-w-7xl mx-auto">
            <nav class="flex flex-wrap items-center gap-space-xs font-body-sm text-body-sm text-text-tertiary" aria-label="مسار التنقل">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="text-text-tertiary/50" aria-hidden="true">/</span>
                <a href="{{ route('home') }}#leaderboard-section" class="hover:text-primary transition-colors">النماذج</a>
                <span class="text-text-tertiary/50" aria-hidden="true">/</span>
                <span class="text-text-primary" aria-current="page">{{ $model->name }}</span>
            </nav>
        </div>
    </div>

    <!-- ==================== MODEL HERO ==================== -->
    <section class="w-full px-gutter-desktop pt-space-md pb-space-lg" aria-labelledby="model-heading">
        <div class="max-w-7xl mx-auto space-y-space-md">

            <div class="bg-surface-subtle rounded-xl p-space-lg shadow-xl relative overflow-hidden border border-border-subtle">
                <div class="absolute -top-24 -left-24 w-64 h-64 rounded-full blur-3xl opacity-20" style="background-color: var({{ $accentVar }});"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-space-lg">
                    <!-- Identity -->
                    <div class="flex flex-col sm:flex-row items-start gap-space-md">
                        <div class="w-16 h-16 rounded-xl flex items-center justify-center shrink-0 border border-border-subtle"
                             style="background-color: color-mix(in srgb, var({{ $accentVar }}) 18%, transparent);">
                            <span class="font-metric-display text-metric-display" style="color: var({{ $accentVar }});">{{ $initials ?: 'AI' }}</span>
                        </div>

                        <div class="space-y-space-xs min-w-0">
                            <div class="flex flex-wrap items-center gap-space-sm">
                                <h1 id="model-heading" class="font-headline-lg text-headline-lg text-text-primary tracking-tight">{{ $model->name }}</h1>
                                <span class="material-symbols-outlined text-primary text-[20px]" title="نموذج موثّق في منصة إدراك Lab" aria-label="نموذج موثّق">verified</span>
                                <span class="px-space-sm py-0.5 rounded bg-surface-elevated font-label-code text-label-code" style="color: var({{ $accentVar }});">بواسطة {{ $providerName }}</span>
                                <span class="px-space-sm py-0.5 rounded bg-surface-panel border border-border-subtle font-label-code text-label-code text-text-secondary">
                                    {{ $model->is_open_source ? 'مفتوح الأوزان' : 'سحابي مغلق' }}
                                </span>
                                @if ($hasArabic)
                                    <span class="inline-flex items-center gap-1.5 px-space-sm py-0.5 rounded bg-secondary/10 text-secondary font-label-code text-label-code">
                                        <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                                        مفحوص عربياً
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-space-sm py-0.5 rounded bg-surface-panel border border-border-subtle text-text-tertiary font-label-code text-label-code">
                                        بانتظار الفحص العربي
                                    </span>
                                @endif
                            </div>

                            <p class="font-body-sm text-body-sm text-text-secondary max-w-2xl leading-relaxed">
                                {{ $plainDescription !== '' ? Str::limit($plainDescription, 320) : "ملف تقني كامل لأداء {$model->name} على المحتوى العربي: كفاءة التقطيع، نقاط الاختبار العربي، السرعة، والتكلفة الفعلية." }}
                            </p>

                            <div class="flex flex-wrap items-center gap-space-md pt-space-xs font-label-code text-label-code text-text-tertiary">
                                @if ($releaseDate)
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">calendar_month</span>
                                        تاريخ الإطلاق: {{ $releaseDate }}
                                    </span>
                                    <span aria-hidden="true">•</span>
                                @endif
                                @if ($model->openrouter_id)
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">bolt</span>
                                        المعرف التقني: <span dir="ltr">{{ $model->openrouter_id }}</span>
                                    </span>
                                    <span aria-hidden="true">•</span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">memory</span>
                                    أقصى إخراج: {{ $maxOutput ? number_format($maxOutput).' توكن' : '—' }}
                                </span>
                                @if ($benchmark?->tested_at)
                                    <span aria-hidden="true">•</span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">history</span>
                                        آخر فحص: <time datetime="{{ $benchmark->tested_at->toDateString() }}">{{ $benchmark->tested_at->toDateString() }}</time>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick actions -->
                    <div class="flex flex-wrap items-center gap-space-sm shrink-0">
                        <a href="{{ route('home') }}#leaderboard-section"
                           class="px-space-md py-space-sm rounded-lg bg-primary-container text-on-primary-container font-headline-sm text-headline-sm flex items-center gap-2 hover:opacity-90 transition-opacity">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">swords</span>
                            لوحة المتصدرين
                        </a>
                        <button type="button" @click="goTo('pricing')"
                                class="px-space-sm py-space-sm rounded-lg bg-surface-elevated border border-border-subtle hover:border-border-highlight text-text-primary font-body-sm text-body-sm flex items-center gap-2 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-metric-cost" aria-hidden="true">calculate</span>
                            حساب التكلفة
                        </button>
                        @if ($model->openrouter_id)
                            <button type="button" @click="copy(@js($model->openrouter_id), 'identifier')"
                                    title="نسخ المعرف التقني" aria-label="نسخ المعرف التقني"
                                    class="px-space-sm py-space-sm rounded-lg bg-surface-elevated border border-border-subtle text-text-secondary hover:text-text-primary hover:border-border-highlight transition-colors">
                                <span class="material-symbols-outlined text-[18px]" x-text="copied === 'identifier' ? 'check' : 'content_copy'"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ==================== KPI MATRIX ==================== -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-space-sm md:gap-space-md">
                <!-- Intelligence -->
                <div class="bg-surface-subtle p-space-md rounded-xl shadow-md border border-border-subtle">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="font-body-sm text-body-sm">مؤشر الذكاء العام</span>
                        <span class="material-symbols-outlined text-[18px] text-metric-cost" aria-hidden="true">military_tech</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="font-metric-display text-metric-display text-text-primary" dir="ltr">{{ $intelligence !== null ? number_format($intelligence, 1) : '—' }}</span>
                        @if ($rank['general'])
                            <span class="font-label-code text-label-code text-secondary font-bold">#{{ $rank['general'] }}</span>
                        @endif
                    </div>
                    <div class="mt-2 font-body-sm text-body-sm text-text-tertiary">
                        {{ $intelligence !== null ? 'وفق مؤشر Artificial Analysis' : 'لا يوجد ترتيب عام بعد' }}
                    </div>
                    <div class="w-full bg-surface-panel h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-secondary h-full rounded-full" style="width: {{ $intelligence !== null ? min(100, $intelligence) : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Arabic compression -->
                <div class="bg-surface-subtle p-space-md rounded-xl shadow-md border border-border-subtle">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="font-body-sm text-body-sm">كفاءة التقطيع العربي</span>
                        <span class="material-symbols-outlined text-[18px] text-metric-efficiency" aria-hidden="true">translate</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-metric-display text-metric-display {{ $compression !== null ? 'text-metric-efficiency' : 'text-text-tertiary' }}" dir="ltr">{{ $compression !== null ? number_format($compression, 2) : '—' }}</span>
                        @if ($compression !== null)
                            <span class="font-body-sm text-body-sm text-text-tertiary">توكن/كلمة</span>
                        @endif
                    </div>
                    <div class="mt-2 font-body-sm text-body-sm text-text-tertiary">
                        {{ $compression !== null ? 'المرجع الإنجليزي 1.33' : 'لم يُقس عربياً بعد' }}
                    </div>
                    <div class="w-full bg-surface-panel h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-metric-efficiency h-full rounded-full" style="width: {{ $compression !== null ? min(100, $compression / 4 * 100) : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Arabic score -->
                <div class="bg-surface-subtle p-space-md rounded-xl shadow-md border border-border-subtle">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="font-body-sm text-body-sm">الاختبار العربي</span>
                        <span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">target</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="font-metric-display text-metric-display {{ $arabicScore !== null ? 'text-text-primary' : 'text-text-tertiary' }}" dir="ltr">{{ $arabicScore !== null ? $arabicScore.'/'.App\Services\ModelRankingService::ARABIC_SCORE_MAX : '—' }}</span>
                        @if ($rank['arabic'])
                            <span class="font-label-code text-label-code text-primary font-bold">#{{ $rank['arabic'] }}</span>
                        @endif
                    </div>
                    <div class="mt-2 font-body-sm text-body-sm text-text-tertiary">
                        {{ $scorePercent !== null ? 'الدقة: '.$scorePercent.'%' : 'لم يُختبر عربياً بعد' }}
                    </div>
                    <div class="w-full bg-surface-panel h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-primary h-full rounded-full" style="width: {{ $scorePercent ?? 0 }}%;"></div>
                    </div>
                </div>

                <!-- Speed -->
                <div class="bg-surface-subtle p-space-md rounded-xl shadow-md border border-border-subtle">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="font-body-sm text-body-sm">السرعة وزمن الاستجابة</span>
                        <span class="material-symbols-outlined text-[18px] text-metric-speed" aria-hidden="true">speed</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-metric-display text-metric-display {{ $tps !== null ? 'text-metric-speed' : 'text-text-tertiary' }}" dir="ltr">{{ $tps !== null ? number_format($tps, 0) : '—' }}</span>
                        @if ($tps !== null)
                            <span class="font-body-sm text-body-sm text-text-tertiary">t/s</span>
                        @endif
                    </div>
                    <div class="mt-2 font-body-sm text-body-sm text-text-tertiary">
                        {{ $latency !== null ? 'أول استجابة: '.number_format($latency, 2).' ثانية' : 'لا توجد قياسات سرعة' }}
                    </div>
                    <div class="w-full bg-surface-panel h-1 rounded-full mt-3 overflow-hidden">
                        <div class="bg-metric-speed h-full rounded-full" style="width: {{ $tps !== null ? min(100, $tps / 150 * 100) : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Context -->
                <div class="bg-surface-subtle p-space-md rounded-xl shadow-md border border-border-subtle col-span-2 lg:col-span-1">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="font-body-sm text-body-sm">نافذة السياق</span>
                        <span class="material-symbols-outlined text-[18px] text-tertiary" aria-hidden="true">dataset</span>
                    </div>
                    <div class="font-metric-display text-metric-display text-text-primary" dir="ltr">{{ $formatContext($context) }}</div>
                    <div class="mt-2 font-body-sm text-body-sm text-text-tertiary">
                        أقصى إخراج: {{ $maxOutput ? number_format($maxOutput) : '—' }} توكن
                    </div>
                    <div class="mt-3 flex items-center gap-space-sm font-label-code text-label-code">
                        <span class="text-metric-cost" dir="ltr">${{ $pricing['input'] !== null ? number_format($pricing['input'], 2) : '—' }}</span>
                        <span class="text-text-tertiary">In</span>
                        <span class="w-px h-3 bg-border-subtle"></span>
                        <span class="text-metric-cost" dir="ltr">${{ $pricing['output'] !== null ? number_format($pricing['output'], 2) : '—' }}</span>
                        <span class="text-text-tertiary">Out</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== MAIN CONTENT ==================== -->
    <section class="w-full px-gutter-desktop pb-space-xl" id="profile-tabs">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-space-lg">

            <!-- Main column -->
            <div class="lg:col-span-8 space-y-space-md">
                <!-- Engineers' Verdict Summary -->
                @php
                    $isHighArabic = $scorePercent !== null && $scorePercent >= 80;
                    $isEfficientToken = $compression !== null && $compression <= 1.65;
                    $isFast = $tps !== null && $tps >= 60;
                @endphp
                <div class="bg-surface-subtle border border-border-subtle rounded-xl p-space-md grid grid-cols-1 md:grid-cols-2 gap-space-sm">
                    <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-sm space-y-1">
                        <div class="flex items-center gap-1.5 font-label-code text-label-code text-metric-efficiency">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">recommend</span>
                            خلاصة تقييم المهندسين العرب
                        </div>
                        <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                            @if ($isHighArabic && $isEfficientToken)
                                مناسب جداً للمهام العربية الإنتاجية بفضل توازن الدقة ({{ $scorePercent }}%) وكفاءة التقطيع ({{ number_format($compression, 2) }} توكن/كلمة).
                            @elseif ($isHighArabic)
                                خيار ممتاز للاستدلال المعقد وصياغة العقود والتقارير العربية ({{ $scorePercent }}% دقة عربية)، مع ضرورة مراقبة تكلفة المدخلات الطويلة.
                            @elseif ($hasArabic)
                                أداء عملي متوازن للاستخدامات اليومية والتلخيص السريع{{ $isFast ? ' بسرعة استجابة عالية ('.number_format($tps, 0).' t/s)' : '' }}.
                            @else
                                ينتظر اكتمال الاختبار العربي الشامل في المختبر؛ يمكنك الاعتماد على مؤشر الذكاء العام والتسعير المعلن أدناه.
                            @endif
                        </p>
                    </div>
                    <div class="bg-surface-panel border border-primary/25 rounded-lg p-space-sm space-y-1">
                        <div class="flex items-center gap-1.5 font-label-code text-label-code text-primary">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">extension</span>
                            تحسين التكلفة مع خدماتنا
                        </div>
                        <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                            استخدم <button type="button" @click="goTo('pricing')" class="text-primary hover:underline cursor-pointer">مختبر التقطيع وحاسبة التكلفة</button> لقياس فاتورتك الفعلية، أو دمج النموذج مع خدمات Doc AI وإضافة إدراك Lab.
                        </p>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex items-center gap-space-xs overflow-x-auto bg-surface-subtle border border-border-subtle rounded-xl p-space-xs"
                     role="tablist" aria-label="أقسام ملف النموذج">
                    <button type="button" role="tab" @click="tab = 'benchmarks'" :aria-selected="tab === 'benchmarks'"
                            :class="tab === 'benchmarks' ? 'bg-primary-container text-on-primary-container' : 'text-text-secondary hover:text-text-primary'"
                            class="px-space-md py-space-sm rounded-lg font-body-sm text-body-sm whitespace-nowrap transition-colors">
                        الأداء العربي
                    </button>
                    <button type="button" role="tab" @click="tab = 'pricing'" :aria-selected="tab === 'pricing'"
                            :class="tab === 'pricing' ? 'bg-primary-container text-on-primary-container' : 'text-text-secondary hover:text-text-primary'"
                            class="px-space-md py-space-sm rounded-lg font-body-sm text-body-sm whitespace-nowrap transition-colors">
                        التسعير والتوكن
                    </button>
                    <button type="button" role="tab" @click="tab = 'api'" :aria-selected="tab === 'api'"
                            :class="tab === 'api' ? 'bg-primary-container text-on-primary-container' : 'text-text-secondary hover:text-text-primary'"
                            class="px-space-md py-space-sm rounded-lg font-body-sm text-body-sm whitespace-nowrap transition-colors">
                        الربط البرمجي
                    </button>
                    <button type="button" role="tab" @click="tab = 'rivals'" :aria-selected="tab === 'rivals'"
                            :class="tab === 'rivals' ? 'bg-primary-container text-on-primary-container' : 'text-text-secondary hover:text-text-primary'"
                            class="px-space-md py-space-sm rounded-lg font-body-sm text-body-sm whitespace-nowrap transition-colors">
                        المنافسون
                    </button>
                </div>

                <!-- ============ TAB: BENCHMARKS ============ -->
                <div x-show="tab === 'benchmarks'" x-cloak class="bg-surface-subtle border border-border-subtle rounded-xl p-space-lg space-y-space-md">
                    <div class="flex flex-wrap items-start justify-between gap-space-sm">
                        <div class="space-y-space-xs">
                            <h2 class="font-headline-md text-headline-md text-text-primary">مصفوفة القياس المعياري للغة العربية</h2>
                            <p class="font-body-sm text-body-sm text-text-secondary max-w-2xl">
                                نتائج الاختبار العربي (156 نقطة) موزعة على خمس قدرات أساسية، مقاسة على 24 مهمة لغوية وحسابية ولهجية.
                            </p>
                        </div>
                        @if ($arabicScore !== null)
                            <div class="text-left">
                                <div class="font-metric-display text-metric-display text-text-primary" dir="ltr">{{ $arabicScore }}<span class="text-text-tertiary text-body-md">/{{ App\Services\ModelRankingService::ARABIC_SCORE_MAX }}</span></div>
                                <div class="font-body-sm text-body-sm text-metric-efficiency">الدقة {{ $scorePercent }}%</div>
                            </div>
                        @endif
                    </div>

                    @if (count($categories))
                        <div class="space-y-space-sm">
                            @foreach ($categories as $category)
                                <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-md space-y-space-xs">
                                    <div class="flex flex-wrap items-center justify-between gap-space-xs">
                                        <div class="flex items-center gap-space-xs">
                                            <span class="font-body-md text-body-md text-text-primary">{{ $category['name'] }}</span>
                                            @if ($category['status'] === 'passed')
                                                <span class="material-symbols-outlined text-[16px] text-metric-efficiency" aria-label="مُجتاز">check_circle</span>
                                            @elseif ($category['status'] !== '')
                                                <span class="material-symbols-outlined text-[16px] text-metric-cost" aria-label="جزئي">error</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-space-sm font-label-code text-label-code">
                                            <span class="text-text-primary" dir="ltr">{{ rtrim(rtrim(number_format($category['earned'], 1), '0'), '.') }} / {{ rtrim(rtrim(number_format($category['points'], 1), '0'), '.') }}</span>
                                            <span class="text-text-tertiary" dir="ltr">{{ number_format($category['percentage'], 1) }}%</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-surface-elevated h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $category['percentage'] >= 80 ? 'bg-metric-efficiency' : ($category['percentage'] >= 50 ? 'bg-metric-cost' : 'bg-metric-latency') }}"
                                             style="width: {{ min(100, $category['percentage']) }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-lg text-center space-y-space-xs">
                            <span class="material-symbols-outlined text-[28px] text-text-tertiary" aria-hidden="true">science</span>
                            <p class="font-body-md text-body-md text-text-secondary">
                                لم تُسجل تفاصيل الفئات لهذا النموذج بعد.
                                @if ($hasArabic)
                                    النتيجة الإجمالية المسجلة هي {{ $arabicScore }} من {{ App\Services\ModelRankingService::ARABIC_SCORE_MAX }}.
                                @else
                                    أضف النموذج إلى جولة اختبار عربية لعرض التفاصيل هنا.
                                @endif
                            </p>
                            <a href="{{ route('home') }}#leaderboard-section" class="inline-flex items-center gap-1 font-body-sm text-body-sm text-primary hover:underline">
                                استعراض النماذج المفحوصة
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                            </a>
                        </div>
                    @endif

                    <!-- General benchmarks -->
                    <div class="pt-space-sm">
                        <h3 class="font-headline-sm text-headline-sm text-text-primary mb-space-sm">المؤشرات العامة (Artificial Analysis)</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-right border-collapse text-body-sm">
                                <caption class="sr-only">المؤشرات العامة للنموذج</caption>
                                <thead>
                                    <tr class="border-b border-border-subtle font-label-code text-label-code text-text-tertiary">
                                        <th scope="col" class="py-space-sm px-space-xs font-normal">المؤشر</th>
                                        <th scope="col" class="py-space-sm px-space-xs font-normal">القيمة</th>
                                        <th scope="col" class="py-space-sm px-space-xs font-normal">ملاحظة</th>
                                    </tr>
                                </thead>
                                <tbody class="font-metric-cell text-metric-cell">
                                    <tr class="border-b border-border-subtle/60">
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-secondary">مؤشر الذكاء</td>
                                        <td class="py-space-sm px-space-xs text-text-primary" dir="ltr">{{ $intelligence !== null ? number_format($intelligence, 1) : '—' }}</td>
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-tertiary">ترتيب عام {{ $rank['general'] ? '#'.$rank['general'].' من '.$rank['total'] : 'غير متاح' }}</td>
                                    </tr>
                                    <tr class="border-b border-border-subtle/60">
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-secondary">مؤشر البرمجة</td>
                                        <td class="py-space-sm px-space-xs text-text-primary" dir="ltr">{{ $general?->coding_index !== null ? number_format((float) $general->coding_index, 1) : '—' }}</td>
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-tertiary">Coding Index</td>
                                    </tr>
                                    <tr class="border-b border-border-subtle/60">
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-secondary">المهام الوكيلة</td>
                                        <td class="py-space-sm px-space-xs text-text-primary" dir="ltr">{{ $general?->agentic_index !== null ? number_format((float) $general->agentic_index, 1) : '—' }}</td>
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-tertiary">Agentic Index</td>
                                    </tr>
                                    <tr class="border-b border-border-subtle/60">
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-secondary">تقييم Elo</td>
                                        <td class="py-space-sm px-space-xs text-text-primary" dir="ltr">{{ $general?->elo_rating !== null ? number_format((float) $general->elo_rating, 0) : '—' }}</td>
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-tertiary">ساحة النماذج</td>
                                    </tr>
                                    <tr>
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-secondary">MMLU / الرياضيات</td>
                                        <td class="py-space-sm px-space-xs text-text-primary" dir="ltr">{{ $general?->mmlu_score !== null ? number_format((float) $general->mmlu_score, 1) : '—' }} / {{ $general?->math_score !== null ? number_format((float) $general->math_score, 1) : '—' }}</td>
                                        <td class="py-space-sm px-space-xs font-body-sm text-text-tertiary">اختبارات أكاديمية</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============ TAB: PRICING ============ -->
                <div x-show="tab === 'pricing'" x-cloak class="space-y-space-md">
                    <div class="bg-surface-subtle border border-border-subtle rounded-xl p-space-lg space-y-space-md">
                        <div class="space-y-space-xs">
                            <h2 class="font-headline-md text-headline-md text-text-primary">التسعير والتوكن</h2>
                            <p class="font-body-sm text-body-sm text-text-secondary max-w-2xl">
                                الأسعار لكل مليون توكن بالدولار الأمريكي، مع تقدير التكلفة الفعلية للنصوص العربية بناءً على معامل التقطيع المقاس.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-space-sm">
                            <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-md">
                                <div class="font-body-sm text-body-sm text-text-tertiary">إدخال / 1M</div>
                                <div class="font-metric-display text-metric-display text-metric-cost" dir="ltr">${{ $pricing['input'] !== null ? number_format($pricing['input'], 2) : '—' }}</div>
                            </div>
                            <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-md">
                                <div class="font-body-sm text-body-sm text-text-tertiary">إخراج / 1M</div>
                                <div class="font-metric-display text-metric-display text-metric-cost" dir="ltr">${{ $pricing['output'] !== null ? number_format($pricing['output'], 2) : '—' }}</div>
                            </div>
                            <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-md">
                                <div class="font-body-sm text-body-sm text-text-tertiary">قراءة الكاش / 1M</div>
                                <div class="font-metric-display text-metric-display text-text-primary" dir="ltr">${{ $pricing['cache_read'] !== null ? number_format($pricing['cache_read'], 3) : '—' }}</div>
                            </div>
                            <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-md">
                                <div class="font-body-sm text-body-sm text-text-tertiary">كتابة الكاش / 1M</div>
                                <div class="font-metric-display text-metric-display text-text-primary" dir="ltr">${{ $pricing['cache_write'] !== null ? number_format($pricing['cache_write'], 3) : '—' }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-space-sm">
                            <div class="bg-surface-panel/60 border border-border-subtle rounded-lg p-space-md space-y-space-xs">
                                <div class="font-body-sm text-body-sm text-text-tertiary">معامل التقطيع العربي</div>
                                <div class="font-metric-display text-metric-display text-metric-efficiency" dir="ltr">{{ $compression !== null ? number_format($compression, 2).'x' : '—' }}</div>
                                <div class="font-body-sm text-body-sm text-text-secondary">
                                    {{ $compression !== null ? 'كل كلمة عربية تستهلك '.number_format($compression, 2).' توكن مقابل 1.33 للإنجليزية.' : 'لم يُقس معامل التقطيع لهذا النموذج بعد.' }}
                                </div>
                            </div>
                            <div class="bg-surface-panel/60 border border-border-subtle rounded-lg p-space-md space-y-space-xs">
                                <div class="font-body-sm text-body-sm text-text-tertiary">تكلفة 1,000 كلمة عربية</div>
                                <div class="font-metric-display text-metric-display text-text-primary" dir="ltr">${{ $pricing['cost_per_1k_words'] !== null ? number_format($pricing['cost_per_1k_words'], 5) : '—' }}</div>
                                <div class="font-body-sm text-body-sm text-text-secondary" dir="ltr">{{ $pricing['cost_per_1k_words_sar'] !== null ? number_format($pricing['cost_per_1k_words_sar'], 4).' SAR' : '—' }}</div>
                            </div>
                        </div>

                        <!-- Cost calculator -->
                        <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                            <div class="flex flex-wrap items-center justify-between gap-space-sm">
                                <h3 class="font-headline-sm text-headline-sm text-text-primary">حاسبة التكلفة الشهرية</h3>
                                <span class="font-label-code text-label-code text-text-tertiary">نسبة إدخال/إخراج 75:25</span>
                            </div>
                            <label for="monthly-words" class="font-body-sm text-body-sm text-text-secondary">عدد الكلمات العربية شهرياً</label>
                            <input type="range" id="monthly-words" min="10000" max="5000000" step="10000" x-model.number="monthlyWords"
                                   class="w-full accent-primary-container cursor-pointer">
                            <div class="flex flex-wrap items-baseline justify-between gap-space-xs">
                                <span class="font-metric-display text-metric-display text-primary" x-text="formatNumber(monthlyWords) + ' كلمة'"></span>
                                <div class="flex items-baseline gap-space-sm">
                                    <span class="font-metric-display text-metric-display text-text-primary" x-text="'$' + monthlyCost().toFixed(2)"></span>
                                    <span class="font-metric-cell text-metric-cell text-metric-efficiency" x-text="(monthlyCost() * 3.75).toFixed(2) + ' ر.س'"></span>
                                </div>
                            </div>
                            <p class="font-body-sm text-body-sm text-text-tertiary">
                                التقدير يعتمد على {{ $compression !== null ? number_format($compression, 2).' توكن لكل كلمة' : 'متوسط التقطيع' }} وأسعار المليون توكن المعلنة. قد تختلف الفاتورة الفعلية حسب طول المخرجات واستخدام الكاش.
                            </p>
                        </div>

                        <!-- Interactive Arabic Tokenizer & Cost Probe -->
                        <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                            <div class="flex flex-wrap items-center justify-between gap-space-sm">
                                <div>
                                    <h3 class="font-headline-sm text-headline-sm text-text-primary">مختبر تقطيع النصوص العربية التفاعلي</h3>
                                    <p class="font-body-sm text-body-sm text-text-tertiary">اختبر نصك العربي لمعرفة عدد التوكنات التقديري وتكلفة 10,000 طلب مماثل.</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <button type="button" @click="setProbeSample('contract')" class="px-2.5 py-1 rounded-lg bg-surface-elevated border border-border-subtle hover:border-primary/50 font-body-sm text-body-sm text-text-secondary cursor-pointer">عقد قانوني</button>
                                    <button type="button" @click="setProbeSample('finance')" class="px-2.5 py-1 rounded-lg bg-surface-elevated border border-border-subtle hover:border-primary/50 font-body-sm text-body-sm text-text-secondary cursor-pointer">تقرير مالي</button>
                                    <button type="button" @click="setProbeSample('support')" class="px-2.5 py-1 rounded-lg bg-surface-elevated border border-border-subtle hover:border-primary/50 font-body-sm text-body-sm text-text-secondary cursor-pointer">خدمة عملاء</button>
                                </div>
                            </div>

                            <label for="arabic-probe-input" class="sr-only">النص العربي للاختبار</label>
                            <textarea id="arabic-probe-input"
                                      rows="3"
                                      x-model="probeText"
                                      placeholder="اكتب أو الصق نصاً عربياً هنا..."
                                      class="w-full rounded-xl bg-surface-base border border-border-subtle p-space-sm text-text-primary font-body-sm text-body-sm focus:outline-none focus:border-primary"></textarea>

                            <div class="flex flex-wrap gap-1.5" dir="rtl" aria-label="معاينة التقطيع">
                                <template x-for="(chunk, idx) in probeChips()" :key="idx">
                                    <span class="px-2 py-0.5 rounded bg-primary/10 border border-primary/25 text-text-primary font-body-sm text-body-sm" x-text="chunk"></span>
                                </template>
                            </div>

                            <div class="grid grid-cols-3 gap-space-xs pt-space-xs">
                                <div class="bg-surface-base border border-border-subtle rounded-lg p-space-xs">
                                    <div class="font-body-sm text-body-sm text-text-tertiary">عدد الكلمات</div>
                                    <div class="font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="probeWords()"></div>
                                </div>
                                <div class="bg-surface-base border border-border-subtle rounded-lg p-space-xs">
                                    <div class="font-body-sm text-body-sm text-text-tertiary">التوكنات التقديرية</div>
                                    <div class="font-metric-cell text-metric-cell text-metric-efficiency" dir="ltr" x-text="probeTokens() + ' tokens'"></div>
                                </div>
                                <div class="bg-surface-base border border-border-subtle rounded-lg p-space-xs">
                                    <div class="font-body-sm text-body-sm text-text-tertiary">تكلفة 10K طلب</div>
                                    <div class="font-metric-cell text-metric-cell text-metric-cost" dir="ltr" x-text="probeTenKCost()"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ TAB: API ============ -->
                <div x-show="tab === 'api'" x-cloak class="bg-surface-subtle border border-border-subtle rounded-xl p-space-lg space-y-space-md">
                    <div class="flex flex-wrap items-start justify-between gap-space-sm">
                        <div class="space-y-space-xs">
                            <h2 class="font-headline-md text-headline-md text-text-primary">الربط المباشر مع واجهة البرمجة</h2>
                            <p class="font-body-sm text-body-sm text-text-secondary max-w-2xl">
                                أمثلة جاهزة للاتصال بالنموذج عبر OpenRouter — نفس المفتاح يمنحك الوصول لكل المزودين مع تحويل تلقائي عند تعطل أي نقطة نهاية.
                            </p>
                        </div>
                        <span class="px-space-sm py-space-xs rounded bg-surface-panel border border-border-subtle font-label-code text-label-code text-text-secondary" dir="ltr">{{ $model->openrouter_id ?: 'openrouter-model-id' }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-space-xs">
                        <template x-for="lang in codeLanguages" :key="lang.key">
                            <button type="button" @click="codeLang = lang.key"
                                    :class="codeLang === lang.key ? 'bg-primary-container text-on-primary-container' : 'bg-surface-panel text-text-secondary border-border-subtle hover:text-text-primary'"
                                    class="px-space-md py-space-xs rounded-lg border font-label-code text-label-code transition-colors">
                                <span x-text="lang.label"></span>
                            </button>
                        </template>
                        <button type="button" @click="copy(code(), 'code')"
                                class="ms-auto flex items-center gap-space-xs px-space-md py-space-xs rounded-lg border border-border-subtle bg-surface-panel text-text-secondary hover:text-text-primary transition-colors font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-[16px]" x-text="copied === 'code' ? 'check' : 'content_copy'"></span>
                            <span x-text="copied === 'code' ? 'تم النسخ' : 'نسخ الكود'"></span>
                        </button>
                    </div>

                    <pre class="bg-surface-base border border-border-subtle rounded-xl p-space-md overflow-x-auto text-left" dir="ltr"><code class="font-metric-cell text-metric-cell text-text-secondary whitespace-pre" x-text="code()"></code></pre>

                    <div class="flex flex-wrap items-center gap-space-md font-label-code text-label-code text-text-tertiary">
                        <span class="flex items-center gap-space-xs">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">bolt</span>
                            البروتوكول: OpenAI-compatible
                        </span>
                        @if ($detailsUrl)
                            <a href="{{ $detailsUrl }}" rel="noopener nofollow" target="_blank" class="flex items-center gap-space-xs text-primary hover:underline">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">open_in_new</span>
                                صفحة النموذج على OpenRouter
                            </a>
                        @endif
                    </div>
                </div>

                <!-- ============ TAB: RIVALS ============ -->
                <div x-show="tab === 'rivals'" x-cloak class="bg-surface-subtle border border-border-subtle rounded-xl p-space-lg space-y-space-md">
                    <div class="space-y-space-xs">
                        <h2 class="font-headline-md text-headline-md text-text-primary">نماذج منافسة وبديلة</h2>
                        <p class="font-body-sm text-body-sm text-text-secondary max-w-2xl">
                            بدائل قريبة في مستوى الأداء أو من نفس المزود، مع مقارنة سريعة للذكاء والتكلفة.
                        </p>
                    </div>

                    @if (count($alternatives))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-sm">
                            @foreach ($alternatives as $alternative)
                                <a href="{{ route('models.show', $alternative['slug']) }}"
                                   class="bg-surface-panel border border-border-subtle rounded-xl p-space-md space-y-space-xs hover:border-primary/50 transition-colors group">
                                    <div class="flex items-center justify-between gap-space-xs">
                                        <span class="font-headline-sm text-headline-sm text-text-primary group-hover:text-primary transition-colors">{{ $alternative['name'] }}</span>
                                        @if ($alternative['is_open_source'])
                                            <span class="px-space-xs py-0.5 rounded bg-secondary/10 text-secondary font-label-code text-label-code">Open</span>
                                        @endif
                                    </div>
                                    <div class="font-label-code text-label-code text-text-tertiary">{{ $alternative['provider'] }}</div>
                                    <div class="flex items-center justify-between pt-space-xs font-metric-cell text-metric-cell">
                                        <span class="text-text-secondary" dir="ltr">ذكاء {{ $alternative['intelligence'] !== null ? number_format($alternative['intelligence'], 1) : '—' }}</span>
                                        <span class="text-metric-cost" dir="ltr">${{ $alternative['in_price'] !== null ? number_format($alternative['in_price'], 2) : '—' }}/M</span>
                                    </div>
                                    <div class="flex items-center justify-between font-metric-cell text-metric-cell">
                                        <span class="text-text-secondary" dir="ltr">عربي {{ $alternative['score'] !== null ? $alternative['score'].'/'.App\Services\ModelRankingService::ARABIC_SCORE_MAX : '—' }}</span>
                                        <span class="font-label-code text-label-code text-primary flex items-center gap-1">
                                            عرض الملف
                                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">arrow_back</span>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="font-body-md text-body-md text-text-secondary">لا تتوفر نماذج بديلة مسجلة حالياً.</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="lg:col-span-4 space-y-space-md">
                <!-- Focused Plugin & Services Spotlight -->
                <div class="bg-surface-subtle border border-primary/30 rounded-xl p-space-md space-y-space-sm shadow-lg">
                    <div class="flex items-center justify-between gap-space-xs">
                        <span class="px-space-sm py-0.5 rounded bg-primary/15 text-primary font-label-code text-label-code">PLUGIN & SERVICES</span>
                        <span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">extension</span>
                    </div>
                    <h2 class="font-headline-sm text-headline-sm text-text-primary">خدمات وإضافات متوافقة مع {{ $model->name }}</h2>
                    <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                        اضبط استهلاك التوكن واستخرج بيانات المستندات العربية بدقة مع أدوات إدراك Lab.
                    </p>
                    <div class="space-y-space-xs">
                        @foreach ($services ?? [] as $service)
                            <a href="{{ route('home').$service['anchor'] }}"
                               class="flex items-start gap-2.5 p-space-sm rounded-lg bg-surface-panel border border-border-subtle hover:border-primary/40 transition-colors">
                                <span class="material-symbols-outlined text-[18px] text-secondary mt-0.5 shrink-0" aria-hidden="true">{{ $service['icon'] }}</span>
                                <div class="min-w-0">
                                    <div class="font-body-sm text-body-sm text-text-primary font-semibold">{{ $service['title'] }}</div>
                                    <div class="font-label-code text-label-code text-text-tertiary">{{ $service['badge'] }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Deep specs -->
                <div class="bg-surface-subtle border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                    <h2 class="font-headline-sm text-headline-sm text-text-primary">المواصفات التقنية</h2>
                    <dl class="divide-y divide-border-subtle/60">
                        @php
                            $specRows = array_filter([
                                'المزود' => $providerName,
                                'النوع' => $model->is_open_source ? 'مفتوح الأوزان' : 'سحابي مغلق',
                                'المعمارية' => $model->architecture,
                                'حد المعرفة' => $model->knowledge_cutoff,
                                'نافذة السياق' => $context ? number_format($context).' توكن' : null,
                                'أقصى إخراج' => $maxOutput ? number_format($maxOutput).' توكن' : null,
                                'تاريخ الإطلاق' => $model->release_date?->toDateString(),
                                'معرف Hugging Face' => $model->hugging_face_id,
                            ], fn (mixed $value): bool => $value !== null && $value !== '');
                        @endphp
                        @foreach ($specRows as $label => $value)
                            <div class="flex items-start justify-between gap-space-sm py-space-xs">
                                <dt class="font-body-sm text-body-sm text-text-tertiary shrink-0">{{ $label }}</dt>
                                <dd class="font-metric-cell text-metric-cell text-text-primary text-left break-all">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <!-- Capabilities -->
                <div class="bg-surface-subtle border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                    <h2 class="font-headline-sm text-headline-sm text-text-primary">القدرات المدعومة</h2>
                    @if (count($capabilities))
                        <ul class="flex flex-wrap gap-space-xs">
                            @foreach ($capabilities as $capability)
                                <li class="px-space-sm py-1 rounded-full bg-surface-panel border border-border-subtle font-body-sm text-body-sm text-text-secondary">
                                    {{ $capability }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="font-body-sm text-body-sm text-text-tertiary">لم تُعلن قدرات إضافية لهذا النموذج.</p>
                    @endif
                </div>

                <!-- Data sources / trust -->
                <div class="bg-surface-subtle border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                    <h2 class="font-headline-sm text-headline-sm text-text-primary">مصادر البيانات</h2>
                    <ul class="space-y-space-xs font-body-sm text-body-sm text-text-secondary">
                        <li class="flex items-center gap-space-xs">
                            <span class="material-symbols-outlined text-[16px] text-metric-efficiency" aria-hidden="true">verified</span>
                            قياسات الاختبار العربي من مختبر إدراك Lab
                        </li>
                        <li class="flex items-center gap-space-xs">
                            <span class="material-symbols-outlined text-[16px] text-metric-speed" aria-hidden="true">sync</span>
                            مؤشرات الذكاء والأسعار من كتالوج OpenRouter
                        </li>
                        <li class="flex items-center gap-space-xs">
                            <span class="material-symbols-outlined text-[16px] text-metric-cost" aria-hidden="true">history</span>
                            آخر تحديث للبيانات: <time datetime="{{ $model->updated_at?->toDateString() }}">{{ $model->updated_at?->toDateString() }}</time>
                        </li>
                    </ul>
                    <a href="{{ route('home') }}#methodology" class="inline-flex items-center gap-1 font-body-sm text-body-sm text-primary hover:underline">
                        قراءة المنهجية الكاملة
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                    </a>
                </div>

                <!-- Related Research -->
                @if (! empty($relatedPosts))
                    <div class="bg-surface-subtle border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="font-headline-sm text-headline-sm text-text-primary">أبحاث مرتبطة</h2>
                            <a href="{{ route('blog.index') }}" class="font-label-code text-label-code text-primary hover:underline">المدونة</a>
                        </div>
                        <div class="space-y-space-xs">
                            @foreach ($relatedPosts as $article)
                                <a href="{{ route('blog.show', $article['slug']) }}"
                                   class="block p-space-sm rounded-lg bg-surface-panel border border-border-subtle hover:border-primary/40 transition-colors">
                                    <div class="font-label-code text-label-code text-secondary">{{ $article['category_label'] }} • {{ $article['key_metric_value'] }}</div>
                                    <div class="font-body-sm text-body-sm text-text-primary mt-0.5 leading-snug">{{ $article['title'] }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Share -->
                <div class="bg-surface-subtle border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                    <h2 class="font-headline-sm text-headline-sm text-text-primary">شارك ملف النموذج</h2>
                    <div class="flex flex-wrap items-center gap-space-xs">
                        <button type="button" @click="copy(window.location.href, 'link')"
                                class="flex items-center gap-space-xs px-space-md py-space-sm rounded-lg bg-surface-panel border border-border-subtle text-text-secondary hover:text-text-primary hover:border-border-highlight transition-colors font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-[16px]" x-text="copied === 'link' ? 'check' : 'link'"></span>
                            <span x-text="copied === 'link' ? 'تم نسخ الرابط' : 'نسخ الرابط'"></span>
                        </button>
                        <a href="{{ route('home') }}#leaderboard-section"
                           class="flex items-center gap-space-xs px-space-md py-space-sm rounded-lg bg-surface-panel border border-border-subtle text-text-secondary hover:text-text-primary hover:border-border-highlight transition-colors font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">compare_arrows</span>
                            قارن مع نموذج آخر
                        </a>
                    </div>
                    <p class="font-label-code text-label-code text-text-tertiary">
                        الرابط الدائم: <span dir="ltr" class="text-text-secondary">{{ $pageUrl }}</span>
                    </p>
                </div>
            </aside>
        </div>
    </section>

    <!-- Mobile Sticky Action Dock -->
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-surface-subtle/95 backdrop-blur-lg border-t border-border-subtle px-gutter py-space-xs flex items-center justify-between gap-space-xs">
        <button type="button" @click="goTo('pricing')" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-lg bg-primary-container text-on-primary-container font-body-sm text-body-sm font-semibold">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">calculate</span>
            حساب التكلفة
        </button>
        <a href="{{ route('home') }}#leaderboard-section" class="px-3 py-2 rounded-lg bg-surface-panel border border-border-subtle text-text-secondary font-body-sm text-body-sm">
            مقارنة
        </a>
        <button type="button" @click="copy(window.location.href, 'link')" class="px-3 py-2 rounded-lg bg-surface-panel border border-border-subtle text-text-secondary font-body-sm text-body-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]" x-text="copied === 'link' ? 'check' : 'link'"></span>
            <span x-text="copied === 'link' ? 'نُسخ' : 'رابط'"></span>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function modelProfile(config) {
        return {
            tab: 'benchmarks',
            codeLang: 'curl',
            monthlyWords: 100000,
            copied: null,
            config: config,
            probeText: 'وباستثناءاتهم القانونية في العقود المالية الموحدة يجب مطابقة جميع الفواتير الضريبية قبل الاعتماد النهائي.',
            probeSamples: {
                contract: 'وباستثناءاتهم القانونية في العقود المالية الموحدة يجب مطابقة جميع الفواتير الضريبية قبل الاعتماد النهائي.',
                finance: 'ارتفعت إيرادات الربع الثالث بنسبة 18.4% مقارنة بالعام الماضي مع تحسن هامش التشغيل في قطاع الحوسبة السحابية.',
                support: 'السلام عليكم، الطلب رقم 4821 تأخر عن موعد التوصيل المحدد وأحتاج لتحديث فوري لحالة الشحنة.',
            },
            codeLanguages: [
                { key: 'curl', label: 'cURL' },
                { key: 'python', label: 'Python' },
                { key: 'javascript', label: 'Node.js' },
                { key: 'php', label: 'Laravel' },
            ],

            setProbeSample(key) {
                this.probeText = this.probeSamples[key] || this.probeText;
            },

            probeWords() {
                const trimmed = (this.probeText || '').trim();
                return trimmed === '' ? 0 : trimmed.split(/\s+/).length;
            },

            probeTokens() {
                const words = this.probeWords();
                if (words === 0) {
                    return 0;
                }

                const ratio = this.config.tokensPerWord ?? 1.65;
                return Math.max(1, Math.round(words * ratio));
            },

            probeChips() {
                const words = (this.probeText || '').trim().split(/\s+/).filter(Boolean).slice(0, 12);
                const chips = [];
                words.forEach((word) => {
                    if (word.length > 7) {
                        chips.push(word.slice(0, 4), word.slice(4));
                    } else {
                        chips.push(word);
                    }
                });
                return chips;
            },

            probeTenKCost() {
                if (this.config.input === null) {
                    return '—';
                }
                const totalTokens = this.probeTokens() * 10000;
                const usd = (totalTokens / 1_000_000) * this.config.input;
                return '$' + usd.toFixed(2);
            },

            goTo(tabKey) {
                this.tab = tabKey;
                this.$nextTick(() => {
                    document.getElementById('profile-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },

            monthlyCost() {
                if (this.config.input === null || this.config.tokensPerWord === null) {
                    return 0;
                }

                const tokens = this.monthlyWords * this.config.tokensPerWord;
                const inputCost = (tokens * 0.75) / 1_000_000 * this.config.input;
                const outputCost = (tokens * 0.25) / 1_000_000 * (this.config.output ?? this.config.input);

                return inputCost + outputCost;
            },

            formatNumber(value) {
                return Number(value).toLocaleString('en-US');
            },

            copy(value, key) {
                if (!value) {
                    return;
                }

                navigator.clipboard.writeText(value).then(() => {
                    this.copied = key;
                    setTimeout(() => { if (this.copied === key) { this.copied = null; } }, 2000);
                });
            },

            code() {
                const model = this.config.openrouter || 'openrouter-model-id';

                const snippets = {
                    curl: `curl https://openrouter.ai/api/v1/chat/completions \\\n  -H "Authorization: Bearer $OPENROUTER_API_KEY" \\\n  -H "Content-Type: application/json" \\\n  -d '{\n    "model": "${model}",\n    "messages": [\n      {"role": "user", "content": "\u0627\u0643\u062a\u0628 \u0645\u0644\u062e\u0635\u0627\u064b \u0639\u0631\u0628\u064a\u0627\u064b \u0645\u0646 3 \u0623\u0633\u0637\u0631."}\n    ]\n  }'`,
                    python: `import os\nimport requests\n\nresponse = requests.post(\n    "https://openrouter.ai/api/v1/chat/completions",\n    headers={\n        "Authorization": f"Bearer {os.environ['OPENROUTER_API_KEY']}",\n        "Content-Type": "application/json",\n    },\n    json={\n        "model": "${model}",\n        "messages": [\n            {"role": "user", "content": "\u0627\u0643\u062a\u0628 \u0645\u0644\u062e\u0635\u0627\u064b \u0639\u0631\u0628\u064a\u0627\u064b \u0645\u0646 3 \u0623\u0633\u0637\u0631."}\n        ],\n    },\n    timeout=60,\n)\n\nprint(response.json()["choices"][0]["message"]["content"])`,
                    javascript: `const response = await fetch("https://openrouter.ai/api/v1/chat/completions", {\n  method: "POST",\n  headers: {\n    Authorization: \`Bearer \${process.env.OPENROUTER_API_KEY}\`,\n    "Content-Type": "application/json",\n  },\n  body: JSON.stringify({\n    model: "${model}",\n    messages: [\n      { role: "user", content: "\u0627\u0643\u062a\u0628 \u0645\u0644\u062e\u0635\u0627\u064b \u0639\u0631\u0628\u064a\u0627\u064b \u0645\u0646 3 \u0623\u0633\u0637\u0631." },\n    ],\n  }),\n});\n\nconst data = await response.json();\nconsole.log(data.choices[0].message.content);`,
                    php: `$response = Http::withToken(config('services.openrouter.key'))\n    ->post('https://openrouter.ai/api/v1/chat/completions', [\n        'model' => '${model}',\n        'messages' => [\n            ['role' => 'user', 'content' => '\u0627\u0643\u062a\u0628 \u0645\u0644\u062e\u0635\u0627\u064b \u0639\u0631\u0628\u064a\u0627\u064b \u0645\u0646 3 \u0623\u0633\u0637\u0631.'],\n        ],\n    ]);\n\n$answer = $response->json('choices.0.message.content');`,
                };

                return snippets[this.codeLang];
            },
        };
    }
</script>
@endpush
