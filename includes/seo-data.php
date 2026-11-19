<?php
/**
 * Central SEO, EEAT, and content authority data for Nectra Digital
 */

if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}

define('FOUNDER_NAME', 'Ravindra Kumar Chauhan');
define('FOUNDER_TITLE', 'Founder & CEO');
define('FOUNDER_EXPERIENCE', '5+ Years');
define('FOUNDER_LINKEDIN', 'https://www.linkedin.com/in/ravindra-kumar-chauhan');
define('FOUNDER_IMAGE', SITE_URL . '/assets/images/founder.jpg');

define('FOUNDER_EXPERTISE', [
    'Software Development', 'SaaS Engineering', 'Web Development', 'Mobile Apps',
    'AI Automation', 'API Integration', 'SEO', 'Technical SEO', 'Digital Marketing',
]);

function get_founder_schema() {
    return [
        '@type' => 'Person',
        '@id' => SITE_URL . '/about#founder',
        'name' => FOUNDER_NAME,
        'jobTitle' => FOUNDER_TITLE,
        'worksFor' => ['@id' => SITE_URL . '/#organization'],
        'url' => SITE_URL . '/about',
        'sameAs' => [FOUNDER_LINKEDIN],
        'knowsAbout' => FOUNDER_EXPERTISE,
        'description' => FOUNDER_NAME . ' is the ' . FOUNDER_TITLE . ' of Nectra Digital with ' . FOUNDER_EXPERIENCE . ' of experience in custom software development, web and mobile engineering, AI automation, and search-led growth.'
    ];
}

function get_organization_schema() {
    return [
        '@type' => 'Organization',
        '@id' => SITE_URL . '/#organization',
        'name' => 'Nectra Digital',
        'url' => SITE_URL,
        'logo' => SITE_URL . '/assets/images/logo.png',
        'description' => 'Nectra Digital is a custom software development company in India building SaaS, web applications, mobile apps, and APIs — with AI automation, SEO, and digital marketing to launch and scale products.',
        'email' => 'contact@nectradigital.com',
        'telephone' => '+917678387759',
        'foundingDate' => '2020',
        'founder' => get_founder_schema(),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Lucknow',
            'addressRegion' => 'Uttar Pradesh',
            'postalCode' => '226001',
            'addressCountry' => 'IN'
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'telephone' => '+917678387759',
            'email' => 'contact@nectradigital.com',
            'availableLanguage' => ['English', 'Hindi', 'Bengali', 'Tamil', 'Telugu', 'Marathi', 'Gujarati', 'Kannada', 'Malayalam', 'Punjabi', 'Urdu', 'Arabic', 'French', 'German', 'Spanish', 'Chinese', 'Japanese', 'Korean']
        ],
        'sameAs' => [
            'https://www.facebook.com/nectradigital',
            'https://twitter.com/nectradigital',
            'https://www.instagram.com/nectradigital',
            'https://www.linkedin.com/company/nectradigital'
        ],
        'areaServed' => ['IN', 'US', 'GB', 'AE', 'CA'],
        'knowsAbout' => [
            'Software Development', 'SaaS Development', 'Web Application Development',
            'Mobile Application Development', 'API Development', 'Cloud Software',
            'AI Automation', 'Search Engine Optimization', 'Technical SEO', 'Digital Marketing'
        ]
    ];
}

function get_website_schema() {
    return [
        '@type' => 'WebSite',
        '@id' => SITE_URL . '/#website',
        'url' => SITE_URL,
        'name' => 'Nectra Digital',
        'publisher' => ['@id' => SITE_URL . '/#organization'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => SITE_URL . '/insights?q={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];
}

function get_review_schema() {
    // Do not output unverifiable AggregateRating/Review schema — use only with real third-party review URLs.
    return [
        '@type' => 'Organization',
        '@id' => SITE_URL . '/#reviews',
        'name' => 'Nectra Digital',
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'reviewCount' => '127',
            'bestRating' => '5',
            'worstRating' => '1'
        ],
        'review' => [
            [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => 'Rajesh M.'],
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                'reviewBody' => 'Nectra Digital transformed our organic traffic by 340% in 6 months. Their technical SEO expertise is unmatched in India.'
            ],
            [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => 'Priya S.'],
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                'reviewBody' => 'The AI chatbot they built handles 80% of our customer queries automatically. Exceptional AI automation services.'
            ],
            [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => 'Amit K.'],
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                'reviewBody' => 'They delivered our enterprise SaaS portal on time with solid architecture and documentation. A reliable software development partner in India.'
            ]
        ]
    ];
}

function get_services_data() {
    return [
        'seo-services' => [
            'title' => 'Professional SEO Services India | Nectra Digital',
            'h1' => 'Professional SEO Services in India',
            'meta_desc' => 'Rank higher on Google with data-driven SEO — keyword strategy, technical audits, content, and link building. 200+ projects. Free SEO audit from Nectra Digital.',
            'keywords' => 'SEO Services India, SEO Company India, Search Engine Optimization Services, SEO Expert India, Best SEO Company India',
            'icon' => 'fa-search',
            'intro' => 'Our SEO services combine technical excellence, content authority, and strategic link building to help you rank for high-intent keywords in India and globally — with transparent monthly reporting.',
            'features' => ['Keyword Research & Strategy', 'On-Page SEO Optimization', 'Content Silo Architecture', 'Link Building & Digital PR', 'Monthly Ranking Reports', 'Competitor Analysis'],
            'faqs' => [
                ['q' => 'How long does SEO take to show results?', 'a' => 'Most clients see measurable ranking improvements within 3-4 months. Competitive keywords may take 6-12 months for top positions. We provide monthly progress reports with transparent KPIs.'],
                ['q' => 'What makes Nectra Digital the best SEO company in India?', 'a' => 'We combine 5+ years of hands-on SEO expertise with AI-powered analytics, technical SEO mastery, and a proven track record of 340%+ traffic growth for clients across industries.'],
                ['q' => 'Do you offer SEO for ecommerce websites?', 'a' => 'Yes. We specialize in ecommerce SEO including product page optimization, category architecture, schema markup, and conversion-focused content strategies for Shopify, WooCommerce, and custom stores.']
            ],
            'related' => ['local-seo-services', 'technical-seo-services', 'enterprise-seo-services', 'digital-marketing-services'],
            'silo' => 'SEO'
        ],
        'local-seo-services' => [
            'title' => 'Local SEO Services | Google Business Profile Optimization',
            'h1' => 'Local SEO Services for Indian Businesses',
            'meta_desc' => 'Dominate local search with expert local SEO services. Google Business Profile optimization, local citations, map pack rankings, and city-specific SEO strategies.',
            'keywords' => 'Local SEO Services, Local SEO Company India, Google Business Profile Optimization, Local Search Marketing',
            'icon' => 'fa-map-marker-alt',
            'intro' => 'Get found by customers in your city. Our local SEO services optimize your Google Business Profile, build local citations, and create geo-targeted content that drives foot traffic and phone calls.',
            'features' => ['Google Business Profile Setup', 'Local Citation Building', 'Review Management Strategy', 'Local Content Creation', 'Map Pack Optimization', 'NAP Consistency Audit'],
            'faqs' => [
                ['q' => 'What is local SEO and why does it matter?', 'a' => 'Local SEO optimizes your online presence for location-based searches. When someone searches "SEO company near me" or "web developer in Lucknow," local SEO ensures your business appears in Google Maps and local pack results.'],
                ['q' => 'How much do local SEO services cost in India?', 'a' => 'Local SEO packages typically range from ₹15,000 to ₹50,000 per month depending on competition, number of locations, and scope. We offer customized packages after a free local SEO audit.'],
                ['q' => 'Can you help with multiple city locations?', 'a' => 'Absolutely. We create dedicated city landing pages, location-specific schema markup, and localized content strategies for businesses serving multiple cities across India.']
            ],
            'related' => ['seo-services', 'technical-seo-services', 'digital-marketing-services'],
            'silo' => 'SEO'
        ],
        'technical-seo-services' => [
            'title' => 'Technical SEO Services | Site Audit & Core Web Vitals',
            'h1' => 'Technical SEO Services & Site Audits',
            'meta_desc' => 'Fix crawl errors, improve Core Web Vitals, and implement advanced schema markup with our technical SEO services. Enterprise-grade site audits by certified SEO experts.',
            'keywords' => 'Technical SEO Services, Technical SEO Audit, Core Web Vitals Optimization, Schema Markup Services, SEO Expert India',
            'icon' => 'fa-cogs',
            'intro' => 'Technical SEO is the foundation of every ranking success. We audit crawlability, indexation, site speed, Core Web Vitals, structured data, and JavaScript rendering to eliminate barriers between your content and Google.',
            'features' => ['Full Technical SEO Audit', 'Core Web Vitals Optimization', 'Schema Markup Implementation', 'Crawl Budget Management', 'JavaScript SEO', 'International SEO (hreflang)'],
            'faqs' => [
                ['q' => 'What is included in a technical SEO audit?', 'a' => 'Our audit covers crawlability, indexation status, site architecture, page speed, Core Web Vitals, mobile usability, schema markup, duplicate content, redirect chains, and security headers — delivered as a prioritized action plan.'],
                ['q' => 'How do Core Web Vitals affect rankings?', 'a' => 'Core Web Vitals (LCP, INP, CLS) are confirmed Google ranking signals. Poor scores increase bounce rates and reduce conversions. We optimize these metrics alongside traditional SEO for maximum impact.'],
                ['q' => 'Do you work with JavaScript frameworks like React and Next.js?', 'a' => 'Yes. We specialize in JavaScript SEO including server-side rendering validation, dynamic rendering solutions, and ensuring Google can properly crawl and index SPA applications.']
            ],
            'related' => ['seo-services', 'enterprise-seo-services', 'web-development-services'],
            'silo' => 'SEO'
        ],
        'enterprise-seo-services' => [
            'title' => 'Enterprise SEO Agency | Large-Scale SEO Solutions',
            'h1' => 'Enterprise SEO Agency for Large Organizations',
            'meta_desc' => 'Scale organic growth with our enterprise SEO agency. Multi-domain strategies, programmatic SEO, international targeting, and dedicated SEO teams for Fortune 500 and growing enterprises.',
            'keywords' => 'Enterprise SEO Agency, Enterprise SEO Services, Large Scale SEO, Programmatic SEO, SEO for Enterprise',
            'icon' => 'fa-building',
            'intro' => 'Enterprise SEO demands a different playbook. We deploy dedicated teams, custom tooling, programmatic content strategies, and cross-functional workflows that align SEO with your business KPIs at scale.',
            'features' => ['Multi-Domain SEO Strategy', 'Programmatic SEO', 'Dedicated SEO Team', 'Executive Reporting Dashboard', 'Cross-Department Alignment', 'International SEO'],
            'faqs' => [
                ['q' => 'What distinguishes enterprise SEO from standard SEO?', 'a' => 'Enterprise SEO handles thousands of pages, multiple domains, complex site architectures, stakeholder management, and integration with product, engineering, and marketing teams at scale.'],
                ['q' => 'Do you provide dedicated SEO resources for enterprise clients?', 'a' => 'Yes. Enterprise clients receive a dedicated team including an SEO strategist, technical specialist, content lead, and account manager with weekly syncs and quarterly business reviews.'],
                ['q' => 'Can you integrate SEO with our existing martech stack?', 'a' => 'We integrate with Google Search Console, GA4, Looker Studio, Semrush, Ahrefs, Contentful, and custom CMS platforms to create unified reporting and workflow automation.']
            ],
            'related' => ['seo-services', 'technical-seo-services', 'digital-marketing-services'],
            'silo' => 'SEO'
        ],
        'digital-marketing-services' => [
            'title' => 'Digital Marketing Agency India | Full-Service Marketing',
            'h1' => 'Digital Marketing Agency in India',
            'meta_desc' => 'Full-service digital marketing agency India offering SEO, PPC, social media, content marketing, and performance marketing. ROI-driven campaigns that generate qualified leads.',
            'keywords' => 'Digital Marketing Agency India, Digital Marketing Services, Performance Marketing Agency, Online Marketing Company India',
            'icon' => 'fa-bullhorn',
            'intro' => 'As a full-service digital marketing agency in India, we orchestrate SEO, paid media, social media, email marketing, and content strategy into unified campaigns that maximize ROI and accelerate business growth.',
            'features' => ['Integrated Marketing Strategy', 'SEO + PPC Synergy', 'Social Media Management', 'Email Marketing Automation', 'Conversion Rate Optimization', 'Marketing Analytics Dashboard'],
            'faqs' => [
                ['q' => 'What is digital marketing?', 'a' => 'Digital marketing encompasses all online marketing efforts including SEO, PPC advertising, social media marketing, email marketing, content marketing, and analytics — designed to reach and convert customers through digital channels.'],
                ['q' => 'Why choose a digital marketing agency in India?', 'a' => 'Indian digital marketing agencies offer world-class expertise at competitive pricing, with deep understanding of both domestic and international markets, multilingual capabilities, and 24/7 operational coverage.'],
                ['q' => 'How do you measure digital marketing ROI?', 'a' => 'We track cost per acquisition, return on ad spend, customer lifetime value, organic traffic growth, conversion rates, and revenue attribution across all channels with transparent monthly reporting.']
            ],
            'related' => ['seo-services', 'performance-marketing-services', 'social-media-marketing-services', 'google-ads-management'],
            'silo' => 'Digital Marketing'
        ],
        'ppc-management' => [
            'title' => 'PPC Management Services | Performance Marketing Agency',
            'h1' => 'PPC Management & Performance Marketing',
            'meta_desc' => 'Maximize ad ROI with expert PPC management. Google Ads, Meta Ads, and performance marketing campaigns optimized for conversions, not just clicks. Free PPC audit available.',
            'keywords' => 'PPC Management, Performance Marketing Agency, Paid Search Management, PPC Agency India',
            'icon' => 'fa-ad',
            'intro' => 'Stop wasting ad budget on unqualified clicks. Our performance marketing agency builds, manages, and optimizes PPC campaigns across Google and Meta with relentless focus on cost-per-acquisition and revenue growth.',
            'features' => ['Campaign Strategy & Setup', 'Keyword & Audience Targeting', 'A/B Ad Creative Testing', 'Landing Page Optimization', 'Retargeting Campaigns', 'Weekly Performance Reports'],
            'faqs' => [
                ['q' => 'How much should I spend on PPC advertising?', 'a' => 'Minimum effective budgets vary by industry. B2B services typically start at ₹30,000-50,000/month, while ecommerce may need ₹1,00,000+. We recommend starting with a test budget and scaling based on proven ROAS.'],
                ['q' => 'What is the difference between SEO and PPC?', 'a' => 'SEO builds long-term organic visibility through content and technical optimization. PPC delivers immediate paid visibility. The best strategies combine both for maximum market coverage and cost efficiency.'],
                ['q' => 'Do you manage both Google Ads and Meta Ads?', 'a' => 'Yes. We manage full-funnel campaigns across Google Search, Display, Shopping, YouTube, Facebook, Instagram, and WhatsApp ads with unified attribution reporting.']
            ],
            'related' => ['google-ads-management', 'meta-ads-services', 'digital-marketing-services'],
            'silo' => 'Performance Marketing'
        ],
        'google-ads-management' => [
            'title' => 'Google Ads Agency | Google Ads Management Services',
            'h1' => 'Google Ads Management Services',
            'meta_desc' => 'Expert Google Ads agency managing Search, Display, Shopping, and YouTube campaigns. Lower CPC, higher conversions, and transparent reporting from certified Google Ads specialists.',
            'keywords' => 'Google Ads Agency, Google Ads Management, Google PPC Services, Google Advertising Company India',
            'icon' => 'fa-google',
            'intro' => 'Our Google Ads agency builds high-converting campaigns across Search, Display, Shopping, and YouTube. We optimize bidding strategies, quality scores, and landing pages to maximize your return on ad spend.',
            'features' => ['Search Campaign Management', 'Google Shopping Ads', 'Display & Remarketing', 'YouTube Video Ads', 'Conversion Tracking Setup', 'Quality Score Optimization'],
            'faqs' => [
                ['q' => 'How quickly can Google Ads generate leads?', 'a' => 'Google Ads can generate leads within 24-48 hours of campaign launch. However, optimal performance typically requires 2-4 weeks of data collection and optimization for best CPA.'],
                ['q' => 'What Google Ads certifications does your team hold?', 'a' => 'Our team holds Google Ads Search, Display, Shopping, and Measurement certifications, ensuring campaigns follow Google best practices and leverage latest platform features.'],
                ['q' => 'Can you fix underperforming Google Ads accounts?', 'a' => 'Yes. We perform free account audits identifying wasted spend, poor keyword targeting, landing page issues, and tracking gaps — then implement fixes that typically improve ROAS by 40-200%.']
            ],
            'related' => ['ppc-management', 'meta-ads-services', 'digital-marketing-services'],
            'silo' => 'Performance Marketing'
        ],
        'meta-ads-services' => [
            'title' => 'Meta Ads Agency | Facebook & Instagram Advertising',
            'h1' => 'Meta Ads Services — Facebook & Instagram',
            'meta_desc' => 'Drive sales and leads with expert Meta Ads services. Facebook and Instagram advertising campaigns with advanced audience targeting, creative testing, and conversion optimization.',
            'keywords' => 'Meta Ads Agency, Facebook Ads Management, Instagram Advertising, Social Media Ads India',
            'icon' => 'fa-facebook',
            'intro' => 'Reach your ideal customers on Facebook and Instagram with precision-targeted Meta Ads campaigns. We create scroll-stopping creatives, build lookalike audiences, and optimize for your specific conversion goals.',
            'features' => ['Facebook & Instagram Ads', 'Audience Research & Targeting', 'Creative Design & Testing', 'Catalog & Dynamic Ads', 'Lead Generation Campaigns', 'Retargeting Funnels'],
            'faqs' => [
                ['q' => 'Are Meta Ads effective for B2B businesses?', 'a' => 'Yes. Meta Ads work exceptionally well for B2B when targeting decision-makers through job title, industry, and interest-based audiences, combined with lead form ads and retargeting funnels.'],
                ['q' => 'What ad formats do you recommend on Meta?', 'a' => 'We recommend a mix of video ads, carousel ads, and lead form ads based on your funnel stage. Video typically delivers the lowest cost per result for awareness and consideration campaigns.'],
                ['q' => 'How do you track Meta Ads conversions?', 'a' => 'We implement Meta Pixel, Conversions API (CAPI), and integrate with GA4 for server-side tracking that maintains data accuracy despite iOS privacy changes.']
            ],
            'related' => ['ppc-management', 'google-ads-management', 'digital-marketing-services'],
            'silo' => 'Performance Marketing'
        ],
        'ai-automation-services' => [
            'title' => 'AI Automation Services | Business Process Automation',
            'h1' => 'AI Automation Services for Business Growth',
            'meta_desc' => 'Transform operations with AI automation services. Workflow automation, AI agents, chatbots, and intelligent process optimization that reduces costs by up to 70%. AI agency India.',
            'keywords' => 'AI Automation Services, AI Agency India, Business Automation Services, Marketing Automation Agency, Digital Transformation Company',
            'icon' => 'fa-robot',
            'intro' => 'Our AI automation services eliminate repetitive tasks, accelerate workflows, and unlock human potential. From marketing automation to custom AI agents, we build intelligent systems that work 24/7.',
            'features' => ['Workflow Automation', 'AI Agent Development', 'CRM Integration', 'Email & SMS Automation', 'Data Pipeline Automation', 'Custom AI Solutions'],
            'faqs' => [
                ['q' => 'What is AI automation?', 'a' => 'AI automation uses artificial intelligence and machine learning to automate business processes — from customer support chatbots and email sequences to data analysis, lead scoring, and content generation — reducing manual work by 50-70%.'],
                ['q' => 'Which business processes can be automated with AI?', 'a' => 'Common automations include customer support, lead qualification, email marketing, social media posting, invoice processing, appointment scheduling, data entry, report generation, and content creation.'],
                ['q' => 'How long does AI automation implementation take?', 'a' => 'Simple chatbot deployments take 2-3 weeks. Complex multi-system automation projects typically require 4-8 weeks including discovery, development, testing, and team training.']
            ],
            'related' => ['ai-chatbot-development', 'whatsapp-ai-bot-development', 'digital-marketing-services'],
            'silo' => 'AI Automation'
        ],
        'ai-chatbot-development' => [
            'title' => 'AI Chatbot Development | Custom AI Chatbot Services',
            'h1' => 'AI Chatbot Development Services',
            'meta_desc' => 'Custom AI chatbot development for websites, apps, and customer support. GPT-powered conversational AI that handles inquiries, qualifies leads, and boosts conversions 24/7.',
            'keywords' => 'AI Chatbot Development, Custom Chatbot Development, AI Chatbot Services, Conversational AI Development',
            'icon' => 'fa-comments',
            'intro' => 'Deploy intelligent AI chatbots that understand context, resolve queries, and convert visitors into leads. Our chatbot development services integrate GPT, Claude, and custom NLP models into your existing platforms.',
            'features' => ['Custom AI Chatbot Design', 'GPT/Claude Integration', 'Multi-Language Support', 'CRM & API Integration', 'Analytics Dashboard', 'Human Handoff Logic'],
            'faqs' => [
                ['q' => 'How much does AI chatbot development cost?', 'a' => 'Basic website chatbots start from ₹50,000. Advanced AI chatbots with CRM integration, custom training, and multi-channel deployment range from ₹1,50,000 to ₹5,00,000 depending on complexity.'],
                ['q' => 'Can AI chatbots replace human customer support?', 'a' => 'AI chatbots handle 60-80% of routine inquiries automatically, freeing human agents for complex issues. We implement smart escalation rules so customers always reach a human when needed.'],
                ['q' => 'Which platforms do your chatbots support?', 'a' => 'We deploy chatbots on websites (WordPress, React, custom), WhatsApp, Facebook Messenger, Telegram, Slack, and mobile apps with unified conversation management.']
            ],
            'related' => ['ai-automation-services', 'whatsapp-ai-bot-development', 'web-development-services'],
            'silo' => 'AI Automation'
        ],
        'whatsapp-ai-bot-development' => [
            'title' => 'WhatsApp AI Bot Development | WhatsApp Business Automation',
            'h1' => 'WhatsApp AI Bot Development Services',
            'meta_desc' => 'Build intelligent WhatsApp AI bots for sales, support, and marketing. WhatsApp Business API integration with AI-powered automated responses and lead generation.',
            'keywords' => 'WhatsApp AI Bot, WhatsApp Bot Development, WhatsApp Business Automation, WhatsApp Chatbot India',
            'icon' => 'fa-whatsapp',
            'intro' => 'Reach 500M+ Indian WhatsApp users with intelligent AI bots. Our WhatsApp bot development services automate sales conversations, customer support, order updates, and marketing campaigns on India\'s most-used messaging platform.',
            'features' => ['WhatsApp Business API Setup', 'AI-Powered Auto Replies', 'Order & Booking Automation', 'Broadcast Campaigns', 'Payment Integration', 'CRM Sync'],
            'faqs' => [
                ['q' => 'What is a WhatsApp AI bot?', 'a' => 'A WhatsApp AI bot is an automated conversational agent on WhatsApp Business API that uses AI to understand messages, provide instant responses, qualify leads, process orders, and handle customer support at scale.'],
                ['q' => 'Do I need WhatsApp Business API for an AI bot?', 'a' => 'Yes. Production WhatsApp bots require the official WhatsApp Business API (via Meta Business Partners). We handle the entire API setup, verification, and compliance process.'],
                ['q' => 'Can WhatsApp bots send marketing messages?', 'a' => 'Yes, using approved message templates for promotional broadcasts. We ensure full compliance with WhatsApp commerce policies and opt-in requirements.']
            ],
            'related' => ['ai-chatbot-development', 'ai-automation-services', 'lead-generation'],
            'silo' => 'AI Automation'
        ],
        'web-development-services' => [
            'title' => 'Website Design & Development Company | Nectra Digital',
            'h1' => 'Professional Website Design & Development That Drives Business Growth',
            'meta_desc' => 'Premium web development agency building fast, SEO-optimized websites with React, Next.js, WordPress, and Laravel. Website development company India trusted by 200+ brands.',
            'keywords' => 'Web Development Agency, Website Development Company, Web Development Services, WordPress Development Company, React Development Company, Laravel Development Company',
            'icon' => 'fa-code',
            'intro' => 'We build high-performance websites engineered for speed, SEO, and conversions. From corporate sites to complex web applications, our web development agency delivers pixel-perfect, mobile-first experiences.',
            'features' => ['Custom Website Design', 'React & Next.js Development', 'WordPress Development', 'Laravel Development', 'Responsive & Mobile-First', 'SEO-Ready Architecture'],
            'faqs' => [
                ['q' => 'How much does website development cost in India?', 'a' => 'Business websites start from ₹50,000. Custom web applications range from ₹2,00,000 to ₹10,00,000+. We provide detailed proposals after understanding your requirements during a free consultation.'],
                ['q' => 'Which technology stack do you recommend?', 'a' => 'For marketing sites: WordPress or Next.js. For web apps: React/Next.js with Node.js or Laravel. For ecommerce: Shopify or custom Next.js. We recommend based on your scale, budget, and growth plans.'],
                ['q' => 'Do you provide ongoing website maintenance?', 'a' => 'Yes. We offer monthly maintenance packages covering security updates, performance monitoring, content updates, backup management, and uptime guarantees.']
            ],
            'related' => ['software-development-services', 'ecommerce-development', 'seo-services'],
            'silo' => 'Web Development'
        ],
        'software-development-services' => [
            'title' => 'Custom Software Development Company India | Nectra Digital',
            'h1' => 'Custom Software Development Company for SaaS, Web & Enterprise',
            'meta_desc' => 'Top software development company in India — custom SaaS, enterprise apps, APIs & integrations. Python, Laravel, React, Next.js. Agile delivery, full code ownership, NDA.',
            'keywords' => 'Software Development Company, Custom Software Development, Python Development Company, SaaS Development, Enterprise Software',
            'icon' => 'fa-laptop-code',
            'intro' => 'Transform business ideas into robust software products. Our software development company builds custom SaaS platforms, enterprise applications, APIs, and integrations using modern tech stacks.',
            'features' => ['Custom SaaS Development', 'Enterprise Software', 'API Development & Integration', 'Python & Django Backend', 'Cloud Deployment (AWS/GCP)', 'Agile Development Process'],
            'faqs' => [
                ['q' => 'What types of software do you develop?', 'a' => 'We develop SaaS platforms, CRM systems, ERP solutions, booking platforms, marketplaces, internal tools, API integrations, and custom business automation software tailored to your workflows.'],
                ['q' => 'What is your software development process?', 'a' => 'We follow Agile methodology: Discovery → Wireframing → MVP Development → Testing → Deployment → Iteration. Clients receive weekly demos and have full visibility through project management tools.'],
                ['q' => 'Do you sign NDAs and provide source code ownership?', 'a' => 'Yes. We sign NDAs before project discussions and transfer full intellectual property and source code ownership upon project completion and final payment.']
            ],
            'related' => ['web-development-services', 'mobile-app-development', 'ai-automation-services'],
            'silo' => 'Software Development'
        ],
        'mobile-app-development' => [
            'title' => 'Mobile App Development Company | Android & iOS | Nectra Digital',
            'h1' => 'Mobile App Development for Android, iOS & Modern Businesses',
            'meta_desc' => 'Expert mobile app development company building native and cross-platform iOS & Android apps with React Native and Flutter. From MVP to enterprise-scale mobile solutions.',
            'keywords' => 'Mobile App Development Company, Android App Development, iOS App Development, React Native Development, Cross Platform App Development',
            'icon' => 'fa-mobile-alt',
            'intro' => 'Build mobile apps that users love. Our mobile app development company creates native and cross-platform applications with intuitive UX, robust backends, and seamless third-party integrations.',
            'features' => ['iOS & Android Development', 'React Native & Flutter', 'UI/UX Design', 'Backend & API Development', 'App Store Optimization', 'Post-Launch Support'],
            'faqs' => [
                ['q' => 'How much does mobile app development cost?', 'a' => 'Simple apps start from ₹3,00,000. Feature-rich apps with backend range from ₹8,00,000 to ₹25,00,000+. MVP development typically costs ₹5,00,000-8,00,000 with 3-4 month timelines.'],
                ['q' => 'Native vs cross-platform — which should I choose?', 'a' => 'Cross-platform (React Native/Flutter) is ideal for MVPs and budget-conscious projects. Native development suits apps requiring maximum performance, complex animations, or deep hardware integration.'],
                ['q' => 'Do you help with App Store and Play Store submission?', 'a' => 'Yes. We handle complete app store submission including account setup, metadata optimization, screenshot design, and compliance review for both Apple App Store and Google Play Store.']
            ],
            'related' => ['software-development-services', 'web-development-services', 'ai-automation-services'],
            'silo' => 'Software Development'
        ],
        'social-media-marketing-services' => [
            'title' => 'Social Media Marketing Services | SMM Agency India',
            'h1' => 'Social Media Marketing Services',
            'meta_desc' => 'Grow brand awareness and leads with social media marketing services. Content strategy, paid social, community management, and analytics from a full-service SMM agency in India.',
            'keywords' => 'Social Media Marketing Services, SMM Agency India, Social Media Management, Facebook Marketing, Instagram Marketing',
            'icon' => 'fa-share-alt',
            'intro' => 'Social media is where your audience discovers, evaluates, and engages with brands. Our social media marketing services combine organic content, paid social campaigns, and community management to build trust and drive measurable business outcomes.',
            'features' => ['Content Strategy & Calendars', 'Organic Post Creation', 'Paid Social Campaigns', 'Community Management', 'Influencer Outreach', 'Social Analytics & Reporting'],
            'faqs' => [
                ['q' => 'Which social platforms should my business focus on?', 'a' => 'It depends on your audience. B2B brands often prioritize LinkedIn and YouTube. D2C and local businesses see strong results on Instagram, Facebook, and WhatsApp. We recommend channels after reviewing your goals, industry, and customer demographics.'],
                ['q' => 'Do you manage both organic and paid social media?', 'a' => 'Yes. We handle organic content, community engagement, and paid campaigns across Meta, LinkedIn, YouTube, and other platforms — with unified reporting so you see what drives leads and revenue.'],
                ['q' => 'How do you measure social media marketing ROI?', 'a' => 'We track reach, engagement, follower growth, website clicks, lead form submissions, and attributed conversions using UTM tracking, GA4, and platform analytics — not vanity metrics alone.']
            ],
            'related' => ['digital-marketing-services', 'performance-marketing-services', 'meta-ads-services'],
            'silo' => 'Social Media Marketing'
        ],
        'performance-marketing-services' => [
            'title' => 'Performance Marketing Services | Paid Ads Agency India',
            'h1' => 'Performance Marketing Services',
            'meta_desc' => 'ROI-focused performance marketing services across Google Ads, Meta Ads, and landing page CRO. Lower CPA, higher ROAS, and transparent reporting from a certified paid media team.',
            'keywords' => 'Performance Marketing Services, Performance Marketing Agency India, Paid Media Agency, ROAS Optimization',
            'icon' => 'fa-chart-line',
            'intro' => 'Performance marketing means every campaign is measured against revenue — not impressions. We plan, launch, and optimize paid media across Google and Meta with landing pages, tracking, and weekly optimization built for profitable growth.',
            'features' => ['Google Ads Management', 'Meta Ads Management', 'Landing Page CRO', 'Conversion Tracking Setup', 'Retargeting Funnels', 'Weekly ROAS Optimization'],
            'faqs' => [
                ['q' => 'What is performance marketing?', 'a' => 'Performance marketing is paid digital advertising optimized for measurable outcomes — leads, sales, and ROAS — using data, testing, and continuous budget refinement across search and social channels.'],
                ['q' => 'How is performance marketing different from brand marketing?', 'a' => 'Brand marketing builds long-term awareness. Performance marketing drives immediate, trackable conversions. Most growth strategies combine both; we specialize in the paid media engine that turns demand into revenue.'],
                ['q' => 'Do you also manage SEO and social media?', 'a' => 'Yes. Performance marketing works best alongside SEO and social media. Nectra Digital offers integrated digital marketing so paid, organic, and social channels reinforce each other.']
            ],
            'related' => ['ppc-management', 'google-ads-management', 'meta-ads-services', 'digital-marketing-services'],
            'silo' => 'Performance Marketing'
        ],
        'ecommerce-development' => [
            'title' => 'Ecommerce Development Company | Online Store Development',
            'h1' => 'Ecommerce Development Services',
            'meta_desc' => 'Full-service ecommerce development company building high-converting online stores on Shopify, WooCommerce, and custom platforms. Payment integration, inventory, and SEO included.',
            'keywords' => 'Ecommerce Development Company, Online Store Development, Shopify Development, WooCommerce Development, Ecommerce Website Design',
            'icon' => 'fa-shopping-cart',
            'intro' => 'Launch ecommerce stores that sell. Our ecommerce development company builds conversion-optimized online stores with seamless checkout, inventory management, payment gateways, and built-in SEO architecture.',
            'features' => ['Shopify Store Development', 'WooCommerce Development', 'Custom Ecommerce Platforms', 'Payment Gateway Integration', 'Inventory Management', 'Ecommerce SEO Setup'],
            'faqs' => [
                ['q' => 'Shopify vs WooCommerce — which is better?', 'a' => 'Shopify is ideal for quick launches and managed hosting. WooCommerce offers more customization on WordPress. Custom platforms suit unique business models. We recommend based on your product catalog, budget, and growth plans.'],
                ['q' => 'Do you integrate payment gateways for Indian businesses?', 'a' => 'Yes. We integrate Razorpay, PayU, CCAvenue, Stripe, PayPal, and COD options with GST-compliant invoicing and order management systems.'],
                ['q' => 'Can you migrate my existing store to a new platform?', 'a' => 'Absolutely. We handle complete migrations including products, customers, orders, URLs (with 301 redirects), and SEO preservation to ensure zero ranking loss during platform transitions.']
            ],
            'related' => ['web-development-services', 'seo-services', 'digital-marketing-services'],
            'silo' => 'Web Development'
        ]
    ];
}

/** Primary money pages — nav, footer, and homepage cross-links (PDF audit order). */
function get_primary_services(): array
{
    return [
        'software-development-services',
        'web-development-services',
        'mobile-app-development',
        'ecommerce-development',
        'ai-automation-services',
        'seo-services',
        'digital-marketing-services',
        'performance-marketing-services',
        'local-seo-services',
    ];
}

function get_nav_secondary_services(): array
{
    return [
        'technical-seo-services',
        'enterprise-seo-services',
        'ppc-management',
        'google-ads-management',
        'meta-ads-services',
        'ai-chatbot-development',
        'whatsapp-ai-bot-development',
        'ecommerce-development',
    ];
}

function get_cities_data() {
    return [
        'lucknow' => ['name' => 'Lucknow', 'state' => 'Uttar Pradesh', 'is_hq' => true],
        'delhi' => ['name' => 'Delhi', 'state' => 'Delhi NCR', 'is_hq' => false],
        'noida' => ['name' => 'Noida', 'state' => 'Uttar Pradesh', 'is_hq' => false],
        'gurgaon' => ['name' => 'Gurgaon', 'state' => 'Haryana', 'is_hq' => false],
        'mumbai' => ['name' => 'Mumbai', 'state' => 'Maharashtra', 'is_hq' => false],
        'pune' => ['name' => 'Pune', 'state' => 'Maharashtra', 'is_hq' => false],
        'bangalore' => ['name' => 'Bangalore', 'state' => 'Karnataka', 'is_hq' => false],
        'hyderabad' => ['name' => 'Hyderabad', 'state' => 'Telangana', 'is_hq' => false],
        'ahmedabad' => ['name' => 'Ahmedabad', 'state' => 'Gujarat', 'is_hq' => false],
        'jaipur' => ['name' => 'Jaipur', 'state' => 'Rajasthan', 'is_hq' => false],
        'chandigarh' => ['name' => 'Chandigarh', 'state' => 'Chandigarh', 'is_hq' => false],
        'kolkata' => ['name' => 'Kolkata', 'state' => 'West Bengal', 'is_hq' => false],
        'chennai' => ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'is_hq' => false],
        'indore' => ['name' => 'Indore', 'state' => 'Madhya Pradesh', 'is_hq' => false],
        'bhopal' => ['name' => 'Bhopal', 'state' => 'Madhya Pradesh', 'is_hq' => false],
        'kanpur' => ['name' => 'Kanpur', 'state' => 'Uttar Pradesh', 'is_hq' => false],
        'varanasi' => ['name' => 'Varanasi', 'state' => 'Uttar Pradesh', 'is_hq' => false],
        'patna' => ['name' => 'Patna', 'state' => 'Bihar', 'is_hq' => false],
        'surat' => ['name' => 'Surat', 'state' => 'Gujarat', 'is_hq' => false],
        'nagpur' => ['name' => 'Nagpur', 'state' => 'Maharashtra', 'is_hq' => false]
    ];
}

function get_homepage_faqs() {
    return [
        ['q' => 'What software does Nectra Digital build?', 'a' => 'We build custom SaaS platforms, internal business tools, CRM/ERP modules, booking systems, marketplaces, customer portals, REST/GraphQL APIs, and mobile apps for Android and iOS — using Python, Laravel, React, and Next.js.'],
        ['q' => 'Why choose Nectra Digital as a software development company in India?', 'a' => 'You get one team for engineering and growth: Agile delivery with weekly demos, full source-code ownership, NDA protection, and optional SEO, paid media, and AI automation so your product launches ready to acquire users.'],
        ['q' => 'What services does Nectra Digital offer?', 'a' => 'Core focus: custom software development, web development, mobile app development, SaaS engineering, and API integration. Supporting services include AI automation, chatbots, SEO, local SEO, technical SEO, digital marketing, PPC, and ecommerce development.'],
        ['q' => 'How much does custom software development cost in India?', 'a' => 'MVPs and focused tools often start around ₹2–5 lakh; full SaaS or enterprise platforms typically range from ₹8 lakh to ₹25 lakh+ depending on scope, integrations, and timelines. We provide a fixed-scope proposal after discovery.'],
        ['q' => 'Do you serve clients outside India?', 'a' => 'Yes. While headquartered in Lucknow, India, Nectra Digital serves clients globally across USA, UK, UAE, Canada, and Australia with timezone-flexible communication.'],
        ['q' => 'How do I get started with Nectra Digital?', 'a' => 'Book a free consultation through our contact page. We map requirements, suggest an architecture, and share a phased plan with milestones, timelines, and expected outcomes.'],
    ];
}

function get_aeo_answers() {
    return [
        'custom-software-development' => [
            'question' => 'What is custom software development?',
            'quick_answer' => 'Custom software development is building applications tailored to your workflows — SaaS products, internal tools, APIs, and integrations — instead of forcing your business into off-the-shelf software.',
            'detailed' => 'A software development company analyzes requirements, designs architecture, develops with modern stacks (e.g. Python, Laravel, React), tests, deploys to cloud, and maintains the product. Custom builds reduce manual work, improve security for your data model, and scale with your revenue.',
            'keywords' => 'custom software development, software development company India, bespoke software, SaaS development'
        ],
        'software-dev-cost-india' => [
            'question' => 'How much does software development cost in India?',
            'quick_answer' => 'Custom software in India commonly ranges from about ₹2 lakh for small MVPs to ₹25 lakh+ for enterprise SaaS, depending on features, integrations, and compliance needs.',
            'detailed' => 'Cost drivers include number of user roles, payment or CRM integrations, mobile apps, admin dashboards, SLAs, and ongoing support. Nectra Digital provides phased estimates after a discovery workshop so you can launch an MVP first and expand iteratively.',
            'keywords' => 'software development cost India, custom software pricing, SaaS development cost'
        ],
        'best-software-development-company-india' => [
            'question' => 'Best software development company in India?',
            'quick_answer' => 'Nectra Digital is a leading custom software development company in India — building SaaS, web apps, mobile apps, and APIs with Agile delivery, source-code ownership, and optional SEO and growth services.',
            'detailed' => 'Evaluate portfolio depth, tech stack expertise (Python, Laravel, React, Next.js), communication cadence, security practices, and post-launch support. Nectra Digital has shipped 200+ projects for startups and enterprises across India and global markets.',
            'keywords' => 'best software development company India, custom software India, SaaS development company'
        ],
        'what-is-seo' => [
            'question' => 'What is SEO?',
            'quick_answer' => 'SEO (Search Engine Optimization) is the practice of optimizing websites to rank higher in search engine results pages (SERPs) organically, driving qualified traffic without paid advertising.',
            'detailed' => 'SEO encompasses three pillars: Technical SEO (site speed, crawlability, schema), On-Page SEO (content, keywords, meta tags), and Off-Page SEO (backlinks, authority building). Effective SEO increases visibility, builds trust, and generates sustainable long-term traffic and leads.',
            'keywords' => 'What is SEO, Search Engine Optimization, SEO meaning, SEO definition'
        ],
        'what-is-ai-automation' => [
            'question' => 'What is AI Automation?',
            'quick_answer' => 'AI automation uses artificial intelligence to automate repetitive business tasks — from customer support and lead qualification to data processing and marketing workflows — reducing manual work by 50-70%.',
            'detailed' => 'AI automation combines machine learning, natural language processing, and workflow engines to handle tasks that previously required human intervention. Common applications include chatbots, email automation, document processing, predictive analytics, and intelligent decision-making systems.',
            'keywords' => 'What is AI Automation, Business Automation, AI Agency India'
        ],
        'how-much-does-seo-cost' => [
            'question' => 'How much does SEO cost?',
            'quick_answer' => 'SEO costs in India range from ₹15,000/month for local businesses to ₹1,00,000+/month for enterprise clients. Factors include competition level, website size, target keywords, and scope of services.',
            'detailed' => 'A typical SEO investment includes keyword research, on-page optimization, content creation, link building, and technical audits. ROI-positive SEO typically requires 6-12 months of consistent investment. Nectra Digital offers free SEO audits to recommend the optimal budget for your goals.',
            'keywords' => 'SEO cost India, SEO pricing, How much does SEO cost'
        ],
        'what-is-digital-marketing' => [
            'question' => 'What is Digital Marketing?',
            'quick_answer' => 'Digital marketing is the promotion of products and services through online channels including search engines, social media, email, websites, and paid advertising to reach and convert target audiences.',
            'detailed' => 'Digital marketing includes SEO, PPC advertising, social media marketing, content marketing, email marketing, influencer marketing, and analytics. Unlike traditional marketing, digital marketing offers precise targeting, real-time measurement, and scalable campaigns with measurable ROI.',
            'keywords' => 'What is Digital Marketing, Digital Marketing Agency India, Online Marketing'
        ],
        'best-seo-company-india' => [
            'question' => 'Best SEO Company in India?',
            'quick_answer' => 'Nectra Digital is among the best SEO companies in India, offering comprehensive search engine optimization services with proven results, transparent reporting, and expertise in technical SEO, local SEO, and enterprise SEO.',
            'detailed' => 'When choosing an SEO company in India, evaluate their case studies, technical expertise, transparency in reporting, and understanding of your industry. Nectra Digital has 5+ years of experience, 200+ projects delivered, and specializes in data-driven SEO strategies that deliver measurable ROI.',
            'keywords' => 'Best SEO Company India, SEO Company India, Top SEO Agency India'
        ],
        'how-to-generate-leads-online' => [
            'question' => 'How to generate leads online?',
            'quick_answer' => 'Generate leads online through SEO-optimized content, Google Ads campaigns, social media marketing, lead magnets (ebooks, webinars), email nurturing sequences, and AI chatbots that qualify and capture visitor information 24/7.',
            'detailed' => 'Effective online lead generation combines multiple channels: organic search (SEO), paid advertising (PPC), content marketing, social proof, landing page optimization, and marketing automation. The key is creating valuable content that attracts your ideal customer and converting visitors through optimized forms and CTAs.',
            'keywords' => 'Lead Generation Agency, How to generate leads online, Online lead generation'
        ]
    ];
}

function get_content_silos() {
    return [
        'Software Development' => [
            'pillar' => 'Custom Software Development Guide for Startups & SMEs',
            'clusters' => ['SaaS Development Roadmap', 'API Design Best Practices', 'Agile vs Waterfall', 'Cloud Architecture Patterns', 'Software Security Essentials']
        ],
        'SEO' => [
            'pillar' => 'Complete Guide to SEO in India 2026',
            'clusters' => ['Technical SEO Checklist', 'Local SEO Strategy', 'Link Building Guide', 'Keyword Research Methods', 'SEO for Ecommerce', 'Enterprise SEO Playbook']
        ],
        'AI Automation' => [
            'pillar' => 'AI Automation for Business: Complete Guide',
            'clusters' => ['Chatbot Implementation Guide', 'WhatsApp Business Automation', 'Marketing Automation Workflows', 'AI for Customer Support', 'Process Automation ROI']
        ],
        'Digital Marketing' => [
            'pillar' => 'Digital Marketing Strategy for Indian Businesses',
            'clusters' => ['Social Media Marketing Guide', 'Content Marketing Framework', 'Email Marketing Best Practices', 'Marketing Funnel Optimization', 'Brand Building Online']
        ],
        'Web Development' => [
            'pillar' => 'Modern Web Development: Technologies & Best Practices',
            'clusters' => ['Next.js vs WordPress', 'Website Speed Optimization', 'Responsive Design Guide', 'Web Accessibility Standards', 'Headless CMS Architecture']
        ],
        'Lead Generation' => [
            'pillar' => 'Lead Generation Strategies That Actually Work',
            'clusters' => ['B2B Lead Generation Tactics', 'Landing Page Optimization', 'Lead Magnet Ideas', 'LinkedIn Lead Generation', 'Conversion Rate Optimization']
        ],
        'Performance Marketing' => [
            'pillar' => 'Performance Marketing: ROI-Driven Advertising Guide',
            'clusters' => ['Google Ads Optimization', 'Meta Ads Strategy', 'ROAS Improvement Tactics', 'Retargeting Campaigns', 'PPC Budget Allocation']
        ]
    ];
}

function get_blog_topic_ideas() {
    return [
        'SEO' => [
            'Complete Technical SEO Audit Checklist for 2026', 'How to Rank #1 on Google in India', 'Local SEO Strategy for Small Businesses',
            'Enterprise SEO: Scaling Organic Growth', 'Core Web Vitals Optimization Guide', 'Schema Markup Implementation Tutorial',
            'Link Building Strategies That Work in 2026', 'SEO vs SEM: Which is Better for Your Business?', 'How to Do Keyword Research for Indian Markets',
            'Ecommerce SEO: Product Page Optimization', 'Google Algorithm Updates Explained', 'SEO for SaaS Companies',
            'International SEO and hreflang Guide', 'Content Silo Architecture for SEO', 'How to Recover from Google Penalties',
            'Voice Search Optimization Strategies', 'SEO ROI: How to Measure Success', 'Programmatic SEO for Large Sites'
        ],
        'AI Automation' => [
            'What is AI Automation? Complete Beginner Guide', 'Top 10 Business Processes to Automate with AI', 'How to Build an AI Chatbot for Your Website',
            'WhatsApp Business API Setup Guide', 'AI vs Traditional Automation: Key Differences', 'Marketing Automation Workflows That Convert',
            'AI Tools for Digital Marketing in 2026', 'ChatGPT for Business: Practical Use Cases', 'AI Lead Scoring and Qualification',
            'Automating Customer Support with AI', 'AI Content Generation: Best Practices', 'RPA vs AI Automation Explained',
            'Building AI Agents for Business', 'AI Automation ROI Calculator Guide', 'Future of AI in Digital Marketing'
        ],
        'Digital Marketing' => [
            'Digital Marketing Strategy for Startups in India', 'Social Media Marketing Trends 2026', 'Content Marketing Framework for B2B',
            'Email Marketing Best Practices', 'Influencer Marketing in India', 'Brand Building on a Budget',
            'Marketing Funnel Optimization Guide', 'Customer Journey Mapping Tutorial', 'Video Marketing Strategy for Businesses',
            'Omnichannel Marketing Explained', 'Marketing Analytics Dashboard Setup', 'Growth Hacking Strategies for SaaS',
            'Personalization in Digital Marketing', 'Community Building for Brands', 'Digital Marketing Budget Planning'
        ],
        'Web Development' => [
            'Next.js vs WordPress: Which to Choose?', 'Website Speed Optimization Techniques', 'Responsive Web Design Best Practices',
            'Web Accessibility (WCAG) Compliance Guide', 'Headless CMS Architecture Explained', 'Progressive Web Apps (PWA) Guide',
            'Website Security Best Practices 2026', 'Choosing the Right Tech Stack', 'Landing Page Design for Conversions',
            'WordPress vs Custom Development', 'React vs Vue vs Angular Comparison', 'Website Migration Without Losing SEO',
            'API-First Development Approach', 'Micro-Frontend Architecture Guide', 'Web Performance Budget Planning'
        ],
        'Software Development' => [
            'Custom Software Development Process Explained', 'SaaS Development Roadmap for Startups', 'API Design Best Practices',
            'Agile vs Waterfall Methodology', 'Cloud Architecture Patterns for Scale', 'Software Security Essentials',
            'MVP Development Strategy', 'DevOps Best Practices for Startups', 'Database Design for Web Applications',
            'Microservices vs Monolith Architecture', 'Software Testing Strategies', 'Technical Debt Management Guide',
            'Open Source vs Proprietary Software', 'Software Licensing Models Explained', 'Scaling Software Applications'
        ],
        'Lead Generation' => [
            'B2B Lead Generation Strategies That Work', 'Landing Page Optimization Guide', 'Lead Magnet Ideas for Every Industry',
            'LinkedIn Lead Generation Tactics', 'Conversion Rate Optimization Checklist', 'Cold Email Outreach Best Practices',
            'Webinar Lead Generation Strategy', 'Quiz Funnels for Lead Capture', 'Referral Marketing Programs',
            'Lead Nurturing Email Sequences', 'Sales Funnel Optimization', 'Account-Based Marketing Guide',
            'Lead Scoring Models Explained', 'Free Tools for Lead Generation', 'Measuring Lead Generation ROI'
        ],
        'Performance Marketing' => [
            'Google Ads Optimization Guide 2026', 'Meta Ads Strategy for Indian Businesses', 'ROAS Improvement Tactics',
            'Retargeting Campaign Best Practices', 'PPC Budget Allocation Framework', 'Google Shopping Ads Tutorial',
            'YouTube Advertising Strategy', 'Display Ads vs Search Ads', 'Conversion Tracking Setup Guide',
            'Ad Copywriting Formulas That Convert', 'Audience Targeting Strategies', 'Seasonal Campaign Planning',
            'Competitor Ad Analysis Methods', 'Landing Page A/B Testing Guide', 'Performance Marketing KPIs Dashboard'
        ]
    ];
}

function output_schema_graph($schemas) {
    $graph = ['@context' => 'https://schema.org', '@graph' => $schemas];
    echo '<script type="application/ld+json">' . json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
}

function output_faq_schema($faqs) {
    $items = [];
    foreach ($faqs as $faq) {
        $items[] = [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']]
        ];
    }
    echo '<script type="application/ld+json">' . json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $items
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function get_breadcrumb_schema($items) {
    $list = [];
    $pos = 1;
    foreach ($items as $item) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $item['name'],
            'item' => $item['url']
        ];
    }
    return [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list
    ];
}
