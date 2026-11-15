<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\ArabicBenchmarkRun;
use App\Models\BenchmarkArabic;
use App\Models\ModelSpec;
use App\Models\Provider;
use App\Models\User;
use App\Services\ArabicBenchmarkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArabicBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_guest_is_redirected_from_arabic_benchmark_lab(): void
    {
        $response = $this->get('/admin/benchmarks/arabic');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_arabic_benchmark_lab(): void
    {
        $provider = Provider::factory()->create(['name' => 'Qwen AI']);
        $model = AiModel::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Qwen 2.5 72B',
            'openrouter_id' => 'qwen/qwen-2.5-72b-instruct',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/benchmarks/arabic');

        $response->assertStatus(200);
        $response->assertSee('Arabic AI Benchmark Lab');
        $response->assertSee('Qwen 2.5 72B');
        $response->assertSee('Master Prompt');
    }

    public function test_deterministic_evaluator_scores_perfect_response(): void
    {
        $service = new ArabicBenchmarkService;

        $perfectJson = json_encode([
            'task1' => -345,
            'task2' => 807,
            'task3' => [
                'quantity' => 38,
                'value' => '1520 ريال',
                'date' => '10 مايو 2025',
            ],
            'task4' => [
                'a' => 'غير معلوم',
                'b' => 'مؤكد',
                'c' => 'منفي',
            ],
            'task5' => [
                'مساجد' => 'مَسَاجِدَ',
                'المهندسين' => 'الْمُهَنْدِسِينَ',
            ],
            'task6' => [
                'a' => 'فعل ماضٍ',
                'b' => 'اسم جمع',
            ],
            'task7' => 'إذا لم تجد القطعة الأصلية، فلا تركّب شيئاً بدلاً منها، وأخبرني قبل أن تعود.',
            'task8' => [
                'inference_location' => 'على الحافة الطرفية للكاميرا edge',
                'recording_location' => 'على خادم السيرفر server',
            ],
            'task9' => [
                'can_determine' => false,
                'possible_owners' => ['ليلى', 'مريم'],
            ],
            'task10' => [
                'invoice_number' => 'ف-208',
                'supplier' => 'شركة المشرق للأدوات',
            ],
            'task11' => "تأجلت التجربة من الثلاثاء إلى الخميس بسبب انقطاع الكهرباء.\nوبقي عدد العينات 12 دون تغيير آلية القياس.",
            'task12' => 'وصل وليد ووجد ورقة ووضعها وراء وعاء واسع',
            'task13_saudi' => [
                'requested_time' => 'الحين (الآن)',
                'if_unfinished' => 'علّمني قبل نهاية الدوام',
                'prohibited' => 'إرساله ناقصاً',
            ],
            'task14_moroccan' => [
                'found_now' => false,
                'if_found_later' => 'يعيط له ويتصل به',
                'prohibited_until_arrival' => 'فتح الباب',
            ],
            'task15_tunisian' => [
                'can_send_now' => false,
                'sending_condition' => 'اكتمال التحميل غدوة',
                'completion_tomorrow_certain' => false,
            ],
            'task16_algerian' => [
                'has_invoice_now' => false,
                'sending_time' => 'كي يلقاها عندما يجدها',
                'payment_instruction' => 'عدم دفع أي شيء حتى تصله',
            ],
            'task17_iraqi' => [
                'has_number_now' => false,
                'sending_condition' => 'إذا وصله الرقم',
                'old_number_instruction' => 'عدم اعتماد الرقم القديم',
            ],
            'task18_kuwaiti' => [
                'size_available_now' => false,
                'message_condition_and_time' => 'إذا توفر باجر (غداً)',
                'order_alternative_now' => false,
            ],
            'task19_egyptian' => [
                'order_arrived' => false,
                'conditional_action' => 'يرسل صورته بكرة لو وصل',
                'payment_condition' => 'التأكد قبل الدفع',
            ],
            'task20_sudanese' => [
                'order_arrived_now' => false,
                'notification_time' => 'أول ما يوصل',
                'prohibited_before_call' => 'الذهاب لاستلامه قبل الاتصال',
            ],
            'task21_jordanian_order' => [
                'quantity' => 2,
                'delivery' => 'بكرا مش اليوم',
                'black_substitute_approved' => false,
                'if_blue_unavailable' => 'انتظار اتصال الزبون',
            ],
            'task22_jordanian_inference' => [
                'certain_will_attend' => false,
                'certain_will_not_attend' => false,
                'meaning' => 'المتحدث يوضح أنه لم يخبره بأنه سيحضر، ولا يجزم بحضوره أو عدمه',
            ],
            'task23_jordanian_reply' => 'معك حق، طلبت قطعتين ووصلتك وحدة. الثانية رح نبعثها بكرا ولا يهمك.',
            'task24_jordanian_reference' => [
                'file_sent' => false,
                'promised_by' => 'أخو ليث',
                'promised_time' => 'بكرا',
            ],
        ], JSON_UNESCAPED_UNICODE);

        $eval = $service->evaluateModelResponse($perfectJson);

        $this->assertTrue($eval['is_valid_json']);
        $this->assertSame(100.0, (float) $eval['core_score']);
        $this->assertSame(56.0, (float) $eval['dialect_score']);
        $this->assertSame(156.0, (float) $eval['total_score']);
        $this->assertSame(100.0, (float) $eval['score_percentage']);
        $this->assertEmpty($eval['failure_types']);
    }

    public function test_deterministic_evaluator_identifies_individual_task_failures(): void
    {
        $service = new ArabicBenchmarkService;

        $failingJson = json_encode([
            'task1' => 200,
            'task2' => 500,
            'task3' => ['quantity' => 10, 'value' => '0', 'date' => 'قديم'],
            'task4' => ['a' => 'مؤكد', 'b' => 'منفي', 'c' => 'مؤكد'],
            'task5' => ['مساجد' => 'مساجدِ', 'المهندسين' => 'المهندسون'],
            'task6' => ['a' => 'اسم', 'b' => 'فعل'],
            'task7' => 'ركب بدالها شي',
            'task8' => ['inference_location' => 'مكان غير معروف', 'recording_location' => 'مكان غير معروف'],
            'task9' => ['can_determine' => true, 'possible_owners' => ['ليلى']],
            'task10' => ['invoice_number' => 'غلط', 'supplier' => 'شركة الأفق تم'],
            'task11' => 'نص طويل جداً جداً جداً جداً جداً جداً جداً جداً جداً جداً جداً جداً يتجاوز اثنتي عشرة كلمة بجملة واحدة فقط.',
            'task12' => 'جملة لا تبدأ كل كلماتها بالواو',
        ], JSON_UNESCAPED_UNICODE);

        $eval = $service->evaluateModelResponse($failingJson);

        $this->assertTrue($eval['is_valid_json']);
        $this->assertSame(0.0, (float) $eval['core_score']);
        $this->assertNotEmpty($eval['failure_types']);
    }

    public function test_deterministic_evaluator_handles_markdown_wrapped_json(): void
    {
        $service = new ArabicBenchmarkService;

        $markdownContent = "```json\n".json_encode([
            'task1' => -345,
            'task2' => 807,
            'task3' => [
                'quantity' => 38,
                'value' => '1520 ريال',
                'date' => '10 مايو 2025',
            ],
            'task4' => [
                'a' => 'غير معلوم',
                'b' => 'مؤكد',
                'c' => 'منفي',
            ],
            'task5' => [
                'مساجد' => 'مَسَاجِدَ',
                'المهندسين' => 'الْمُهَنْدِسِينَ',
            ],
        ], JSON_UNESCAPED_UNICODE)."\n```";

        $eval = $service->evaluateModelResponse($markdownContent);

        // Parsed candidate JSON extracted from markdown blocks
        $this->assertGreaterThan(0, $eval['total_score']);
    }

    public function test_run_benchmark_saves_results_to_database_and_syncs_model(): void
    {
        $provider = Provider::factory()->create(['name' => 'Anthropic']);
        $model = AiModel::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Claude 3.7 Sonnet',
            'openrouter_id' => 'anthropic/claude-3.7-sonnet',
        ]);

        ModelSpec::factory()->create([
            'model_id' => $model->id,
            'input_cost_per_1m' => 3.00,
            'output_cost_per_1m' => 15.00,
        ]);

        $mockResponseBody = [
            'id' => 'gen-12345',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => json_encode([
                            'task1' => -345,
                            'task2' => 807,
                            'task3' => [
                                'quantity' => 38,
                                'value' => '1520 ريال',
                                'date' => '10 مايو 2025',
                            ],
                            'task4' => [
                                'a' => 'غير معلوم',
                                'b' => 'مؤكد',
                                'c' => 'منفي',
                            ],
                            'task5' => [
                                'مساجد' => 'مَسَاجِدَ',
                                'المهندسين' => 'الْمُهَنْدِسِينَ',
                            ],
                        ], JSON_UNESCAPED_UNICODE),
                        'reasoning' => 'Thinking process: Math calculation gives -345. Price gives 807. Diacritics: مساجد بالفتحة.',
                    ],
                ],
            ],
            'usage' => [
                'prompt_tokens' => 450,
                'completion_tokens' => 200,
                'total_tokens' => 650,
            ],
        ];

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response($mockResponseBody, 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson('/admin/benchmarks/arabic/run', [
            'model_id' => $model->id,
            'openrouter_model_id' => 'anthropic/claude-3.7-sonnet',
            'thinking_mode' => 'effort',
            'thinking_level' => 'high',
            'api_key' => 'sk-or-test-mock-key',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'openrouter_model_id' => 'anthropic/claude-3.7-sonnet',
            'thinking' => [
                'mode' => 'effort',
                'level' => 'high',
            ],
        ]);

        // Verify ArabicBenchmarkRun record in DB
        $this->assertDatabaseHas('arabic_benchmark_runs', [
            'model_id' => $model->id,
            'openrouter_model_id' => 'anthropic/claude-3.7-sonnet',
            'thinking_mode' => 'effort',
            'thinking_level' => 'high',
            'is_valid_json' => true,
            'prompt_tokens' => 450,
            'completion_tokens' => 200,
        ]);

        // Verify Model's BenchmarkArabic record was synced
        $this->assertDatabaseHas('benchmarks_arabic', [
            'model_id' => $model->id,
            'is_valid_json' => true,
            'thinking_mode' => 'effort:high',
        ]);

        $savedRun = ArabicBenchmarkRun::first();
        $this->assertNotNull($savedRun);
        $this->assertGreaterThan(0, (float) $savedRun->tokens_per_word);
        $this->assertGreaterThan(0, (float) $savedRun->actual_cost);
    }

    public function test_admin_can_show_and_delete_benchmark_run(): void
    {
        $run = ArabicBenchmarkRun::create([
            'openrouter_model_id' => 'meta/llama-3.3-70b-instruct',
            'thinking_mode' => 'none',
            'tokens_per_word' => 1.45,
            'arabic_words_count' => 196,
            'latency_seconds' => 1.5,
            'tokens_per_second' => 50.0,
            'is_valid_json' => true,
            'total_score' => 4,
            'actual_cost' => 0.00025,
            'prompt_tokens' => 280,
            'completion_tokens' => 75,
            'total_tokens' => 355,
        ]);

        // Show run
        $showResponse = $this->actingAs($this->adminUser)->getJson("/admin/benchmarks/arabic/runs/{$run->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertJson([
            'success' => true,
            'run' => [
                'id' => $run->id,
                'openrouter_model_id' => 'meta/llama-3.3-70b-instruct',
            ],
        ]);

        // Delete run
        $deleteResponse = $this->actingAs($this->adminUser)->deleteJson("/admin/benchmarks/arabic/runs/{$run->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('arabic_benchmark_runs', ['id' => $run->id]);
    }

    public function test_admin_navigating_to_show_run_in_browser_redirects_to_lab_with_query_param(): void
    {
        $run = ArabicBenchmarkRun::create([
            'openrouter_model_id' => 'qwen/qwen-2.5-72b-instruct',
            'thinking_mode' => 'none',
            'tokens_per_word' => 1.2,
            'arabic_words_count' => 196,
            'latency_seconds' => 2.0,
            'tokens_per_second' => 45.0,
            'is_valid_json' => true,
            'total_score' => 156,
            'score_percentage' => 100,
            'actual_cost' => 0.0003,
            'prompt_tokens' => 300,
            'completion_tokens' => 100,
            'total_tokens' => 400,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/benchmarks/arabic/runs/{$run->id}");

        $response->assertRedirect(route('admin.benchmarks.arabic', ['run' => $run->id]));
    }

    public function test_admin_can_load_benchmark_lab_with_preloaded_run(): void
    {
        $provider = Provider::factory()->create(['name' => 'Mistral']);
        $model = AiModel::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Mistral Large',
            'openrouter_id' => 'mistralai/mistral-large',
        ]);

        $run = ArabicBenchmarkRun::create([
            'model_id' => $model->id,
            'openrouter_model_id' => 'mistralai/mistral-large',
            'thinking_mode' => 'none',
            'tokens_per_word' => 1.35,
            'arabic_words_count' => 196,
            'latency_seconds' => 1.8,
            'tokens_per_second' => 40.0,
            'is_valid_json' => true,
            'total_score' => 156,
            'score_percentage' => 100,
            'actual_cost' => 0.0004,
            'prompt_tokens' => 250,
            'completion_tokens' => 90,
            'total_tokens' => 340,
            'raw_response' => '{"task1": -345}',
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/benchmarks/arabic?run={$run->id}");

        $response->assertStatus(200);
        $response->assertSee('Mistral Large');
        $response->assertSee('mistralai/mistral-large');
    }
}
