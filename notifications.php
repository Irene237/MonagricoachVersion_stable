<?php
// notifications.php
require_once 'vendor/autoload.php'; 
use Twilio\Rest\Client;

function verifierEtEnvoyerAlerte($nomIntrant, $quantite, $seuil, $telephone) {
    if ($quantite <= $seuil) {
        $sid = "VOTRE_TWILIO_SID"; 
        $token = "VOTRE_TWILIO_TOKEN";
        $client = new Client($sid, $token);

        try {
            $client->messages->create(
                $telephone,
                [
                    'from' => 'VOTRE_NUMERO_TWILIO',
                    'body' => "MonAgriCoach: Stock bas pour $nomIntrant ! Il ne reste que $quantite kg."
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log("Erreur Twilio : " . $e->getMessage());
            return false;
        }
    }
    return false;
}