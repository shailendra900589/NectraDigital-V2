

<?php
if (!function_exists('get_services_data')) {
    require_once __DIR__ . '/seo-data.php';
}
require_once __DIR__ . '/site-contact.php';
$footer_all_services = get_services_data();
$footer_services = [];
foreach (get_primary_services() as $slug) {
    if (isset($footer_all_services[$slug])) {
        $footer_services[$slug] = $footer_all_services[$slug];
    }
}
$footer_cities = array_slice(get_cities_data(), 0, 8, true);
?>

<?php include __DIR__ . '/ui/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js?v=3" defer></script>
<script src="<?php echo SITE_URL; ?>/assets/js/motion.js?v=8" defer></script>
<script src="<?php echo SITE_URL; ?>/assets/js/depth-3d.js?v=6" defer></script>

<?php
$nectraChatbotEnabled = false;
try {
    if (is_file(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
    }
    if (
        function_exists('nectra_db_is_connected')
        && nectra_db_is_connected()
        && file_exists(__DIR__ . '/growth/bootstrap.php')
    ) {
        require_once __DIR__ . '/growth/bootstrap.php';
        $nectraChatbotEnabled = ge_setting('chatbot_enabled', '0') === '1';
    }
} catch (\Throwable $e) {
    $nectraChatbotEnabled = false;
}
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/floating-contact.css?v=5">
<script src="<?php echo SITE_URL; ?>/assets/js/i18n.js?v=4" defer></script>
<script src="<?php echo SITE_URL; ?>/assets/js/mega-menu.js?v=1" defer></script>
<?php if ($nectraChatbotEnabled): ?>
<script>window.NECTRA_CHATBOT = { apiUrl: '<?php echo SITE_URL; ?>/api/chatbot' };</script>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/growth-chatbot.css?v=4">
<script src="<?php echo SITE_URL; ?>/assets/js/growth-chatbot.js?v=4" defer></script>
<?php endif; ?>
<script src="<?php echo SITE_URL; ?>/assets/js/floating-contact.js?v=4" defer></script>
<?php if (function_exists('nectra_render_adsense_init')) { nectra_render_adsense_init(); } ?>
<script defer>
(function(c,l,a,r,i,t,y){
    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
})(window, document, "clarity", "script", "vjpbvxww01");
</script>
</body>
</html>
