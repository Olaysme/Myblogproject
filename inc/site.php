<?php
declare(strict_types=1);

function load_env(): void
{
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;
    $file = dirname(__DIR__) . '/.env';
    if (!is_file($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), "\"'");
        if ($key !== '' && getenv($key) === false) putenv($key . '=' . $value);
    }
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    load_env();
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'yinkapw';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function initialize_database(): void
{
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
        setting_value TEXT NOT NULL
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(190) NOT NULL,
        description TEXT NOT NULL,
        position INT NOT NULL DEFAULT 0
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(190) NOT NULL,
        description TEXT NOT NULL,
        technologies TEXT NOT NULL,
        image_url VARCHAR(500) NULL,
        position INT NOT NULL DEFAULT 0
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS quote_requests (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(254) NOT NULL,
        details MEDIUMTEXT NOT NULL,
        budget VARCHAR(120) NULL,
        timeline VARCHAR(120) NULL,
        status ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_quote_requests_status_created (status, created_at)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $defaults = [
        'brand_name' => 'Olayinka Solaja',
        'home_status' => 'Available for freelance work',
        'home_title' => 'Websites and apps that pay for themselves.',
        'home_intro' => 'Full-stack development for startups and small businesses — from a simple website to a full SaaS platform. Clear packages, fixed scope, delivered on time.',
        'home_cta' => 'Get a quote',
        'home_hero_image' => '/assets/images/olayinka-hero.png',
        'recent_title' => 'Recent work',
        'services_eyebrow' => 'Services',
        'services_title' => 'What I do',
        'stack_title' => 'Tools & technologies',
        'stack_tools' => "HTML\nCSS\nJavaScript\nVue.js\nAPIs & Back-End Logic\nAuthentication\nDatabase Architecture\nThird-Party Integrations\nGit Repository",
        'projects_eyebrow' => 'Projects',
        'projects_title' => 'Selected work',
        'about_bio_1' => "I'm a full-stack developer and designer with over 3 years of experience building digital products that are fast, user-friendly, and scalable. I specialize in mobile apps, web development, SaaS platforms, and database architecture.",
        'about_bio_2' => 'From idea to launch, I help startups and businesses turn their vision into powerful, results-driven solutions — focused on clean code, modern design, and delivering on time, every time.',
        'about_location' => 'Berlin, Germany',
        'about_availability' => 'Remote or in-person',
        'about_experience' => '3+ years',
        'contact_title' => "Let's build something together",
        'contact_intro' => 'Based in Berlin, available remote or in person. Reach out to talk through your project.',
        'contact_email' => 'Solajaolayinka@gmail.com',
        'linkedin_url' => 'https://www.linkedin.com/in/olayinka-solaja-045a24232',
        'whatsapp_number' => '+36 30 625 7850',
    ];
    $insert = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($defaults as $key => $value) $insert->execute([$key, $value]);
    $migrateDefault = $pdo->prepare('UPDATE site_settings SET setting_value = ? WHERE setting_key = ? AND setting_value = ?');
    $migrateDefault->execute(['Solajaolayinka@gmail.com', 'contact_email', 'hello@olayinkasolaja.com']);
    $migrateDefault->execute(['https://www.linkedin.com/in/olayinka-solaja-045a24232', 'linkedin_url', 'https://www.linkedin.com']);

    if ((int)$pdo->query('SELECT COUNT(*) FROM services')->fetchColumn() === 0) {
        $seed = [
            ['Web Development', 'Responsive, high-performance websites and web apps.'],
            ['Mobile Development', 'Cross-platform apps with smooth performance and clean UI.'],
            ['SaaS Development', 'End-to-end platforms — architecture, dashboards, billing.'],
            ['Application Development', 'Custom software that turns requirements into working tools.'],
            ['Database Development', 'Scalable architecture and integrations that stay fast under load.'],
            ['Web Design', 'Modern interface design that supports the product, not decoration.'],
        ];
        $stmt = $pdo->prepare('INSERT INTO services (title, description, position) VALUES (?, ?, ?)');
        foreach ($seed as $i => $row) $stmt->execute([$row[0], $row[1], $i]);
    }
    if ((int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn() === 0) {
        $seed = [
            ['SaaS Dashboard Platform', 'Multi-tenant admin dashboard with role-based access and real-time data views.', 'Vue.js, Node.js, PostgreSQL'],
            ['Mobile Booking App', 'Cross-platform app with live availability, push notifications, and in-app payments.', 'React Native, REST API, Stripe'],
            ['Business Reporting Tool', 'Internal web app turning operational data into clear, exportable reports.', 'JavaScript, Chart.js, SQL'],
        ];
        $stmt = $pdo->prepare('INSERT INTO projects (title, description, technologies, position) VALUES (?, ?, ?, ?)');
        foreach ($seed as $i => $row) $stmt->execute([$row[0], $row[1], $row[2], $i]);
    }
}

function setting(string $key, string $fallback = ''): string
{
    try {
        $stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        $legacyDefaults = ['contact_email' => ['hello@olayinkasolaja.com', 'Solajaolayinka@gmail.com'], 'linkedin_url' => ['https://www.linkedin.com', 'https://www.linkedin.com/in/olayinka-solaja-045a24232']];
        if (isset($legacyDefaults[$key]) && $value === $legacyDefaults[$key][0]) {
            $upgrade = db()->prepare('UPDATE site_settings SET setting_value = ? WHERE setting_key = ? AND setting_value = ?');
            $upgrade->execute([$legacyDefaults[$key][1], $key, $legacyDefaults[$key][0]]);
            return $legacyDefaults[$key][1];
        }
        return $value === false ? $fallback : (string)$value;
    } catch (Throwable $e) {
        return $fallback;
    }
}

function service_list(): array
{
    try { return db()->query('SELECT id, title, description, position FROM services ORDER BY position, id')->fetchAll(); }
    catch (Throwable $e) {
        return [
            ['title' => 'Web Development', 'description' => 'Responsive, high-performance websites and web apps.'],
            ['title' => 'Mobile Development', 'description' => 'Cross-platform apps with smooth performance and clean UI.'],
            ['title' => 'SaaS Development', 'description' => 'End-to-end platforms — architecture, dashboards, billing.'],
            ['title' => 'Application Development', 'description' => 'Custom software that turns requirements into working tools.'],
            ['title' => 'Database Development', 'description' => 'Scalable architecture and integrations that stay fast under load.'],
            ['title' => 'Web Design', 'description' => 'Modern interface design that supports the product, not decoration.'],
        ];
    }
}

function project_list(): array
{
    try {
        $rows = db()->query('SELECT id, title, description, technologies, image_url FROM projects ORDER BY position, id')->fetchAll();
        foreach ($rows as &$row) $row['technologies'] = array_values(array_filter(array_map('trim', explode(',', $row['technologies']))));
        return $rows;
    } catch (Throwable $e) {
        return [
            ['title' => 'SaaS Dashboard Platform', 'description' => 'Multi-tenant admin dashboard with role-based access and real-time data views.', 'technologies' => ['Vue.js', 'Node.js', 'PostgreSQL'], 'image_url' => null],
            ['title' => 'Mobile Booking App', 'description' => 'Cross-platform app with live availability, push notifications, and in-app payments.', 'technologies' => ['React Native', 'REST API', 'Stripe'], 'image_url' => null],
            ['title' => 'Business Reporting Tool', 'description' => 'Internal web app turning operational data into clear, exportable reports.', 'technologies' => ['JavaScript', 'Chart.js', 'SQL'], 'image_url' => null],
        ];
    }
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
