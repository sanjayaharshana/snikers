<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daintee Campaign</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #000;
            height: 100vh;
            overflow: hidden;
            position: relative;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100vw;
            height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            aspect-ratio: 9/16;
            max-width: calc(100vh * 9 / 16);
            margin: 0 auto;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Remove hover scaling to prevent size changes */
        .container:hover {
            transform: none;
        }

        .container.fullscreen {
            width: 100vw;
            height: 100vh;
            max-width: 100vw;
            aspect-ratio: unset;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            background: #000;
            transform: scale(1);
        }

        .fullscreen-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.8);
            color: #000;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .container.fullscreen .fullscreen-indicator {
            opacity: 1;
        }

        .fullscreen-button {
            position: absolute;
            top: 20px;
            right: 20px; /* move to corner */
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .fullscreen-button:hover {
            transform: scale(1.05);
            background: rgba(0, 0, 0, 0.75);
        }

        .fullscreen-button svg {
            width: 22px;
            height: 22px;
            fill: #FFD700;
        }

        /* Mobile fullscreen support */
        @media (max-width: 768px) {
            .fullscreen-indicator {
                top: 10px;
                right: 10px;
                padding: 8px 12px;
                font-size: 12px;
            }
        }

        /* Touch device support */
        @media (hover: none) and (pointer: coarse) {
            .container:hover {
                transform: none;
            }
        }

        @media (orientation: landscape) {
            .container {
                width: calc(100vh * 9 / 16);
                height: 100vh;
                max-width: calc(100vh * 9 / 16);
            }
        }

        .step {
            display: none;
            width: 100%;
            height: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            position: absolute;
            top: 0;
            left: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            outline: none;
            -webkit-tap-highlight-color: transparent;
        }

        #step1 {
            background-image: url('/01/BG.jpg');
        }

        #step2 {
            background-image: url('/02/BG.jpg');
        }

        #step3 {
            background-image: url('/03/BG.jpg');
        }

        #step4 {
            background-image: url('/04/BG.jpg');
        }

        #step5 {
            background-image: url('/05/BG_FRAME.png');
        }

        #step6 {
            background-image: url('/06/BG.jpg');
        }

        #step7 {
            background-image: url('/07/BG.jpg');
        }

        #step8 {
            background-image: url('/01/BG.jpg');
        }

        .thank-you-message {
            text-align: center;
            margin: 4vh 0;
        }

        .thank-you-title {
            font-size: 4.6vh;
            color: #FFD700;
            margin-bottom: 2vh;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            font-weight: bold;
        }

        .thank-you-text {
            font-size: 2.4vh;
            color: white;
            margin-bottom: 1vh;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .step.active {
            display: flex;
            animation: slideIn 0.8s ease-in-out;
        }

        .step.fade-out {
            animation: slideOut 0.5s ease-in-out forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-100%);
            }
        }

        .daintee-logo {
            width: 38vh; /* scale with screen height */
            height: auto;
            margin-bottom: 2vh;
            cursor: pointer;
            transition: none;
        }

        .daintee-logo:hover { /* keep size constant */ }

        .asset-image {
            width: 31vh;
            height: auto;
            margin: 3.2vh 0;
            margin-bottom: 30px;
        }

        .daintee-bar-asset {
            width: 34vh;
            height: auto;
            margin: 2vh 0;
            cursor: pointer;
            transition: none;
            animation: shake 2s infinite;
        }

        .daintee-bar-asset:hover { /* keep size constant */ }

        .btn-asset {
            width: 27vh;
            height: auto;
            margin: 2vh 0;
            cursor: pointer;
            transition: none;
        }

        .btn-asset:hover { /* keep size constant */ }


        .daintee-bar {
            width: 300px;
            height: 120px;
            background: linear-gradient(45deg, #800080, #9932CC);
            border-radius: 20px;
            position: relative;
            margin: 20px 0;
            cursor: pointer;
            transition: transform 0.3s ease;
            animation: shake 2s infinite;
        }

        .daintee-bar:hover {
            transform: scale(1.05);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .daintee-bar::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            height: 30px;
            background: #FFD700;
            border-radius: 10px;
        }

        .daintee-bar::after {
            content: '';
            position: absolute;
            top: 50px;
            left: 10px;
            right: 10px;
            height: 30px;
            background: #FFD700;
            border-radius: 10px;
        }

        .title {
            font-size: 4.6vh;
            color: #FFD700;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            font-weight: bold;
        }

        .subtitle {
            font-size: 2.4vh;
            color: white;
            margin-bottom: 40px;
        }

        /* unify button sizing to viewport */
        .btn-asset {
            width: 27vh;
            height: auto;
            margin: 1.6vh 0;
            cursor: pointer;
            transition: none;
        }

        .btn-asset:hover { }

        .phone-input {
            width: 70%;
            height: 6vh;
            font-size: 2.2vh;
            padding: 1.2vh;
            border: 3px solid transparent;
            border-radius: 15px;
            text-align: center;
            margin: 1.6vh 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: border-color 0.3s ease;
        }

        .phone-input.error {
            border-color: #ff0000;
            background-color: rgba(255, 0, 0, 0.1);
        }

        .phone-input.error::placeholder {
            color: #ff0000;
        }

        .btn {
            background: #FFD700;
            color: #800080;
            border: none;
            padding: 2vh 4vh;
            font-size: 2.4vh;
            font-weight: bold;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .btn:hover {
            background: #FFA500;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .camera-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1000;
            background: #000;
        }

        /* Hide other elements when camera is active */
        .step.active .camera-container ~ * {
            position: relative;
            z-index: 1001;
        }

        .step.active .daintee-logo,
        .step.active .asset-image,
        .step.active .btn-asset {
            position: relative;
            z-index: 1001;
        }

        .capture-btn {
            bottom: -240px;
            width: 80px;
            height: 80px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 50%;
            transition: transform 0.2s
            ease;
        }

        .capture-btn:hover {
            transform: translateX(-50%) scale(1.1);
        }

        .capture-btn:active {
            transform: translateX(-50%) scale(0.95);
        }


        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #canvas {
            display: none;
        }

        .camera-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 3px solid #FFD700;
            border-radius: 15px;
            pointer-events: none;
        }

        .hungry-text {
            font-size: 3.6vh;
            color: #FFD700;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .hungry-subtitle {
            font-size: 2vh;
            color: white;
            margin-bottom: 30px;
        }

        .video-container {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            overflow: hidden;
        }

        #satisfying-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .emotion-buttons {
            display: flex;
            gap: 30px;
            margin: 30px 0;
        }

        .emotion-btn {
            width: 14vh;
            height: 14vh;
            border-radius: 50%;
            border: none;
            font-size: 2.2vh;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .emotion-btn.sad {
            background: #4A90E2;
            color: white;
        }

        .emotion-btn.happy {
            background: #FFD700;
            color: #800080;
        }

        .emotion-btn:hover {
            transform: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }

        .result-image {
            width: 28vh;
            height: 28vh;
            border-radius: 20px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 5px solid #FFD700;
        }

        .slogan {
            background: #FFD700;
            color: #800080;
            padding: 20px;
            border-radius: 15px;
            font-size: 2vh;
            font-weight: bold;
            margin: 20px 0;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .loading {
            display: none;
            color: #FFD700;
            font-size: 24px;
            margin: 20px 0;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #FFD700;
            border-radius: 50%;
            width: 4vh;
            height: 4vh;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .frame-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><rect x="20" y="20" width="360" height="360" fill="none" stroke="%23FFD700" stroke-width="8" rx="20"/><circle cx="200" cy="200" r="150" fill="none" stroke="%23FFD700" stroke-width="4"/></svg>') no-repeat center;
            background-size: contain;
            pointer-events: none;
            z-index: 10;
        }

        .emotion-container {
            width: 40vh;
            height: 49vh;
            max-height: 100%;
            border: 5px solid white;
            border-radius: 20px;
            overflow: hidden;
            margin: 1.5vh 0;
            background: #f0f0f0;
            position: relative;
        }

        .emotion-section {
            height: 33.33%;
            position: relative;
            border-bottom: 2px solid white;
        }

        .emotion-section:last-child {
            border-bottom: none;
        }

        .single-image-section {
            height: 100%;
            border-bottom: none;
        }

        .emotion-label {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.8vh;
            font-weight: bold;
            color: white;
            z-index: 10;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            background: rgba(0,0,0,0.6);
            padding: 5px 10px;
            border-radius: 10px;
        }

        .emotion-image-container {
            width: 100%;
            height: 100%;
            position: relative;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emotion-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-text {
            color: #666;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .daintee-overlay {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 10;
        }

        .mini-daintee-bar {
            width: 60px;
            height: 20px;
            background: linear-gradient(45deg, #800080, #9932CC);
            border-radius: 8px;
            position: relative;
            transform: rotate(-15deg);
        }

        .mini-daintee-bar::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            right: 2px;
            height: 4px;
            background: #FFD700;
            border-radius: 2px;
        }

        .mini-daintee-bar::after {
            content: '';
            position: absolute;
            top: 8px;
            left: 2px;
            right: 2px;
            height: 4px;
            background: #FFD700;
            border-radius: 2px;
        }

        .hungry-result-container {
            width: 40vh;
            margin: 20px 0;
            text-align: center;
        }

        .hungry-result-image {
        }

        .hungry-result-image img {
            width: 25vh;
            height: 25vh;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 5px solid #FFD700;
            object-fit: cover;
        }

        /* Emotion Processing UI Styles */
        .emotion-processing-container {
            width: 42vh;
            height: 50vh;
            margin: 20px 0;
            position: relative;
        }

        .emotion-border {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 20px;
            padding: 8px;
        }

        .emotion-border-purple {
            background: #800080;
            border-radius: 25px;
        }

        .emotion-border-silver {
            background: #C0C0C0;
            border-radius: 20px;
        }

        .emotion-border-brown {
            background: #8B4513;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
        }

        .emotion-portrait-section {
            flex: 1;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emotion-portrait-frame {
            width: 100%;
            height: 100%;
            border: 3px solid #800080;
            border-radius: 15px;
            overflow: hidden;
            background: #f0f0f0;
            position: relative;
        }

        .emotion-result-section {
            flex: 1;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #8B4513;
        }

        .emotion-text-container {
            text-align: center;
            margin-bottom: 15px;
        }

        .emotion-text-line {
            color: white;
            font-size: 1.8vh;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .emotion-text-hungry {
            color: #FFD700;
            font-size: 2.8vh;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            animation: pulse 1.5s infinite;
        }

        .emotion-text-grab {
            color: white;
            font-size: 1.8vh;
            font-weight: bold;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .emotion-product-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emotion-daintee-bar {
            width: 12vh;
            height: auto;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            animation: float 2s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .emotion-image-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
        }

        .emotion-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

    </style>
</head>
<body>
    <div class="container" ondblclick="toggleFullscreen()">
        <div class="fullscreen-indicator">Double-click to exit fullscreen</div>
        <div class="fullscreen-button" onclick="enterBrowserFullscreen()" title="Enter fullscreen">
            <!-- Corner arrows icon -->
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7 3H3v4h2V5h2V3zm12 0h-4v2h2v2h2V3zM5 17H3v4h4v-2H5v-2zm16 0h-2v2h-2v2h4v-4z"/>
            </svg>
        </div>
        <!-- Step 1: Camera View -->
        <div class="step active" id="step1">
            <div class="camera-container">
                <video id="video" autoplay muted></video>
                <canvas id="canvas"></canvas>
            </div>
            <button class="capture-btn" onclick="captureSelfie()">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" fill="white" stroke="#800080" stroke-width="2"/>
                    <circle cx="12" cy="12" r="6" fill="#800080"/>
                </svg>
            </button>
        </div>

        <!-- Step 2: Processing -->
        <div class="step" id="step2">
            <img src="/01/DAINTEE LOGO.png" alt="Daintee Logo" class="daintee-logo">

            <div class="loading" id="processingLoading">
                <div class="spinner"></div>
                <div style="color: #FFD700; font-size: 2vh; font-weight: bold; margin-top: 20px;">
                    Processing your photo...
                </div>
            </div>

            <!-- Processing Status -->
            <div id="processingStatus" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.8); color: #FFD700; padding: 20px; border-radius: 15px; text-align: center; font-size: 16px; font-weight: bold;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <div class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></div>
                    <span id="statusText">Processing...</span>
                </div>
            </div>
        </div>

        <!-- Step 3: Results -->
        <div class="step" id="step3">
            <img src="/01/DAINTEE LOGO.png" alt="Daintee Logo" class="daintee-logo">

            <div class="emotion-container" id="resultsContainer" style="width: 40vh; height: 50vh; border: 5px solid #800080; border-radius: 20px; overflow: hidden; margin: 20px 0; background: #f0f0f0; position: relative;">
                <div class="emotion-section single-image-section">
                    <div class="emotion-image-container" id="resultContainer">
                        <div class="placeholder-text">Loading result...</div>
                    </div>
                </div>
            </div>

            <div class="slogan">
                <div style="font-size: 2.5vh; margin-bottom: 10px;">YOU'RE NOT YOU</div>
                <div style="font-size: 2.5vh; margin-bottom: 10px;">WHEN YOU ARE</div>
                <div style="font-size: 3vh; color: #FFD700;">HUNGRY!</div>
            </div>

            <img src="/07/DAINTEE BAR_1.png" alt="Daintee Bar" class="daintee-bar-asset" style="width: 25vh;">

            <img src="/07/BT_done.png" alt="Done" class="btn-asset" onclick="restartCampaign()" style="cursor: pointer; margin-top: 20px;">
        </div>
    </div>

    <script>
        let currentStep = 1;
        let selfieData = '';
        let processingJobId = null;
        let processingInterval = null;
        let stream = null;

        function nextStep() {
            console.log('nextStep called, current step:', currentStep);

            if (currentStep < 3) {
                const currentStepEl = document.getElementById(`step${currentStep}`);
                if (!currentStepEl) {
                    console.error('Current step element not found:', `step${currentStep}`);
                    return;
                }

                console.log('Transitioning from step', currentStep, 'to step', currentStep + 1);
                currentStepEl.classList.add('fade-out');

                setTimeout(() => {
                    currentStepEl.classList.remove('active', 'fade-out');
                    currentStep++;
                    const nextStepEl = document.getElementById(`step${currentStep}`);

                    if (nextStepEl) {
                        nextStepEl.classList.add('active');

                        if (currentStep === 1) {
                            startCamera();
                        } else if (currentStep === 2) {
                            processSelfie();
                        }
                    } else {
                        console.error('Next step element not found:', `step${currentStep}`);
                    }
                }, 500);
            }
        }

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(mediaStream) {
                    stream = mediaStream;
                    const video = document.getElementById('video');
                    video.srcObject = mediaStream;
                })
                .catch(function(err) {
                    console.error('Error accessing camera:', err);
                    alert('Camera access denied. Please allow camera access to continue.');
                });
        }

        function captureSelfie() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);

            selfieData = canvas.toDataURL('image/jpeg');

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            nextStep();
        }

        function processSelfie() {
            if (!selfieData) {
                console.error('No selfie data available');
                return;
            }

            // Process the selfie with AI
            const formData = new FormData();
            formData.append('phone_number', '');
            formData.append('selfie_image', selfieData);

            fetch('/daintee/process-first-selfie', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Selfie Processing Response:', data);

                if (data.success) {
                    // Store the generated image ID for later use
                    window.generatedImageId = data.generated_image_id;
                    processingJobId = data.jobId;

                    // Start polling for processing status
                    startProcessingCheck();
                } else {
                    console.error('Selfie processing failed:', data.message);
                    displayFallbackResults();
                }
            })
            .catch(error => {
                console.error('Error processing selfie:', error);
                displayFallbackResults();
            });
        }

        function startProcessingCheck() {
            if (processingInterval) {
                clearInterval(processingInterval);
            }

            processingInterval = setInterval(async () => {
                try {
                    const response = await fetch('/daintee/check-job-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            jobId: processingJobId
                        })
                    });

                    const result = await response.json();

                    if (result.status === 'completed') {
                        clearInterval(processingInterval);
                        displayResults(result.data);
                    } else if (result.status === 'failed') {
                        clearInterval(processingInterval);
                        console.error('Processing failed:', result.message);
                        displayFallbackResults();
                    }
                } catch (error) {
                    console.error('Error checking job status:', error);
                }
            }, 2000);
        }

        function displayResults(data) {
            // Hide processing loading
            const loading = document.getElementById('processingLoading');
            if (loading) {
                loading.style.display = 'none';
            }

            const statusDiv = document.getElementById('processingStatus');
            if (statusDiv) {
                statusDiv.style.display = 'none';
            }

            // Display the result image
            const resultContainer = document.getElementById('resultContainer');
            if (resultContainer && data.framedImageUrl) {
                resultContainer.innerHTML = `<img src="${data.framedImageUrl}" alt="Processed Image" style="width: 100%; height: 100%; object-fit: cover;">`;
            }

            // Move to step 3
            nextStep();
        }

        function displayFallbackResults() {
            // Hide processing loading
            const loading = document.getElementById('processingLoading');
            if (loading) {
                loading.style.display = 'none';
            }

            const statusDiv = document.getElementById('processingStatus');
            if (statusDiv) {
                statusDiv.style.display = 'none';
            }

            // Show original selfie as fallback
            const resultContainer = document.getElementById('resultContainer');
            if (resultContainer && selfieData) {
                resultContainer.innerHTML = `<img src="${selfieData}" alt="Original Selfie" style="width: 100%; height: 100%; object-fit: cover;">`;
            }

            // Move to step 3
            nextStep();
        }

        function restartCampaign() {
            // Stop camera stream
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Reset variables
            currentStep = 1;
            selfieData = '';
            processingJobId = null;

            if (processingInterval) {
                clearInterval(processingInterval);
                processingInterval = null;
            }

            // Reset UI
            showStep(1);

            // Show processing elements again
            const loading = document.getElementById('processingLoading');
            if (loading) {
                loading.style.display = 'block';
            }

            const statusDiv = document.getElementById('processingStatus');
            if (statusDiv) {
                statusDiv.style.display = 'block';
            }

            // Clear results
            const resultContainer = document.getElementById('resultContainer');
            if (resultContainer) {
                resultContainer.innerHTML = '<div class="placeholder-text">Loading result...</div>';
            }
        }



        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Error attempting to enable fullscreen:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }

        function showStep(step) {
            // Hide all steps
            for (let i = 1; i <= 3; i++) {
                const stepElement = document.getElementById(`step${i}`);
                if (stepElement) {
                    stepElement.classList.remove('active');
                }
            }

            // Show current step
            const currentStepElement = document.getElementById(`step${step}`);
            if (currentStepElement) {
                currentStepElement.classList.add('active');
            }

            // Initialize camera for step 1
            if (step === 1) {
                startCamera();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
        });

        // Prevent context menu on right-click
        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });

        // Touch support for mobile devices
        let touchStartTime = 0;
        let touchCount = 0;

        document.addEventListener('touchstart', function(event) {
            touchStartTime = Date.now();
            touchCount++;

            // Double tap detection for mobile
            if (touchCount === 2) {
                const timeDiff = Date.now() - touchStartTime;
                if (timeDiff < 500) { // 500ms threshold for double tap
                    toggleFullscreen();
                    touchCount = 0;
                }
            }

            // Reset touch count after 1 second
            setTimeout(() => {
                touchCount = 0;
            }, 1000);
        });

    </script>
</body>
</html>
