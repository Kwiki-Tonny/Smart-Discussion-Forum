# Smart Discussion Forum

A discussion forum project (backend in Laravel). This README gives setup notes and the explicit versions used by the project.

## Key versions
- PHP: ^8.2 (project requires PHP 8.2 or newer)
- Laravel: ^12.0 (Laravel 12.x)
- MySQL: Not specified in repository files. The app uses DB_CONNECTION=mysql and DB_PORT=3306; recommend MySQL 8.0 (or MySQL 5.7+) for compatibility.

## Getting started (local / XAMPP)
1. Ensure PHP 8.2+, Composer, and MySQL are installed (XAMPP recommended).
2. Copy .env.example to .env and set DB_* values.
3. Run `composer install` and `npm install`.
4. Generate app key: `php artisan key:generate`.
5. Run migrations: `php artisan migrate`.
6. Start server: `php artisan serve` or use XAMPP Apache + PHP.

## Notes
- Database connection is configured in `backend-api-laravel/.env.example` (DB_PORT=3306).
- If an exact MySQL server version is required for deployment, confirm with the ops/team; repository does not pin a MySQL image/version.

Questions or corrections: ping the team.
