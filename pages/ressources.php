<?php
session_start();
include_once 'includes/config.php';
include_once 'includes/notification_functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

$stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['statut'] === 'suspendu') {
    $_SESSION['error'] = "Votre compte a été suspendu. Veuillez contacter l'administrateur.";
    header("Location: dashboard.php");
    exit();
}

$is_admin = ($user['role'] === 'admin');
$is_formateur = ($user['role'] === 'formateur');
$can_manage_all = ($is_admin || $is_formateur);

$unread_count = getUnreadNotificationsCount($conn, $user_id);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_resource'])) {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $id_module = !empty($_POST['module']) ? $_POST['module'] : null;
    $id_filiere = !empty($_POST['filiere']) ? $_POST['filiere'] : null;
    $annee = !empty($_POST['annee']) ? $_POST['annee'] : null;
    
    $file_path = '';
    if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar'];
        $filename = $_FILES['resource_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_dir = '../assets/uploads/resources/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $upload_path)) {
                $file_path = 'assets/uploads/resources/' . $new_filename;
            } else {
                $error = "Erreur lors du téléchargement du fichier.";
            }
        } else {
            $error = "Type de fichier non autorisé. Types acceptés : PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, RAR";
        }
    } else {
        $error = "Veuillez sélectionner un fichier.";
    }
    
    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO ressource (titre, description, fichier, type, date_ajoute, id_utilisateur, id_module, id_filiere, annee, statut) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error = "Erreur de préparation de la requête: " . $conn->error;
        } else {
            $file_type = strtolower(pathinfo($_FILES['resource_file']['name'], PATHINFO_EXTENSION));
            $statut = $can_manage_all ? 'approuve' : 'en_attente';
            if (!$stmt->bind_param("ssssiiiss", $titre, $description, $file_path, $file_type, $_SESSION['user_id'], $id_module, $id_filiere, $annee, $statut)) {
                $error = "Erreur de liaison des paramètres: " . $stmt->error;
            } else {
                if ($stmt->execute()) {
                    $message = $can_manage_all ? "Ressource publiée avec succès!" : "Ressource soumise avec succès! Elle sera visible après validation par un formateur ou un administrateur.";
                } else {
                    $error = "Erreur lors de la publication de la ressource: " . $stmt->error;
                }
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_resource'])) {
    $resource_id = (int)$_POST['resource_id'];
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $id_module = !empty($_POST['module']) ? $_POST['module'] : null;
    $id_filiere = !empty($_POST['filiere']) ? $_POST['filiere'] : null;
    $annee = !empty($_POST['annee']) ? $_POST['annee'] : null;
    
    $stmt = $conn->prepare("SELECT id_utilisateur FROM ressource WHERE id_ressource = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    
    if ($can_manage_all || $resource['id_utilisateur'] == $user_id) {
        $stmt = $conn->prepare("UPDATE ressource SET titre = ?, description = ?, id_module = ?, id_filiere = ?, annee = ? WHERE id_ressource = ?");
        if (!$stmt) {
            $error = "Erreur de préparation de la requête: " . $conn->error;
        } else {
            if (!$stmt->bind_param("ssiisi", $titre, $description, $id_module, $id_filiere, $annee, $resource_id)) {
                $error = "Erreur de liaison des paramètres: " . $stmt->error;
            } else {
                if ($stmt->execute()) {
                    $message = "Ressource modifiée avec succès!";
                } else {
                    $error = "Erreur lors de la modification de la ressource: " . $stmt->error;
                }
            }
        }
    } else {
        $error = "Vous n'avez pas les droits pour modifier cette ressource.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_resource'])) {
    $resource_id = (int)$_POST['resource_id'];
    
    $stmt = $conn->prepare("SELECT id_utilisateur, fichier, statut FROM ressource WHERE id_ressource = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    
    if ($resource['statut'] === 'en_attente') {
        $error = "Vous ne pouvez pas supprimer une ressource en attente de validation. Veuillez d'abord la valider ou la rejeter.";
    } else if ($can_manage_all || $resource['id_utilisateur'] == $user_id) {
        if (!empty($resource['fichier'])) {
            $file_path = '../' . $resource['fichier'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM ressource WHERE id_ressource = ?");
        $stmt->bind_param("i", $resource_id);
        
        if ($stmt->execute()) {
            $message = "Ressource supprimée avec succès!";
        } else {
            $error = "Erreur lors de la suppression de la ressource.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour supprimer cette ressource.";
    }
}

if (isset($_GET['download']) && !empty($_GET['download'])) {
    $resource_id = (int)$_GET['download'];
    
    $stmt = $conn->prepare("SELECT fichier FROM ressource WHERE id_ressource = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    
    if ($resource) {
        $file_path = '../' . $resource['fichier'];
        if (file_exists($file_path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    if (!isset($_POST['ressource_id']) || empty($_POST['ressource_id'])) {
        $error = "ID de ressource manquant.";
    } else {
        $ressource_id = (int)$_POST['ressource_id'];
        $comment = trim($_POST['comment']);
        
        if (!empty($comment)) {
            $statut = ($is_admin || $is_formateur) ? 'approuve' : 'en_attente';
            
            $stmt = $conn->prepare("INSERT INTO commentaire_ressource (id_ressource, id_utilisateur, contenu, date_commentaire, statut) VALUES (?, ?, ?, NOW(), ?)");
            if (!$stmt) {
                $error = "Erreur de préparation de la requête: " . $conn->error;
            } else {
                $stmt->bind_param("iiss", $ressource_id, $user_id, $comment, $statut);
                
                if ($stmt->execute()) {
                    $stmt = $conn->prepare("SELECT id_utilisateur FROM ressource WHERE id_ressource = ?");
                    $stmt->bind_param("i", $ressource_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $ressource = $result->fetch_assoc();
                    
                    if ($ressource && $ressource['id_utilisateur'] != $user_id) {
                        $message = "a commenté votre ressource";
                        addNotification($conn, $ressource['id_utilisateur'], 'resource_comment', $ressource_id, $message);
                    }
                    
                    $message = ($is_admin || $is_formateur) ? "Commentaire ajouté avec succès!" : "Commentaire soumis avec succès! Il sera visible après validation.";
                } else {
                    $error = "Erreur lors de l'ajout du commentaire: " . $stmt->error;
                }
            }
        } else {
            $error = "Le commentaire ne peut pas être vide.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['validate_comment'])) {
    $comment_id = (int)$_POST['comment_id'];
    
    if ($can_manage_all) {
        $stmt = $conn->prepare("UPDATE commentaire_ressource SET statut = ? WHERE id_commentaire = ?");
        $stmt->bind_param("si", $action, $comment_id);
        
        if ($stmt->execute()) {
            $message = $action === 'approuve' ? "Commentaire approuvé avec succès!" : "Commentaire rejeté avec succès!";
        } else {
            $error = "Erreur lors de la modification du statut du commentaire.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour effectuer cette action.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_comment'])) {
    $comment_id = (int)$_POST['comment_id'];
    
    $stmt = $conn->prepare("SELECT id_utilisateur FROM commentaire_ressource WHERE id_commentaire = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    
    if ($can_manage_all || $comment['id_utilisateur'] == $user_id) {
        $stmt = $conn->prepare("DELETE FROM commentaire_ressource WHERE id_commentaire = ?");
        $stmt->bind_param("i", $comment_id);
        
        if ($stmt->execute()) {
            $message = "Commentaire supprimé avec succès!";
        } else {
            $error = "Erreur lors de la suppression du commentaire.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour supprimer ce commentaire.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_like'])) {
    $resource_id = (int)$_POST['resource_id'];
    
    $stmt = $conn->prepare("SELECT id_like FROM likes_ressource WHERE id_ressource = ? AND id_utilisateur = ?");
    $stmt->bind_param("ii", $resource_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM likes_ressource WHERE id_ressource = ? AND id_utilisateur = ?");
        $stmt->bind_param("ii", $resource_id, $user_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO likes_ressource (id_ressource, id_utilisateur, date_like) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $resource_id, $user_id);
        $stmt->execute();
    }
    
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$filieres = [];
$result = $conn->query("SELECT * FROM filiere ORDER BY nom");
while ($row = $result->fetch_assoc()) {
    $filieres[] = $row;
}

$modules = [];
$result = $conn->query("SELECT * FROM module ORDER BY nom");
while ($row = $result->fetch_assoc()) {
    $modules[] = $row;
}

$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['filiere']) && !empty($_GET['filiere'])) {
    $where_conditions[] = "r.id_filiere = ?";
    $params[] = (int)$_GET['filiere'];
    $types .= "i";
}

if (isset($_GET['module']) && !empty($_GET['module'])) {
    $where_conditions[] = "r.id_module = ?";
    $params[] = (int)$_GET['module'];
    $types .= "i";
}

if (isset($_GET['annee']) && !empty($_GET['annee'])) {
    $where_conditions[] = "r.annee = ?";
    $params[] = $_GET['annee'];
    $types .= "s";
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_conditions[] = "r.type = ?";
    $params[] = $_GET['type'];
    $types .= "s";
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(r.titre LIKE ? OR r.description LIKE ?)";
    $search = "%" . trim($_GET['search']) . "%";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

$sql = "SELECT r.*, 
               u.nom, u.prenom, u.photo_profil, u.numero_whatsapp, u.role,
               m.nom as module_nom,
               f.nom as filiere_nom,
               f.id_filiere as filiere_id,
               COALESCE(r.telechargements, 0) as telechargements
        FROM ressource r 
        INNER JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur 
        LEFT JOIN module m ON r.id_module = m.id_module
        LEFT JOIN filiere f ON r.id_filiere = f.id_filiere
        WHERE r.statut = 'approuve'";

if (!$can_manage_all) {
    $where_conditions[] = "r.statut = 'approuve'";
}

if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}

$sql .= " GROUP BY r.id_ressource ORDER BY r.date_ajoute DESC";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête: " . $conn->error);
    }

    if (!empty($params)) {
        if (!$stmt->bind_param($types, ...$params)) {
            throw new Exception("Erreur de liaison des paramètres: " . $stmt->error);
        }
    }

    if (!$stmt->execute()) {
        throw new Exception("Erreur d'exécution de la requête: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Erreur lors de la récupération des résultats: " . $stmt->error);
    }

    $resources = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Une erreur est survenue lors de la récupération des ressources: " . $e->getMessage();
    error_log("Erreur SQL dans ressources.php: " . $e->getMessage());
    $resources = [];
}

error_log("Nombre de ressources trouvées : " . count($resources));
foreach ($resources as $resource) {
    error_log("Ressource ID: " . $resource['id_ressource'] . 
              ", Module: " . $resource['module_nom'] . 
              ", Filière: " . $resource['filiere_nom']);
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Ressources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="./../assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Andika:wght@400;700&display=swap");

      body {
        font-family: "Andika", sans-serif;
        background-color: #f4f6f9;
        min-height: 100vh;
        margin: 0;
        padding-top: 56px;
      }

      .navbar {
        background-color: #ffffff !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        z-index: 1030;
      }

      .navbar-brand {
        font-weight: bold;
        color: #343a40;
      }

      .navbar-toggler {
        border: none;
        font-size: 1.2rem;
      }

      .sidebar {
        background-color: #343a40;
        color: #ffffff;
        width: 250px;
        height: 100%;
        position: fixed;
        top: 56px;
        left: 0;
        z-index: 1020;
        transition: transform 0.3s ease;
      }

      .sidebar .nav-link {
        padding: 0.8rem 1.5rem;
        color: #ffffff;
        display: flex;
        align-items: center;
        text-decoration: none;
      }

      .sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
      }

      #active {
        background-color: rgba(255, 255, 255, 0.1);
      }

      .sidebar .nav-link i {
        margin-right: 0.75rem;
      }

      .main-content {
        margin-left: 250px;
        padding: 1.5rem;
        transition: margin-left 0.3s ease;
      }

      .overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1010;
      }

      @media (max-width: 767.98px) {
        .sidebar {
          transform: translateX(-100%);
        }

        .sidebar.show {
          transform: translateX(0);
        }

        .main-content {
          margin-left: 0;
        }

        .overlay.show {
          display: block;
        }
      }

      .resource-card {
        background: #ffffff;
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
      }

      .resource-card .card-body {
        padding: 0;
      }

      .resource-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
      }

      .resource-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 1rem;
        object-fit: cover;
      }

      .resource-info {
        flex: 1;
      }

      .resource-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 1rem;
      }

      .resource-meta {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
      }

      .resource-meta-item {
        display: flex;
        align-items: center;
        color: #6c757d;
        font-size: 0.875rem;
      }

      .resource-meta-item i {
        margin-right: 0.5rem;
      }

      .resource-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
      }

      .page-header {
        margin-bottom: 2rem;
      }

      .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 0.5rem;
      }

      .page-subtitle {
        color: #6c757d;
        font-size: 1rem;
      }

      .filter-section {
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      }

      .file-icon {
        font-size: 2rem;
        margin-right: 1rem;
      }

      .file-info {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
      }

      .file-name {
        font-weight: 500;
        color: #343a40;
      }

      .file-size {
        color: #6c757d;
        font-size: 0.875rem;
      }
    </style>
  </head>
  <body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="./../index.php">
          <img src="./../assets/images/logo.png" width="30px" alt="" />
          ISMOShare
        </a>
        <button class="navbar-toggler text-dark d-md-none" type="button" id="sidebarToggle" style="outline: none; box-shadow: none">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </nav>

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
      <ul class="nav flex-column">
        <br />
        <li class="nav-item">
          <a class="nav-link" href="./dashboard.php">
            <i class="fa-solid fa-chart-line"></i>
            Tableau de bord
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./forum.php">
            <i class="fas fa-comments"></i>
            Forum
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="active" href="./ressources.php">
            <i class="fas fa-folder-open"></i>
            Resources
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./annonces.php">
            <i class="fa-solid fa-bullhorn"></i>
            Annonces
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./notifications.php">
            <i class="fas fa-bell"></i>
            Notifications
            <?php if ($unread_count > 0): ?>
              <span class="badge bg-danger ms-2"><?= $unread_count ?></span>
            <?php endif; ?>
          </a>
        </li>

        <hr class="text-white" />

        <li class="nav-item">
          <a class="nav-link" href="./profile.php">
            <i class="fas fa-user"></i>
            Mon Profil
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-light" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </a>
        </li>
      </ul>
    </nav>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    <!-- Main Content -->
    <main class="main-content">
      <div class="container-fluid mt-2">
        <?php if ($message): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
          <div>
            <h1 class="page-title">Partagez et accédez aux ressources pédagogiques</h1>
          </div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadResourceModal">
            <i class="fas fa-upload"></i>
          </button>
        </div>

        <!-- Filters -->
        <div class="filter-section">
          <form method="GET" class="row g-3">
            <div class="col-md-4">
              <label for="search" class="form-label">Rechercher</label>
              <input type="text" class="form-control" id="search" name="search" 
                     value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
                     placeholder="Rechercher une ressource...">
                    </div>
            <div class="col-md-4">
              <label for="filiere" class="form-label">Filière</label>
              <select class="form-select" id="filiere" name="filiere">
                <option value="">Toutes les filières</option>
                <?php foreach ($filieres as $filiere): ?>
                  <option value="<?= $filiere['id_filiere'] ?>" 
                          <?= isset($_GET['filiere']) && $_GET['filiere'] == $filiere['id_filiere'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($filiere['nom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
                  </div>
            <div class="col-md-4">
              <label for="module" class="form-label">Module</label>
              <select class="form-select" id="module" name="module">
                <option value="">Tous les modules</option>
                <?php foreach ($modules as $module): ?>
                  <option value="<?= $module['id_module'] ?>"
                          <?= isset($_GET['module']) && $_GET['module'] == $module['id_module'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($module['nom']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
                </div>
            <div class="col-md-4">
              <label for="annee" class="form-label">Année</label>
              <select class="form-select" id="annee" name="annee">
                <option value="">Toutes les années</option>
                <option value="1ere annee" <?= isset($_GET['annee']) && $_GET['annee'] == '1ere annee' ? 'selected' : '' ?>>1ère année</option>
                <option value="2eme annee" <?= isset($_GET['annee']) && $_GET['annee'] == '2eme annee' ? 'selected' : '' ?>>2ème année</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="type" class="form-label">Type de fichier</label>
              <select class="form-select" id="type" name="type">
                <option value="">Tous les types</option>
                <option value="pdf" <?= isset($_GET['type']) && $_GET['type'] == 'pdf' ? 'selected' : '' ?>>PDF</option>
                <option value="doc" <?= isset($_GET['type']) && $_GET['type'] == 'doc' ? 'selected' : '' ?>>DOC</option>
                <option value="docx" <?= isset($_GET['type']) && $_GET['type'] == 'docx' ? 'selected' : '' ?>>DOCX</option>
                <option value="ppt" <?= isset($_GET['type']) && $_GET['type'] == 'ppt' ? 'selected' : '' ?>>PPT</option>
                <option value="pptx" <?= isset($_GET['type']) && $_GET['type'] == 'pptx' ? 'selected' : '' ?>>PPTX</option>
                <option value="xls" <?= isset($_GET['type']) && $_GET['type'] == 'xls' ? 'selected' : '' ?>>XLS</option>
                <option value="xlsx" <?= isset($_GET['type']) && $_GET['type'] == 'xlsx' ? 'selected' : '' ?>>XLSX</option>
                <option value="zip" <?= isset($_GET['type']) && $_GET['type'] == 'zip' ? 'selected' : '' ?>>ZIP</option>
                <option value="rar" <?= isset($_GET['type']) && $_GET['type'] == 'rar' ? 'selected' : '' ?>>RAR</option>
              </select>
            </div>
            <div class="col-12 text-end">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter me-2"></i>Filtrer
                  </button>
              <a href="ressources.php" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Réinitialiser
              </a>
                </div>
          </form>
              </div>

        <!-- Resources List -->
        <div class="row">
          <?php if (empty($resources)): ?>
            <div class="col-12">
              <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Aucune ressource trouvée</h4>
                <p class="text-muted">Soyez le premier à partager une ressource !</p>
            </div>
          </div>
          <?php else: ?>
            <?php foreach ($resources as $resource): ?>
              <div class="col-md-6">
                <div class="resource-card">
                  <div class="resource-header">
                    <img src="<?= $resource['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($resource['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>" 
                         alt="Photo de profil" 
                         class="resource-avatar"
                         style="cursor: pointer;"
                         onclick="showUserInfo(<?= htmlspecialchars(json_encode([
                             'id' => $resource['id_utilisateur'],
                             'nom' => $resource['nom'],
                             'prenom' => $resource['prenom'],
                             'photo' => $resource['photo_profil'],
                             'role' => $resource['role'],
                             'whatsapp' => $resource['numero_whatsapp'] ? '0' . $resource['numero_whatsapp'] : '0'
                         ])) ?>)">
                    <div class="resource-info">
                        <h6 class="mb-0"><?= htmlspecialchars($resource['prenom'] . ' ' . $resource['nom']) ?></h6>
                        <small class="text-muted"><?= date('d/m/Y', strtotime($resource['date_ajoute'])) ?></small>
        </div>
      </div>

                  
                  <div class="file-info">
                    <?php
                    $file_ext = pathinfo($resource['fichier'], PATHINFO_EXTENSION);
                    $icon_class = 'fa-file';
                    switch (strtolower($file_ext)) {
                        case 'pdf':
                            $icon_class = 'fa-file-pdf';
                            break;
                        case 'doc':
                        case 'docx':
                            $icon_class = 'fa-file-word';
                            break;
                        case 'xls':
                        case 'xlsx':
                            $icon_class = 'fa-file-excel';
                            break;
                        case 'ppt':
                        case 'pptx':
                            $icon_class = 'fa-file-powerpoint';
                            break;
                        case 'zip':
                        case 'rar':
                            $icon_class = 'fa-file-archive';
                            break;
                    }
                    ?>
                    <i class="fas <?= $icon_class ?> file-icon text-primary"></i>
                    <div>
                      <div class="file-name"><?= basename($resource['fichier']) ?></div>
                      <div class="file-size">
                        <?php
                        $file_path = '../' . $resource['fichier'];
                        if (file_exists($file_path)) {
                            $size = filesize($file_path);
                            if ($size < 1024) {
                                echo $size . ' B';
                            } elseif ($size < 1048576) {
                                echo round($size / 1024, 2) . ' KB';
                            } else {
                                echo round($size / 1048576, 2) . ' MB';
                            }
                        }
                        ?>
          </div>
              </div>
              </div>

                  <h5 class="resource-title"><?= htmlspecialchars($resource['titre']) ?></h5>
                  <p class="text-muted"><?= htmlspecialchars($resource['description']) ?></p>

                  <div class="resource-meta">
                    <?php if ($resource['module_nom']): ?>
                        <div class="resource-meta-item">
                            <i class="fas fa-book me-2"></i>
                            <?= htmlspecialchars($resource['module_nom']) ?>
                            <?php if ($resource['filiere_nom']): ?>
                                <div class="ms-4 mt-1">
                                    <i class="fas fa-graduation-cap me-2"></i>
                                    <?= htmlspecialchars($resource['filiere_nom']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($resource['annee']): ?>
                                <div class="ms-4 mt-1">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    <?= htmlspecialchars($resource['annee']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                  </div>

                  <div class="resource-actions">
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="resource_id" value="<?= $resource['id_ressource'] ?>">
                      <?php
                      // Vérifier si l'utilisateur a liké cette ressource
                      $stmt = $conn->prepare("SELECT id_like FROM likes_ressource WHERE id_ressource = ? AND id_utilisateur = ?");
                      $stmt->bind_param("ii", $resource['id_ressource'], $user_id);
                      $stmt->execute();
                      $has_liked = $stmt->get_result()->num_rows > 0;
                      
                      // Compter le nombre total de likes
                      $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes_ressource WHERE id_ressource = ?");
                      $stmt->bind_param("i", $resource['id_ressource']);
                      $stmt->execute();
                      $likes_count = $stmt->get_result()->fetch_assoc()['count'];
                      ?>
                      <button type="submit" name="toggle_like" class="btn <?= $has_liked ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm">
                        <i class="fas fa-heart me-2"></i>J'aime
                        <?php if ($likes_count > 0): ?>
                          <span>(<?= $likes_count ?>)</span>
                        <?php endif; ?>
                      </button>
                    </form>
                    <a href="?download=<?= $resource['id_ressource'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-download me-2"></i>Télécharger
                        <?php if ($resource['telechargements'] > 0): ?>
                            <span>(<?= $resource['telechargements'] ?>)</span>
                        <?php endif; ?>
                    </a>
                    <button class="btn btn-info btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#commentsModal<?= $resource['id_ressource'] ?>">
                        <i class="fas fa-comments me-2"></i>Commentaires
                        <?php
                        // Compter le nombre de commentaires pour cette ressource
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM commentaire_ressource WHERE id_ressource = ?");
                        $stmt->bind_param("i", $resource['id_ressource']);
                        $stmt->execute();
                        $comment_count = $stmt->get_result()->fetch_assoc()['count'];
                        if ($comment_count > 0): ?>
                            <span>(<?= $comment_count ?>)</span>
                        <?php endif; ?>
                    </button>
                    <?php if ($can_manage_all || $resource['id_utilisateur'] == $user_id): ?>
                        <button class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editResourceModal<?= $resource['id_ressource'] ?>">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </button>
                        <button class="btn btn-danger btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteResourceModal<?= $resource['id_ressource'] ?>">
                            <i class="fas fa-trash-alt me-2"></i>Supprimer
                        </button>
                    <?php endif; ?>
                  </div>

                  <!-- Comments Modal -->
                  <div class="modal fade" id="commentsModal<?= $resource['id_ressource'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                      <div class="modal-content">
                        <div class="modal-header bg-info text-dark">
                          <h5 class="modal-title">
                            <i class="fas fa-comments me-2"></i>Commentaires
                          </h5>
                          <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <!-- Liste des commentaires -->
                          <div class="comments-list mb-4">
                            <?php
                            $stmt = $conn->prepare("
                              SELECT c.*, u.nom, u.prenom, u.photo_profil, u.role, u.numero_whatsapp
                              FROM commentaire_ressource c
                              INNER JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
                              WHERE c.id_ressource = ? AND (c.statut = 'approuve' OR c.id_utilisateur = ? OR ? = 1)
                              ORDER BY c.date_commentaire DESC
                            ");
                            $can_see_all = ($is_admin || $is_formateur) ? 1 : 0;
                            $stmt->bind_param("iii", $resource['id_ressource'], $user_id, $can_see_all);
                            $stmt->execute();
                            $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            
                            if (empty($comments)): ?>
                              <div class="text-center text-muted py-4">
                                <i class="fas fa-comments fa-3x mb-3"></i>
                                <p>Aucun commentaire pour le moment</p>
                              </div>
                            <?php else:
                              foreach ($comments as $comment): ?>
                                <div class="comment-item mb-3 pb-3 border-bottom">
                                  <div class="d-flex align-items-start">
                                    <img src="<?= $comment['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($comment['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>" 
                                         alt="Photo de profil" 
                                         class="rounded-circle me-3"
                                         style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                         onclick="showUserInfo(<?= htmlspecialchars(json_encode([
                                             'id' => $comment['id_utilisateur'],
                                             'nom' => $comment['nom'],
                                             'prenom' => $comment['prenom'],
                                             'photo' => $comment['photo_profil'],
                                             'role' => $comment['role'],
                                             'whatsapp' => $comment['numero_whatsapp'] ? '0' . $comment['numero_whatsapp'] : '0'
                                         ])) ?>)">
                                    <div class="flex-grow-1">
                                      <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                          <h6 class="mb-0"><?= htmlspecialchars($comment['prenom'] . ' ' . $comment['nom']) ?></h6>
                                          <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($comment['date_commentaire'])) ?>
                                            <?php if ($comment['statut'] === 'en_attente'): ?>
                                                <span class="badge bg-warning">En attente</span>
                                            <?php elseif ($comment['statut'] === 'rejete'): ?>
                                                <span class="badge bg-danger">Rejeté</span>
                                            <?php endif; ?>
                                          </small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($can_manage_all && $comment['statut'] === 'en_attente'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id_commentaire'] ?>">
                                                    <input type="hidden" name="action" value="approuve">
                                                    <button type="submit" name="validate_comment" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id_commentaire'] ?>">
                                                    <input type="hidden" name="action" value="rejete">
                                                    <button type="submit" name="validate_comment" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($can_manage_all || $comment['id_utilisateur'] == $user_id): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id_commentaire'] ?>">
                                                    <button type="submit" name="delete_comment" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                      </div>
                                      <p class="mb-0"><?= nl2br(htmlspecialchars($comment['contenu'])) ?></p>
                                    </div>
                                  </div>
                                </div>
                              <?php endforeach;
                            endif; ?>
                          </div>
                          
                          <!-- Formulaire d'ajout de commentaire -->
                          <form method="POST" class="mt-4">
                            <input type="hidden" name="ressource_id" value="<?= $resource['id_ressource'] ?>">
                            <div class="mb-3">
                              <textarea class="form-control" name="comment" rows="3" placeholder="Ajouter un commentaire..." required></textarea>
                            </div>
                            <div class="text-end">
                              <button type="submit" name="add_comment" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Publier
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Edit Resource Modal -->
                  <?php if ($can_manage_all || $resource['id_utilisateur'] == $user_id): ?>
                    <div class="modal fade" id="editResourceModal<?= $resource['id_ressource'] ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">
                              <i class="fas fa-edit me-2"></i>Modifier la ressource
                            </h5>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <form method="POST">
                              <input type="hidden" name="resource_id" value="<?= $resource['id_ressource'] ?>">
                              <div class="mb-3">
                                <label for="titre" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="titre" name="titre" 
                                       value="<?= htmlspecialchars($resource['titre']) ?>" required>
                              </div>
                              <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" required><?= htmlspecialchars($resource['description']) ?></textarea>
                              </div>
                              <div class="mb-3">
                                <label for="filiere" class="form-label">Filière</label>
                                <select class="form-select" id="filiere" name="filiere">
                                  <option value="">Sélectionnez une filière</option>
                                  <?php foreach ($filieres as $filiere): ?>
                                    <option value="<?= $filiere['id_filiere'] ?>"
                                            <?= isset($resource['filiere_id']) && $resource['filiere_id'] == $filiere['id_filiere'] ? 'selected' : '' ?>>
                                      <?= htmlspecialchars($filiere['nom']) ?>
                                    </option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="mb-3">
                                <label for="module" class="form-label">Module</label>
                                <select class="form-select" id="module" name="module">
                                  <option value="">Sélectionnez un module</option>
                                  <?php foreach ($modules as $module): ?>
                                    <option value="<?= $module['id_module'] ?>"
                                            <?= $resource['id_module'] == $module['id_module'] ? 'selected' : '' ?>>
                                      <?= htmlspecialchars($module['nom']) ?>
                                    </option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="mb-3">
                                <label for="annee" class="form-label">Année</label>
                                <select class="form-select" id="annee" name="annee" required>
                                  <option value="">Sélectionnez une année</option>
                                  <option value="1ere annee" <?= $resource['annee'] == '1ere annee' ? 'selected' : '' ?>>1ère année</option>
                                  <option value="2eme annee" <?= $resource['annee'] == '2eme annee' ? 'selected' : '' ?>>2ème année</option>
                                </select>
                              </div>
                              <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" name="edit_resource" class="btn btn-warning">
                                  <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Delete Resource Modal -->
                    <div class="modal fade" id="deleteResourceModal<?= $resource['id_ressource'] ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                              <i class="fas fa-trash-alt me-2"></i>Confirmer la suppression
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <p>Êtes-vous sûr de vouloir supprimer cette ressource ? Cette action est irréversible.</p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <form method="POST" class="d-inline">
                              <input type="hidden" name="resource_id" value="<?= $resource['id_ressource'] ?>">
                              <button type="submit" name="delete_resource" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
                  </div>
                  </div>
    </main>

    <!-- Upload Resource Modal -->
    <div class="modal fade" id="uploadResourceModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-upload me-2"></i>Publier une ressource
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
          <div class="modal-body">
            <form method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="titre" class="form-label">Titre</label>
                <input type="text" class="form-control" id="titre" name="titre" required>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                  </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="filiere" class="form-label">Filière</label>
                  <select class="form-select" id="filiere" name="filiere" required>
                    <option value="">Sélectionnez une filière</option>
                    <?php foreach ($filieres as $filiere): ?>
                      <option value="<?= $filiere['id_filiere'] ?>">
                        <?= htmlspecialchars($filiere['nom']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  </div>

                <div class="col-md-6 mb-3">
                  <label for="module" class="form-label">Module</label>
                  <select class="form-select" id="module" name="module" required>
                    <option value="">Sélectionnez un module</option>
                    <?php foreach ($modules as $module): ?>
                      <option value="<?= $module['id_module'] ?>">
                        <?= htmlspecialchars($module['nom']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label for="annee" class="form-label">Année</label>
                <select class="form-select" id="annee" name="annee" required>
                  <option value="">Sélectionnez une année</option>
                  <option value="1ere annee">1ère année</option>
                  <option value="2eme annee">2ème année</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="resource_file" class="form-label">Fichier</label>
                <input type="file" class="form-control" id="resource_file" name="resource_file" required>
                <div class="form-text">
                  Types de fichiers acceptés : PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, RAR
              </div>
            </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" name="upload_resource" class="btn btn-primary">
                  <i class="fas fa-upload me-2"></i>Publier
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">
              <i class="fas fa-sign-out-alt me-2"></i>Confirmer la déconnexion
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            Êtes-vous sûr de vouloir vous déconnecter ?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
          </div>
        </div>
      </div>
    </div>

    <!-- User Info Modal -->
    <div class="modal fade" id="userInfoModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>Informations de l'utilisateur
            </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
                    <div class="text-center mb-4">
                        <img id="userInfoPhoto" src="" alt="Photo de profil" 
                             class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <h4 id="userInfoName" class="mb-2"></h4>
                        <p id="userInfoRole" class="text-muted mb-3"></p>
          </div>
                    <div class="user-details">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fab fa-whatsapp text-success me-2" style="font-size: 1.5rem;"></i>
                                    <a id="userInfoWhatsapp" href="#" class="text-decoration-none" style="font-size: 1.2rem;"></a>
          </div>
        </div>
      </div>
    </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      const sidebarToggle = document.getElementById("sidebarToggle");
      const sidebar = document.getElementById("sidebar");
      const overlay = document.getElementById("overlay");

      sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("show");
        overlay.classList.toggle("show");
      });

      overlay.addEventListener("click", () => {
        sidebar.classList.remove("show");
        overlay.classList.remove("show");
      });

      document.addEventListener('DOMContentLoaded', function() {
        const filiereSelect = document.getElementById('filiere');
        const moduleSelect = document.getElementById('module');
        
        // Sauvegarder tous les modules au chargement
        const allModules = Array.from(moduleSelect.options);
        
        // Quand la filière change, on garde tous les modules
        filiereSelect.addEventListener('change', function() {
            // On garde l'option par défaut
            moduleSelect.innerHTML = '<option value="">Sélectionnez un module</option>';
            
            // On ajoute tous les modules sauf l'option par défaut
            allModules.forEach(option => {
                if (option.value !== "") {
                    moduleSelect.appendChild(option.cloneNode(true));
                }
            });
        });

        // Mettre à jour le compteur de téléchargements
        document.querySelectorAll('a[href^="?download="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const resourceId = this.getAttribute('href').split('=')[1];
                const downloadBtn = this;
                const downloadCount = downloadBtn.querySelector('span');
                
                // Mettre à jour le compteur
                fetch('update_download_count.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'resource_id=' + resourceId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (downloadCount) {
                            downloadCount.textContent = '(' + data.count + ')';
                        } else {
                            downloadBtn.innerHTML = '<i class="fas fa-download me-2"></i>Télécharger <span>(' + data.count + ')</span>';
                        }
                        // Télécharger le fichier après la mise à jour du compteur
                        window.location.href = this.getAttribute('href');
                    }
                })
                .catch(error => console.error('Erreur:', error));
            });
        });
      });

      function showUserInfo(user) {
        const modal = new bootstrap.Modal(document.getElementById('userInfoModal'));
        const photo = document.getElementById('userInfoPhoto');
        const name = document.getElementById('userInfoName');
        const role = document.getElementById('userInfoRole');
        const whatsapp = document.getElementById('userInfoWhatsapp');
        
        // Mettre à jour les informations
        photo.src = user.photo ? '../assets/uploads/profile/' + user.photo : '../assets/images/ISMO SHARE.png';
        name.textContent = user.prenom + ' ' + user.nom;
        role.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
        
        // Mettre à jour le lien WhatsApp
        if (user.whatsapp && user.whatsapp !== '0') {
          whatsapp.href = 'https://wa.me/' + user.whatsapp.substring(1);
          whatsapp.textContent = user.whatsapp;
          whatsapp.classList.add('text-success');
          whatsapp.classList.remove('text-muted');
        } else {
          whatsapp.textContent = 'Aucun numéro WhatsApp';
          whatsapp.classList.add('text-muted');
          whatsapp.classList.remove('text-success');
          whatsapp.removeAttribute('href');
        }
        
        // Afficher le modal
        modal.show();
      }

      // Ajouter l'événement click sur toutes les photos de profil
      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.resource-avatar').forEach(avatar => {
            avatar.style.cursor = 'pointer';
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
