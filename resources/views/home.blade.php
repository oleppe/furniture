@extends('layouts.public')

@php
    $homeUrl = route('home');
    $pageTitleText = 'مقارنة نماذج الذكاء الاصطناعي بالعربية: أسعار وسرعة ودقة | إدراك Lab';
    $pageDescriptionText = 'قارن أسعار وسرعة ودقة أكثر من 50 نموذج ذكاء اصطناعي على المحتوى العربي: كفاءة التوكن، اختبار من 156 نقطة، وسرعة التوليد، وحاسبة تكلفة بالريال والدولار.';

    // Single source of truth for both the visible FAQ and the FAQPage schema.
    $faqs = [
        [
            'question' => 'ما هو إدراك Lab؟',
            'answer' => 'إدراك Lab منصة عربية مستقلة تقيس أداء نماذج الذكاء الاصطناعي التوليدي على المحتوى العربي بقياسات فعلية لا تقديرية: كفاءة تقطيع التوكن، وجودة الفهم والاستدلال بالعربية، وسرعة التوليد، وتكلفة المليون توكن. الهدف هو مساعدة الفرق العربية على اختيار النموذج الأنسب لميزانيتها بدلاً من الاعتماد على أرقام تسويقية.',
        ],
        [
            'question' => 'كيف تُحسب كفاءة تقطيع التوكن العربي (Compression Factor)؟',
            'answer' => 'نرسل عينة نصية موحدة من 100,000 كلمة عربية تغطي نصوصاً قانونية وشعرية وإخبارية إلى كل نموذج، ثم نقيس عدد التوكنات المستهلكة لكل كلمة مقارنةً بالقياس الإنجليزي المرجعي. كلما اقترب الرقم من 1x زادت كفاءة النموذج مع العربية، وكلما ارتفع زادت فاتورتك الفعلية لنفس حجم المحتوى؛ فالنموذج بعامل 3x يستهلك نحو ضعف تكلفة نموذج بعامل 1.5x تقريباً.',
        ],
        [
            'question' => 'ما هي نقاط الاختبار العربي من 156؟',
            'answer' => 'درجة مركبة من 156 نقطة تقيس كيف يفهم النموذج اللغة العربية ويستجيب لها: التحليل النحوي والصرفي، دقة استخراج المعلومات من نصوص عربية، الالتزام بالقيود المطلوبة، والتعامل مع اللهجات. تُعرض النتيجة أيضاً كنسبة دقة مئوية لتسهيل المقارنة، وتظهر دائماً بجانب مؤشر الذكاء العام لأن النموذج قد يكون قوياً بالإنجليزية وأضعف بالعربية.',
        ],
        [
            'question' => 'ما الفرق بين مؤشر الذكاء العام والاختبار العربي؟',
            'answer' => 'مؤشر الذكاء العام مستمد من قياسات OpenRouter وArtificial Analysis العالمية ويقيس القدرات المنطقية والبرمجية بالإنجليزية أساساً، أما الاختبار العربي فيقيس أداء النموذج على نصوص عربية حقيقية. النموذج الأعلى عالمياً ليس بالضرورة الأفضل أو الأرخص للعربية، ولهذا تعرض لوحة المتصدرين المقياسين معاً مع السعر والسرعة.',
        ],
        [
            'question' => 'كم مرة يتم تحديث البيانات؟',
            'answer' => 'تُحدَّث أسعار المليون توكن تلقائياً من كتالوج المزودين، وتطلق خوادم الفحص في الرياض والمنامة اختبارات قياس زمن أول توكن وسرعة التوليد كل 15 دقيقة. أما نتائج الاختبار العربي فتُحدَّث مع كل جولة تشغيل جديدة، ويظهر تاريخ آخر تحديث أعلى لوحة المتصدرين.',
        ],
        [
            'question' => 'كيف أحسب التكلفة الشهرية لنموذج عربي؟',
            'answer' => 'استخدم حاسبة التكاليف داخل الصفحة: حدد حجم العمليات اليومية بالكلمات ونسبة المحتوى العربي، وستحصل على فاتورة تقديرية لمدة 30 يوماً بالدولار والريال مع احتساب معامل تقطيع التوكن العربي لكل نموذج ونسبة مدخلات إلى مخرجات 3:1. الأرقام تقديرية للتخطيط الميزاني وقد تختلف بحسب المزود ومستوى الخدمة المطلوب.',
        ],
        [
            'question' => 'أي نموذج يناسب مشروعي العربي: الأرخص أم الأذكى؟',
            'answer' => 'يعتمد على حساسية الخطأ في مهمتك: في المهام القانونية أو الطبية أو البحثية اختر النموذج الأعلى في الاختبار العربي حتى لو كان أغلى، بينما في التصنيف والترجمة ومعالجة المحتوى واسع النطاق اختر النموذج الأقل تكلفة فعلية بعد احتساب معامل التقطيع. استخدم لوحة المتصدرين للفرز حسب الدقة والتكلفة والسرعة ثم احسب فاتورتك في الحاسبة.',
        ],
    ];

    $rankedRows = collect($rows)->take(10)->values();
    $lastUpdated = $leaderboard->max('updated_at');

    $itemListSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        '@id' => $homeUrl.'#arabic-leaderboard',
        'name' => 'ترتيب نماذج الذكاء الاصطناعي حسب الأداء على اللغة العربية',
        'description' => 'ترتيب محدث لنماذج الذكاء الاصطناعي وفق نتائج اختبار إدراك العربي، ومؤشر الذكاء العام، وسرعة التوليد، وتكلفة المليون توكن.',
        'url' => $homeUrl.'#leaderboard-section',
        'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
        'numberOfItems' => $rankedRows->count(),
        'itemListElement' => $rankedRows->map(fn (array $row, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $row['name'],
            'item' => [
                '@type' => 'Thing',
                'name' => $row['name'],
                'description' => $row['description'],
                'additionalType' => 'https://schema.org/SoftwareApplication',
            ],
        ])->all(),
    ];

    $datasetSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Dataset',
        '@id' => $homeUrl.'#dataset',
        'name' => 'لوحة المتصدرين العربية لنماذج الذكاء الاصطناعي',
        'alternateName' => 'Arabic AI Model Leaderboard',
        'description' => 'قياسات مباشرة لكفاءة تقطيع التوكن العربي، ونقاط الاختبار العربي، وسرعة التوليد، وأسعار المليون توكن لكل نموذج ذكاء اصطناعي.',
        'url' => $homeUrl,
        'inLanguage' => 'ar',
        'isAccessibleForFree' => true,
        'creator' => ['@id' => $homeUrl.'#organization'],
        'publisher' => ['@id' => $homeUrl.'#organization'],
        'keywords' => [
            'نماذج الذكاء الاصطناعي بالعربية',
            'تكلفة التوكن العربي',
            'كفاءة تقطيع التوكن',
            'مقارنة نماذج اللغة',
            'MMLU-AR',
            'Arabic LLM benchmark',
        ],
        'measurementTechnique' => [
            'قياس كفاءة تقطيع التوكن على عينة موحدة من 100,000 كلمة عربية مقابل المرجع الإنجليزي',
            'مسبار نشط لقياس زمن أول توكن وسرعة التوليد كل 15 دقيقة',
            'معايرة نتائج الاختبار العربي مع أوزان LMSYS و MMLU-AR',
        ],
        'variableMeasured' => [
            ['@type' => 'PropertyValue', 'name' => 'مؤشر الذكاء العام', 'unitText' => 'نقطة'],
            ['@type' => 'PropertyValue', 'name' => 'كفاءة تقطيع التوكن العربي', 'unitText' => 'توكن لكل كلمة'],
            ['@type' => 'PropertyValue', 'name' => 'نقاط الاختبار العربي', 'unitText' => 'من 156'],
            ['@type' => 'PropertyValue', 'name' => 'سرعة التوليد', 'unitText' => 'توكن/ثانية'],
            ['@type' => 'PropertyValue', 'name' => 'سعر المليون توكن (إدخال)', 'unitText' => 'USD'],
            ['@type' => 'PropertyValue', 'name' => 'سعر المليون توكن (إخراج)', 'unitText' => 'USD'],
            ['@type' => 'PropertyValue', 'name' => 'أقصى حجم سياق', 'unitText' => 'توكن'],
        ],
    ];

    if ($lastUpdated) {
        $datasetSchema['dateModified'] = $lastUpdated->toIso8601String();
    }

    if ($datasetLicense = config('seo.dataset_license')) {
        $datasetSchema['license'] = $datasetLicense;
    }

    $webPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $homeUrl.'#webpage',
        'url' => $homeUrl,
        'name' => $pageTitleText,
        'description' => $pageDescriptionText,
        'inLanguage' => 'ar',
        'isPartOf' => ['@id' => $homeUrl.'#website'],
        'about' => [['@id' => $homeUrl.'#dataset']],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => asset(config('seo.social_image.path')),
            'width' => config('seo.social_image.width'),
            'height' => config('seo.social_image.height'),
        ],
        'speakable' => [
            '@type' => 'SpeakableSpecification',
            'cssSelector' => ['#page-heading', '#faq-heading'],
        ],
        'mainEntity' => [
            '@type' => 'WebApplication',
            'name' => 'لوحة المتصدرين العربية لنماذج الذكاء الاصطناعي',
            'applicationCategory' => 'BusinessApplication',
            'applicationSubCategory' => 'مقارنة نماذج الذكاء الاصطناعي',
            'operatingSystem' => 'Web',
            'browserRequirements' => 'Requires JavaScript',
            'url' => $homeUrl,
            'inLanguage' => 'ar',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ],
    ];

    if ($lastUpdated) {
        $webPageSchema['dateModified'] = $lastUpdated->toIso8601String();
    }

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        '@id' => $homeUrl.'#faq',
        'inLanguage' => 'ar',
        'mainEntity' => collect($faqs)->map(fn (array $faq): array => [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['answer'],
            ],
        ])->all(),
    ];
@endphp

@section('title', $pageTitleText)

@section('meta_description', $pageDescriptionText)

@section('canonical', $homeUrl)

@if ($lastUpdated)
    @section('meta_updated_at', $lastUpdated->toIso8601String())
@endif

@push('head')
    <script type="application/ld+json">
        @json($webPageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <script type="application/ld+json">
        @json($itemListSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <script type="application/ld+json">
        @json($datasetSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <script type="application/ld+json">
        @json($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
@endpush

@section('content')
<div x-data="leaderboardApp(@js($rows), @js($calcModels))">

    <!-- ==================== HERO ==================== -->
    <section class="hero-section w-full px-gutter-desktop pt-space-xl pb-space-lg relative overflow-hidden bg-gradient-to-b from-surface-subtle to-surface">
        <div class="hero-dot-grid absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, #4cd7f6 1px, transparent 0); background-size: 32px 32px;"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-space-xl items-center">

                <!-- Hero Copy -->
                <div class="space-y-space-md">
                    <div class="soft-badge inline-flex items-center gap-space-xs px-space-md py-space-xs rounded-full border border-primary/30 bg-primary/5">
                        <span class="w-1.5 h-1.5 rounded-full bg-metric-efficiency animate-pulse"></span>
                        <span class="font-label-code text-label-code text-primary">بيانات مستقلة وموثوقة 100%</span>
                    </div>

                    <h1 id="page-heading" class="font-headline-lg text-headline-lg lg:text-display-hero lg:font-display-hero text-text-primary leading-tight">
                        المرجع العربي الأول لمقارنة سرعة، ذكاء، وتكاليف نماذج الذكاء الاصطناعي
                    </h1>

                    <p class="font-body-lg text-body-lg text-text-secondary max-w-xl">
                        قارن سرعة وذكاء وتكلفة نماذج الذكاء الاصطناعي على المحتوى العربي، بقياسات فعلية لا تقديرية.
                    </p>

                    <div class="flex flex-wrap gap-space-sm">
                        <a href="#calculator-widget" class="flex items-center gap-space-xs px-space-lg py-space-md rounded-lg bg-primary-container text-on-primary-container hover:opacity-90 transition-opacity font-headline-sm text-headline-sm">
                            <span class="material-symbols-outlined text-[20px]">calculate</span>
                            حاسبة التكاليف السريعة
                        </a>
                    </div>
                </div>

                <!-- Highlight Card -->
                <div class="w-full">
                    @if ($highlights['best_arabic'])
                        <div class="accent-card--efficiency bg-surface-panel border border-border-subtle rounded-xl p-space-md">
                            <div class="flex flex-wrap items-center justify-between gap-space-md">
                                <div class="space-y-space-xs">
                                    <div class="flex items-center gap-space-xs">
                                        <span class="font-label-code text-label-code text-metric-efficiency">الأفضل عربياً هذا الأسبوع</span>
                                        <span class="material-symbols-outlined text-[18px] text-metric-efficiency">verified</span>
                                    </div>
                                    <div class="font-label-code text-label-code text-text-tertiary">{{ $highlights['best_arabic']->provider?->name }}</div>
                                    <div class="font-headline-md text-headline-md text-text-primary">{{ $highlights['best_arabic']->name }}</div>
                                </div>
                                <div class="flex items-center gap-space-md">
                                    <div>
                                        <div class="font-metric-display text-metric-display text-metric-efficiency">{{ number_format((float) $highlights['best_arabic']->benchmarkArabic->tokens_per_word, 2) }}x</div>
                                        <div class="font-body-sm text-body-sm text-text-tertiary">تقطيع</div>
                                    </div>
                                    <div class="w-px h-8 bg-border-subtle"></div>
                                    <div>
                                        <div class="font-metric-display text-metric-display text-text-primary" dir="ltr">{{ number_format((int) $highlights['best_arabic']->benchmarkArabic->total_score) }}/{{ 156 }}</div>
                                        <div class="font-body-sm text-body-sm text-text-tertiary">نقاط الاختبار العربي</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LEADERBOARD ==================== -->
    <section class="w-full px-gutter-desktop pt-space-lg pb-space-sm scroll-mt-24" id="leaderboard-section" aria-labelledby="leaderboard-heading">
        <div class="max-w-7xl mx-auto flex flex-col gap-space-md">

            <div class="space-y-space-xs">
                <h2 id="leaderboard-heading" class="font-headline-md text-headline-md text-text-primary">
                    لوحة المتصدرين: أفضل نماذج الذكاء الاصطناعي في اللغة العربية
                </h2>
                <p class="font-body-md text-body-md text-text-secondary max-w-3xl">
                    ترتيب محدّث لكل نموذج وفق مؤشر الذكاء العام، وكفاءة تقطيع التوكن العربي، ونقاط الاختبار العربي، وسرعة التوليد، وسعر المليون توكن — مع إمكانية فرز النتائج حسب الفئة والمزود.
                </p>

                <div class="flex flex-wrap items-center gap-space-sm font-label-code text-label-code text-text-tertiary">
                    <span class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">update</span>
                        آخر تحديث للبيانات:
                        <time datetime="{{ $lastUpdated?->toIso8601String() }}" class="text-text-secondary">
                            {{ $lastUpdated?->locale('ar')->translatedFormat('j F Y') ?? 'قيد التحديث' }}
                        </time>
                    </span>
                    <span class="w-px h-3 bg-border-subtle" aria-hidden="true"></span>
                    <span>{{ $stats['catalog_models'] }} نموذج نشط · {{ $stats['total_runs'] }} جولة اختبار</span>
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="flex flex-wrap items-center gap-space-xs" role="tablist" aria-label="فئات ترتيب النماذج">
                <template x-for="tab in tabs" :key="tab.key">
                    <button type="button"
                            role="tab"
                            :aria-selected="activeTab === tab.key"
                            @click="selectTab(tab.key)"
                            :class="activeTab === tab.key
                                ? 'bg-primary-container text-on-primary-container border-primary-container shadow-sm dark:bg-primary-container/15 dark:text-primary dark:border-primary/40'
                                : 'bg-surface-subtle text-text-secondary border-border-subtle hover:border-border-highlight hover:text-text-primary'"
                            class="px-space-md py-space-sm rounded-lg border font-body-sm text-body-sm transition-colors"
                            x-text="tab.label"></button>
                </template>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex flex-wrap items-center gap-space-sm">
                    <div class="flex items-center gap-space-xs bg-surface-panel border border-border-subtle rounded-lg px-space-md py-space-sm focus-within:border-primary/50 focus-within:ring-1 focus-within:ring-primary/30 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-text-tertiary" aria-hidden="true">search</span>
                        <label for="leaderboard-search" class="sr-only">بحث عن نموذج أو شركة مزودة</label>
                        <input type="text" id="leaderboard-search" x-model="search" placeholder="بحث عن نموذج أو شركة..."
                               aria-label="بحث عن نموذج أو شركة مزودة"
                               class="bg-transparent border-none outline-none font-body-sm text-body-sm text-text-primary placeholder:text-text-tertiary w-48">
                        <button type="button" x-show="search.trim() !== ''" x-cloak @click="search = ''"
                                aria-label="مسح البحث"
                                class="text-text-tertiary hover:text-text-primary transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                    </div>
                    <label for="leaderboard-provider" class="sr-only">تصفية حسب الشركة المزودة</label>
                    <select id="leaderboard-provider" x-model="providerFilter"
                            aria-label="تصفية حسب الشركة المزودة"
                            class="bg-surface-panel border border-border-subtle rounded-lg px-space-md py-space-sm font-body-sm text-body-sm text-text-secondary focus:border-primary/50 focus:outline-none cursor-pointer">
                        <option value="">جميع الشركات المزودة (All Providers)</option>
                        <template x-for="provider in providers" :key="provider">
                            <option :value="provider" x-text="provider"></option>
                        </template>
                    </select>
                    <button type="button" x-show="hasActiveFilters" x-cloak @click="resetFilters()"
                            class="flex items-center gap-space-xs px-space-sm py-space-sm rounded-lg border border-border-subtle text-text-secondary hover:border-primary/50 hover:text-primary transition-colors font-body-sm text-body-sm cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">restart_alt</span>
                        <span>مسح الفلاتر</span>
                    </button>
                </div>
                <div class="flex items-center gap-space-xs font-label-code text-label-code text-text-tertiary" aria-live="polite">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">tune</span>
                    <span>عرض <span class="text-text-primary" x-text="visibleRows.length"></span> من <span x-text="filteredRows.length"></span></span>
                </div>
            </div>

            <!-- No-JavaScript fallback: keeps the ranking readable for crawlers and text browsers -->
            <noscript>
                <div class="w-full overflow-x-auto bg-surface-subtle rounded-xl shadow-md">
                    <table class="w-full text-right border-collapse">
                        <caption class="p-space-md font-body-sm text-body-sm text-text-secondary">ترتيب النماذج حسب مؤشر الذكاء العام (أفضل 10 نماذج)</caption>
                        <thead>
                            <tr class="border-b border-border-subtle">
                                <th class="p-space-md font-label-code text-label-code text-text-tertiary" scope="col">#</th>
                                <th class="p-space-md font-label-code text-label-code text-text-tertiary" scope="col">النموذج</th>
                                <th class="p-space-md font-label-code text-label-code text-text-tertiary" scope="col">المزود</th>
                                <th class="p-space-md font-label-code text-label-code text-text-tertiary" scope="col">كفاءة التقطيع العربي</th>
                                <th class="p-space-md font-label-code text-label-code text-text-tertiary" scope="col">نقاط الاختبار العربي</th>
                                <th class="p-space-md font-label-code text-label-code text-text-tertiary" scope="col">السرعة (t/s)</th>
                                <th class="p-space-md font-label-code text-label-code text-text-tertiary" scope="col">سعر 1M توكن (إدخال / إخراج)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_slice($rows, 0, 10) as $index => $row)
                                <tr class="border-b border-border-subtle/60">
                                    <td class="p-space-md font-metric-cell text-metric-cell text-text-tertiary">{{ $index + 1 }}</td>
                                    <td class="p-space-md font-body-sm text-body-sm text-text-primary">
                                        <a href="{{ route('models.show', $row['slug']) }}" class="hover:text-primary transition-colors">{{ $row['name'] }}</a>
                                    </td>
                                    <td class="p-space-md font-body-sm text-body-sm text-text-secondary">{{ $row['provider'] }}</td>
                                    <td class="p-space-md font-metric-cell text-metric-cell text-metric-efficiency" dir="ltr">{{ $row['compression'] !== null ? number_format((float) $row['compression'], 2).'x' : '—' }}</td>
                                    <td class="p-space-md font-metric-cell text-metric-cell text-text-primary" dir="ltr">{{ $row['score'] !== null ? $row['score'].' / '.$row['score_max'] : '—' }}</td>
                                    <td class="p-space-md font-metric-cell text-metric-cell text-metric-speed" dir="ltr">{{ $row['tps'] !== null ? (int) round((float) $row['tps']) : '—' }}</td>
                                    <td class="p-space-md font-metric-cell text-metric-cell text-metric-cost" dir="ltr">${{ number_format((float) $row['in_price'], 2) }} / ${{ number_format((float) $row['out_price'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </noscript>

            <!-- Table View -->
            <div class="w-full overflow-x-auto bg-surface-subtle rounded-xl shadow-md" id="table-view-container">
                <table class="w-full text-right border-collapse min-w-[1150px]">
                    <caption class="sr-only">ترتيب نماذج الذكاء الاصطناعي حسب مؤشر الذكاء العام وكفاءة التقطيع العربي والسرعة والتكلفة</caption>
                    <thead>
                        <tr class="border-b border-border-subtle bg-surface-base/50">
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary"># والنموذج</th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary"
                                :aria-sort="sortKey === 'intelligence' ? (sortDir === -1 ? 'descending' : 'ascending') : 'none'">
                                <button type="button" @click="sortBy('intelligence')"
                                        :class="sortKey === 'intelligence' ? 'text-primary' : 'hover:text-text-secondary'"
                                        class="flex items-center gap-space-xs transition-colors cursor-pointer">
                                    مؤشر الذكاء العام
                                    <span class="material-symbols-outlined text-[14px]" x-text="sortIcon('intelligence')"></span>
                                </button>
                            </th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary"
                                :aria-sort="sortKey === 'compression' ? (sortDir === -1 ? 'descending' : 'ascending') : 'none'">
                                <button type="button" @click="sortBy('compression')"
                                        :class="sortKey === 'compression' ? 'text-primary' : 'hover:text-text-secondary'"
                                        class="flex items-center gap-space-xs transition-colors cursor-pointer">
                                    كفاءة التقطيع العربي
                                    <span class="material-symbols-outlined text-[14px]" x-text="sortIcon('compression')"></span>
                                </button>
                            </th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary"
                                :aria-sort="sortKey === 'score' ? (sortDir === -1 ? 'descending' : 'ascending') : 'none'">
                                <button type="button" @click="sortBy('score')"
                                        :class="sortKey === 'score' ? 'text-primary' : 'hover:text-text-secondary'"
                                        class="flex items-center gap-space-xs transition-colors cursor-pointer">
                                    نقاط الاختبار العربي (156)
                                    <span class="material-symbols-outlined text-[14px]" x-text="sortIcon('score')"></span>
                                </button>
                            </th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary"
                                :aria-sort="sortKey === 'tps' ? (sortDir === -1 ? 'descending' : 'ascending') : 'none'">
                                <button type="button" @click="sortBy('tps')"
                                        :class="sortKey === 'tps' ? 'text-primary' : 'hover:text-text-secondary'"
                                        class="flex items-center gap-space-xs transition-colors cursor-pointer">
                                    السرعة (t/s)
                                    <span class="material-symbols-outlined text-[14px]" x-text="sortIcon('tps')"></span>
                                </button>
                            </th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary">مستوى التفكير</th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary"
                                :aria-sort="sortKey === 'in_price' ? (sortDir === -1 ? 'descending' : 'ascending') : 'none'">
                                <button type="button" @click="sortBy('in_price')"
                                        :class="sortKey === 'in_price' ? 'text-primary' : 'hover:text-text-secondary'"
                                        class="flex items-center gap-space-xs transition-colors cursor-pointer">
                                    سعر 1M توكن (إدخال / إخراج)
                                    <span class="material-symbols-outlined text-[14px]" x-text="sortIcon('in_price')"></span>
                                </button>
                            </th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary">أقصى سياق</th>
                            <th class="p-space-md font-label-code text-label-code text-text-tertiary">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in visibleRows" :key="row.slug">
                            <tr class="border-b border-border-subtle/60 hover:bg-surface-panel/50 transition-colors">
                                <!-- Model -->
                                <td class="p-space-md">
                                    <div class="flex items-center gap-space-sm">
                                        <span class="rank-number font-metric-display text-metric-display text-text-tertiary" x-text="String(index + 1).padStart(2, '0')"></span>
                                        <div>
                                            <div class="flex items-center gap-space-xs">
                                                <a :href="row.url" class="font-headline-sm text-headline-sm text-text-primary hover:text-primary transition-colors" x-text="row.name"></a>
                                            </div>
                                            <span class="mt-0.5 inline-block px-space-xs py-0.5 rounded bg-surface-elevated font-label-code text-label-code text-text-tertiary" x-text="row.supports_vision ? 'Vision' : 'Text'"></span>
                                        </div>
                                    </div>
                                </td>
                                <!-- Intelligence Index -->
                                <td class="p-space-md">
                                    <div class="font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="row.intelligence !== null ? row.intelligence.toFixed(1) : '—'"></div>
                                    <div class="font-body-sm text-body-sm text-text-tertiary" x-text="row.coding_index !== null ? 'Coding: ' + row.coding_index.toFixed(1) : '—'"></div>
                                </td>
                                <!-- Compression -->
                                <td class="p-space-md">
                                    <div class="font-metric-cell text-metric-cell" :class="row.has_arabic ? 'text-metric-efficiency' : 'text-text-tertiary'" x-text="row.compression !== null ? row.compression.toFixed(2) + 'x' : '—'"></div>
                                    <div class="font-body-sm text-body-sm text-text-tertiary" x-text="row.has_arabic ? 'نسبة التقطيع' : 'لم يُقس عربياً'"></div>
                                </td>
                                <!-- Arabic Score -->
                                <td class="p-space-md">
                                    <div class="font-metric-cell text-metric-cell" :class="row.has_arabic ? 'text-text-primary' : 'text-text-tertiary'" dir="ltr" x-text="row.score !== null ? row.score + ' / ' + row.score_max : '—'"></div>
                                    <div class="font-body-sm text-body-sm" :class="row.score_pct !== null ? 'text-metric-efficiency' : 'text-text-tertiary'" x-text="row.score_pct !== null ? 'الدقة: ' + row.score_pct + '%' : 'لم يُختبر عربياً'"></div>
                                </td>
                                <!-- Speed -->
                                <td class="p-space-md">
                                    <div class="font-metric-cell text-metric-cell" :class="row.tps !== null ? 'text-metric-speed' : 'text-text-tertiary'" dir="ltr" x-text="row.tps !== null ? Math.round(row.tps) + ' t/s' : '—'"></div>
                                </td>
                                <!-- Thinking Level -->
                                <td class="p-space-md">
                                    <div class="font-metric-cell text-metric-cell" :class="row.thinking_level ? 'text-text-primary' : 'text-text-tertiary'" dir="ltr" x-text="thinkingLabel(row)"></div>
                                </td>
                                <!-- Pricing -->
                                <td class="p-space-md">
                                    <div class="flex items-center gap-space-sm" dir="ltr">
                                        <div class="text-right">
                                            <div class="font-metric-cell text-metric-cell text-metric-cost" x-text="'$' + formatPrice(row.in_price)"></div>
                                            <span class="font-body-sm text-body-sm text-text-tertiary">In</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-metric-cell text-metric-cell text-metric-cost" x-text="'$' + formatPrice(row.out_price)"></div>
                                            <span class="font-body-sm text-body-sm text-text-tertiary">Out</span>
                                        </div>
                                    </div>
                                </td>
                                <!-- Context -->
                                <td class="p-space-md">
                                    <div class="font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="formatContext(row.context)"></div>
                                </td>
                                <!-- Actions -->
                                <td class="p-space-md">
                                    <button type="button" @click="toggleCompare(row)"
                                            :disabled="compared.length >= 2 && !isCompared(row)"
                                            :title="compared.length >= 2 && !isCompared(row) ? 'تم بلوغ الحد الأقصى (نموذجان) — أزل نموذجاً من شريط المقارنة لإضافة غيره' : ''"
                                            :class="isCompared(row) ? 'bg-primary/20 text-primary border-primary/40' : 'text-text-secondary border-border-subtle hover:border-primary/50 hover:text-primary'"
                                            class="px-space-sm py-space-xs rounded-lg border font-body-sm text-body-sm transition-colors whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                        <span x-text="isCompared(row) ? '✓ مضاف' : (compared.length >= 2 ? 'حد المقارنة' : '+ قارن')"></span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="visibleRows.length === 0" x-cloak>
                            <td colspan="9" class="p-space-xl text-center">
                                <div class="flex flex-col items-center justify-center gap-space-sm max-w-md mx-auto">
                                    <span class="material-symbols-outlined text-[28px] text-text-tertiary" aria-hidden="true">search_off</span>
                                    <div class="font-headline-sm text-headline-sm text-text-primary">لا توجد نماذج مطابقة</div>
                                    <p class="font-body-sm text-body-sm text-text-secondary">جرّب تعديل كلمة البحث أو اختيار مزود آخر لعرض النتائج المتاحة.</p>
                                    <button type="button" @click="resetFilters()"
                                            class="px-space-md py-space-sm rounded-lg bg-primary-container text-on-primary-container hover:opacity-90 transition-opacity font-body-sm text-body-sm font-semibold cursor-pointer">
                                        إعادة ضبط الفلاتر
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Show More -->
            <div class="flex items-center justify-center gap-space-sm" x-show="filteredRows.length > 8">
                <button type="button" x-show="visibleRows.length < filteredRows.length" @click="limit += 8"
                        class="px-space-lg py-space-sm rounded-lg border border-border-highlight text-text-primary hover:border-primary/50 hover:text-primary transition-colors font-body-sm text-body-sm">
                    عرض المزيد (<span x-text="filteredRows.length - visibleRows.length"></span> متبقٍ)
                </button>
                <button type="button" x-show="limit > 8" @click="limit = 8"
                        class="px-space-lg py-space-sm rounded-lg border border-border-subtle text-text-secondary hover:text-text-primary transition-colors font-body-sm text-body-sm">
                    عرض أقل
                </button>
            </div>

            <!-- Compression Standard Note -->
            <div class="flex flex-wrap items-center justify-between gap-space-sm bg-surface-subtle/60 border border-border-subtle rounded-lg px-space-md py-space-sm">
                <div class="flex items-center gap-space-xs font-body-sm text-body-sm text-text-secondary">
                    <span class="material-symbols-outlined text-[18px] text-metric-cost">info</span>
                    <span><b class="text-text-primary">معيار معدل التقطيع (Compression Ratio):</b> عدد التوكنات المستهلكة لكل كلمة عربية مقارنة بالقياس الإنجليزي المرجعي.</span>
                </div>
                <a href="#methodology" class="font-body-sm text-body-sm text-primary hover:underline whitespace-nowrap">قراءة وثيقة المنهجية الكاملة ←</a>
            </div>
        </div>
    </section>

    <!-- ==================== COST CALCULATOR ==================== -->
    <section class="w-full px-gutter-desktop py-space-xl scroll-mt-24" id="calculator-widget" aria-labelledby="calculator-heading">
        <div class="max-w-7xl mx-auto">
            <div class="bg-surface-subtle p-space-xl rounded-xl shadow-lg relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-64 h-64 rounded-full bg-primary-container/10 blur-3xl"></div>

                <div class="relative z-10 space-y-space-lg">
                    <!-- Header -->
                    <div class="space-y-space-xs">
                        <div class="flex items-center gap-space-xs font-label-code text-label-code text-metric-cost">
                            <span class="material-symbols-outlined text-[18px]">calculate</span>
                            أداة تخطيط الميزانية الهندسية
                        </div>
                        <h2 id="calculator-heading" class="font-headline-lg text-headline-lg text-text-primary">
                            حاسبة التكلفة السريعة بالريال والدولار للمشاريع العربية
                        </h2>
                        <p class="font-body-md text-body-md text-text-secondary max-w-2xl">
                            احسب الفاتورة الشهرية المتوقعة بدقة مع الأخذ بالاعتبار معامل التقطيع العربي الفعلي لكل نموذج بدلاً من الأرقام النظرية.
                        </p>
                    </div>

                    <!-- Controls -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-space-lg">
                        <!-- Daily Volume -->
                        <div class="space-y-space-sm">
                            <label class="font-body-md text-body-md text-text-secondary">حجم العمليات اليومية (كلمة / يوم):</label>
                            <div class="flex items-baseline gap-space-sm">
                                <span class="font-metric-display text-metric-display text-primary" x-text="formatNumber(dailyWords) + ' كلمة'"></span>
                            </div>
                            <input type="range" min="5000" max="500000" step="5000" x-model.number="dailyWords"
                                   class="w-full accent-primary-container cursor-pointer">
                            <div class="flex justify-between font-label-code text-label-code text-text-tertiary">
                                <span>5,000 كلمة (موقع تجريبي)</span>
                                <span>250k كلمة (تطبيق مؤسسي)</span>
                                <span>500k كلمة (منصة مليونية)</span>
                            </div>
                        </div>

                        <!-- Arabic Ratio -->
                        <div class="space-y-space-sm">
                            <label class="font-body-md text-body-md text-text-secondary">نسبة المحتوى العربي في الاستعلامات:</label>
                            <div class="flex items-baseline gap-space-sm">
                                <span class="font-metric-display text-metric-display text-primary" x-text="arabicRatio + '% عربي'"></span>
                            </div>
                            <input type="range" min="10" max="100" step="5" x-model.number="arabicRatio"
                                   class="w-full accent-primary-container cursor-pointer">
                            <div class="flex justify-between font-label-code text-label-code text-text-tertiary">
                                <span>10% (استعلامات تقنية مدمجة)</span>
                                <span>50% (نظام ثنائي اللغة)</span>
                                <span>100% (محتوى عربي صرف)</span>
                            </div>
                            <div class="font-label-code text-label-code text-text-tertiary pt-space-xs">معامل مدخلات/مخرجات: 3:1 قياسي</div>
                        </div>
                    </div>

                    <!-- Results -->
                    <div class="space-y-space-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="font-headline-sm text-headline-sm text-text-primary">مقارنة الفاتورة الشهرية التقديرية</h3>
                            <span class="font-label-code text-label-code text-text-tertiary">شهرياً (30 يوم)</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-sm">
                            <template x-for="model in calcModels" :key="model.name">
                                <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-md space-y-space-xs">
                                    <div class="font-headline-sm text-headline-sm text-text-primary" x-text="model.name"></div>
                                    <div class="font-label-code text-label-code text-metric-cost" x-text="model.tag"></div>
                                    <div class="pt-space-xs">
                                        <div class="font-metric-display text-metric-display text-text-primary" x-text="'$' + monthlyCost(model).usd.toFixed(2)"></div>
                                        <div class="font-metric-cell text-metric-cell text-metric-efficiency" x-text="monthlyCost(model).sar.toFixed(2) + ' ر.س / شهر'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Export -->
                    <div class="flex justify-end">
                        <button type="button" @click="window.print()"
                                class="flex items-center gap-space-xs px-space-lg py-space-md rounded-lg border border-border-highlight text-text-primary hover:border-primary/50 hover:text-primary transition-colors font-body-md text-body-md">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                            تصدير تقرير التكاليف التقديري (PDF)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== METHODOLOGY ==================== -->
    <section class="w-full px-gutter-desktop py-space-xl bg-surface-subtle scroll-mt-24" id="methodology" aria-labelledby="methodology-heading">
        <div class="max-w-7xl mx-auto space-y-space-lg">
            <div class="max-w-3xl space-y-space-xs">
                <div class="flex items-center gap-space-xs font-label-code text-label-code text-primary">
                    <span class="material-symbols-outlined text-[18px]">api</span>
                    معايير الشفافية التقنية
                </div>
                <h2 id="methodology-heading" class="font-headline-lg text-headline-lg text-text-primary">منهجية القياس والاختبار في منصة إدراك Lab</h2>
                <p class="font-body-md text-body-md text-text-secondary">
                    كيف نحسب كفاءة التوكن العربي وزمن الاستجابة بدقة علمية قابلة للتحقق من قبل مجتمع المصادر المفتوحة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
                <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-lg space-y-space-sm">
                    <div class="font-metric-display text-metric-display text-primary">01</div>
                    <h3 class="font-headline-sm text-headline-sm text-text-primary">معدل تقطيع التوكن (Compression Factor)</h3>
                    <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                        نرسل عينة نصية موحدة من 100,000 كلمة مأخوذة من نصوص قانونية، شعرية، وإخبارية متنوعة لحساب عدد الـ Tokens الناتجة لكل كلمة مقارنة بمقياس اللغة الإنجليزية.
                    </p>
                </div>
                <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-lg space-y-space-sm">
                    <div class="font-metric-display text-metric-display text-secondary">02</div>
                    <h3 class="font-headline-sm text-headline-sm text-text-primary">المسبار النشط (Active Probing TTFT)</h3>
                    <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                        تطلق خوادم الفحص بالرياض والمنامة اختبارات كل 15 دقيقة عبر واجهات الـ REST API لقياس زمن وصول أول بايت استجابة وسرعة التدفق المستقر تحت الضغط.
                    </p>
                </div>
                <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-lg space-y-space-sm">
                    <div class="font-metric-display text-metric-display text-tertiary">03</div>
                    <h3 class="font-headline-sm text-headline-sm text-text-primary">معايرة LMSYS و MMLU-AR</h3>
                    <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                        مزامنة حية مع لوحة Arena العالمية مع ترجيح أوزان الأسئلة المنطقية واللغوية العربية لضمان توافق الترتيب مع القدرات الفكرية الحقيقية لكل نموذج.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FAQ ==================== -->
    <section class="w-full px-gutter-desktop py-space-xl scroll-mt-24" id="faq" aria-labelledby="faq-heading">
        <div class="max-w-7xl mx-auto space-y-space-lg">
            <div class="max-w-3xl space-y-space-xs">
                <div class="flex items-center gap-space-xs font-label-code text-label-code text-primary">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">help_outline</span>
                    أسئلة تتكرر
                </div>
                <h2 id="faq-heading" class="font-headline-lg text-headline-lg text-text-primary">
                    الأسئلة الشائعة حول مقارنة نماذج الذكاء الاصطناعي بالعربية
                </h2>
                <p class="font-body-md text-body-md text-text-secondary">
                    إجابات مباشرة عن منهجية القياس، ومصادر البيانات، وطريقة اختيار النموذج الأنسب لمشروعك العربي.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                @foreach ($faqs as $faq)
                    <article class="bg-surface-panel border border-border-subtle rounded-xl p-space-lg space-y-space-xs">
                        <h3 class="font-headline-sm text-headline-sm text-text-primary">{{ $faq['question'] }}</h3>
                        <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">{{ $faq['answer'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ==================== COMPARE TRAY ==================== -->
    <div x-show="compared.length > 0" x-cloak
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
         class="fixed bottom-0 inset-x-0 z-50 bg-surface-panel/95 backdrop-blur-xl border-t border-border-highlight">
        <div class="max-w-7xl mx-auto px-gutter-desktop py-space-md flex flex-wrap items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
                <span class="material-symbols-outlined text-[20px] text-primary">compare_arrows</span>
                <div>
                    <div class="font-headline-sm text-headline-sm text-text-primary">المقارنة المباشرة</div>
                    <div class="font-body-sm text-body-sm text-text-tertiary"
                         x-text="compared.length < 2 ? 'تم اختيار نموذج واحد — اختر نموذجاً ثانياً للمقارنة' : 'جاهز للمقارنة المباشرة بين نموذجين'"></div>
                </div>
                <div class="flex flex-wrap items-center gap-space-xs">
                    <template x-for="row in compared" :key="'chip-' + row.slug">
                        <span class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-full bg-primary/10 border border-primary/30 font-label-code text-label-code text-primary">
                            <span x-text="row.name"></span>
                            <button type="button" @click="toggleCompare(row)" aria-label="إزالة من المقارنة" class="hover:text-text-primary cursor-pointer">×</button>
                        </span>
                    </template>
                </div>
            </div>
            <div class="flex items-center gap-space-sm">
                <button type="button" @click="compared = []; compareOpen = false"
                        class="px-space-md py-space-sm rounded-lg border border-border-subtle text-text-secondary hover:text-text-primary hover:border-border-highlight transition-colors font-body-sm text-body-sm cursor-pointer">
                    إلغاء
                </button>
                <button type="button" :disabled="compared.length < 2" @click="compareOpen = true"
                        class="px-space-md py-space-sm rounded-lg bg-primary-container text-on-primary-container hover:opacity-90 transition-opacity font-body-sm text-body-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    فتح المقارنة
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== SIDE-BY-SIDE COMPARE MODAL ==================== -->
    <div x-show="compareOpen && compared.length === 2" x-cloak
         @keydown.escape.window="compareOpen = false"
         role="dialog" aria-modal="true" aria-labelledby="compare-modal-title"
         class="fixed inset-0 z-50 flex items-center justify-center p-gutter bg-surface-base/80 backdrop-blur-sm"
         @click.self="compareOpen = false">
        <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto bg-surface-panel border border-border-highlight rounded-xl">
            <div class="flex items-center justify-between px-space-lg py-space-md border-b border-border-subtle">
                <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-[20px] text-primary">compare_arrows</span>
                    <h3 id="compare-modal-title" class="font-headline-md text-headline-md text-text-primary">مقارنة مباشرة بين نموذجين</h3>
                </div>
                <button type="button" @click="compareOpen = false" aria-label="إغلاق نافذة المقارنة"
                        class="p-space-xs rounded-lg border border-border-subtle text-text-secondary hover:text-text-primary hover:border-border-highlight transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="p-space-lg overflow-x-auto" x-if="compared.length === 2">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="border-b border-border-subtle">
                            <th class="py-space-sm px-space-md font-label-code text-label-code text-text-tertiary">المعيار</th>
                            <template x-for="item in compared" :key="'cmp-head-' + item.slug">
                                <th class="py-space-sm px-space-md">
                                    <a :href="item.url" class="font-headline-sm text-headline-sm text-primary hover:underline" x-text="item.name"></a>
                                    <div class="font-label-code text-label-code text-text-tertiary mt-0.5" x-text="item.provider"></div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle/60 font-body-sm text-body-sm">
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">مؤشر الذكاء العام</td>
                            <template x-for="item in compared" :key="'cmp-int-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="item.intelligence !== null ? item.intelligence.toFixed(1) : '—'"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">البرمجة (Coding)</td>
                            <template x-for="item in compared" :key="'cmp-cod-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="item.coding_index !== null ? item.coding_index.toFixed(1) : '—'"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">كفاءة التقطيع العربي</td>
                            <template x-for="item in compared" :key="'cmp-cmp-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell" :class="item.has_arabic ? 'text-metric-efficiency' : 'text-text-tertiary'" dir="ltr" x-text="item.compression !== null ? item.compression.toFixed(2) + 'x' : '—'"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">نقاط الاختبار العربي</td>
                            <template x-for="item in compared" :key="'cmp-scr-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="item.score !== null ? item.score + ' / ' + item.score_max + (item.score_pct !== null ? ' (' + item.score_pct + '%)' : '') : '—'"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">السرعة (توكن/ثانية)</td>
                            <template x-for="item in compared" :key="'cmp-tps-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell text-metric-speed" dir="ltr" x-text="item.tps !== null ? Math.round(item.tps) + ' t/s' : '—'"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">مستوى التفكير</td>
                            <template x-for="item in compared" :key="'cmp-thk-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="thinkingLabel(item)"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">سعر الإدخال / الإخراج (لكل 1M)</td>
                            <template x-for="item in compared" :key="'cmp-prc-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell text-metric-cost" dir="ltr" x-text="'$' + formatPrice(item.in_price) + ' / $' + formatPrice(item.out_price)"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">حجم السياق</td>
                            <template x-for="item in compared" :key="'cmp-ctx-' + item.slug">
                                <td class="py-space-sm px-space-md font-metric-cell text-metric-cell text-text-primary" dir="ltr" x-text="formatContext(item.context)"></td>
                            </template>
                        </tr>
                        <tr>
                            <td class="py-space-sm px-space-md text-text-secondary">القدرات</td>
                            <template x-for="item in compared" :key="'cmp-cap-' + item.slug">
                                <td class="py-space-sm px-space-md">
                                    <div class="flex flex-wrap gap-space-xs">
                                        <span class="px-space-xs py-0.5 rounded bg-surface-elevated font-label-code text-label-code text-text-secondary" x-text="item.supports_vision ? 'دعم الرؤية' : 'نصوص فقط'"></span>
                                        <span x-show="item.is_open_source" class="px-space-xs py-0.5 rounded bg-surface-elevated font-label-code text-label-code text-metric-efficiency">مفتوح المصدر</span>
                                    </div>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function leaderboardApp(rows, calcModels) {
        return {
            rows: rows,
            calcModels: calcModels.length ? calcModels : [
                { name: '—', tag: 'لا توجد بيانات', tokens_per_word: 2, in_price: 0, out_price: 0 }
            ],
            tabs: [
                { key: 'all', label: 'الكل (' + rows.length + ')' },
                { key: 'arabic', label: 'الأفضل في اللغة العربية (' + rows.filter(row => row.has_arabic).length + ')' },
                { key: 'fast', label: 'الأسرع استجابة (TPS)' },
                { key: 'budget', label: 'الأقل تكلفة (Budget)' },
                { key: 'open', label: 'مفتوحة المصدر (Open Weights)' },
                { key: 'vision', label: 'دعم الرؤية (Multimodal)' },
            ],
            activeTab: 'all',
            search: '',
            providerFilter: '',
            sortKey: 'intelligence',
            sortDir: -1,
            limit: 8,
            compared: [],
            compareOpen: false,
            dailyWords: 50000,
            arabicRatio: 85,

            get providers() {
                return [...new Set(this.rows.map(row => row.provider))].sort();
            },

            get filteredRows() {
                let result = this.rows;

                const query = this.search.trim().toLowerCase();
                if (query) {
                    result = result.filter(row =>
                        row.name.toLowerCase().includes(query) ||
                        row.provider.toLowerCase().includes(query)
                    );
                }

                if (this.providerFilter) {
                    result = result.filter(row => row.provider === this.providerFilter);
                }

                switch (this.activeTab) {
                    case 'arabic':
                        result = result.filter(row => row.has_arabic);
                        break;
                    case 'fast':
                        result = result.filter(row => row.tps !== null);
                        break;
                    case 'budget':
                        result = result.filter(row => row.in_price > 0);
                        break;
                    case 'open':
                        result = result.filter(row => row.is_open_source);
                        break;
                    case 'vision':
                        result = result.filter(row => row.supports_vision);
                        break;
                }

                return result;
            },

            get sortedRows() {
                return [...this.filteredRows].sort((a, b) => {
                    const aValue = a[this.sortKey];
                    const bValue = b[this.sortKey];

                    if (aValue === null || aValue === undefined) return 1;
                    if (bValue === null || bValue === undefined) return -1;

                    return (aValue - bValue) * this.sortDir;
                });
            },

            get visibleRows() {
                return this.sortedRows.slice(0, this.limit);
            },

            get hasActiveFilters() {
                return this.search.trim() !== '' || this.providerFilter !== '' || this.activeTab !== 'all';
            },

            resetFilters() {
                this.search = '';
                this.providerFilter = '';
                this.selectTab('all');
            },

            sortIcon(key) {
                if (this.sortKey !== key) return 'unfold_more';
                return this.sortDir === -1 ? 'arrow_downward' : 'arrow_upward';
            },

            selectTab(key) {
                this.activeTab = key;
                this.limit = 8;

                const defaultSorts = {
                    arabic: ['score', -1],
                    fast: ['tps', -1],
                    budget: ['in_price', 1],
                };
                const [sortKey, sortDir] = defaultSorts[key] ?? ['intelligence', -1];

                this.sortKey = sortKey;
                this.sortDir = sortDir;
            },

            sortBy(key) {
                if (this.sortKey === key) {
                    this.sortDir *= -1;
                } else {
                    this.sortKey = key;
                    this.sortDir = -1;
                }
            },

            toggleCompare(row) {
                const index = this.compared.findIndex(item => item.slug === row.slug);
                if (index >= 0) {
                    this.compared.splice(index, 1);
                    if (this.compared.length < 2) {
                        this.compareOpen = false;
                    }
                } else if (this.compared.length < 2) {
                    this.compared.push(row);
                }
            },

            isCompared(row) {
                return this.compared.some(item => item.slug === row.slug);
            },

            formatPrice(value) {
                if (!value) return '0.00';
                return value >= 1 ? value.toFixed(2) : value.toFixed(3);
            },

            thinkingLabel(row) {
                const labels = {
                    minimal: 'Minimal',
                    low: 'Low',
                    medium: 'Medium',
                    high: 'High',
                    xhigh: 'XHigh',
                };

                return labels[row.thinking_level] || '—';
            },

            formatContext(value) {
                if (!value) return '—';
                if (value >= 1000000) return (value / 1000000).toFixed(2).replace(/\.?0+$/, '') + 'M';
                return value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value;
            },

            formatNumber(value) {
                return Number(value).toLocaleString('en-US');
            },

            monthlyCost(model) {
                // 30 days of words, blended tokens/word weighted by Arabic ratio,
                // standard 3:1 input-to-output token ratio.
                const monthlyWords = this.dailyWords * 30;
                const blendedTpw = (this.arabicRatio / 100) * model.tokens_per_word
                    + (1 - this.arabicRatio / 100) * 1.33;
                const totalTokens = monthlyWords * blendedTpw;
                const inputTokens = totalTokens * 0.75;
                const outputTokens = totalTokens * 0.25;
                const usd = (inputTokens / 1_000_000) * model.in_price
                    + (outputTokens / 1_000_000) * model.out_price;

                return { usd: usd, sar: usd * 3.75 };
            },
        };
    }
</script>
@endpush
@endsection
