<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/site.php';
$projects = array_slice(project_list(), 0, 3);
$brand = setting('brand_name', 'Olayinka Solaja');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Olayinka Solaja builds websites and apps that pay for themselves.">
    <title><?= h($brand) ?> | Full-stack developer</title>
    <link rel="stylesheet" href="assets/site.css">
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
            <a class="contact-link" href="/contact">Get in touch</a>
        </div>
    </header>

    <main>
        <div class="hero">
            <section aria-labelledby="hero-title">
                <div class="status"><?= h(setting('home_status', 'Available for freelance work')) ?></div>
                <h1 id="hero-title"><?= h(setting('home_title', 'Websites and apps that pay for themselves.')) ?></h1>
                <p class="intro"><?= h(setting('home_intro', 'Full-stack development for startups and small businesses — from a simple website to a full SaaS platform. Clear packages, fixed scope, delivered on time.')) ?></p>
                <div class="actions">
                    <a class="button secondary" href="/contact#quote-form"><?= h(setting('home_cta', 'Get a quote')) ?></a>
                </div>
            </section>
            <figure class="portrait">
                <img src="<?= h(setting('home_hero_image', '/assets/images/olayinka-hero.png')) ?>" alt="<?= h($brand) ?> portrait">
            </figure>
        </div>

        <section class="blog-feed" aria-labelledby="recent-title">
            <div class="section-heading">
                <h2 id="recent-title"><?= h(setting('recent_title', 'Recent work')) ?></h2>
                <a href="/projects">View all projects →</a>
            </div>
            <div class="post-list">
                <?php foreach ($projects as $project): ?>
                    <article class="post">
                        <?php if (!empty($project['image_url'])): ?><img class="post-image" src="<?= h($project['image_url']) ?>" alt="<?= h($project['title']) ?>"><?php else: ?><div class="post-image image-placeholder" aria-label="Project image placeholder">Project image</div><?php endif; ?>
                        <span class="post-kicker">Case study</span>
                        <h3><?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <span class="post-tags"><?= htmlspecialchars(implode(', ', $project['technologies']), ENT_QUOTES, 'UTF-8') ?></span>
                    </article>
                <?php endforeach; ?>
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
