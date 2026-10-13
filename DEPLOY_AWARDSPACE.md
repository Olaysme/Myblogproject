# Deploying to AwardSpace

The app requires PHP 8.0 or newer and the `pdo_mysql` extension. Select the newest PHP version available for the domain in AwardSpace's control panel.

## Upload the app

Create or select the domain/subdomain, then upload the project files into that domain's document directory using the File Manager or FTP. Keep the root `index.php` at the document root. Upload the `about/`, `admin/`, `assets/`, `contact/`, `inc/`, `projects/`, `services/`, and `uploads/` directories, plus the root `.htaccess`. Do not upload `.git/` or the local development `.env`.

## Configure MySQL

In Control Panel > Database Manager, create a MySQL database. Open its Information tab and copy the exact Database Host, Port, Name, User, and Password. AwardSpace uses a separate database server, so use the host shown there; `localhost` and `127.0.0.1` will not connect to that hosted database.

Create a file named `.env` in the website document directory with these values from the panel:

```dotenv
DB_HOST=HOST_FROM_DATABASE_INFORMATION
DB_PORT=PORT_FROM_DATABASE_INFORMATION
DB_NAME=DATABASE_NAME_FROM_PANEL
DB_USER=DATABASE_USER_FROM_PANEL
DB_PASSWORD=DATABASE_PASSWORD_FROM_PANEL
```

Keep the root `.htaccess` uploaded; it blocks web requests for `.env` files.

## Finish setup

Visit `https://YOUR_DOMAIN/admin/setup.php` and create the first admin account. The app creates its tables and default content automatically. Then sign in at `/admin/login.php`. Project and portrait uploads go into `uploads/`, so PHP needs write permission to that directory.

Check the home page, `/services`, `/projects`, `/about`, `/contact`, and `/admin/login.php`. Submit a test quote inquiry and confirm it appears in the admin dashboard.
