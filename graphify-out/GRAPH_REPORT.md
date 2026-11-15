# Graph Report - .  (2026-09-25)

## Corpus Check
- 91 files · ~157,916 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 496 nodes · 549 edges · 76 communities (52 shown, 24 thin omitted)
- Extraction: 89% EXTRACTED · 10% INFERRED · 0% AMBIGUOUS · INFERRED: 57 edges (avg confidence: 0.59)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Community 0|Community 0]]
- [[_COMMUNITY_Community 1|Community 1]]
- [[_COMMUNITY_Community 2|Community 2]]
- [[_COMMUNITY_Community 3|Community 3]]
- [[_COMMUNITY_Community 4|Community 4]]
- [[_COMMUNITY_Community 5|Community 5]]
- [[_COMMUNITY_Community 6|Community 6]]
- [[_COMMUNITY_Community 7|Community 7]]
- [[_COMMUNITY_Community 8|Community 8]]
- [[_COMMUNITY_Community 9|Community 9]]
- [[_COMMUNITY_Community 10|Community 10]]
- [[_COMMUNITY_Community 11|Community 11]]
- [[_COMMUNITY_Community 12|Community 12]]
- [[_COMMUNITY_Community 13|Community 13]]
- [[_COMMUNITY_Community 14|Community 14]]
- [[_COMMUNITY_Community 15|Community 15]]
- [[_COMMUNITY_Community 16|Community 16]]
- [[_COMMUNITY_Community 17|Community 17]]
- [[_COMMUNITY_Community 18|Community 18]]
- [[_COMMUNITY_Community 19|Community 19]]
- [[_COMMUNITY_Community 20|Community 20]]
- [[_COMMUNITY_Community 21|Community 21]]
- [[_COMMUNITY_Community 22|Community 22]]
- [[_COMMUNITY_Community 23|Community 23]]
- [[_COMMUNITY_Community 24|Community 24]]
- [[_COMMUNITY_Community 32|Community 32]]
- [[_COMMUNITY_Community 33|Community 33]]
- [[_COMMUNITY_Community 34|Community 34]]
- [[_COMMUNITY_Community 35|Community 35]]
- [[_COMMUNITY_Community 36|Community 36]]
- [[_COMMUNITY_Community 37|Community 37]]
- [[_COMMUNITY_Community 38|Community 38]]
- [[_COMMUNITY_Community 45|Community 45]]
- [[_COMMUNITY_Community 46|Community 46]]
- [[_COMMUNITY_Community 47|Community 47]]
- [[_COMMUNITY_Community 48|Community 48]]
- [[_COMMUNITY_Community 49|Community 49]]
- [[_COMMUNITY_Community 75|Community 75]]

## God Nodes (most connected - your core abstractions)
1. `CatalogPricingFetcherService` - 19 edges
2. `AdminPanelTest` - 16 edges
3. `ModelOffering` - 14 edges
4. `AiModel` - 12 edges
5. `User` - 12 edges
6. `require-dev` - 9 edges
7. `scripts` - 9 edges
8. `CatalogPricingFetcherTest` - 8 edges
9. `AiPlatformDatabaseTest` - 8 edges
10. `AiModelController` - 6 edges

## Surprising Connections (you probably didn't know these)
- `ADR 0001: Bespoke Blade/Tailwind v4 Admin` --decides architecture of--> `Admin Layout (layouts/admin.blade.php)`  [INFERRED]
   →   _Bridges community 1 → community 8_
- `Artisan CLI Entry Point` --requires and bootstraps--> `Application Bootstrap (bootstrap/app.php)`  [EXTRACTED]
   →   _Bridges community 5 → community 7_
- `AdminMiddleware` --checks isAdmin() on authenticated--> `User (users table)`  [EXTRACTED]
   →   _Bridges community 7 → community 0_
- `ModelOffering Model` --belongs to (aiModel relation on model_id)--> `AiModel (canonical AI model record)`  [EXTRACTED]
   →   _Bridges community 1 → community 0_
- `UserFactory` --extends--> `Illuminate Factory Base Class`  [EXTRACTED]
   →   _Bridges community 3 → community 0_

## Hyperedges (group relationships)
- **AI Platform Catalog Data Model** — table_providers, table_models, table_model_specs, table_benchmarks_general, table_benchmarks_arabic, table_speed_telemetry, table_model_offerings [EXTRACTED]
- **Laravel Framework Infrastructure Tables** — table_users, table_password_reset_tokens, table_sessions, table_cache, table_cache_locks, table_jobs, table_job_batches, table_failed_jobs [EXTRACTED]
- **Admin Panel Module (controllers protected by AdminMiddleware)** — admin_middleware, auth_controller, dashboard_controller, user_controller, ai_model_controller, provider_controller, catalog_sync_controller, admin_login_route, admin_dashboard_route [INFERRED]
- **Bespoke Admin Panel (Blade + Tailwind v4 + Alpine.js per ADR 0001)** —  [INFERRED]

## Communities (76 total, 24 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.00
Nodes (40): admin.dashboard Route, ProviderController, AiModel (models table), Admin AiModelController, AiModelFactory, Admin AuthController, Controller (abstract base), Illuminate Factory Base Class (+32 more)

### Community 1 - "Community 1"
Cohesion: 0.00
Nodes (50): catalog:fetch-prices Artisan Command, Admin Portal Routes, Arabic Token Efficiency Metrics, Model Capabilities JSON (vision, function calling, caching, web search), Model Curation Flags (is_active, is_featured), Model Offering (hosting endpoint + pricing), OpenRouter /models API (data source), Tiered & Caching Pricing Matrix (batch, flex, priority, cache read/write, regional uplift) (+42 more)

### Community 2 - "Community 2"
Cohesion: 0.00
Nodes (42): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+34 more)

### Community 3 - "Community 3"
Cohesion: 0.00
Nodes (9): DashboardController, UserController, UserFactory, AdminPanelTest, User, AdminUserSeeder, DatabaseSeeder, User (+1 more)

### Community 4 - "Community 4"
Cohesion: 0.00
Nodes (4): AiPlatformDatabaseTest, ModelOffering, ModelSpec, SpeedTelemetry

### Community 5 - "Community 5"
Cohesion: 0.00
Nodes (20): Artisan CLI Entry Point, boost:mcp Command, agents, cloud, guidelines, mcp, nightwatch, sail (+12 more)

### Community 6 - "Community 6"
Cohesion: 0.00
Nodes (21): dependencies, alpinejs, devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite (+13 more)

### Community 7 - "Community 7"
Cohesion: 0.00
Nodes (20): admin.login Route, AdminMiddleware, AppServiceProvider, BoostServiceProvider, Application Bootstrap (bootstrap/app.php), Provider Registry (bootstrap/providers.php), catalog:fetch-prices Command, inspire Command (+12 more)

### Community 8 - "Community 8"
Cohesion: 0.00
Nodes (19): ADR 0001: Bespoke Blade/Tailwind v4 Admin, ADR 0002: Admin Authentication & Role Scoping, AdminMiddleware (role === 'admin' guard), Admin Role & Admin Portal Access, Users Password Reset Broker, Role-Based Admin Access (admin, editor, user), Web Session Auth Guard, Auth Configuration (+11 more)

### Community 12 - "Community 12"
Cohesion: 0.00
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 17 - "Community 17"
Cohesion: 0.00
Nodes (4): Filesystems Configuration, Local Storage Disk, Public Storage Disk, S3 Storage Disk

### Community 20 - "Community 20"
Cohesion: 0.00
Nodes (4): Create Jobs Table Migration, failed_jobs Table, job_batches Table, jobs Table

### Community 21 - "Community 21"
Cohesion: 0.00
Nodes (4): README (Laravel framework), Vite JS Entry (resources/js/app.js), Laravel Front Controller (public/index.php), Welcome Page (welcome.blade.php)

### Community 38 - "Community 38"
Cohesion: 0.00
Nodes (3): Create Cache Table Migration, cache Table, cache_locks Table

## Ambiguous Edges - Review These
- `AiPlatformSeeder` → `AiModel (canonical AI model record)`  [AMBIGUOUS]
   · relation: intended to seed
- `App Configuration (config/app.php)` → `Services Configuration (config/services.php)`  [AMBIGUOUS]
   · relation: configures third-party mail and notification services for

## Knowledge Gaps
- **64 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+59 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **24 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.