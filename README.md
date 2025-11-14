#  LaravelToon

**Token-Optimized Object Notation for Laravel**  Compress your AI prompts, reduce API costs, and optimize LLM contexts seamlessly.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/squareetlabs/laravel-toon.svg?style=for-the-badge&color=blueviolet)](https://packagist.org/packages/squareetlabs/laravel-toon)
[![Total Downloads](https://img.shields.io/packagist/dt/squareetlabs/laravel-toon.svg?style=for-the-badge&color=brightgreen)](https://packagist.org/packages/squareetlabs/laravel-toon)
![License: MIT](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-9%2B-orange?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue?style=for-the-badge&logo=php)
![AI Ready](https://img.shields.io/badge/AI%20Ready-ChatGPT%2C%20Claude%2C%20Gemini%2C%20Mistral-success?style=for-the-badge)

---

##  Overview

**LaravelToon** is a native Laravel package that integrates **TOON (Token-Oriented Object Notation)**  a compact and readable format designed to optimize token usage in LLM contexts.

### Why LaravelToon

-  **Cost Savings**: Reduces tokens by 60-70%, saving money on APIs
-  **Native Integration**: Service providers, Facades, Artisan commands ready to use
-  **Multi-LLM**: Supports OpenAI, Claude, Gemini, Mistral with ready-made adapters
-  **Deep Analysis**: Token analysis, compression metrics, cost estimation
-  **High Performance**: Integrated benchmarking and caching optimizations
-  **Interactive Dashboard**: CLI tool to explore and experiment

---

##  Quick Start

### Installation

```bash
composer require squareetlabs/laravel-toon
```

### Configuration (Optional)

```bash
php artisan vendor:publish --provider="Squareetlabs\LaravelToon\LaravelToonServiceProvider" --tag=laravel-toon-config
```

### Basic Usage

```php
use Squareetlabs\LaravelToon\Facades\Toon;

// Convert to TOON
$data = [
    'user' => 'John',
    'email' => 'john@example.com',
    'roles' => ['admin', 'user']
];

$toon = Toon::encode($data);
echo $toon;
// user: John
// email: john@example.com
// roles[2]: admin,user

// Or use helpers
echo toon_readable($data);      // Readable format
echo toon_compact($data);       // Compact format
echo toon_tabular($data);       // Tabular format
```

---

##  Documentation

### Full Guides

- [Quick Start Guide](./QUICKSTART.md) - Get started in 5 minutes
- [Installation Guide](./INSTALLATION.md) - Detailed setup and configuration
- [Examples](./EXAMPLES.md) - 10+ real-world usage examples
- [README](./README.md) - Complete reference


---

##  Main Features

### 1. Compression and Conversion

```php
// Complete compression analysis
$metrics = Toon::getMetrics($data);
echo "Bytes saved: " . $metrics['bytes_saved'] . "\n";
echo "Tokens saved: " . $metrics['tokens_saved'] . "\n";
echo "Ratio: " . $metrics['compression_ratio'] . "\n";

// Compare with JSON
$comparison = Toon::compareWithJson($data);
```

### 2. Token Analysis

```php
use Squareetlabs\LaravelToon\Services\TokenAnalyzer;

$analyzer = app(TokenAnalyzer::class);

// Estimate tokens
$tokens = $analyzer->estimate($content);
$comparison = $analyzer->compareJsonVsToon($data);

// Token budget
$budget = $analyzer->budgetTokens(10000, $data);
// ['max_tokens' => 10000, 'tokens_used' => 2500, 'within_budget' => true]
```

### 3. API Cost Calculation

```php
use Squareetlabs\LaravelToon\Services\CostCalculator;

$calculator = app(CostCalculator::class);

// Estimate cost for GPT-4o
$cost = $calculator->estimateCost('gpt-4o', $data, 'input');
// ['tokens' => 2500, 'cost' => 0.0625, 'cost_formatted' => '$0.0625']

// Compare JSON vs TOON cost
$comparison = $calculator->estimateWithJsonComparison('gpt-4o', $data);

// Compare prices across models
$models = $calculator->compareModels($data);
```

### 4. LLM API Adapters

```php
use Squareetlabs\LaravelToon\Adapters\OpenAIAdapter;

$openai = new OpenAIAdapter();

// Send compressed message to OpenAI
$response = $openai->sendMessage(
    'Analyze this compressed JSON...',
    'gpt-4o',
    ['temperature' => 0.7]
);

// Chat with compressed messages
$messages = [
    ['role' => 'user', 'content' => 'First message'],
    ['role' => 'assistant', 'content' => 'Response'],
];

$chatResponse = $openai->chat($messages, 'gpt-4o');
```

---

##  Artisan Commands

### toon:convert

Converts JSON files to TOON or vice versa.

```bash
# JSON to TOON
php artisan toon:convert data.json --format=readable

# TOON to JSON (decode)
php artisan toon:convert data.toon --decode --pretty

# Save to file
php artisan toon:convert data.json --output=compressed.toon
```

### toon:analyze

Analyzes compression and efficiency.

```bash
php artisan toon:analyze data.json --verbose
```

Shows:
- JSON vs TOON size
- Estimated tokens
- Reduction percentage
- Recommendations

### toon:benchmark

Runs performance and cost estimation benchmarks.

```bash
php artisan toon:benchmark data.json --iterations=100 --model=gpt-4o
```

Shows:
- Encoding/decoding time
- Size comparison
- Cost estimation

### toon:dashboard

Interactive dashboard to explore LaravelToon.

```bash
php artisan toon:dashboard
```

Allows:
- Convert JSON  TOON
- Analyze compression
- Estimate costs
- View model prices

---

## � Available Helpers

```php
// Encoding
toon($data)                          // Readable format
toon_compact($data)                  // Compact format
toon_readable($data)                 // Readable format
toon_tabular($data)                  // Tabular format
toon_convert($data, 'compact')       // With specified format

// Decoding
toon_decode($toon)                   // TOON to PHP

// Compression and Metrics
toon_compress($data)                 // Full compression
toon_metrics($data)                  // Detailed metrics
toon_compression_summary($data)      // Summary

// Tokens
toon_estimate_tokens($content)       // Estimate tokens
toon_compare_json_vs_toon($data)     // Compare JSON vs TOON
toon_analyze($content)               // Detailed analysis

// Benchmark
toon_benchmark($data, 100)           // Performance benchmark

// Costs
toon_cost_estimate('gpt-4o', $data)          // Estimate cost
toon_cost_compare_models($data)              // Compare models
toon_cost_with_json_comparison('gpt-4o', $data) // With JSON comparison
toon_budget_analysis('gpt-4o', 100, $data)  // Budget analysis
```

---

##  Integration with Eloquent Models

```php
use Squareetlabs\LaravelToon\Traits\HasToonEncoding;

class User extends Model
{
    use HasToonEncoding;
}

$user = User::first();

// Convert to TOON
echo $user->toToon();                    // Readable format
echo $user->toToonCompact();             // Compact format

// Get metrics
$metrics = $user->getToonMetrics();
$ratio = $user->getToonCompressionRatio();
```

---

##  Validation

```php
use Squareetlabs\LaravelToon\Rules\ValidToonFormat;

$request->validate([
    'compressed_data' => [new ValidToonFormat()],
]);
```

---

##  Advanced Configuration

The `config/laravel-toon.php` file allows you to configure:

```php
return [
    // Encoding options
    'encoding' => [
        'indent' => 2,
        'delimiter' => ',',
        'min_rows_to_tabular' => 2,
    ],

    // Token analysis
    'token_analysis' => [
        'enabled' => true,
        'estimate_method' => 'character_ratio',
        'cache_results' => true,
    ],

    // LLM model prices
    'cost_calculation' => [
        'models' => [
            'gpt-4o' => ['input' => 0.0025, 'output' => 0.010],
            'claude-3-sonnet' => ['input' => 0.003, 'output' => 0.015],
            // ... more models
        ],
    ],

    // Compression middleware
    'middleware' => [
        'auto_compress' => false,
        'min_response_size' => 1024,
    ],
];
```

---

##  Use Cases

### 1. ChatGPT Prompt Optimization

```php
$prompt = "Analyze this dataset with millions of records...";
$data = $largeDataset;

$optimized = [
    'system_message' => 'You are an expert data analyst',
    'user_prompt' => $prompt,
    'data' => toon_compact($data),
];

// Save 60% of tokens
$tokens = toon_estimate_tokens(json_encode($optimized));
```

### 2. RAG with Optimized Context

```php
$contextData = $database->search('query', 1000);

$ragPrompt = [
    'context' => toon_compact($contextData),
    'query' => 'User question',
];

$cost = toon_cost_estimate('gpt-4o', $ragPrompt);
// Significantly reduces costs
```

### 3. Cost Monitoring

```php
// In your controller
public function sendToAI(Request $request)
{
    $data = $request->validated();
    
    $budget = toon_budget_analysis('gpt-4o', 100, $data);
    
    if (!$budget['within_budget']) {
        return response()->json([
            'error' => 'Exceeds token budget'
        ]);
    }
    
    // Proceed...
}
```

---

##  Testing

```php
use Squareetlabs\LaravelToon\Facades\Toon;

class ToonTest extends TestCase
{
    public function test_compression_ratio()
    {
        $data = ['users' => range(1, 1000)];
        $ratio = Toon::calculateCompressionRatio($data);
        
        $this->assertLessThan(0.4, $ratio); // Less than 40%
    }
}
```

---

##  Environment Variables

```env
# LLM APIs
OPENAI_API_KEY=sk-...
OPENAI_API_BASE=https://api.openai.com/v1

ANTHROPIC_API_KEY=sk-ant-...

GEMINI_API_KEY=AIzaSy...

MISTRAL_API_KEY=...

# LaravelToon
LARAVEL_TOON_ENABLED=true
```

---

##  Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a branch for your feature (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

##  License

Distributed under the MIT License. See `LICENSE` for more information.

---

##  About

LaravelToon is developed and maintained by [SquareetLabs](https://squareet.com)

---

##  Support

For support, create an issue in the repository or contact [labs@squareet.com](mailto:labs@squareet.com)

---

**LaravelToon v1.0.0 - November 14, 2025**
