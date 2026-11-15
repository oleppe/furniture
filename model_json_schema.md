# OpenRouter Model JSON Schema & Database Mapping Reference
## توثيق هيكلية بيانات النماذج من OpenRouter API ومطابقتها في قاعدة البيانات

هذا الملف يوثق جميع الحقول المسترجعة من الـ Endpoint الرسمي لـ OpenRouter:
`https://openrouter.ai/api/v1/models`
وكيفية تخزينها ومطابقتها في جداول قاعدة البيانات الخاصة بالمنصة (`models`, `model_specs`, `benchmarks_general`).

---

## 1. جدول حقول الـ JSON الكامل ومطابقتها في قاعدة البيانات

### 1.1 بيانات التعريف الأساسية (Core Identification & Metadata)
| الحقل في OpenRouter JSON | النوع (Type) | جدول وعمود التخزين | الوصف | مثال فعلي (Live Example) |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `string` | `models.openrouter_id` | المعرف الفريد للموديل لدى OpenRouter | `"anthropic/claude-opus-5.5"` |
| `canonical_slug` | `string` | `models.canonical_slug` | السلاج المعتمد مع تاريخ النسخة | `"anthropic/claude-opus-5.5-20260921"` |
| `hugging_face_id` | `string\|null` | `models.hugging_face_id` | معرّف النموذج على Hugging Face إن وُجد | `"inclusionAI/Ling-3.0-flash-VL"` أو `null` |
| `name` | `string` | `models.name` | الاسم التسويقي الكامل للنموذج | `"Anthropic: Claude Opus 5.5"` |
| `created` | `integer` | `models.release_date` | طابع زمني (Unix timestamp) لإطلاق النموذج | `1790094732` (تاريخ: `2026-09-21`) |
| `description` | `string` | `models.description` | الوصف الرسمي للنموذج واستخداماته | `"Claude Opus 5.5 is Anthropic's flagship model..."` |

---

### 1.2 معمارية النموذج والوسائط (`architecture`)
| الحقل في OpenRouter JSON | النوع (Type) | جدول وعمود التخزين | الوصف | مثال فعلي (Live Example) |
| :--- | :--- | :--- | :--- | :--- |
| `architecture.modality` | `string` | `models.architecture` | اتجاه ونوع المعالجة | `"text+image+video->text"` أو `"text->text"` |
| `architecture.input_modalities` | `array` | `models.architecture_details` | الوسائط المدعومة في المدخلات | `["text", "image", "file"]` |
| `architecture.output_modalities`| `array` | `models.architecture_details` | الوسائط المدعومة في المخرجات | `["text"]` |
| `architecture.tokenizer` | `string` | `models.architecture_details` | نوع الـ Tokenizer المعتمد | `"Claude"` أو `"o200k_base"` أو `"Llama3"` |
| `architecture.instruct_type` | `string\|null` | `models.architecture_details` | تنسيق التعليمات (Prompt Template Format) | `"chatml"` أو `"alpaca"` أو `null` |

---

### 1.3 التسعير والتكاليف اللحظية (`pricing`)
> **ملاحظة:** يتم تحويل أسعار التوكن الفردية في الـ JSON تلقائياً إلى تسعير لكل **1 مليون توكن (Per 1 Million Tokens)** بالدولار في قاعدة البيانات لتسهيل المقارنة والحسابات الحسابية.

| الحقل في OpenRouter JSON | النوع | جدول وعمود التخزين | الوصف والتحويل | مثال فعلي (Live Example) |
| :--- | :--- | :--- | :--- | :--- |
| `pricing.prompt` | `string` | `model_specs.input_cost_per_1m` | سعر توكن الإدخال (يُضرب في 1,000,000) | `"0.000004"` ($4.00 لكل 1M توكن) |
| `pricing.completion` | `string` | `model_specs.output_cost_per_1m` | سعر توكن الإخراج (يُضرب في 1,000,000) | `"0.00002"` ($20.00 لكل 1M توكن) |
| `pricing.input_cache_read` | `string\|null` | `model_specs.input_cache_read_cost_per_1m` | سعر قراءة الكاش (يُضرب في 1,000,000) | `"0.0000002"` ($0.20 لكل 1M توكن) |
| `pricing.input_cache_write` | `string\|null` | `model_specs.input_cache_write_cost_per_1m` | سعر كتابة الكاش (يُضرب في 1,000,000) | `"0.000005"` ($5.00 لكل 1M توكن) |
| `pricing.input_cache_write_1h` | `string\|null` | `model_specs.input_cache_write_1h_cost_per_1m` | سعر كاش ساعة كاملة (Prompt Caching 1h) | `"0.000008"` ($8.00 لكل 1M توكن) |
| `pricing.web_search` | `string\|null` | `model_specs.web_search_cost` | تكلفة طلب البحث على الويب للنموذج | `"0.01"` ($0.01 لكل استعلام بحث) |
| `pricing.internal_reasoning` | `string\|null` | `model_specs.internal_reasoning_cost_per_1m` | تكلفة توكنات التفكير الداخلي | `"0.000002"` ($2.00 لكل 1M توكن) |
| `pricing.image` | `string\|null` | `model_specs.image_input_cost` | تكلفة معالجة صورة واحدة في المدخلات | `"0.003"` ($0.003 للصورة) |
| `pricing.image_output` | `string\|null` | `model_specs.image_output_cost` | تكلفة توليد صورة واحدة في المخرجات | `"0.04"` ($0.04 للصورة) |
| `pricing.audio` | `string\|null` | `model_specs.audio_input_cost_per_1m` | تكلفة مدخلات الصوت لكل 1M توكن | `"0.00001"` ($10.00 لكل 1M توكن) |
| `pricing.audio_output` | `string\|null` | `model_specs.audio_output_cost_per_1m` | تكلفة مخرجات الصوت لكل 1M توكن | `"0.00002"` ($20.00 لكل 1M توكن) |
| `pricing.overrides` | `array\|null` | `model_specs.pricing_overrides` | خصومات الحجم والكمية (Tiered Pricing) | `[{"min_prompt_tokens": 272000, "prompt": "0.000002"}]` |
| `pricing` (كامل الكائن) | `object` | `model_specs.raw_pricing` | الكائن الخام كما ورد من المصدر | `{...}` |

---

### 1.4 حدود السياق والمزود الأعلى (`top_provider`)
| الحقل في OpenRouter JSON | النوع (Type) | جدول وعمود التخزين | الوصف | مثال فعلي (Live Example) |
| :--- | :--- | :--- | :--- | :--- |
| `context_length` | `integer` | `model_specs.context_window` | الحجم الكلي لنافذة السياق (Context Window) | `1000000` (1 مليون توكن) |
| `top_provider.context_length` | `integer` | `model_specs.top_provider_context_length` | أقصى سياق متاح عبر المزود الأول | `1000000` |
| `top_provider.max_completion_tokens` | `integer` | `model_specs.max_output_tokens` | الحد الأقصى للمخرجات في الطلب الواحد | `128000` |
| `top_provider.is_moderated` | `boolean` | `model_specs.is_moderated` | هل المزود يطبق فلترة محتوى تلقائية | `true` أو `false` |

---

### 1.5 التقييمات المعيارية ومؤشرات الذكاء (`benchmarks`)
| الحقل في OpenRouter JSON | النوع (Type) | جدول وعمود التخزين | الوصف | مثال فعلي (Live Example) |
| :--- | :--- | :--- | :--- | :--- |
| `benchmarks.artificial_analysis.intelligence_index` | `float\|null` | `benchmarks_general.intelligence_index` | مؤشر الذكاء العام من منصة Artificial Analysis | `57.6` |
| `benchmarks.artificial_analysis.coding_index` | `float\|null` | `benchmarks_general.coding_index` | مؤشر كفاءة البرمجة وهندسة البرمجيات | `57.0` |
| `benchmarks.artificial_analysis.agentic_index` | `float\|null` | `benchmarks_general.agentic_index` | مؤشر تنفيذ مهام الوكلاء الذاتية (AI Agents) | `28.7` |
| `benchmarks.design_arena` | `array` | `benchmarks_general.design_arena` | تصنيفات وبطولات ساحات التصميم والـ 3D | `[{"arena": "agents", "category": "webapps", "elo": 1297, "win_rate": 58.4, "rank": 4}]` |
| `benchmarks` (كامل الكائن) | `object` | `benchmarks_general.raw_benchmarks` | كامل سجل التقييمات الأصلي | `{...}` |

---

### 1.6 قدرات التفكير وسلاسل الأفكار (`reasoning`)
| الحقل في OpenRouter JSON | النوع (Type) | جدول وعمود التخزين | الوصف | مثال فعلي (Live Example) |
| :--- | :--- | :--- | :--- | :--- |
| `reasoning.mandatory` | `boolean` | `models.reasoning_spec['mandatory']` | هل التفكير المنطقي إجباري لا يمكن تعطيله | `true` |
| `reasoning.default_enabled` | `boolean\|null` | `models.reasoning_spec['default_enabled']` | هل التفكير مفعل افتراضياً في الاستدعاء | `true` |
| `reasoning.default_effort` | `string\|null` | `models.reasoning_spec['default_effort']` | مستوى الجهد الافتراضي للتفكير | `"high"` أو `"medium"` أو `"max"` |
| `reasoning.supported_efforts` | `array\|null` | `models.reasoning_spec['supported_efforts']` | مستويات جهد التفكير المسموح بها | `["low", "medium", "high", "max"]` |
| `reasoning.supports_max_tokens` | `boolean\|null` | `models.reasoning_spec['supports_max_tokens']` | إمكانية تحديد ميزانية توكنات التفكير | `true` |

---

### 1.7 البارامترات والحدود والروابط الإضافية
| الحقل في OpenRouter JSON | النوع (Type) | جدول وعمود التخزين | الوصف | مثال فعلي (Live Example) |
| :--- | :--- | :--- | :--- | :--- |
| `supported_parameters` | `array` | `models.supported_parameters` | البارامترات المدعومة من النموذج | `["temperature", "top_p", "tools", "tool_choice", "response_format", "structured_outputs", "reasoning"]` |
| `default_parameters` | `object` | `models.default_parameters` | القيم الافتراضية لكل بارامتر | `{"temperature": 1, "top_p": 0.95}` |
| `supported_voices` | `array\|null` | `models.supported_voices` | الأصوات المدعومة لموديلات توليد الصوت | `[]` أو أسماء الأصوات |
| `knowledge_cutoff` | `string\|null` | `models.knowledge_cutoff` | تاريخ انتهاء معرفة النموذج (Cutoff Date) | `"2026-02-16"` |
| `expiration_date` | `string\|null` | `models.expiration_date` | تاريخ تقاعد أو إيقاف النموذج من الموزع | `"2098-12-31"` |
| `links.details` | `string` | `models.openrouter_details_url` | رابط تفاصيل الـ Endpoints والموزعين | `"/api/v1/models/z-ai/glm-5.3-prime-20260921/endpoints"` |
| `alias_target` | `object\|null` | `models.alias_target` | إذا كان النموذج يوجه تلقائياً إلى نموذج آخر | `{"name": "DeepSeek: DeepSeek V4 Pro 0813", "slug": "deepseek/deepseek-v4-pro-0813"}` |
| `per_request_limits` | `object\|null` | `models.per_request_limits` | قيود الطلبات اللحظية لكل مستخدم إن وُجدت | `null` |

---

## 2. أمثلة JSON فعلية متكاملة من الـ API الحقيقي

### المثال 1: نموذج استدلال رائد مع تقييمات (`anthropic/claude-opus-5.5`)
```json
{
  "id": "anthropic/claude-opus-5.5",
  "canonical_slug": "anthropic/claude-opus-5.5-20260921",
  "hugging_face_id": null,
  "name": "Anthropic: Claude Opus 5.5",
  "created": 1790094732,
  "description": "Claude Opus 5.5 is Anthropic's flagship model for demanding reasoning, coding, and long-horizon agentic work, succeeding Claude Opus 5. It is particularly strong at multi-step changes in large codebases, code...",
  "context_length": 1000000,
  "architecture": {
    "modality": "text+image+file->text",
    "input_modalities": [
      "text",
      "image",
      "file"
    ],
    "output_modalities": [
      "text"
    ],
    "tokenizer": "Claude",
    "instruct_type": null
  },
  "pricing": {
    "prompt": "0.000004",
    "completion": "0.00002",
    "web_search": "0.01",
    "input_cache_read": "0.0000002",
    "input_cache_write": "0.000005",
    "input_cache_write_1h": "0.000008"
  },
  "top_provider": {
    "context_length": 1000000,
    "max_completion_tokens": 128000,
    "is_moderated": true
  },
  "per_request_limits": null,
  "supported_parameters": [
    "include_reasoning",
    "max_completion_tokens",
    "max_tokens",
    "reasoning",
    "reasoning_effort",
    "response_format",
    "stop",
    "structured_outputs",
    "temperature",
    "tool_choice",
    "tools",
    "verbosity"
  ],
  "default_parameters": {},
  "supported_voices": null,
  "knowledge_cutoff": null,
  "expiration_date": null,
  "links": {
    "details": "/api/v1/models/anthropic/claude-opus-5.5-20260921/endpoints"
  },
  "benchmarks": {
    "design_arena": [],
    "artificial_analysis": {
      "intelligence_index": 57.6,
      "coding_index": null,
      "agentic_index": null
    }
  },
  "reasoning": {
    "mandatory": true,
    "supported_efforts": [
      "max",
      "xhigh",
      "high",
      "medium",
      "low"
    ],
    "default_effort": "high"
  }
}
```

---

### المثال 2: نموذج متعدد الوسائط مع تقييمات كاملة وساحات التصميم (`inclusionai/ling-3.0-flash-vl`)
```json
{
  "id": "inclusionai/ling-3.0-flash-vl",
  "canonical_slug": "inclusionai/ling-3.0-flash-vl-20260910",
  "hugging_face_id": "inclusionAI/Ling-3.0-flash-VL",
  "name": "inclusionAI: Ling 3.0 Flash VL",
  "created": 1789056114,
  "description": "Ling 3.0 Flash VL builds on Ling 3.0 Flash (124B total / 5.5B active MoE from InclusionAI), further strengthening its language capabilities while adding native visual perception and advanced visual...",
  "context_length": 262144,
  "architecture": {
    "modality": "text+image+video->text",
    "input_modalities": [
      "text",
      "image",
      "video"
    ],
    "output_modalities": [
      "text"
    ],
    "tokenizer": "Other",
    "instruct_type": null
  },
  "pricing": {
    "prompt": "0.00000006",
    "completion": "0.00000018",
    "input_cache_read": "0.000000012"
  },
  "top_provider": {
    "context_length": 131072,
    "max_completion_tokens": 32768,
    "is_moderated": false
  },
  "per_request_limits": null,
  "supported_parameters": [
    "frequency_penalty",
    "include_reasoning",
    "logit_bias",
    "logprobs",
    "max_tokens",
    "min_p",
    "presence_penalty",
    "reasoning",
    "repetition_penalty",
    "response_format",
    "seed",
    "stop",
    "structured_outputs",
    "temperature",
    "tool_choice",
    "tools",
    "top_k",
    "top_logprobs",
    "top_p"
  ],
  "default_parameters": {},
  "supported_voices": null,
  "knowledge_cutoff": null,
  "expiration_date": null,
  "links": {
    "details": "/api/v1/models/inclusionai/ling-3.0-flash-vl-20260910/endpoints"
  },
  "benchmarks": {
    "design_arena": [
      {
        "arena": "agents",
        "category": "webapps",
        "elo": 1297,
        "win_rate": 58.4,
        "rank": 4
      },
      {
        "arena": "models",
        "category": "3d",
        "elo": 1348,
        "win_rate": 58.8,
        "rank": 8
      }
    ],
    "artificial_analysis": {
      "intelligence_index": 24.6,
      "coding_index": 57.0,
      "agentic_index": 28.7
    }
  },
  "reasoning": {
    "mandatory": false,
    "default_enabled": true
  }
}
```

---

### المثال 3: نموذج تفكير فائق السرعة مع قيم افتراضية ومستويات جهد (`z-ai/glm-5.3-prime`)
```json
{
  "id": "z-ai/glm-5.3-prime",
  "canonical_slug": "z-ai/glm-5.3-prime-20260921",
  "hugging_face_id": null,
  "name": "Z.ai: GLM 5.3 Prime",
  "created": 1790199651,
  "description": "GLM-5.3-Prime is the high-speed variant of Z.ai's GLM-5.3, inheriting its full capabilities while delivering 1.5–2× the output throughput through inference acceleration. It supports text input and output with a 1M-token...",
  "context_length": 1000000,
  "architecture": {
    "modality": "text->text",
    "input_modalities": [
      "text"
    ],
    "output_modalities": [
      "text"
    ],
    "tokenizer": "Other",
    "instruct_type": null
  },
  "pricing": {
    "prompt": "0.0000028",
    "completion": "0.0000088",
    "input_cache_read": "0.00000056"
  },
  "top_provider": {
    "context_length": 1000000,
    "max_completion_tokens": 131072,
    "is_moderated": false
  },
  "per_request_limits": null,
  "supported_parameters": [
    "frequency_penalty",
    "include_reasoning",
    "logprobs",
    "max_tokens",
    "presence_penalty",
    "reasoning",
    "reasoning_effort",
    "response_format",
    "seed",
    "stop",
    "temperature",
    "tool_choice",
    "tools",
    "top_k",
    "top_logprobs",
    "top_p"
  ],
  "default_parameters": {
    "temperature": 1,
    "top_p": 0.95
  },
  "supported_voices": null,
  "knowledge_cutoff": null,
  "expiration_date": null,
  "links": {
    "details": "/api/v1/models/z-ai/glm-5.3-prime-20260921/endpoints"
  },
  "reasoning": {
    "mandatory": true,
    "default_enabled": true,
    "supported_efforts": [
      "max",
      "high",
      "low"
    ],
    "default_effort": "max"
  }
}
```

---

### المثال 4: نموذج مع خصومات متدرجة حسب الحجم (Tiered Pricing Overrides)
```json
{
  "id": "openai/gpt-6-luna-pro",
  "pricing": {
    "prompt": "0.0000001",
    "completion": "0.0000005",
    "web_search": "0.01",
    "input_cache_read": "0.00000001",
    "input_cache_write": "0.000000125",
    "overrides": [
      {
        "min_prompt_tokens": 272000,
        "prompt": "0.0000002",
        "completion": "0.00000075",
        "input_cache_read": "0.00000002",
        "input_cache_write": "0.00000025"
      }
    ]
  }
}
```

---

## 3. هيكلية عروض الاستضافة وتعدد المزودين (Multi-Provider Hosting & Offerings - LiteLLM)

### 3.1 جدول عروض الاستضافة (`model_offerings`)
يربط هذا الجدول النموذج الأساسي بمختلف نقاط الاستضافة المتاحة سحابياً (سواء مباشرة من الشركة المصنعة، أو عبر السحابة المؤسسية مثل Azure و AWS Bedrock، أو محركات الاستدلال فائق السرعة مثل Groq و Cerebras):

| الحقل في LiteLLM Spec | جدول وعمود التخزين | النوع | الوصف والتصنيف |
| :--- | :--- | :--- | :--- |
| مفتاح الـ Endpoint في LiteLLM | `model_offerings.endpoint_key` | `string` | المعرف الفريد للنقطة (مثال: `azure/eu/gpt-4o-2024-08-06`) |
| `litellm_provider` أو بادئة المفتاح | `model_offerings.provider_id` | `foreignId` | ربط المزود المستضيف في جدول `providers` |
| تصنيف المزود | `model_offerings.host_type` | `string` | تصنيف الاستضافة: `direct`, `enterprise_cloud`, `fast_inference`, `aggregator` |
| استخراج المنطقة من المفتاح | `model_offerings.region` | `string` | المنطقة الجغرافية: `global`, `eu`, `us`, `apac` |
| `input_cost_per_token` * 1M | `model_offerings.input_cost_per_1m` | `decimal` | السعر القياسي لمليون توكن إدخال |
| `output_cost_per_token` * 1M | `model_offerings.output_cost_per_1m` | `decimal` | السعر القياسي لمليون توكن إخراج |
| `input_cost_per_token_batches` * 1M | `model_offerings.batch_input_cost_per_1m` | `decimal` | سعر الإدخال للطلبات المجمعة (خصم 50% Batch API) |
| `output_cost_per_token_batches` * 1M | `model_offerings.batch_output_cost_per_1m` | `decimal` | سعر الإخراج للطلبات المجمعة (Batch API) |
| `input_cost_per_token_flex` * 1M | `model_offerings.flex_input_cost_per_1m` | `decimal` | سعر إدخال فليكس/سبوت المنخفض للأوقات خارج الذروة |
| `output_cost_per_token_flex` * 1M | `model_offerings.flex_output_cost_per_1m` | `decimal` | سعر إخراج فليكس/سبوت |
| `input_cost_per_token_priority` * 1M | `model_offerings.priority_input_cost_per_1m` | `decimal` | سعر إدخال المعالجة ذات الأولوية الفائقة (Priority Tier) |
| `output_cost_per_token_priority` * 1M | `model_offerings.priority_output_cost_per_1m` | `decimal` | سعر إخراج المعالجة ذات الأولوية الفائقة |
| `input_cost_per_token_above_272k_tokens` * 1M | `model_offerings.context_tier_above_272k_input_cost_per_1m` | `decimal` | سعر الإدخال للسياق الممتد فوق 272k توكن |
| `output_cost_per_token_above_272k_tokens` * 1M | `model_offerings.context_tier_above_272k_output_cost_per_1m` | `decimal` | سعر الإخراج للسياق الممتد فوق 272k توكن |
| `cache_read_input_token_cost` * 1M | `model_offerings.cache_read_input_token_cost_per_1m` | `decimal` | تكلفة قراءة الكاش لمليون توكن |
| `cache_creation_input_token_cost` * 1M | `model_offerings.cache_creation_input_token_cost_per_1m` | `decimal` | تكلفة إنشاء/كتابة الكاش لمليون توكن |
| `regional_processing_uplift_multiplier_eu` | `model_offerings.regional_processing_uplift_eu` | `decimal` | معامل الزيادة الإقليمية لمنطقة أوروبا (مثلاً 1.100 = +10%) |
| `regional_processing_uplift_multiplier_us` | `model_offerings.regional_processing_uplift_us` | `decimal` | معامل الزيادة الإقليمية لمنطقة أمريكا |
| مطابقة المزود مع منشئ الموديل | `model_offerings.is_official_direct` | `boolean` | هل هذه النقطة هي الرسمية المباشرة من الشركة المنتجة |
| حساب أقل سعر إدخال لكل موديل | `model_offerings.is_cheapest` | `boolean` | إشارة لأرخص مزود يقدم هذا الموديل حالياً |
| كامل الكائن من LiteLLM | `model_offerings.raw_offering_spec` | `json` | كائن البيانات الخام للمزود |

---

### 3.2 إمكانيات وقدرات النموذج (`models.capabilities`)
حقل `JSON` على مستوى جدول `models` يجمع خصائص النموذج الذكية من كلا المصدرين:

```json
{
  "supports_vision": true,
  "supports_function_calling": true,
  "supports_parallel_function_calling": true,
  "supports_response_schema": true,
  "supports_prompt_caching": true,
  "supports_prompt_cache_breakpoint": true,
  "supports_pdf_input": true,
  "supports_computer_use": false,
  "supports_reasoning": true,
  "supports_native_streaming": true,
  "supports_system_messages": true,
  "supports_tool_choice": true,
  "supports_web_search": true,
  "supports_audio_input": false,
  "supports_audio_output": false
}
```

---

### المثال 5: نموذج LiteLLM متقدم متعدد الأسعار والمناطق (`gpt-6-astra`)
```json
{
  "gpt-6-astra": {
    "cache_creation_input_token_cost": 1.25e-05,
    "cache_creation_input_token_cost_above_272k_tokens": 2.5e-05,
    "cache_creation_input_token_cost_above_272k_tokens_flex": 1.25e-05,
    "cache_creation_input_token_cost_above_272k_tokens_priority": 5e-05,
    "cache_creation_input_token_cost_flex": 6.25e-06,
    "cache_creation_input_token_cost_priority": 2.5e-05,
    "cache_read_input_token_cost": 1e-06,
    "cache_read_input_token_cost_above_272k_tokens": 2e-06,
    "cache_read_input_token_cost_above_272k_tokens_flex": 1e-06,
    "cache_read_input_token_cost_above_272k_tokens_priority": 4e-06,
    "cache_read_input_token_cost_batches": 5e-07,
    "cache_read_input_token_cost_above_272k_tokens_batches": 1e-06,
    "cache_creation_input_token_cost_batches": 6.25e-06,
    "cache_creation_input_token_cost_above_272k_tokens_batches": 1.25e-05,
    "cache_read_input_token_cost_flex": 5e-07,
    "cache_read_input_token_cost_priority": 2e-06,
    "input_cost_per_token": 1e-05,
    "input_cost_per_token_above_272k_tokens": 2e-05,
    "input_cost_per_token_above_272k_tokens_flex": 1e-05,
    "input_cost_per_token_above_272k_tokens_priority": 4e-05,
    "input_cost_per_token_batches": 5e-06,
    "input_cost_per_token_above_272k_tokens_batches": 1e-05,
    "input_cost_per_token_flex": 5e-06,
    "input_cost_per_token_priority": 2e-05,
    "litellm_provider": "openai",
    "max_input_tokens": 922000,
    "max_output_tokens": 128000,
    "max_tokens": 128000,
    "mode": "chat",
    "output_cost_per_token": 5e-05,
    "output_cost_per_token_above_272k_tokens": 7.5e-05,
    "output_cost_per_token_above_272k_tokens_flex": 3.75e-05,
    "output_cost_per_token_above_272k_tokens_priority": 0.00015,
    "output_cost_per_token_batches": 2.5e-05,
    "output_cost_per_token_above_272k_tokens_batches": 3.75e-05,
    "output_cost_per_token_flex": 2.5e-05,
    "output_cost_per_token_priority": 0.0001,
    "regional_processing_uplift_multiplier_eu": 1.1,
    "regional_processing_uplift_multiplier_us": 1.1,
    "supports_computer_use": 1,
    "supports_function_calling": 1,
    "supports_parallel_function_calling": 1,
    "supports_pdf_input": 1,
    "supports_prompt_cache_breakpoint": 1,
    "supports_prompt_caching": 1,
    "supports_reasoning": 1,
    "supports_response_schema": 1,
    "supports_vision": 1,
    "supports_web_search": 1
  }
}
```
