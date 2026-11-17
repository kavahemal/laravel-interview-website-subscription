# Laravel Interview: Website Subscription Platform

A RESTful API (no front end) built as a technical interview task. Users can subscribe to websites, and when a new post is published on a website, every subscriber receives an email with the post title and description.

## Features

- Multiple websites; a subscriber can subscribe to any number of them.
- Endpoint to create a post for a website.
- Endpoint to subscribe an email to a website, with validation (duplicate subscription returns `409`).
- Artisan command checks all websites and queues emails for posts that haven't been sent yet.
- Emails are sent in the background through the queue (one job per subscriber).
- Seeders for websites and subscribers; Postman collection included.

## How duplicate emails are prevented

- One `post_notifications` row per post and subscriber (unique index), created in bulk chunks.
- Each job marks its row `sent_at` before sending, only if still empty, so the same email is never sent twice; failed sends are retried.

## Tech Stack

| Layer    | Technology         |
| -------- | ------------------ |
| Backend  | PHP 8.2+, Laravel 12 |
| Database | MySQL              |
| Queue    | Laravel database queue |

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL or MariaDB (WAMP, XAMPP, or Laragon)

## Installation

```bash
git clone https://github.com/kavahemal/laravel-interview-website-subscription.git
cd laravel-interview-website-subscription
composer install
cp .env.example .env
php artisan key:generate
```

Create an empty database, then update `.env`:

```
DB_DATABASE=website_subscription
DB_USERNAME=root
DB_PASSWORD=
```

Emails are written to `storage/logs/laravel.log` by default (`MAIL_MAILER=log`). Set SMTP details in `.env` to send real emails.

```bash
php artisan migrate --seed   # creates tables + test data (websites, subscribers)
php artisan serve            # http://127.0.0.1:8000
php artisan queue:work       # processes the email queue
```

## API Endpoints

| Method | Endpoint                          | Body                                  | Description                         |
| ------ | --------------------------------- | ------------------------------------- | ----------------------------------- |
| GET    | /api/websites                     | -                                     | List websites with post and subscriber counts |
| GET    | /api/subscribers                  | -                                     | List subscribers with their websites |
| POST   | /api/posts                        | `website_id`, `title`, `description`  | Create a post for a website         |
| POST   | /api/websites/{website}/subscribe | `email`, `name` (optional)            | Subscribe an email to a website     |

Responses: `201` created, `409` already subscribed, `422` validation error, `404` website not found. All responses are JSON.

## Sending Emails

```bash
php artisan websites:notify-subscribers   # queues emails for all unsent posts
php artisan queue:work                    # sends them
```

## Project Structure (custom code)

```
app/Console/Commands/WebsitesNotifySubscribers.php   # finds unsent posts, queues jobs
app/Jobs/SendPostNotifications.php                   # sends one email to one subscriber
app/Http/Controllers/Api/                            # PostController, SubscriptionController, WebsiteController
app/Http/Resources/                                  # API resources
app/Mail/NewPostNotification.php                     # email with post title and description
app/Models/                                          # Website, Subscriber, Subscription, Post, PostNotification
database/migrations/                                 # websites, subscribers, subscriptions, posts, post_notifications
database/seeders/                                    # WebsiteSeeder, SubscriberSeeder
resources/views/mail/new-post.blade.php              # email template
```

## Postman

Import `postman/subscription-api.json` into Postman.
