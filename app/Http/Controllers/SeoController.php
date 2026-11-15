<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\ArabicBenchmarkRun;
use App\Models\BenchmarkArabic;
use App\Models\BenchmarkGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SeoController extends Controller
{
    /**
     * How long crawlers and CDNs may cache the discovery endpoints.
     */
    private const CACHE_SECONDS = 3600;

    /**
     * Serve robots.txt dynamically so the sitemap URL always matches the current host.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            '',
            'Sitemap: '.route('seo.sitemap'),
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age='.self::CACHE_SECONDS,
        ]);
    }

    /**
     * Serve the XML sitemap for every publicly indexable page.
     */
    public function sitemap(): Response
    {
        // Freshest data point across the catalog and both benchmark pipelines.
        $lastModified = collect([
            AiModel::max('updated_at'),
            BenchmarkArabic::max('updated_at'),
            ArabicBenchmarkRun::max('updated_at'),
        ])
            ->filter()
            ->map(fn (string $value): Carbon => Carbon::parse($value))
            ->max();

        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => $lastModified,
                'changefreq' => 'daily',
                'priority' => '1.0',
                'images' => [
                    [
                        'loc' => asset('images/idraak-lab-logo.png'),
                        'title' => 'شعار إدراك Lab — لوحة المتصدرين العربية لنماذج الذكاء الاصطناعي',
                    ],
                ],
            ],
            [
                'loc' => route('blog.index'),
                'lastmod' => $lastModified,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
        ];

        foreach (BlogController::posts() as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post['slug']),
                'lastmod' => Carbon::parse($post['published_at']),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        // One indexable profile page per active model that has benchmark data.
        $models = AiModel::query()
            ->select(['id', 'slug', 'updated_at'])
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereIn('id', BenchmarkGeneral::query()->select('model_id')->whereNotNull('intelligence_index'))
                    ->orWhereIn('id', BenchmarkArabic::query()->select('model_id')->whereNotNull('tested_at'));
            })
            ->orderByDesc('updated_at')
            ->get();

        foreach ($models as $model) {
            $urls[] = [
                'loc' => route('models.show', $model->slug),
                'lastmod' => $model->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age='.self::CACHE_SECONDS);
    }
}
