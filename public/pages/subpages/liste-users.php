<?php
session_start();
include_once '../includes/config.php';

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $nom = sanitize($_POST['nom']);
                $prenom = sanitize($_POST['prenom']);
                $email = sanitize($_POST['email']) . '@ofppt-edu.ma';
                $cef_matricule = sanitize($_POST['cef_matricule']);
                $role = sanitize($_POST['role']);
                $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
                
                // Handle profile photo upload
                $photo_profil = '';
                if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['photo_profil']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $new_filename = uniqid() . '.' . $ext;
                        $upload_path = '../uploads/profiles/' . $new_filename;
                        
                        if (!file_exists('../uploads/profiles/')) {
                            mkdir('../uploads/profiles/', 0777, true);
                        }
                        
                        if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $upload_path)) {
                            $photo_profil = 'uploads/profiles/' . $new_filename;
                        }
                    }
                }
                
                $sql = "INSERT INTO utilisateur (nom, prenom, email, cef_matricule, role, mot_de_passe, photo_profil, statut) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'approuvé')";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", $nom, $prenom, $email, $cef_matricule, $role, $mot_de_passe, $photo_profil);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Utilisateur créé avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la création de l'utilisateur";
                }
                $stmt->close();
                break;
                
            case 'update_role':
                $user_id = (int)$_POST['user_id'];
                $new_role = sanitize($_POST['new_role']);
                
                $sql = "UPDATE utilisateur SET role = ? WHERE id_utilisateur = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_role, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Rôle mis à jour avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour du rôle";
                }
                $stmt->close();
                break;
                
            case 'suspend':
                $user_id = (int)$_POST['user_id'];
                
                // Check if user is an admin or trying to suspend themselves
                $check_sql = "SELECT role FROM utilisateur WHERE id_utilisateur = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $user_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $user_data = $check_result->fetch_assoc();
                $check_stmt->close();

                if ($user_data['role'] === 'admin' || $user_id == $_SESSION['user_id']) {
                    $_SESSION['error'] = "Impossible de suspendre un administrateur ou votre propre compte";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }

                if (!in_array($user_data['role'], ['stagiaire', 'formateur'])) {
                    $_SESSION['error'] = "Vous ne pouvez suspendre que les stagiaires et les formateurs";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
                
                $sql = "UPDATE utilisateur SET statut = 'suspendu' WHERE id_utilisateur = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Utilisateur suspendu avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la suspension de l'utilisateur";
                }
                $stmt->close();
                break;
                
            case 'unsuspend':
                $user_id = (int)$_POST['user_id'];
                $new_role = sanitize($_POST['new_role']);
                
                $sql = "UPDATE utilisateur SET statut = 'approuvé', role = ? WHERE id_utilisateur = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_role, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Utilisateur réactivé avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la réactivation de l'utilisateur";
                }
                $stmt->close();
                break;

            case 'reactivate':
                $user_id = (int)$_POST['user_id'];
                $new_role = sanitize($_POST['new_role']);
                
                $sql = "UPDATE utilisateur SET statut = 'approuvé', role = ? WHERE id_utilisateur = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_role, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Utilisateur réactivé avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la réactivation de l'utilisateur";
                }
                $stmt->close();
                break;
                
            case 'delete':
                $user_id = (int)$_POST['user_id'];
                
                // First get the user's photo to delete it
                $sql = "SELECT photo_profil FROM utilisateur WHERE id_utilisateur = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                // Delete the user
                $sql = "DELETE FROM utilisateur WHERE id_utilisateur = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                
                if ($stmt->execute()) {
                    // Delete the profile photo if it exists
                    if (!empty($user['photo_profil'])) {
                        $photo_path = '../' . $user['photo_profil'];
                        if (file_exists($photo_path)) {
                            unlink($photo_path);
                        }
                    }
                    $_SESSION['success'] = "Utilisateur supprimé avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur";
                }
                $stmt->close();
                break;
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get total users count
$count_sql = "SELECT COUNT(*) as total FROM utilisateur";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_users = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Get users list
$users = [];

// Get current user's role
$current_user_sql = "SELECT role FROM utilisateur WHERE id_utilisateur = ?";
$current_user_stmt = $conn->prepare($current_user_sql);
$current_user_stmt->bind_param("i", $_SESSION['user_id']);
$current_user_stmt->execute();
$current_user_result = $current_user_stmt->get_result();
$current_user = $current_user_result->fetch_assoc();
$current_user_stmt->close();

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = '%' . $conn->real_escape_string(trim($_GET['search'])) . '%';
    
    if ($current_user['role'] === 'admin') {
        $sql = "SELECT * FROM utilisateur 
                WHERE (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR cef_matricule LIKE ?)
                AND role != 'admin'
                AND id_utilisateur != ?
                ORDER BY id_utilisateur DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $search, $search, $search, $search, $_SESSION['user_id']);
    } else {
        $sql = "SELECT * FROM utilisateur 
                WHERE (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR cef_matricule LIKE ?)
                AND id_utilisateur != ?
                ORDER BY id_utilisateur DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $search, $search, $search, $search, $_SESSION['user_id']);
    }
} else {
    if ($current_user['role'] === 'admin') {
        $sql = "SELECT * FROM utilisateur WHERE role != 'admin' AND id_utilisateur != ? ORDER BY id_utilisateur DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
    } else {
        $sql = "SELECT * FROM utilisateur WHERE id_utilisateur != ? ORDER BY id_utilisateur DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
    }
}

// Execute
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

$stmt->close();

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'approuvé':
            return 'bg-success fw-light';
        case 'suspendu':
            return 'bg-danger fw-light';
        case 'en_attente':
            return 'bg-warning text-dark';
        case 'rejeté':
            return 'bg-secondary fw-light';
        default:
            return 'bg-primary fw-light';
    }
}

// Function to get status display text
function getStatusDisplayText($status) {
    switch ($status) {
        case 'approuvé':
            return 'Approuvé';
        case 'suspendu':
            return 'Suspendu';
        case 'en_attente':
            return 'En attente';
        case 'rejeté':
            return 'Rejeté';
        default:
            return ucfirst($status);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Liste des utilisateurs</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      rel="shortcut icon"
      href="./../../assets/images/logo.png"
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

      .user-card {
        background-color: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        position: relative;
      }

      .user-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
      }

      .user-info-item {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: #6c757d;
        font-size: 0.9rem;
        flex-wrap: wrap;
      }

      .user-info-item strong {
        color: #343a40;
        min-width: 120px;
      }

      .action-buttons {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
      }

      .search-container {
        max-width: 400px;
        margin-bottom: 1.5rem;
      }

      .modal-content {
        border-radius: 0.5rem;
      }

      .modal-header {
        padding: 1rem 1.5rem;
      }

      .modal-body {
        padding: 1.5rem;
      }

      .form-select, .form-control {
        padding: 0.5rem 0.75rem;
      }

      .profile-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 2px solid #e9ecef;
      }

      @media (max-width: 576px) {
        .user-card {
          padding: 1rem;
        }

        .action-buttons {
          position: static;
          margin-bottom: 1rem;
          justify-content: flex-start;
        }

        .profile-image {
          width: 80px;
          height: 80px;
        }

        .user-info-item strong {
          min-width: 100px;
        }

        .search-container {
          max-width: 100%;
        }
      }
    </style>
  </head>
  <body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand" href="./../../index.php">
          <img src="./../../assets/images/logo.png" width="30px" alt="" />
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
          <a class="nav-link" href="./../dashboard.php">
            <i class="fa-solid fa-chart-line"></i>
            Tableau de bord
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./../forum.php">
            <i class="fas fa-comments"></i>
            Forum
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./../ressources.php">
            <i class="fas fa-folder-open"></i>
            Resources
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./../annonces.php">
            <i class="fa-solid fa-bullhorn"></i>
            Annonces
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./../notifications.php">
            <i class="fas fa-bell"></i>
            Notifications
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./../rechercher.php">
            <i class="fas fa-search"></i>
            Rechercher
          </a>
        </li>

        <hr class="text-white" />

        <li class="nav-item">
          <a class="nav-link" href="./../profile.php">
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
      <div class="container-fluid">
        <!-- Page Title -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 mt-3">
          <div>
            <h3 class="fw-bold">Gérer les utilisateurs</h3>
            <span class="badge bg-primary rounded fs-6">Total: <?php echo $total_users; ?> utilisateurs</span>
          </div>
          <br>
          <div class="d-flex align-items-center gap-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
              <i class="fas fa-user-plus"></i>
            </button>
          </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <?php if (!empty($users)): ?>
        <div class="search-container">
          <form class="d-flex" role="search" method="GET">
            <div class="input-group">
              <input
                type="search"
                class="form-control"
                name="search"
                placeholder="Rechercher un utilisateur"
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
              />
              <button class="btn btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
              </button>
            </div>
          </form>
        </div>
        <?php endif; ?>

        <!-- User Cards -->
        <div class="row">
          <?php if (empty($users)): ?>
            <div class="col-12">
              <div class="text-muted text-center alert alert-info">
                <i class="fas fa-users fa-2x mt-3 mb-2"></i>
                <p>Aucun utilisateur trouvé</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($users as $user): ?>
          <div class="col-12 col-lg-6 mb-4">
            <div class="user-card h-100">
          <div class="action-buttons">
            <button
              class="btn btn-sm btn-primary"
              data-bs-toggle="modal"
              data-bs-target="#editRoleModal<?php echo $user['id_utilisateur']; ?>"
              title="Modifier le rôle"
              <?php if ($user['id_utilisateur'] == $_SESSION['user_id']) echo 'style="display: none;"'; ?>
            >
              <i class="fas fa-pen"></i>
            </button>
                <?php if ($user['statut'] === 'approuvé'): ?>
            <button
              class="btn btn-sm btn-warning"
              data-bs-toggle="modal"
              data-bs-target="#suspendModal<?php echo $user['id_utilisateur']; ?>"
              title="Suspendre"
              <?php if ($user['id_utilisateur'] == $_SESSION['user_id'] || $user['role'] === 'admin') echo 'style="display: none;"'; ?>
            >
              <i class="fas fa-ban"></i>
            </button>
                <?php elseif ($user['statut'] === 'suspendu'): ?>
                <button
                  class="btn btn-sm btn-success"
                  data-bs-toggle="modal"
                  data-bs-target="#unsuspendModal<?php echo $user['id_utilisateur']; ?>"
                  title="Réactiver"
                  <?php if ($user['id_utilisateur'] == $_SESSION['user_id'] || $user['role'] === 'admin') echo 'style="display: none;"'; ?>
                >
                  <i class="fas fa-check"></i>
                </button>
                <?php elseif ($user['statut'] === 'rejeté'): ?>
                <button
                  class="btn btn-sm btn-success"
                  data-bs-toggle="modal"
                  data-bs-target="#reactivateModal<?php echo $user['id_utilisateur']; ?>"
                  title="Réactiver"
                  <?php if ($user['id_utilisateur'] == $_SESSION['user_id'] || $user['role'] === 'admin') echo 'style="display: none;"'; ?>
                >
                  <i class="fas fa-check"></i>
                </button>
                <?php endif; ?>
            <button
              class="btn btn-sm btn-danger"
              data-bs-toggle="modal"
              data-bs-target="#deleteModal<?php echo $user['id_utilisateur']; ?>"
              title="Supprimer"
              <?php if ($user['id_utilisateur'] == $_SESSION['user_id']) echo 'style="display: none;"'; ?>
            >
              <i class="fas fa-trash"></i>
            </button>
          </div>

              <div class="text-center mb-4">
                <img src="<?= $user['photo_profil'] ? '../../assets/uploads/profile/' . htmlspecialchars($user['photo_profil']) : '../../assets/images/ISMO SHARE.png' ?>" 
                     alt="Photo de l'utilisateur" 
                     class="rounded-circle profile-image" />
              </div>

            <div class="user-info">
                <h5 class="fw-semibold mb-1 text-center"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h5>
                <p class="text-muted mb-3 text-center"><?php echo htmlspecialchars($user['email']); ?></p>

                <div class="user-info-item">
                  <strong>Statut:</strong>
                  <span class="badge <?php echo getStatusBadgeClass($user['statut']); ?>">
                    <?php echo getStatusDisplayText($user['statut']); ?>
                  </span>
                </div>
                <div class="user-info-item">
                  <strong>Rôle:</strong>
                  <?php 
                  if ($user['statut'] === 'en_attente') {
                      echo '<span class="text-muted">Non spécifié</span>';
                  } else {
                      echo '<span class="badge bg-danger-subtle text-danger">' . strtoupper($user['role']) . '</span>';
                  }
                  ?>
                </div>
                <div class="user-info-item">
                  <strong>CEF:</strong>
                  <span><?php echo !empty($user['cef_matricule']) ? htmlspecialchars($user['cef_matricule']) : 'Non spécifié'; ?></span>
                </div>
                <div class="user-info-item">
                  <strong>Filière:</strong>
                  <span><?php echo !empty($user['filiere']) ? htmlspecialchars($user['filiere']) : 'Non spécifiée'; ?></span>
                </div>
                <div class="user-info-item">
                  <strong>Email:</strong>
                  <span><?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : 'Non spécifié'; ?></span>
                </div>
                <div class="user-info-item">
                  <strong>WhatsApp:</strong>
                  <?php if (!empty($user['numero_whatsapp'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $user['numero_whatsapp']) ?>" target="_blank" class="text-decoration-none">
                      +212<?php echo htmlspecialchars($user['numero_whatsapp']); ?>
                    </a>
                  <?php else: ?>
                    <span>Non spécifié</span>
                  <?php endif; ?>
                </div>
                <div class="user-info-item">
                  <strong>Année:</strong>
                  <span><?php echo !empty($user['annee_formation']) ? htmlspecialchars($user['annee_formation']) : 'Non spécifiée'; ?></span>
                </div>
                <div class="user-info-item">
                  <strong>Bio:</strong>
                  <span><?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'Non spécifiée'; ?></span>
                </div>
              </div>
            </div>

            <!-- Edit Role Modal -->
            <div class="modal fade" id="editRoleModal<?php echo $user['id_utilisateur']; ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                      <i class="fas fa-user-tag me-2"></i>Modifier le rôle
                    </h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <form method="POST">
                      <input type="hidden" name="action" value="update_role">
                      <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateur']; ?>">
                      <div class="mb-3">
                        <label for="userNewRole<?php echo $user['id_utilisateur']; ?>" class="form-label">Nouveau rôle</label>
                        <select class="form-select" name="new_role" required>
                          <option value="stagiaire" <?php echo $user['role'] === 'stagiaire' ? 'selected' : ''; ?>>Stagiaire</option>
                          <option value="formateur" <?php echo $user['role'] === 'formateur' ? 'selected' : ''; ?>>Formateur</option>
                          <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                      </div>
                      <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Suspend Modal -->
            <div class="modal fade" id="suspendModal<?php echo $user['id_utilisateur']; ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                      <i class="fas fa-ban me-2"></i>Suspendre l'utilisateur
                    </h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p class="fs-5">Voulez-vous <strong>suspendre</strong> <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?> ?</p>
                    <form method="POST">
                      <input type="hidden" name="action" value="suspend">
                      <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateur']; ?>">
                      <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Confirmer</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Unsuspend Modal -->
            <div class="modal fade" id="unsuspendModal<?php echo $user['id_utilisateur']; ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                      <i class="fas fa-check me-2"></i>Réactiver l'utilisateur
                    </h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p class="fs-5 mb-3">Voulez-vous <strong>réactiver</strong> <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?> ?</p>
                    <form method="POST">
                      <input type="hidden" name="action" value="unsuspend">
                      <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateur']; ?>">
                      <div class="mb-3">
                        <label for="unsuspendRole<?php echo $user['id_utilisateur']; ?>" class="form-label">Nouveau rôle</label>
                        <select class="form-select" name="new_role" required>
                          <option value="stagiaire" <?php echo $user['role'] === 'stagiaire' ? 'selected' : ''; ?>>Stagiaire</option>
                          <option value="formateur" <?php echo $user['role'] === 'formateur' ? 'selected' : ''; ?>>Formateur</option>
                          <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                      </div>
                      <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Confirmer</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reactivate Modal -->
            <div class="modal fade" id="reactivateModal<?php echo $user['id_utilisateur']; ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                      <i class="fas fa-check me-2"></i>Réactiver l'utilisateur
                    </h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p class="fs-5 mb-3">Voulez-vous <strong>réactiver</strong> <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?> ?</p>
                    <form method="POST">
                      <input type="hidden" name="action" value="reactivate">
                      <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateur']; ?>">
                      <div class="mb-3">
                        <label for="reactivateRole<?php echo $user['id_utilisateur']; ?>" class="form-label">Nouveau rôle</label>
                        <select class="form-select" name="new_role" required>
                          <option value="stagiaire" <?php echo $user['role'] === 'stagiaire' ? 'selected' : ''; ?>>Stagiaire</option>
                          <option value="formateur" <?php echo $user['role'] === 'formateur' ? 'selected' : ''; ?>>Formateur</option>
                          <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                      </div>
                      <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Confirmer</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal<?php echo $user['id_utilisateur']; ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                      <i class="fas fa-trash me-2"></i>Supprimer l'utilisateur
                    </h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p class="fs-5">Êtes-vous sûr de vouloir <strong>supprimer</strong> <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?> ?</p>
                    <form method="POST">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="user_id" value="<?php echo $user['id_utilisateur']; ?>">
                      <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Create User Modal -->
        <div class="modal fade" id="createUserModal" tabindex="-1">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                  <i class="fas fa-user-plus me-2"></i>Ajouter un utilisateur
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="action" value="create">
                  
                  <!-- Photo de profil -->
                  <div class="text-center mb-4">
                    <img
                      src="../../assets/images/ISMO SHARE.png"
                      alt="Aperçu de la photo de profil"
                      class="rounded-circle mb-2"
                      width="120"
                      height="120"
                      id="newUserPreviewImage"
                    />
                    <div class="mt-2">
                      <input
                        type="file"
                        class="form-control d-none"
                        id="userPhoto"
                        name="photo_profil"
                        accept="image/*"
                        onchange="previewNewUserPhoto(event)"
                      />
                      <label for="userPhoto" class="btn btn-outline-primary">
                        <i class="fas fa-camera me-2"></i>Choisir une photo
                      </label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="userFirstName" class="form-label">Prénom</label>
                      <input
                        type="text"
                        class="form-control"
                        id="userFirstName"
                        name="prenom"
                        placeholder="Entrez le prénom"
                        required
                      />
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="userLastName" class="form-label">Nom</label>
                      <input
                        type="text"
                        class="form-control"
                        id="userLastName"
                        name="nom"
                        placeholder="Entrez le nom"
                        required
                      />
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="userEmail" class="form-label">Email Institutionnel</label>
                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control"
                        id="userEmail"
                        name="email"
                        placeholder="Entrez l'email"
                        required
                      />
                      <span class="input-group-text">@ofppt-edu.ma</span>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="userMatricule" class="form-label">CEF/Matricule</label>
                    <input
                      type="text"
                      class="form-control"
                      id="userMatricule"
                      name="cef_matricule"
                      placeholder="Entrez le CEF/Matricule"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <label for="userPassword" class="form-label">Mot de passe</label>
                    <input
                      type="password"
                      class="form-control"
                      id="userPassword"
                      name="mot_de_passe"
                      placeholder="Entrez le mot de passe"
                      required
                    />
                  </div>

                  <div class="mb-4">
                    <label for="userRole" class="form-label">Rôle</label>
                    <select class="form-select" id="userRole" name="role" required>
                      <option value="" selected>Sélectionnez un rôle</option>
                      <option value="admin">Admin</option>
                      <option value="formateur">Formateur</option>
                      <option value="stagiaire">Stagiaire</option>
                    </select>
                  </div>

                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-check-circle me-2"></i>Ajouter
                    </button>
                  </div>
                </form>
              </div>
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
            <a href="../logout.php" class="btn btn-danger">Se déconnecter</a>
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

      function previewNewUserPhoto(event) {
        const reader = new FileReader();
        reader.onload = function() {
          const preview = document.getElementById('newUserPreviewImage');
          preview.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
