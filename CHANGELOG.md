# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-14

### Added

- Initial release of LaravelToon
- Full TOON (Token-Oriented Object Notation) encoding and decoding
- Service layer: ToonService, TokenAnalyzer, CompressionMetrics, CostCalculator
- Adapters for major LLM providers:
  - OpenAI (GPT-4o, GPT-4, GPT-3.5-turbo)
  - Anthropic/Claude (Claude 3 series)
  - Google Gemini (Gemini Pro)
  - Mistral AI
- Artisan commands:
  - `toon:convert` - Convert JSON to TOON and vice versa
  - `toon:analyze` - Analyze compression efficiency
  - `toon:benchmark` - Performance and cost benchmarking
  - `toon:dashboard` - Interactive dashboard for exploration
- Comprehensive helper functions for easy integration
- HasToonEncoding trait for Eloquent models
- CompressResponse middleware for automatic response compression
- ValidToonFormat validation rule
- Token estimation and analysis tools
- Cost calculation for multiple LLM models
- Budget analysis and forecasting
- Support for Laravel 9.0 to 12.x
- Support for PHP 8.1+
- Configuration file for customization
- Extensive documentation and examples
- Unit and feature tests

### Features

- **Compression Efficiency**: Reduces data size by 60-70% compared to JSON
- **Token Optimization**: Significant token savings for LLM APIs
- **Multiple Format Options**: Readable, Compact, and Tabular formats
- **Cost Estimation**: Calculate and compare API costs across models
- **Performance Benchmarking**: Built-in performance testing tools
- **Integration-Ready**: Works seamlessly with Laravel applications
- **AI-First Design**: Optimized for use with LLM contexts
- **Caching Support**: Token estimates and cost calculations caching
- **Developer-Friendly**: Extensive helpers, traits, and middleware

### Documentation

- README with quick start guide
- Installation guide with troubleshooting
- Comprehensive examples including real-world use cases
- API documentation via PHPDocs
- Configuration guide
- Changelog

---

## [Unreleased]

### Planned

- Vector store integration for RAG systems
- Streaming support for LLM responses
- Request/Response logging and monitoring
- Advanced token budgeting algorithms
- Database persistence for compression history
- Web UI dashboard
- CLI wizard for configuration
- Support for additional LLM providers (Cohere, Together AI, etc.)
- Batch processing optimizations
- Custom delimiters and encoding options

---

## Version Support

| Version | PHP | Laravel | Status |
|---------|-----|---------|--------|
| 1.0.x   | 8.1+ | 9-12    |  Active |

---

## Contributing

We welcome contributions!

## License

LaravelToon is open-sourced software licensed under the MIT license.

## About

LaravelToon is developed and maintained by [Squareetlabs](https://squareet.com)

---

For more information, see [README.md](./README.md) and [INSTALLATION.md](./INSTALLATION.md).

---

**LaravelToon v1.0.0 - November 14, 2025**
