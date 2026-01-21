# PHP_Laravel12_Translate_Data_Language_Wise_Using_API

## Project Overview

This Laravel 12 project demonstrates how to implement **automatic multi-language translation** using the **Google Translate API**. The system automatically translates content into **English, Hindi, and Gujarati** without any manual translation work and exposes clean RESTful APIs for managing translated content.

---

## Features

* Automatic translation using Google Translate API
* Supports three languages: English (en), Hindi (hi), Gujarati (gu)
* RESTful API endpoints for CRUD operations
* Database storage for translations
* JSON-based translation storage
* Caching to reduce API calls
* Fallback to original text on failure
* On-the-fly translation if missing

---

## Prerequisites

* PHP 8.1 or higher
* Composer
* Laravel 12
* MySQL or supported database
* Internet connection (Google Translate)

---

## Installation Steps

### Step 1: Create Laravel Project

```bash
laravel new laravel-translate-project
cd laravel-translate-project
```

### Step 2: Install Required Packages

```bash
composer require google/cloud-translate
composer require guzzlehttp/guzzle
```

### Step 3: Configure Environment

Update `.env` file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_translate
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
```

### Step 4: Run Database Migrations

```bash
php artisan migrate
```

### Step 5: Create Required Files

Add the following files:

* `app/Services/GoogleTranslateService.php`
* `app/Models/Post.php`
* `app/Models/PostTranslation.php`
* `app/Http/Controllers/PostController.php`

### Step 6: Set Up Routes

Update `routes/api.php` with translation API routes.

### Step 7: Seed Sample Data

```bash
php artisan db:seed --class=PostSeeder
```

---

## Project Structure

```
app/
├── Services/GoogleTranslateService.php
├── Models/
│   ├── Post.php
│   └── PostTranslation.php
└── Http/Controllers/PostController.php

database/
├── migrations/
│   ├── create_posts_table.php
│   └── create_post_translations_table.php
└── seeders/PostSeeder.php

routes/
└── api.php
```

---

## API Endpoints

### 1. Get All Posts

* **URL:** `/api/v1/posts`
* **Method:** GET
* **Query Params:** `lang` (en, hi, gu)

### 2. Create New Post

* **URL:** `/api/v1/posts`
* **Method:** POST

```json
{
  "title": "Post Title",
  "content": "Post content here"
}
```

### 3. Get Single Post

* **URL:** `/api/v1/posts/{id}`
* **Method:** GET
* **Query Params:** `lang`

### 4. Update Post

* **URL:** `/api/v1/posts/{id}`
* **Method:** PUT / PATCH

### 5. Delete Post

* **URL:** `/api/v1/posts/{id}`
* **Method:** DELETE

### 6. Translate Specific Post

* **URL:** `/api/v1/posts/{id}/translate`
* **Method:** GET
* **Query Params:** `locale`

### 7. Get Supported Languages

* **URL:** `/api/v1/languages`
* **Method:** GET

---
## Screenshot
<img width="1395" height="938" alt="image" src="https://github.com/user-attachments/assets/03ac2d38-c77f-4d7c-ae9b-e14d829d6b9d" />
<img width="1382" height="937" alt="image" src="https://github.com/user-attachments/assets/cceec077-6f4b-43d1-a14d-4bfce5d1ba86" />
<img width="1378" height="885" alt="image" src="https://github.com/user-attachments/assets/7dda08da-b88a-4ef5-8b53-0940e7eb6d0b" />

## Database Schema

### Posts Table

* id
* title (English)
* content (English)
* translations (JSON)
* created_at
* updated_at

### Post Translations Table

* id
* post_id
* locale
* title
* content
* created_at
* updated_at

---

## How It Works

### Translation Flow

1. Post is created in English
2. System auto-translates to Hindi and Gujarati
3. Translations saved in JSON + translations table
4. Cached for 30 days
5. Requested language served from cache or DB
6. Missing translations generated on-the-fly

---

## Google Translate Service

`GoogleTranslateService` handles:

* Language validation
* Bulk translations
* Error handling
* Caching
* Rate limit prevention

---

## Testing the API

### Using cURL

```bash
curl -X POST http://localhost:8000/api/v1/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Post","content":"This is test content"}'
```

```bash
curl "http://localhost:8000/api/v1/posts?lang=hi"
```

```bash
curl "http://localhost:8000/api/v1/posts/1/translate?locale=gu"
```

### Using Postman

* Base URL: `http://localhost:8000/api/v1`
* Use endpoints listed above

---

## Configuration Options

### Supported Languages

```php
protected $supportedLanguages = [
    'en' => 'English',
    'hi' => 'Hindi',
    'gu' => 'Gujarati'
];
```

### Cache Duration

Change cache TTL inside `GoogleTranslateService.php`.

---

## Error Handling

* Translation failures return original text
* Invalid language defaults to English
* API errors handled gracefully
* Logged via Laravel logger

---

## Performance Optimization

* Translation caching
* Database indexing
* Bulk translation support
* Lazy loading translations

---

## Limitations

* Free Google Translate API limits
* Requires internet connection
* Machine translation accuracy
* Rate limits on heavy traffic

---

## License

This project is open-source and licensed under the MIT License.

