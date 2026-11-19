<?php 
// 1. INITIALIZATION & DATABASE
ob_start();
session_start();
require_once 'includes/db.php';

// Define SEO Meta Tags for Header
$page_title = "Hire Dedicated Digital & Engineering Experts | Nectra Digital";
$page_desc  = "Hire senior web engineers, Technical SEO architects, and AI automation specialists. Dedicated sprint teams engineered for rapid compounding ROI.";
$page_keys  = "Hire Developers, Hire SEO Expert, Dedicated Engineers, Digital Growth Agency, AI Automation Services, Nectra Digital";

// 2. FORM PROCESSING LOGIC
$msg = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_hire'])) {
    
    // Sanitize Inputs
    $full_name = $conn->real_escape_string(trim($_POST['full_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $service = $conn->real_escape_string(trim($_POST['service_needed']));
    $budget = $conn->real_escape_string(trim($_POST['budget']));
    $details = $conn->real_escape_string(trim(htmlspecialchars($_POST['project_details'])));
    
    // Bot Trap (Honeypot)
    if (!empty($_POST['website_url'])) {
        die("Spam detected.");
    }

    // Insert into Database
    $sql = "INSERT INTO hire_requests (full_name, email, phone, service_needed, budget, project_details) 
            VALUES ('$full_name', '$email', '$phone', '$service', '$budget', '$details')";
            
    if ($conn->query($sql) === TRUE) {
        $msg = "Thank you! Your project requirements have been submitted. Our technical director will contact you within 4 business hours.";
        $msg_type = "success";
    } else {
        $msg = "Something went wrong saving your inquiry. Please reach out via WhatsApp or email directly.";
        $msg_type = "danger";
    }
}

// 3. INJECT HEADER
include 'includes/header.php'; 
?>

<main class="bg-light">
    <!-- Hero Section -->
    <section class="nd-nav-hero-tone py-5 text-center text-white" style="background: linear-gradient(135deg, #081426 0%, #0B1F3A 100%);">
        <div class="container py-4">
            <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-25 px-3 py-1.5 rounded-pill mb-3 small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">
                Dedicated Technical Squads
            </span>
            <h1 class="fw-bold text-white mb-3 nd-hero-title">
                Hire Vetted Senior <span class="text-primary" style="background: linear-gradient(135deg, #38BDF8 0%, #818CF8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Engineering & Growth</span> Experts
            </h1>
            <p class="lead text-white-50 mx-auto nd-prose-width">
                Scale your engineering and marketing capabilities with high-velocity talent. We build scalable software, AI automations, and search dominance that drive compounding revenue.
            </p>
        </div>
    </section>

    <!-- Value Propositions -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.05em;">Why Choose Nectra</span>
                <h2 class="h2 fw-bold text-dark mt-1">Engineered for Predictable Delivery</h2>
                <div class="mx-auto mt-2" style="width: 48px; height: 3px; background: #2563EB; border-radius: 2px;"></div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                        <div class="rounded-3 p-3 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width: 56px; height: 56px;">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                        <h4 class="h5 fw-bold text-dark mb-2">Revenue-Driven Outcomes</h4>
                        <p class="text-muted small mb-0">We focus on business metrics—pipeline growth, Core Web Vitals, organic search traffic, and conversion lift—not vanity KPIs.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                        <div class="rounded-3 p-3 d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info mx-auto mb-3" style="width: 56px; height: 56px;">
                            <i class="fas fa-robot fa-lg"></i>
                        </div>
                        <h4 class="h5 fw-bold text-dark mb-2">Production AI & Agents</h4>
                        <p class="text-muted small mb-0">We architect practical AI applications, WhatsApp conversational agents, and workflow automations that eliminate human bottlenecks.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                        <div class="rounded-3 p-3 d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success mx-auto mb-3" style="width: 56px; height: 56px;">
                            <i class="fas fa-code-branch fa-lg"></i>
                        </div>
                        <h4 class="h5 fw-bold text-dark mb-2">Modern Enterprise Stack</h4>
                        <p class="text-muted small mb-0">Clean code, robust relational databases, zero unnecessary bloat, and enterprise-grade security for rock-solid uptime.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Engagement Workflow & Fast Track -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.05em;">Our Process</span>
                    <h2 class="h2 fw-bold text-dark mt-1 mb-3">How We Execute Your Project</h2>
                    <p class="text-muted small mb-4">A structured, low-risk engineering engagement designed to get you to market fast without technical debt.</p>
                    
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">1</div>
                            <div>
                                <h5 class="h6 fw-bold text-dark mb-1">Discovery & Audit</h5>
                                <p class="text-muted small mb-0">We dissect your technical architecture, audience behavior, and market opportunities to define clear specifications.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">2</div>
                            <div>
                                <h5 class="h6 fw-bold text-dark mb-1">Architecture & Sprint Execution</h5>
                                <p class="text-muted small mb-0">We build in iterative milestones with daily transparent standups, code commits, and staging reviews.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">3</div>
                            <div>
                                <h5 class="h6 fw-bold text-dark mb-1">Launch, Quality Audit & Scale</h5>
                                <p class="text-muted small mb-0">Rigorous load testing, Core Web Vitals audit, automated indexing, and hands-on maintenance.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5" style="background: #081426;">
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill small mb-2 d-inline-block">Fast-Track Response</span>
                        <h3 class="h4 fw-bold text-white mb-2">Speak Directly With An Engineer</h3>
                        <p class="text-white-50 small mb-4">Need an urgent project estimate or technical consultation? Chat with our team via WhatsApp.</p>
                        
                        <?php 
                            $waNumber = "917678387759";
                            $waText = urlencode("Hello Nectra Digital! I am looking to hire experts for a project. Let's discuss.");
                        ?>
                        <a href="https://wa.me/<?php echo $waNumber; ?>?text=<?php echo $waText; ?>" target="_blank" rel="noopener" class="btn btn-success w-100 py-3 rounded-pill fw-semibold shadow-xs mb-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="fab fa-whatsapp fa-lg"></i> Chat on WhatsApp (+91 76783 87759)
                        </a>
                        
                        <div class="d-flex align-items-center my-3">
                            <div class="flex-grow-1 border-top border-secondary border-opacity-25"></div>
                            <span class="px-3 text-white-50 small" style="font-size: 0.75rem;">OR SUBMIT BRIEF BELOW</span>
                            <div class="flex-grow-1 border-top border-secondary border-opacity-25"></div>
                        </div>
                        
                        <a href="#hire-form" class="btn btn-outline-light w-100 py-2.5 rounded-pill small fw-semibold">
                            <i class="fas fa-file-alt me-1"></i> Fill Requirement Form
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hire Form Section -->
    <section class="py-5 nd-interactive-zone nd-no-section-animate" id="hire-form">
        <div class="container">
            <div class="text-center mb-5">
                <span class="text-primary fw-semibold small text-uppercase" style="letter-spacing: 0.05em;">Project Inquiry</span>
                <h2 class="h2 fw-bold text-dark mt-1">Submit Your Project Brief</h2>
                <p class="text-muted small mx-auto" style="max-width: 520px;">Share your requirements and we will review your brief and prepare a comprehensive proposal.</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                        
                        <?php if($msg): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($msg_type); ?> d-flex align-items-center gap-2 rounded-3 mb-4">
                                <i class="fas fa-<?php echo ($msg_type === 'success') ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                                <div><?php echo htmlspecialchars($msg); ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="hire-experts.php#hire-form">
                            <input type="text" name="website_url" style="display:none;" autocomplete="off">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Full Name *</label>
                                    <input type="text" name="full_name" class="form-control" required placeholder="e.g. John Doe">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required placeholder="john@company.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Phone / WhatsApp *</label>
                                    <input type="tel" name="phone" class="form-control" required placeholder="+91 98765 43210">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Primary Service Needed *</label>
                                    <select name="service_needed" class="form-select" required>
                                        <option value="" disabled selected>Select a service domain...</option>
                                        <option value="Custom Software/App">Custom Software Development</option>
                                        <option value="Web Development (React/PHP)">Website &amp; Full-Stack Development</option>
                                        <option value="Mobile App Development">Mobile App Development</option>
                                        <option value="Advanced SEO & Ranking">Enterprise SEO & Local Domination</option>
                                        <option value="AI Automation & Chatbots">AI Automation & WhatsApp Bots</option>
                                        <option value="Full Digital Marketing">Performance & Digital Marketing</option>
                                        <option value="Other">Other Custom Requirement</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-dark">Estimated Budget *</label>
                                    <select name="budget" class="form-select" required>
                                        <option value="Under $500 (Basic)">Under ₹50,000 / $500 (Basic)</option>
                                        <option value="$500 - $2000 (Standard)">₹50k – ₹1.5L / $500 – $2,000 (Standard)</option>
                                        <option value="$2000+ (Enterprise)">₹1.5L+ / $2,000+ (Enterprise)</option>
                                        <option value="Not Sure Yet">Flexible / Discuss on Call</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-dark">Project Details & Objectives *</label>
                                    <textarea name="project_details" class="form-control" rows="4" required placeholder="Tell us about your project scope, target timeline, business goals, and current setup..."></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" name="submit_hire" class="btn btn-primary w-100 py-3 rounded-pill fw-semibold shadow-xs">
                                        Submit Project Brief <i class="fas fa-paper-plane ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>