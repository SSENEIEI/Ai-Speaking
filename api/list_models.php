<?php
$apiKey = 'AIzaSyA5xBQYmUUrUKS7NXo4Lie6mQYU-QPth-4';
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
$response = file_get_contents($url);
echo $response;
?>