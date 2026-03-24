# Real Estate CRM System

A scalable, production-ready CRM system built with Laravel for real estate companies.

## Features

- **Role-Based Access Control**: 5 distinct user roles (Admin, CRM, Sales Manager, Sales Executive, Telecaller)
- **Real-Time Notifications**: WebSocket-based real-time updates
- **RESTful APIs**: Mobile app ready with Laravel Sanctum authentication
- **Clean Architecture**: MVC pattern with Event-Driven architecture
- **Scalable Design**: Optimized for 100+ users, ready for 1000+

## Tech Stack

- **Backend**: Laravel 10.x
- **Database**: MySQL
- **Real-Time**: Laravel WebSockets (Pusher)
- **Queue**: Redis
- **Authentication**: Laravel Sanctum

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure
4. Generate app key: `php artisan key:generate`
5. Run migrations: `php artisan migrate`
6. Seed database: `php artisan db:seed`
7. Install frontend dependencies: `npm install`
8. Build assets: `npm run build`
9. Start server: `php artisan serve`

## User Roles

1. **ADMIN**: Full system access
2. **CRM**: Operations management
3. **SALES MANAGER**: Team lead management
4. **SALES EXECUTIVE**: Lead management
5. **TELECALLER**: Call management

## API Documentation

All APIs are prefixed with `/api/v1/` and require Sanctum authentication.

## Scaling Best Practices

See `SCALING.md` for detailed scaling guidelines.

## License

MIT

