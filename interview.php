<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$scenario = $_POST['scenario'] ?? 'General Interview';
$interviewer_role = $_POST['interviewer_role'] ?? 'Interviewer';
$user_role = $_POST['user_role'] ?? 'Candidate';
$duration = $_POST['duration'] ?? 5; // Default 5 minutes
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Mode - AI Speaking</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Specific styles for interview page that extend the base style.css */
        body {
            height: 100vh;
            overflow: hidden; /* Prevent body scroll, let chat box scroll */
            display: flex;
            flex-direction: column;
        }

        .interview-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 1000px;
            margin: 20px auto;
            width: 95%;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }

        .interview-header {
            padding: 15px 20px;
            background: var(--primary-color);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .interview-header h3 {
            margin: 0;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .timer-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .timer-warning {
            background: #ff4757;
            animation: pulse-red 1s infinite;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 71, 87, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 71, 87, 0); }
        }

        .chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Message Styles Override/Enhancement */
        .message {
            display: flex;
            align-items: flex-end;
            margin-bottom: 15px;
            max-width: 80%;
        }
        
        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.ai {
            align-self: flex-start;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .message.ai .avatar {
            background: var(--accent-color);
            margin-right: 10px;
        }

        .message.user .avatar {
            background: var(--primary-color);
            margin-left: 10px;
        }

        .bubble {
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 1rem;
            line-height: 1.5;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .message.ai .bubble {
            background: white;
            color: var(--text-color);
            border-top-left-radius: 4px;
        }

        .message.user .bubble {
            background: var(--primary-color);
            color: white;
            border-top-right-radius: 4px;
        }

        .control-panel {
            padding: 15px 20px;
            background: white;
            border-top: 1px solid #eee;
        }

        .status-bar {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 10px;
            text-align: center;
            font-style: italic;
        }

        .input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .mic-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            background: var(--danger-color); /* Default red for mic off/ready */
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }

        .mic-btn:hover {
            transform: scale(1.05);
        }

        .mic-btn.listening {
            background: var(--success-color);
            animation: pulse-green 1.5s infinite;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }

        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(40, 167, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
        }

        .text-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #eee;
            border-radius: 25px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: 'Prompt', sans-serif;
        }

        .text-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .send-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: transform 0.2s;
        }

        .send-btn:hover {
            transform: scale(1.05);
            background: var(--primary-hover);
        }

        .settings-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }

        .voice-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .voice-select {
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 0.85rem;
            max-width: 200px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 700px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .evaluation-score {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>

    <div class="interview-wrapper">
        <div class="interview-header">
            <h3><i class="fas fa-user-tie"></i> Interview Room</h3>
            <div id="timerDisplay" class="timer-badge">
                <i class="fas fa-stopwatch"></i> <span>00:00</span>
            </div>
        </div>

        <div class="chat-area" id="chatBox">
            <!-- Chat messages will appear here -->
        </div>

        <div class="control-panel">
            <div class="status-bar" id="statusBar">Ready to start...</div>
            
            <div class="input-wrapper">
                <button id="micBtn" class="mic-btn" onclick="toggleMic()" title="กดเพื่อพูด">
                    <i class="fas fa-microphone"></i>
                </button>
                <input type="text" id="textInput" class="text-input" placeholder="พิมพ์ข้อความ หรือกดไมค์เพื่อพูด..." onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()" class="send-btn" title="ส่งข้อความ">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>

            <div class="settings-panel">
                <!-- Voice Controls Removed (Using F5-TTS Only) -->
                <div class="voice-controls" style="display: none;">
                    <i class="fas fa-volume-up" style="color: var(--text-muted);"></i>
                    <select id="voiceSelect" class="voice-select"></select>
                    <button onclick="testVoice()" class="btn secondary" style="padding: 4px 10px; font-size: 0.8rem;">Test</button>
                </div>
                
                <!-- Cloud TTS Checkbox Removed (Always On) -->
                <div style="display: none; align-items: center; gap: 5px;">
                    <input type="checkbox" id="useCloudTTS" checked disabled>
                    <label for="useCloudTTS" style="font-size: 0.85rem; cursor: pointer; color: var(--primary-color);">
                        <i class="fas fa-cloud"></i> AI Voice (HQ)
                    </label>
                </div>

                <a href="setup_interview.php" class="btn danger" style="padding: 5px 15px; font-size: 0.85rem;">
                    <i class="fas fa-sign-out-alt"></i> จบการสัมภาษณ์
                </a>
            </div>
        </div>
    </div>

    <script>
        const scenario = <?php echo json_encode($scenario); ?>;
        const interviewerRole = <?php echo json_encode($interviewer_role); ?>;
        const userRole = <?php echo json_encode($user_role); ?>;
        const durationMinutes = <?php echo json_encode($duration); ?>;
        
        const chatBox = document.getElementById('chatBox');
        const statusBar = document.getElementById('statusBar');
        const micBtn = document.getElementById('micBtn');
        const textInput = document.getElementById('textInput');
        const voiceSelect = document.getElementById('voiceSelect');
        const timerDisplay = document.getElementById('timerDisplay');
        
        let recognition;
        let isListening = false;
        let timerInterval;
        let timeLeft = durationMinutes * 60; // seconds
        let isInterviewEnded = false;

        let conversationHistory = [
            {
                role: "system",
                content: `คุณคือ ${interviewerRole} ผู้ใช้คือ ${userRole}
                รายละเอียดสถานการณ์: ${scenario}. 
                เริ่มด้วยการแนะนำตัวและถามคำถามแรก
                ตอบให้กระชับ (2-3 ประโยค) เพื่อให้บทสนทนาลื่นไหล
                พูดภาษาไทยเท่านั้น`
            }
        ];

        // Initialize Web Speech API
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.lang = 'th-TH';
            recognition.interimResults = false;

            recognition.onstart = function() {
                isListening = true;
                micBtn.classList.add('listening');
                micBtn.innerHTML = '<i class="fas fa-wave-square"></i>'; // Change icon when listening
                statusBar.innerText = "กำลังฟัง...";
            };

            recognition.onend = function() {
                isListening = false;
                micBtn.classList.remove('listening');
                micBtn.innerHTML = '<i class="fas fa-microphone"></i>'; // Revert icon
                statusBar.innerText = "ประมวลผล...";
                // Auto send if text is present
                if (textInput.value.trim() !== "") {
                    sendMessage();
                }
            };

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                textInput.value = transcript;
            };
        } else {
            alert("Browser not supported for Speech Recognition. Please use Chrome.");
        }

        function toggleMic() {
            if (isListening) {
                recognition.stop();
            } else {
                recognition.start();
            }
        }

        function handleKeyPress(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        function addMessage(role, text) {
            const div = document.createElement('div');
            div.className = `message ${role}`;
            
            let avatarIcon = role === 'ai' ? '<i class="fas fa-user-tie"></i>' : '<i class="fas fa-user"></i>';
            if (role === 'system') avatarIcon = '<i class="fas fa-cog"></i>';

            div.innerHTML = `
                <div class="avatar">${avatarIcon}</div>
                <div class="bubble">${text}</div>
            `;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function sendMessage() {
            const text = textInput.value.trim();
            if (!text) return;

            // 1. Show User Message
            addMessage('user', text);
            textInput.value = '';
            conversationHistory.push({ role: "user", content: text });

            // 2. Call API
            statusBar.innerText = "AI กำลังคิด...";
            
            try {
                const response = await fetch('api/interview_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ history: conversationHistory })
                });
                
                const data = await response.json();
                
                if (data.error) {
                    alert("Error: " + data.error);
                    return;
                }

                const aiText = data.reply;
                
                // 3. Show AI Message
                addMessage('ai', aiText);
                conversationHistory.push({ role: "model", content: aiText });
                
                // 4. Speak AI Message
                speak(aiText);
                statusBar.innerText = "ตาคุณแล้ว";

            } catch (error) {
                console.error('Error:', error);
                statusBar.innerText = "Error connecting to AI";
            }
        }

        const useCloudTTSCheckbox = document.getElementById('useCloudTTS');

        function toggleCloudTTS() {
            if (useCloudTTSCheckbox.checked) {
                voiceSelect.disabled = true;
            } else {
                voiceSelect.disabled = false;
            }
        }

        async function speak(text) {
            // Use Edge TTS (via Python backend)
            statusBar.innerText = "กำลังสร้างเสียง AI...";
            try {
                const response = await fetch('api/tts_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ text: text })
                });
                const data = await response.json();

                if (data.audioContent) {
                    // Support both MP3 and WAV
                    // WAV starts with "UklGR" (RIFF)
                    // MP3 usually starts with "SUQz" (ID3) or "//" (MPEG frame sync)
                    const isWav = data.audioContent.startsWith('UklGR');
                    const mimeType = isWav ? 'audio/wav' : 'audio/mpeg'; 
                    
                    const audio = new Audio(`data:${mimeType};base64,` + data.audioContent);
                    audio.play().catch(e => {
                        console.error("Audio Playback Error:", e);
                        if (e.name === 'NotAllowedError') {
                            statusBar.innerText = "⚠️ กรุณาคลิกที่นี่เพื่อเปิดเสียง";
                            statusBar.style.cursor = "pointer";
                            statusBar.onclick = () => {
                                audio.play();
                                statusBar.innerText = "กำลังพูด...";
                                statusBar.style.cursor = "default";
                                statusBar.onclick = null;
                            };
                        } else {
                            statusBar.innerText = "Play Error: " + e.message;
                        }
                    });
                    
                    audio.onended = () => { statusBar.innerText = "ตาคุณแล้ว"; };
                    statusBar.innerText = "กำลังพูด...";
                } else {
                    console.warn("Cloud TTS Failed", data);
                    let errorMsg = "ไม่สามารถใช้เสียง AI ได้";
                    if (data.details && data.details.error) {
                        errorMsg += ": " + data.details.error;
                    } else if (data.error) {
                        errorMsg += ": " + data.error;
                    }
                    alert(errorMsg);
                    statusBar.innerText = "Error: TTS Failed";
                }
            } catch (e) {
                console.error("Cloud TTS Error:", e);
                alert("เกิดข้อผิดพลาดในการเชื่อมต่อกับเสียง AI");
                statusBar.innerText = "Error: Connection Failed";
            }
        }

        function testVoice() {
            speak("สวัสดีครับ นี่คือเสียงทดสอบสำหรับการสัมภาษณ์");
        }

        // Start the conversation automatically
        window.onload = async function() {
            startTimer();
            
            // Trigger initial AI greeting
            statusBar.innerText = "กำลังเริ่มการสัมภาษณ์...";
            try {
                const response = await fetch('api/interview_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ history: conversationHistory })
                });
                const data = await response.json();

                if (data.error) {
                    addMessage('ai', "System Error: " + data.error);
                    statusBar.innerText = "Error occurred";
                    return;
                }

                const aiText = data.reply;
                addMessage('ai', aiText);
                conversationHistory.push({ role: "model", content: aiText });
                speak(aiText);
                statusBar.innerText = "เริ่มสัมภาษณ์";
            } catch (e) {
                console.error(e);
                addMessage('ai', "Connection Error: " + e.message);
            }
        };

        function startTimer() {
            updateTimerDisplay();
            timerInterval = setInterval(() => {
                timeLeft--;
                updateTimerDisplay();
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    endInterview();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const display = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            timerDisplay.querySelector('span').textContent = display;
            
            if (timeLeft <= 60) {
                timerDisplay.classList.add('timer-warning');
            }
        }

        async function endInterview() {
            if (isInterviewEnded) return;
            isInterviewEnded = true;
            
            // Disable inputs
            textInput.disabled = true;
            micBtn.disabled = true;
            micBtn.onclick = null;
            if (isListening) recognition.stop();
            
            statusBar.innerText = "หมดเวลา! กำลังประเมินผล...";
            addMessage('system', "⏳ หมดเวลาการสัมภาษณ์ ระบบกำลังประเมินผล...");

            // Send evaluation prompt
            const evaluationPrompt = `
                การสัมภาษณ์สิ้นสุดลงแล้ว กรุณาประเมินผู้สมัครตามประวัติการสนทนาที่ผ่านมา โดยให้ข้อมูลดังนี้:
                1. คะแนนรวม (เต็ม 100)
                2. จุดแข็ง (Strengths)
                3. จุดที่ควรปรับปรุง (Weaknesses)
                4. คำแนะนำเพิ่มเติม (Suggestions)
                5. ความเหมาะสมกับบทบาท (Professionalism)
                
                ตอบเป็นภาษาไทย ในรูปแบบที่อ่านง่าย แยกหัวข้อชัดเจน ไม่ต้องสวมบทบาทแล้ว ให้ตอบในฐานะผู้ประเมิน
            `;
            
            conversationHistory.push({ role: "user", content: evaluationPrompt });

            try {
                const response = await fetch('api/interview_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ history: conversationHistory })
                });
                
                const data = await response.json();
                const evaluation = data.reply;
                
                // Display Evaluation
                showEvaluationModal(evaluation);
                statusBar.innerText = "การประเมินเสร็จสิ้น";
                
            } catch (error) {
                console.error('Error:', error);
                addMessage('system', "เกิดข้อผิดพลาดในการประเมินผล");
            }
        }

        function showEvaluationModal(text) {
            // Create modal overlay
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';

            // Create modal content
            const content = document.createElement('div');
            content.className = 'modal-content';

            // Format text (simple markdown to html conversion for line breaks)
            const formattedText = text.replace(/\n/g, '<br>');

            content.innerHTML = `
                <h2 style="color: var(--primary-color); text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-clipboard-check"></i> ผลการประเมินการสัมภาษณ์
                </h2>
                <div style="font-size: 1rem; line-height: 1.6; color: var(--text-color); margin-bottom: 30px;">
                    ${formattedText}
                </div>
                <div style="text-align: center; display: flex; justify-content: center; gap: 15px;">
                    <a href="setup_interview.php" class="btn">
                        <i class="fas fa-redo"></i> เริ่มใหม่
                    </a>
                    <a href="index.php" class="btn secondary">
                        <i class="fas fa-home"></i> กลับหน้าหลัก
                    </a>
                </div>
            `;

            modal.appendChild(content);
            document.body.appendChild(modal);
        }

    </script>
</body>
</html>