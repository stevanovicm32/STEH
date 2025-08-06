# Laravel Chat API

Real-time chat application API built with Laravel, featuring user authentication, room management, and message handling.

## Features

- **User Authentication**: Register, login, logout with Laravel Sanctum
- **Role-based Access**: Admin, Moderator, and User roles
- **Room Management**: Create, join, leave, and manage chat rooms
- **Real-time Messaging**: Send and receive messages in rooms
- **System Messages**: Admin-only system messages
- **Pagination & Filtering**: Advanced search and filtering capabilities
- **API Documentation**: Complete REST API with JSON responses

## Requirements

- PHP 8.1+
- Laravel 12.x
- MySQL/SQLite
- Composer

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/stevanovicm32/STEH.git
   cd STEH
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Login user |
| POST | `/api/logout` | Logout user |
| GET | `/api/me` | Get current user |
| POST | `/api/change-password` | Change password |

### Rooms

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/rooms` | List all rooms (with filters) |
| POST | `/api/rooms` | Create a new room |
| GET | `/api/rooms/{id}` | Get room details |
| PUT | `/api/rooms/{id}` | Update room |
| DELETE | `/api/rooms/{id}` | Delete room |
| POST | `/api/rooms/{id}/join` | Join room |
| POST | `/api/rooms/{id}/leave` | Leave room |
| GET | `/api/rooms/{id}/members` | Get room members |

### Messages

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/messages` | List messages (with filters) |
| POST | `/api/messages` | Send a message |
| GET | `/api/messages/{id}` | Get message details |
| PUT | `/api/messages/{id}` | Update message |
| DELETE | `/api/messages/{id}` | Delete message |
| GET | `/api/rooms/{id}/messages` | Get room messages |
| POST | `/api/rooms/{id}/system-message` | Send system message |

### Users

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/users` | List users (with filters) |
| GET | `/api/users/{id}` | Get user details |
| PUT | `/api/users/{id}` | Update user |
| DELETE | `/api/users/{id}` | Delete user |
| GET | `/api/users/{id}/rooms` | Get user's rooms |
| GET | `/api/users/{id}/messages` | Get user's messages |
| GET | `/api/users/{id}/statistics` | Get user statistics |
| GET | `/api/online-users` | Get online users |

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {your_token}
```

### Register User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

## Test Data

The application comes with pre-seeded test data:

### Test Users
- **Admin**: admin@example.com / password
- **Moderator**: moderator@example.com / password
- **Users**: john@example.com, jane@example.com, bob@example.com / password

### Test Rooms
- General Chat (public)
- Tech Talk (public)
- Music Lovers (public)
- Private Discussion (private)

## Features Implemented

### ✅ Minimum Requirements
- [x] Laravel application with API routes
- [x] Git version control with 10+ meaningful commits
- [x] Public GitHub repository
- [x] 3+ interconnected models (User, Room, Message)
- [x] 5+ different migration types
- [x] REST API with JSON responses
- [x] Resource routes + 3+ additional route types
- [x] User authentication (login, logout, register)
- [x] Protected routes for authenticated users
- [x] Error handling in JSON format

### ✅ Bonus Features
- [x] API pagination and filtering
- [x] Password change functionality
- [x] 3+ user roles (admin, moderator, user)
- [x] Seeders and factories for all models
- [x] Advanced search functionality
- [x] User statistics and online status

## Database Schema

### Users Table
- id, name, email, password, role, timestamps

### Rooms Table
- id, name, description, is_private, created_by, timestamps

### Messages Table
- id, content, user_id, room_id, is_system_message, timestamps

### User_Room Pivot Table
- user_id, room_id, joined_at, is_admin, timestamps

## Testing with Postman

1. Import the following collection into Postman
2. Set the base URL to `http://localhost:8000/api`
3. Use the authentication endpoints to get a token
4. Add the token to the Authorization header for protected routes

### Example Postman Collection

```json
{
  "info": {
    "name": "Laravel Chat API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"Test User\",\n  \"email\": \"test@example.com\",\n  \"password\": \"password123\",\n  \"password_confirmation\": \"password123\"\n}",
              "options": {
                "raw": {
                  "language": "json"
                }
              }
            },
            "url": {
              "raw": "{{base_url}}/register",
              "host": ["{{base_url}}"],
              "path": ["register"]
            }
          }
        },
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"admin@example.com\",\n  \"password\": \"password\"\n}",
              "options": {
                "raw": {
                  "language": "json"
                }
              }
            },
            "url": {
              "raw": "{{base_url}}/login",
              "host": ["{{base_url}}"],
              "path": ["login"]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api"
    }
  ]
}
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
