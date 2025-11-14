# Usage Examples - LaravelToon

## Table of Contents

1. [Basic Compression](#basic-compression)
2. [Token Analysis](#token-analysis)
3. [LLM Integration](#llm-integration)
4. [Cost Calculation](#cost-calculation)
5. [Eloquent Models](#eloquent-models)
6. [Compression Middleware](#compression-middleware)
7. [Real-World Cases](#real-world-cases)

---

## Basic Compression

### Example 1: Convert JSON to TOON

```php
use Squareetlabs\LaravelToon\Facades\Toon;

$userData = [
    'id' => 1,
    'name' => 'John Garcia',
    'email' => 'john@example.com',
    'roles' => ['admin', 'user', 'moderator'],
    'profile' => [
        'bio' => 'Laravel Developer',
        'country' => 'USA',
        'verified' => true,
    ],
];

// Readable format (recommended)
$toonReadable = Toon::encode($userData);
// user: John Garcia
// id: 1
// email: john@example.com
// roles[3]: admin,user,moderator
// profile:
//   bio: Laravel Developer
//   country: USA
//   verified: true

// Compact format (maximum compression)
$toonCompact = Toon::encodeCompact($userData);

// Tabular format (for uniform arrays)
$toonTabular = Toon::encodeTabular($userData);
```

### Example 2: Decode TOON

```php
$toon = <<<TOON
id: 1
name: John Garcia
email: john@example.com
TOON;

$decoded = Toon::decode($toon);
// Returns:
// ['id' => 1, 'name' => 'John Garcia', 'email' => 'john@example.com']
```

### Example 3: Configuration Files

```php
// Use helpers for more concise code
$data = [...];

$readable = toon_readable($data);  // Readable
$compact = toon_compact($data);    // Compact
$tabular = toon_tabular($data);    // Tabular
$decoded = toon_decode($toon);     // Decode
```

---

## Token Analysis

### Example 1: Estimate Tokens

```php
use Squareetlabs\LaravelToon\Services\TokenAnalyzer;

$analyzer = app(TokenAnalyzer::class);

$content = "This is a long content with many words...";

// Estimate tokens in content
$tokens = $analyzer->estimate($content);
// 125

// Detailed analysis
$analysis = $analyzer->analyze($content);
// [
//     'length_chars' => 312,
//     'length_words' => 45,
//     'tokens_estimated' => 125,
//     'chars_per_token' => 2.5,
// ]
```

### Example 2: Compare JSON vs TOON

```php
$data = [
    'users' => [
        ['id' => 1, 'name' => 'John'],
        ['id' => 2, 'name' => 'Maria'],
        ['id' => 3, 'name' => 'Carlos'],
    ],
];

$comparison = $analyzer->compareJsonVsToon($data);
// [
//     'json_tokens' => 245,
//     'toon_tokens' => 98,
//     'tokens_saved' => 147,
//     'percent_saved' => 60.0,
//     'efficiency_ratio' => 0.4,
// ]
```

### Example 3: Token Budget

```php
// Does it fit in 10,000 tokens?
$budget = $analyzer->budgetTokens(10000, $largeDataset);

if ($budget['within_budget']) {
    // Process
} else {
    echo "You need ".$budget['tokens_used']." tokens";
    echo "Available: ".$budget['tokens_available'];
}
```

---

## LLM Integration

### Example 1: OpenAI

```php
use Squareetlabs\LaravelToon\Adapters\OpenAIAdapter;

$openai = new OpenAIAdapter();

if (!$openai->isEnabled()) {
    throw new Exception('OpenAI is not configured');
}

// Send compressed message
$response = $openai->sendMessage(
    'Analyze this dataset: '.json_encode($largeData),
    'gpt-4o',
    ['temperature' => 0.7, 'max_tokens' => 2000]
);

if ($response['success']) {
    echo "Tokens saved: ".$response['original_message_tokens'] 
         - $response['compressed_message_tokens'];
}
```

### Example 2: Claude/Anthropic

```php
use Squareetlabs\LaravelToon\Adapters\AnthropicAdapter;

$claude = new AnthropicAdapter();

// Multi-turn compressed chat
$messages = [
    ['role' => 'user', 'content' => 'First question with lots of context...'],
    ['role' => 'assistant', 'content' => 'First response...'],
    ['role' => 'user', 'content' => 'Second question...'],
];

$response = $claude->chat(
    $messages,
    'claude-3-sonnet-20240229'
);

echo "Compressed messages: ".$response['messages_count'];
echo "Tokens saved: ".$response['tokens_saved'];
```

### Example 3: Google Gemini

```php
use Squareetlabs\LaravelToon\Adapters\GeminiAdapter;

$gemini = new GeminiAdapter();

$response = $gemini->sendMessage(
    'Process this JSON: '.json_encode($data),
    'gemini-pro'
);

// Gemini automatically receives compressed format
```

---

## Cost Calculation

### Example 1: Estimate Single Request Cost

```php
use Squareetlabs\LaravelToon\Services\CostCalculator;

$calculator = app(CostCalculator::class);

$dataset = [...]; // Large data

// Estimate cost for GPT-4o
$cost = $calculator->estimateCost('gpt-4o', $dataset, 'input');

echo "Tokens: ".$cost['tokens'];
echo "Cost: ".$cost['cost_formatted'];  // $0.0625

// Output:
// [
//     'model' => 'gpt-4o',
//     'tokens' => 2500,
//     'cost_per_million' => 0.0025,
//     'cost' => 0.00625,
//     'cost_formatted' => '$0.0063',
// ]
```

### Example 2: Compare Prices Across Models

```php
$comparison = $calculator->compareModels($dataset, 'input');

// Returns models sorted by cost (lowest to highest)
// [
//     'gpt-3.5-turbo' => ['cost' => 0.001],
//     'claude-3-haiku' => ['cost' => 0.0006],
//     'gemini-pro' => ['cost' => 0.0003],
//     'gpt-4o' => ['cost' => 0.00625],
// ]

echo "Cheapest model: ".$comparison['cheapest'];
echo "Most expensive: ".$comparison['most_expensive'];
```

### Example 3: JSON vs TOON - Cost Savings

```php
// Compare cost using JSON vs TOON
$jsonVsToon = $calculator->estimateWithJsonComparison(
    'gpt-4o',
    $largeDataset,
    'input'
);

// [
//     'json' => ['tokens' => 5000, 'cost' => 0.0125],
//     'toon' => ['tokens' => 1500, 'cost' => 0.00375],
//     'savings' => ['tokens' => 3500, 'cost' => 0.00875, 'percent' => 70.0],
// ]

echo "You save: ".$jsonVsToon['savings']['cost_formatted'];
```

### Example 4: Budget Analysis

```php
// How many requests can I make with $100?
$budget = $calculator->budgetAnalysis(
    'gpt-4o',
    100,        // $100 budget
    $dataset,
    'input'
);

// [
//     'budget' => 100.0,
//     'cost_per_request' => 0.00625,
//     'requests_affordable' => 16000,
//     'percent_budget_per_request' => 0.00625,
// ]

echo "I can make ".$budget['requests_affordable']." requests";
```

---

## Eloquent Models

### Example 1: Serialize Model to TOON

```php
use Squareetlabs\LaravelToon\Traits\HasToonEncoding;

class User extends Model
{
    use HasToonEncoding;
}

$user = User::with('posts', 'comments')->first();

// Convert to TOON
$toon = $user->toToon('readable');

// Compact format
$compact = $user->toToonCompact();

// Get metrics
$metrics = $user->getToonMetrics();
echo "Compression ratio: ".$metrics['compression_ratio'];
```

### Example 2: Send Model to LLM API

```php
use Squareetlabs\LaravelToon\Adapters\OpenAIAdapter;

$user = User::find(1);
$openai = new OpenAIAdapter();

// Send compressed model
$response = $openai->sendMessage(
    "Analyze this user: ".$user->toToonCompact(),
    'gpt-4o'
);
```

---

## Compression Middleware

### Example 1: Configure Middleware

In `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
];

protected $routeMiddleware = [
    // ...
    'compress' => \Squareetlabs\LaravelToon\Middleware\CompressResponse::class,
];
```

### Example 2: Use in Routes

```php
Route::get('/api/data', function () {
    return User::all();
})->middleware('compress');
```

### Example 3: Configuration in `.env`

```env
# In config/laravel-toon.php
LARAVEL_TOON_AUTO_COMPRESS=true
LARAVEL_TOON_MIN_RESPONSE_SIZE=1024
LARAVEL_TOON_COMPRESSION_THRESHOLD=50  # Minimum % to activate
```

---

## Real-World Cases

### Example 1: AI Recommendation System

```php
class RecommendationController extends Controller
{
    public function getRecommendations(Request $request)
    {
        $user = auth()->user();
        $userHistory = $user->purchaseHistory()->limit(100)->get();
        $similarUsers = $this->findSimilarUsers($user);
        
        // Compress data
        $contextData = toon_compact([
            'user' => $user->toArray(),
            'history' => $userHistory->toArray(),
            'similar_users' => $similarUsers->toArray(),
        ]);
        
        // Estimate cost
        $cost = toon_cost_estimate('gpt-4o', $contextData);
        
        if ($cost['cost'] > 0.01) {
            // Reduce context
            $contextData = toon_compact([
                'user_summary' => $user->only(['id', 'name', 'preferences']),
                'recent_purchases' => $userHistory->take(20)->toArray(),
            ]);
        }
        
        // Use adapter
        $ai = new OpenAIAdapter();
        $recommendations = $ai->sendMessage(
            "Based on this user: $contextData, recommend 5 products",
            'gpt-4o'
        );
        
        return $recommendations;
    }
}
```

### Example 2: Log Analysis with AI

```php
class LogAnalyzerController extends Controller
{
    public function analyzeLogs(Request $request)
    {
        $logs = Log::whereBetween('created_at', [
            now()->subDays(7),
            now()
        ])->get();
        
        // Group and compress
        $logsCompressed = toon_compact([
            'errors' => $logs->where('level', 'error')->groupBy('message'),
            'warnings' => $logs->where('level', 'warning')->count(),
            'stats' => [
                'total' => $logs->count(),
                'timespan' => '7 days',
            ],
        ]);
        
        // Analyze with Claude (better for long analysis)
        $claude = new AnthropicAdapter();
        $analysis = $claude->sendMessage(
            "Analyze these logs: $logsCompressed\n\nProvide insights and recommendations",
            'claude-3-sonnet-20240229'
        );
        
        return [
            'original_size' => strlen(json_encode($logs)),
            'compressed_size' => strlen($logsCompressed),
            'analysis' => $analysis,
            'cost_saved' => toon_cost_with_json_comparison(
                'claude-3-sonnet-20240229',
                $logs->toArray()
            ),
        ];
    }
}
```

### Example 3: RAG (Retrieval-Augmented Generation)

```php
class RAGController extends Controller
{
    public function query(Request $request)
    {
        $query = $request->input('query');
        
        // Search relevant documents
        $documents = Document::search($query)->take(20)->get();
        
        // Compress context
        $context = toon_compact(
            $documents->map(fn($doc) => [
                'title' => $doc->title,
                'content' => substr($doc->content, 0, 500),
                'relevance' => $doc->relevance_score,
            ])->toArray()
        );
        
        // Check if fits in budget
        $budget = toon_budget_analysis(
            'gpt-4o',
            0.50,  // $0.50 budget
            ['context' => $context, 'query' => $query]
        );
        
        if (!$budget['within_budget']) {
            // Reduce number of documents
            $documents = $documents->take(5);
            $context = toon_compact($documents->toArray());
        }
        
        // Generate response
        $openai = new OpenAIAdapter();
        $answer = $openai->sendMessage(
            "Based on this context: $context\n\nAnswer: $query",
            'gpt-4o'
        );
        
        return $answer;
    }
}
```

### Example 4: Benchmark and Optimization

```php
class OptimizationController extends Controller
{
    public function benchmark(Request $request)
    {
        $data = $request->input('data');
        
        $metrics = app(\Squareetlabs\LaravelToon\Services\CompressionMetrics::class)
            ->full($data);
        
        return [
            'summary' => [
                'json_size' => $metrics['compression']['json_size_bytes'],
                'toon_size' => $metrics['compression']['toon_size_bytes'],
                'reduction_percent' => $metrics['compression']['percent_reduced'],
            ],
            'tokens' => [
                'json' => $metrics['tokens']['json_tokens'],
                'toon' => $metrics['tokens']['toon_tokens'],
                'saved' => $metrics['tokens']['tokens_saved'],
                'saved_percent' => $metrics['tokens']['percent_saved'],
            ],
            'recommendations' => $metrics['recommendations'],
            'formats' => [
                'json' => $metrics['content']['original_json'],
                'toon' => $metrics['content']['compressed_toon'],
            ],
        ];
    }
}
```

---

These are complete examples! For more help, check:
- [README](./README.md)
- [Installation](./INSTALLATION.md)
- [CHANGELOG](./CHANGELOG.md)
- ---

**LaravelToon v1.0.0 - November 14, 2025**

