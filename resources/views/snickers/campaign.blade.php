<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snickers Campaign</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

        .snickers-logo {
            width: 60%;
            max-width: 300px;
            height: auto;
            margin-bottom: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .snickers-logo:hover {
            transform: scale(1.05);
        }

        .asset-image {
            max-width: 50%;
            height: auto;
            margin: 10px 0;
        }

        .snickers-bar-asset {
            width: 70%;
            max-width: 400px;
            height: auto;
            margin: 20px 0;
            cursor: pointer;
            transition: transform 0.3s ease;
            animation: shake 2s infinite;
        }

        .snickers-bar-asset:hover {
            transform: scale(1.05);
        }

        .btn-asset {
            width: 200px;
            height: auto;
            margin: 20px 0;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn-asset:hover {
            transform: scale(1.05);
        }

        .selfie-frame-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            pointer-events: none;
            z-index: 10;
        }

        .snickers-bar {
            width: 300px;
            height: 120px;
            background: linear-gradient(45deg, #8B4513, #A0522D);
            border-radius: 20px;
            position: relative;
            margin: 20px 0;
            cursor: pointer;
            transition: transform 0.3s ease;
            animation: shake 2s infinite;
        }

        .snickers-bar:hover {
            transform: scale(1.05);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .snickers-bar::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            height: 30px;
            background: #FFD700;
            border-radius: 10px;
        }

        .snickers-bar::after {
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
            font-size: 48px;
            color: #FFD700;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            font-weight: bold;
        }

        .subtitle {
            font-size: 24px;
            color: white;
            margin-bottom: 40px;
        }

        .btn-asset {
            width: 50%;
            max-width: 200px;
            height: auto;
            margin: 15px 0;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn-asset:hover {
            transform: scale(1.05);
        }

        .phone-input {
            width: 80%;
            max-width: 300px;
            height: 50px;
            font-size: 20px;
            padding: 12px;
            border: none;
            border-radius: 15px;
            text-align: center;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn {
            background: #FFD700;
            color: #8B4513;
            border: none;
            padding: 20px 40px;
            font-size: 24px;
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
            width: 32vh;
            max-width: 356px;
            height: 32.1vh;
            max-height: 409px;
            border: solid #df0100;
            border-radius: 20px;
            overflow: hidden;
            margin: 15px 0;
            position: relative;
            background: #000;
            border-radius: 55px 0px 64px 0px;
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
            font-size: 36px;
            color: #FFD700;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .hungry-subtitle {
            font-size: 20px;
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
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: none;
            font-size: 24px;
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
            color: #8B4513;
        }

        .emotion-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }

        .result-image {
            width: 300px;
            height: 300px;
            border-radius: 20px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 5px solid #FFD700;
        }

        .slogan {
            background: #FFD700;
            color: #8B4513;
            padding: 20px;
            border-radius: 15px;
            font-size: 20px;
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
            width: 40px;
            height: 40px;
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
            width: 80%;
            max-width: 350px;
            height: 490px;
            max-height: 100%;
            border: 5px solid white;
            border-radius: 20px;
            overflow: hidden;
            margin: 15px 0;
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
            font-size: 18px;
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

        .snickers-overlay {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 10;
        }

        .mini-snickers-bar {
            width: 60px;
            height: 20px;
            background: linear-gradient(45deg, #8B4513, #A0522D);
            border-radius: 8px;
            position: relative;
            transform: rotate(-15deg);
        }

        .mini-snickers-bar::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            right: 2px;
            height: 4px;
            background: #FFD700;
            border-radius: 2px;
        }

        .mini-snickers-bar::after {
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
            width: 80%;
            max-width: 350px;
            margin: 20px 0;
            text-align: center;
        }

        .hungry-result-image {
        }

        .hungry-result-image img {
            width: 250px;
            height: 250px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border: 5px solid #FFD700;
            object-fit: cover;
        }

        /* Emotion Processing UI Styles */
        .emotion-processing-container {
            width: 80%;
            max-width: 400px;
            height: 500px;
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

        .emotion-border-red {
            background: #df0100;
            border-radius: 25px;
        }

        .emotion-border-blue {
            background: #0066cc;
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
            border: 3px solid #df0100;
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
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .emotion-text-hungry {
            color: #FFD700;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            animation: pulse 1.5s infinite;
        }

        .emotion-text-grab {
            color: white;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .emotion-product-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emotion-snickers-bar {
            width: 120px;
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
    <div class="container">
        <!-- Step 1: Ready Screen -->
        <div class="step active" id="step1">
            <img src="/01/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/01/Ready.png" alt="Ready" class="asset-image">
            <img src="/01/SNICKERS BAR.png" alt="Snickers Bar" class="snickers-bar-asset" onclick="nextStep()">
        </div>

        <!-- Step 2: Phone Number -->
        <div class="step" id="step2">
            <img src="/02/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/02/Enter Number.png" alt="Enter Number" class="asset-image">
            <input type="tel" class="phone-input" id="phoneInput" placeholder="+1234567890" maxlength="20">
            <img src="/02/BT_Continue.png" alt="Continue" class="btn-asset" onclick="nextStep()" style="cursor: pointer;">
        </div>

        <!-- Step 3: First Selfie -->
        <div class="step" id="step3">
            <img src="/03/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/03/Take a Selfie.png" alt="Take a Selfie" class="asset-image">
            <div class="camera-container">
                <video id="video" autoplay muted></video>
                <canvas id="canvas"></canvas>
                <img src="/03/Selfie_Frame.png" alt="Selfie Frame" class="selfie-frame-overlay">
            </div>
            <img src="/03/BT_Snap.png" alt="Snap" class="btn-asset" onclick="captureSelfie()" style="cursor: pointer;">
        </div>

        <!-- Step 4: Final Results -->
        <div class="step" id="step4">
            <img src="/04/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">

            <div class="emotion-container" id="finalResultsContainer" style="display: none;">
                <div class="emotion-section single-image-section">
                    <div class="emotion-label">SAD</div>
                    <div class="emotion-image-container" id="sadContainer">
                        <div class="placeholder-text">Processing...</div>
                    </div>
                </div>
            </div>

            <div class="loading" id="hungryLoading" style="display: none;">
                <div class="video-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; background: #000; border-radius: 20px; overflow: hidden;">
                    <video id="processing-video" autoplay muted loop style="width: 100%; height: 100%; object-fit: cover;">
                        <source src="/05/SNK SATISFYING VIDEO IGS.mp4" type="video/mp4">
                    </video>
                    <div class="video-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,0.3);">
                        <div class="spinner" style="margin-bottom: 20px;"></div>
                        <div style="color: #FFD700; font-size: 18px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">Processing your photos...</div>
                    </div>
                </div>
            </div>

            <img src="/04/BT_OK.png" alt="OK" class="btn-asset" onclick="finishCampaign()" style="cursor: pointer; display: none;" id="finalOkBtn">
        </div>

        <!-- Step 5: Video -->
        <div class="step" id="step5">
            <div class="video-container">
                <video id="satisfying-video" autoplay muted loop>
                    <source src="/05/SNK SATISFYING VIDEO IGS.mp4" type="video/mp4">
                </video>
            </div>
        </div>

        <!-- Step 6: Second Selfie -->
        <div class="step" id="step6">
            <img src="/06/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/06/Take a Selfie.png" alt="Take a Selfie" class="asset-image">
            <div class="camera-container">
                <video id="video2" autoplay muted style="position: absolute;right: -90%;top: -130px;"></video>
                <canvas id="canvas2"></canvas>
                <img src="/06/Selfie_Frame.png" alt="Selfie Frame" class="selfie-frame-overlay">
            </div>
            <img src="/06/BT_Snap.png" alt="Snap" class="btn-asset" onclick="captureSecondSelfie()" style="cursor: pointer;">
        </div>

        <!-- Step 7: Emotion Processing & Result -->
        <div class="step" id="step7">
            <img src="/07/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">

            <!-- Emotion Processing Container -->
            <div class="emotion-processing-container" id="emotionProcessingContainer">
                <!-- Outer Red Border -->
                <div class="emotion-border emotion-border-red">
                    <!-- Inner Blue Border -->
                    <div class="emotion-border emotion-border-blue">
                        <!-- Inner Brown Border -->
                        <div class="emotion-border emotion-border-brown">
                            <!-- Top Section: Portrait Image -->
                            <div class="emotion-portrait-section">
                                <div class="emotion-portrait-frame">
                                    <div class="emotion-image-container" id="emotionPortraitContainer">
                                        <div class="placeholder-text">Processing...</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Section: Text and Product -->
                            <div class="emotion-result-section">
                                <div class="emotion-text-container">
                                    <div class="emotion-text-line">LOOKS LIKE YOU'RE</div>
                                    <div class="emotion-text-hungry">HUNGRY!</div>
                                    <div class="emotion-text-grab">GRAB A</div>
                                </div>
                                <div class="emotion-product-container">
                                    <img src="/07/SNICKERS BAR_1.png" alt="Snickers Bar" class="emotion-snickers-bar">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                Processing your emotions...
            </div>

            <!-- Done Button -->
            <img src="/07/BT_done.png" alt="Done" class="btn-asset" onclick="finishCampaign()" style="display: none; cursor: pointer;" id="doneBtn">
        </div>
    </div>

    <script>
        let currentStep = 1;
        let phoneNumber = '';
        let firstSelfie = null;
        let secondSelfie = null;
        let firstSelfieSadResult = null;
        let secondSelfieHappyResult = null;
        let stream = null;
        let stream2 = null;

        function nextStep() {
            console.log('nextStep called, current step:', currentStep);

            if (currentStep === 2) {
                phoneNumber = document.getElementById('phoneInput').value;
                if (!phoneNumber) {
                    alert('Please enter your phone number');
                    return;
                }
            }

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

                    if (currentStep === 3) {
                        startCamera();
                    } else if (currentStep === 4) {
                        // Process first selfie for all emotions and complete campaign
                        processFirstSelfieForHungryDetection();
                    } else if (currentStep === 6) {
                        startSecondCamera();
                    } else if (currentStep === 7) {
                        // Process both emotions for final result
                        processBothEmotions();
                    }
                } else {
                    console.error('Next step element not found:', `step${currentStep}`);
                }
            }, 500);
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

        function startSecondCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(mediaStream) {
                    stream2 = mediaStream;
                    const video = document.getElementById('video2');
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

            firstSelfie = canvas.toDataURL('image/jpeg');

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            nextStep();
        }

        function processFirstSelfieForHungryDetection() {
            // Show loading
            document.getElementById('hungryLoading').style.display = 'block';
            document.getElementById('finalResultsContainer').style.display = 'none';
            document.getElementById('finalOkBtn').style.display = 'none';

            if (!firstSelfie) {
                console.error('No first selfie data available');
                document.getElementById('hungryLoading').style.display = 'none';
                document.getElementById('finalResultsContainer').style.display = 'block';
                document.getElementById('finalOkBtn').style.display = 'block';
                return;
            }

            // Process the first selfie with AI for both emotions
            const formData = new FormData();
            formData.append('phone_number', phoneNumber);
            formData.append('selfie_image', firstSelfie);

            fetch('/snickers/process-first-selfie', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('hungryLoading').style.display = 'none';

                console.log('First Selfie Processing Response:', data); // Debug log
                console.log('Response data structure:', {
                    success: data.success,
                    original_image_url: data.original_image_url,
                    sad_image_url: data.sad_image_url,
                    happy_image_url: data.happy_image_url
                });

                if (data.success) {
                    // Display all three images
                    displayFinalResults(data);
                    document.getElementById('finalResultsContainer').style.display = 'block';
                    document.getElementById('finalOkBtn').style.display = 'block';
                } else {
                    console.error('First selfie processing failed:', data.message);
                    // Show original selfie as fallback in all sections
                    displayFallbackResults();
                    document.getElementById('finalResultsContainer').style.display = 'block';
                    document.getElementById('finalOkBtn').style.display = 'block';
                }
            })
            .catch(error => {
                document.getElementById('hungryLoading').style.display = 'none';
                console.error('Error processing first selfie:', error);

                // Show original selfie as fallback in all sections
                displayFallbackResults();
                document.getElementById('finalResultsContainer').style.display = 'block';
                document.getElementById('finalOkBtn').style.display = 'block';
            });
        }

        function displayFinalResults(data) {
            console.log('displayFinalResults called with data:', data);
            
            const sadContainer = document.getElementById('sadContainer');

            console.log('Container found:', {
                sad: !!sadContainer
            });

            // Clear placeholder text
            sadContainer.innerHTML = '';

            // Display only sad image
            if (data.sad_image_url) {
                console.log('Displaying sad image:', data.sad_image_url);
                const sadImg = document.createElement('img');
                sadImg.src = data.sad_image_url;
                sadImg.style.width = '100%';
                sadImg.style.height = '100%';
                sadImg.style.objectFit = 'cover';
                sadImg.onload = function() {
                    console.log('Sad image loaded successfully');
                };
                sadImg.onerror = function() {
                    console.error('Failed to load sad image:', data.sad_image_url);
                    sadContainer.innerHTML = '<div class="placeholder-text">Sad image failed to load</div>';
                };
                sadContainer.appendChild(sadImg);
            } else {
                console.log('No sad image URL provided');
                sadContainer.innerHTML = '<div class="placeholder-text">No sad image available</div>';
            }
        }

        function displayFallbackResults() {
            const sadContainer = document.getElementById('sadContainer');

            // Clear placeholder text
            sadContainer.innerHTML = '';

            if (firstSelfie) {
                // Show original selfie as fallback for sad section
                const fallbackImg = document.createElement('img');
                fallbackImg.src = firstSelfie;
                fallbackImg.style.width = '100%';
                fallbackImg.style.height = '100%';
                fallbackImg.style.objectFit = 'cover';
                sadContainer.appendChild(fallbackImg);
            } else {
                sadContainer.innerHTML = '<div class="placeholder-text">Error occurred</div>';
            }
        }

        function captureSecondSelfie() {
            const video = document.getElementById('video2');
            const canvas = document.getElementById('canvas2');
            const ctx = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);

            secondSelfie = canvas.toDataURL('image/jpeg');

            if (stream2) {
                stream2.getTracks().forEach(track => track.stop());
            }

            // Move to next step (emotion processing)
            nextStep();
        }


        function playVideo() {
            const video = document.getElementById('satisfying-video');
            video.play();

            // Auto advance after video duration
            video.addEventListener('ended', function() {
                setTimeout(() => {
                    nextStep();
                }, 2000);
            });

            // Also allow clicking to advance
            video.addEventListener('click', function() {
                nextStep();
            });
        }

        function processBothEmotions() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('emotionProcessingContainer').style.display = 'none';

            // Process the second selfie for the final result
            if (secondSelfie) {
                const formData = new FormData();
                formData.append('phone_number', phoneNumber);
                formData.append('selfie_image', secondSelfie);

                fetch('/snickers/process-second-selfie', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Final Emotion Processing Response:', data);

                    // Hide loading and show the emotion processing container
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('emotionProcessingContainer').style.display = 'block';

                    // Display the processed image in the portrait section
                    displayEmotionResult(data);

                    // Show done button after a delay
                    setTimeout(() => {
                        document.getElementById('doneBtn').style.display = 'block';
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error processing final emotion:', error);

                    // Hide loading and show fallback
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('emotionProcessingContainer').style.display = 'block';

                    // Show fallback with original selfie
                    displayEmotionFallback();

                    // Show done button
                    document.getElementById('doneBtn').style.display = 'block';
                });
            } else {
                // No second selfie available, show fallback
                document.getElementById('loading').style.display = 'none';
                document.getElementById('emotionProcessingContainer').style.display = 'block';
                displayEmotionFallback();
                document.getElementById('doneBtn').style.display = 'block';
            }
        }

        function displayEmotionResult(data) {
            const portraitContainer = document.getElementById('emotionPortraitContainer');

            // Clear placeholder text
            portraitContainer.innerHTML = '';

            // Display the processed image
            if (data.happy_image_url) {
                const processedImg = document.createElement('img');
                processedImg.src = data.happy_image_url;
                processedImg.style.width = '100%';
                processedImg.style.height = '100%';
                processedImg.style.objectFit = 'cover';
                processedImg.onerror = function() {
                    console.error('Failed to load processed image:', data.happy_image_url);
                    displayEmotionFallback();
                };
                portraitContainer.appendChild(processedImg);
            } else {
                displayEmotionFallback();
            }
        }

        function displayEmotionFallback() {
            const portraitContainer = document.getElementById('emotionPortraitContainer');

            // Clear placeholder text
            portraitContainer.innerHTML = '';

            if (secondSelfie) {
                // Show original second selfie as fallback
                const fallbackImg = document.createElement('img');
                fallbackImg.src = secondSelfie;
                fallbackImg.style.width = '100%';
                fallbackImg.style.height = '100%';
                fallbackImg.style.objectFit = 'cover';
                portraitContainer.appendChild(fallbackImg);
            } else if (firstSelfie) {
                // Show first selfie as fallback if no second selfie
                const fallbackImg = document.createElement('img');
                fallbackImg.src = firstSelfie;
                fallbackImg.style.width = '100%';
                fallbackImg.style.height = '100%';
                fallbackImg.style.objectFit = 'cover';
                portraitContainer.appendChild(fallbackImg);
            } else {
                portraitContainer.innerHTML = '<div class="placeholder-text">No image available</div>';
            }
        }

        function processSelfieWithAI(selfieData) {
            const formData = new FormData();
            formData.append('phone_number', phoneNumber);
            formData.append('selfie_image', selfieData);

            fetch('/snickers/capture', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';

                console.log('AI Processing Response:', data); // Debug log

                if (data.success) {
                    // Display both images
                    const sadContainer = document.getElementById('sadContainer');
                    const happyContainer = document.getElementById('happyContainer');

                    // Clear placeholder text
                    sadContainer.innerHTML = '';
                    happyContainer.innerHTML = '';

                    // Add images
                    const sadImg = document.createElement('img');
                    sadImg.src = data.sad_image_url;
                    sadImg.style.width = '100%';
                    sadImg.style.height = '100%';
                    sadImg.style.objectFit = 'cover';
                    sadImg.onerror = function() {
                        console.error('Failed to load sad image:', data.sad_image_url);
                        sadContainer.innerHTML = '<div class="placeholder-text">Image failed to load</div>';
                    };
                    sadContainer.appendChild(sadImg);

                    const happyImg = document.createElement('img');
                    happyImg.src = data.happy_image_url;
                    happyImg.style.width = '100%';
                    happyImg.style.height = '100%';
                    happyImg.style.objectFit = 'cover';
                    happyImg.onerror = function() {
                        console.error('Failed to load happy image:', data.happy_image_url);
                        happyContainer.innerHTML = '<div class="placeholder-text">Image failed to load</div>';
                    };
                    happyContainer.appendChild(happyImg);

                    // Add Snickers overlay to happy section
                    const snickersOverlay = document.createElement('div');
                    snickersOverlay.className = 'snickers-overlay';
                    snickersOverlay.innerHTML = '<div class="mini-snickers-bar"></div>';
                    happyContainer.appendChild(snickersOverlay);

                    // Show done button
                    document.getElementById('doneBtn').style.display = 'block';
                } else {
                    console.error('AI Processing failed:', data.message);
                    // Show fallback with original selfie
                    const sadContainer = document.getElementById('sadContainer');
                    const happyContainer = document.getElementById('happyContainer');

                    // Show original selfie in both sections as fallback
                    const fallbackImg1 = document.createElement('img');
                    fallbackImg1.src = secondSelfie;
                    fallbackImg1.style.width = '100%';
                    fallbackImg1.style.height = '100%';
                    fallbackImg1.style.objectFit = 'cover';
                    sadContainer.appendChild(fallbackImg1);

                    const fallbackImg2 = document.createElement('img');
                    fallbackImg2.src = secondSelfie;
                    fallbackImg2.style.width = '100%';
                    fallbackImg2.style.height = '100%';
                    fallbackImg2.style.objectFit = 'cover';
                    happyContainer.appendChild(fallbackImg2);

                    // Add Snickers overlay to happy section
                    const snickersOverlay = document.createElement('div');
                    snickersOverlay.className = 'snickers-overlay';
                    snickersOverlay.innerHTML = '<div class="mini-snickers-bar"></div>';
                    happyContainer.appendChild(snickersOverlay);

                    document.getElementById('doneBtn').style.display = 'block';
                }
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                console.error('Error:', error);

                // Show fallback with original selfie on error
                const sadContainer = document.getElementById('sadContainer');
                const happyContainer = document.getElementById('happyContainer');

                if (secondSelfie) {
                    // Show original selfie in both sections as fallback
                    const fallbackImg1 = document.createElement('img');
                    fallbackImg1.src = secondSelfie;
                    fallbackImg1.style.width = '100%';
                    fallbackImg1.style.height = '100%';
                    fallbackImg1.style.objectFit = 'cover';
                    sadContainer.appendChild(fallbackImg1);

                    const fallbackImg2 = document.createElement('img');
                    fallbackImg2.src = secondSelfie;
                    fallbackImg2.style.width = '100%';
                    fallbackImg2.style.height = '100%';
                    fallbackImg2.style.objectFit = 'cover';
                    happyContainer.appendChild(fallbackImg2);

                    // Add Snickers overlay to happy section
                    const snickersOverlay = document.createElement('div');
                    snickersOverlay.className = 'snickers-overlay';
                    snickersOverlay.innerHTML = '<div class="mini-snickers-bar"></div>';
                    happyContainer.appendChild(snickersOverlay);
                } else {
                    sadContainer.innerHTML = '<div class="placeholder-text">Error occurred</div>';
                    happyContainer.innerHTML = '<div class="placeholder-text">Error occurred</div>';
                }

                document.getElementById('doneBtn').style.display = 'block';
            });
        }

        function finishCampaign() {
            alert('Thank you for participating in the Snickers campaign! Your photos have been saved.');
            // Reset the campaign
            currentStep = 1;
            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active', 'fade-out');
            });
            document.getElementById('step1').classList.add('active');
            document.getElementById('phoneInput').value = '';
            document.getElementById('finalResultsContainer').style.display = 'none';
            document.getElementById('finalOkBtn').style.display = 'none';

            // Reset variables
            phoneNumber = '';
            firstSelfie = null;
            secondSelfie = null;
            firstSelfieSadResult = null;
            secondSelfieHappyResult = null;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Campaign initialized, starting at step 1');

            // Add some initial animations
            setTimeout(() => {
                const snickersBar = document.querySelector('.snickers-bar-asset');
                if (snickersBar) {
                    snickersBar.style.animation = 'shake 2s infinite';
                }
            }, 1000);
        });
    </script>
</body>
</html>
