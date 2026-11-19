<?php
namespace Growth\Engines;

use Growth\Models\LandingPage;

class FeedEngine
{
    public static function defaultImage(): string
    {
        return SITE_URL . '/assets/images/logo.png';
    }

    /** Collect feed items: blog, landing pages, services, cities. */
    public static function collectItems(int $limit = 50): array
    {
        global $conn;
        $items = [];

        if (isset($conn) && $conn instanceof \mysqli) {
            require_once __DIR__ . '/../../blog_orphan.php';
            blog_orphan_ensure_schema($conn);
            $listWhere = blog_listable_sql();
            $res = @$conn->query("SELECT title, slug, content, category, created_at, image FROM blog_posts WHERE {$listWhere} ORDER BY created_at DESC LIMIT " . (int)min($limit, 30));
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $items[] = self::itemFromBlog($row);
                }
            }
        }

        if (ge_is_ready()) {
            $pages = LandingPage::forFeed(min($limit, 100));
            foreach ($pages as $p) {
                $items[] = self::itemFromLanding($p);
            }
        }

        require_once __DIR__ . '/../../seo-data.php';
        foreach (get_services_data() as $slug => $svc) {
            $items[] = [
                'title' => $svc['h1'] ?? $svc['title'],
                'url' => SITE_URL . '/' . $slug,
                'description' => $svc['meta_desc'] ?? mb_substr(strip_tags($svc['intro'] ?? ''), 0, 200),
                'category' => $svc['silo'] ?? 'Services',
                'keywords' => IntentKeywordEngine::forStaticPage($slug, $svc['keywords'] ?? ''),
                'pubDate' => time(),
                'image' => self::defaultImage(),
                'type' => 'service',
            ];
        }

        foreach (get_cities_data() as $slug => $city) {
            $items[] = [
                'title' => 'Software Development Company in ' . $city['name'],
                'url' => SITE_URL . '/digital-agency-' . $slug,
                'description' => 'Custom software, web & mobile app development in ' . $city['name'] . ', ' . $city['state'] . '. SaaS, APIs, AI automation, and SEO-ready launches.',
                'category' => 'Locations',
                'keywords' => 'software development ' . $city['name'] . ', custom software ' . $city['name'] . ', web development ' . $city['name'],
                'pubDate' => time() - 3600,
                'image' => self::defaultImage(),
                'type' => 'city',
            ];
        }

        usort($items, fn($a, $b) => ($b['pubDate'] ?? 0) <=> ($a['pubDate'] ?? 0));
        return array_slice($items, 0, $limit);
    }

    public static function rssXml(int $limit = 50): string
    {
        $items = self::collectItems($limit);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>Nectra Digital — SEO, Web Development &amp; AI Automation</title>' . "\n";
        $xml .= '<link>' . SITE_URL . '</link>' . "\n";
        $xml .= '<description>High-intent SEO, digital marketing, web development, and AI automation insights and service pages from Nectra Digital India.</description>' . "\n";
        $xml .= '<language>en-in</language>' . "\n";
        $xml .= '<lastBuildDate>' . gmdate('D, d M Y H:i:s', time()) . ' GMT</lastBuildDate>' . "\n";
        $xml .= '<atom:link href="' . SITE_URL . '/rss.xml" rel="self" type="application/rss+xml" />' . "\n";
        $xml .= '<image><url>' . self::defaultImage() . '</url><title>Nectra Digital</title><link>' . SITE_URL . '</link></image>' . "\n";

        foreach ($items as $item) {
            $xml .= self::rssItem($item);
        }

        $xml .= '</channel></rss>';
        return $xml;
    }

    /** Google Discover / Web Stories style feed with large images. */
    public static function discoverXml(int $limit = 40): string
    {
        $items = array_filter(self::collectItems($limit), fn($i) => in_array($i['type'] ?? '', ['blog', 'landing', 'service'], true));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>Nectra Digital Discover Feed</title>' . "\n";
        $xml .= '<link>' . SITE_URL . '</link>' . "\n";
        $xml .= '<description>Fresh SEO, marketing, and web development content for Google Discover and feed readers.</description>' . "\n";
        $xml .= '<language>en-in</language>' . "\n";
        $xml .= '<atom:link href="' . SITE_URL . '/discover-feed.xml" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($items as $item) {
            $xml .= self::rssItem($item, true);
        }

        $xml .= '</channel></rss>';
        return $xml;
    }

    public static function atomXml(int $limit = 30): string
    {
        $items = self::collectItems($limit);
        $updated = !empty($items[0]['pubDate']) ? gmdate('c', $items[0]['pubDate']) : gmdate('c');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<title>Nectra Digital</title>' . "\n";
        $xml .= '<link href="' . SITE_URL . '" />' . "\n";
        $xml .= '<link href="' . SITE_URL . '/atom.xml" rel="self" />' . "\n";
        $xml .= '<updated>' . $updated . '</updated>' . "\n";
        $xml .= '<id>' . SITE_URL . '/</id>' . "\n";

        foreach ($items as $item) {
            $xml .= '<entry>';
            $xml .= '<title>' . htmlspecialchars($item['title']) . '</title>';
            $xml .= '<link href="' . htmlspecialchars($item['url']) . '" />';
            $xml .= '<id>' . htmlspecialchars($item['url']) . '</id>';
            $xml .= '<updated>' . gmdate('c', $item['pubDate'] ?? time()) . '</updated>';
            $xml .= '<summary>' . htmlspecialchars($item['description']) . '</summary>';
            $xml .= '</entry>' . "\n";
        }

        $xml .= '</feed>';
        return $xml;
    }

    private static function itemFromBlog(array $row): array
    {
        if (!function_exists('nectra_decode_entities')) {
            require_once __DIR__ . '/../../text-utils.php';
        }
        $img = self::defaultImage();
        if (!empty($row['image'])) {
            $img = strpos($row['image'], 'http') === 0 ? $row['image'] : SITE_URL . '/assets/uploads/' . ltrim($row['image'], '/');
        }
        return [
            'title' => nectra_decode_entities($row['title'] ?? ''),
            'url' => SITE_URL . '/' . $row['slug'],
            'description' => mb_substr(strip_tags($row['content']), 0, 280) . '...',
            'category' => $row['category'] ?? 'Insights',
            'keywords' => '',
            'pubDate' => strtotime($row['created_at']),
            'image' => $img,
            'type' => 'blog',
        ];
    }

    private static function itemFromLanding(array $p): array
    {
        $keys = is_string($p['keywords_json'] ?? '') ? implode(', ', ge_json_decode($p['keywords_json'])) : '';
        return [
            'title' => $p['meta_title'] ?? $p['h1'],
            'url' => SITE_URL . '/' . $p['slug'],
            'description' => $p['meta_description'] ?? $p['quick_answer'] ?? '',
            'category' => ($p['service_name'] ?? 'Services') . ' · ' . ($p['city_name'] ?? ''),
            'keywords' => $keys,
            'pubDate' => strtotime($p['generated_at'] ?? 'now'),
            'image' => self::defaultImage(),
            'type' => 'landing',
        ];
    }

    private static function rssItem(array $item, bool $discover = false): string
    {
        $pub = gmdate('D, d M Y H:i:s', $item['pubDate'] ?? time()) . ' GMT';
        $xml = '<item>' . "\n";
        $xml .= '<title><![CDATA[' . $item['title'] . ']]></title>' . "\n";
        $xml .= '<link>' . htmlspecialchars($item['url']) . '</link>' . "\n";
        $xml .= '<guid isPermaLink="true">' . htmlspecialchars($item['url']) . '</guid>' . "\n";
        $xml .= '<description><![CDATA[' . $item['description'] . ']]></description>' . "\n";
        if (!empty($item['category'])) {
            $xml .= '<category><![CDATA[' . $item['category'] . ']]></category>' . "\n";
        }
        if (!empty($item['keywords'])) {
            $xml .= '<category><![CDATA[' . $item['keywords'] . ']]></category>' . "\n";
        }
        $xml .= '<pubDate>' . $pub . '</pubDate>' . "\n";
        $img = htmlspecialchars($item['image'] ?? self::defaultImage());
        $xml .= '<enclosure url="' . $img . '" type="image/png" length="0" />' . "\n";
        if ($discover) {
            $xml .= '<media:content url="' . $img . '" medium="image" type="image/png" />' . "\n";
            $xml .= '<media:thumbnail url="' . $img . '" />' . "\n";
        }
        $xml .= '</item>' . "\n";
        return $xml;
    }
}
