# AI API Configuration Guide

## Overview
The Snickers campaign now supports multiple AI APIs for emotion-based image processing. You can choose which API to use by setting environment variables.

## Available Options

### Option 1: Google Gemini Imagen API (Recommended)
**Best for**: High-quality image emotion editing using Google's latest AI
**Pros**: Excellent image quality, Google's advanced AI, good for emotion manipulation
**Cons**: Requires Google AI Studio access, paid service

**Setup**:
1. Sign up for [Google AI Studio](https://aistudio.google.com/)
2. Get your API key from the Gemini API section
3. Add to your `.env` file:
```env
USE_GOOGLE_GEMINI_API=true
GOOGLE_GEMINI_API_KEY=your_gemini_api_key_here
```

### Option 2: Replicate API
**Best for**: High-quality image emotion editing
**Pros**: Excellent image quality, reliable, good documentation
**Cons**: Paid service, requires API token

**Setup**:
1. Sign up at [replicate.com](https://replicate.com)
2. Get your API token
3. Find a suitable emotion editing model
4. Add to your `.env` file:
```env
USE_REPLICATE_API=true
REPLICATE_API_TOKEN=your_token_here
REPLICATE_EMOTION_MODEL_VERSION=your_model_version
```

### Option 2: Hugging Face API
**Best for**: Open-source models, cost-effective
**Pros**: Free tier available, many models
**Cons**: May require model-specific setup

**Setup**:
1. Sign up at [huggingface.co](https://huggingface.co)
2. Get your API token
3. Find an emotion editing model
4. Add to your `.env` file:
```env
USE_HUGGINGFACE_API=true
HUGGINGFACE_API_TOKEN=your_token_here
```

### Option 3: Google Cloud Vision API
**Best for**: Emotion detection (not image modification)
**Pros**: Reliable, good accuracy
**Cons**: Only detects emotions, doesn't modify images

**Setup**:
1. Set up Google Cloud project
2. Enable Vision API
3. Get API key
4. Add to your `.env` file:
```env
USE_GOOGLE_VISION_API=true
GOOGLE_VISION_API_KEY=your_api_key_here
```

### Option 4: Original API (Fallback)
**Best for**: Current working setup
**Pros**: Already configured
**Cons**: May have limitations

**Setup**:
```env
# Keep current AILABTOOLS_API_KEY
AILABTOOLS_API_KEY=imff7TwAtdh9xZku1PWRCMjN9CJqLFvr5BevQyKI3ZzEy6DTOrXVI8S4hWgo146U
```

## How to Switch APIs

1. **Choose your preferred API** from the options above
2. **Set the corresponding environment variable** to `true` in your `.env` file
3. **Add the required API credentials** to your `.env` file
4. **Restart your Laravel application**

## Example Configuration

For Google Gemini Imagen API:
```env
USE_GOOGLE_GEMINI_API=true
GOOGLE_GEMINI_API_KEY=AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

For Replicate API:
```env
USE_REPLICATE_API=true
REPLICATE_API_TOKEN=r8_1234567890abcdef
REPLICATE_EMOTION_MODEL_VERSION=stability-ai/stable-diffusion:27b93a2413e7f36cd83da926f3656280b2931564ff050bf9575f1fdf9bcdc33e
```

## Testing Your Setup

1. Take a selfie in the campaign
2. Check the logs for any API errors
3. Verify the processed image appears correctly

## Troubleshooting

- **API errors**: Check your API tokens and model versions
- **No image processing**: Ensure only one API is enabled at a time
- **Slow processing**: Some APIs are slower than others (Replicate can take 10-30 seconds)

## Cost Considerations

- **Replicate**: Pay per generation (~$0.01-0.10 per image)
- **Hugging Face**: Free tier available, then pay per request
- **Google Vision**: Pay per API call (~$0.0015 per image)
- **Original API**: Current pricing model
