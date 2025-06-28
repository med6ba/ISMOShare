<?php
session_start();
include_once 'includes/config.php';
include_once 'includes/notification_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Marquer une notification comme lue
if (isset($_POST['mark_as_read'])) {
    $notification_id = intval($_POST['notification_id']);
    markNotificationAsRead($conn, $notification_id);
    header("Location: notifications.php");
    exit();
}

// Marquer toutes les notifications comme lues
if (isset($_POST['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notification SET est_lu = 1 WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

// Récupérer toutes les notifications de l'utilisateur
$stmt = $conn->prepare("
    SELECT n.*, 
           CASE 
               WHEN n.type_notification = 'comment' THEN 'commentaire'
               WHEN n.type_notification = 'announcement' THEN 'annonce'
               WHEN n.type_notification = 'resource_comment' THEN 'commentaire sur ressource'
               WHEN n.type_notification = 'forum_comment' THEN 'commentaire sur forum'
           END as type_affichage
    FROM notification n 
    WHERE n.id_utilisateur = ? 
    ORDER BY n.date_notification DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Récupérer le nombre de notifications non lues
$unread_count = getUnreadNotificationsCount($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Notifications</title>
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

      .notification-card {
        background: #ffffff;
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease;
        padding: 1.5rem;
        margin-bottom: 1rem;
        position: relative;
      }

      .notification-card.unread {
        border-left: 4px solid #0d6efd;
      }

      .notification-card.unread::before {
        content: '';
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 10px;
        height: 10px;
        background-color: #0d6efd;
        border-radius: 50%;
      }

      .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
      }

      .notification-icon.comment {
        background-color: #e3f2fd;
        color: #0d6efd;
      }

      .notification-icon.announcement {
        background-color: #fff3cd;
        color: #ffc107;
      }

      .notification-icon.resource {
        background-color: #d1e7dd;
        color: #198754;
      }

      .notification-icon.forum {
        background-color: #f8d7da;
        color: #dc3545;
      }

      .notification-time {
        font-size: 0.875rem;
        color: #6c757d;
      }

      .notification-type {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
      }

      .notification-message {
        margin-bottom: 0;
        color: #212529;
      }

      .notification-actions {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
      }

      .empty-state {
        text-align: center;
        padding: 3rem 1rem;
      }

      .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1rem;
      }

      .empty-state h4 {
        color: #6c757d;
        margin-bottom: 0.5rem;
      }

      .empty-state p {
        color: #6c757d;
        margin-bottom: 0;
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
          <a class="nav-link" href="./annonces.php">
            <i class="fa-solid fa-bullhorn"></i>
            Annonces
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="active" href="./notifications.php">
            <i class="fas fa-bell"></i>
            Notifications
            <?php
            if ($unread_count > 0) {
                echo '<span class="badge bg-danger ms-2">' . htmlspecialchars($unread_count) . '</span>';
            }
            ?>

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
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">Notifications</h3>
          <?php
            if (!empty($notifications)) {
                echo '<form method="POST" class="d-inline">
                        <button type="submit" name="mark_all_read" class="btn btn-outline-primary">
                          <i class="fas fa-check-double me-2"></i>Marquer tout comme lu
                        </button>
                      </form>';
            }
            ?>

        </div>

        <?php
          if (empty($notifications)) {
              echo '<div class="empty-state">
                      <i class="fas fa-bell-slash text-muted"></i>
                      <h4>Aucune notification</h4>
                      <p>Vous n\'avez pas encore reçu de notifications</p>
                    </div>';
          } else {
              foreach ($notifications as $notification) {
                  $unreadClass = !$notification['est_lu'] ? 'unread' : '';
                  echo '<div class="notification-card ' . $unreadClass . '">
                          <div class="d-flex align-items-start">
                            <div class="notification-icon ' . htmlspecialchars($notification['type_notification']) . '">';
                  
                  switch ($notification['type_notification']) {
                      case 'comment':
                          echo '<i class="fas fa-comment"></i>';
                          break;
                      case 'announcement':
                          echo '<i class="fas fa-bullhorn"></i>';
                          break;
                      case 'resource_comment':
                          echo '<i class="fas fa-file-alt"></i>';
                          break;
                      case 'forum_comment':
                          echo '<i class="fas fa-comments"></i>';
                          break;
                  }

                  echo '</div>
                        <div class="flex-grow-1">
                          <div class="notification-type">' . htmlspecialchars($notification['type_affichage']) . '</div>
                          <p class="notification-message">' . htmlspecialchars($notification['message']) . '</p>
                          <div class="notification-time">' . date('d/m/Y H:i', strtotime($notification['date_notification'])) . '</div>';

                  if (!$notification['est_lu']) {
                      echo '<div class="notification-actions">
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="notification_id" value="' . $notification['id_notification'] . '">
                                <button type="submit" name="mark_as_read" class="btn btn-sm btn-outline-primary">
                                  <i class="fas fa-check me-2"></i>Marquer comme lu
                                </button>
                              </form>
                            </div>';
                  }

                  echo '</div></div></div>';
              }
          }
          ?>

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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
