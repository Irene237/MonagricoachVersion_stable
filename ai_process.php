<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    
    $apiKey = "AIzaSyCfBh-6qnLnXVIFz2nmEZ6gA29yPyTReCk"; 
    $userQuery = trim($_POST['query']);

    // NOUVELLE URL 2026 : On utilise Gemini 2.5 Flash (le plus compatible)
    // On garde v1beta car c'est la plus flexible pour les nouveaux modèles
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    $data = [
        "contents" => [
            ["parts" => [["text" => "Tu es l'expert agricole de MonAgriCoach au Cameroun. Réponds courtement : " . $userQuery]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            echo trim($result['candidates'][0]['content']['parts'][0]['text']);
        } else {
            echo "L'IA est connectée mais n'a pas pu générer de texte.";
        }
    } else {
        // Si 2.5 Flash ne passe pas, on essaie le tout nouveau Gemini 3 Flash
        // (C'est ton plan de secours automatique pour la soutenance)
        echo "Erreur (Code $httpCode). Essayez de remplacer 'gemini-2.5-flash' par 'gemini-3-flash-preview' dans l'URL.";
    }
}
?>