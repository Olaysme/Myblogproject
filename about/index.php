<?php require_once dirname(__DIR__) . '/inc/site.php'; $brand = setting('brand_name', 'Olayinka Solaja'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="About Olayinka Solaja, full-stack developer and designer.">
    <title>About | <?= h($brand) ?></title>
    <link rel="stylesheet" href="../assets/about.css">
</head>
<body>
    <header class="site-header">
        <div class="nav-shell">
            <a class="brand" href="/"><?= h($brand) ?></a>
            <nav aria-label="Primary navigation">
                <a href="/services">Services</a>
                <a href="/projects">Projects</a>
                <a href="/about" aria-current="page">About</a>
            </nav>
            <a class="contact-link" href="/contact">Get in touch</a>
        </div>
    </header>

    <main class="about">
        <section aria-labelledby="about-title">
            <h1 class="eyebrow" id="about-title">About</h1>
            <p class="bio"><?= h(setting('about_bio_1', "I'm a full-stack developer and designer with over 3 years of experience building digital products that are fast, user-friendly, and scalable. I specialize in mobile apps, web development, SaaS platforms, and database architecture.")) ?></p>
            <p class="bio"><?= h(setting('about_bio_2', 'From idea to launch, I help startups and businesses turn their vision into powerful, results-driven solutions — focused on clean code, modern design, and delivering on time, every time.')) ?></p>
        </section>

        <section class="details" aria-label="Profile details">
            <div class="detail-row"><span class="detail-label">Location</span><strong class="detail-value"><?= h(setting('about_location', 'Berlin, Germany')) ?></strong></div>
            <div class="detail-row"><span class="detail-label">Availability</span><strong class="detail-value"><?= h(setting('about_availability', 'Remote or in-person')) ?></strong></div>
            <div class="detail-row"><span class="detail-label">Experience</span><strong class="detail-value"><?= h(setting('about_experience', '3+ years')) ?></strong></div>
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
