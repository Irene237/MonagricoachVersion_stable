<?php
// Fichier : liste_message_cons.php (ou recherche_mesa_cons.php si c'est son nom)
session_start();
require_once 'db.php'; 

// --- Sécurité ---
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: connexion.php");
    exit();
}

$conseils = [];
$conditions = [];
$params = [];
$message_error = '';

// Récupération des paramètres de recherche
$recherche_query = $_GET['recherche'] ?? '';
$date_fin_assistance = $_GET['date_fin_assistance'] ?? '';

// Requête de base pour les conseils
$query_string = "
    SELECT 
        m.id, 
        m.date_debut_assistance,
        m.date_fin_assistance,
        m.message_agriculteur,
        CONCAT(u.nom, ' ', u.prenom) AS nom_complet_agriculteur
    FROM 
        assistance_conseiller AS m 
    JOIN 
        utilisateur AS u ON m.id_agriculteur_assiste = u.id 
    WHERE 
        u.type_utilisateur = 'agriculteur'
";

// --- Logique de recherche avancée ---

if (!empty($recherche_query)) {
    $termeRecherche = '%' . $recherche_query . '%';
    // Recherche sur le message, la date de début OU le nom/prénom de l'agriculteur
    $conditions[] = "
        (m.message_agriculteur LIKE :termeRecherche 
        OR m.date_debut_assistance LIKE :termeRecherche 
        OR u.nom LIKE :termeRecherche 
        OR u.prenom LIKE :termeRecherche)
    ";
    $params[':termeRecherche'] = $termeRecherche;
}

if (!empty($date_fin_assistance)) {
    // Filtrer par la date de fin exacte
    $conditions[] = "m.date_fin_assistance = :date_fin_assistance";
    $params[':date_fin_assistance'] = $date_fin_assistance;
}

// Ajouter les conditions à la requête
if (!empty($conditions)) {
    $query_string .= " AND " . implode(" AND ", $conditions);
}

$query_string .= " ORDER BY m.date_debut_assistance DESC";

try {
    $stmt = $pdo->prepare($query_string);
    $stmt->execute($params);
    $conseils = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Erreur de récupération: " . $e->getMessage());
    $message_error = "Erreur de base de données. Les conseils n'ont pas pu être chargés.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Conseils - MonAgriCoach</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* --- PALETTE ET FOND (Identique aux autres pages) --- */
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
        
        /* --- SIDEBAR (Harmonisation) --- */
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
        .sidebar .logo { font-family: 'Montserrat', sans-serif; color: var(--color-primary-emerald); font-size: 20px; font-weight: 800; text-align: center; padding: 0 20px 40px 20px; text-decoration : none; display: block; }
        .sidebar .logo i { color: var(--color-primary-emerald); font-size: 28px; margin-right: 5px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; display: flex; flex-direction: column; }
        .sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: var(--color-text-dark); text-decoration: none; font-size: 15px; font-weight: 500; transition: all 0.2s ease-in-out; border-left: 0px solid transparent; }
        .sidebar li a i { margin-right: 15px; font-size: 18px; color: #4B5563; width: 25px; text-align: center; }
        .sidebar li a:hover { background-color: var(--color-secondary-beige-light); color: var(--color-primary-dark); }
        
        /* Lien Actif: Mes messages */
        .sidebar li a[href="liste_message_cons.php"] {
            background-color: rgba(6, 189, 189, 0.1);
            color: var(--color-primary-emerald);
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald);
        }
        .sidebar li a[href="liste_message_cons.php"] i {
            color: var(--color-primary-emerald);
        }

        /* Bouton de déconnexion stylisé */
        .disconnect-item { margin-top: auto; padding: 25px; display: block; }
        .disconnect-item a { 
            display: flex; justify-content: center; align-items: center; gap: 10px; 
            background-color: var(--color-secondary-gold); 
            color: var(--color-card-bg) !important; 
            padding: 12px 20px; 
            border-radius: var(--border-radius-sm); 
            font-size: 15px; 
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4); 
            text-decoration: none; 
            border-left: none !important; 
            transition: background-color 0.2s;
        }
        .disconnect-item a i { color: var(--color-card-bg) !important; }
        .disconnect-item a:hover { background-color: #E37D00 !important; }

        /* --- CONTENU PRINCIPAL --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            flex-grow: 1;
            min-width: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-family: 'Montserrat', sans-serif;
            color: var(--color-text-dark);
            font-size: 28px;
            margin: 0;
        }
        
        .content-container {
            background-color: var(--color-card-bg);
            padding: 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .content-container h2 {
            font-size: 18px;
            color: #4B5563;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #E5E7EB;
        }

        /* --- Lien 'Écrire un conseil' (Bouton d'Action Haut) --- */
        .add-new-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--color-primary-emerald);
            color: white;
            padding: 10px 15px;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .add-new-link:hover {
            background-color: var(--color-primary-dark);
        }

        /* --- FORMULAIRE DE RECHERCHE --- */
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap; /* Permet de passer à la ligne si l'écran est petit */
        }

        .search-form input[type="text"],
        .search-form input[type="date"] {
            padding: 10px;
            border: 1px solid #D1D5DB;
            border-radius: var(--border-radius-sm);
            font-size: 15px;
        }
        .search-form input[type="text"] {
            flex-grow: 1;
        }
        .search-form input[type="date"] {
            max-width: 180px;
        }


        .search-form button {
            padding: 10px 15px;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .search-form button[type="submit"] {
            background-color: var(--color-secondary-gold);
            color: white;
        }
        .search-form button[type="submit"]:hover {
            background-color: #E37D00;
        }

        .search-form button[type="button"] { /* Rénitialiser */
            background-color: #9CA3AF;
            color: white;
        }
        .search-form button[type="button"]:hover {
            background-color: #6B7280;
        }

        /* --- TABLEAU --- */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        table thead tr {
            background-color: var(--color-secondary-beige-light);
            color: var(--color-text-dark);
            font-size: 14px;
            text-transform: uppercase;
        }

        table th {
            padding: 12px 15px;
            border-bottom: 2px solid #D1D5DB;
        }

        table tbody tr {
            border-bottom: 1px solid #E5E7EB;
            transition: background-color 0.1s;
        }

        table tbody tr:hover {
            background-color: #F3F4F6;
        }

        table td {
            padding: 12px 15px;
            font-size: 14px;
            max-width: 300px;
            /* Pour la colonne message, on veut que le texte soit lisible, donc pas de 'white-space: nowrap;' */
        }
        
        /* Style des actions (suppression) */
        .table-actions {
            text-align: center;
            white-space: nowrap;
            width: 80px;
        }
        
        .delete-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #EF4444;
            font-size: 16px;
            padding: 5px;
            transition: color 0.2s;
            display: inline-block;
        }
        
        .delete-button:hover {
            color: #B91C1C;
        }
        
        .delete-button i.fas.fa-trash-alt {
            font-size: 16px;
            color: #EF4444;
        }
        
        /* Bouton Editer (harmonisation) */
        .edit-button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-primary-emerald);
            font-size: 16px;
            padding: 5px;
            transition: color 0.2s;
            display: inline-block;
        }
        .edit-button:hover {
            color: var(--color-primary-dark);
        }

        p.no-results {
            padding: 15px;
            background-color: #FEF3C7;
            border: 1px solid var(--color-secondary-gold);
            border-radius: var(--border-radius-sm);
            color: #92400E;
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
            <li><a href="advisor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
            <li><a href="outiis_analyse.php"><i class="fas fa-flask"></i> Outils d'analyse</a></li>
            <li><a href="rapport.php"><i class="fas fa-file-invoice"></i> Rapports personnalisés</a></li>
            <li><a href="liste_message_cons.php"><i class="fas fa-comments"></i> **Mes messages**</a></li>
            <li><a href="liste_agriculteur_cons.php"><i class="fas fa-user-friends"></i> Agriculteurs</a></li>
           
          
            
            <li class="disconnect-item">
                <a href="deconnexion.php">
                    <i class="fas fa-sign-out-alt"></i> DÉCONNEXION
                </a>
            </li>
        </ul>
    </nav>


    <div class="main-content">
        <div class="header">
            <h1>Mes Conseils</h1>
            <a href="redige_message.php" class="add-new-link">
                <i class="fas fa-edit"></i> Écrire un conseil
            </a>
        </div>
        
        <div class="content-container">
            <h2>Gérer l'ensemble de vos conseils</h2>
            
            <form action="liste_message_cons.php" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="recherche" 
                    placeholder="Recherche par nom, date ou message..."
                    value="<?php echo htmlspecialchars($recherche_query); ?>"
                >
                <input 
                    type="date" 
                    name="date_fin_assistance" 
                    title="Filtrer par date de fin d'assistance"
                    value="<?php echo htmlspecialchars($date_fin_assistance); ?>"
                >
                
                <button type="submit">
                    <i class="fas fa-search"></i> Rechercher
                </button>
                <button type="button" onclick="window.location.href='liste_message_cons.php'">
                    <i class="fas fa-sync-alt"></i> Réinitialiser
                </button>
            </form>

            <?php if (isset($message_error)): ?>
                <p class="no-results" style="background-color: #FEE2E2; border: 1px solid #EF4444; color: #EF4444;"><?php echo $message_error; ?></p>
            <?php elseif (count($conseils) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr> 
                                <th>Date Début</th> 
                                <th>Date Fin</th> 
                                <th>Message de l'Agriculteur</th> 
                                <th>Agriculteur Assisté</th> 
                                <th>Action</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($conseils as $conseil ): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($conseil['date_debut_assistance']); ?></td>
                                    <td><?php echo htmlspecialchars($conseil['date_fin_assistance']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($conseil['message_agriculteur'], 0, 80)) . (strlen($conseil['message_agriculteur']) > 80 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($conseil['nom_complet_agriculteur']); ?></td>
                                    
                                    <td class="table-actions">
                                        <a href="editer_message.php?id=<?php echo htmlspecialchars($conseil['id']); ?>" title="Éditer le conseil" class="edit-button">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="supprimer_messages.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($conseil['id']); ?>" >
                                            <button type="submit" title="Supprimer le conseil" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce conseil ?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr> 
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div> 
            <?php else: ?> 
                <p class="no-results">Aucun conseil trouvé pour vos critères de recherche.</p> 
            <?php endif; ?>
        </div> 
    </div>
</body>
</html>