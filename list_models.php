<?php
$apiKey = "AIzaSyCfBh-6qnLnXVIFz2nmEZ6gA29yPyTReCk"; 
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Diagnostic MonAgriCoach</h1>";
echo "Code HTTP : " . $httpCode . "<br><br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>