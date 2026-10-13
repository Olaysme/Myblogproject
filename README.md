# YinkaPW portfolio site

For AwardSpace deployment, follow [DEPLOY_AWARDSPACE.md](DEPLOY_AWARDSPACE.md).

## MySQL setup

1. Create an empty MySQL database, for example `yinkapw` (`CREATE DATABASE yinkapw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`), and a user that can create tables and update rows.
2. Copy `.env.example` to `.env` and enter the database host, database name, username, and password. Keep `.env` private; it is ignored by Git.
3. Make sure PHP has the `pdo_mysql` extension enabled.
4. Start the PHP development server from this folder, for example `php -S localhost:8082`.
5. Open `/admin/setup.php` to create the first administrator account. Tables and initial page content are created automatically.
6. Sign in at `/admin/login.php`; manage page copy, services, projects, project images, the home portrait, and quote inquiries submitted from the Contact page.

Quote requests are stored in MySQL and can be tracked from the admin dashboard. Project and portrait uploads accept JPG, PNG, WEBP, and GIF up to 8 MB and are saved in `uploads/`. The frontend uses the MySQL records after setup and retains built-in defaults if the database is not yet connected.
# Myblogproject
