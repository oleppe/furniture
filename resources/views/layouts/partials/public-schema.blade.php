@php
    $homeUrl = route('home');
    $logoUrl = asset('images/idraak-lab-logo.png');
    $siteName = config('seo.site_name');

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => $homeUrl.'#organization',
        'name' => $siteName,
        'alternateName' => config('seo.site_name_en'),
        'url' => $homeUrl,
        'logo' => [
            '@type' => 'ImageObject',
            '@id' => $homeUrl.'#logo',
            'url' => $logoUrl,
            'width' => 626,
            'height' => 694,
        ],
        'image' => ['@id' => $homeUrl.'#logo'],
        'description' => 'منصة مستقلة تقيس سرعة وذكاء وتكلفة نماذج الذكاء الاصطناعي على المحتوى العربي بقياسات فعلية.',
        'slogan' => 'قياسات فعلية لا تقديرية لأداء الذكاء الاصطناعي بالعربية',
        'knowsLanguage' => ['ar'],
        'areaServed' => config('seo.organization.area_served'),
    ];

    if ($sameAs = config('seo.organization.same_as')) {
        $organizationSchema['sameAs'] = $sameAs;
    }

    if ($contactEmail = config('seo.organization.contact_email')) {
        $organizationSchema['contactPoint'] = [[
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'email' => $contactEmail,
            'availableLanguage' => ['ar', 'en'],
        ]];
    }

    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => $homeUrl.'#website',
        'url' => $homeUrl,
        'name' => $siteName,
        'alternateName' => config('seo.site_name_en'),
        'description' => 'لوحة متصدرين عربية ومقارنة مباشرة لسرعة وذكاء وتكلفة نماذج الذكاء الاصطناعي على المحتوى العربي.',
        'inLanguage' => 'ar',
        'publisher' => ['@id' => $homeUrl.'#organization'],
    ];
@endphp

<script type="application/ld+json">
    @json($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
</script>
<script type="application/ld+json">
    @json($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
</script>
