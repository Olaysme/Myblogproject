<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/site.php';
$projects = project_list();
$brand = setting('brand_name', 'Olayinka Solaja');

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Selected development projects by Olayinka Solaja.">
    <title><?= h(setting('projects_eyebrow', 'Projects')) ?> | <?= h($brand) ?></title>
    <link rel="stylesheet" href="../assets/projects.css">
</head>
<body>
    <header class="site-header">
        <div class="nav-shell">
            <a class="brand" href="/"><?= h($brand) ?></a>
            <nav aria-label="Primary navigation">
                <a href="/services">Services</a>
                <a href="/projects" aria-current="page">Projects</a>
                <a href="/about">About</a>
            </nav>
            <a class="contact-link" href="/contact">Get in touch</a>
        </div>
    </header>

    <main class="projects">
        <p class="eyebrow"><?= h(setting('projects_eyebrow', 'Projects')) ?></p>
        <h1><?= h(setting('projects_title', 'Selected work')) ?></h1>
        <section class="project-grid" aria-label="Selected projects">
            <?php foreach ($projects as $project): ?>
                <article class="project-card">
                    <?php if (!empty($project['image_url'])): ?>
                        <img class="project-image" src="<?= h((string)$project['image_url']) ?>" alt="<?= h($project['title']) ?>">
                    <?php else: ?>
                        <div class="preview" aria-label="Project image placeholder">Project image</div>
                    <?php endif; ?>
                    <div class="project-content">
                        <h2><?= h($project['title']) ?></h2>
                        <p class="description"><?= h($project['description']) ?></p>
                        <div class="tags" aria-label="Technologies">
                            <?php foreach ($project['technologies'] as $technology): ?>
                                <span class="tag"><?= h($technology) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
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
