# Snickers Campaign - AI-Powered Selfie Experience

A Laravel application that creates an interactive Snickers promotion campaign using AI Lab Tools APIs for facial expression analysis and face generation.

## Features

- 📱 **Phone Number Collection**: Collect user phone numbers for campaign follow-up
- 📸 **Selfie Capture**: Real-time camera access for taking selfies
- 🤖 **AI Facial Expression Analysis**: Analyze emotions using AI Lab Tools API
- 🎨 **AI Face Generation**: Generate Snickers-themed faces based on expressions
- 🍫 **Personalized Messages**: Custom Snickers messages based on detected emotions
- 📱 **Social Sharing**: Share results on social media platforms

## Setup Instructions

### 1. Environment Configuration

Create a `.env` file in the root directory with the following configuration:

```env
APP_NAME=Snickers
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database (using SQLite for simplicity)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# AI Lab Tools API Configuration
AILABTOOLS_API_KEY=your_ailabtools_api_key_here
AILABTOOLS_BASE_URL=https://api.ailabtools.com
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Create Database

```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 5. Create Storage Link

```bash
php artisan storage:link
```

### 6. Set Up AI Lab Tools API

1. Sign up for an AI Lab Tools account at [ailabtools.com](https://ailabtools.com)
2. Get your API key from the dashboard
3. Add the API key to your `.env` file

### 7. Run the Application

```bash
# Start the development server
php artisan serve

# In another terminal, start Vite for asset compilation
npm run dev
```

Visit `http://localhost:8000/snickers` to access the campaign.

## API Endpoints

### POST /snickers/capture

Captures a selfie, analyzes facial expression, and generates a Snickers-themed face.

**Request Body:**
```json
{
    "phone_number": "+1234567890",
    "selfie_image": "data:image/png;base64,..."
}
```

**Response:**
```json
{
    "success": true,
    "phone_number": "+1234567890",
    "expression_analysis": {
        "emotions": {
            "happy": 0.8,
            "neutral": 0.2
        },
        "dominant_emotion": "happy",
        "confidence": 0.85
    },
    "generated_face": {
        "image_url": "http://localhost:8000/storage/generated/...",
        "style": "cheerful_snickers"
    },
    "snickers_message": "You're not you when you're hungry! Grab a Snickers and keep that smile! 😊"
}
```

## File Structure

```
app/
├── Http/Controllers/
│   └── SnickersController.php      # Main campaign controller
├── Services/
│   └── AILabToolsService.php       # AI Lab Tools API integration
resources/views/snickers/
└── campaign.blade.php              # Main campaign interface
routes/
└── web.php                         # Application routes
storage/app/public/
├── temp/                           # Temporary selfie storage
└── generated/                      # Generated face storage
```

## Technologies Used

- **Laravel 12**: PHP framework
- **Tailwind CSS**: Styling framework
- **JavaScript**: Frontend interactivity
- **AI Lab Tools API**: Facial expression analysis and face generation
- **SQLite**: Database (for simplicity)

## Campaign Flow

1. **Phone Collection**: User enters their phone number
2. **Selfie Capture**: User takes a selfie using device camera
3. **AI Analysis**: Facial expression is analyzed using AI Lab Tools
4. **Face Generation**: AI generates a Snickers-themed version of the face
5. **Results Display**: Shows original selfie, generated face, and personalized message
6. **Social Sharing**: User can share their results

## Customization

### Adding New Emotion Styles

Edit `app/Services/AILabToolsService.php` to add new emotion-to-style mappings:

```php
private function getStyleFromExpression(array $expressionData): string
{
    $styleMap = [
        'happy' => 'cheerful_snickers',
        'sad' => 'melancholic_snickers',
        // Add your custom styles here
    ];
}
```

### Customizing Messages

Modify the `getSnickersMessage()` method in `AILabToolsService.php` to add custom messages for different emotions.

## Security Considerations

- CSRF protection is enabled
- Input validation for phone numbers and images
- Temporary file cleanup after processing
- API key stored in environment variables

## Troubleshooting

### Camera Not Working
- Ensure HTTPS is enabled (required for camera access)
- Check browser permissions for camera access
- Test on different browsers/devices

### API Errors
- Verify AI Lab Tools API key is correct
- Check API quota and limits
- Review Laravel logs for detailed error messages

### Storage Issues
- Ensure `storage/app/public` directory is writable
- Run `php artisan storage:link` if images aren't loading
- Check file permissions on storage directories

## License

This project is for demonstration purposes. Please ensure you have proper licensing for commercial use of AI Lab Tools APIs.


