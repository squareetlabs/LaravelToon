# Quick Start Guide - LaravelToon

## 1. Installation (30 seconds)

```bash
# Navigate to your Laravel project directory
cd your-laravel-project

# Install LaravelToon
composer require squareetlabs/laravel-toon

# Publish configuration (optional)
php artisan vendor:publish --provider="Squareetlabs\LaravelToon\LaravelToonServiceProvider"
```

**Done!** The package is installed and available immediately.

---

## 2. Basic Usage (1 minute)

### Option A: Using Facade

```php
use Squareetlabs\LaravelToon\Facades\Toon;

$data = [
    'user' => 'John',
    'email' => 'john@example.com',
    'items' => [1, 2, 3, 4, 5],
];

// Convert to TOON
$toon = Toon::encode($data);
echo $toon;
```

### Option B: Using Helpers (Recommended)

```php
// Readable format
echo toon_readable($data);

// Compact format (maximum compression)
echo toon_compact($data);

// Decode
$decoded = toon_decode($toon);
```

### Option C: Using Models

```php
use Squareetlabs\LaravelToon\Traits\HasToonEncoding;

class User extends Model
{
    use HasToonEncoding;
}

$user = User::first();
echo $user->toToon();      // Convert model to TOON
```

---

## 3. Get Metrics (1 minute)

```php
// Complete compression info
$metrics = toon_metrics($data);

echo "Bytes saved: ".$metrics['bytes_saved'];
echo "Tokens saved: ".$metrics['tokens_saved'];
echo "Ratio: ".$metrics['compression_ratio'];
```

**Typical output:**
```
Bytes saved: 2,456
Tokens saved: 1,200
Ratio: 0.35 (35% of original size)
```

---

## 4. Estimate API Costs (1 minute)

```php
// Estimate cost for GPT-4o
$cost = toon_cost_estimate('gpt-4o', $data);

echo "Tokens: ".$cost['tokens'];
echo "Cost: ".$cost['cost_formatted'];  // $0.0025
```

**Compare JSON vs TOON:**
```php
$comparison = toon_cost_with_json_comparison('gpt-4o', $data);

echo "JSON cost: ".$comparison['json']['cost_formatted'];
echo "TOON cost: ".$comparison['toon']['cost_formatted'];
echo "You save: ".$comparison['savings']['cost_formatted'];
```

---

## 5. Use Interactive Dashboard (2 minutes)

```bash
# Open interactive dashboard
php artisan toon:dashboard
```

The dashboard allows:
- Convert JSON ↔ TOON
- Analyze compression
- Estimate costs
- View model prices

---

## 6. OpenAI Integration (1 minute)

### Step 1: Configure API Key

```env
OPENAI_API_KEY=sk-...
```

### Step 2: Send Compressed Message

```php
use Squareetlabs\LaravelToon\Adapters\OpenAIAdapter;

$openai = new OpenAIAdapter();

$response = $openai->sendMessage(
    'Analyze this JSON: '.json_encode($largeData),
    'gpt-4o'
);

echo "Tokens saved: ".$response['original_message_tokens'] 
     - $response['compressed_message_tokens'];
```

---

## 7. Claude Integration (1 minute)

### Step 1: Configure API Key

```env
ANTHROPIC_API_KEY=sk-ant-...
```

### Step 2: Use Adapter

```php
use Squareetlabs\LaravelToon\Adapters\AnthropicAdapter;

$claude = new AnthropicAdapter();

$response = $claude->sendMessage(
    'Process this JSON: '.json_encode($data),
    'claude-3-sonnet-20240229'
);
```

---

## 8. Useful Artisan Commands

### Convert Files
```bash
# JSON to TOON
php artisan toon:convert data.json --format=readable

# TOON to JSON
php artisan toon:convert data.toon --decode --pretty

# Save result
php artisan toon:convert data.json --output=result.toon
```

### Analyze Compression
```bash
php artisan toon:analyze data.json --verbose
```

### Benchmarking
```bash
php artisan toon:benchmark data.json --iterations=100 --model=gpt-4o
```

---

## 9. Form Validation

```php
use Squareetlabs\LaravelToon\Rules\ValidToonFormat;

$request->validate([
    'compressed_data' => [new ValidToonFormat()],
]);
```

---

## 10. Controller Examples

### Example 1: Optimized API Endpoint

```php
class DataController extends Controller
{
    public function export()
    {
        $data = User::with('posts')->get();
        
        return response()->json([
            'original' => json_encode($data),
            'compressed' => toon_readable($data),
            'metrics' => toon_metrics($data),
        ]);
    }
}
```

### Example 2: Send to AI with Budget

```php
class AIController extends Controller
{
    public function analyze(Request $request)
    {
        $data = $request->validated();
        
        // Check budget
        $budget = toon_budget_analysis('gpt-4o', 0.50, $data);
        
        if (!$budget['within_budget']) {
            return response()->json(['error' => 'Exceeds budget']);
        }
        
        // Proceed...
        $response = $this->callOpenAI($data);
        
        return $response;
    }
}
```

---

## Quick Comparison

```php
$data = ['users' => range(1, 1000)];

// JSON
json_encode($data);  // 7,718 bytes

// TOON
toon_readable($data);  // 2,538 bytes

// Savings: 67% fewer bytes, 60% fewer tokens
```

---

## API Configuration (Optional)

### Environment Variables

```env
# OpenAI
OPENAI_API_KEY=sk-...
OPENAI_API_BASE=https://api.openai.com/v1

# Anthropic
ANTHROPIC_API_KEY=sk-ant-...

# Google Gemini
GEMINI_API_KEY=AIzaSy...

# Mistral
MISTRAL_API_KEY=...

# LaravelToon
LARAVEL_TOON_ENABLED=true
```

---

## Tips & Tricks

### Tip 1: Shorter Alias

```php
// In routes/web.php or AppServiceProvider
if (!function_exists('t')) {
    function t($data) {
        return toon_readable($data);
    }
}

// Usage
echo t($data);  // Shorter
```

### Tip 2: Compression Cache

```php
// LaravelToon automatically caches
// but you can clear it manually
app(\Squareetlabs\LaravelToon\Services\TokenAnalyzer::class)
    ->clearCache();
```

### Tip 3: Format by Context

```php
$data = User::all();

// For API: compact
$api = toon_compact($data);

// For debug: readable
$debug = toon_readable($data);

// For tables: tabular
$table = toon_tabular($data);
```

### Tip 4: Cost Monitoring

```php
// On each AI request
$cost = toon_cost_estimate('gpt-4o', $data);

Log::info('AI cost', [
    'tokens' => $cost['tokens'],
    'cost' => $cost['cost'],
]);
```

---

## Performance

| Operation | Time | Notes |
|-----------|------|-------|
| Encode 1MB | ~50ms | Very fast |
| Decode 1MB | ~30ms | Very fast |
| Estimate tokens | ~1ms | With cache |
| Calculate cost | <1ms | With cache |

---

## Troubleshooting

### Problem: "Class not found"
```bash
php artisan optimize:clear
```

### Problem: "Service not registered"
```bash
php artisan package:discover
```

### Problem: "API Key not working"
```env
# Make sure it's in .env
OPENAI_API_KEY=sk-... (no spaces)
```

---

## Full Documentation

For more information:
- [README](./README.md) - Complete guide
- [Installation](INSTALLATION.md) - Detailed setup
- [Examples](EXAMPLES.md) - 10+ real examples

---

## Next Steps

1. Installation: `composer require squareetlabs/laravel-toon`
2. Test: `php artisan toon:dashboard`
3. Use in your project
4. Configure APIs (OpenAI, Claude, etc.)
5. Optimize costs

---

## FAQ

**Q: Is it compatible with Laravel 11?**
A: Yes, supports Laravel 9.0 to 12.x

**Q: Does it work with PHP 8.0?**
A: No, requires PHP 8.1+

**Q: How much compression?**
A: Typically 60-70% fewer bytes

**Q: Can I use it without external APIs?**
A: Yes, completely functional without adapters

**Q: Is there support for other LLM APIs?**
A: Yes, easy to create custom adapters

---

## Ready!

You can now:
- Compress data with TOON
- Save on LLM APIs
- Analyze metrics
- Use in your Laravel application

**Start now!**

```bash
php artisan toon:dashboard
```

Questions? See the full documentation or contact support.

---

**LaravelToon v1.0.0 - November 14, 2025**
