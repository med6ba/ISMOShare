<?php
include_once '../includes/config.php';
include_once '../includes/notification_functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

$stmt = $conn->prepare("SELECT role FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$unread_count = getUnreadNotificationsCount($conn, $_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_user'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("UPDATE utilisateur SET statut = 'approuvé', role = ? WHERE id_utilisateur = ?");
    $stmt->bind_param("si", $role, $user_id);
    
    if ($stmt->execute()) {
        $message = "Votre compte a été approuvé. Vous pouvez maintenant vous connecter.";
        addNotification($conn, $user_id, 'announcement', $user_id, $message);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_user'])) {
    $user_id = $_POST['user_id'];
    
    $stmt = $conn->prepare("UPDATE utilisateur SET statut = 'rejeté' WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $message = "Votre demande d'inscription a été rejetée.";
        addNotification($conn, $user_id, 'announcement', $user_id, $message);
    }
}

$stmt = $conn->prepare("SELECT * FROM utilisateur WHERE statut = 'en_attente'");
$stmt->execute();
$pending_users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Valider les inscriptions</title>
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

      .table {
        margin-bottom: 0;
      }

      .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 1rem;
        border-bottom: 2px solid #dee2e6;
      }

      .table td {
        padding: 1rem;
        vertical-align: middle;
      }

      .action-buttons {
        display: flex;
        gap: 0.5rem;
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
            <?php if ($unread_count > 0): ?>
              <span class="badge bg-danger ms-2"><?= $unread_count ?></span>
            <?php endif; ?>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold mt-3">Valider les Inscriptions</h3>
        </div>

        <?php if ($pending_users->num_rows === 0): ?>
          <div class="text-muted text-center alert alert-info">
            <i class="fas fa-user-clock fa-2x mt-3 mb-3"></i>
            <p>Il n'y a aucune demande d'inscription en attente pour le moment.</p>
          </div>
        <?php else: ?>
          <!-- Registrations Table -->
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="table-container">
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col">Prénom</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Email Institutionnel</th>
                        <th scope="col">CEF/Matricule</th>
                        <th scope="col" class="text-center">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $pending_users->fetch_assoc()): ?>
                        <tr>
                          <td><?= htmlspecialchars($row['prenom']) ?></td>
                          <td><?= htmlspecialchars($row['nom']) ?></td>
                          <td>
                            <div class="d-flex align-items-center">
                              <?= htmlspecialchars($row['email']) ?>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <?= htmlspecialchars($row['cef_matricule']) ?>
                            </div>
                          </td>
                          <td class="text-center">
                            <div class="action-buttons justify-content-center">
                              <button
                                class="btn btn-success btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#approveModal"
                                onclick="setUserId(<?= $row['id_utilisateur'] ?>)"
                                title="Approuver"
                              >
                                <i class="fas fa-check"></i>
                              </button>
                              <button
                                class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal"
                                onclick="setUserId(<?= $row['id_utilisateur'] ?>)"
                                title="Rejeter"
                              >
                                <i class="fas fa-times"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Modals -->
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                  <i class="fas fa-check-circle me-2"></i>Choisir le rôle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <p class="fs-5 mb-4">Sélectionnez le rôle pour l'utilisateur :</p>
                <form method="POST" action="">
                  <input type="hidden" name="user_id" id="approveUserId">
                  <div class="mb-3">
                    <label for="roleSelect" class="form-label">Rôle</label>
                    <select class="form-select" id="roleSelect" name="role" required>
                      <option value="" disabled selected>Sélectionnez un rôle</option>
                      <option value="admin">Admin</option>
                      <option value="formateur">Formateur</option>
                      <option value="stagiaire">Stagiaire</option>
                    </select>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="approve_user" class="btn btn-success">
                      <i class="fas fa-check me-2"></i>Approuver
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                  <i class="fas fa-times-circle me-2"></i>Confirmer le rejet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <p class="fs-5 mb-4">Êtes-vous sûr de vouloir rejeter cette demande d'inscription ? Cette action est irréversible.</p>
                <form method="POST" action="">
                  <input type="hidden" name="user_id" id="rejectUserId">
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="reject_user" class="btn btn-danger">
                      <i class="fas fa-times me-2"></i>Rejeter
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
      let currentUserId = null;

      function setUserId(id) {
        currentUserId = id;
        document.getElementById('approveUserId').value = id;
        document.getElementById('rejectUserId').value = id;
      }
    </script>

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
