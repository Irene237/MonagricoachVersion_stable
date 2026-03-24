<?php
// Démarrer la session pour la vérification des droits
session_start();
require_once 'db.php'; 

// Vérification: l'utilisateur doit être un CONSEILLER et être connecté
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true || $_SESSION['type_utilisateur'] !== 'conseiller_agricole') { 
    header("Location: connexion.php"); 
    exit(); 
} 

$conseiller_id = $_SESSION['user_id'];
$message_status = '';

// --- Logique PHP de Traitement du Formulaire POST ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Récupération et sécurisation des données POST
    $id_assistance = isset($_POST['id_assistance']) ? (int) $_POST['id_assistance'] : 0;
    $id_agriculteur = isset($_POST['id_agriculteur']) ? (int) $_POST['id_agriculteur'] : 0;
    $message_reponse = trim($_POST['message_conseiller'] ?? '');
    $date_fin = date('Y-m-d'); // Date du jour pour la clôture de l'assistance

    // 2. Validation des données
    if ($id_assistance === 0 || $id_agriculteur === 0 || empty($message_reponse)) {
        $message_status = '<p class="warning">⚠️ Données manquantes ou invalides pour la réponse.</p>';
    } else {
        try {
            // 3. Requête UPDATE pour ajouter la réponse et mettre à jour la date de fin
            $sql = "UPDATE assistance_conseiller SET 
                        message_conseiller = :reponse,
                        date_fin_assistance = :date_fin
                    WHERE id = :id_assistance 
                    AND id_conseiller = :conseiller_id 
                    AND id_agriculteur_assiste = :agriculteur_id"; 

            $stmt = $pdo->prepare($sql);
            
            // 4. Liaison des paramètres
            $stmt->bindParam(':reponse', $message_reponse);
            $stmt->bindParam(':date_fin', $date_fin);
            $stmt->bindParam(':id_assistance', $id_assistance, PDO::PARAM_INT);
            $stmt->bindParam(':conseiller_id', $conseiller_id, PDO::PARAM_INT);
            $stmt->bindParam(':agriculteur_id', $id_agriculteur, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // 5. Vérification du succès et redirection
            if ($stmt->rowCount() > 0) {
                // Redirection vers la liste des messages du conseiller (à créer)
                header("Location: liste_message_conseiller.php?status=replied");
                exit();
            } else {
                $message_status = '<p class="error">❌ Erreur : Impossible de mettre à jour le message. Vérifiez l\'ID ou les permissions.</p>';
            }

        } catch (PDOException $e) {
            error_log("Erreur PDO réponse conseiller: " . $e->getMessage());
            $message_status = '<p class="error">❌ Une erreur est survenue lors de l\'enregistrement de la réponse.</p>';
        }
    }
} 

// --- Logique PHP d'Affichage du Formulaire (si on vient de la liste) ---

// Récupère l'ID de la requête à répondre si l'on arrive via un lien GET
$id_assistance_get = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message_agriculteur_data = null;

if ($id_assistance_get > 0) {
    try {
        // Sélectionne le message initial pour pré-remplir et confirmer l'information
        $sql_select = "SELECT id, id_agriculteur_assiste, message_agriculteur FROM assistance_conseiller 
                       WHERE id = :id_assistance AND id_conseiller = :conseiller_id";
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->bindParam(':id_assistance', $id_assistance_get, PDO::PARAM_INT);
        $stmt_select->bindParam(':conseiller_id', $conseiller_id, PDO::PARAM_INT);
        $stmt_select->execute();
        $message_agriculteur_data = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if (!$message_agriculteur_data) {
             $message_status = '<p class="error">❌ Message introuvable ou vous n\'avez pas la permission d\'y répondre.</p>';
        }
        
    } catch (PDOException $e) {
        error_log("Erreur PDO chargement message: " . $e->getMessage());
        $message_status = '<p class="error">❌ Erreur système lors du chargement du message.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre à l'Agriculteur</title>
    <link rel="stylesheet" href="styl_modi.css">
    <style>
        .form-container h2 { color: #28a745; margin-bottom: 20px; }
        .message-initial { 
            background: #f8f9fa; 
            padding: 15px; 
            border-left: 5px solid #007bff; 
            margin-bottom: 20px;
        }
        .message-initial p { margin: 5px 0; }
        .form-container form textarea {
            min-height: 180px;
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container form input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            transition: background-color 0.3s;
        }
        .form-container form input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>

<body> 
    <nav class="sidebar">
        <a href="conseiller_dashboard.php" class="logo">🌱Mon agricoach</a>
        </nav>
    
    <div class="main-content">
        <div class="form-container">
            <h2>Répondre à l'Agriculteur 💬</h2>
            
            <?php echo $message_status; ?>

            <?php if ($message_agriculteur_data): ?>
                
                <div class="message-initial">
                    <h4>Message de l'Agriculteur :</h4>
                    <p style="font-style: italic;"><?php echo htmlspecialchars($message_agriculteur_data['message_agriculteur']); ?></p>
                </div>
                
                <form action="message_conseiller.php" method="POST">
                    <input type="hidden" name="id_assistance" value="<?php echo htmlspecialchars($message_agriculteur_data['id']); ?>">
                    <input type="hidden" name="id_agriculteur" value="<?php echo htmlspecialchars($message_agriculteur_data['id_agriculteur_assiste']); ?>">
                    
                    <label for="message_conseiller">Votre Réponse :</label>
                    <textarea id="message_conseiller" name="message_conseiller" placeholder="Rédigez votre conseil ou réponse ici..." required></textarea>
                    
                    <input type="submit" value="Envoyer la Réponse et Clôturer l'Assistance">
                </form>
                
            <?php elseif (empty($message_status)): ?>
                 <p class="warning">Veuillez sélectionner un message dans votre liste pour y répondre.</p>
                <a href="liste_message_conseiller.php" style="background-color: #007bff; color: white; padding: 10px; border-radius: 5px; text-decoration: none;">Voir les messages en attente</a>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>