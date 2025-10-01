# AI Mode Configuration

This document explains how to configure the AI mode for testing purposes to avoid API billing charges.

## Environment Variables

Add the following environment variables to your `.env` file:

```env
# AI Configuration
# Set to false for testing without API calls (saves billing costs)
AI_MODE=true

# Google Gemini API Configuration
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

# AILabTools API Configuration (Fallback)
AILABTOOLS_API_KEY=imff7TwAtdh9xZku1PWRCMjN9CJqLFvr5BevQyKI3ZzEy6DTOrXVI8S4hWgo146U
```

## How to Use

### For Testing (No API Charges)
Set `AI_MODE=false` in your `.env` file:
```env
AI_MODE=false
```

When `AI_MODE=false`:
- No actual API calls are made
- The system returns the original image as the "processed" result
- Simulates a 2-second processing delay
- Logs indicate dummy processing is being used
- Perfect for testing UI and flow without incurring costs

### For Production (Real AI Processing)
Set `AI_MODE=true` in your `.env` file:
```env
AI_MODE=true
```

When `AI_MODE=true`:
- Real API calls are made to the configured AI service
- Actual emotion processing occurs
- Billing charges apply based on API usage

## Implementation Details

The system checks the `AI_MODE` environment variable in the `processWithAI()` method:

```php
private function processWithAI($imagePath, $emotion = 'happy')
{
    // Check if AI mode is disabled for testing
    if (env('AI_MODE', true) === false) {
        \Log::info('AI_MODE is disabled, using dummy processing for emotion: ' . $emotion);
        return $this->processWithDummyAI($imagePath, $emotion);
    }
    
    // ... real AI processing logic
}
```

## Dummy Processing

When `AI_MODE=false`, the `processWithDummyAI()` method:
1. Logs the dummy processing
2. Simulates a 2-second delay
3. Returns the original image as base64
4. Allows the UI to function normally

## Benefits

- **Cost Savings**: No API charges during testing
- **Faster Development**: No need to wait for real API responses
- **UI Testing**: Full UI flow can be tested without AI dependencies
- **Easy Switching**: Simple environment variable toggle
- **Logging**: Clear indication when dummy mode is active

## Usage Examples

### Testing the Campaign Flow
1. Set `AI_MODE=false` in `.env`
2. Run the campaign
3. Take selfies and proceed through all steps
4. Verify UI works correctly
5. Check logs to confirm dummy processing

### Production Deployment
1. Set `AI_MODE=true` in `.env`
2. Configure appropriate AI service credentials
3. Deploy to production
4. Monitor API usage and billing

## Troubleshooting

### Dummy Mode Not Working
- Check that `AI_MODE=false` is set correctly
- Verify the `.env` file is being loaded
- Check Laravel logs for "AI_MODE is disabled" messages

### Real AI Not Working
- Ensure `AI_MODE=true` is set
- Verify API credentials are correct
- Check that the appropriate AI service is enabled
- Review API response logs

## Security Note

Never commit real API keys to version control. Use environment variables and keep your `.env` file secure.

