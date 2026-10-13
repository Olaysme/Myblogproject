<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/site.php';
$services = service_list();
$brand = setting('brand_name', 'Olayinka Solaja');
$tools = preg_split('/\R/', setting('stack_tools', "HTML\nCSS\nJavaScript\nVue.js\nAPIs & Back-End Logic\nAuthentication\nDatabase Architecture\nThird-Party Integrations\nGit Repository")) ?: [];

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Full-stack development, design, mobile apps, SaaS platforms, and databases.">
    <title><?= h(setting('services_eyebrow', 'Services')) ?> | <?= h($brand) ?></title>
    <link rel="stylesheet" href="../assets/services.css">
</head>
<body>
    <header class="site-header">
        <div class="nav-shell">
            <a class="brand" href="/"><?= h($brand) ?></a>
            <nav aria-label="Primary navigation">
                <a href="/services" aria-current="page">Services</a>
                <a href="/projects">Projects</a>
                <a href="/about">About</a>
            </nav>
            <a class="contact-link" href="/contact">Get in touch</a>
        </div>
    </header>

    <main class="services">
        <p class="eyebrow"><?= h(setting('services_eyebrow', 'Services')) ?></p>
        <h1><?= h(setting('services_title', 'What I do')) ?></h1>
        <section class="grid" aria-label="Services offered">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <span class="service-icon" aria-hidden="true"></span>
                    <h2><?= h($service['title']) ?></h2>
                    <p><?= h($service['description']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <section class="stack-section" aria-labelledby="stack-title">
        <div class="stack-content">
            <p class="eyebrow">Stack</p>
            <h2 class="stack-title" id="stack-title"><?= h(setting('stack_title', 'Tools & technologies')) ?></h2>
            <div class="tool-list" aria-label="Technologies">
                <?php foreach ($tools as $tool): ?>
                    <span class="tool"><?= h(trim($tool)) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
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
