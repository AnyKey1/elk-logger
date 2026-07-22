# webmonet/elk-logger

Laravel package that ships logs to Elasticsearch through a custom Monolog channel and provides a request-logging middleware.

## Installation

In the host project's `composer.json` register the package via a path repository:

```json
{
    "repositories": [
        { "type": "path", "url": "packages/webmonet/elk-logger" }
    ],
    "require": {
        "webmonet/elk-logger": "*"
    }
}
```

Then run `composer require webmonet/elk-logger`.

The service provider is auto-discovered. Publish the config if you want to override defaults:

```bash
php artisan vendor:publish --tag=elk-logger-config
```

## Configuration

Driven by env variables (see `config/elk-logger.php`):

| Variable | Default | Description |
|---|---|---|
| `ELASTICSEARCH_HOST` | — | Comma-separated list of Elasticsearch hosts (e.g. `http://host:9200`) |
| `ELASTICSEARCH_USER` | — | Basic auth user |
| `ELASTICSEARCH_PASSWORD` | — | Basic auth password |
| `ELASTICSEARCH_INDEX_PREFIX` | `log` | Index prefix; final name is `{prefix}_{date}` |
| `ELASTICSEARCH_INDEX_DATE_FORMAT` | `Y_m_d` | `date()` format suffix |
| `ELASTICSEARCH_LOG_LEVEL` | `info` | Minimum Monolog level |
| `ELASTICSEARCH_IGNORE_ERRORS` | `true` | Suppress handler errors |
| `ELK_LOGGER_ASYNC` / `ELASTICSEARCH_ASYNC` | `false` | Enable asynchronous (buffered) log sending |
| `ELK_LOGGER_BUFFER_LIMIT` / `ELASTICSEARCH_BUFFER_LIMIT` | `0` | Max buffered logs (0 = unlimited, send all on shutdown) |
| `ELK_LOGGER_FLUSH_ON_OVERFLOW` / `ELASTICSEARCH_FLUSH_ON_OVERFLOW` | `false` | Flush buffer to ELK immediately when buffer limit is reached |
| `ELK_LOGGER_CHANNEL` | `elasticsearch` | Log channel name registered in `config/logging.php` |
| `ELK_LOGGER_DURATION_HEADER` | `Duration` | Response header read for `db-duration` |
| `ELK_LOGGER_REQUEST_MESSAGE` | `Incoming Request` | Log message used by request middleware |

The provider auto-registers a `logging.channels.{ELK_LOGGER_CHANNEL}` entry if missing, so the channel works without any change to `config/logging.php`.

## Usage

```php
use Illuminate\Support\Facades\Log;

Log::channel('elasticsearch')->info('user.logged_in', ['user_id' => $user->id]);
```

### Request logging middleware

Attach `Webmonet\ElkLogger\Http\Middleware\LogRequests` to a route group to log full request/response payloads.

```php
use Webmonet\ElkLogger\Http\Middleware\LogRequests;

Route::middleware([LogRequests::class])->group(function () {
    // routes
});
```

The middleware logs in `terminate()` so it runs after the response is sent. It reads a response header (`Duration` by default) and records it as `db-duration` in the payload — useful when an upstream layer (e.g. a stored procedure) measures its own time and exposes it via that header.
