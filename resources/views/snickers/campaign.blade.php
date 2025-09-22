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

        .step.step1 {
            background-image: url('/01/BG.jpg');
        }

        .step.step2 {
            background-image: url('/02/BG.jpg');
        }

        .step.step3 {
            background-image: url('/03/BG.jpg');
        }

        .step.step4 {
            background-image: url('/04/BG.jpg');
        }

        .step.step5 {
            background-image: url('/05/BG_FRAME.png');
        }

        .step.step6 {
            background-image: url('/06/BG.jpg');
        }

        .step.step7 {
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
            max-width: 80%;
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
            width: 80%;
            max-width: 350px;
            height: 60vh;
            max-height: 400px;
            border: 5px solid #FFD700;
            border-radius: 20px;
            overflow: hidden;
            margin: 15px 0;
            position: relative;
            background: #000;
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
            position: absolute;
        }

        .emotion-section {
            height: 50%;
            position: relative;
            border-bottom: 2px solid white;
        }

        .emotion-section:last-child {
            border-bottom: none;
        }

        .emotion-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
            color: black;
            z-index: 5;
            text-shadow: 2px 2px 4px rgba(255,255,255,0.8);
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Step 1: Ready Screen -->
        <div class="step step1 active" id="step1">
            <img src="/01/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/01/Ready.png" alt="Ready" class="asset-image">
            <img src="/01/SNICKERS BAR.png" alt="Snickers Bar" class="snickers-bar-asset" onclick="nextStep()">
        </div>

        <!-- Step 2: Phone Number -->
        <div class="step step2" id="step2">
            <img src="/02/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/02/Enter Number.png" alt="Enter Number" class="asset-image">
            <input type="tel" class="phone-input" id="phoneInput" placeholder="+1234567890" maxlength="20">
            <img src="/02/BT_Continue.png" alt="Continue" class="btn-asset" onclick="nextStep()" style="cursor: pointer;">
        </div>

        <!-- Step 3: First Selfie -->
        <div class="step step3" id="step3">
            <img src="/03/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/03/Take a Selfie.png" alt="Take a Selfie" class="asset-image">
            <div class="camera-container">
                <video id="video" autoplay muted></video>
                <canvas id="canvas"></canvas>
                <img src="/03/Selfie_Frame.png" alt="Selfie Frame" class="selfie-frame-overlay">
            </div>
            <img src="/03/BT_Snap.png" alt="Snap" class="btn-asset" onclick="captureSelfie()" style="cursor: pointer;">
        </div>

        <!-- Step 4: Hungry Detection -->
        <div class="step step4" id="step4">
            <img src="/04/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/04/04_TEXT.png" alt="Hungry Text" class="asset-image">
            <img src="/04/BT_OK.png" alt="OK" class="btn-asset" onclick="nextStep()" style="cursor: pointer;">
        </div>

        <!-- Step 5: Video -->
        <div class="step step5" id="step5">
            <div class="video-container">
                <video id="satisfying-video" autoplay muted loop>
                    <source src="/05/SNK SATISFYING VIDEO IGS.mp4" type="video/mp4">
                </video>
            </div>
        </div>

        <!-- Step 6: Second Selfie -->
        <div class="step step6" id="step6">
            <img src="/06/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            <img src="/06/Take a Selfie.png" alt="Take a Selfie" class="asset-image">
            <div class="camera-container">
                <video id="video2" autoplay muted></video>
                <canvas id="canvas2"></canvas>
                <img src="/06/Selfie_Frame.png" alt="Selfie Frame" class="selfie-frame-overlay">
            </div>
            <img src="/06/BT_Snap.png" alt="Snap" class="btn-asset" onclick="captureSecondSelfie()" style="cursor: pointer;">
        </div>

        <!-- Step 7: Emotion Processing & Result -->
        <div class="step step7" id="step7">
            <img src="/07/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo">
            
            <div class="emotion-container">
                <div class="emotion-section">
                    <div class="emotion-label">SAD</div>
                    <div class="emotion-image-container" id="sadContainer">
                        <div class="placeholder-text">Processing...</div>
                    </div>
                </div>
                <div class="emotion-section">
                    <div class="emotion-label">HAPPY</div>
                    <div class="emotion-image-container" id="happyContainer">
                        <div class="placeholder-text">Processing...</div>
                        <div class="snickers-overlay">
                            <div class="mini-snickers-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                Processing both emotions...
            </div>
            
            <img src="/07/Frame_New.png" alt="Frame" class="asset-image">
            <img src="/07/BT_done.png" alt="Done" class="btn-asset" onclick="finishCampaign()" style="display: none; cursor: pointer;" id="doneBtn">
        </div>
    </div>

    <script>
        let currentStep = 1;
        let phoneNumber = '';
        let firstSelfie = null;
        let secondSelfie = null;
        let stream = null;
        let stream2 = null;

        function nextStep() {
            if (currentStep === 2) {
                phoneNumber = document.getElementById('phoneInput').value;
                if (!phoneNumber) {
                    alert('Please enter your phone number');
                    return;
                }
            }

            const currentStepEl = document.getElementById(`step${currentStep}`);
            currentStepEl.classList.add('fade-out');
            
            setTimeout(() => {
                currentStepEl.classList.remove('active', 'fade-out');
                currentStep++;
                const nextStepEl = document.getElementById(`step${currentStep}`);
                nextStepEl.classList.add('active', `step${currentStep}`);
                
                if (currentStep === 3) {
                    startCamera();
                } else if (currentStep === 5) {
                    playVideo();
                } else if (currentStep === 6) {
                    startSecondCamera();
                } else if (currentStep === 7) {
                    // Automatically process both emotions
                    processBothEmotions();
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
            
            // Check if we have a selfie to process
            if (!secondSelfie) {
                console.error('No selfie data available');
                document.getElementById('loading').style.display = 'none';
                
                // Show fallback with original selfie
                const sadContainer = document.getElementById('sadContainer');
                const happyContainer = document.getElementById('happyContainer');
                sadContainer.innerHTML = '<div class="placeholder-text">No selfie captured</div>';
                happyContainer.innerHTML = '<div class="placeholder-text">No selfie captured</div>';
                document.getElementById('doneBtn').style.display = 'block';
                return;
            }
            
            // Process the selfie with AI for both emotions
            processSelfieWithAI(secondSelfie);
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
            alert('Thank you for participating in the Snickers campaign!');
            // Reset the campaign
            currentStep = 1;
            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active', 'fade-out');
            });
            document.getElementById('step1').classList.add('active');
            document.getElementById('phoneInput').value = '';
            document.getElementById('resultImage').style.display = 'none';
            document.getElementById('doneBtn').style.display = 'none';
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add step class to first step
            document.getElementById('step1').classList.add('step1');
            
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
