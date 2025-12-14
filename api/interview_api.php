<?php
header('Content-Type: application/json');

// รับข้อมูลจาก Client
$input = json_decode(file_get_contents('php://input'), true);
$history = $input['history'] ?? [];

// API Key Strategy:
// 1. Try Environment Variable (Production/Render)
// 2. Try Local Secrets File (Local Development)
$apiKey = getenv('GEMINI_API_KEY');

if (!$apiKey) {
    $secretsPath = __DIR__ . '/../config/secrets.php';
    if (file_exists($secretsPath)) {
        require_once $secretsPath;
        if (defined('LOCAL_GEMINI_API_KEY')) {
            $apiKey = LOCAL_GEMINI_API_KEY;
        }
    }
}

if (!$apiKey) {
    echo json_encode(['error' => 'Server Configuration Error: API Key not found.']);
    exit;
}

// Use gemini-2.0-flash for better stability and quota
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

// แปลง format history ให้ตรงกับ Gemini API
// Gemini API ใช้ structure: { "contents": [{ "role": "user", "parts": [{"text": "..."}] }] }
// System prompt ใน Gemini 1.5 มักจะใส่เป็น user message แรก หรือใช้ systemInstruction (ถ้า model รองรับ)
// เพื่อความง่าย เราจะแปลง system prompt เป็น user message แรกที่บอก context

$geminiContents = [];

// แยก System Prompt ออกมา (ถ้ามี)
$systemPrompt = "";
if (!empty($history) && $history[0]['role'] === 'system') {
    $systemPrompt = $history[0]['content'];
    array_shift($history); // เอา system ออกจาก array ปกติ
}

// สร้าง Prompt แรกที่รวม System Instruction
if (!empty($systemPrompt)) {
    // Hack: ใส่ System prompt รวมกับข้อความแรก หรือส่งเป็น User message แรก
    // สำหรับ Gemini Flash, การส่งเป็น User message แรกทำงานได้ดี
    $geminiContents[] = [
        "role" => "user",
        "parts" => [[ "text" => "System Instruction: " . $systemPrompt . "\n\n(Start the roleplay now)" ]]
    ];
}

// เติม History ที่เหลือ
foreach ($history as $msg) {
    $role = ($msg['role'] === 'user') ? 'user' : 'model';
    $geminiContents[] = [
        "role" => $role,
        "parts" => [[ "text" => $msg['content'] ]]
    ];
}

// ถ้าไม่มี history เลย (เริ่มบทสนทนา)
if (empty($geminiContents)) {
    $geminiContents[] = [
        "role" => "user",
        "parts" => [[ "text" => "สวัสดีครับ" ]]
    ];
}

$data = [
    "contents" => $geminiContents,
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 1000 // ให้ตอบสั้นๆ กระชับ
    ]
];

// ยิง Request ไปหา Gemini (พร้อม Retry Logic)
$maxRetries = 5; // เพิ่มจำนวนครั้งในการลองใหม่
$retryCount = 0;
$response = null;
$httpCode = 0;

do {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
        exit;
    }
    curl_close($ch);

    // ถ้าเจอ 429 (Too Many Requests) ให้รอแล้วลองใหม่
    if ($httpCode == 429) {
        $retryCount++;
        if ($retryCount < $maxRetries) {
            sleep(5); // รอ 5 วินาที (เพิ่มเวลาให้มากขึ้น)
            continue;
        }
    }
    
    // ถ้าไม่ใช่ 429 หรือครบจำนวน retry แล้ว ให้หลุดลูป
    break;

} while ($retryCount < $maxRetries);

// แปลง Response
$result = json_decode($response, true);

if ($httpCode !== 200) {
    $errorMsg = 'Unknown error';
    if (isset($result['error']['message'])) {
        $errorMsg = $result['error']['message'];
    } else {
        // กรณี Response ไม่ใช่ JSON หรือไม่มี error message (เช่น 404 HTML)
        $errorMsg = "HTTP $httpCode";
    }
    echo json_encode(['error' => 'API Error: ' . $errorMsg]);
    exit;
}

// ดึงข้อความตอบกลับ
$reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I did not understand that.';

echo json_encode(['reply' => $reply]);
?>