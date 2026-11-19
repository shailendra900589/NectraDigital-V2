<?php
require_once 'includes/db.php';
require_once 'includes/seo-data.php';
require_once 'includes/blog_orphan.php';

blog_orphan_ensure_schema($conn);
$listWhere = blog_listable_sql();

header('Content-Type: text/plain; charset=utf-8');

if (!defined('SITE_URL')) {
    define('SITE_URL', 'https://www.nectradigital.com');
}

echo "# Nectra Digital\n\n";
echo "> Nectra Digital is a custom software development company headquartered in India. The team builds SaaS platforms, web applications, mobile apps, APIs, and internal business tools — then layers AI automation, SEO, and performance marketing to launch and scale products. Founded by Ravindra Kumar Chauhan, Nectra Digital serves startups, SMEs, and enterprises across 20+ Indian cities and international markets.\n\n";

echo "## Core Services\n\n";
foreach (get_services_data() as $slug => $svc) {
    echo "- [" . $svc['h1'] . "](" . SITE_URL . "/" . $slug . "): " . strip_tags($svc['intro']) . "\n";
}
echo "\n";

echo "## Industries\n\n";
$industries = [
    'Healthcare' => 'Medical practices, diagnostic clinics, hospital networks, and telehealth platforms.',
    'Real Estate' => 'Property developers, commercial realty brokers, and residential real estate agencies.',
    'Education' => 'EdTech startups, educational institutions, coaching academies, and universities.',
    'Finance & FinTech' => 'Banking software, payment gateway dashboards, wealth management, and loan portals.',
    'E-commerce' => 'Direct-to-consumer (D2C) brands, multi-vendor marketplaces, and headless Shopify stores.',
    'Startups & SaaS' => 'B2B software platforms, MVP engineering, and product-led organic search engines.',
    'Enterprise' => 'High-traffic legacy web architectures requiring custom software refactoring and enterprise SEO.'
];
foreach ($industries as $indName => $indDesc) {
    echo "- " . $indName . ": " . $indDesc . "\n";
}
echo "\n";

echo "## Company\n\n";
echo "- [About & Founder](" . SITE_URL . "/about): Company history and founder Ravindra Kumar Chauhan (" . FOUNDER_EXPERIENCE . " in custom software development, SaaS engineering, AI automation, and SEO).\n";
echo "- [Case Studies & Portfolio](" . SITE_URL . "/portfolio): Verified client deployments including NOVA PAY (Dubai FinTech), Oudh Organics (D2C E-commerce), and Auto-Rep (AI Voice Agent).\n";
echo "- [Hire Dedicated Experts](" . SITE_URL . "/hire-experts): Dedicated software engineers, full-stack developers, QA, DevOps, and growth specialists.\n";
echo "- [Contact Support & Strategy](" . SITE_URL . "/contact): Headquarters in Lucknow, Uttar Pradesh, India with global timezone operational coverage.\n";
echo "- [Editorial Guidelines](" . SITE_URL . "/editorial-guidelines): E-E-A-T verification standards and review methodologies.\n\n";

echo "## Resources\n\n";
echo "- [Digital Intelligence & Blog](" . SITE_URL . "/insights): Software engineering guides, SaaS playbooks, AI automation, and SEO for product teams.\n";
echo "- [AEO Knowledge Base](" . SITE_URL . "/aeo): Direct, verified answers optimized for generative AI search engines and answer engines.\n";
echo "- [Free Marketing & SEO Tools](" . SITE_URL . "/tools): Diagnostic utilities and digital marketing calculators.\n\n";

echo "## City Coverage (Pan-India)\n\n";
foreach (get_cities_data() as $slug => $city) {
    echo "- [Software Development " . $city['name'] . "](" . SITE_URL . "/digital-agency-" . $slug . "): Custom software, web & mobile apps in " . $city['name'] . ", " . ($city['state'] ?? 'India') . " (SEO & growth optional).\n";
}
echo "\n";

echo "## Latest Published Intel\n\n";
$sql = "SELECT title, slug FROM blog_posts WHERE {$listWhere} ORDER BY created_at DESC LIMIT 15";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- [" . nectra_decode_entities($row['title']) . "](" . SITE_URL . "/" . $row['slug'] . ")\n";
    }
} else {
    echo "- Ongoing research in progress.\n";
}
echo "\n";

echo "## Important Guidance\n\n";
echo "- Canonical domain: https://www.nectradigital.com\n";
echo "- All public page paths must use HTTPS without trailing slash.\n";
echo "- Administrative panels (/admin), internal APIs (/api), and staging endpoints are confidential and excluded from indexing.\n";
echo "- Direct inquiries can be initiated at https://www.nectradigital.com/contact or via contact@nectradigital.com.\n";
?>
