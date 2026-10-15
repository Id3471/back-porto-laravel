<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Portfolio administration

The React portfolio uses this Laravel application to authenticate administrators and persist public portfolio content.

1. Configure the database and session storage in `.env`, set `APP_URL` to this backend's public URL, and set `FRONTEND_URL` to the exact origin serving the React site. Credentialed CORS is restricted to that single origin.
2. Set `FILESYSTEM_DISK=public` for CV uploads. On a local filesystem, run `php artisan storage:link` so the uploaded PDF can be downloaded.
3. Apply migrations with `php artisan migrate`, then create the first administrator with `php artisan admin:create`. The command prompts for a username and a password without writing credentials to source files.
4. Configure a real SMTP provider in `.env` using `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and the verified sender address `MAIL_FROM_ADDRESS`. Set `CONTACT_TO_EMAIL` to the inbox that should receive portfolio messages. Never commit SMTP credentials. The contact API returns an explicit configuration error until a valid recipient is set.
5. Set `VITE_API_URL` in the frontend build environment to this backend's URL. For local development, the Vite server proxies requests to `http://127.0.0.1:8000`; set `BACKEND_URL` to override it.
6. When the frontend and API are on different sites, configure HTTPS and `SESSION_SAME_SITE=none` with `SESSION_SECURE_COOKIE=true`. Browsers that block third-party cookies may require serving both applications from the same site (for example, through a reverse proxy).

All private `/admin-api` routes require both a valid Laravel session and an administrator account. State-changing requests also require the session's CSRF token.

The public contact form sends messages synchronously through the configured mail transport. It is rate-limited to three submissions per IP every ten minutes and includes server-side validation and a honeypot. `GET /cv` returns the PDF uploaded in the administration as a file download.

## Docker

The backend image uses PHP 8.3 with Apache and serves Laravel from `public/`.
Build it from this directory:

```bash
docker build -t portfolio-backend .
```

`docker/nginx.conf` is an optional reverse-proxy server configuration for an
Nginx container on the same Docker network. It forwards requests to a backend
service named `backend` on port 80. If your Compose service uses another name,
update the `proxy_pass` upstream to match it. Configure TLS at Nginx or at the
load balancer in front of it.

Configure the container's environment variables with your deployment values
(for example, by passing a local `.env` file that is kept out of the image):

```bash
docker run -d --name portfolio-backend -p 8000:80 --env-file .env \
  -v portfolio-db:/var/www/html/database/database.sqlite \
  -v portfolio-uploads:/var/www/html/storage/app/public \
  portfolio-backend
```

The named volumes persist the SQLite database and uploaded CVs when the
container is replaced. Keep `DB_CONNECTION=sqlite` for this setup; if using
another database, configure its connection settings and remove the SQLite
database volume. After starting the container, run migrations and create the
initial administrator:

```bash
docker exec -it portfolio-backend php artisan migrate --force
docker exec -it portfolio-backend php artisan admin:create
```

Set a valid `APP_KEY`, `APP_URL`, `FRONTEND_URL`, and mail configuration in the
container environment before deploying. Do not pass secrets as Docker build
arguments or copy `.env` into the image.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
