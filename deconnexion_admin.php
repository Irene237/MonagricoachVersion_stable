<?php 
session_start(); // Démarre la session 
 
// Détruit toutes les variables de session 
$_SESSION = array(); 
 
// Si vous voulez détruire complètement la session, effacez également le cookie de session. 
// Note : cela détruira la session, et non seulement les données de session ! 
if (ini_get("session.use_cookies")) { 
    $params = session_get_cookie_params(); 
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"] 
    ); 
} 
 
// Finalement, détruit la session. 
session_destroy(); 
 
// Redirige vers la page de connexion 
header("Location: connexion_admin.php?status=logout_success"); 
exit(); 
?>