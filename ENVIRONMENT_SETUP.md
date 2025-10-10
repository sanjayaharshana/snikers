# Environment Setup Guide

## Required Environment Variables

Create a `.env` file in your project root with the following configuration:

```env
APP_NAME=Snickers
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snickers
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# AI Configuration
# Set to false for testing without API calls (saves billing costs)
AI_MODE=true

# AILabTools API Configuration (Primary - Default)
USE_AILABTOOLS_API=true
AILABTOOLS_API_KEY=imff7TwAtdh9xZku1PWRCMjN9CJqLFvr5BevQyKI3ZzEy6DTOrXVI8S4hWgo146U

# Google Gemini API Configuration (Alternative)
USE_GOOGLE_GEMINI_API=false
GOOGLE_GEMINI_API_KEY=your_gemini_api_key_here

# Replicate API Configuration
USE_REPLICATE_API=false
REPLICATE_API_TOKEN=your_replicate_token_here
REPLICATE_EMOTION_MODEL_VERSION=your_model_version_here

# Hugging Face API Configuration
USE_HUGGINGFACE_API=false
HUGGINGFACE_API_TOKEN=your_huggingface_token_here

# Google Cloud Vision API Configuration
USE_GOOGLE_VISION_API=false
GOOGLE_VISION_API_KEY=your_vision_api_key_here
```

## AI API Configuration

### Default Configuration (AILabTools)
The system is now configured to use AILabTools API by default:

```env
USE_AILABTOOLS_API=true
AILABTOOLS_API_KEY=imff7TwAtdh9xZku1PWRCMjN9CJqLFvr5BevQyKI3ZzEy6DTOrXVI8S4hWgo146U
```

### Switch to Google Gemini
To use Google Gemini instead:

```env
USE_AILABTOOLS_API=false
USE_GOOGLE_GEMINI_API=true
GOOGLE_GEMINI_API_KEY=your_gemini_api_key_here
```

### Testing Mode (No API Calls)
To test without making API calls:

```env
AI_MODE=false
```

## Setup Instructions

1. Copy the environment variables above to your `.env` file
2. Update database credentials as needed
3. Generate application key: `php artisan key:generate`
4. Run migrations: `php artisan migrate`
5. Test AI configuration: `php test_ai_mode.php`

## Verification

After setup, you can verify the configuration by running:

```bash
php test_ai_mode.php
```

This will show you which AI services are enabled and the current configuration status.
