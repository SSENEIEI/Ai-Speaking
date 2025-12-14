<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าการฝึกซ้อม - AI Speaking</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles for dynamic form elements */
        .script-row {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            align-items: flex-start;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .script-row:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-color: var(--primary-color);
        }

        .script-row select {
            width: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .script-row textarea {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            min-height: 60px;
            font-family: 'Prompt', sans-serif;
        }

        .remove-row {
            background: var(--danger-color);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-top: 5px;
            transition: transform 0.2s;
        }

        .remove-row:hover {
            transform: scale(1.1);
        }

        .scenario-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .scenario-item {
            background: white;
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #ddd;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.2s;
            color: var(--text-color);
        }

        .scenario-item:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .speaker-inputs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container wide">
        <div class="text-center mb-20">
            <i class="fas fa-theater-masks fa-3x" style="color: var(--secondary-color); margin-bottom: 15px;"></i>
            <h2>ตั้งค่าการฝึกซ้อม (Roleplay)</h2>
            <p>กำหนดตัวละครและบทสนทนาเพื่อฝึกพูดตาม</p>
        </div>

        <!-- Saved Scenarios -->
        <div class="menu-card mb-20" style="text-align: left;">
            <h3 style="font-size: 1.1rem; margin-bottom: 10px; color: var(--primary-color);">
                <i class="fas fa-bookmark"></i> โหลดบทสนทนาตัวอย่าง
            </h3>
            <div class="scenario-list">
                <div class="scenario-item" onclick="loadScenario('greeting')">
                    <i class="fas fa-handshake"></i> ทักทายเพื่อนใหม่
                </div>
                <div class="scenario-item" onclick="loadScenario('restaurant')">
                    <i class="fas fa-utensils"></i> สั่งอาหาร
                </div>
                <div class="scenario-item" onclick="loadScenario('shopping')">
                    <i class="fas fa-shopping-bag"></i> ซื้อของ
                </div>
                <div class="scenario-item" onclick="loadScenario('doctor')">
                    <i class="fas fa-user-md"></i> หาหมอ
                </div>
            </div>
        </div>

        <!-- My Saved Scenarios -->
        <div class="menu-card mb-20" style="text-align: left;">
            <h3 style="font-size: 1.1rem; margin-bottom: 10px; color: var(--secondary-color);">
                <i class="fas fa-save"></i> บทสนทนาที่บันทึกไว้
            </h3>
            <div id="mySavedScenarios" class="scenario-list">
                <span style="color: var(--text-muted); font-size: 0.9rem;">กำลังโหลด...</span>
            </div>
        </div>
        
        <form action="practice.php" method="POST" id="practiceForm">
            
            <!-- Step 1: Define Speakers -->
            <div class="menu-card mb-20" style="text-align: left;">
                <h3 style="font-size: 1.1rem; margin-bottom: 10px; color: var(--primary-color);">
                    <i class="fas fa-users"></i> 1. กำหนดตัวละคร
                </h3>
                <div class="form-group">
                    <label>จำนวนผู้พูด:</label>
                    <select id="speakerCount" onchange="updateSpeakerInputs()" style="max-width: 100px;">
                        <option value="2">2 คน</option>
                        <option value="3">3 คน</option>
                        <option value="4">4 คน</option>
                    </select>
                </div>
                <div id="speakerInputs" class="speaker-inputs">
                    <!-- JS will populate this -->
                </div>

                <div class="form-group mt-15" style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                    <label style="color: var(--primary-color); font-weight: 600;"><i class="fas fa-user-circle"></i> คุณเล่นเป็นตัวละครไหน? (เลือกบทของคุณ)</label>
                    <select name="user_role" id="userRoleSelect" style="width: 100%; max-width: 300px; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        <!-- JS will populate this -->
                    </select>
                </div>
            </div>

            <!-- Step 2: Create Script -->
            <div class="menu-card mb-20" style="text-align: left;">
                <h3 style="font-size: 1.1rem; margin-bottom: 10px; color: var(--primary-color);">
                    <i class="fas fa-pen-fancy"></i> 2. เขียนบทสนทนา
                </h3>
                <div id="scriptContainer">
                    <!-- Script rows will be added here -->
                </div>
                
                <button type="button" class="btn secondary" onclick="addScriptRow()" style="width: auto; margin-top: 10px;">
                    <i class="fas fa-plus"></i> เพิ่มบทพูด
                </button>
            </div>

            <div class="text-center mt-20">
                <button type="button" class="btn secondary" onclick="openSaveModal()" style="font-size: 1.2rem; padding: 15px 30px; margin-right: 10px; background: var(--secondary-color); color: white;">
                    <i class="fas fa-save"></i> บันทึก
                </button>
                <button type="submit" class="btn" style="font-size: 1.2rem; padding: 15px 40px;">
                    <i class="fas fa-play-circle"></i> เริ่มฝึกซ้อม
                </button>
                <br><br>
                <a href="index.php" class="btn secondary" style="width: auto; background: transparent; color: var(--text-muted); border: none; box-shadow: none;">
                    <i class="fas fa-arrow-left"></i> ยกเลิก
                </a>
            </div>
        </form>
    </div>

    <!-- Save Modal -->
    <div id="saveModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 15px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            <h3 style="margin-bottom: 20px; color: var(--primary-color);">บันทึกบทสนทนา</h3>
            <input type="text" id="scenarioTitle" placeholder="ตั้งชื่อบทสนทนา..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; font-family: 'Prompt', sans-serif;">
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button onclick="saveScenario()" class="btn" style="padding: 10px 20px;">บันทึก</button>
                <button onclick="closeSaveModal()" class="btn secondary" style="padding: 10px 20px;">ยกเลิก</button>
            </div>
        </div>
    </div>

    <script>
        // Initialize with 2 speakers
        updateSpeakerInputs();
        // Add initial script row
        addScriptRow();

        function updateSpeakerInputs() {
            const count = document.getElementById('speakerCount').value;
            const container = document.getElementById('speakerInputs');
            container.innerHTML = '';

            for (let i = 1; i <= count; i++) {
                const div = document.createElement('div');
                div.className = 'form-group';
                div.innerHTML = `
                    <label>ชื่อตัวละครที่ ${i}:</label>
                    <input type="text" name="speakers[]" value="Speaker ${i}" class="speaker-name-input" onchange="updateSpeakerSelects()" required>
                `;
                container.appendChild(div);
            }
            updateSpeakerSelects();
        }

        function updateSpeakerSelects() {
            const speakerInputs = document.querySelectorAll('.speaker-name-input');
            const selects = document.querySelectorAll('.speaker-select');
            const userRoleSelect = document.getElementById('userRoleSelect');
            
            const speakers = Array.from(speakerInputs).map(input => input.value);

            // Update Script Row Selects
            selects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '';
                speakers.forEach(speaker => {
                    const option = document.createElement('option');
                    option.value = speaker;
                    option.textContent = speaker;
                    if (speaker === currentValue) option.selected = true;
                    select.appendChild(option);
                });
            });

            // Update User Role Select
            const currentUserRole = userRoleSelect.value;
            userRoleSelect.innerHTML = '';
            
            if (speakers.length === 0) {
                const option = document.createElement('option');
                option.textContent = "-- กรุณากำหนดตัวละคร --";
                userRoleSelect.appendChild(option);
            } else {
                speakers.forEach(speaker => {
                    const option = document.createElement('option');
                    option.value = speaker;
                    option.textContent = speaker;
                    if (speaker === currentUserRole) option.selected = true;
                    userRoleSelect.appendChild(option);
                });
            }
        }

        function addScriptRow(speaker = '', text = '') {
            const container = document.getElementById('scriptContainer');
            const div = document.createElement('div');
            div.className = 'script-row';
            
            // Get current speakers for the select box
            const speakerInputs = document.querySelectorAll('.speaker-name-input');
            let optionsHtml = '';
            speakerInputs.forEach(input => {
                const selected = input.value === speaker ? 'selected' : '';
                optionsHtml += `<option value="${input.value}" ${selected}>${input.value}</option>`;
            });

            div.innerHTML = `
                <select name="script_speaker[]" class="speaker-select">
                    ${optionsHtml}
                </select>
                <textarea name="script_text[]" placeholder="พิมพ์บทพูดที่นี่..." required>${text}</textarea>
                <button type="button" class="remove-row" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
        }

        // Pre-defined Scenarios
        const scenarios = {
            greeting: {
                speakers: ['A', 'B'],
                script: [
                    {s: 'A', t: 'สวัสดีครับ สบายดีไหมครับ?'},
                    {s: 'B', t: 'สวัสดีค่ะ ฉันสบายดีค่ะ ขอบคุณค่ะ แล้วคุณล่ะคะ?'},
                    {s: 'A', t: 'ผมก็สบายดีครับ วันนี้อากาศดีนะครับ'},
                    {s: 'B', t: 'ใช่ค่ะ เหมาะกับการไปเดินเล่นมากเลยค่ะ'}
                ]
            },
            restaurant: {
                speakers: ['Customer', 'Waiter'],
                script: [
                    {s: 'Waiter', t: 'สวัสดีครับ รับอะไรดีครับ?'},
                    {s: 'Customer', t: 'ขอดูเมนูหน่อยครับ'},
                    {s: 'Waiter', t: 'นี่ครับ เมนูแนะนำวันนี้คือต้มยำกุ้งครับ'},
                    {s: 'Customer', t: 'งั้นขอต้มยำกุ้ง 1 ที่ และข้าวเปล่า 1 จานครับ'}
                ]
            },
            shopping: {
                speakers: ['Seller', 'Buyer'],
                script: [
                    {s: 'Seller', t: 'สนใจสินค้าตัวไหนสอบถามได้นะคะ'},
                    {s: 'Buyer', t: 'เสื้อตัวนี้ราคาเท่าไหร่ครับ?'},
                    {s: 'Seller', t: 'ตัวนี้ 350 บาทค่ะ ผ้าดีมากนะคะ'},
                    {s: 'Buyer', t: 'ลดได้ไหมครับ? ซัก 300 ได้ไหม'},
                    {s: 'Seller', t: 'ได้ค่ะ สำหรับคุณลูกค้า พิเศษเลยค่ะ'}
                ]
            },
            doctor: {
                speakers: ['Doctor', 'Patient'],
                script: [
                    {s: 'Doctor', t: 'สวัสดีครับ วันนี้เป็นอะไรมาครับ?'},
                    {s: 'Patient', t: 'รู้สึกปวดหัว ตัวร้อน และเจ็บคอครับ'},
                    {s: 'Doctor', t: 'เป็นมานานหรือยังครับ?'},
                    {s: 'Patient', t: 'เป็นมาตั้งแต่เมื่อวานเย็นครับ'},
                    {s: 'Doctor', t: 'เดี๋ยวหมอขอวัดไข้หน่อยนะครับ'}
                ]
            }
        };

        function loadScenario(key) {
            const data = scenarios[key];
            if (!data) return;

            // 1. Set Speaker Count
            document.getElementById('speakerCount').value = data.speakers.length;
            
            // 2. Update Speaker Inputs
            const container = document.getElementById('speakerInputs');
            container.innerHTML = '';
            data.speakers.forEach((name, index) => {
                const div = document.createElement('div');
                div.className = 'form-group';
                div.innerHTML = `
                    <label>ชื่อตัวละครที่ ${index + 1}:</label>
                    <input type="text" name="speakers[]" value="${name}" class="speaker-name-input" onchange="updateSpeakerSelects()" required>
                `;
                container.appendChild(div);
            });

            // 3. Clear and Add Script
            document.getElementById('scriptContainer').innerHTML = '';
            data.script.forEach(line => {
                addScriptRow(line.s, line.t);
            });

            // 4. Update User Role Select
            updateSpeakerSelects();
        }

        // --- Save/Load Logic ---

        function openSaveModal() {
            document.getElementById('saveModal').style.display = 'flex';
        }

        function closeSaveModal() {
            document.getElementById('saveModal').style.display = 'none';
        }

        function saveScenario() {
            const title = document.getElementById('scenarioTitle').value;
            if (!title) {
                alert('กรุณาตั้งชื่อบทสนทนา');
                return;
            }

            const form = document.getElementById('practiceForm');
            const formData = new FormData(form);
            formData.append('title', title);

            fetch('save_scenario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('บันทึกเรียบร้อยแล้ว');
                    closeSaveModal();
                    fetchSavedScenarios(); // Refresh list
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            });
        }

        function fetchSavedScenarios() {
            fetch('get_scenarios.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('mySavedScenarios');
                    container.innerHTML = '';
                    
                    if (data.scenarios.length === 0) {
                        container.innerHTML = '<p class="text-muted">ยังไม่มีบทสนทนาที่บันทึกไว้</p>';
                        return;
                    }

                    data.scenarios.forEach(scenario => {
                        const div = document.createElement('div');
                        div.className = 'scenario-card';
                        div.style.cursor = 'pointer';
                        div.style.padding = '15px';
                        div.style.marginBottom = '10px';
                        div.style.background = '#f8f9fa';
                        div.style.borderRadius = '10px';
                        div.style.border = '1px solid #eee';
                        div.style.transition = 'all 0.3s ease';
                        
                        div.onmouseover = function() { this.style.background = '#e9ecef'; this.style.transform = 'translateY(-2px)'; };
                        div.onmouseout = function() { this.style.background = '#f8f9fa'; this.style.transform = 'translateY(0)'; };
                        
                        div.onclick = () => loadSavedScenario(scenario);
                        div.innerHTML = `
                            <h4 style="margin: 0 0 5px 0; color: var(--primary-color);">${scenario.title}</h4>
                            <p style="margin: 0; font-size: 0.9rem; color: #6c757d;">
                                <i class="far fa-clock"></i> ${new Date(scenario.created_at).toLocaleString('th-TH')}
                            </p>
                        `;
                        container.appendChild(div);
                    });
                }
            });
        }

        function loadSavedScenario(scenario) {
            if (!confirm('ต้องการโหลดบทสนทนา "' + scenario.title + '" ใช่หรือไม่? ข้อมูลปัจจุบันจะถูกแทนที่')) {
                return;
            }

            try {
                // Handle both string JSON and object (if already parsed)
                const data = typeof scenario.scenario_data === 'string' 
                    ? JSON.parse(scenario.scenario_data) 
                    : scenario.scenario_data;

                // 1. Set Speaker Count
                document.getElementById('speakerCount').value = data.speaker_count;

                // 2. Generate Speaker Inputs manually to avoid default values
                const container = document.getElementById('speakerInputs');
                container.innerHTML = '';
                
                // Ensure speakers is an array
                const speakers = Array.isArray(data.speakers) ? data.speakers : [];
                
                speakers.forEach((name, index) => {
                    const div = document.createElement('div');
                    div.className = 'form-group';
                    div.innerHTML = `
                        <label>ชื่อตัวละครที่ ${index + 1}:</label>
                        <input type="text" name="speakers[]" value="${name}" class="speaker-name-input" onchange="updateSpeakerSelects()" required>
                    `;
                    container.appendChild(div);
                });

                // 3. Update Selects
                updateSpeakerSelects();

                // 4. Set User Role
                if (data.user_role) {
                    const userRoleSelect = document.getElementById('userRoleSelect');
                    userRoleSelect.value = data.user_role;
                }

                // 5. Rebuild Script
                const scriptContainer = document.getElementById('scriptContainer');
                scriptContainer.innerHTML = '';

                if (data.script_speaker && data.script_text) {
                    data.script_speaker.forEach((speaker, index) => {
                        const text = data.script_text[index];
                        addScriptRow(speaker, text);
                    });
                }
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } catch (e) {
                console.error('Error loading scenario:', e);
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            }
        }

        // Load saved scenarios on page load
        document.addEventListener('DOMContentLoaded', fetchSavedScenarios);
    </script>
</body>
</html>