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

        .snickers-logo {
            width: 38vh; /* scale with screen height */
            height: auto;
            margin-bottom: 2vh;
            cursor: pointer;
            transition: none;
        }

        .snickers-logo:hover { /* keep size constant */ }

        .asset-image {
            width: 31vh;
            height: auto;
            margin: 3.2vh 0;
            margin-bottom: 30px;
        }

        .snickers-bar-asset {
            width: 34vh;
            height: auto;
            margin: 2vh 0;
            cursor: pointer;
            transition: none;
            animation: shake 2s infinite;
        }

        .snickers-bar-asset:hover { /* keep size constant */ }

        .btn-asset {
            width: 27vh;
            height: auto;
            margin: 2vh 0;
            cursor: pointer;
            transition: none;
        }

        .btn-asset:hover { /* keep size constant */ }

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
            color: #8B4513;
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
            width: 32vh;
            height: 32.1vh;
            border: solid #df0100;
            border-radius: 20px;
            overflow: hidden;
            margin: 1.5vh 0;
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
            color: #8B4513;
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
            color: #8B4513;
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

        @keyframes rotateSnickers {
            0% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(90deg) scale(1.1); }
            50% { transform: rotate(180deg) scale(1); }
            75% { transform: rotate(270deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1); }
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

        .emotion-snickers-bar {
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
        <div class="fullscreen-button" onclick="toggleFullscreen()" title="Toggle fullscreen" id="fullscreenBtn">
            <!-- Corner arrows icon for entering fullscreen -->
            <svg class="fullscreen-enter-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7 3H3v4h2V5h2V3zm12 0h-4v2h2v2h2V3zM5 17H3v4h4v-2H5v-2zm16 0h-2v2h-2v2h4v-4z"/>
            </svg>
            <!-- Compress icon for exiting fullscreen -->
            <svg class="fullscreen-exit-icon" viewBox="0 0 24 24" aria-hidden="true" style="display: none;">
                <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>
            </svg>
        </div>
        <!-- Step 1: Ready Screen -->
        <div class="step active" id="step1">
            <img src="/01/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo" style="width: 38vh; height: auto; margin-bottom: 0.5vh; cursor: pointer; transition: none;position: absolute;top: 10vh;">
            <img src="/01/Ready.png" alt="Ready" class="asset-image" style="position: absolute;top: 24vh;">
            <img src="/01/001_Text_1.png" alt="Ready" class="asset-image" style="position: absolute;top: 60vh;">
            <img src="/01/SNICKERS BAR.png" alt="Snickers Bar" class="snickers-bar-asset" onclick="nextStep()" style="animation: 2s ease 0s infinite normal none running shake;position: absolute;top: 70vh;">
        </div>

        <!-- Step 2: Phone Number -->
        <div class="step" id="step2">
            <img src="/02/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo"  style="width: 38vh; height: auto; margin-bottom: 0.5vh; cursor: pointer; transition: none;position: absolute;top: 10vh;">
            <img src="/02/Enter Number.png" alt="Enter Number" class="asset-image" style="width: 40vh !important;">
            <input type="tel" class="phone-input" id="phoneInput" placeholder="+1234567890" maxlength="20">
            <img src="/02/BT_Continue.png" alt="Continue" class="btn-asset" onclick="nextStep()" style="cursor: pointer;position: absolute;top: 79vh;">
        </div>

        <!-- Step 3: First Selfie -->
        <div class="step" id="step3">
            <img src="/03/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo"  style="width: 38vh; height: auto; margin-bottom: 0.5vh; cursor: pointer; transition: none;position: absolute;top: 10vh;">

            <div class="camera-container">
                <video id="video" autoplay muted></video>
                <canvas id="canvas"></canvas>
                <button id="rotateBtn1" onclick="toggleCamera(1)" style="position: absolute; top: 10px; right: 10px; width: 42px; height: 42px; border-radius: 50%; border: none; background: rgba(0,0,0,0.6); color: #FFD700; font-size: 20px; cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center;">⟳</button>
            </div>
            <img src="/03/Take a Selfie.png" alt="Take a Selfie" class="asset-image">
            <img src="/03/BT_Snap.png" alt="Snap" class="btn-asset" onclick="captureSelfie()" style="cursor: pointer;position: absolute;top: 79vh;">
        </div>

        <!-- Step 4: Final Results -->
        <div class="step" id="step4">


            <div class="emotion-container" id="finalResultsContainer" style="display: none;width: 32vh;max-width: 356px;height: 32.1vh;max-height: 409px;border: solid #df0100;border-radius: 20px;overflow: hidden;margin: 15px 0;position: relative;background: #000;border-radius: 55px 0px 64px 0px;">
                <div class="emotion-section single-image-section">
                    <div class="emotion-image-container" id="sadContainer">
                        <div class="placeholder-text">Processing...</div>
                    </div>
                </div>
            </div>

            <img src="/uis/05/05_Text.png" alt="Snickers Logo" class="snickers-logo" style="width: 38vh;height: auto;margin-bottom: 5vh;cursor: pointer;transition: none;margin-top: 3vh;">

            <div class="loading" id="hungryLoading" style="display: none;">
                <div class="snickers-animation-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; background: #000; border-radius: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    <img src="/01/SNICKERS BAR.png" alt="Snickers Bar" class="rotating-snickers" style="width: 200px; height: auto; animation: rotateSnickers 2s linear infinite;">
                    <div class="processing-text" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #FFD700; font-size: 18px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                        Processing your photos...
                    </div>
                </div>
            </div>

            <img src="/uis/05/Next_Button.png" alt="OK" class="btn-asset" onclick="finishCampaign()" style="cursor: pointer; display: none;" id="finalOkBtn">
        </div>

        <!-- Step 5: Video -->
        <div class="step" id="step5">
            <div class="snickers-animation-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; background: #000; border-radius: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <img src="/01/SNICKERS BAR.png" alt="Snickers Bar" class="rotating-snickers" style="width: 200px; height: auto; animation: rotateSnickers 2s linear infinite;">
                <div class="processing-text" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #FFD700; font-size: 18px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                    Processing your photos...
                </div>
            </div>

            <div class="video-container">
                <video id="satisfying-video" autoplay muted loop style="display: none !important;">
                    <source src="/05/SNK SATISFYING VIDEO IGS.mp4" type="video/mp4">
                </video>
            </div>

            <!-- Processing Status Overlay -->
            <div id="processingStatusOverlay" style="position: absolute; top: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: #FFD700; padding: 15px; border-radius: 10px; text-align: center; font-size: 16px; font-weight: bold; z-index: 1000;">

                <div class="loading" id="hungryLoading" style="display: none;">
                    <div class="snickers-animation-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; background: #000; border-radius: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img src="/01/SNICKERS BAR.png" alt="Snickers Bar" class="rotating-snickers" style="width: 200px; height: auto; animation: rotateSnickers 2s linear infinite;">
                        <div class="processing-text" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #FFD700; font-size: 18px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                            Processing your photos...
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <div class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></div>
                </div>
            </div>
        </div>

        <!-- Step 6: Second Selfie -->
        <div class="step" id="step6">
            <img src="/06/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo" style="width: 38vh; height: auto; margin-bottom: 2vh; cursor: pointer; transition: none;">
            <img src="/06/Take a Selfie.png" alt="Take a Selfie" class="asset-image">
            <div class="camera-container">
                <video id="video2" autoplay muted style="position: absolute;right: -90%;top: -130px;"></video>
                <canvas id="canvas2"></canvas>
                <button id="rotateBtn2" onclick="toggleCamera(2)" style="position: absolute; top: 10px; right: 10px; width: 42px; height: 42px; border-radius: 50%; border: none; background: rgba(0,0,0,0.6); color: #FFD700; font-size: 20px; cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center;">⟳</button>
            </div>
            <img src="/06/BT_Snap.png" alt="Snap" class="btn-asset" onclick="captureSecondSelfie()" style="cursor: pointer;">
        </div>

        <!-- Step 7: Emotion Processing & Result -->
        <div class="step" id="step7">
            <img src="/07/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo" style="width: 38vh; height: auto; margin-bottom: 2vh; cursor: pointer; transition: none;">

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

        <!-- Step 8: Thank You Screen -->
        <div class="step" id="step8" onclick="restartCampaign()">
            <img src="/uis/06/06_info.png" alt="Snickers Logo" class="asset-image" style="width: 41vh !important;">
            <img src="/01/SNICKERS LOGO.png" alt="Snickers Logo" class="snickers-logo" style="width: 38vh; height: auto; margin-bottom: 2vh; cursor: pointer; transition: none;">
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
        let currentFacing1 = 'user';
        let currentFacing2 = 'user';

        function nextStep() {
            console.log('nextStep called, current step:', currentStep);

            if (currentStep === 2) {
                const phoneInput = document.getElementById('phoneInput');
                phoneNumber = phoneInput.value;

                if (!phoneNumber) {
                    // Remove any existing error state
                    phoneInput.classList.remove('error');

                    // Add error styling
                    phoneInput.classList.add('error');
                    phoneInput.placeholder = 'Please enter your phone number';

                    // Clear error after user starts typing
                    phoneInput.addEventListener('input', function() {
                        this.classList.remove('error');
                        this.placeholder = '+1234567890';
                    }, { once: true });

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
                        // Process first selfie for sad emotion and navigate to video
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

        function stopStream(activeStream) {
            if (activeStream) {
                activeStream.getTracks().forEach(track => track.stop());
            }
        }

        function startCamera(facing = 'user') {
            // Stop existing stream before starting a new one
            stopStream(stream);

            const constraints = { video: { facingMode: { ideal: facing } }, audio: false };
            navigator.mediaDevices.getUserMedia(constraints)
                .then(function(mediaStream) {
                    stream = mediaStream;
                    const video = document.getElementById('video');
                    video.srcObject = mediaStream;
                    currentFacing1 = facing;
                })
                .catch(function(err) {
                    console.warn('Preferred facing failed, retrying without facingMode. Error:', err);
                    return navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                })
                .then(function(fallbackStream) {
                    if (!fallbackStream) return; // already set by first then
                    stream = fallbackStream;
                    const video = document.getElementById('video');
                    video.srcObject = fallbackStream;
                })
                .catch(function(err) {
                    console.error('Error accessing camera:', err);
                    alert('Camera access denied. Please allow camera access to continue.');
                });
        }

        function startSecondCamera(facing = 'user') {
            // Stop existing stream before starting a new one
            stopStream(stream2);

            const constraints = { video: { facingMode: { ideal: facing } }, audio: false };
            navigator.mediaDevices.getUserMedia(constraints)
                .then(function(mediaStream) {
                    stream2 = mediaStream;
                    const video = document.getElementById('video2');
                    video.srcObject = mediaStream;
                    currentFacing2 = facing;
                })
                .catch(function(err) {
                    console.warn('Preferred facing failed (second), retrying without facingMode. Error:', err);
                    return navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                })
                .then(function(fallbackStream) {
                    if (!fallbackStream) return;
                    stream2 = fallbackStream;
                    const video = document.getElementById('video2');
                    video.srcObject = fallbackStream;
                })
                .catch(function(err) {
                    console.error('Error accessing camera (second):', err);
                    alert('Camera access denied. Please allow camera access to continue.');
                });
        }

        function toggleCamera(which) {
            if (which === 1) {
                const nextFacing = currentFacing1 === 'user' ? 'environment' : 'user';
                startCamera(nextFacing);
            } else if (which === 2) {
                const nextFacing = currentFacing2 === 'user' ? 'environment' : 'user';
                startSecondCamera(nextFacing);
            }
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
                    // Store the generated image ID for later use
                    window.generatedImageId = data.generated_image_id;

                    // Navigate to video step (step 5) immediately after job is queued
                    setTimeout(() => {
                        // Hide current step
                        document.getElementById('step4').classList.add('fade-out');

                        setTimeout(() => {
                            document.getElementById('step4').classList.remove('active', 'fade-out');

                            // Show video step
                            currentStep = 5;
                            document.getElementById('step5').classList.add('active');

                            // Start video playback and polling
                            playVideo();
                        }, 500);
                    }, 1000); // Wait 1 second to show the job queued message
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

            // Process second selfie with AI
            processSecondSelfie();
        }

        function processSecondSelfie() {
            if (!secondSelfie) {
                console.error('No second selfie data available');
                nextStep();
                return;
            }

            // Process the second selfie with AI for happy emotion
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
                console.log('Second Selfie Processing Response:', data);

                if (data.success) {
                    // Store the happy image result
                    secondSelfieHappyResult = data.happy_image_url;

                    // Move to next step (final emotion processing)
                    nextStep();
                } else {
                    console.error('Second selfie processing failed:', data.message);
                    // Still move to next step with fallback
                    nextStep();
                }
            })
            .catch(error => {
                console.error('Error processing second selfie:', error);
                // Still move to next step with fallback
                nextStep();
            });
        }


        function playVideo() {
            const video = document.getElementById('satisfying-video');
            video.play();

            // Start polling for sad image processing status
            startPollingForSadImage();

            // Auto advance after video duration
            video.addEventListener('ended', function() {
                // Stop polling when video ends
                stopPolling();

                setTimeout(() => {
                    // Navigate to step 6 (second selfie)
                    document.getElementById('step5').classList.add('fade-out');

                    setTimeout(() => {
                        document.getElementById('step5').classList.remove('active', 'fade-out');

                        currentStep = 6;
                        document.getElementById('step6').classList.add('active');

                        // Start second camera
                        startSecondCamera();
                    }, 500);
                }, 2000);
            });
        }

        let pollingInterval = null;

        function startPollingForSadImage() {
            if (!window.generatedImageId) {
                console.error('No generated image ID available for polling');
                return;
            }

            console.log('Starting polling for sad image processing...');

            // Show processing status overlay


            pollingInterval = setInterval(() => {
                checkSadImageStatus();
            }, 3000); // Poll every 3 seconds
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                console.log('Stopped polling for sad image processing');

                // Hide processing status overlay
                const overlay = document.getElementById('processingStatusOverlay');
                if (overlay) {
                    overlay.style.display = 'none';
                }
            }
        }

        function checkSadImageStatus() {
            if (!window.generatedImageId) {
                console.error('No generated image ID available');
                return;
            }

            fetch('/snickers/check-job-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    generated_image_id: window.generatedImageId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Job status check response:', data);

                if (data.success) {
                    // Update status message based on job status
                    updateProcessingStatus(data.job_status);

                    // Check if sad image processing is completed
                    if (data.sad_image_url && data.job_status === 'completed') {
                        console.log('Sad image processing completed! Navigating to results...');

                        // Stop polling
                        stopPolling();

                        // Store the sad image result
                        window.sadImageResult = data.sad_image_url;

                        // Navigate to results page (step 4) with the processed image
                        setTimeout(() => {
                            navigateToResultsWithSadImage(data);
                        }, 1000);
                    } else if (data.job_status === 'failed') {
                        console.log('Sad image processing failed, using fallback');

                        // Stop polling
                        stopPolling();

                        // Navigate to results with fallback
                        setTimeout(() => {
                            navigateToResultsWithFallback();
                        }, 1000);
                    }
                    // If still processing (queued/processing), continue polling
                } else {
                    console.error('Failed to check job status:', data.message);
                }
            })
            .catch(error => {
                console.error('Error checking job status:', error);
            });
        }

        function updateProcessingStatus(status) {
            const overlay = document.getElementById('processingStatusOverlay');
            if (!overlay) return;

            const statusText = overlay.querySelector('span');
            if (!statusText) return;

            switch (status) {
                case 'queued':
                    statusText.textContent = 'Job queued, waiting to start...';
                    break;
                case 'processing':
                    statusText.textContent = 'Processing your sad emotion...';
                    break;
                case 'completed':
                    statusText.textContent = 'Processing completed!';
                    break;
                case 'failed':
                    statusText.textContent = 'Processing failed, using fallback...';
                    break;
                default:
                    statusText.textContent = 'Processing your sad emotion...';
            }
        }

        function navigateToResultsWithSadImage(data) {
            // Hide video step
            document.getElementById('step5').classList.add('fade-out');

            setTimeout(() => {
                document.getElementById('step5').classList.remove('active', 'fade-out');

                // Show results step (step 4)
                currentStep = 4;
                document.getElementById('step4').classList.add('active');

                // Display the processed sad image
                displayFinalResults({
                    sad_image_url: data.sad_image_url,
                    original_image_url: data.original_image_url
                });

                // Show the results container and OK button
                document.getElementById('finalResultsContainer').style.display = 'block';
                document.getElementById('finalOkBtn').style.display = 'block';

                console.log('Navigated to results with processed sad image');
            }, 500);
        }

        function navigateToResultsWithFallback() {
            // Hide video step
            document.getElementById('step5').classList.add('fade-out');

            setTimeout(() => {
                document.getElementById('step5').classList.remove('active', 'fade-out');

                // Show results step (step 4)
                currentStep = 4;
                document.getElementById('step4').classList.add('active');

                // Display fallback results
                displayFallbackResults();

                // Show the results container and OK button
                document.getElementById('finalResultsContainer').style.display = 'block';
                document.getElementById('finalOkBtn').style.display = 'block';

                console.log('Navigated to results with fallback image');
            }, 500);
        }

        function processBothEmotions() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('emotionProcessingContainer').style.display = 'none';

            // Use the already processed results
            setTimeout(() => {
                // Hide loading and show the emotion processing container
                document.getElementById('loading').style.display = 'none';
                document.getElementById('emotionProcessingContainer').style.display = 'block';

                // Display the processed image in the portrait section
                displayEmotionResult({
                    happy_image_url: secondSelfieHappyResult
                });

                // Show done button after a delay
                setTimeout(() => {
                    document.getElementById('doneBtn').style.display = 'block';
                }, 2000);
            }, 1500); // Simulate processing time
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
            // Navigate to thank you screen (step 8)
            const currentStepEl = document.getElementById(`step${currentStep}`);
            if (currentStepEl) {
                currentStepEl.classList.add('fade-out');

                setTimeout(() => {
                    currentStepEl.classList.remove('active', 'fade-out');
                    currentStep = 8;
                    document.getElementById('step8').classList.add('active');
                }, 500);
            }
        }

        function restartCampaign() {
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

        // Fullscreen functionality
        function toggleFullscreen() {
            const container = document.querySelector('.container');
            const indicator = document.querySelector('.fullscreen-indicator');

            if (container.classList.contains('fullscreen')) {
                // Exit fullscreen
                exitBrowserFullscreen();
            } else {
                // Enter fullscreen
                enterBrowserFullscreen();
            }
        }

        function enterBrowserFullscreen() {
            const container = document.querySelector('.container');
            const indicator = document.querySelector('.fullscreen-indicator');
            const enterIcon = document.querySelector('.fullscreen-enter-icon');
            const exitIcon = document.querySelector('.fullscreen-exit-icon');

            const el = container;
            const request = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen || el.mozRequestFullScreen;
            if (request) {
                request.call(el).catch(() => {});
            }

            container.classList.add('fullscreen');
            if (indicator) indicator.textContent = 'Double-click to exit fullscreen';
            if (enterIcon) enterIcon.style.display = 'none';
            if (exitIcon) exitIcon.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function exitBrowserFullscreen() {
            const container = document.querySelector('.container');
            const indicator = document.querySelector('.fullscreen-indicator');
            const enterIcon = document.querySelector('.fullscreen-enter-icon');
            const exitIcon = document.querySelector('.fullscreen-exit-icon');

            const exit = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen || document.mozCancelFullScreen;
            if (exit && document.fullscreenElement) {
                exit.call(document).catch(() => {});
            }

            container.classList.remove('fullscreen');
            if (indicator) indicator.textContent = 'Double-click to enter fullscreen';
            if (enterIcon) enterIcon.style.display = 'block';
            if (exitIcon) exitIcon.style.display = 'none';
            document.body.style.overflow = 'hidden';
        }

        // Keep UI in sync if user exits fullscreen via Esc or system controls
        document.addEventListener('fullscreenchange', () => {
            const container = document.querySelector('.container');
            const indicator = document.querySelector('.fullscreen-indicator');
            const enterIcon = document.querySelector('.fullscreen-enter-icon');
            const exitIcon = document.querySelector('.fullscreen-exit-icon');

            if (!document.fullscreenElement) {
                container.classList.remove('fullscreen');
                if (indicator) indicator.textContent = 'Double-click to enter fullscreen';
                if (enterIcon) enterIcon.style.display = 'block';
                if (exitIcon) exitIcon.style.display = 'none';
            } else {
                container.classList.add('fullscreen');
                if (indicator) indicator.textContent = 'Double-click to exit fullscreen';
                if (enterIcon) enterIcon.style.display = 'none';
                if (exitIcon) exitIcon.style.display = 'block';
            }
            document.body.style.overflow = 'hidden';
        });

        // Add keyboard support for fullscreen (ESC key)
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const container = document.querySelector('.container');
                if (container.classList.contains('fullscreen')) {
                    toggleFullscreen();
                }
            }
        });

        // Prevent context menu on double-click
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
