# AI Platform Context

The AI Platform is an intelligence, benchmarking, and cost analytics system designed for evaluating Large Language Models (LLMs) with a special focus on the Arabic language market and multi-provider hosting cost efficiency.

## Language

**User**:
An account record within the platform, categorized by privilege level (`admin`, `editor`, `user`).
_Avoid_: Account, client, member

**Admin**:
A user holding administrative privileges (`role = 'admin'`) authorized to access the `/admin` portal, manage users, curate catalog items, and trigger sync pipelines.
_Avoid_: Superuser, operator, webmaster

**Active Model (`is_active`)**:
A curation flag indicating whether an AI Model is visible in public-facing comparisons and platform listings.
_Avoid_: Enabled, published, visible

**Featured Model (`is_featured`)**:
An editorial highlight flag designating prominent benchmark models to surface at the top of the explorer and leaderboard.
_Avoid_: Starred, promoted, pinned

**AI Model**:
A canonical artificial intelligence model record (e.g., `claude-3-5-sonnet`, `deepseek-r1`) tracking metadata, architectural details, and developer provenance.
_Avoid_: LLM, algorithm, agent, engine

**Provider**:
An entity or company that develops models (e.g., OpenAI, Anthropic) or hosts inference endpoints (e.g., Groq, Azure, Together AI).
_Avoid_: Vendor, host, supplier

**Model Spec**:
The canonical baseline specifications of an AI model, defining token window sizes, maximum output limits, and baseline reference pricing.
_Avoid_: Hardware spec, configuration, parameters

**Model Offering**:
A concrete deployment or hosting endpoint for an AI Model operated by a specific Provider, defining live per-million token rates, response latency (TTFT), throughput (tokens/sec), and feature availability.
_Avoid_: Pricing plan, SKU, endpoint product, tier

