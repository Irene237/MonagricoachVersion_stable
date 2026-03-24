<?php
// init_lang.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// On récupère la langue de la session ou français par défaut
$current_lang = $_SESSION['lang'] ?? 'fr';

$lang_file = "lang/$current_lang.php";

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    // Ton dictionnaire de secours au cas où
    $translations = [
        'nav_home' => "Accueil",
        'btn_save' => "Sauvegarder"
        // ...
    ];
}