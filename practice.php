<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

// รับค่าจากฟอร์ม
$num_speakers = $_POST['num_speakers'] ?? 0;
$user_name = $_POST['user_name'] ?? 'Me'; // Legacy fallback
$user_role = $_POST['user_role'] ?? ''; // New: Selected role
$speaker_names = $_POST['speaker_names'] ?? [];
$script_speakers = $_POST['script_speaker'] ?? [];
$script_texts = $_POST['script_text'] ?? [];

// รวมบทสนทนาเป็น Array เดียวกันเพื่อความง่าย
$script = [];
for ($i = 0; $i < count($script_texts); $i++) {
    $script[] = [
        'speaker' => $script_speakers[$i],
        'text' => $script_texts[$i]
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practice Mode - AI Speaking</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--background-color);
        }

        .practice-container {
            text-align: center;
            padding: 40px;
            width: 100%;
            max-width: 700px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }

        .speaker-avatar {
            width: 120px;
            height: 120px;
            background: #f0f2f5;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: var(--text-muted);
            border: 5px solid transparent;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .speaker-avatar.active {
            border-color: var(--accent-color);
            background: white;
            color: var(--accent-color);
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(52, 152, 219, 0.3);
        }

        .speaker-avatar.user-active {
            border-color: var(--primary-color);
            background: white;
            color: var(--primary-color);
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(74, 144, 226, 0.3);
        }

        .speaker-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-color);
        }

        .dialogue-box {
            font-size: 1.4rem;
            margin: 30px 0;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            border: 1px dashed #ddd;
            line-height: 1.6;
        }

        .status-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 20px;
            font-style: italic;
            height: 20px;
        }

        .controls {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .mic-visualizer {
            height: 8px;
            background: #eee;
            border-radius: 10px;
            margin: 20px auto 0;
            overflow: hidden;
            width: 80%;
            display: none;
        }

        .mic-bar {
            height: 100%;
            width: 0%;
            background: var(--success-color);
            transition: width 0.1s linear;
            border-radius: 10px;
        }

        /* Overlay Styles */
        #startOverlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .start-btn {
            font-size: 1.5rem;
            padding: 20px 50px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(74, 144, 226, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .start-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(74, 144, 226, 0.5);
        }

        .progress-dots {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-bottom: 20px;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: #ddd;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .dot.active {
            background: var(--primary-color);
            transform: scale(1.2);
        }

        /* Hide Text Toggle */
        .toggle-container {
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .hidden-text-placeholder {
            color: var(--text-muted);
            font-style: italic;
            opacity: 0.6;
        }
        
        .reveal-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: underline;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Overlay -->
    <div id="startOverlay">
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-headphones-alt fa-4x" style="color: var(--primary-color); margin-bottom: 20px;"></i>
            <h1>พร้อมฝึกซ้อมหรือยัง?</h1>
            <p style="color: var(--text-muted);">กดปุ่มด้านล่างเพื่อเริ่มบทสนทนา</p>
        </div>
        <button class="start-btn" onclick="startPractice()">
            <i class="fas fa-microphone-alt"></i> เริ่มฝึกซ้อม
        </button>
    </div>

    <div class="practice-container">
        <div class="progress-dots" id="progressDots">
            <!-- Dots will be generated here -->
        </div>

        <div id="speakerAvatar" class="speaker-avatar">
            <i class="fas fa-user"></i>
        </div>
        
        <div id="speakerName" class="speaker-name">Waiting...</div>
        
        <div class="toggle-container">
            <label class="toggle-switch">
                <input type="checkbox" id="hideMyLinesCheckbox" onchange="toggleTextVisibility()">
                <span class="slider"></span>
            </label>
            <span style="font-size: 0.9rem; color: var(--text-muted);">ซ่อนบทพูดของฉัน (ฝึกจำ)</span>
        </div>

        <div class="dialogue-box">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <span id="dialogueText">...</span>
                <button id="revealBtn" class="reveal-btn" style="display: none;" onclick="revealText()">แสดงข้อความ</button>
            </div>
        </div>

        <div class="mic-visualizer" id="micVisualizer">
            <div class="mic-bar" id="micBar"></div>
        </div>

        <div class="status-text" id="statusText">Ready to start</div>

        <div class="controls">
            <a href="setup_practice.php" class="btn secondary">
                <i class="fas fa-stop"></i> เลิกฝึก / กลับไปแก้ไข
            </a>
        </div>
    </div>

    <script>
        // ข้อมูลจาก PHP
        const scriptData = <?php echo json_encode($script); ?>;
        const speakerNames = <?php echo json_encode($speaker_names); ?>;
        const userName = <?php echo json_encode($user_name); ?>;
        const userRole = <?php echo json_encode($user_role); ?>;
        
        let currentLine = 0;
        let voices = [];
        let speakerVoiceMap = {};
        let audioContext;
        let analyser;
        let microphone;
        let silenceTimer;
        let isListening = false;
        let hasSpoken = false; // เช็คว่าเริ่มพูดหรือยัง

        // Generate Progress Dots
        const dotsContainer = document.getElementById('progressDots');
        scriptData.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.className = 'dot';
            dot.id = `dot-${index}`;
            dotsContainer.appendChild(dot);
        });

        // โหลดเสียง TTS
        function loadVoices() {
            voices = window.speechSynthesis.getVoices();
            
            // Filter Thai voices
            const thaiVoices = voices.filter(v => v.lang.includes('th'));
            const otherVoices = voices.filter(v => !v.lang.includes('th'));
            const allVoices = [...thaiVoices, ...otherVoices]; // Prefer Thai

            // จับคู่เสียงกับคนพูด
            speakerNames.forEach((name, index) => {
                // Try to assign different voices
                const selectedVoice = allVoices[index % allVoices.length];
                speakerVoiceMap[name] = selectedVoice;
            });
        }

        window.speechSynthesis.onvoiceschanged = loadVoices;

        async function startPractice() {
            document.getElementById('startOverlay').style.opacity = '0';
            setTimeout(() => {
                document.getElementById('startOverlay').style.display = 'none';
            }, 300);
            
            // ขออนุญาตใช้ไมค์
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                setupAudioAnalysis(stream);
                
                // โหลดเสียงอีกรอบเผื่อยังไม่มา
                if (voices.length === 0) loadVoices();
                
                processLine();
            } catch (err) {
                alert("กรุณาอนุญาตให้ใช้ไมโครโฟนเพื่อใช้งานระบบนี้");
                console.error(err);
            }
        }

        function setupAudioAnalysis(stream) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();
            microphone = audioContext.createMediaStreamSource(stream);
            microphone.connect(analyser);
            analyser.fftSize = 256;
        }

        function processLine() {
            // Update Dots
            document.querySelectorAll('.dot').forEach(d => d.classList.remove('active'));
            if (currentLine < scriptData.length) {
                document.getElementById(`dot-${currentLine}`).classList.add('active');
            }

            if (currentLine >= scriptData.length) {
                document.getElementById('speakerName').innerText = "Finished!";
                document.getElementById('dialogueText').innerText = "การฝึกซ้อมเสร็จสิ้น เก่งมาก!";
                document.getElementById('speakerAvatar').innerHTML = '<i class="fas fa-check"></i>';
                document.getElementById('speakerAvatar').className = 'speaker-avatar user-active';
                document.getElementById('statusText').innerText = "Completed";
                return;
            }

            const line = scriptData[currentLine];
            
            // Check if the speaker is the user's selected role
            // Fallback to 'Me' check if userRole is empty (backward compatibility)
            let isMe = false;
            if (userRole && userRole !== '') {
                isMe = (line.speaker === userRole);
            } else {
                isMe = ['Me', 'me', 'I', 'ฉัน', 'ผม', 'User'].includes(line.speaker);
            }

            // อัปเดต UI
            document.getElementById('speakerName').innerText = line.speaker;
            
            // Handle Text Visibility
            updateDialogueText(line.text, isMe);
            
            const avatar = document.getElementById('speakerAvatar');
            if (isMe) {
                avatar.innerHTML = '<i class="fas fa-user"></i>';
                avatar.className = 'speaker-avatar user-active';
                startListening();
            } else {
                avatar.innerHTML = '<i class="fas fa-robot"></i>';
                avatar.className = 'speaker-avatar active';
                stopListening(); // ปิดไมค์ตอน AI พูด
                speakText(line.text, line.speaker);
            }
        }

        function updateDialogueText(text, isMe) {
            const dialogueText = document.getElementById('dialogueText');
            const revealBtn = document.getElementById('revealBtn');
            const hideMyLines = document.getElementById('hideMyLinesCheckbox').checked;

            if (isMe && hideMyLines) {
                dialogueText.innerText = "(...บทพูดของคุณถูกซ่อนอยู่...)";
                dialogueText.classList.add('hidden-text-placeholder');
                dialogueText.dataset.fullText = text; // Store full text
                revealBtn.style.display = 'block';
            } else {
                dialogueText.innerText = text;
                dialogueText.classList.remove('hidden-text-placeholder');
                revealBtn.style.display = 'none';
            }
        }

        function toggleTextVisibility() {
            if (currentLine < scriptData.length) {
                const line = scriptData[currentLine];
                
                // Check isMe logic again (duplicated from processLine, could be refactored but keeping it simple)
                let isMe = false;
                if (userRole && userRole !== '') {
                    isMe = (line.speaker === userRole);
                } else {
                    isMe = ['Me', 'me', 'I', 'ฉัน', 'ผม', 'User'].includes(line.speaker);
                }

                updateDialogueText(line.text, isMe);
            }
        }

        function revealText() {
            const dialogueText = document.getElementById('dialogueText');
            const revealBtn = document.getElementById('revealBtn');
            
            if (dialogueText.dataset.fullText) {
                dialogueText.innerText = dialogueText.dataset.fullText;
                dialogueText.classList.remove('hidden-text-placeholder');
                revealBtn.style.display = 'none';
            }
        }

        function speakText(text, speaker) {
            document.getElementById('statusText').innerText = "AI กำลังพูด...";
            
            // ยกเลิกเสียงเก่าที่อาจจะค้างอยู่
            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.voice = speakerVoiceMap[speaker];
            utterance.rate = 1; // ความเร็วปกติ
            utterance.pitch = 1;
            utterance.lang = 'th-TH'; // Default to Thai

            utterance.onend = function() {
                // พูดจบแล้ว ไปบรรทัดต่อไป
                setTimeout(() => {
                    currentLine++;
                    processLine();
                }, 500); // เว้นจังหวะนิดนึง
            };

            window.speechSynthesis.speak(utterance);
        }

        function startListening() {
            document.getElementById('statusText').innerText = "ตาคุณแล้ว! พูดตามบทได้เลย...";
            document.getElementById('micVisualizer').style.display = 'block';
            isListening = true;
            hasSpoken = false;
            
            detectVoiceActivity();
        }

        function stopListening() {
            isListening = false;
            document.getElementById('micVisualizer').style.display = 'none';
            clearTimeout(silenceTimer);
        }

        function detectVoiceActivity() {
            if (!isListening) return;

            const dataArray = new Uint8Array(analyser.frequencyBinCount);
            analyser.getByteFrequencyData(dataArray);

            // คำนวณความดังเฉลี่ย
            let sum = 0;
            for (let i = 0; i < dataArray.length; i++) {
                sum += dataArray[i];
            }
            const average = sum / dataArray.length;

            // แสดง Visualizer
            const barWidth = Math.min(100, average * 2);
            document.getElementById('micBar').style.width = barWidth + '%';

            // Logic ตรวจจับเสียง
            const threshold = 10; // ปรับค่าความไวของไมค์ตรงนี้ (0-255)

            if (average > threshold) {
                // กำลังพูดอยู่
                if (!hasSpoken) {
                    hasSpoken = true;
                    document.getElementById('statusText').innerText = "กำลังฟัง... (พูดอยู่)";
                    document.getElementById('statusText').style.color = "var(--success-color)";
                }
                // ถ้าพูดอยู่ ให้เคลียร์ตัวจับเวลาเงียบ
                clearTimeout(silenceTimer);
                silenceTimer = null;
            } else {
                // เงียบ
                if (hasSpoken && !silenceTimer) {
                    // ถ้าเคยพูดมาแล้ว และตอนนี้เงียบ -> เริ่มจับเวลา 2 วินาที
                    document.getElementById('statusText').innerText = "เงียบ... รอ 2 วินาทีเพื่อไปต่อ";
                    document.getElementById('statusText').style.color = "var(--warning-color)";
                    
                    silenceTimer = setTimeout(() => {
                        if (isListening) {
                            document.getElementById('statusText').innerText = "เรียบร้อย!";
                            stopListening();
                            currentLine++;
                            processLine();
                        }
                    }, 2000); // 2 วินาที
                }
            }

            requestAnimationFrame(detectVoiceActivity);
        }
    </script>
</body>
</html>