# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Primary:** Arabic-speaking AI developers and engineers evaluating and selecting LLMs for production workloads.
- **Secondary (internal):** Site administrators managing the model catalog, running Arabic benchmark evaluations, and publishing technical research articles.

## Product Purpose

Idraak Lab (إدراك لاب) is a live Arabic AI model leaderboard, benchmarking lab, and technical research hub. It helps developers compare AI models across speed (latency), intelligence/Arabic quality, Arabic tokenization efficiency, and API cost so they can pick the right model for production Arabic workloads.

## Positioning

Live Arabic-specific benchmarking — measuring Arabic tokenization efficiency, response latency, Arabic quality, and real-world cost in one place, rather than relying on English-centric global leaderboards.

## Operating Context

- **Public surfaces:**
  - `/` — Live Arabic AI model leaderboard and comparison matrix.
  - `/models/{slug}` — Individual SEO model profile pages with pricing, offerings, and benchmark metrics.
  - `/blog` & `/blog/{slug}` — Arabic AI research blog and technical studies.
- **Admin surfaces (`/admin`):** Dashboard, AI models & pricing matrix, Arabic Benchmark Lab & evaluation suite, providers directory, user management, real-time catalog sync pipeline, and research blog CMS (categories & articles).
- **Tech stack:** Laravel 13 (PHP 8.3), Blade templates, Tailwind CSS v4, Alpine.js, and Vite.

## Capabilities and Constraints

- **Single-language Arabic (RTL):** Public UI and copy are Arabic (`lang="ar"`, `dir="rtl"`), with `hreflang="ar"` and `ar_AR` Open Graph targeting.
- **Theme behavior:** Dark-first default (`dark` class) with an opt-in light mode toggle persisted via `localStorage` (`theme` / `admin_theme`).
- **Real data only:** Leaderboard rankings, pricing, offerings, and benchmark runs come from the database and catalog sync/benchmark pipelines; future work must never fabricate scores, metrics, or testimonials.

## Brand Commitments

- **Brand name:** Idraak Lab (`config('seo.site_name')`).
- **SEO & discoverability:** Preserve canonical URLs, Open Graph metadata, `/sitemap.xml`, and `/robots.txt`.

## Evidence on Hand

- Live database-backed model catalog, provider offerings, Arabic benchmark runs (`ArabicBenchmarkService`), and blog articles/categories.
- Existing public views (`resources/views/home.blade.php`, `resources/views/models/`, `resources/views/blog/`, `resources/views/layouts/public.blade.php`) and admin views (`resources/views/admin/`).

## Product Principles

1. **Arabic-first clarity:** Every public metric, label, and layout decision must read naturally in RTL Arabic.
2. **Decision-ready comparisons:** Surface the trade-offs engineers care about in production — Arabic tokenization efficiency, latency, quality, and cost — without hiding key numbers.
3. **Evidence over hype:** Present verifiable benchmark runs and real synced pricing; never invent claims or placeholder scores.
4. **Precision in dark and light:** Maintain crisp readability, contrast, and hierarchy across both the default dark theme and opt-in light mode.

