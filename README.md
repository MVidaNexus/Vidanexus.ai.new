## VidaNexus AI

Multi-tool AI marketplace built on Laravel 11.

### Documentation

- [Authentication & password recovery](docs/AUTHENTICATION.md)
- [Social login (Google, GitHub, Microsoft)](docs/SOCIAL_LOGIN.md)
- [AI provider architecture & fallback chain](docs/AI_PROVIDERS.md)
- [AI security: prompt-injection defense](docs/SECURITY.md)
- [Platform enhancement roadmap](docs/PLATFORM_ENHANCEMENT_ROADMAP.md)

## Docker Compose (Prod + Dev)

### Production-like stack

Use only the base file:

```bash
docker compose -f docker-compose.yml up --build -d
```

Notes:
- Uses built assets from the image (no Vite HMR container).
- No source bind mount (closer to production runtime behavior).
- Default web port is `80` (`APP_PORT` can override).

### Local development stack

Use the dedicated dev file:

```bash
docker compose -f docker-compose.dev.yml up --build -d
```

This enables:
- Source bind mount for live code edits
- `vite` dev server (`5173`) for HMR
- `composer` helper service
- Local-friendly app env overrides

### 1) Configure local env

- Copy `.env.example` to `.env` if needed.
- For Docker networking, use:
  - `DB_CONNECTION=mysql`
  - `DB_HOST=mysql`
  - `DB_PORT=3306`
  - `DB_DATABASE=vidanexus`
  - `DB_USERNAME=vidanexus`
  - `DB_PASSWORD=secret`
  - `REDIS_HOST=redis`
- Set `APP_URL=http://localhost:8000` for local dev.

### 2) Build and start the stack

Services (dev):
- App: [http://localhost:8000](http://localhost:8000) (or your `APP_PORT`)
- Vite HMR: [http://localhost:5173](http://localhost:5173)

### 3) Install dependencies and initialize app

```bash
docker compose -f docker-compose.dev.yml run --rm composer install
docker compose -f docker-compose.dev.yml exec app php artisan key:generate
docker compose -f docker-compose.dev.yml exec app php artisan migrate
```

### 4) Useful commands

```bash
docker compose -f docker-compose.dev.yml logs -f app vite
docker compose -f docker-compose.dev.yml exec app php artisan test
docker compose -f docker-compose.dev.yml --profile horizon up -d horizon
docker compose -f docker-compose.dev.yml down
```

---

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

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
