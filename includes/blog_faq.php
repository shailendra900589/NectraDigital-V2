<?php
/**
 * Blog post FAQs — admin CRUD + frontend display + FAQPage schema.
 */

if (!function_exists('blog_faq_ensure_schema')) {
    function blog_faq_ensure_schema($conn): void
    {
        if (is_file(__DIR__ . '/blog_schema.php')) {
            require_once __DIR__ . '/blog_schema.php';
            blog_schema_ensure($conn);
            return;
        }
        static $done = false;
        if ($done) {
            return;
        }
        $col = @$conn->query("SHOW COLUMNS FROM blog_posts LIKE 'faq_json'");
        if ($col && $col->num_rows === 0) {
            @$conn->query('ALTER TABLE blog_posts ADD COLUMN faq_json TEXT NULL');
        }
        $done = true;
    }
}

if (!function_exists('blog_faq_decode')) {
    function blog_faq_decode(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $q = trim((string)($item['q'] ?? $item['question'] ?? ''));
            $a = trim((string)($item['a'] ?? $item['answer'] ?? ''));
            if ($q !== '' && $a !== '') {
                $out[] = ['q' => $q, 'a' => $a];
            }
        }
        return $out;
    }
}

if (!function_exists('blog_faq_encode')) {
    function blog_faq_encode(array $faqs): string
    {
        $clean = [];
        foreach ($faqs as $faq) {
            $q = trim((string)($faq['q'] ?? ''));
            $a = trim((string)($faq['a'] ?? ''));
            if ($q !== '' && $a !== '') {
                $clean[] = ['q' => $q, 'a' => $a];
            }
        }
        return json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('blog_faq_parse_request')) {
    function blog_faq_parse_request(): array
    {
        $questions = $_POST['faq_q'] ?? [];
        $answers = $_POST['faq_a'] ?? [];
        if (!is_array($questions) || !is_array($answers)) {
            return [];
        }
        $faqs = [];
        foreach ($questions as $i => $q) {
            $q = sanitize_db_text((string)$q);
            $a = sanitize_db_text((string)($answers[$i] ?? ''));
            if ($q !== '' && $a !== '') {
                $faqs[] = ['q' => $q, 'a' => $a];
            }
        }
        return $faqs;
    }
}

if (!function_exists('blog_faq_defaults')) {
    function blog_faq_defaults(array $post): array
    {
        if (!function_exists('nectra_blog_byline')) {
            require_once __DIR__ . '/text-utils.php';
        }
        $pub = !empty($post['created_at']) ? date('F j, Y', strtotime($post['created_at'])) : date('F j, Y');
        $byline = nectra_blog_byline();
        return [
            [
                'q' => 'Who wrote this article?',
                'a' => 'This article was authored and verified by ' . $byline . ' on the Nectra Digital editorial team.',
            ],
            [
                'q' => 'Is this information up to date?',
                'a' => 'We review and update our content regularly to reflect current SEO best practices, algorithm changes, and industry standards. Published: ' . $pub . '.',
            ],
            [
                'q' => 'Can Nectra Digital help implement these strategies?',
                'a' => 'Yes. Nectra Digital offers full-service SEO, digital marketing, AI automation, and web development. Book a free consultation at nectradigital.com/contact.',
            ],
        ];
    }
}

if (!function_exists('blog_faq_for_post')) {
    /** Custom FAQs from admin, or sensible defaults when empty. */
    function blog_faq_for_post(array $post): array
    {
        $custom = blog_faq_decode($post['faq_json'] ?? null);
        if (!empty($custom)) {
            return $custom;
        }
        return blog_faq_defaults($post);
    }
}
