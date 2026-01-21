# Website Subscription Platform

### 🚀 Quick Start

```bash
php artisan migrate          # Creates tables
php artisan db:seed          # Adds test data
php artisan serve            # http://127.0.0.1:8000
php artisan queue:work       # Email queue
```

### 📱 API Endpoints

| Method | Endpoint                  | Description                       |
| ------ | ------------------------- | --------------------------------- |
| GET    | /api/websites             | List websites + counts            |
| GET    | /api/subscribers          | List subscribers + websites       |
| POST   | /api/posts                | Create post |
| POST   | /api/websites/1/subscribe | Add subscriber to website |

### ✉️ Workflow

```bash
POST /api/posts              # Create post
php artisan websites:notify-subscribers  # Queue emails
php artisan queue:work       # Send emails
```

### 📁 Postman

postman/ folder included