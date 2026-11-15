<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\ArabicBenchmarkRun;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Services\ModelRankingService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(private readonly ModelRankingService $rankingService) {}

    /**
     * Display the public Arabic AI research blog index.
     */
    public function index(): View
    {
        $posts = self::posts();
        $featuredPost = collect($posts)->firstWhere('featured', true) ?? $posts[0];

        return view('blog.index', [
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'categories' => self::categories(),
            'services' => self::promotedServices(),
            'topModels' => $this->topModels(),
            'stats' => $this->liveStats(),
        ]);
    }

    /**
     * Display a single Arabic technical research article.
     */
    public function show(string $slug): View
    {
        $posts = collect(self::posts());
        $post = $posts->firstWhere('slug', $slug);

        abort_if($post === null, 404);

        $post = $this->hydratePostMediaAndQuotes($post);

        $relatedPosts = $posts
            ->reject(fn (array $candidate): bool => $candidate['slug'] === $slug)
            ->sortByDesc(fn (array $candidate): int => $candidate['category_key'] === $post['category_key'] ? 1 : 0)
            ->take(3)
            ->values()
            ->all();

        $spotlightModel = $this->resolveSpotlightModel($post['spotlight_model_slug'] ?? null);

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'services' => self::promotedServices(),
            'spotlightModel' => $spotlightModel,
            'stats' => $this->liveStats(),
        ]);
    }

    /**
     * Filter categories for the research blog index.
     *
     * @return list<array{key: string, label: string}>
     */
    public static function categories(): array
    {
        $defaults = [
            ['key' => 'tokenomics', 'label' => 'التوكن العربي'],
            ['key' => 'benchmarks', 'label' => 'المعايير القياسية'],
            ['key' => 'economics', 'label' => 'اقتصاديات النماذج'],
            ['key' => 'doc-ai', 'label' => 'المستندات و Doc AI'],
        ];

        try {
            $dbCategories = BlogCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (BlogCategory $cat): array => [
                    'key' => $cat->slug,
                    'label' => $cat->name,
                ])
                ->all();

            $merged = collect($dbCategories)
                ->merge($defaults)
                ->unique('key')
                ->values()
                ->all();
        } catch (\Throwable) {
            $merged = $defaults;
        }

        return array_merge([['key' => 'all', 'label' => 'جميع الأبحاث']], $merged);
    }

    /**
     * Return merged published database articles and curated default studies.
     *
     * @return list<array<string, mixed>>
     */
    public static function posts(): array
    {
        $defaults = collect(self::defaultPosts());

        try {
            $dbPosts = BlogArticle::query()
                ->with(['category', 'spotlightModel'])
                ->where('is_published', true)
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get()
                ->map(function (BlogArticle $article): array {
                    $blocks = $article->blocks ?? [];
                    $publishedAt = $article->published_at ?? $article->created_at ?? now();

                    return [
                        'slug' => $article->slug,
                        'report_id' => $article->report_id ?: 'EL-TR-'.$publishedAt->format('Y-md'),
                        'featured' => (bool) $article->is_featured,
                        'category_key' => $article->category?->slug ?? 'general',
                        'category_label' => $article->category?->name ?? 'أبحاث تقنية',
                        'title' => $article->title,
                        'excerpt' => $article->excerpt,
                        'read_time' => $article->read_time ?: '8 دقائق قراءة',
                        'published_at' => $publishedAt->toDateString(),
                        'published_label' => $publishedAt->format('Y-m-d'),
                        'author' => $article->author_name ?: 'فريق أبحاث إدراك Lab',
                        'author_role' => $article->author_role ?: 'مختبر اقتصاديات النماذج العربية',
                        'key_metric_value' => $article->key_metric_value ?: 'Live Study',
                        'key_metric_label' => $article->key_metric_label ?: 'دراسة تطبيقية',
                        'spotlight_model_slug' => $article->spotlightModel?->slug,
                        'hero_metrics' => $article->hero_metrics ?? [],
                        'takeaways' => $article->takeaways ?? [],
                        'sections' => $article->sections ?? [],
                        'comparison_rows' => $article->comparison_rows ?? [],
                        'charts' => $blocks['charts'] ?? [],
                        'tables' => $blocks['tables'] ?? [],
                        'quoted_models' => $blocks['quoted_models'] ?? [],
                        'code_snippet' => $article->code_snippet,
                    ];
                });

            if ($dbPosts->isEmpty()) {
                return $defaults->values()->all();
            }

            $dbSlugs = $dbPosts->pluck('slug')->all();

            return $dbPosts
                ->merge($defaults->reject(fn (array $item): bool => in_array($item['slug'], $dbSlugs, true)))
                ->values()
                ->all();
        } catch (\Throwable) {
            return $defaults->values()->all();
        }
    }

    /**
     * Focused services and upcoming plugin features promoted across blog and model pages.
     *
     * @return list<array{key: string, badge: string, title: string, description: string, icon: string, cta_label: string, anchor: string}>
     */
    public static function promotedServices(): array
    {
        return [
            [
                'key' => 'token-plugin',
                'badge' => 'إضافة إدراك Lab (Plugin)',
                'title' => 'مراقب التوكن العربي وتوجيه النماذج',
                'description' => 'إضافة للمتصفح وبيئات التطوير تقيس استهلاك التوكن العربي لحظياً وتقترح النموذج الأوفر لكل مهمة قبل الإرسال.',
                'icon' => 'extension',
                'cta_label' => 'احسب التوفير الآن',
                'anchor' => '#calculator-widget',
            ],
            [
                'key' => 'doc-ai',
                'badge' => 'خدمة Doc AI العربية',
                'title' => 'تحويل ملفات PDF والعقود إلى JSON دقيق',
                'description' => 'استخراج الجداول والنصوص العربية المعقدة بنسبة التزام تتجاوز 99% مع تهيئة تلقائية لأنظمة RAG و ERP.',
                'icon' => 'description',
                'cta_label' => 'استكشف أدوات المستندات',
                'anchor' => '#doc-ai',
            ],
            [
                'key' => 'smart-routing',
                'badge' => 'بوابة التوجيه الذكي',
                'title' => 'موازنة الأداء والتكلفة عبر API موحد',
                'description' => 'وزّع طلبات التلخيص على النماذج الاقتصادية والطلبات المعقدة على نماذج الاستدلال مع تقليص الفاتورة حتى 42%.',
                'icon' => 'route',
                'cta_label' => 'قارن الأسعار والسرعة',
                'anchor' => '#leaderboard-section',
            ],
        ];
    }

    /**
     * Curated Arabic AI technical research articles.
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultPosts(): array
    {
        return [
            [
                'slug' => 'arabic-token-efficiency-analysis',
                'report_id' => 'EL-TR-2025-09',
                'featured' => true,
                'category_key' => 'tokenomics',
                'category_label' => 'التوكن العربي',
                'title' => 'تحليل كفاءة التوكن العربي: لماذا تستهلك النماذج العالمية 3 أضعاف التكلفة في النصوص العربية؟',
                'excerpt' => 'دراسة قياسية مقارنة لمعماريات التقطيع اللغوي (BPE و Tiktoken) عبر 12.5 مليون كلمة عربية من الوثائق القانونية والمالية والتقنية، مع مقارنة عملية بين DeepSeek-R1 و Claude 3.5 Sonnet و GPT-4o.',
                'read_time' => '9 دقائق قراءة',
                'published_at' => '2025-05-14',
                'published_label' => '14 مايو 2025',
                'author' => 'فريق أبحاث إدراك Lab',
                'author_role' => 'مختبر اقتصاديات النماذج العربية',
                'key_metric_value' => '1.28 T/Word',
                'key_metric_label' => 'أفضل كفاءة مسجلة',
                'spotlight_model_slug' => 'claude-3-5-sonnet',
                'hero_metrics' => [
                    ['value' => '1.28 T/Word', 'label' => 'أفضل معامل تقطيع عربي', 'tone' => 'efficiency'],
                    ['value' => '-34.8%', 'label' => 'متوسط الوفر السنوي المختبر', 'tone' => 'primary'],
                    ['value' => '12.5M Words', 'label' => 'حجم المدونة العربية القياسية', 'tone' => 'cost'],
                ],
                'takeaways' => [
                    [
                        'num' => '01',
                        'title' => 'التقطيع الصرفي',
                        'body' => 'المقطّعات غير المدربة بعمق على الجذور العربية تفكك الكلمة الواحدة مثل «وباستثناءاتهم» إلى 5–7 توكنات منفصلة، ما يضاعف استهلاك نافذة السياق.',
                    ],
                    [
                        'num' => '02',
                        'title' => 'الأثر المالي المباشر',
                        'body' => 'الانتقال من نموذج بمعامل تقطيع 2.40 إلى نموذج بمعامل 1.30 يخفض فاتورة الـ API الشهرية بنسبة تصل إلى 42% عند نفس حجم المحتوى العربي.',
                    ],
                    [
                        'num' => '03',
                        'title' => 'التوجيه الهجين في الإنتاج',
                        'body' => 'توجيه مهام التلخيص ومعالجة المستندات للنماذج عالية الكفاءة مع إبقاء الاستدلال المعقد للنماذج الرائدة يحقق أفضل عائد استثماري.',
                    ],
                ],
                'sections' => [
                    [
                        'id' => 'context',
                        'title' => '1. لماذا ترتفع فاتورة النصوص العربية في الواجهات البرمجية؟',
                        'paragraphs' => [
                            'تُحاسب مزودات الذكاء الاصطناعي المطورين بعدد التوكنات (Tokens) لا بعدد الكلمات. بينما تستهلك الكلمة الإنجليزية نحو 1.33 توكن في المتوسط، تفرض اللغة العربية بنية صرفية غنية بالسوابق واللواحق والضمائر المتصلة، ما يدفع المقطّعات التقليدية إلى تجزئة الكلمة الواحدة إلى بايتات متفرقة.',
                            'في اختباراتنا على وثائق مالية وقانونية حقيقية، أدت التجزئة المفرطة إلى استهلاك 3 أضعاف عدد التوكنات في بعض الأوزان القديمة، وهو ما لا يرفع التكلفة فحسب، بل يقلص السعة الفعلية لنافذة السياق (Context Window) ويرفع زمن الوصول لأول توكن.',
                        ],
                    ],
                    [
                        'id' => 'benchmarks',
                        'title' => '2. نتائج قياس كفاءة التقطيع عبر أبرز النماذج',
                        'paragraphs' => [
                            'اختبر مختبر إدراك Lab خمسة نماذج إنتاجية شائعة على عينة موحدة تضم مليون كلمة عربية متوازنة بين العقود القانونية، الفواتير، والتقارير التقنية، مع حساب التكلفة الفعلية لكل مليون كلمة عربية (بنسبة 75% إدخال و 25% إخراج).',
                        ],
                    ],
                    [
                        'id' => 'cost-impact',
                        'title' => '3. قياس التقطيع عملياً قبل إرسال الطلبات',
                        'paragraphs' => [
                            'الاعتماد على تقديرات اللغة الإنجليزية عند تصميم ميزانيات الأنظمة العربية يؤدي إلى مفاجآت مكلفة. يوصى بدمج فحص التقطيع ضمن خط معالجة البيانات قبل الإرسال، أو استخدام إضافة إدراك Lab لمراقبة المعامل الفعلي لكل Prompt.',
                        ],
                    ],
                    [
                        'id' => 'recommendations',
                        'title' => '4. توصيات هندسية لتخفيض الفاتورة دون فقدان الدقة',
                        'paragraphs' => [
                            'ابدأ بضبط القوالب (System Prompts) وإزالة التشكيل غير الضروري في مهام الاسترجاع العام، وفعّل التخزين المؤقت للسياق (Prompt Caching) للنصوص المرجعية الثابتة، وخصص خدمات Doc AI لاستخراج الجداول بدلاً من إرسال ملفات خام ضخمة في كل طلب.',
                        ],
                    ],
                ],
                'comparison_rows' => [
                    ['model' => 'DeepSeek-R1', 'tpw' => '1.28', 'arabic_score' => '142 / 156', 'cost_usd' => '$1.45', 'cost_sar' => '5.44 ر.س', 'highlight' => true],
                    ['model' => 'Qwen 2.5 72B', 'tpw' => '1.35', 'arabic_score' => '138 / 156', 'cost_usd' => '$0.92', 'cost_sar' => '3.45 ر.س', 'highlight' => false],
                    ['model' => 'Claude 3.5 Sonnet', 'tpw' => '1.68', 'arabic_score' => '148 / 156', 'cost_usd' => '$9.25', 'cost_sar' => '34.69 ر.س', 'highlight' => false],
                    ['model' => 'GPT-4o', 'tpw' => '1.52', 'arabic_score' => '145 / 156', 'cost_usd' => '$7.60', 'cost_sar' => '28.50 ر.س', 'highlight' => false],
                    ['model' => 'Llama 3.3 70B', 'tpw' => '1.44', 'arabic_score' => '131 / 156', 'cost_usd' => '$0.78', 'cost_sar' => '2.93 ر.س', 'highlight' => false],
                ],
                'code_snippet' => <<<'PYTHON'
import tiktoken

sample = "وباستثناءاتهم القانونية في العقود المالية الموحدة"
words = len(sample.split())

enc = tiktoken.get_encoding("o200k_base")
tokens = enc.encode(sample)

print(f"Words: {words} | Tokens: {len(tokens)} | Ratio: {len(tokens)/words:.2f} T/Word")
PYTHON,
            ],
            [
                'slug' => 'ttft-latency-arabic-rag-benchmarks',
                'report_id' => 'EL-TR-2025-08',
                'featured' => false,
                'category_key' => 'benchmarks',
                'category_label' => 'المعايير القياسية',
                'title' => 'زمن استجابة التوكن الأول (TTFT) في تطبيقات المحادثة الفورية بالعربية',
                'excerpt' => 'تحليل الأداء اللحظي وزمن الكمون لـ 14 نموذجاً عند معالجة أوامر النظام العربية الطويلة وتطبيقات خدمة العملاء الصوتية والمحادثة الفورية.',
                'read_time' => '8 دقائق قراءة',
                'published_at' => '2025-05-10',
                'published_label' => '10 مايو 2025',
                'author' => 'فريق أبحاث إدراك Lab',
                'author_role' => 'هندسة الاستجابة السريعة',
                'key_metric_value' => '140ms TTFT',
                'key_metric_label' => 'أسرع استجابة أولى',
                'spotlight_model_slug' => 'gpt-4o',
                'hero_metrics' => [
                    ['value' => '140ms', 'label' => 'أسرع زمن للتوكن الأول', 'tone' => 'speed'],
                    ['value' => '14 Models', 'label' => 'عدد النماذج المختبرة', 'tone' => 'primary'],
                    ['value' => '4K Tokens', 'label' => 'حجم سياق الاختبار الثابت', 'tone' => 'efficiency'],
                ],
                'takeaways' => [
                    [
                        'num' => '01',
                        'title' => 'حساسية البث المباشر',
                        'body' => 'تأخر التوكن الأول لأكثر من 900 مللي ثانية يرفع معدل مغادرة المستخدمين في الدردشة الفورية بنسبة ملموسة.',
                    ],
                    [
                        'num' => '02',
                        'title' => 'تأثير التقطيع على السرعة',
                        'body' => 'كلما ارتفع معامل التقطيع العربي، زاد عدد الخطوات المطلوبة لتوليد نفس الفقرة العربية مما يبطئ زمن الإكمال الكلي.',
                    ],
                    [
                        'num' => '03',
                        'title' => 'اختيار نقطة النهاية',
                        'body' => 'اختبار سرعة المزودين من نقاط قياس إقليمية (الرياض ودبي) يكشف فوارق كبيرة بين الاستضافة المباشرة والوسائط.',
                    ],
                ],
                'sections' => [
                    [
                        'id' => 'context',
                        'title' => '1. أهمية زمن التوكن الأول مقابل سرعة التوليد الإجمالية',
                        'paragraphs' => [
                            'في الأنظمة التفاعلية، يشعر المستخدم بذكاء النظام عبر سرعة بدء الظهور (TTFT) قبل أن يلاحظ عدد التوكنات في الثانية (TPS). وجود مقدمة عربية طويلة في موجه النظام (System Prompt) دون تخزين مؤقت يضاعف هذا الزمن.',
                        ],
                    ],
                    [
                        'id' => 'benchmarks',
                        'title' => '2. مقارنة أزمنة الاستجابة والسرعة العربية',
                        'paragraphs' => [
                            'رصدنا زمن الاستجابة الفعلي عبر 500 طلب عربي متتالي بطول 2,000 كلمة إدخال و 300 كلمة إخراج لقياس التفاوت تحت الضغط.',
                        ],
                    ],
                    [
                        'id' => 'cost-impact',
                        'title' => '3. قياس الكمون في بيئة الإنتاج',
                        'paragraphs' => [
                            'استخدم البث المتدفق (Streaming SSE) دائماً مع قياس الزمن بين إرسال الطلب ووصول أول جزء نصي مفيد.',
                        ],
                    ],
                    [
                        'id' => 'recommendations',
                        'title' => '4. توصيات تقليص زمن الانتظار',
                        'paragraphs' => [
                            'قلّص سياق RAG إلى الفقرات الأعلى صلة فقط، واعتمد التخزين المؤقت لأوامر النظام، ووجّه الاستفسارات البسيطة لنماذج فائقة السرعة.',
                        ],
                    ],
                ],
                'comparison_rows' => [
                    ['model' => 'Gemini 2.0 Flash', 'tpw' => '1.48', 'arabic_score' => '139 / 156', 'cost_usd' => '$0.42', 'cost_sar' => '1.58 ر.س', 'highlight' => true],
                    ['model' => 'GPT-4o Mini', 'tpw' => '1.55', 'arabic_score' => '132 / 156', 'cost_usd' => '$0.60', 'cost_sar' => '2.25 ر.س', 'highlight' => false],
                    ['model' => 'Claude 3.5 Haiku', 'tpw' => '1.70', 'arabic_score' => '136 / 156', 'cost_usd' => '$2.95', 'cost_sar' => '11.06 ر.س', 'highlight' => false],
                ],
                'code_snippet' => <<<'PYTHON'
import time, requests

start = time.perf_counter()
with requests.post("https://openrouter.ai/api/v1/chat/completions", stream=True, json={...}) as r:
    for line in r.iter_lines():
        if line:
            print(f"TTFT: {(time.perf_counter() - start) * 1000:.0f} ms")
            break
PYTHON,
            ],
            [
                'slug' => 'arabic-dialects-mmlu-evaluation',
                'report_id' => 'EL-TR-2025-07',
                'featured' => false,
                'category_key' => 'benchmarks',
                'category_label' => 'المعايير القياسية',
                'title' => 'تقييم الفهم السياقي للهجات العربية (الخليجية، الشامية، المصرية) في معيار MMLU-AR',
                'excerpt' => 'إطار اختبار يقيس تدهور الدقة المنطقية عندما يتحول مدخل المستخدم من العربية الفصحى إلى اللهجات المحلية غير المهيكلة في قطاعات التجزئة والبنوك.',
                'read_time' => '11 دقيقة قراءة',
                'published_at' => '2025-05-04',
                'published_label' => '04 مايو 2025',
                'author' => 'فريق أبحاث إدراك Lab',
                'author_role' => 'تقييم اللغويات واللهجات',
                'key_metric_value' => '88.4% دقة',
                'key_metric_label' => 'متوسط فهم اللهجات',
                'spotlight_model_slug' => 'claude-3-5-sonnet',
                'hero_metrics' => [
                    ['value' => '88.4%', 'label' => 'أعلى دقة في اللهجة الخليجية', 'tone' => 'efficiency'],
                    ['value' => '3 Dialects', 'label' => 'نطاقات لهجية رئيسية', 'tone' => 'primary'],
                    ['value' => '156 Pts', 'label' => 'مقياس إدراك الكلي', 'tone' => 'cost'],
                ],
                'takeaways' => [
                    [
                        'num' => '01',
                        'title' => 'فجوة الفصحى والعامية',
                        'body' => 'تتراجع دقة النماذج متوسطة الحجم بين 9% و 16% عند صياغة نفس السؤال البنكي باللهجة المحلية بدلاً من الفصحى.',
                    ],
                    [
                        'num' => '02',
                        'title' => 'المزج اللغوي (Arabizi / Code-Switching)',
                        'body' => 'إدراج مصطلحات إنجليزية تقنية وسط جملة عربية عامية يختبر مرونة النموذج في الحفاظ على السياق التجاري.',
                    ],
                    [
                        'num' => '03',
                        'title' => 'أهمية التقييم المحلي',
                        'body' => 'معايير MMLU العالمية المترجمة آلياً لا تعكس تحديات اللهجات الواقعية في دعم العملاء.',
                    ],
                ],
                'sections' => [
                    [
                        'id' => 'context',
                        'title' => '1. تحدي المحادثات الواقعية في المنطقة العربية',
                        'paragraphs' => [
                            'لا يتحدث عملاء التجارة الإلكترونية والمصارف بالفصحى المعجمية في تذاكر الدعم. تعتمد جودة المساعد الذكي على فهم التعابير الدارجة والمختصرات المحلية دون طلب إعادة صياغة من المستخدم.',
                        ],
                    ],
                    [
                        'id' => 'benchmarks',
                        'title' => '2. نتائج الاختبار عبر اللهجات الرئيسية',
                        'paragraphs' => [
                            'تفوقت النماذج الرائدة في فهم النوايا والشكاوى المصاغة باللهجات الخليجية والمصرية والشامية عند ضبط موجه النظام بأمثلة توضيحية قليلة (Few-Shot).',
                        ],
                    ],
                    [
                        'id' => 'cost-impact',
                        'title' => '3. كلفة الأخطاء وتصعيد التذاكر',
                        'paragraphs' => [
                            'فهم النية من الرسالة الأولى يخفض عدد دورات المحادثة اللازمة لحل المشكلة بمقدار الثلث تقريباً.',
                        ],
                    ],
                    [
                        'id' => 'recommendations',
                        'title' => '4. كيف ترفع دقة اللهجات في تطبيقك؟',
                        'paragraphs' => [
                            'أضف قاموساً مصغراً للمصطلحات المحلية في الـ System Prompt، واختبر النموذج على عينة حقيقية من تذاكر عملائك عبر لوحة إدراك Lab قبل الاعتماد النهائي.',
                        ],
                    ],
                ],
                'comparison_rows' => [
                    ['model' => 'Claude 3.5 Sonnet', 'tpw' => '1.68', 'arabic_score' => '148 / 156', 'cost_usd' => '$9.25', 'cost_sar' => '34.69 ر.س', 'highlight' => true],
                    ['model' => 'GPT-4o', 'tpw' => '1.52', 'arabic_score' => '145 / 156', 'cost_usd' => '$7.60', 'cost_sar' => '28.50 ر.س', 'highlight' => false],
                    ['model' => 'DeepSeek-R1', 'tpw' => '1.28', 'arabic_score' => '142 / 156', 'cost_usd' => '$1.45', 'cost_sar' => '5.44 ر.س', 'highlight' => false],
                ],
                'code_snippet' => <<<'PYTHON'
system_prompt = """
أنت مساعد خدمة عملاء يفهم اللهجات الخليجية والمصرية والشامية بدقة،
ويرد بعربية مهنية واضحة ومباشرة دون تكلف.
"""
PYTHON,
            ],
            [
                'slug' => 'long-context-arabic-pdf-rag-doc-ai',
                'report_id' => 'EL-TR-2025-06',
                'featured' => false,
                'category_key' => 'doc-ai',
                'category_label' => 'المستندات و Doc AI',
                'title' => 'هندسة RAG للمستندات العربية الطويلة: نافذة 200K مقابل الاسترجاع الهجين',
                'excerpt' => 'متى تكتفي بحقن ملفات PDF العربية الكاملة في نافذة السياق الطويلة، ومتى يصبح التقطيع الدلالي واستخراج الجداول عبر Doc AI ضرورة حتمية لخفض الهلوسة والتكلفة؟',
                'read_time' => '14 دقيقة قراءة',
                'published_at' => '2025-04-28',
                'published_label' => '28 أبريل 2025',
                'author' => 'فريق أبحاث إدراك Lab',
                'author_role' => 'مختبر معالجة المستندات Doc AI',
                'key_metric_value' => '200K+ Context',
                'key_metric_label' => 'اختبار العقود والملفات',
                'spotlight_model_slug' => 'claude-3-5-sonnet',
                'hero_metrics' => [
                    ['value' => '96.4%', 'label' => 'دقة استرجاع البنود عبر Doc AI', 'tone' => 'efficiency'],
                    ['value' => '-64%', 'label' => 'خفض التكلفة مقارنة بالحقن الكامل', 'tone' => 'primary'],
                    ['value' => '200K+', 'label' => 'نوافذ السياق المختبرة', 'tone' => 'cost'],
                ],
                'takeaways' => [
                    [
                        'num' => '01',
                        'title' => 'مشكلة نسيان المنتصف',
                        'body' => 'حقن 150 صفحة عربية دفعة واحدة يرفع احتمالية إغفال الجداول والبنود الفرعية في منتصف المستند.',
                    ],
                    [
                        'num' => '02',
                        'title' => 'تآكل الميزانية',
                        'body' => 'إرسال المستند الكامل مع كل سؤال متابعة للمستخدم يضاعف فاتورة التوكن دون داعٍ.',
                    ],
                    [
                        'num' => '03',
                        'title' => 'الحل عبر Doc AI',
                        'body' => 'تحويل ملف PDF إلى Markdown وجداول مهيكلة أولاً ثم استرجاع المقاطع ذات الصلة يجمع بين الدقة القصوى والتكلفة المنخفضة.',
                    ],
                ],
                'sections' => [
                    [
                        'id' => 'context',
                        'title' => '1. تحديات ملفات PDF العربية: RTL والجداول المدمجة',
                        'paragraphs' => [
                            'تعاني أدوات القراءة التقليدية لملفات PDF من عكس ترتيب الكلمات العربية أو تفكيك الأعمدة المالية. تبدأ جودة أي نظام RAG أو مساعد قانوني من دقة طبقة الاستخراج المسبقة (Doc AI).',
                        ],
                    ],
                    [
                        'id' => 'benchmarks',
                        'title' => '2. مقارنة الاسترجاع الهجين مقابل الحقن الكامل',
                        'paragraphs' => [
                            'في مستندات تتجاوز 40 صفحة، تفوقت معمارية الاسترجاع الهجين المقترنة بمحرك Doc AI بنسبة 11 نقطة مئوية في دقة الإجابة الموثقة برقم الصفحة.',
                        ],
                    ],
                    [
                        'id' => 'cost-impact',
                        'title' => '3. الأثر الاقتصادي في الأرشفة المؤسسية',
                        'paragraphs' => [
                            'معالجة المستند مرة واحدة وتخزين تمثيله الدلالي يخفض كلفة كل استعلام لاحق بأكثر من 64%.',
                        ],
                    ],
                    [
                        'id' => 'recommendations',
                        'title' => '4. متى تستخدم كل منهجية؟',
                        'paragraphs' => [
                            'استخدم نافذة السياق الكاملة لمقارنة عقدين قصيرين في جلسة واحدة، واعتمد Doc AI + RAG عندما تبني قاعدة معرفة متنامية لآلاف الوثائق.',
                        ],
                    ],
                ],
                'comparison_rows' => [
                    ['model' => 'Doc AI + Hybrid RAG', 'tpw' => '1.35', 'arabic_score' => '96.4% دقة', 'cost_usd' => '$1.15', 'cost_sar' => '4.31 ر.س', 'highlight' => true],
                    ['model' => 'Full 200K Injection', 'tpw' => '1.68', 'arabic_score' => '85.2% دقة', 'cost_usd' => '$14.80', 'cost_sar' => '55.50 ر.س', 'highlight' => false],
                ],
                'code_snippet' => <<<'PYTHON'
# تهيئة مقاطع مستند عربي مع الحفاظ على العناوين والجداول
chunks = split_arabic_markdown(doc_markdown, max_tokens=650, overlap=80)
PYTHON,
            ],
            [
                'slug' => 'prompt-caching-arabic-cost-optimization',
                'report_id' => 'EL-TR-2025-05',
                'featured' => false,
                'category_key' => 'economics',
                'category_label' => 'اقتصاديات النماذج',
                'title' => 'تخفيض فاتورة الـ API بنسبة 78% باستخدام التخزين المؤقت للسياق (Prompt Caching)',
                'excerpt' => 'دليل تطبيقي لهيكلة أوامر النظام والوثائق المرجعية الثابتة للاستفادة القصوى من تسعيرة الكاش في النماذج السحابية الرائدة.',
                'read_time' => '7 دقائق قراءة',
                'published_at' => '2025-04-19',
                'published_label' => '19 أبريل 2025',
                'author' => 'فريق أبحاث إدراك Lab',
                'author_role' => 'هندسة تحسين التكاليف',
                'key_metric_value' => '-78% وفر',
                'key_metric_label' => 'على المدخلات المتكررة',
                'spotlight_model_slug' => 'claude-3-5-sonnet',
                'hero_metrics' => [
                    ['value' => '-78%', 'label' => 'خفض صافي الفاتورة الشهرية', 'tone' => 'efficiency'],
                    ['value' => 'Up to 90%', 'label' => 'خصم قراءة التوكن المخزن', 'tone' => 'primary'],
                    ['value' => '1,024+', 'label' => 'الحد الأدنى لتفعيل الكاش', 'tone' => 'cost'],
                ],
                'takeaways' => [
                    [
                        'num' => '01',
                        'title' => 'تثبيت البادئة (Prefix Stability)',
                        'body' => 'ضع التعليمات الثابتة وقاعدة السياسات العربية في بداية الطلب قبل أي متغيرات متغيرة مثل التاريخ أو اسم المستخدم.',
                    ],
                    [
                        'num' => '02',
                        'title' => 'الأثر التراكمي في العربية',
                        'body' => 'بما أن النصوص العربية تولد توكنات أكثر، يتضاعف وفر الـ Prompt Caching مقارنة بالطلبات الإنجليزية المماثلة.',
                    ],
                    [
                        'num' => '03',
                        'title' => 'حساب العائد الفوري',
                        'body' => 'استخدم صفحة المواصفات لكل نموذج في إدراك Lab لمقارنة سعر قراءة الكاش مقابل السعر القياسي.',
                    ],
                ],
                'sections' => [
                    [
                        'id' => 'context',
                        'title' => '1. كيف يعمل التخزين المؤقت للموجهات؟',
                        'paragraphs' => [
                            'بدلاً من إعادة معالجة 5,000 توكن من السياسات والقواعد العربية في كل رسالة عميل، يحتفظ المزود بحالة الـ KV Cache لفترة زمنية قصيرة ويحاسبك بخصم يصل إلى 90% عند القراءة المتكررة.',
                        ],
                    ],
                    [
                        'id' => 'benchmarks',
                        'title' => '2. الأثر على التكلفة وسرعة الاستجابة',
                        'paragraphs' => [
                            'لم ينخفض السعر فحسب، بل تحسن زمن استجابة التوكن الأول بنسبة 35% في الأوامر الطويلة.',
                        ],
                    ],
                    [
                        'id' => 'cost-impact',
                        'title' => '3. أخطاء شائعة تكسر الكاش',
                        'paragraphs' => [
                            'إدراج الطابع الزمني المتغير أو معرف الجلسة في أول الـ System Prompt يبطل تطابق البادئة ويفقدك الخصم.',
                        ],
                    ],
                    [
                        'id' => 'recommendations',
                        'title' => '4. قائمة تحقق قبل التفعيل',
                        'paragraphs' => [
                            'اجعل السياق الثابت في الأعلى، وحافظ على طول يتجاوز عتبة التفعيل (1024 توكن)، وراقب نسبة Cache Hit من سجلات التشغيل.',
                        ],
                    ],
                ],
                'comparison_rows' => [
                    ['model' => 'Standard Input (No Cache)', 'tpw' => '1.68', 'arabic_score' => '148 / 156', 'cost_usd' => '$9.25', 'cost_sar' => '34.69 ر.س', 'highlight' => false],
                    ['model' => 'With Prompt Caching', 'tpw' => '1.68', 'arabic_score' => '148 / 156', 'cost_usd' => '$2.04', 'cost_sar' => '7.65 ر.س', 'highlight' => true],
                ],
                'code_snippet' => <<<'JSON'
{
  "role": "system",
  "content": [
    {
      "type": "text",
      "text": "دليل السياسات الكامل باللغة العربية...",
      "cache_control": { "type": "ephemeral" }
    }
  ]
}
JSON,
            ],
            [
                'slug' => 'structured-json-extraction-arabic-invoices',
                'report_id' => 'EL-TR-2025-04',
                'featured' => false,
                'category_key' => 'doc-ai',
                'category_label' => 'المستندات و Doc AI',
                'title' => 'دقة استخراج البيانات المهيكلة (JSON Schema) من الفواتير والعقود العربية',
                'excerpt' => 'اختبار صارم لموثوقية Function Calling والمخرجات المهيكلة في تحويل المستندات العربية إلى بيانات جاهزة للربط مع أنظمة ERP والمحاسبة.',
                'read_time' => '10 دقائق قراءة',
                'published_at' => '2025-04-11',
                'published_label' => '11 أبريل 2025',
                'author' => 'فريق أبحاث إدراك Lab',
                'author_role' => 'أتمتة المستندات المؤسسية',
                'key_metric_value' => '99.1% JSON',
                'key_metric_label' => 'نسبة التزام المخطط',
                'spotlight_model_slug' => 'gpt-4o',
                'hero_metrics' => [
                    ['value' => '99.1%', 'label' => 'التزام صارم بمخطط JSON', 'tone' => 'efficiency'],
                    ['value' => '0 Errors', 'label' => 'في تنسيق الأرقام والتواريخ', 'tone' => 'primary'],
                    ['value' => '1,200 Docs', 'label' => 'فواتير وعقود عربية مختبرة', 'tone' => 'cost'],
                ],
                'takeaways' => [
                    [
                        'num' => '01',
                        'title' => 'الأرقام الهندية والعربية',
                        'body' => 'توحيد تطبيع الأرقام (٠١٢٣ إلى 0123) وتنسيق تواريخ الهجري والميلادي يمنع أخطاء التحليل في الأنظمة المحاسبية.',
                    ],
                    [
                        'num' => '02',
                        'title' => 'مفاتيح إنجليزية وقيم عربية',
                        'body' => 'أفضل ممارسة هي تثبيت أسماء الحقول (Keys) بالإنجليزية داخل Schema مع حفظ القيم النصية بالعربية.',
                    ],
                    [
                        'num' => '03',
                        'title' => 'التحقق التلقائي عبر Doc AI',
                        'body' => 'تمرير الفواتير عبر خط معالجة Doc AI يضمن مطابقة المجاميع الضريبية (15% VAT) قبل اعتماد السجل.',
                    ],
                ],
                'sections' => [
                    [
                        'id' => 'context',
                        'title' => '1. لماذا تفشل الاستخراجات النصية الحرة في بيئات ERP؟',
                        'paragraphs' => [
                            'في أتمتة الفواتير والعقود، تكفي فاصلة زائدة أو تاريخ غير منضبط لتعطيل مسار الربط البرمجي بالكامل. تفرض المخرجات المهيكلة الصارمة (Structured Outputs) التزام النموذج بمخطط JSON محدد مسبقاً.',
                        ],
                    ],
                    [
                        'id' => 'benchmarks',
                        'title' => '2. نتائج دقة استخراج الحقول الضريبية والبنود',
                        'paragraphs' => [
                            'حقق الجمع بين الفحص البصري عالي الدقة والمخططات الصارمة نسبة التزام بلغت 99.1% عبر 1,200 فاتورة وعقد باللغة العربية.',
                        ],
                    ],
                    [
                        'id' => 'cost-impact',
                        'title' => '3. خفض المراجعة اليدوية للمحاسبين',
                        'paragraphs' => [
                            'بدلاً من إدخال الفواتير يدوياً، يقتصر دور المراجع البشري على السجلات ذات التباين في المجاميع فقط.',
                        ],
                    ],
                    [
                        'id' => 'recommendations',
                        'title' => '4. قالب JSON موصى به للفواتير العربية',
                        'paragraphs' => [
                            'افصل دائماً بين حقل المبلغ الصافي والضريبة والإجمالي كأرقام عشرية صريحة مع حقل عملة موحد (SAR / USD).',
                        ],
                    ],
                ],
                'comparison_rows' => [
                    ['model' => 'GPT-4o (Strict Schema)', 'tpw' => '1.52', 'arabic_score' => '99.1% التزام', 'cost_usd' => '$7.60', 'cost_sar' => '28.50 ر.س', 'highlight' => true],
                    ['model' => 'Claude 3.5 Sonnet (Tools)', 'tpw' => '1.68', 'arabic_score' => '98.7% التزام', 'cost_usd' => '$9.25', 'cost_sar' => '34.69 ر.س', 'highlight' => false],
                ],
                'code_snippet' => <<<'JSON'
{
  "invoice_number": "INV-2025-8841",
  "vendor_name_ar": "شركة الأفق للتوريدات التقنية",
  "subtotal_sar": 12500.00,
  "vat_15_sar": 1875.00,
  "total_sar": 14375.00
}
JSON,
            ],
        ];
    }

    /**
     * Top three Arabic-ranked models for sidebar discovery links.
     *
     * @return list<array{name: string, slug: string, provider: string, score: int|null, tpw: float|null, in_price: float|null}>
     */
    private function topModels(): array
    {
        try {
            ['arabic' => $arabic, 'pool' => $pool] = $this->rankingService->leaderboards();
            $source = $arabic->isNotEmpty() ? $arabic : $pool;

            return $source
                ->take(3)
                ->map(fn (AiModel $model): array => [
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'provider' => $model->provider?->name ?? 'OpenRouter',
                    'score' => $model->benchmarkArabic?->total_score,
                    'tpw' => $model->benchmarkArabic?->tokens_per_word !== null ? (float) $model->benchmarkArabic->tokens_per_word : null,
                    'in_price' => $model->latestSpec?->input_cost_per_1m !== null ? (float) $model->latestSpec->input_cost_per_1m : null,
                ])
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Resolve the featured model spotlight for a blog article.
     *
     * @return array{name: string, slug: string, provider: string, score: int|null, tpw: float|null, in_price: float|null}|null
     */
    private function resolveSpotlightModel(?string $preferredSlug): ?array
    {
        try {
            $model = null;

            if ($preferredSlug !== null) {
                $model = AiModel::query()
                    ->with(['provider', 'latestSpec', 'benchmarkArabic'])
                    ->where('is_active', true)
                    ->where('slug', $preferredSlug)
                    ->first();
            }

            if ($model === null) {
                ['arabic' => $arabic, 'pool' => $pool] = $this->rankingService->leaderboards();
                $model = $arabic->first() ?? $pool->first();
            }

            if ($model === null) {
                return null;
            }

            return [
                'name' => $model->name,
                'slug' => $model->slug,
                'provider' => $model->provider?->name ?? 'OpenRouter',
                'score' => $model->benchmarkArabic?->total_score,
                'tpw' => $model->benchmarkArabic?->tokens_per_word !== null ? (float) $model->benchmarkArabic->tokens_per_word : null,
                'in_price' => $model->latestSpec?->input_cost_per_1m !== null ? (float) $model->latestSpec->input_cost_per_1m : null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Hydrate quoted models and parse inline [model:slug|quote="..."] shortcodes inside sections.
     *
     * @param  array<string, mixed>  $post
     * @return array<string, mixed>
     */
    private function hydratePostMediaAndQuotes(array $post): array
    {
        try {
            $modelsBySlug = AiModel::query()
                ->with(['provider', 'latestSpec', 'benchmarkArabic'])
                ->where('is_active', true)
                ->get()
                ->keyBy('slug');
        } catch (\Throwable) {
            $modelsBySlug = collect();
        }

        $hydratedQuotes = [];
        foreach ((array) ($post['quoted_models'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $hydratedQuotes[] = $this->buildModelQuoteCard(
                $modelsBySlug,
                (string) ($item['slug'] ?? ''),
                $item['name'] ?? null,
                $item['provider'] ?? null,
                $item['badge'] ?? 'نموذج مرجعي',
                $item['quote'] ?? '',
                $item['placement'] ?? 'after_section_1'
            );
        }
        $post['quoted_models'] = $hydratedQuotes;

        $parsedSections = [];
        foreach ((array) ($post['sections'] ?? []) as $section) {
            if (! is_array($section)) {
                continue;
            }

            $items = [];
            foreach ((array) ($section['paragraphs'] ?? []) as $paragraph) {
                $text = trim((string) $paragraph);
                if ($text === '') {
                    continue;
                }

                if (preg_match('/^\[model:([a-zA-Z0-9_\/-]+)(?:\|quote="([^"]*)")?(?:\|badge="([^"]*)")?\]$/u', $text, $matches)) {
                    $items[] = [
                        'type' => 'model_quote',
                        'card' => $this->buildModelQuoteCard(
                            $modelsBySlug,
                            $matches[1],
                            null,
                            null,
                            $matches[3] ?? 'اقتباس نموذج حي',
                            $matches[2] ?? '',
                            'inline'
                        ),
                    ];
                } else {
                    $items[] = [
                        'type' => 'paragraph',
                        'text' => $text,
                    ];
                }
            }

            $section['rendered_items'] = $items;
            $parsedSections[] = $section;
        }
        $post['sections'] = $parsedSections;

        return $post;
    }

    /**
     * Build a model quote card enriched with live database telemetry when available.
     *
     * @param  Collection<string, AiModel>  $modelsBySlug
     * @return array<string, mixed>
     */
    private function buildModelQuoteCard(
        Collection $modelsBySlug,
        string $slug,
        ?string $fallbackName,
        ?string $fallbackProvider,
        string $badge,
        string $quote,
        string $placement
    ): array {
        /** @var AiModel|null $live */
        $live = $modelsBySlug->get($slug);

        return [
            'slug' => $live?->slug ?? $slug,
            'name' => $live?->name ?? ($fallbackName ?: $slug),
            'provider' => $live?->provider?->name ?? ($fallbackProvider ?: 'OpenRouter'),
            'badge' => $badge !== '' ? $badge : 'نموذج مرجعي',
            'quote' => $quote,
            'placement' => $placement,
            'has_profile' => $live !== null,
            'score' => $live?->benchmarkArabic?->total_score,
            'tpw' => $live?->benchmarkArabic?->tokens_per_word !== null ? (float) $live->benchmarkArabic->tokens_per_word : null,
            'tps' => $live?->benchmarkArabic?->tokens_per_second !== null ? (float) $live->benchmarkArabic->tokens_per_second : null,
            'in_price' => $live?->latestSpec?->input_cost_per_1m !== null ? (float) $live->latestSpec->input_cost_per_1m : null,
        ];
    }

    /**
     * Live platform stats for the public top telemetry bar.
     *
     * @return array{tested_models: int|null, avg_latency: float|null, catalog_models: int|null}
     */
    private function liveStats(): array
    {
        try {
            ['arabic' => $arabic] = $this->rankingService->leaderboards();
            $avgLatency = ArabicBenchmarkRun::where('status', 'completed')
                ->where('latency_seconds', '>', 0)
                ->avg('latency_seconds');

            return [
                'tested_models' => $arabic->count(),
                'avg_latency' => $avgLatency !== null ? round((float) $avgLatency, 3) : null,
                'catalog_models' => AiModel::where('is_active', true)->count(),
            ];
        } catch (\Throwable) {
            return [
                'tested_models' => null,
                'avg_latency' => null,
                'catalog_models' => null,
            ];
        }
    }
}
