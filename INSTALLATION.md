# Installation Guide - LaravelToon

## Prerequisites

- **PHP**: 8.1 or higher
- **Laravel**: 9.0 or higher (up to 12.x)
- **Composer**: 2.0 or higher

## Installation Steps

### 1. Install the Package

```bash
composer require squareetlabs/laravel-toon
```

Laravel will automatically discover the Service Provider and register it.

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Squareetlabs\LaravelToon\LaravelToonServiceProvider" --tag=laravel-toon-config
```

This will create the `config/laravel-toon.php` file that you can customize.

### 3. Configure Environment Variables (Optional)

If you want to use LLM adapters, add to your `.env`:

```env
# OpenAI
OPENAI_API_KEY=sk-your-key-here
OPENAI_API_BASE=https://api.openai.com/v1

# Anthropic/Claude
ANTHROPIC_API_KEY=sk-ant-your-key-here

# Google Gemini
GEMINI_API_KEY=AIzaSyYourKeyHere

# Mistral
MISTRAL_API_KEY=your-key-here

# LaravelToon
LARAVEL_TOON_ENABLED=true
```

---

## Verify Installation

### Option 1: Using the Facade

```php
php artisan tinker

>>> use Squareetlabs\LaravelToon\Facades\Toon;
>>> Toon::encode(['test' => 'data'])
```

### Option 2: Using Helpers

```php
php artisan tinker

>>> toon(['test' => 'data'])
>>> toon_compact(['test' => 'data'])
>>> toon_readable(['test' => 'data'])
```

### Option 3: Running Artisan Commands

```bash
# View the interactive dashboard
php artisan toon:dashboard

# Run a benchmark
php artisan toon:benchmark --help
```

---

## Basic Usage

### In Eloquent Models

```php
use Squareetlabs\LaravelToon\Traits\HasToonEncoding;

class User extends Model
{
    use HasToonEncoding;
}

$user = User::first();
echo $user->toToon(); // Convert model to TOON
```

### In Controllers

```php
namespace App\Http\Controllers;

use Squareetlabs\LaravelToon\Facades\Toon;

class DataController extends Controller
{
    public function compress()
    {
        $data = ['users' => User::all()];
        
        return response()->json([
            'original' => json_encode($data),
            'compressed' => Toon::encode($data),
            'metrics' => Toon::getMetrics($data),
        ]);
    }
}
```

### In Validation

```php
use Squareetlabs\LaravelToon\Rules\ValidToonFormat;

$request->validate([
    'compressed_data' => [new ValidToonFormat()],
]);
```

---

## LLM Adapter Configuration

### OpenAI

```env
OPENAI_API_KEY=sk-...
```

```php
use Squareetlabs\LaravelToon\Adapters\OpenAIAdapter;

$adapter = new OpenAIAdapter();
$response = $adapter->sendMessage('Your message', 'gpt-4o');
```

### Anthropic/Claude

```env
ANTHROPIC_API_KEY=sk-ant-...
```

```php
use Squareetlabs\LaravelToon\Adapters\AnthropicAdapter;

$adapter = new AnthropicAdapter();
$response = $adapter->sendMessage('Your message', 'claude-3-sonnet-20240229');
```

### Google Gemini

```env
GEMINI_API_KEY=AIzaSy...
```

```php
use Squareetlabs\LaravelToon\Adapters\GeminiAdapter;

$adapter = new GeminiAdapter();
$response = $adapter->sendMessage('Your message', 'gemini-pro');
```

### Mistral

```env
MISTRAL_API_KEY=...
```

```php
use Squareetlabs\LaravelToon\Adapters\MistralAdapter;

$adapter = new MistralAdapter();
$response = $adapter->sendMessage('Your message', 'mistral-large-latest');
```

---

## Troubleshooting

### Error: "Service Provider not found"

Run:
```bash
php artisan optimize:clear
```

### Artisan commands not working

Make sure the package is installed:
```bash
php artisan package:discover
```

### LLM APIs not responding

1. Verify API keys are correctly configured in `.env`
2. Check network connectivity
3. Verify adapter is enabled in `config/laravel-toon.php`

### Special characters issues in TOON

Make sure your database and files use UTF-8:

```php
// In config/database.php
'charset' => 'utf8mb4',
'collation' => 'utf8mb4_unicode_ci',
```

---

## Next Steps

1. **Explore Dashboard**: `php artisan toon:dashboard`
2. **Read Documentation**: See [README](./README.md)
3. **Test Commands**: `php artisan toon:convert --help`
4. **Implement in Your App**: Use helpers and traits

---

## Support

If you encounter issues:

1. Check documentation in [README](./README.md)
2. Create an issue on GitHub
3. Contact support: [labs@squareet.com](mailto:labs@squareet.com)

---

**LaravelToon v1.0.0 - November 14, 2025**
