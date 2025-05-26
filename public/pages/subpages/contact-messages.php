<?php
include_once '../includes/config.php';

// Vérifier si l'utilisateur est connecté et est admin
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

// Marquer tous les messages comme lus lors du chargement de la page
$stmt = $conn->prepare("UPDATE contact_messages SET est_lu = 1 WHERE est_lu = 0");
$stmt->execute();
$stmt->close();

// Gérer le marquage comme lu et la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $conn->prepare("UPDATE contact_messages SET est_lu = 1 WHERE id_message = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id_message = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Rediriger pour éviter la soumission multiple
    header("Location: contact-messages.php" . (isset($_GET['search']) ? "?search=" . urlencode($_GET['search']) : ""));
    exit();
}

// Gérer la recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where_clause = "WHERE nom_complet LIKE ? OR email LIKE ? OR message LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
    $types = "sss";
}

// Récupérer les messages
$sql = "SELECT * FROM contact_messages $where_clause ORDER BY date_envoi DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Messages de contact</title>
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

      .table-container {
        margin-top: 1.5rem;
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
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
          <h3 class="fw-bold mb-3 mb-md-0 mt-3">Messages de contact</h3>
        </div>

        <!-- Search Bar -->
        <?php if (!empty($messages)): ?>
        <div class="search-container mb-4">
          <div class="row">
            <div class="col-md-4">
              <form class="d-flex" role="search" method="GET">
                <div class="input-group">
                  <input
                    type="search"
                    class="form-control"
                    name="search"
                    placeholder="Rechercher un message"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                  />
                  <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Messages List -->
        <div class="row">
          <?php if (empty($messages)): ?>
            <div class="col-12">
            <div class="text-muted text-center alert alert-info">
                <i class="fas fa-envelope fa-2x mt-3 mb-2"></i>
                <p>Aucun message trouvé</p>
            </div>
            </div>
          <?php else: ?>
            <?php foreach ($messages as $message): ?>
              <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100 <?= $message['est_lu'] ? '' : 'border-primary' ?>">
                  <div class="card-body p-4">
                    <div class="mb-3">
                      <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-semibold mb-1"><?= htmlspecialchars($message['message']) ?></h5>
                        <div class="btn-group">
                          <?php if (!$message['est_lu']): ?>
                            <form method="POST" class="d-inline">
                              <input type="hidden" name="message_id" value="<?= $message['id_message'] ?>">
                              <button type="submit" name="mark_read" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                              </button>
                            </form>
                          <?php endif; ?>
                          <button type="button" class="btn btn-sm btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $message['id_message'] ?>">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                      <div class="text-muted small mt-2">
                        <p class="mb-1">Nom: <?= htmlspecialchars($message['nom_complet']) ?></p>
                        <p class="mb-0">Email: <?= htmlspecialchars($message['email']) ?></p>
                      </div>
                    </div>
                    <div class="mt-3">
                      <small class="text-muted">
                        Envoyé le <?= date('d/m/Y à H:i', strtotime($message['date_envoi'])) ?>
                        <?php if (!$message['est_lu']): ?>
                          <span class="badge bg-primary ms-2">Nouveau</span>
                        <?php endif; ?>
                      </small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal de confirmation de suppression -->
              <div class="modal fade" id="deleteModal<?= $message['id_message'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $message['id_message'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header bg-danger text-white">
                      <h5 class="modal-title" id="deleteModalLabel<?= $message['id_message'] ?>">
                        <i class="fas fa-trash me-2"></i>Confirmer la suppression
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                      <p class="mb-0">Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="message_id" value="<?= $message['id_message'] ?>">
                        <button type="submit" name="delete" class="btn btn-danger">
                          Supprimer
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
