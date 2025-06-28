<?php
session_start();
include_once 'includes/config.php';
include_once 'includes/notification_functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT u.*, f.nom as filiere_nom FROM utilisateur u LEFT JOIN filiere f ON u.id_filiere = f.id_filiere WHERE u.id_utilisateur = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$counts = [
    'inscriptions' => 0,
    'ressources' => 0,
    'reponses' => 0,
    'users' => 0,
    'messages' => 0
];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM utilisateur WHERE statut = 'en_attente'");
$stmt->execute();
$counts['inscriptions'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM ressource WHERE statut = 'en_attente'");
$stmt->execute();
$counts['ressources'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reponseforum WHERE est_valide = 0");
$stmt->execute();
$counts['reponses'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM utilisateur WHERE statut = 'approuvé'");
$stmt->execute();
$counts['users'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE est_lu = 0");
$stmt->execute();
$counts['messages'] = $stmt->get_result()->fetch_assoc()['count'];

$unread_count = getUnreadNotificationsCount($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Tableau de bord</title>
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

      #active {
        background-color: rgba(255, 255, 255, 0.1);
      }

      .sidebar .nav-link:hover {
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

      .badge-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
      }

      .welcome-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .welcome-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
      }

      .welcome-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(13, 110, 253, 0.1);
        border-radius: 50%;
      }

      .stat-item {
        transition: transform 0.2s ease;
      }

      .stat-item:hover {
        transform: translateY(-3px);
      }

      .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        display: block;
        margin-bottom: 0.25rem;
      }

      .stat-value {
        font-weight: 600;
        color: #212529;
      }

      .date-time-section {
        padding: 1rem;
        background-color: rgba(13, 110, 253, 0.05);
        border-radius: 0.75rem;
        display: inline-block;
      }

      .current-date, .current-time {
        font-size: 1.1rem;
      }

      @media (max-width: 991.98px) {
        .date-time-section {
          text-align: center !important;
          width: 100%;
        }
      }

      @media (max-width: 767.98px) {
        .welcome-card .card-body {
          padding: 1.5rem;
        }

        .user-stats {
          margin-top: 1rem;
        }
      }

      .date-time-box {
        background-color: rgba(13, 110, 253, 0.05);
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        text-align: left;
      }

      .current-date, .current-time {
        font-size: 1.1rem;
        color: #495057;
      }

      .welcome-statement .card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      @media (max-width: 767.98px) {
        .date-time-box {
          text-align: left;
          width: 100%;
        }
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
          <a class="nav-link" id="active" href="./dashboard.php">
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
    <main class="main-content bg-light py-4">
      <div class="container-fluid">
        <!-- Header with User Info -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
          <div class="d-flex align-items-center">
            <img src="<?= $user['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($user['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>" 
                 alt="Photo de l'utilisateur" 
                 width="100" 
                 height="100" 
                 class="rounded-circle me-3 border" />
            <div>
              <h3 class="mb-0 fw-semibold" style="text-transform: uppercase;"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h3>
              <span class="badge bg-danger-subtle text-danger"><?= strtoupper($user['role']) ?></span>
            </div>
          </div>
          <div class="date-time-box mt-3 mt-md-0">
            <div class="current-date mb-2">
              <i class="far fa-calendar-alt text-primary me-2"></i>
              <span id="current-date" class="fw-semibold"></span>
            </div>
            <div class="current-time">
              <i class="far fa-clock text-primary me-2"></i>
              <span id="current-time" class="fw-semibold"></span>
            </div>
          </div>
        </div>

        <!-- Welcome Statement -->
        <div class="welcome-statement mb-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h4 class="fw-bold mb-0">
                <i class="fas fa-quote-left text-primary me-2"></i>
                Bienvenue dans votre espace personnel
                <i class="fas fa-quote-right text-primary ms-2"></i>
              </h4>
            </div>
          </div>
        </div>

        <!-- Action Cards -->
        <?php if ($user['role'] !== 'stagiaire' && !empty($user['role'])): ?>
        <div class="row g-4">
          <h4 class="fw-bold text-dark mt-5">Actions rapides</h4>
          <?php if ($user['role'] === 'admin'): ?>
          <!-- Admin Actions -->
          <div class="col-md-3">
            <a href="./subpages/valider-inscriptions.php" 
               class="card shadow-sm h-100 border-0 text-center py-4 px-3 d-flex flex-column align-items-center justify-content-center text-decoration-none bg-white rounded-3 action-card position-relative">
              <i class="fas fa-user-check fa-2x text-primary mb-3"></i>
              <h5 class="fw-semibold mb-0">Valider les inscriptions</h5>
              <?php if ($counts['inscriptions'] > 0): ?>
                <span class="badge-count"><?= $counts['inscriptions'] ?></span>
              <?php endif; ?>
            </a>
          </div>
          <?php endif; ?>

          <?php if ($user['role'] === 'admin'): ?>
          <!-- Admin Only Actions -->
          <div class="col-md-3">
            <a href="./subpages/liste-users.php" 
               class="card shadow-sm h-100 border-0 text-center py-4 px-3 d-flex flex-column align-items-center justify-content-center text-decoration-none bg-white rounded-3 action-card position-relative">
              <i class="fas fa-users-cog fa-2x text-primary mb-3"></i>
              <h5 class="fw-semibold mb-0">Gérer les utilisateurs</h5>
            </a>
          </div>

          <div class="col-md-3">
            <a href="./subpages/contact-messages.php" 
               class="card shadow-sm h-100 border-0 text-center py-4 px-3 d-flex flex-column align-items-center justify-content-center text-decoration-none bg-white rounded-3 action-card position-relative">
              <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
              <h5 class="fw-semibold mb-0">Messages de contact</h5>
              <?php if ($counts['messages'] > 0): ?>
                <span class="badge-count"><?= $counts['messages'] ?></span>
              <?php endif; ?>
            </a>
          </div>
          <?php endif; ?>

          <!-- Common Actions for Admin and Formateur -->
          <div class="col-md-3">
            <a href="./subpages/valider-ressources.php" 
               class="card shadow-sm h-100 border-0 text-center py-4 px-3 d-flex flex-column align-items-center justify-content-center text-decoration-none bg-white rounded-3 action-card position-relative">
              <i class="fas fa-folder-open fa-2x text-primary mb-3"></i>
              <h5 class="fw-semibold mb-0">Valider les ressources</h5>
              <?php if ($counts['ressources'] > 0): ?>
                <span class="badge-count"><?= $counts['ressources'] ?></span>
              <?php endif; ?>
            </a>
          </div>
        </div>
        <?php endif; ?>
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

      function updateDateTime() {
        const currentDate = new Date();
        
        // Format date: "Jour, Date Mois Année"
        const fullDate = currentDate.toLocaleDateString("fr-FR", {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric"
        });
        
        // Format time: "HH:MM:SS AM/PM"
        const currentTime = currentDate.toLocaleTimeString("fr-FR", {
          hour: "2-digit",
          minute: "2-digit",
          second: "2-digit",
          hour12: true
        });
        
        document.getElementById("current-date").textContent = fullDate;
        document.getElementById("current-time").textContent = currentTime;
      }

      // Update immediately
      updateDateTime();
      
      // Update every second
      setInterval(updateDateTime, 1000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
