<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Identity
    |--------------------------------------------------------------------------
    |
    | Brand strings used across the public <head>, Open Graph tags and the
    | JSON-LD Organization node. Keep these in config so marketing copy can
    | change without touching Blade templates.
    |
    */

    'site_name' => env('SEO_SITE_NAME', 'إدراك Lab'),

    'site_name_en' => env('SEO_SITE_NAME_EN', 'Idraak Lab'),

    'tagline' => env('SEO_TAGLINE', 'لوحة المتصدرين العربية لنماذج الذكاء الاصطناعي'),

    /*
    |--------------------------------------------------------------------------
    | Social Preview Image
    |--------------------------------------------------------------------------
    |
    | Used by Open Graph and Twitter Card tags. A 1200x630 landscape image is
    | strongly recommended; the default is the square site logo until a
    | dedicated social card is available.
    |
    */

    'social_image' => [
        'path' => env('SEO_SOCIAL_IMAGE', 'images/idraak-lab-logo.png'),
        'type' => env('SEO_SOCIAL_IMAGE_TYPE', 'image/png'),
        'width' => (int) env('SEO_SOCIAL_IMAGE_WIDTH', 626),
        'height' => (int) env('SEO_SOCIAL_IMAGE_HEIGHT', 694),
        'alt' => env('SEO_SOCIAL_IMAGE_ALT', 'شعار إدراك Lab لمقارنة نماذج الذكاء الاصطناعي بالعربية'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Profiles
    |--------------------------------------------------------------------------
    */

    'twitter' => [
        'site' => env('SEO_TWITTER_SITE'),
        'creator' => env('SEO_TWITTER_CREATOR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Console Verification
    |--------------------------------------------------------------------------
    */

    'verification' => [
        'google' => env('SEO_GOOGLE_VERIFICATION'),
        'bing' => env('SEO_BING_VERIFICATION'),
        'yandex' => env('SEO_YANDEX_VERIFICATION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    */

    'organization' => [
        'same_as' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('SEO_ORGANIZATION_SAME_AS', ''))
        ))),
        'contact_email' => env('SEO_CONTACT_EMAIL'),
        'area_served' => env('SEO_AREA_SERVED', 'SA'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dataset Licensing
    |--------------------------------------------------------------------------
    |
    | License URL declared on the leaderboard Dataset schema. Leave empty to
    | omit the property until a licensing decision is made.
    |
    */

    'dataset_license' => env('SEO_DATASET_LICENSE'),

];
