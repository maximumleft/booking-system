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
