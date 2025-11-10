# Sistema de Gestión Académica - 2do Parcial

Sistema de gestión académica desarrollado con Laravel 11 y PostgreSQL.

## Requisitos

- PHP 8.2 o superior
- PostgreSQL 13 o superior
- Composer
- Node.js y NPM (para assets)

## Instalación Local

1. Clonar el repositorio
2. Instalar dependencias:
```bash
composer install
npm install
```

3. Configurar variables de entorno:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar base de datos en `.env`

5. Ejecutar migraciones:
```bash
php artisan migrate
```

6. Asegurar directorios de almacenamiento:
```bash
php artisan storage:ensure
```

7. Compilar assets:
```bash
npm run build
```

8. Iniciar servidor:
```bash
php artisan serve
```

## Despliegue en Producción (Railway)

Después de cada despliegue en Railway, ejecutar:

```bash
php artisan storage:ensure
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Importante**: El comando `storage:ensure` crea los directorios necesarios para la carga masiva de archivos y otras funcionalidades.

## Características Principales

- **CU04-CU06**: Gestión de Docentes
- **CU17**: Generación de Reportes
- **CU18**: Gestión de Usuarios con Roles
- **CU20**: Carga Masiva de Usuarios (Excel/CSV)
- Sistema de autenticación
- Gestión de períodos académicos
- Control de horarios y grupos
- Bitácora de actividades

## Tecnologías Utilizadas

- Laravel 11
- PostgreSQL
- Bootstrap 5
- Font Awesome
- FastExcel (carga masiva)
- DomPDF (generación de reportes)

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

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
