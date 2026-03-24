<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function envoyerAlerteStock($emailAgriculteur, $nomIntrant, $quantite, $seuil) {
    $mail = new PHPMailer(true);
    try {
        // Configuration SMTP (exemple avec Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'votre-email@gmail.com';
        $mail->Password   = 'votre-mot-de-passe-application';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Destinataire
        $mail->setFrom('alerte@monagricoach.com', 'MonAgriCoach');
        $mail->addAddress($emailAgriculteur);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = "⚠️ ALERTE : Stock Bas - $nomIntrant";
        $mail->Body    = "Bonjour, <br><br>Votre stock de <b>$nomIntrant</b> est critique.<br>
                          Quantité actuelle : <b>$quantite</b><br>
                          Seuil d'alerte : <b>$seuil</b><br><br>
                          Veuillez vous réapprovisionner rapidement.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}