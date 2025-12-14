<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$text = $input['text'] ?? '';

if (empty($text)) {
    echo json_encode(['error' => 'No text provided']);
    exit;
}

// --- Edge TTS (Python) Implementation ---
// Path to the Python script
$pythonScript = __DIR__ . '/../python_scripts/tts_gen.py';

// Check if script exists
if (!file_exists($pythonScript)) {
    echo json_encode(['error' => 'Python script not found', 'path' => $pythonScript]);
    exit;
}

// Command to execute Python script
// Note: Ensure 'python3' is in your system PATH and has the required libraries installed.
// You might need to specify the full path to python, e.g., '/usr/local/bin/python3' or '/opt/homebrew/bin/python3'
$pythonCmd = "/Library/Frameworks/Python.framework/Versions/3.11/bin/python3"; // Updated to specific path found on system
if (!file_exists($pythonCmd)) {
    $pythonCmd = "python3"; // Fallback
}

$command = escapeshellcmd($pythonCmd) . " " . escapeshellarg($pythonScript) . " " . escapeshellarg($text);

$output = shell_exec($command);
$result = json_decode($output, true);

if ($result && isset($result['success']) && $result['success']) {
    // Return the audio content directly as expected by the frontend
    echo json_encode(['audioContent' => $result['audioContent']]);
} else {
    $errorMsg = $result['error'] ?? 'Unknown error from Python script';
    if (!$result) $errorMsg .= " | Raw Output: " . $output;
    
    echo json_encode(['error' => 'TTS Generation Failed', 'details' => $errorMsg]);
}

/* --- OLD Google Cloud TTS Implementation (Disabled) ---
// ใช้ API Key เดียวกับ Gemini (ต้อง Enable Google Cloud Text-to-Speech API ใน Console ด้วย)
$apiKey = 'AIzaSyA5xBQYmUUrUKS7NXo4Lie6mQYU-QPth-4'; 
$url = "https://texttospeech.googleapis.com/v1/text:synthesize?key=" . $apiKey;

// เลือกเสียงแบบ Neural2 (คุณภาพสูงคล้าย Gemini/Assistant)
// th-TH-Neural2-C = เสียงผู้หญิง
// th-TH-Standard-A = เสียงผู้หญิง (แบบธรรมดา)
$data = [
    "input" => ["text" => $text],
    "voice" => [
        "languageCode" => "th-TH", 
        "name" => "th-TH-Neural2-C" 
    ],
    "audioConfig" => ["audioEncoding" => "MP3"]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo $response; // ส่งกลับ JSON ที่มี audioContent
} else {
    // ถ้า Error (เช่น API Key ไม่รองรับ TTS) ให้ส่ง Error กลับไป
    $errorMsg = json_decode($response, true);
    echo json_encode(['error' => 'TTS API Failed', 'details' => $errorMsg, 'http_code' => $httpCode]);
}
*/
?>