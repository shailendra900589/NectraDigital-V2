<?php
/**
 * Centralized SEO meta, breadcrumbs, and copy for city hub + service×city pages.
 */
require_once __DIR__ . '/growth/helpers.php';
require_once __DIR__ . '/eeat-copy.php';

function ge_locations_url(): string
{
    return SITE_URL . '/services#locations';
}

function ge_city_hub_path(string $citySlug): string
{
    return '/digital-agency-' . $citySlug;
}

function ge_city_hub_url(string $citySlug): string
{
    return SITE_URL . ge_city_hub_path($citySlug);
}

function ge_service_record_for_seo(string $serviceSlug, array $serviceBase, ?array $geServiceRecord = null): array
{
    if ($geServiceRecord) {
        return $geServiceRecord;
    }

    return [
        'name' => $serviceBase['silo'] ?? ($serviceBase['h1'] ?? 'Digital Service'),
        'slug' => $serviceSlug,
        'url_prefix' => ge_static_service_url_prefix($serviceSlug),
    ];
}

function ge_city_record_for_seo(array $city, string $citySlug): array
{
    return [
        'name' => $city['name'],
        'slug' => $citySlug,
        'state' => $city['state'] ?? '',
        'country' => $city['country'] ?? 'India',
        'city_description' => $city['city_description'] ?? '',
    ];
}

function ge_city_hub_seo(array $city, string $citySlug): array
{
    $name = $city['name'];
    $state = $city['state'] ?? '';
    $canonical = ge_city_hub_url($citySlug);

    $pageTitle = ge_trim_seo_title("Software Development Company in {$name}", 'Nectra Digital', 30, 60);
    $pageDesc = ge_trim_seo_description("Custom software, SaaS, web & mobile apps in {$name}, {$state}. APIs, AI automation, and SEO-ready launches from Nectra Digital. Free discovery call.");
    $pageKeys = implode(', ', array_unique([
        "software development company {$name}",
        "custom software {$name}",
        "SaaS development {$name}",
        "web development {$name}",
        "mobile app development {$name}",
        "AI automation {$name}",
    ]));

    $breadcrumbs = [
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Locations', 'url' => ge_locations_url()],
        ['name' => $name, 'url' => $canonical],
    ];

    return [
        'page_title' => $pageTitle,
        'page_desc' => $pageDesc,
        'page_keys' => $pageKeys,
        'canonical_url' => $canonical,
        'h1' => "Software Development Company in {$name}",
        'hero_label' => !empty($city['is_hq']) ? 'Global Headquarters' : 'Local Engineering Team',
        'hero_intro' => "Custom software, web &amp; mobile apps for teams in {$name}, {$state} — plus SEO, AI automation, and paid media when you need go-to-market support from one Nectra Digital squad.",
        'breadcrumbs' => $breadcrumbs,
        'og_type' => 'website',
    ];
}

function ge_service_city_seo(
    string $serviceSlug,
    array $serviceBase,
    array $city,
    string $citySlug,
    ?array $geServiceRecord = null
): array {
    require_once __DIR__ . '/growth/engines/IntentKeywordEngine.php';

    $svcRecord = ge_service_record_for_seo($serviceSlug, $serviceBase, $geServiceRecord);
    $cityRecord = ge_city_record_for_seo($city, $citySlug);

    $silo = $serviceBase['silo'] ?? ($serviceBase['h1'] ?? $svcRecord['name']);
    $cityName = $city['name'];
    $cityState = $city['state'] ?? '';

    $primary = \Growth\Engines\IntentKeywordEngine::primaryPhrase($svcRecord, $cityRecord, null);

    $h1 = "{$silo} Services in {$cityName}";
    $h2 = "Expert {$silo} in {$cityState} · Results-Driven · Nectra Digital";
    $pageTitle = ge_trim_seo_title(
        \Growth\Engines\IntentKeywordEngine::optimizeMetaTitle(
            "Best {$silo} in {$cityName} | Nectra Digital",
            $primary
        ),
        'Nectra Digital',
        30,
        60
    );
    $pageDesc = ge_trim_seo_description(
        \Growth\Engines\IntentKeywordEngine::optimizeMetaDescription(
            '',
            $svcRecord,
            $cityRecord,
            null
        )
    );
    $pageKeys = \Growth\Engines\IntentKeywordEngine::metaKeywords($svcRecord, $cityRecord, null, 15);

    $introCore = trim($serviceBase['intro'] ?? '');
    if ($introCore === '') {
        $introCore = "Expert {$silo} with data-driven strategy, transparent reporting, and proven ROI.";
    }
    if (mb_strlen($introCore) > 220) {
        $introCore = mb_substr($introCore, 0, 217) . '…';
    }
    $intro = $introCore . " Serving businesses in {$cityName}, {$cityState} with local market expertise.";

    $quickAnswer = "Nectra Digital is a software development and {$silo} partner in {$cityName}, {$cityState} — 200+ products shipped with engineering, transparent delivery, and optional SEO and growth support.";

    require_once __DIR__ . '/seo-components.php';
    $servicePath = ge_service_city_landing_url($serviceSlug, $citySlug);

    $breadcrumbs = [
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Locations', 'url' => ge_locations_url()],
        ['name' => $cityName, 'url' => ge_city_hub_url($citySlug)],
        ['name' => $silo, 'url' => SITE_URL . $servicePath],
    ];

    return [
        'page_title' => $pageTitle,
        'page_desc' => $pageDesc,
        'page_keys' => $pageKeys,
        'canonical_url' => SITE_URL . $servicePath,
        'localized_h1' => $h1,
        'localized_h2' => $h2,
        'localized_intro' => $intro,
        'quick_answer' => $quickAnswer,
        'breadcrumbs' => $breadcrumbs,
        'og_type' => 'website',
    ];
}

function ge_city_hub_faqs(array $city, string $citySlug): array
{
    $cityName = $city['name'];
    $isHq = !empty($city['is_hq']);

    return [
        [
            'q' => "Why choose Nectra Digital for software development in {$cityName}?",
            'a' => "We build custom SaaS, web and mobile apps for {$cityName} teams with Agile delivery, clean architecture, and full source-code ownership — plus SEO-ready launches when you need user acquisition.",
        ],
        [
            'q' => "What services do you offer in {$cityName}?",
            'a' => "Core focus: custom software development, web and mobile apps, SaaS engineering, APIs, and AI automation. Supporting services include SEO, local SEO, Google Ads, social media, and digital marketing for the {$cityName} market.",
        ],
        [
            'q' => "Do you have an office in {$cityName}?",
            'a' => $isHq
                ? "Yes — {$cityName} is our global headquarters. We serve clients across India with on-site and remote delivery."
                : "We serve {$cityName} with dedicated account managers, video consultations, and on-site meetings when required. HQ: Lucknow, pan-India operations.",
        ],
        [
            'q' => "How much does custom software development cost in {$cityName}?",
            'a' => "MVPs and focused tools often start around ₹2–5 lakh; larger SaaS or enterprise builds range higher based on scope. We share a fixed-scope proposal after a free discovery call for {$cityName} projects.",
        ],
        [
            'q' => "How quickly can you start a project in {$cityName}?",
            'a' => ge_city_kickoff_timeline($citySlug),
        ],
    ];
}
