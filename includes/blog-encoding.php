<?php
/**
 * Repair UTF-8 / mojibake in blog_posts (run from admin or CLI).
 */
require_once __DIR__ . '/text-utils.php';

function blog_encoding_repair_text(?string $text): string
{
    return nectra_fix_mojibake($text);
}

function blog_encoding_repair_all(mysqli $conn): int
{
    $res = $conn->query('SELECT id, title, category, meta_description, content FROM blog_posts');
    if (!$res) {
        return 0;
    }

    $fixed = 0;
    while ($row = $res->fetch_assoc()) {
        $id = (int)$row['id'];
        $title = blog_encoding_repair_text($row['title'] ?? '');
        $category = blog_encoding_repair_text($row['category'] ?? '');
        $meta = blog_encoding_repair_text($row['meta_description'] ?? '');
        $content = nectra_sanitize_blog_html($row['content'] ?? '');

        if ($title === ($row['title'] ?? '') && $category === ($row['category'] ?? '')
            && $meta === ($row['meta_description'] ?? '') && $content === ($row['content'] ?? '')) {
            continue;
        }

        $stmt = $conn->prepare('UPDATE blog_posts SET title=?, category=?, meta_description=?, content=? WHERE id=?');
        if (!$stmt) {
            continue;
        }
        $stmt->bind_param('ssssi', $title, $category, $meta, $content, $id);
        if ($stmt->execute()) {
            $fixed++;
        }
    }

    return $fixed;
}
