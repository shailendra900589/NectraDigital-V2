<?php
require_once 'includes/seo-data.php';
require_once 'includes/seo-components.php';
require_once 'includes/i18n.php';
require_once 'includes/site-contact.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = "Contact Nectra Digital | Software Development Consultation";
$page_desc = "Contact Nectra Digital for custom software, SaaS, web and mobile apps, APIs, and AI automation — plus SEO and growth when you need launch support. Free discovery call; reply within one business day.";
$isDarkHeader = true;
$canonical_url = nectra_page_canonical('/contact');

$page_schema = [
    get_breadcrumb_schema([
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Contact', 'url' => SITE_URL . '/contact'],
    ]),
];

include 'includes/header.php';
?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ContactPage",
  "name": "Contact Nectra Digital",
  "description": "Global Headquarters and Project Consultation Center",
  "mainEntity": {
    "@type": "ProfessionalService",
    "name": "Nectra Digital",
    "telephone": "<?php echo NECTRA_PHONE_E164; ?>",
    "email": "<?php echo nectra_schema_email(); ?>",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Lucknow",
      "addressLocality": "Lucknow",
      "addressRegion": "UP",
      "postalCode": "226001",
      "addressCountry": "IN"
    },
    "openingHoursSpecification": {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "opens": "09:00",
      "closes": "18:00"
    }
  }
}
</script>

<main style="background: #F8FAFC; min-height: 100vh;">
    <!-- HERO (Dark Navy) -->
    <header class="nd-page-hero nd-section-dark text-center position-relative overflow-hidden">
        <div class="nd-page-hero-glow" aria-hidden="true"></div>
        <div class="container position-relative z-1 py-2 py-md-3">
            <?php render_breadcrumbs([['name' => 'Home', 'url' => '/'], ['name' => 'Contact', 'url' => '/contact']]); ?>
            <h1 class="fw-bold text-white mb-3 nd-hero-title">Let's Grow Your <span class="nd-text-gradient">Organic Traffic</span></h1>
            <p class="lead text-white-50 mx-auto nd-prose-width" style="line-height: 1.6;">
                Request a free SEO audit, book a consultation, or ask about AI automation and development — our team responds within one business day.
            </p>
        </div>
    </header>

    <!-- TRUST STRIP -->
    <?php include __DIR__ . '/includes/ui/trust-bar.php'; ?>

    <!-- MAIN CONTACT SECTION -->
    <section class="py-5">
        <div class="container py-3">
            <div class="row g-5">
                <!-- Left Column: Company & Operations Details -->
                <div class="col-lg-5">
                    <div class="p-4 p-md-5 bg-white border rounded-4 shadow-sm h-100">
                        <h2 class="h4 fw-bold text-dark mb-4 nd-section-title">Operations &amp; Support</h2>

                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary me-3 mt-1 flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Base of Operations</h3>
                                <p class="text-muted small mb-0">Lucknow, Uttar Pradesh, India.<br>Serving clients Pan-India &amp; Internationally.</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary me-3 mt-1 flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Direct Phone</h3>
                                <p class="text-muted small mb-0">
                                    <a href="tel:<?php echo NECTRA_PHONE_E164; ?>" class="text-primary text-decoration-none fw-semibold"><?php echo NECTRA_PHONE_DISPLAY; ?></a>
                                    <span class="d-block text-muted" style="font-size: 0.75rem;">Mon - Fri: 9:00 AM - 6:00 PM IST</span>
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary me-3 mt-1 flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Electronic Mail</h3>
                                <p class="text-muted small mb-0"><?php echo nectra_email_html_link('text-primary text-decoration-none fw-semibold'); ?></p>
                            </div>
                        </div>

                        <!-- Live Time Zones -->
                        <div class="border-top pt-4 mt-4">
                            <h3 class="text-dark h6 fw-bold small text-uppercase mb-3" style="letter-spacing: 0.5px;">Active Time Zones</h3>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="p-2 rounded text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                                        <span class="d-block text-muted" style="font-size: 0.65rem;">INDIA (IST)</span>
                                        <span class="text-dark fw-bold small" id="time-ist">--:--</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                                        <span class="d-block text-muted" style="font-size: 0.65rem;">NEW YORK (EST)</span>
                                        <span class="text-primary fw-bold small" id="time-est">--:--</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                                        <span class="d-block text-muted" style="font-size: 0.65rem;">LONDON (GMT)</span>
                                        <span class="text-dark fw-bold small" id="time-gmt">--:--</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Socials -->
                        <div class="mt-4 pt-3 border-top">
                            <span class="text-dark fw-bold small d-block mb-2">Verified Channels</span>
                            <div class="d-flex gap-2">
                                <a href="https://www.linkedin.com/company/nectradigital" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" aria-label="LinkedIn"><i class="fab fa-linkedin me-1"></i> LinkedIn</a>
                                <a href="https://twitter.com/nectradigital" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" aria-label="Twitter"><i class="fab fa-twitter me-1"></i> Twitter</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Project Inquiry Form -->
                <div class="col-lg-7">
                    <div class="p-4 p-md-5 bg-white border rounded-4 shadow-sm h-100" id="contactPageFormWrap">
                        <h2 class="h3 fw-bold text-dark mb-1 nd-section-title">Request a Free Proposal</h2>
                        <p class="text-muted small mb-4">Complete the briefing below. A senior strategist will review your requirements.</p>

                        <form id="contactPageForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            <!-- Honeypot -->
                            <div style="display:none; opacity:0; visibility:hidden;">
                                <label for="website_url_hp">Leave blank</label>
                                <input type="text" name="website_url" id="website_url_hp" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label text-dark small fw-semibold">Full Name *</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" required autocomplete="name">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label text-dark small fw-semibold">Business Email *</label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="john@company.com" required autocomplete="email">
                                </div>

                                <div class="col-12">
                                    <label for="service" class="form-label text-dark small fw-semibold">Project Requirement *</label>
                                    <?php
                                    $preselectService = isset($_GET['service']) ? (string)$_GET['service'] : null;
                                    nectra_render_project_requirement_select('service', true, 'service', $preselectService);
                                    ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="budget" class="form-label text-dark small fw-semibold">Estimated Budget</label>
                                    <select name="budget" id="budget" class="form-select">
                                        <option value="₹25k - ₹50k">₹25,000 - ₹50,000 ($300 - $600)</option>
                                        <option value="₹50k - ₹1.5L">₹50,000 - ₹1,50,000 ($600 - $1,800)</option>
                                        <option value="₹1.5L - ₹5L">₹1,50,000 - ₹5,00,000 ($1,800 - $6,000)</option>
                                        <option value="Enterprise / $10k+">Enterprise / Customized Scope</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="timeline" class="form-label text-dark small fw-semibold">Target Timeline</label>
                                    <select name="timeline" id="timeline" class="form-select">
                                        <option value="Immediate">Immediate (Within 2 Weeks)</option>
                                        <option value="1-3 Months">1 - 3 Months</option>
                                        <option value="Exploratory">Planning / Exploratory</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label text-dark small fw-semibold">Brief Project Summary *</label>
                                    <textarea name="message" id="message" class="form-control" rows="4" placeholder="Describe your current bottleneck, target metrics, or project scope..." required></textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold">
                                        Submit Request <i class="fas fa-paper-plane ms-2"></i>
                                    </button>
                                    <div id="pageFormResponse" class="mt-3 text-center small fw-bold" aria-live="polite"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LOCATION MAP -->
    <section class="border-top" aria-label="HQ Map Location">
        <div style="width: 100%; height: 350px; overflow: hidden; background: #E2E8F0;">
            <iframe 
                title="Nectra Digital HQ Map"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d227822.5503903933!2d80.80242469608678!3d26.84862299412656!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399bfd991f32b16b%3A0x93ccba8909978be7!2sLucknow%2C%20Uttar%20Pradesh!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function updateTime() {
        const now = new Date();
        const options = { hour: '2-digit', minute:'2-digit', hour12: true };
        
        const timeIst = document.getElementById('time-ist');
        if (timeIst) timeIst.textContent = now.toLocaleTimeString("en-US", { ...options, timeZone: "Asia/Kolkata" });
        
        const timeEst = document.getElementById('time-est');
        if (timeEst) timeEst.textContent = now.toLocaleTimeString("en-US", { ...options, timeZone: "America/New_York" });
        
        const timeGmt = document.getElementById('time-gmt');
        if (timeGmt) timeGmt.textContent = now.toLocaleTimeString("en-US", { ...options, timeZone: "Europe/London" });
    }
    updateTime();
    setInterval(updateTime, 60000);

    const form = document.getElementById('contactPageForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            const btn = form.querySelector('button[type="submit"]');
            const resp = document.getElementById('pageFormResponse');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';
            btn.disabled = true;

            fetch(typeof nectraWebPath === 'function' ? nectraWebPath('/process') : '/process', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = "/thank-you";
                } else {
                    resp.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> ${data.message || 'Submission error'}</span>`;
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                resp.innerHTML = '<span class="text-danger">Submission error. Please email contact@nectradigital.com</span>';
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>