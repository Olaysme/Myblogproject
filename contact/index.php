<?php
require_once dirname(__DIR__) . '/inc/site.php';
$brand = setting('brand_name', 'Olayinka Solaja');
$phone = setting('whatsapp_number', '+36 30 625 7850');
$wa = preg_replace('/\D+/', '', $phone);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['quote_csrf'])) $_SESSION['quote_csrf'] = bin2hex(random_bytes(32));
$quoteToken = $_SESSION['quote_csrf'];
$quoteSuccess = !empty($_SESSION['quote_submitted']);
unset($_SESSION['quote_submitted']);
$quoteError = '';
$quoteInput = ['name' => '', 'email' => '', 'details' => '', 'budget' => '', 'timeline' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'quote') {
    foreach ($quoteInput as $key => $_) $quoteInput[$key] = trim((string)($_POST[$key] ?? ''));
    $token = (string)($_POST['quote_csrf'] ?? '');
    if (!hash_equals($quoteToken, $token)) {
        $quoteError = 'Your form session expired. Refresh the page and try again.';
    } elseif (!empty($_POST['website'])) {
        $quoteSuccess = true;
    } elseif (strlen($quoteInput['name']) < 2 || strlen($quoteInput['name']) > 120) {
        $quoteError = 'Please enter your name (2–120 characters).';
    } elseif (!filter_var($quoteInput['email'], FILTER_VALIDATE_EMAIL) || strlen($quoteInput['email']) > 254) {
        $quoteError = 'Please enter a valid email address.';
    } elseif (strlen($quoteInput['details']) < 15 || strlen($quoteInput['details']) > 20000) {
        $quoteError = 'Please describe your project (15–20,000 characters).';
    } elseif (strlen($quoteInput['budget']) > 120 || strlen($quoteInput['timeline']) > 120) {
        $quoteError = 'Budget and timeline must each be 120 characters or fewer.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO quote_requests (name, email, details, budget, timeline) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$quoteInput['name'], $quoteInput['email'], $quoteInput['details'], $quoteInput['budget'] ?: null, $quoteInput['timeline'] ?: null]);
            $_SESSION['quote_submitted'] = true;
            header('Location: /contact#quote-form');
            exit;
        } catch (Throwable $e) {
            $quoteError = 'Your request could not be submitted right now. Please contact us by email or WhatsApp instead.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Get in touch with Olayinka Solaja to discuss your project.">
    <title>Get in touch | <?= h($brand) ?></title>
    <link rel="stylesheet" href="../assets/contact.css">
</head>
<body>
    <header class="site-header">
        <div class="nav-shell">
            <a class="brand" href="/"><?= h($brand) ?></a>
            <nav aria-label="Primary navigation">
                <a href="/services">Services</a>
                <a href="/projects">Projects</a>
                <a href="/about">About</a>
            </nav>
            <a class="contact-link" href="/contact" aria-current="page">Get in touch</a>
        </div>
    </header>

    <main class="contact-main">
        <section class="contact-card" aria-labelledby="contact-title">
            <h1 id="contact-title"><?= h(setting('contact_title', "Let's build something together")) ?></h1>
            <p class="intro"><?= h(setting('contact_intro', 'Based in Berlin, available remote or in person. Reach out to talk through your project.')) ?></p>
            <?php if ($quoteSuccess): ?><p class="form-message success" role="status">Thanks for reaching out. Your project request has been received.</p><?php endif; ?>
            <?php if ($quoteError): ?><p class="form-message error" role="alert"><?= h($quoteError) ?></p><?php endif; ?>
            <form class="quote-form" id="quote-form" method="post" action="/contact#quote-form">
                <input type="hidden" name="form_type" value="quote">
                <input type="hidden" name="quote_csrf" value="<?= h($quoteToken) ?>">
                <div class="form-trap" aria-hidden="true"><label>Leave this field empty<input name="website" tabindex="-1" autocomplete="off"></label></div>
                <div class="quote-grid">
                    <label>Your name<input name="name" required minlength="2" maxlength="120" autocomplete="name" value="<?= h($quoteInput['name']) ?>"></label>
                    <label>Email address<input type="email" name="email" required maxlength="254" autocomplete="email" value="<?= h($quoteInput['email']) ?>"></label>
                    <label class="quote-wide">Tell me about your project<textarea name="details" required minlength="15" maxlength="20000" rows="5" placeholder="What would you like to build? What should it help you achieve?"><?= h($quoteInput['details']) ?></textarea></label>
                    <label>Budget <span>(optional)</span><input name="budget" maxlength="120" placeholder="e.g. €2,000–€5,000" value="<?= h($quoteInput['budget']) ?>"></label>
                    <label>Timeline <span>(optional)</span><input name="timeline" maxlength="120" placeholder="e.g. Within 6 weeks" value="<?= h($quoteInput['timeline']) ?>"></label>
                </div>
                <button class="action primary submit-quote" type="submit">Send project request</button>
            </form>
            <p class="contact-alternative">Prefer a quick message? Contact me directly:</p>
            <div class="actions">
                <a class="action primary" href="mailto:<?= h(setting('contact_email', 'Solajaolayinka@gmail.com')) ?>"><?= h(setting('contact_email', 'Solajaolayinka@gmail.com')) ?></a>
                <a class="action whatsapp" href="https://wa.me/<?= h($wa) ?>" target="_blank" rel="noopener noreferrer">WhatsApp&nbsp; <?= h($phone) ?></a>
                <a class="action" href="<?= h(setting('linkedin_url', 'https://www.linkedin.com/in/olayinka-solaja-045a24232')) ?>" target="_blank" rel="noopener noreferrer">LinkedIn ↗</a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-shell">
            <span>© <?= date('Y') ?> <?= h(setting('brand_name', 'Olayinka Solaja')) ?></span>
            <nav aria-label="Footer navigation">
                <a href="/services">Services</a>
                <a href="/projects">Projects</a>
                <a href="/about">About</a>
                <a href="/contact">Contact</a>
            </nav>
        </div>
    </footer>
</body>
</html>
