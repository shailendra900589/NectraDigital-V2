<?php
/**
 * Site-wide contact constants, NAP block, and email privacy helpers.
 */
if (!defined('NECTRA_PHONE_E164')) {
    define('NECTRA_PHONE_E164', '+917678387759');
}
if (!defined('NECTRA_PHONE_DISPLAY')) {
    define('NECTRA_PHONE_DISPLAY', '+91 76783 87759');
}
if (!defined('NECTRA_ADDRESS_LINE1')) {
    define('NECTRA_ADDRESS_LINE1', 'Lucknow');
}
if (!defined('NECTRA_ADDRESS_REGION')) {
    define('NECTRA_ADDRESS_REGION', 'Uttar Pradesh 226001');
}
if (!defined('NECTRA_ADDRESS_COUNTRY')) {
    define('NECTRA_ADDRESS_COUNTRY', 'India');
}
if (!defined('NECTRA_FACEBOOK_URL')) {
    define('NECTRA_FACEBOOK_URL', 'https://www.facebook.com/nectradigital');
}

/** Schema-only email (not rendered as plain text in HTML). */
function nectra_schema_email(): string
{
    return 'contact@nectradigital.com';
}

/** Link to contact page instead of exposing plain-text email in HTML. */
function nectra_email_html_link(string $class = 'text-neon text-decoration-none'): string
{
    $class = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
    return '<a href="/contact" class="' . $class . '">Contact form &amp; email</a>';
}

function render_nap_block(string $variant = 'footer'): void
{
    $phone = NECTRA_PHONE_DISPLAY;
    $tel = NECTRA_PHONE_E164;
    echo '<div class="nectra-nap-block small text-white-50" itemscope itemtype="https://schema.org/LocalBusiness">';
    echo '<meta itemprop="name" content="Nectra Digital">';
    echo '<p class="mb-2" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">';
    echo '<i class="fas fa-map-marker-alt text-neon me-2" aria-hidden="true"></i>';
    echo '<span itemprop="streetAddress">' . htmlspecialchars(NECTRA_ADDRESS_LINE1) . '</span>, ';
    echo '<span itemprop="addressRegion">' . htmlspecialchars(NECTRA_ADDRESS_REGION) . '</span>, ';
    echo '<span itemprop="addressCountry">' . htmlspecialchars(NECTRA_ADDRESS_COUNTRY) . '</span>';
    echo '</p>';
    echo '<p class="mb-2"><i class="fas fa-phone text-neon me-2" aria-hidden="true"></i>';
    echo '<a href="tel:' . htmlspecialchars($tel) . '" class="text-white-50 text-decoration-none hover-neon" itemprop="telephone">' . htmlspecialchars($phone) . '</a></p>';
    echo '<p class="mb-0"><i class="fas fa-envelope text-neon me-2" aria-hidden="true"></i>' . nectra_email_html_link('text-white-50 text-decoration-none hover-neon') . '</p>';
    echo '</div>';
}

function get_home_local_business_schema(): array
{
    return [
        '@type' => 'LocalBusiness',
        '@id' => SITE_URL . '/#localbusiness',
        'name' => 'Nectra Digital',
        'description' => 'Custom software development company in India building SaaS, web and mobile apps, with AI automation, SEO, and digital marketing for product growth.',
        'url' => SITE_URL . '/',
        'telephone' => NECTRA_PHONE_E164,
        'email' => nectra_schema_email(),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => NECTRA_ADDRESS_LINE1,
            'addressRegion' => 'Uttar Pradesh',
            'postalCode' => '226001',
            'addressCountry' => 'IN',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => '26.8467',
            'longitude' => '80.9462',
        ],
        'sameAs' => [
            NECTRA_FACEBOOK_URL,
            'https://www.linkedin.com/company/nectradigital',
            'https://twitter.com/nectradigital',
            'https://www.instagram.com/nectradigital',
        ],
        'openingHoursSpecification' => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'opens' => '09:00',
            'closes' => '18:00',
        ],
        'priceRange' => '₹₹₹',
        'parentOrganization' => ['@id' => SITE_URL . '/#organization'],
    ];
}
