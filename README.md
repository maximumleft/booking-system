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

# Система бронирования спортивной площадки

REST API для бронирования временных слотов на спортивной площадке.

## Установка и настройка

1. Клонируйте репозиторий
2. Установите зависимости: `composer install`
3. Скопируйте `.env.example` в `.env` и настройте базу данных
4. Запустите миграции и сиды: `php artisan migrate:fresh --seed`
5. Запустите сервер: `php artisan serve`

## Аутентификация

API использует простую аутентификацию по API токену. Токен должен передаваться в заголовке `Authorization`.

Пример:
```
Authorization: john_token_123456789
```

## Тестовые пользователи

После запуска сидов будут созданы следующие пользователи:

- **John Doe** (john@example.com) - токен: `john_token_123456789`
- **Jane Smith** (jane@example.com) - токен: `jane_token_987654321`
- **Bob Wilson** (bob@example.com) - токен: `bob_token_456789123`

## API Endpoints

### Получить список бронирований пользователя
```
GET /api/bookings
Authorization: your_api_token
```

### Создать новое бронирование
```
POST /api/bookings
Authorization: your_api_token
Content-Type: application/json

{
  "slots": [
    {
      "start_time": "2025-06-25T12:00:00",
      "end_time": "2025-06-25T13:00:00"
    },
    {
      "start_time": "2025-06-25T13:30:00",
      "end_time": "2025-06-25T14:30:00"
    }
  ]
}
```

### Получить конкретное бронирование
```
GET /api/bookings/{booking_id}
Authorization: your_api_token
```

### Обновить конкретный слот
```
PATCH /api/bookings/{booking_id}/slots/{slot_id}
Authorization: your_api_token
Content-Type: application/json

{
  "start_time": "2025-06-25T15:00:00",
  "end_time": "2025-06-25T16:00:00"
}
```

### Добавить новый слот к существующему бронированию
```
POST /api/bookings/{booking_id}/slots
Authorization: your_api_token
Content-Type: application/json

{
  "start_time": "2025-06-25T16:00:00",
  "end_time": "2025-06-25T17:00:00"
}
```

### Удалить бронирование
```
DELETE /api/bookings/{booking_id}
Authorization: your_api_token
```

## Бизнес-правила

1. **Временные слоты не должны пересекаться** - система проверяет конфликты на уровне всей площадки
2. **Слоты внутри одного бронирования не должны пересекаться**
3. **Пользователи могут управлять только своими бронированиями**
4. **Время начала должно быть в будущем**
5. **Время окончания должно быть после времени начала**

## Примеры ответов

### Успешное создание бронирования
```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "created_at": "2025-07-08T20:00:00.000000Z",
    "updated_at": "2025-07-08T20:00:00.000000Z",
    "slots": [
      {
        "id": 1,
        "booking_id": 1,
        "start_time": "2025-06-25T12:00:00.000000Z",
        "end_time": "2025-06-25T13:00:00.000000Z",
        "created_at": "2025-07-08T20:00:00.000000Z",
        "updated_at": "2025-07-08T20:00:00.000000Z"
      }
    ]
  }
}
```

### Ошибка конфликта времени
```json
{
  "error": "Time slot conflicts with existing booking"
}
```

### Ошибка авторизации
```json
{
  "error": "API token is required"
}
```

## Запуск тестов

```bash
php artisan test tests/Feature/BookingApiTest.php
```

Тесты покрывают:
- Создание бронирования с несколькими слотами
- Проверка конфликтов времени
- Обновление слотов
- Добавление слотов к существующему бронированию
- Авторизация и права доступа
