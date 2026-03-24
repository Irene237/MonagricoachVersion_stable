<?php
// Démarre la session
session_start();
// Inclut le fichier de connexion à la base de données
require_once 'db.php';

// --- Bloc de Vérification de Connexion (SÉCURITÉ) ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    // Redirige si l'utilisateur n'est pas connecté
    header("Location: connexion.php");
    exit();
}

$id_agriculteur = $_SESSION['user_id'];
$apintrants = [];
$error_message = null;

// Initialisation des variables de recherche pour l'affichage
$terme_recherche = $_GET['recherche'] ?? '';
$date_application = $_GET['date_aplication'] ?? '';

// --- LOGIQUE DE RECHERCHE ET REQUÊTE SQL SÉCURISÉE ---
$conditions=[];
$params=[':id_agriculteur' => $id_agriculteur]; // Ajouter l'ID de l'agriculteur aux paramètres

// Requête de base pour l'agriculteur connecté
$query_string ="SELECT 
                    a.id, 
                    a.date_aplication,
                    a.quantite_appliquee,
                    a.unite_appliquee,
                    a.methode_application,
                    a.notes,
                    p.statut_plantation AS statut_plantation, 
                    u.nom AS nom_agriculteur,
                    i.nom_intrant AS nom_intrant 
                FROM application_intrant AS a 
                JOIN plantation AS p ON a.id_plantation = p.id 
                JOIN intrant AS i ON a.id_intrant = i.id 
                JOIN utilisateur AS u ON a.id_agriculteur = u.id 
                WHERE a.id_agriculteur = :id_agriculteur"; // Filtrer directement par l'ID utilisateur

// 1. Recherche par terme (Nom Intrant, Quantité, Statut, etc.)
if(!empty($terme_recherche)){
    $terme_sql = '%' . strtolower($terme_recherche) . '%';
    // Utilisation de LOWER() pour une recherche insensible à la casse
    $conditions[] = "(
                        LOWER(i.nom_intrant) LIKE :termeRecherche 
                        OR LOWER(CAST(a.quantite_appliquee AS CHAR)) LIKE :termeRecherche
                        OR LOWER(a.unite_appliquee) LIKE :termeRecherche 
                        OR LOWER(p.statut_plantation) LIKE :termeRecherche 
                        OR LOWER(a.methode_application) LIKE :termeRecherche
                     )";
    $params[':termeRecherche'] = $terme_sql;
}

// 2. Recherche par date (si vous implémentez ce champ de recherche)
if(!empty($date_application)){
    $conditions[] = "a.date_aplication = :date_aplication";
    $params[':date_aplication'] = $date_application;
}
    
if(!empty($conditions)) {
    $query_string .= " AND " . implode(" AND ", $conditions);
}

// 3. Ajout du tri
$query_string .= " ORDER BY a.date_aplication DESC"; 

try{
    $stmt = $pdo->prepare($query_string);
    $stmt->execute($params);
    $apintrants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
}catch (PDOException $e) {
    // Loguer l'erreur pour le débogage et afficher un message générique pour l'utilisateur
    error_log("Erreur de récupération des applications d'intrants: " . $e->getMessage());
    $error_message = "Erreur lors de l'exécution de la recherche.";
}
?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Résultats de Recherche Applications d'Intrants | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- Récupération du Style de liste_appli_intrant.php --- */
        
        :root {
            --color-primary-emerald: #06bdbdff; 
            --color-primary-dark: #0cb4b4ff; 
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;
            --color-accent-danger: #ef4444; 
            --color-accent-success: #10B981; 
            --color-heading: #111827; 
            --color-card-bg: #FFFFFF; 
            --color-light-bg: #F5F8F5; 
            --color-text-dark: #374151;
            --color-text-medium: #4B5563; 
            --color-disconnect-bg: var(--color-secondary-gold); 
            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Montserrat', sans-serif;
            --sidebar-width: 260px; 
        }

        body {
            font-family: var(--font-main);
            margin: 0;
            padding: 0;
            background-color: var(--color-light-bg);
            color: var(--color-text-dark);
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden; 
        }

        /* --- BARRE LATÉRALE (SIDEBAR) (RÉUTILISATION) --- */
        .sidebar {
            width: var(--sidebar-width); 
            background-color: var(--color-card-bg); 
            padding: 25px 0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar .logo {
            font-family: var(--font-heading);
            color: var(--color-primary-emerald); 
            font-size: 20px;
            font-weight: 800; 
            text-align: center;
            padding: 0 20px 40px 20px; 
            text-decoration : none;
        }
        .sidebar .logo i {
            color: var(--color-primary-emerald);
            font-size: 28px;
            margin-right: 5px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: var(--color-text-dark);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s ease-in-out; 
            border-left: 0px solid transparent;
        }

        .sidebar li a i {
            margin-right: 15px; 
            font-size: 18px;
            color: var(--color-text-medium);
            width: 25px; 
            text-align: center;
            font-family: 'Font Awesome 5 Free'; 
            font-weight: 900;
        }
        
        .sidebar li a:hover {
            background-color: #f5f8f8ff;
            color: var(--color-primary-dark);
        }

        /* Lien Actif : Application Intrant (simulé pour la page de recherche liée) */
        .sidebar li a[href="liste_appli_intrant.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }
        .sidebar li a[href="liste_appli_intrant.php"] i {
             color: var(--color-primary-emerald); 
        }
        
        .btn-deconnexion-wrapper {
            margin-top: auto; 
            padding: 25px; 
        }
        
        .sidebar .btn_lien button { 
             border: none; 
             padding: 0; 
             background: none; 
             width: 100%; 
        }

        .sidebar .btn_lien button a {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background-color: var(--color-disconnect-bg); 
            color: var(--color-card-bg); 
            padding: 12px 20px;
            border-radius: 8px; 
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); 
            transition: all 0.3s; 
            text-decoration: none;
            border-left: none; 
        }
        .sidebar .btn_lien button a:hover { 
             background-color: var(--color-secondary-gold-hover);
             box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
        } 

        /* --- CONTENU PRINCIPAL (RÉUTILISATION) --- */
        .main-content {
            margin-left: var(--sidebar-width); 
            padding: 50px 40px; 
            flex-grow: 1;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px; 
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
        }

        .header h1 {
            font-family: var(--font-heading);
            font-weight: 900; 
            color: var(--color-heading); 
            font-size: 35px; 
            margin: 0;
            line-height: 1.1;
        }
        
        .add-new-link {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            padding: 12px 25px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.2s ease, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(6, 189, 189, 0.4); 
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-new-link:hover { 
             background-color: var(--color-primary-dark); 
             box-shadow: 0 6px 15px rgba(6, 189, 189, 0.6);
        }

        .content-container {
            background: var(--color-card-bg);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .content-container h2 {
            font-family: var(--font-heading); 
            font-weight: 700;
            font-size: 24px; 
            color: var(--color-heading);
            margin-top: 0;
            margin-bottom: 25px;
        }

        /* --- Formulaire de Recherche (RÉUTILISATION) --- */
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            align-items: center;
            border: 1px solid #E5E7EB; 
            padding: 5px;
            border-radius: 10px;
            background-color: var(--color-light-bg);
        }

        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: none; 
            border-radius: 6px;
            font-size: 15px;
            background-color: var(--color-card-bg); 
        }
        .search-form button {
            padding: 10px 18px; 
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .search-form button[type="submit"] {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.2);
        }
        .search-form button[type="button"] {
            background-color: #D1D5DB; 
            color: var(--color-text-dark);
        }
        .search-info { /* Style d'information de recherche */
            font-size: 16px;
            font-weight: 600;
            color: var(--color-primary-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        /* --- STYLE DES LISTES D'APPLICATIONS (RÉUTILISATION) --- */
        
        .appli-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
            display: grid;
            gap: 20px;
        }

        .appli-item {
            background-color: var(--color-card-bg);
            border: 1px solid #E5E7EB;
            border-left: 6px solid var(--color-primary-emerald); 
            border-radius: 12px;
            padding: 20px 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .appli-item:hover {
            box-shadow: 0 8px 20px rgba(6, 189, 189, 0.15); 
            transform: translateY(-3px);
        }
        
        .appli-main-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start; 
            margin-bottom: 15px;
        }

        .appli-details {
             flex-grow: 1;
             margin-right: 30px;
        }
        
        .appli-details h3 {
             margin: 0 0 10px 0;
             font-family: var(--font-heading);
             font-size: 22px;
             color: var(--color-heading);
             font-weight: 800;
             display: flex;
             align-items: center;
             gap: 10px;
        }
        
        .appli-details h3 i {
            color: var(--color-primary-emerald);
            font-size: 24px;
        }
        
        .metadata-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 10px 20px;
            margin-top: 15px;
        }

        .metadata-detail {
            font-size: 15px;
            color: var(--color-text-medium);
            display: flex;
            align-items: center;
        }
        
        .metadata-detail strong {
            font-weight: 600;
            color: var(--color-heading);
            min-width: 140px; 
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* --- BLOC D'INFORMATIONS À DROITE (RÉUTILISATION) --- */
        .appli-info-block {
            min-width: 200px; 
            max-width: 300px;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            background-color: var(--color-light-bg);
            border: 1px solid #E5E7EB;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .appli-info-block h4 {
            font-family: var(--font-heading);
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: var(--color-heading);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .info-tag {
            display: block;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 10px;
        }

        /* Styles de Statut Plantation */
        .status-growing {
            background-color: rgba(16, 185, 129, 0.15); 
            color: var(--color-accent-success);
        }
        .status-ready {
            background-color: rgba(6, 189, 189, 0.2); 
            color: var(--color-primary-emerald);
        }
        .status-harvested {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-dark);
            border: 1px solid var(--color-primary-dark);
        }
        
        .info-actions {
             margin-top: 15px;
             display: flex;
             justify-content: center;
             gap: 15px;
        }
        
        .notes-detail {
            padding-top: 15px;
            border-top: 1px dashed #E5E7EB;
            font-size: 14px;
            line-height: 1.5;
            color: var(--color-text-medium);
        }
        
        .notes-detail strong {
            font-style: normal;
            font-weight: 700;
            color: var(--color-heading);
            margin-bottom: 5px;
            display: block;
        }
        
        /* Bloc d'actions */
        .info-actions a, .info-actions button {
            width: 38px; 
            height: 38px; 
            border-radius: 50%; 
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            padding: 0;
            font-size: 16px;
        }

        /* Modifier (Émeraude) */
        .info-actions a {
            background-color: var(--color-primary-emerald);
            box-shadow: 0 2px 5px rgba(6, 189, 189, 0.3);
        }
        /* Supprimer (Danger Rouge) */
        .delete-button {
            background-color: var(--color-accent-danger);
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
        }
        
        .info-actions a:hover, .delete-button:hover {
            transform: scale(1.1);
        }
        
        .info-actions .fas { 
             color: var(--color-card-bg);
        }
        
        /* Style pour les messages d'erreur */
        .error-php-message {
            color: var(--color-accent-danger); 
            background-color: #FEE2E2; 
            padding: 15px; 
            border-radius: 8px; 
            font-weight: 600; 
            margin-bottom: 30px;
        }

        /* Style pour "Aucun élément" */
        .no-records {
            background-color: #E5E7EB;
            padding: 20px;
            border-radius: 10px;
            color: var(--color-text-dark);
            font-size: 16px;
            font-weight: 500;
        }
        
    </style>
</head>

<body> 
    <nav class="sidebar">
        <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i> MonAgriCoach
            </a>
        
        <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures </a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>**Verser l'engrais**</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> stock engrais</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li>
            
        </ul>
        
        <div class="btn-deconnexion-wrapper"> 
            <div class="btn_lien">
                <button >
                    <a href="deconnexion.php">
                        <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                    </a>
                </button>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Résultats de Recherche Dépot Engrais</h1>
            <a href="appli_intrant.php" class="add-new-link">
                <i class="fas fa-plus-circle"></i> Ajouter une Application
            </a> 
        </div>

        <?php if (isset($error_message)): ?>
            <p class="error-php-message"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        
        <div class="content-container">
            <h2>Résultats pour votre recherche</h2>
            
            <form action="recherche_appli_intrant.php" method="GET" class="search-form">
                <label for="search_query" style="display:none;">Rechercher une application intrant</label>
                <input type="text" name="recherche" placeholder="Recherche par nom d'intrant, statut..." id="search_query" value="<?= htmlspecialchars($terme_recherche); ?>">
                
                <?php /* <input type="date" name="date_aplication" value="<?= htmlspecialchars($date_application); ?>" title="Filtrer par date"> */ ?>
                
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <button type="button" onclick="window.location.href='liste_appli_intrant.php'"><i class="fas fa-sync-alt"></i> Liste Complète</button>
            </form>

            <p class="search-info">
                <i class="fas fa-filter"></i> **<?= count($apintrants); ?>** application(s) trouvée(s) pour le critère : **"<?= htmlspecialchars($terme_recherche); ?>"**.
            </p>

            <?php if (count($apintrants) > 0): ?>
                <ul class="appli-list">
                    <?php foreach($apintrants as $apintrant ): 
                        // Logique pour déterminer la classe de statut
                        $statut = htmlspecialchars($apintrant['statut_plantation']);
                        $status_class = 'status-growing'; 
                        
                        if (stripos($statut, 'récolté') !== false || stripos($statut, 'fini') !== false) {
                            $status_class = 'status-harvested';
                        } elseif (stripos($statut, 'maturation') !== false || stripos($statut, 'prêt') !== false) {
                            $status_class = 'status-ready';
                        }
                    ?>
                        <li class="appli-item">
                            <div class="appli-main-row">
                                <div class="appli-details">
                                    <h3>
                                        <i class="fas fa-cogs"></i> 
                                        Application de **<?= htmlspecialchars($apintrant['nom_intrant']); ?>**
                                    </h3>
                                    
                                    <div class="metadata-grid">
                                        <div class="metadata-detail">
                                            <strong><i class="fas fa-calendar-alt"></i> Date d'application :</strong> 
                                            <span><?= date('d/m/Y', strtotime($apintrant['date_aplication'])); ?></span>
                                        </div>
                                        <div class="metadata-detail">
                                            <strong><i class="fas fa-user-circle"></i> Agriculteur :</strong> 
                                            <span><?= htmlspecialchars($apintrant['nom_agriculteur']); ?></span>
                                        </div>
                                        <div class="metadata-detail">
                                            <strong><i class="fas fa-weight-hanging"></i> Quantité Appliquée :</strong> 
                                            <span><?= htmlspecialchars($apintrant['quantite_appliquee']) . ' ' . htmlspecialchars($apintrant['unite_appliquee']); ?></span>
                                        </div>
                                        <div class="metadata-detail">
                                            <strong><i class="fas fa-wrench"></i> Méthode :</strong> 
                                            <span><?= htmlspecialchars($apintrant['methode_application']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="appli-info-block">
                                    <h4><i class="fas fa-seedling"></i> État de la Plantation</h4>
                                    <span class="info-tag <?= $status_class; ?>">
                                        <?= $statut; ?>
                                    </span>
                                    <p style="font-size: 13px; color: var(--color-text-medium); margin: 0; font-style: italic;">
                                        Application effectuée à cette phase.
                                    </p>
                                    
                                    <div class="info-actions">
                                        <a href="modifier_ap_intrant.php?id=<?= $apintrant['id']; ?>" title="Modifier l'Application">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="supprime_ap_intrant.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette application de <?= htmlspecialchars($apintrant['nom_intrant']); ?> du <?= date('d/m/Y', strtotime($apintrant['date_aplication'])); ?> ?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $apintrant['id']; ?>" >
                                            <button type="submit" title="Supprimer l'Application" class="delete-button">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($apintrant['notes'])): ?>
                                <div class="notes-detail">
                                    <strong><i class="fas fa-clipboard-list"></i> Notes & Observations :</strong> 
                                    <?= nl2br(htmlspecialchars($apintrant['notes'])); ?>
                                </div>
                            <?php endif; ?>

                        </li> 
                    <?php endforeach;?>
                </ul>
            <?php else:?> 
                <p class="no-records"><i class="fas fa-info-circle"></i> Aucun intrant trouvée pour vos critères de recherche : **"<?= htmlspecialchars($terme_recherche); ?>"**.</p> 
            <?php endif;?>
        </div> 
    </div>
</body>
</html>