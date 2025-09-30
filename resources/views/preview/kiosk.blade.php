<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snickers Kiosk Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f0f0f0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .kiosk-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kiosk-frame {
            position: relative;
            width: 100%;
            height: 100%;
            background-image: url('/kiosky.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kiosk-screen {
            position: absolute;
            width: 15.8%;
            height: 60%;
            top: 10%;
            left: 42.4%;
            border-radius: 2px;
        }

            .campaign-iframe {
                width: 100%;
                height: 100%;
                border: none;
                border-radius: 20px;
            }

            .kiosk-controls {
                position: absolute;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                padding: 15px 30px;
                border-radius: 25px;
                display: flex;
                gap: 15px;
                align-items: center;
            }

            .control-btn {
                background: #FFD700;
                color: #8B4513;
                border: none;
                padding: 10px 20px;
                border-radius: 20px;
                cursor: pointer;
                font-weight: bold;
                transition: all 0.3s ease;
                text-decoration: none;
                font-size: 14px;
            }

            .control-btn:hover {
                background: #FFA500;
                transform: translateY(-2px);
            }

            .status-indicator {
                color: #FFD700;
                font-size: 14px;
                font-weight: bold;
            }

            @media (max-width: 768px) {
                .kiosk-screen {
                    width: 80%;
                    height: 60%;
                    top: 20%;
                    left: 10%;
                }

                .kiosk-controls {
                    bottom: 10px;
                    padding: 10px 20px;
                    gap: 10px;
                }

                .control-btn {
                    padding: 8px 16px;
                    font-size: 12px;
                }
            }

            @media (orientation: landscape) and (max-height: 600px) {
                .kiosk-screen {
                    width: 70%;
                    height: 80%;
                    top: 10%;
                    left: 15%;
                }
            }
</style>
</head>
<body>
    <div class="kiosk-container">
        <div class="kiosk-frame">
            <div class="kiosk-screen">
                <iframe
                    src="{{ route('snickers.campaign') }}"
                    class="campaign-iframe"
                    title="Snickers Campaign"
                    allow="camera; microphone">
                </iframe>
            </div>

            <div class="kiosk-controls">
                <span class="status-indicator">🟢 LIVE</span>
                <a href="{{ route('snickers.campaign') }}" class="control-btn" target="_blank">Full Screen</a>
                <a href="{{ route('admin.login') }}" class="control-btn" target="_blank">Admin</a>
                <button class="control-btn" onclick="refreshCampaign()">Refresh</button>
            </div>
        </div>
    </div>

    <script>
        function refreshCampaign() {
            const iframe = document.querySelector('.campaign-iframe');
            iframe.src = iframe.src;
        }

        // Auto-refresh every 30 minutes to prevent any session issues
        setInterval(function() {
            const iframe = document.querySelector('.campaign-iframe');
            iframe.src = iframe.src;
        }, 30 * 60 * 1000);

        // Handle iframe load errors
        document.querySelector('.campaign-iframe').addEventListener('error', function() {
            console.error('Campaign iframe failed to load');
            this.src = this.src; // Retry loading
        });
    </script>
</body>
</html>
