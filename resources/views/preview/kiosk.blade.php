<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snickers Campaign Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            overflow: hidden;
        }

        .preview-container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 300px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px 20px;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar-header h1 {
            color: #8B4513;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: #666;
            font-size: 14px;
        }

        .device-selector {
            margin-bottom: 30px;
        }

        .device-selector label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        .device-dropdown {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .device-dropdown:focus {
            outline: none;
            border-color: #FFD700;
        }

        .device-preview {
            margin-bottom: 30px;
        }

        .device-preview h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .device-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #FFD700;
        }

        .device-info p {
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
        }

        .device-info strong {
            color: #333;
        }

        .controls {
            margin-bottom: 30px;
        }

        .controls h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .control-btn {
            display: block;
            width: 100%;
            background: #FFD700;
            color: #8B4513;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }

        .control-btn:hover {
            background: #FFA500;
            transform: translateY(-2px);
        }

        .control-btn.secondary {
            background: #6c757d;
            color: white;
        }

        .control-btn.secondary:hover {
            background: #545b62;
        }

        .status-section {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #28a745;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #28a745;
            font-weight: bold;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .preview-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
        }

        .device-frame {
            position: relative;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .device-frame.kiosk {
            width: 400px;
            height: 600px;
            background-image: url('/kiosky.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .device-frame.mobile {
            width: 300px;
            height: 600px;
            background-image: url('/mobile-frame.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .device-frame.tablet {
            width: 500px;
            height: 700px;
            background-image: url('/tablet-frame.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .device-frame.screen {
            width: 800px;
            height: 600px;
            background-image: url('/screen-frame.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .device-screen {
            position: absolute;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
        }

        .device-frame.kiosk .device-screen {
            width: 46.8%;
            height: 60%;
            top: 10%;
            left: 27.4%;
            border-radius: 2px;
        }

        .device-frame.mobile .device-screen {
            width: 85%;
            height: 90%;
            top: 5%;
            left: 7.5%;
            border-radius: 25px;
        }

        .device-frame.tablet .device-screen {
            width: 90%;
            height: 85%;
            top: 7.5%;
            left: 5%;
            border-radius: 20px;
        }

        .device-frame.screen .device-screen {
            width: 95%;
            height: 90%;
            top: 5%;
            left: 2.5%;
            border-radius: 15px;
        }

        .campaign-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .device-label {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .preview-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                max-height: 40vh;
            }

            .preview-area {
                height: 60vh;
            }

            .device-frame {
                transform: scale(0.8);
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>🍫 SNICKERS</h1>
                <p>Campaign Preview</p>
            </div>

            <div class="device-selector">
                <label for="deviceSelect">Select Device Type:</label>
                <select id="deviceSelect" class="device-dropdown" onchange="changeDevice()">
                    <option value="kiosk">Kiosk Display</option>
                    <option value="mobile">Mobile Phone</option>
                    <option value="tablet">Tablet</option>
                    <option value="screen">Digital Screen</option>
                </select>
            </div>

            <div class="device-preview">
                <h3>Device Information</h3>
                <div class="device-info" id="deviceInfo">
                    <p><strong>Type:</strong> Kiosk Display</p>
                    <p><strong>Resolution:</strong> 1080x1920</p>
                    <p><strong>Orientation:</strong> Portrait</p>
                    <p><strong>Touch:</strong> Yes</p>
                </div>
            </div>

            <div class="controls">
                <h3>Controls</h3>
                <a href="{{ route('snickers.campaign') }}" class="control-btn" target="_blank">Full Screen</a>
                <a href="{{ route('admin.login') }}" class="control-btn secondary" target="_blank">Admin Panel</a>
                <button class="control-btn secondary" onclick="refreshCampaign()">Refresh Campaign</button>
            </div>

            <div class="status-section">
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>Campaign Live</span>
                </div>
            </div>
        </div>

        <div class="preview-area">
            <div class="device-frame kiosk" id="deviceFrame">
                <div class="device-screen">
                    <iframe
                        src="{{ route('snickers.campaign') }}"
                        class="campaign-iframe"
                        title="Snickers Campaign"
                        allow="camera; microphone">
                    </iframe>
                </div>
                <div class="device-label" id="deviceLabel">Kiosk Display</div>
            </div>
        </div>
    </div>

    <script>
        const deviceInfo = {
            kiosk: {
                type: 'Kiosk Display',
                resolution: '1080x1920',
                orientation: 'Portrait',
                touch: 'Yes'
            },
            mobile: {
                type: 'Mobile Phone',
                resolution: '375x812',
                orientation: 'Portrait',
                touch: 'Yes'
            },
            tablet: {
                type: 'Tablet',
                resolution: '768x1024',
                orientation: 'Portrait',
                touch: 'Yes'
            },
            screen: {
                type: 'Digital Screen',
                resolution: '1920x1080',
                orientation: 'Landscape',
                touch: 'No'
            }
        };

        function changeDevice() {
            const select = document.getElementById('deviceSelect');
            const deviceFrame = document.getElementById('deviceFrame');
            const deviceLabel = document.getElementById('deviceLabel');
            const deviceInfoDiv = document.getElementById('deviceInfo');

            const selectedDevice = select.value;
            const info = deviceInfo[selectedDevice];

            // Update device frame class
            deviceFrame.className = `device-frame ${selectedDevice}`;

            // Update device label
            deviceLabel.textContent = info.type;

            // Update device info
            deviceInfoDiv.innerHTML = `
                <p><strong>Type:</strong> ${info.type}</p>
                <p><strong>Resolution:</strong> ${info.resolution}</p>
                <p><strong>Orientation:</strong> ${info.orientation}</p>
                <p><strong>Touch:</strong> ${info.touch}</p>
            `;
        }

        function refreshCampaign() {
            const iframe = document.querySelector('.campaign-iframe');
            iframe.src = iframe.src;
        }

        // Auto-refresh every 30 minutes
        setInterval(function() {
            const iframe = document.querySelector('.campaign-iframe');
            iframe.src = iframe.src;
        }, 30 * 60 * 1000);

        // Handle iframe load errors
        document.querySelector('.campaign-iframe').addEventListener('error', function() {
            console.error('Campaign iframe failed to load');
            this.src = this.src;
        });
    </script>
</body>
</html>
