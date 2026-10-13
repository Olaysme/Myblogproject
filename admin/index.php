<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_admin();
$error = '';
$message = '';
try {
    initialize_database();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $action = (string)($_POST['action'] ?? '');
        $pdo = db();
        if ($action === 'save_settings') {
            $allowed = [
                'brand_name', 'home_status', 'home_title', 'home_intro', 'home_cta', 'home_hero_image', 'recent_title',
                'services_eyebrow', 'services_title', 'stack_title', 'stack_tools',
                'projects_eyebrow', 'projects_title', 'about_bio_1', 'about_bio_2',
                'about_location', 'about_availability', 'about_experience',
                'contact_title', 'contact_intro', 'contact_email', 'linkedin_url', 'whatsapp_number',
            ];
            if (!empty($_FILES['home_hero_image_file']['name'])) {
                if ($_FILES['home_hero_image_file']['error'] !== UPLOAD_ERR_OK || (int)$_FILES['home_hero_image_file']['size'] > 8 * 1024 * 1024) throw new RuntimeException('The hero image upload failed or exceeds 8 MB.');
                $info = @getimagesize($_FILES['home_hero_image_file']['tmp_name']);
                $exts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
                $mime = $info['mime'] ?? '';
                if (!isset($exts[$mime])) throw new RuntimeException('Upload a JPG, PNG, WEBP, or GIF hero image.');
                $dir = dirname(__DIR__) . '/uploads';
                if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) throw new RuntimeException('Could not create the uploads folder.');
                $file = bin2hex(random_bytes(16)) . '.' . $exts[$mime];
                if (!move_uploaded_file($_FILES['home_hero_image_file']['tmp_name'], $dir . '/' . $file)) throw new RuntimeException('Could not save the hero image.');
                $_POST['home_hero_image'] = '/uploads/' . $file;
            }
            $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($allowed as $key) {
                $value = trim((string)($_POST[$key] ?? ''));
                $stmt->execute([$key, $value]);
            }
            $message = 'Site content saved.';
        } elseif ($action === 'save_service') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            if ($title === '' || $description === '') throw new RuntimeException('Each service needs a title and description.');
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE services SET title = ?, description = ?, position = ? WHERE id = ?');
                $stmt->execute([$title, $description, (int)($_POST['position'] ?? 0), $id]);
                $message = 'Service updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO services (title, description, position) VALUES (?, ?, ?)');
                $stmt->execute([$title, $description, (int)($_POST['position'] ?? 999)]);
                $message = 'Service added.';
            }
        } elseif ($action === 'delete_service') {
            $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            $message = 'Service deleted.';
        } elseif ($action === 'save_project') {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $technologies = trim((string)($_POST['technologies'] ?? ''));
            if ($title === '' || $description === '') throw new RuntimeException('Each project needs a title and description.');
            $imageUrl = trim((string)($_POST['image_url'] ?? '')) ?: null;
            if (!empty($_FILES['image']['name'])) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('The image upload failed. Please try again.');
                if ((int)$_FILES['image']['size'] > 8 * 1024 * 1024) throw new RuntimeException('Images must be 8 MB or smaller.');
                $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
                $mime = $imageInfo['mime'] ?? '';
                $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
                if (!isset($extensions[$mime])) throw new RuntimeException('Upload a JPG, PNG, WEBP, or GIF image.');
                $uploadDir = dirname(__DIR__) . '/uploads';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) throw new RuntimeException('Could not create the uploads folder.');
                $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . '/' . $filename)) throw new RuntimeException('Could not save the uploaded image.');
                $imageUrl = '/uploads/' . $filename;
            } elseif (!empty($_POST['remove_image'])) {
                $imageUrl = null;
            }
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE projects SET title = ?, description = ?, technologies = ?, image_url = ?, position = ? WHERE id = ?');
                $stmt->execute([$title, $description, $technologies, $imageUrl, (int)($_POST['position'] ?? 0), $id]);
                $message = 'Project updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO projects (title, description, technologies, image_url, position) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$title, $description, $technologies, $imageUrl, (int)($_POST['position'] ?? 999)]);
                $message = 'Project added.';
            }
        } elseif ($action === 'delete_project') {
            $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            $message = 'Project deleted.';
        } elseif ($action === 'update_quote') {
            $status = (string)($_POST['status'] ?? '');
            if (!in_array($status, ['new', 'contacted', 'closed'], true)) throw new RuntimeException('Choose a valid inquiry status.');
            $stmt = $pdo->prepare('UPDATE quote_requests SET status = ? WHERE id = ?');
            $stmt->execute([$status, (int)($_POST['id'] ?? 0)]);
            $message = 'Inquiry status updated.';
        } else {
            throw new RuntimeException('Unknown admin action.');
        }
    }
    $settings = [];
    foreach ([
        'brand_name', 'home_status', 'home_title', 'home_intro', 'home_cta', 'home_hero_image', 'recent_title',
        'services_eyebrow', 'services_title', 'stack_title', 'stack_tools',
        'projects_eyebrow', 'projects_title', 'about_bio_1', 'about_bio_2',
        'about_location', 'about_availability', 'about_experience',
        'contact_title', 'contact_intro', 'contact_email', 'linkedin_url', 'whatsapp_number',
    ] as $key) $settings[$key] = setting($key);
    $services = service_list();
    $projects = db()->query('SELECT id, title, description, technologies, image_url, position FROM projects ORDER BY position, id')->fetchAll();
    $inquiries = db()->query('SELECT id, name, email, details, budget, timeline, status, created_at FROM quote_requests ORDER BY created_at DESC LIMIT 100')->fetchAll();
} catch (Throwable $e) {
    $error = $e instanceof RuntimeException ? $e->getMessage() : 'Could not connect to MySQL. Check the database settings in .env.';
    $settings = $services = $projects = $inquiries = [];
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Manage site | Olayinka Solaja</title><link rel="stylesheet" href="/assets/admin.css"></head>
<body class="dashboard">
<header class="admin-header"><a class="admin-brand" href="/">Olayinka Solaja <span>Site admin</span></a><div class="admin-actions"><a href="/" target="_blank" rel="noopener">View site ↗</a><form action="/admin/logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><button class="quiet-button" type="submit">Sign out</button></form></div></header>
<main class="admin-main">
    <div class="page-heading"><div><p class="eyebrow">Dashboard</p><h1>Manage your website</h1><p>Edit page content, services, projects, and images.</p></div></div>
    <?php if ($error): ?><div class="notice error"><?= h($error) ?> <?php if (str_contains($error, 'MySQL')): ?><a href="/admin/setup.php">Database setup help</a><?php endif; ?></div><?php endif; ?>
    <?php if ($message): ?><div class="notice success"><?= h($message) ?></div><?php endif; ?>
    <?php if (!$error): ?>
    <nav class="section-nav" aria-label="Admin sections"><a href="#content">Page content</a><a href="#services">Services</a><a href="#projects">Projects</a><a href="#inquiries">Inquiries</a></nav>
    <section class="admin-section" id="content"><div class="section-title"><div><h2>Page content</h2><p>Text displayed across your frontend pages.</p></div></div>
        <form method="post" enctype="multipart/form-data" class="editor-form"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><input type="hidden" name="action" value="save_settings">
            <fieldset><legend>Shared</legend><div class="form-grid"><label>Site name<input name="brand_name" value="<?= h($settings['brand_name']) ?>"></label></div></fieldset>
            <fieldset><legend>Home page</legend><div class="form-grid"><label>Availability status<input name="home_status" value="<?= h($settings['home_status']) ?>"></label><label>Call to action label<input name="home_cta" value="<?= h($settings['home_cta']) ?>"></label><label>Recent work heading<input name="recent_title" value="<?= h($settings['recent_title']) ?>"></label><label class="wide">Headline<input name="home_title" value="<?= h($settings['home_title']) ?>"></label><label class="wide">Introduction<textarea name="home_intro" rows="3"><?= h($settings['home_intro']) ?></textarea></label><label class="wide">Hero portrait<input type="file" name="home_hero_image_file" accept="image/jpeg,image/png,image/webp,image/gif"><small>Upload to replace the current image (max 8 MB).</small></label><input type="hidden" name="home_hero_image" value="<?= h($settings['home_hero_image']) ?>"></div></fieldset>
            <fieldset><legend>Services page</legend><div class="form-grid"><label>Eyebrow<input name="services_eyebrow" value="<?= h($settings['services_eyebrow']) ?>"></label><label>Page title<input name="services_title" value="<?= h($settings['services_title']) ?>"></label><label>Tools section title<input name="stack_title" value="<?= h($settings['stack_title']) ?>"></label><label class="wide">Tools and technologies <small>One item per line</small><textarea name="stack_tools" rows="5"><?= h($settings['stack_tools']) ?></textarea></label></div></fieldset>
            <fieldset><legend>Projects page</legend><div class="form-grid"><label>Eyebrow<input name="projects_eyebrow" value="<?= h($settings['projects_eyebrow']) ?>"></label><label>Page title<input name="projects_title" value="<?= h($settings['projects_title']) ?>"></label></div></fieldset>
            <fieldset><legend>About page</legend><div class="form-grid"><label class="wide">Biography paragraph 1<textarea name="about_bio_1" rows="4"><?= h($settings['about_bio_1']) ?></textarea></label><label class="wide">Biography paragraph 2<textarea name="about_bio_2" rows="4"><?= h($settings['about_bio_2']) ?></textarea></label><label>Location<input name="about_location" value="<?= h($settings['about_location']) ?>"></label><label>Availability<input name="about_availability" value="<?= h($settings['about_availability']) ?>"></label><label>Experience<input name="about_experience" value="<?= h($settings['about_experience']) ?>"></label></div></fieldset>
            <fieldset><legend>Contact page</legend><div class="form-grid"><label>Heading<input name="contact_title" value="<?= h($settings['contact_title']) ?>"></label><label>Email<input type="email" name="contact_email" value="<?= h($settings['contact_email']) ?>"></label><label class="wide">Introduction<textarea name="contact_intro" rows="3"><?= h($settings['contact_intro']) ?></textarea></label><label>LinkedIn URL<input type="url" name="linkedin_url" value="<?= h($settings['linkedin_url']) ?>"></label><label>WhatsApp number<input name="whatsapp_number" value="<?= h($settings['whatsapp_number']) ?>" placeholder="+36 30 625 7850"></label></div></fieldset>
            <button type="submit">Save all page content</button>
        </form>
    </section>
    <section class="admin-section" id="services"><div class="section-title"><div><h2>Services</h2><p>Add, edit, or remove service cards.</p></div></div>
        <div class="record-list">
        <?php foreach ($services as $service): ?><form method="post" class="record-form"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><input type="hidden" name="action" value="save_service"><input type="hidden" name="id" value="<?= (int)($service['id'] ?? 0) ?>"><div class="form-grid"><label>Title<input name="title" required value="<?= h($service['title']) ?>"></label><label>Order<input type="number" name="position" value="<?= (int)($service['position'] ?? 0) ?>"></label><label class="wide">Description<textarea name="description" rows="2" required><?= h($service['description']) ?></textarea></label></div><div class="record-actions"><button type="submit">Save service</button><?php if (!empty($service['id'])): ?><button class="danger-button" type="submit" formaction="/admin/index.php" name="action" value="delete_service" formmethod="post" onclick="return confirm('Delete this service?')">Delete</button><input type="hidden" name="id" value="<?= (int)$service['id'] ?>"><?php endif; ?></div></form><?php endforeach; ?>
        </div>
        <form method="post" class="record-form"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><input type="hidden" name="action" value="save_service"><input type="hidden" name="position" value="999"><div class="section-title"><h3>Add a service</h3></div><div class="form-grid"><label>Title<input name="title" required></label><label>Description<textarea name="description" rows="2" required></textarea></label></div><button type="submit">Add service</button></form>
    </section>
    <section class="admin-section" id="projects"><div class="section-title"><div><h2>Projects</h2><p>Manage project cards and upload their images. JPG, PNG, WEBP, or GIF up to 8 MB.</p></div></div>
        <div class="record-list">
        <?php foreach ($projects as $project): ?><form method="post" enctype="multipart/form-data" class="record-form"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><input type="hidden" name="action" value="save_project"><input type="hidden" name="id" value="<?= (int)$project['id'] ?>"><input type="hidden" name="image_url" value="<?= h((string)$project['image_url']) ?>"><div class="form-grid"><label>Title<input name="title" required value="<?= h($project['title']) ?>"></label><label>Order<input type="number" name="position" value="<?= (int)$project['position'] ?>"></label><label class="wide">Description<textarea name="description" rows="3" required><?= h($project['description']) ?></textarea></label><label class="wide">Technologies <small>Comma-separated</small><input name="technologies" value="<?= h($project['technologies']) ?>"></label><label class="wide">Project image<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"><?php if ($project['image_url']): ?><small>Current: <a href="<?= h($project['image_url']) ?>" target="_blank" rel="noopener">view image</a> · <label class="inline-label"><input type="checkbox" name="remove_image" value="1"> Remove image</label></small><?php endif; ?></label></div><div class="record-actions"><button type="submit">Save project</button><button class="danger-button" type="submit" name="action" value="delete_project" onclick="return confirm('Delete this project?')">Delete</button></div></form><?php endforeach; ?>
        </div>
        <form method="post" enctype="multipart/form-data" class="record-form"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><input type="hidden" name="action" value="save_project"><input type="hidden" name="position" value="999"><div class="section-title"><h3>Add a project</h3></div><div class="form-grid"><label>Title<input name="title" required></label><label>Description<textarea name="description" rows="3" required></textarea></label><label>Technologies <small>Comma-separated</small><input name="technologies"></label><label>Project image<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label></div><button type="submit">Add project</button></form>
    </section>
    <section class="admin-section" id="inquiries"><div class="section-title"><div><h2>Quote inquiries</h2><p>Recent project requests from the contact form, newest first (up to 100).</p></div></div>
        <?php if (!$inquiries): ?><p class="empty-state">No project inquiries yet.</p><?php else: ?><div class="inquiry-list">
            <?php foreach ($inquiries as $inquiry): ?>
                <article class="inquiry-card">
                    <div class="inquiry-heading"><div><h3><?= h($inquiry['name']) ?></h3><a href="mailto:<?= h($inquiry['email']) ?>"><?= h($inquiry['email']) ?></a></div><time><?= h(date('M j, Y · H:i', strtotime($inquiry['created_at']))) ?></time></div>
                    <p class="inquiry-details"><?= nl2br(h($inquiry['details'])) ?></p>
                    <div class="inquiry-meta"><?php if ($inquiry['budget']): ?><span><strong>Budget:</strong> <?= h($inquiry['budget']) ?></span><?php endif; ?><?php if ($inquiry['timeline']): ?><span><strong>Timeline:</strong> <?= h($inquiry['timeline']) ?></span><?php endif; ?></div>
                    <form method="post" class="inquiry-status"><input type="hidden" name="csrf_token" value="<?= h(admin_csrf()) ?>"><input type="hidden" name="action" value="update_quote"><input type="hidden" name="id" value="<?= (int)$inquiry['id'] ?>"><label>Status<select name="status"><option value="new" <?= $inquiry['status'] === 'new' ? 'selected' : '' ?>>New</option><option value="contacted" <?= $inquiry['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option><option value="closed" <?= $inquiry['status'] === 'closed' ? 'selected' : '' ?>>Closed</option></select></label><button type="submit">Update status</button></form>
                </article>
            <?php endforeach; ?>
        </div><?php endif; ?>
    </section>
    <?php endif; ?>
</main></body></html>
