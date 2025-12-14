<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$text = $input['text'] ?? '';

if (empty($text)) {
    echo json_encode(['error' => 'No text provided']);
    exit;
}

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
?>