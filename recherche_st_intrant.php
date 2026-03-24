<?php
// Démarre la session pour identifier l'utilisateur
session_start(); 
// Assurez-vous que 'db.php' est correctement configuré.
require_once 'db.php';

// --- Gestion de la connexion (Sécurisé pour l'utilisateur connecté) ---
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    header("Location: connexion.php");
    exit();
}
$id_agriculteur = $_SESSION['user_id'];

$conditions=[];
$params=[];
$error_message = null;

// Requête de base. Ajout du filtrage par id_agriculteur pour ne voir que ses stocks.
$query_string ="SELECT s.*, u.nom AS nom_agriculteur, i.nom_intrant AS nom_intrant 
                FROM stock_intrant AS s 
                JOIN intrant AS i ON s.id_intrant = i.id 
                JOIN utilisateur AS u ON s.id_agriculteur = u.id 
                WHERE u.type_utilisateur = 'agriculteur'
                AND s.id_agriculteur = :id_agriculteur"; // Point-virgule ajouté ici

$params[':id_agriculteur'] = $id_agriculteur;

// --- Bloc de Filtrage/Recherche ---
if(!empty($_GET['recherche'])){
    $termeRecherche= '%' . $_GET['recherche'] . '%';
    // Ajout du nom de l'intrant dans la recherche
    $conditions[] = "(s.quantite_actuelle LIKE :termeRecherche OR s.seuil_alerte LIKE :termeRecherche OR u.nom LIKE :termeRecherche OR i.nom_intrant LIKE :termeRecherche)";
    $params[':termeRecherche'] = $termeRecherche;
}

if(!empty($_GET['date_derniere_mise_a_jour'])){
    $conditions[] = "s.date_derniere_mise_a_jour = :date_derniere_mise_a_jour";
    $params[':date_derniere_mise_a_jour'] = $_GET['date_derniere_mise_a_jour'];
}

if(!empty($conditions)) {
    $query_string .= " AND " . implode(" AND ", $conditions);
}

// Tri pour que les alertes apparaissent en premier
$query_string .= " ORDER BY CASE WHEN s.quantite_actuelle <= s.seuil_alerte THEN 0 ELSE 1 END, s.quantite_actuelle ASC";

try{
    $stmt= $pdo->prepare($query_string);
    $stmt->execute($params);
    $stintrants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
}catch (PDOException $e) {
    $error_message = "Erreur de récupération des stocks d'intrants : " . $e->getMessage();
}

// Récupération du terme de recherche pour l'afficher dans l'input
$current_search_term = $_GET['recherche'] ?? '';

?>

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Résultats de Recherche | Stocks Intrants | MonAgriCoach</title> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- PALETTE HARMONISÉE (Identique à liste_st_intrant.php) --- */
        :root {
            --color-primary-emerald: #06bdbd;
            --color-primary-dark: #0cb4b4;
            
            --color-secondary-gold: #FF8C00; 
            --color-secondary-gold-hover: #E37D00;

            --color-accent-danger: #ef4444; 
            --color-accent-warning: #F59E0B;
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

        /* --- BARRE LATÉRALE (SIDEBAR) --- */
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
            display: flex;
            align-items: center;
            justify-content: center;
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
        }

        .sidebar li a:hover {
            background-color: #f5f8f8;
            color: var(--color-primary-dark);
        }

        .sidebar li a[href="liste_st_intrant.php"],
        .sidebar li a[href="recherche_st_intrant.php"] {
            background-color: rgba(6, 189, 189, 0.1); 
            color: var(--color-primary-emerald); 
            font-weight: 600;
            border-left: 5px solid var(--color-primary-emerald); 
        }

        .btn-deconnexion-wrapper {
            margin-top: auto; 
            padding: 25px; 
        }
        
        .btn_deconnexion {
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
            width: 100%;
            box-sizing: border-box;
        }

        /* --- CONTENU PRINCIPAL --- */
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
        }
        
        .add-new-link {
            background-color: var(--color-primary-emerald); 
            color: var(--color-card-bg); 
            padding: 12px 25px; 
            border-radius: 8px; 
            font-weight: 600; 
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .content-container {
            background: var(--color-card-bg);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
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
            background-color: var(--color-card-bg); 
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .intrant-card {
            background: var(--color-card-bg);
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .alert-indicator {
            position: absolute;
            top: 0;
            right: 0;
            padding: 8px 15px;
            border-radius: 0 12px 0 12px;
            font-weight: 700;
            font-size: 13px;
        }
        
        .status-ok { background-color: rgba(6, 189, 189, 0.1); color: var(--color-primary-emerald); }
        .status-low { background-color: rgba(245, 158, 11, 0.15); color: var(--color-accent-warning); }
    </style>
</head>

<body> 
    <nav class="sidebar">
      <a href="index.php" class="logo"><i class="fas fa-leaf"></i> MonAgriCoach</a>
        <ul>
            <li><a href="farmer_dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a></li>
            <li> <a href="liste_parcelle.php"><i class="fas fa-map-marker-alt"></i> Parcelles</a></li> 
            <li> <a href="liste_plantation.php"><i class="fas fa-seedling"></i> plantations</a></li>
            <li><a href="liste_culture.php"><i class="fas fa-leaf"></i>  Cultures</a></li>
            <li><a href="liste_intrant.php"><i class="fas fa-flask"></i> Engrais</a></li>
            <li> <a href="liste_recom.php"><i class="fas fa-lightbulb"></i> Recommandations</a></li>
            <li> <a href="liste_message_agri.php"><i class="fas fa-comments"></i> Messagerie</a></li>
            <li><a href="liste_appli_intrant.php"><i class="fas fa-cogs"></i>Verser l'engrais</a></li>
            <li> <a href="liste_st_intrant.php"><i class="fas fa-warehouse"></i> **stock engrais**</a></li>
            <li><a href="liste_re.php"><i class="fas fa-chart-bar"></i> Rendement</a></li>
            <li><a href="liste_taches.php"><i class="fas fa-tasks"></i> Tâches</a></li>
        </ul>
        <div class="btn-deconnexion-wrapper"> 
            <a href="deconnexion.php" class="btn_deconnexion"><i class="fas fa-sign-out-alt"></i> DÉCONNEXION</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Résultats de Recherche</h1>
            <a href="stock_intrant.php" class="add-new-link"><i class="fas fa-plus-circle"></i> Ajouter un Stock</a> 
        </div>

        <?php if ($error_message): ?>
            <p style="color:red;"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        
        <div class="content-container">
            <h2>Résultats pour : "<?= htmlspecialchars($current_search_term) ?>"</h2>
            
            <form action="recherche_st_intrant.php" method="GET" class="search-form">
                <input type="text" name="recherche" placeholder="Recherche par nom d'intrant..." value="<?= htmlspecialchars($current_search_term) ?>">
                <button type="submit" style="background:var(--color-primary-emerald); color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Rechercher</button>
                <button type="button" onclick="window.location.href='liste_st_intrant.php'" style="background:#D1D5DB; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Réinitialiser</button>
            </form>

            <?php if (count($stintrants) > 0): ?>
                <div class="cards-grid">
                    <?php foreach($stintrants as $stintrant ): 
                        $is_low_stock = ($stintrant['quantite_actuelle'] <= $stintrant['seuil_alerte']);
                        $status_class = $is_low_stock ? 'status-low' : 'status-ok';
                    ?>
                        <div class="intrant-card <?= $status_class; ?>">
                            <div class="alert-indicator <?= $status_class; ?>">
                                <i class="fas <?= $is_low_stock ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i> <?= $is_low_stock ? 'Alerte Stock' : 'Stock OK'; ?>
                            </div>
                            <h3><?= htmlspecialchars($stintrant['nom_intrant']); ?></h3>
                            <p>Quantité : <strong><?= number_format($stintrant['quantite_actuelle'], 2, ',', ' '); ?></strong></p>
                            <p>Seuil : <?= number_format($stintrant['seuil_alerte'], 2, ',', ' '); ?></p>
                            <p><small>Dernière mise à jour : <?= date('d/m/Y', strtotime($stintrant['date_derniere_mise_a_jour'])); ?></small></p>
                            
                            <div style="margin-top:15px; display:flex; gap:10px;">
                                <a href="modifier_st_intrant.php?id=<?= $stintrant['id']; ?>" style="color:var(--color-primary-emerald); text-decoration:none; font-weight:bold;">Modifier</a>
                                <form action="supprime_st_intrant.php" method="POST" onsubmit="return confirm('Supprimer ce stock ?');">
                                    <input type="hidden" name="id" value="<?= $stintrant['id']; ?>">
                                    <button type="submit" style="color:var(--color-accent-danger); border:none; background:none; cursor:pointer; font-weight:bold;">Supprimer</button>
                                </form>
                            </div>
                        </div> 
                    <?php endforeach;?>
                </div>
            <?php else:?> 
                <p><i class="fas fa-info-circle"></i> Aucun stock trouvé pour votre recherche.</p> 
            <?php endif;?>
        </div> 
    </div>
</body>
</html>