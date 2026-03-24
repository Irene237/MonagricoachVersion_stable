<?php
// Fichier : liste_message_cons.php
session_start();
require_once 'db.php'; 

// --- Sécurité ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$id_conseiller_connecte = $_SESSION['user_id']; 

$communications = [];
$search_query = $_GET['recherche'] ?? '';
$search_param = '';

try {
    // Requête pour récupérer les échanges complets
    // On utilise UNION pour récupérer les messages initiés par l'un ou l'autre 
    // et on s'assure d'avoir les colonnes pour les deux types de messages
    $sql = "
        SELECT 
            'ÉCHANGE' AS type_echange,
            m.id AS id_message, 
            m.date_debut_assistance AS date_message,
            m.message_conseiller AS mon_message, 
            m.message_agriculteur AS message_de_lagri,
            m.id_agriculteur_assiste AS id_interlocuteur,
            CONCAT(u_agri.nom, ' ', u_agri.prenom) AS nom_interlocuteur,
            'assistance_conseiller' AS table_source 
        FROM 
            assistance_conseiller AS m 
        JOIN 
            utilisateur AS u_agri ON m.id_agriculteur_assiste = u_agri.id
        WHERE 
            m.id_conseiller = :id_cons_1 
            AND (m.message_conseiller != '' OR m.message_agriculteur != '')
            " . ($search_query ? " AND u_agri.nom LIKE :search_1" : "") . "

        UNION ALL

        SELECT 
            'ÉCHANGE' AS type_echange,
            r.id AS id_message, 
            r.date_envoi AS date_message,
            r.message_conseiller AS mon_message, 
            r.message_agriculteur AS message_de_lagri,
            r.id_agriculteur_assiste AS id_interlocuteur,
            CONCAT(u_agri.nom, ' ', u_agri.prenom) AS nom_interlocuteur,
            'reponse' AS table_source 
        FROM 
            reponse AS r 
        JOIN 
            utilisateur AS u_agri ON r.id_agriculteur_assiste = u_agri.id
        WHERE 
            r.id_conseiller = :id_cons_2 
            AND (r.message_agriculteur != '' OR r.message_conseiller != '')
            " . ($search_query ? " AND u_agri.nom LIKE :search_2" : "") . "
            
        ORDER BY 
            date_message DESC 
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_cons_1', $id_conseiller_connecte, PDO::PARAM_INT);
    $stmt->bindValue(':id_cons_2', $id_conseiller_connecte, PDO::PARAM_INT);

    if ($search_query) {
        $search_param = '%' . $search_query . '%';
        $stmt->bindValue(':search_1', $search_param, PDO::PARAM_STR);
        $stmt->bindValue(':search_2', $search_param, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $communications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur : " . $e->getMessage());
    $message_error = "Erreur de base de données.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Communications - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* --- Votre CSS d'origine conservé --- */
        :root {
            --color-primary-emerald: #0ab9b1ff; 
            --color-primary-dark: #008080; 
            --color-secondary-gold: #fd9f07ff;
            --color-secondary-beige-light: #f5f8f8ff;
            --color-card-bg: #FFFFFF;
            --color-light-bg: #F8F9FA;
            --color-text-dark: #1F2937;
            --sidebar-width: 260px;
            --border-radius-lg: 15px;
            --border-radius-sm: 8px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--color-light-bg);
            color: var(--color-text-dark);
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar { width: var(--sidebar-width); background-color: var(--color-card-bg); padding: 25px 0; height: 100vh; position: fixed; top: 0; left: 0; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; z-index: 1000; }
        .sidebar .logo { font-family: 'Montserrat', sans-serif; color: var(--color-primary-emerald); font-size: 20px; font-weight: 800; text-align: center; padding: 0 20px 40px 20px; text-decoration : none; display: block; }
        .sidebar .logo i { color: var(--color-primary-emerald); font-size: 28px; margin-right: 5px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; display: flex; flex-direction: column; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark); text-decoration: none; font-size: 15px; font-weight: 500; transition: all 0.2s ease-in-out; border-left: 0px solid transparent; }
        .sidebar li a i { margin-right: 15px; font-size: 18px; color: #4B5563; width: 25px; text-align: center; }
        .sidebar li a:hover { background-color: var(--color-secondary-beige-light); color: var(--color-primary-dark); }
        .sidebar li a[href="liste_message_cons.php"] { background-color: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald); font-weight: 600; border-left: 5px solid var(--color-primary-emerald); }
        .sidebar li a[href="liste_message_cons.php"] i { color: var(--color-primary-emerald); }
        
        .disconnect-item { margin-top: auto; padding: 25px; display: block; }
        .disconnect-item a { display: flex; justify-content: center; align-items: center; gap: 10px; background-color: var(--color-secondary-gold); color: var(--color-card-bg) !important; padding: 12px 20px; border-radius: var(--border-radius-sm); font-size: 15px; font-weight: 700; box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); text-decoration: none; border-left: none !important; transition: background-color 0.2s; }
        .disconnect-item a:hover { background-color: #E37D00 !important; }

        .main-content { margin-left: var(--sidebar-width); padding: 40px; flex-grow: 1; min-width: 0; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-family: 'Montserrat', sans-serif; color: var(--color-text-dark); font-size: 28px; margin: 0; }
        .content-container { background-color: var(--color-card-bg); padding: 30px; border-radius: var(--border-radius-lg); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .content-container h2 { font-size: 18px; color: #4B5563; margin-top: 0; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #E5E7EB; }
        
        .add-new-link { display: inline-flex; align-items: center; gap: 8px; background-color: var(--color-primary-emerald); color: white; padding: 10px 15px; border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; transition: background-color 0.2s; }
        .add-new-link:hover { background-color: var(--color-primary-dark); }
        
        .search-form { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        .search-form input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #D1D5DB; border-radius: var(--border-radius-sm); font-size: 15px; }
        .search-form button { padding: 10px 15px; border: none; border-radius: var(--border-radius-sm); font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
        .search-form button[type="submit"] { background-color: var(--color-secondary-gold); color: white; }
        .search-form button[type="submit"]:hover { background-color: #E37D00; }
        .search-form button[type="button"] { background-color: #9CA3AF; color: white; }
        .search-form button[type="button"]:hover { background-color: #6B7280; }

        .card-container { display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); }
        .message-card { background-color: var(--color-secondary-beige-light); padding: 20px; border-radius: var(--border-radius-sm); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; justify-content: space-between; }
        .message-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #E5E7EB; }
        .card-meta { font-size: 13px; color: #6B7280; display: flex; flex-direction: column; }
        .card-meta strong { font-weight: 600; color: var(--color-text-dark); }
        .card-content { margin-bottom: 15px; flex-grow: 1; }
        
        .card-actions { border-top: 1px solid #E5E7EB; padding-top: 15px; display: flex; justify-content: flex-end; gap: 10px; }
        .badge-echange { background-color: rgba(6, 189, 189, 0.1); color: var(--color-primary-dark); padding: 5px 10px; border-radius: 4px; font-weight: 600; font-size: 12px; }

        .delete-button { background: none; border: none; cursor: pointer; color: #EF4444; font-size: 16px; transition: color 0.2s; }
        .reply-link { background-color: var(--color-primary-emerald); color: white; padding: 8px 15px; border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; font-size: 14px; transition: background-color 0.2s; }
        .reply-link:hover { background-color: var(--color-primary-dark); }
        .view-link { background-color: #9CA3AF; color: white; padding: 8px 15px; border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; font-size: 14px; }
        
        p.info { padding: 15px; background-color: #FEF3C7; border: 1px solid var(--color-secondary-gold); border-radius: var(--border-radius-sm); color: #92400E; font-weight: 500; }
        p.error { background-color: #FEE2E2; border: 1px solid #EF4444; color: #EF4444; }
    </style>
</head>

<body> 
    <nav class="sidebar">
        <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <ul>
            <li><a href="advisor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> Rapports</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> Mes messages</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
            <li><a href="modifier_profile.php"><i class="fas fa-user-edit"></i> Modifier mon compte</a></li>
            <li class="disconnect-item">
                <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> DÉCONNEXION</a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Historique des Communications</h1>
            <a href="redige_message.php" class="add-new-link">
                <i class="fas fa-edit"></i> Écrire un nouveau message
            </a>
        </div>
        
        <div class="content-container">
            <h2>Messages envoyés et réponses reçues</h2>
            
            <form action="liste_message_cons.php" method="GET" class="search-form">
                <input type="text" name="recherche" placeholder="Recherche par nom de l'agriculteur..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_message_cons.php'"><i class="fas fa-sync-alt"></i> Réinitialiser</button>
            </form>

            <?php if (isset($message_error)): ?>
                <p class="error"><?php echo $message_error; ?></p>
            <?php elseif (count($communications) > 0): ?>
                
                <div class="card-container">
                    <?php foreach($communications as $com ): ?>
                        <div class="message-card">
                            <div class="card-header">
                                <span class="badge-echange">
                                    <i class="fas fa-sync"></i> ÉCHANGE
                                </span>
                                <div class="card-meta">
                                    <span>Date: <strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($com['date_message']))); ?></strong></span>
                                    <span>Interlocuteur: <strong><?php echo htmlspecialchars($com['nom_interlocuteur']); ?></strong></span>
                                </div>
                            </div>
                            
                            <div class="card-content">
                                <div style="margin-bottom: 12px;">
                                    <h4 style="font-size: 14px; margin: 0 0 5px 0; color: var(--color-primary-dark);">Message de l'agriculteur :</h4>
                                    <p style="font-size: 14px; font-style: italic;">
                                        <?php echo !empty($com['message_de_lagri']) ? htmlspecialchars($com['message_de_lagri']) : "<em>Pas de message</em>"; ?>
                                    </p>
                                </div>

                                <div style="border-top: 1px dashed #ccc; padding-top: 10px;">
                                    <h4 style="font-size: 14px; margin: 0 0 5px 0; color: var(--color-secondary-gold);">Votre message :</h4>
                                    <p style="font-size: 14px;">
                                        <?php echo !empty($com['mon_message']) ? htmlspecialchars($com['mon_message']) : "<em>En attente de réponse</em>"; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <form action="supprimer_messages.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($com['id_message']); ?>" >
                                    <input type="hidden" name="table_source" value="<?php echo htmlspecialchars($com['table_source']); ?>" >
                                    <button type="submit" title="Supprimer ce message" class="delete-button" onclick="return confirm('Êtes-vous sûr ?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>

                                <a href="voir_reponse.php?id=<?php echo htmlspecialchars($com['id_message']); ?>" class="view-link">
                                    <i class="fas fa-eye"></i> Voir
                                </a>

                                <a href="repondre_message.php?agri_id=<?php echo htmlspecialchars($com['id_interlocuteur']); ?>" class="reply-link">
                                    <i class="fas fa-reply"></i> Répondre
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div> 
            <?php else: ?> 
                <p class="info">Aucune communication trouvée.</p> 
            <?php endif; ?>
        </div> 
    </div>
</body>
</html>