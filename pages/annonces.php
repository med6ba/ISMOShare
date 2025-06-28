<?php
session_start();
include_once 'includes/config.php';
include_once 'includes/notification_functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);
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
$is_stagiaire = ($user['role'] === 'stagiaire');
$can_manage_all = $is_admin;
$can_edit_own = $is_formateur;
$can_add = ($is_admin || $is_formateur);

$unread_count = getUnreadNotificationsCount($conn, $user_id);

if (isset($add_announcement)) {
    if (!$can_add) {
        $error = "Vous n'avez pas les droits pour ajouter une annonce.";
    } else {
        $titre = trim($titre);
        $contenu = trim($contenu);
        
        if (!empty($titre) && !empty($contenu) && !empty($date_expiration)) {
            
            if (strtotime($date_expiration) < strtotime(date('Y-m-d'))) {
                $error = "La date d'expiration ne peut pas être antérieure à aujourd'hui.";
            } else {
                $stmt = $conn->prepare("INSERT INTO annonce (titre, contenu, date_creation, date_expiration, id_utilisateur) VALUES (?, ?, NOW(), ?, ?)");
                $stmt->bind_param("sssi", $titre, $contenu, $date_expiration, $user_id);
                
                if ($stmt->execute()) {
                    $new_annonce_id = $conn->insert_id;
                    
                    $stmt_users = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE id_utilisateur != ? AND statut != 'suspendu'");
                    $stmt_users->bind_param("i", $user_id);
                    $stmt_users->execute();
                    $result_users = $stmt_users->get_result();

                    $notification_message = "Une nouvelle annonce a été publiée : " . $titre;
                    while ($usnotif = $result_users->fetch_assoc()) {
                        addNotification($conn, $usnotif['id_utilisateur'], 'announcement', $new_annonce_id, $notification_message);
                    }
                    
                    $message = "Annonce publiée avec succès!";
                } else {
                    $error = "Erreur lors de la publication de l'annonce.";
                }
            }
        } else {
            $error = "Veuillez remplir tous les champs.";
        }
    }
}

if (isset($edit_announcement)) {
    $annonce_id = intval($annonce_id);
    $titre = trim($titre);
    $contenu = trim($contenu);
    
    $stmt = $conn->prepare("SELECT id_utilisateur, date_creation FROM annonce WHERE id_annonce = ?");
    $stmt->bind_param("i", $annonce_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $annonce = $result->fetch_assoc();
    
    if ($can_manage_all || ($can_edit_own && $annonce['id_utilisateur'] == $user_id)) {
        if (strtotime($date_expiration) < strtotime($annonce['date_creation'])) {
            $error = "La date d'expiration ne peut pas être antérieure à la date de publication.";
        } else {
            $stmt = $conn->prepare("UPDATE annonce SET titre = ?, contenu = ?, date_expiration = ? WHERE id_annonce = ?");
            $stmt->bind_param("sssi", $titre, $contenu, $date_expiration, $annonce_id);
            
            if ($stmt->execute()) {
                $message = "Annonce modifiée avec succès!";
            } else {
                $error = "Erreur lors de la modification de l'annonce.";
            }
        }
    } else {
        $error = "Vous n'avez pas les droits pour modifier cette annonce.";
    }
}

if (isset($delete_announcement)) {
    $annonce_id = intval($annonce_id);
    
    $stmt = $conn->prepare("SELECT id_utilisateur FROM annonce WHERE id_annonce = ?");
    $stmt->bind_param("i", $annonce_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $annonce = $result->fetch_assoc();
    
    if ($can_manage_all || ($can_edit_own && $annonce['id_utilisateur'] == $user_id)) {
        $stmt = $conn->prepare("DELETE FROM annonce WHERE id_annonce = ?");
        $stmt->bind_param("i", $annonce_id);
        
        if ($stmt->execute()) {
            $message = "Annonce supprimée avec succès!";
        } else {
            $error = "Erreur lors de la suppression de l'annonce.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour supprimer cette annonce.";
    }
}

if (isset($archive_announcement)) {
    $annonce_id = intval($annonce_id);
    
    if ($can_manage_all) {
        $stmt = $conn->prepare("UPDATE annonce SET est_archive = 1 WHERE id_annonce = ?");
        $stmt->bind_param("i", $annonce_id);
        
        if ($stmt->execute()) {
            $message = "Annonce archivée avec succès!";
        } else {
            $error = "Erreur lors de l'archivage de l'annonce.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour archiver cette annonce.";
    }
}

// Récupérer les annonces
$sql = "SELECT a.*, u.nom, u.prenom, u.photo_profil, u.role, u.numero_whatsapp 
        FROM annonce a 
        INNER JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur 
        WHERE a.est_archive = 0";

$where_conditions = [];
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    array_push($where_conditions,"(a.titre LIKE ? OR a.contenu LIKE ?)");
    array_push($params, $search,$search);
    $types .= "ss";
}

if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY a.date_creation DESC";

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

    $annonces = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Une erreur est survenue lors de la récupération des annonces: " . $e->getMessage();
    error_log("Erreur SQL dans annonces.php: " . $e->getMessage());
    $annonces = [];
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Annonces</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      rel="shortcut icon"
      href="./../assets/images/logo.png"
      type="image/x-icon"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Andika:wght@400;700&display=swap");

      body {
        font-family: "Andika", sans-serif;
        background-color: #f4f6f9;
        min-height: 100vh;
        margin: 0;
        padding-top: 56px; /* height of navbar */
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
        top: 56px; /* height of navbar */
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

      /* Responsive */
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

      .widget {
        background-color: #ffffff;
        border-radius: 0.25rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
      }

      .widget-title {
        font-size: 1.25rem;
        font-weight: bold;
        margin-bottom: 1rem;
        color: #343a40;
      }

      .card {
        background: #ffffff;
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
      }

      .card .card-body {
        padding: 0;
      }

      .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
      }

      .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 1rem;
      }

      .card-meta {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
      }

      .card-meta-item {
        display: flex;
        align-items: center;
        color: #6c757d;
        font-size: 0.875rem;
      }

      .card-meta-item i {
        margin-right: 0.5rem;
      }

      .card-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
      }

      .btn-icon {
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.25rem;
      }

      .btn-icon .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
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
        <button
          class="navbar-toggler text-dark d-md-none"
          type="button"
          id="sidebarToggle"
          style="outline: none; box-shadow: none"
        >
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
          <a class="nav-link" href="./ressources.php">
            <i class="fas fa-folder-open"></i>
            Resources
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="active" href="./annonces.php">
            <i class="fa-solid fa-bullhorn"></i>
            Annonces
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./notifications.php">
            <i class="fas fa-bell"></i>
            Notifications
            <?php if ($unread_count > 0) {
              echo '<span class="badge bg-danger ms-2">' . $unread_count . '</span>';
            } ?>

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
    <main class="main-content bg-light py-4">
      <div class="container-fluid py-2">
        <?php
        if ($message) {
          echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
          . htmlspecialchars($message)
          . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
          . '</div>';
        }

        if ($error) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
              . htmlspecialchars($error)
              . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
              . '</div>';
        }
        ?>  


        <!-- Add Announcement Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="mb-0 fw-bold">
            Restez informé des dernières mises à jour
          </h3>
          <?php
          if ($can_add) {
              echo '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                      <i class="fa-solid fa-calendar-plus"></i>
                    </button>';
          }
          ?>

        </div>

        <div class="filters mb-4">
          <form method="GET" class="row g-3">
            <div class="col-12">
              <div class="input-group">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Rechercher dans les annonces..." 
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search me-2"></i>Rechercher
                </button>
                <?php
                  if (!empty($_GET['search'])) {
                      echo '<a href="annonces.php" class="btn btn-outline-secondary">
                              <i class="fas fa-times me-2"></i>Effacer les filtres
                            </a>';
                  }
                ?>

              </div>
            </div>
          </form>
        </div>

        <div class="row">
          <?php if (empty($annonces)): ?>
            <div class="col-12">
              <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Aucune annonce pour le moment</h4>
                <p class="text-muted">Les annonces apparaîtront ici</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($annonces as $annonce): ?>
              <div class="col-md-6 col-lg-4">
                <div class="resource-card shadow-sm">
                  <div class="card-body">
                    <div class="resource-header">
                      <img src="<?= $annonce['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($annonce['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>" 
                           alt="User" 
                           class="resource-avatar"
                           style="cursor: pointer;"
                           onclick="showUserInfo(<?= htmlspecialchars(json_encode([
                               'id' => $annonce['id_utilisateur'],
                               'nom' => $annonce['nom'],
                               'prenom' => $annonce['prenom'],
                               'photo' => $annonce['photo_profil'],
                               'role' => $annonce['role'],
                               'whatsapp' => $annonce['numero_whatsapp'] ? '0' . $annonce['numero_whatsapp'] : '0'
                           ])) ?>)">
                      <div class="resource-info">
                        <h5><?= htmlspecialchars($annonce['prenom'] . ' ' . $annonce['nom']) ?></h5>
                      </div>
                    </div>
                    <h5 class="resource-title"><?= htmlspecialchars($annonce['titre']) ?></h5>
                    <p class="card-text text-muted mb-3">
                      <?= nl2br(htmlspecialchars($annonce['contenu'])) ?>
                    </p>
                    <div class="resource-meta">
                      <div class="resource-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Publié le <?= date('d/m/Y', strtotime($annonce['date_creation'])) ?></span>
                      </div>
                      <div class="resource-meta-item">
                        <i class="fas fa-clock"></i>
                        <span>Expire le <?= date('d/m/Y', strtotime($annonce['date_expiration'])) ?></span>
                      </div>
                    </div>
                    <?php if (($can_manage_all || ($can_edit_own && $annonce['id_utilisateur'] == $user_id)) && !$is_stagiaire): ?>
                      <div class="resource-actions">
                        <?php if ($can_manage_all): ?>
                        <button class="btn btn-primary btn-icon position-relative" 
                                title="Archiver" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalArchive<?= $annonce['id_annonce'] ?>">
                          <i class="fas fa-archive"></i>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-success btn-icon" 
                                title="Modifier" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEdit<?= $annonce['id_annonce'] ?>">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-icon" 
                                title="Supprimer" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalDelete<?= $annonce['id_annonce'] ?>">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Archive Modal -->
              <div class="modal fade" id="modalArchive<?= $annonce['id_annonce'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title">Archiver l'annonce</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      Voulez-vous vraiment archiver cette annonce ?
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="annonce_id" value="<?= $annonce['id_annonce'] ?>">
                        <button type="submit" name="archive_announcement" class="btn btn-primary">Archiver</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Edit Modal -->
              <div class="modal fade" id="modalEdit<?= $annonce['id_annonce'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                      <h5 class="modal-title">Modifier l'annonce</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST" id="editAnnouncementForm<?= $annonce['id_annonce'] ?>">
                        <input type="hidden" name="annonce_id" value="<?= $annonce['id_annonce'] ?>">
                        <div class="mb-3">
                          <label class="form-label">Titre</label>
                          <input type="text" 
                                 class="form-control" 
                                 name="titre" 
                                 value="<?= htmlspecialchars($annonce['titre']) ?>" 
                                 required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Contenu</label>
                          <textarea class="form-control" 
                                    name="contenu" 
                                    rows="3" 
                                    required><?= htmlspecialchars($annonce['contenu']) ?></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Date d'expiration</label>
                          <input type="date" 
                                 class="form-control" 
                                 name="date_expiration" 
                                 min="<?= date('Y-m-d', strtotime($annonce['date_creation'])) ?>" 
                                 value="<?= date('Y-m-d', strtotime($annonce['date_expiration'])) ?>" 
                                 required>
                        </div>
                        <button type="submit" name="edit_announcement" class="btn btn-success w-100">
                          Enregistrer
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal fade" id="modalDelete<?= $annonce['id_annonce'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                      <h5 class="modal-title">Supprimer l'annonce</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      Cette action est irréversible. Voulez-vous continuer ?
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="annonce_id" value="<?= $annonce['id_annonce'] ?>">
                        <button type="submit" name="delete_announcement" class="btn btn-danger">Supprimer</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Ajouter une annonce</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form method="POST" id="addAnnouncementForm">
                <div class="mb-3">
                  <label class="form-label">Titre</label>
                  <input type="text" class="form-control" name="titre" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Contenu</label>
                  <textarea class="form-control" name="contenu" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Date d'expiration</label>
                  <input type="date" 
                         class="form-control" 
                         name="date_expiration" 
                         min="<?= date('Y-m-d') ?>" 
                         required>
                </div>
                <button type="submit" name="add_announcement" class="btn btn-primary w-100">
                  Publier
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
          <div class="modal-header bg-danger text-white rounded-top-3">
            <h5 class="modal-title" id="logoutModalLabel">
              <i class="fas fa-sign-out-alt me-2"></i>Confirmer la déconnexion
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
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
              <img id="userInfoPhoto" 
                   src="" 
                   alt="Photo de profil" 
                   class="rounded-circle mb-3" 
                   style="width: 150px; height: 150px; object-fit: cover;">
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

      // Fonction pour afficher les informations de l'utilisateur
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
