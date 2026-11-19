<?php
/**
 * High-intent, high-volume search keywords per service (national + city templates).
 * Used for meta tags, landing pages, RSS, and search engine discovery.
 */
function get_intent_keyword_catalog(): array
{
    return [
        'seo-services' => [
            'primary' => [
                'SEO company India', 'SEO services India', 'best SEO company India',
                'SEO agency India', 'search engine optimization company', 'SEO expert India',
                'hire SEO company', 'SEO company near me', 'affordable SEO services India',
            ],
            'commercial' => [
                'SEO packages India', 'SEO pricing India', 'monthly SEO services',
                'enterprise SEO company', 'SEO consultant India', 'top SEO agency India',
            ],
            'local' => [
                'SEO company {city}', 'best SEO company in {city}', 'SEO services {city}',
                'SEO agency {city}', 'local SEO company {city}', 'SEO expert {city}',
                'affordable SEO {city}', 'SEO company near me {city}', 'hire SEO {city}',
            ],
        ],
        'local-seo-services' => [
            'primary' => [
                'local SEO services', 'local SEO company India', 'Google Business Profile optimization',
                'local SEO agency', 'map pack SEO', 'local search marketing', 'GMB optimization services',
            ],
            'commercial' => [
                'local SEO packages', 'local SEO pricing', 'local SEO consultant',
                'Google Maps ranking service', 'local citation building service',
            ],
            'local' => [
                'local SEO {city}', 'local SEO company {city}', 'Google Business Profile {city}',
                'local SEO services {city}', 'map pack SEO {city}', 'GMB optimization {city}',
            ],
        ],
        'technical-seo-services' => [
            'primary' => [
                'technical SEO services', 'technical SEO audit', 'Core Web Vitals optimization',
                'SEO site audit', 'schema markup services', 'JavaScript SEO', 'crawl budget optimization',
            ],
            'commercial' => [
                'technical SEO agency India', 'website SEO audit service', 'SEO audit company',
                'technical SEO consultant', 'enterprise technical SEO',
            ],
            'local' => [
                'technical SEO {city}', 'SEO audit {city}', 'website audit {city}',
                'Core Web Vitals {city}', 'technical SEO company {city}',
            ],
        ],
        'enterprise-seo-services' => [
            'primary' => [
                'enterprise SEO agency', 'enterprise SEO services', 'large scale SEO',
                'programmatic SEO agency', 'SEO for enterprise', 'multi-domain SEO',
            ],
            'commercial' => [
                'enterprise SEO consultant', 'SEO for Fortune 500', 'global SEO agency',
                'dedicated SEO team', 'enterprise SEO pricing',
            ],
            'local' => [
                'enterprise SEO {city}', 'enterprise SEO agency {city}', 'large scale SEO {city}',
            ],
        ],
        'digital-marketing-services' => [
            'primary' => [
                'digital marketing agency India', 'digital marketing company India',
                'best digital marketing agency', 'full service digital marketing',
                'online marketing company India', 'performance marketing agency India',
            ],
            'commercial' => [
                'digital marketing packages', 'digital marketing services pricing',
                'ROI digital marketing agency', 'B2B digital marketing agency',
            ],
            'local' => [
                'digital marketing agency {city}', 'digital marketing company {city}',
                'best digital marketing agency {city}', 'online marketing {city}',
                'performance marketing {city}', 'marketing agency {city}',
            ],
        ],
        'ppc-management' => [
            'primary' => [
                'PPC management services', 'PPC agency India', 'paid search management',
                'performance marketing agency', 'Google Ads management', 'PPC company India',
            ],
            'commercial' => [
                'PPC management pricing', 'hire PPC agency', 'PPC consultant India',
                'ROAS optimization agency', 'paid media agency',
            ],
            'local' => [
                'PPC management {city}', 'PPC agency {city}', 'Google Ads agency {city}',
                'paid ads management {city}', 'performance marketing {city}',
            ],
        ],
        'google-ads-management' => [
            'primary' => [
                'Google Ads agency', 'Google Ads management services', 'Google PPC agency',
                'Google Ads company India', 'Google advertising agency', 'Google Ads expert',
            ],
            'commercial' => [
                'Google Ads management pricing', 'Google Ads consultant', 'Google Ads audit',
                'Google Shopping ads management', 'YouTube ads agency',
            ],
            'local' => [
                'Google Ads agency {city}', 'Google Ads management {city}',
                'Google PPC {city}', 'Google advertising {city}',
            ],
        ],
        'meta-ads-services' => [
            'primary' => [
                'Meta ads agency', 'Facebook ads management', 'Instagram advertising agency',
                'Meta ads services India', 'Facebook marketing agency', 'social media ads agency',
            ],
            'commercial' => [
                'Facebook ads management pricing', 'Meta ads consultant', 'Instagram ads agency',
                'social media advertising services', 'lead generation ads Facebook',
            ],
            'local' => [
                'Meta ads agency {city}', 'Facebook ads {city}', 'Instagram ads {city}',
                'social media marketing {city}', 'Facebook advertising {city}',
            ],
        ],
        'ai-automation-services' => [
            'primary' => [
                'AI automation services', 'AI agency India', 'business process automation',
                'marketing automation agency', 'AI automation company', 'workflow automation services',
            ],
            'commercial' => [
                'AI automation pricing', 'custom AI automation', 'AI integration services',
                'digital transformation company India', 'AI consulting India',
            ],
            'local' => [
                'AI automation {city}', 'AI agency {city}', 'business automation {city}',
                'marketing automation {city}', 'AI company {city}',
            ],
        ],
        'ai-chatbot-development' => [
            'primary' => [
                'AI chatbot development', 'custom chatbot development', 'AI chatbot services',
                'GPT chatbot development', 'conversational AI development', 'website chatbot development',
            ],
            'commercial' => [
                'chatbot development cost India', 'AI chatbot company', 'enterprise chatbot development',
                'customer support chatbot', 'lead generation chatbot',
            ],
            'local' => [
                'chatbot development {city}', 'AI chatbot {city}', 'chatbot company {city}',
            ],
        ],
        'whatsapp-ai-bot-development' => [
            'primary' => [
                'WhatsApp AI bot', 'WhatsApp bot development', 'WhatsApp Business API bot',
                'WhatsApp automation', 'WhatsApp chatbot India', 'WhatsApp marketing automation',
            ],
            'commercial' => [
                'WhatsApp bot development cost', 'WhatsApp Business API setup',
                'WhatsApp sales bot', 'WhatsApp customer support bot',
            ],
            'local' => [
                'WhatsApp bot {city}', 'WhatsApp automation {city}', 'WhatsApp chatbot {city}',
            ],
        ],
        'web-development-services' => [
            'primary' => [
                'website development company', 'web development agency', 'web development company India',
                'website development services', 'best web development company', 'custom website development',
                'professional website development', 'website design and development company',
            ],
            'commercial' => [
                'website development cost India', 'web development agency pricing',
                'hire web developer India', 'corporate website development', 'business website development',
                'WordPress development company', 'React development company', 'Next.js development agency',
            ],
            'local' => [
                'website development company {city}', 'web development agency {city}',
                'web development company {city}', 'website developer {city}',
                'best web development company in {city}', 'website design company {city}',
                'WordPress developer {city}', 'custom website development {city}',
            ],
        ],
        'software-development-services' => [
            'primary' => [
                'software development company', 'custom software development', 'software development India',
                'SaaS development company', 'enterprise software development', 'Python development company',
            ],
            'commercial' => [
                'software development cost India', 'hire software developers', 'offshore software development',
                'API development company', 'Laravel development company', 'custom software solutions',
            ],
            'local' => [
                'software development company {city}', 'custom software {city}',
                'software company {city}', 'SaaS development {city}', 'app development company {city}',
            ],
        ],
        'mobile-app-development' => [
            'primary' => [
                'mobile app development company', 'Android app development', 'iOS app development',
                'React Native development', 'Flutter app development', 'cross platform app development',
            ],
            'commercial' => [
                'mobile app development cost India', 'app development agency',
                'hire app developers India', 'MVP app development', 'enterprise mobile app development',
            ],
            'local' => [
                'mobile app development {city}', 'app development company {city}',
                'Android app developer {city}', 'iOS app developer {city}',
            ],
        ],
        'ecommerce-development' => [
            'primary' => [
                'ecommerce development company', 'online store development', 'Shopify development company',
                'WooCommerce development', 'ecommerce website development', 'ecommerce agency India',
            ],
            'commercial' => [
                'ecommerce development cost', 'Shopify developer India', 'custom ecommerce development',
                'ecommerce website design', 'multi-vendor ecommerce development',
            ],
            'local' => [
                'ecommerce development {city}', 'Shopify developer {city}',
                'online store development {city}', 'ecommerce website {city}',
            ],
        ],
    ];
}

/** City page / homepage high-intent keywords */
function get_global_intent_keywords(): array
{
    return [
        'software development company India', 'custom software development India', 'SaaS development company India',
        'web development company India', 'mobile app development company India', 'hire software developers India',
        'AI automation services India', 'API development company India', 'SEO services India', 'digital marketing agency India',
    ];
}
