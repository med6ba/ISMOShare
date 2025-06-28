<?php
include_once '../includes/config.php';
include_once '../includes/notification_functions.php';

// Vérifier si l'utilisateur est admin ou formateur
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['role'] !== 'admin' && $user['role'] !== 'formateur') {
    header("Location: ../dashboard.php");
    exit();
}

// Récupérer le nombre de notifications non lues
$unread_count = getUnreadNotificationsCount($conn, $_SESSION['user_id']);

// Gérer l'approbation d'une ressource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_resource'])) {
    $resource_id = intval($_POST['resource_id']);
    $stmt = $conn->prepare("UPDATE ressource SET statut = 'approuve' WHERE id_ressource = ?");
    $stmt->bind_param("i", $resource_id);
    if ($stmt->execute()) {
        $message = "Ressource approuvée avec succès!";
    } else {
        $error = "Erreur lors de l'approbation de la ressource.";
    }
}

// Gérer le rejet d'une ressource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_resource'])) {
    $resource_id = intval($_POST['resource_id']);
    $stmt = $conn->prepare("UPDATE ressource SET statut = 'rejete' WHERE id_ressource = ?");
    $stmt->bind_param("i", $resource_id);
    if ($stmt->execute()) {
        $message = "Ressource rejetée avec succès!";
    } else {
        $error = "Erreur lors du rejet de la ressource.";
    }
}

// Récupérer les ressources en attente
$sql = "SELECT r.*, 
               u.nom, u.prenom,
               m.nom as module_nom,
               f.nom as filiere_nom
        FROM ressource r 
        INNER JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur 
        LEFT JOIN module m ON r.id_module = m.id_module
        LEFT JOIN filiere f ON r.id_filiere = f.id_filiere
        WHERE r.statut = 'en_attente'
        ORDER BY r.date_ajoute DESC";

$result = $conn->query($sql);
$resources = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Valider les ressources</title>
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
        <!-- Titre et bouton -->
        <div
          class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3"
        >
          <div>
            <h3 class="fw-bold mb-2 mt-3">Valider les Ressources</h3>
            <p class="text-muted mb-0"></p>
          </div>
        </div>

        <?php if (empty($resources)): ?>
          <div class="text-muted text-center alert alert-info">
            <i class="fas fa-folder-open fa-2x mt-3 mb-3 me-2"></i>
            <p>Aucune ressource en attente de validation</p>
          </div>
        <?php else: ?>
          <!-- Table des ressources -->
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-3">Liste des ressources en attente</h5>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Titre</th>
                      <th>Description</th>
                      <th>Année de formation</th>
                      <th>Filière</th>
                      <th>Module</th>
                      <th>Fichier</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($resources as $resource): ?>
                      <tr>
                        <td><?= htmlspecialchars($resource['titre']) ?></td>
                        <td><?= htmlspecialchars($resource['description']) ?></td>
                        <td><?= htmlspecialchars($resource['annee']) ?></td>
                        <td><?= htmlspecialchars($resource['filiere_nom']) ?></td>
                        <td><?= htmlspecialchars($resource['module_nom']) ?></td>
                        <td>
                          <a href="../<?= $resource['fichier'] ?>" class="btn btn-sm btn-dark" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> <?= basename($resource['fichier']) ?>
                          </a>
                        </td>
                        <td>
                          <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#approveModal<?= $resource['id_ressource'] ?>">
                              <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#rejectModal<?= $resource['id_ressource'] ?>">
                              <i class="fas fa-times"></i>
                            </button>
                          </div>
                        </td>
                      </tr>

                      <!-- Modal Approve -->
                      <div class="modal fade" id="approveModal<?= $resource['id_ressource'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content border-0 rounded-4 shadow">
                            <div class="modal-header bg-success text-white rounded-top-4">
                              <h5 class="modal-title">
                                <i class="fas fa-check-circle me-2"></i> Confirmer l'approbation
                              </h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <p class="fs-5">Voulez-vous approuver cette ressource ?</p>
                              <div class="d-flex justify-content-end">
                                <button class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                                <form method="POST" class="d-inline">
                                  <input type="hidden" name="resource_id" value="<?= $resource['id_ressource'] ?>">
                                  <button type="submit" name="approve_resource" class="btn btn-success">Approuver</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Modal Reject -->
                      <div class="modal fade" id="rejectModal<?= $resource['id_ressource'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content border-0 rounded-4 shadow">
                            <div class="modal-header bg-danger text-white rounded-top-4">
                              <h5 class="modal-title">
                                <i class="fas fa-times-circle me-2"></i> Confirmer le rejet
                              </h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <p class="fs-5">Êtes-vous sûr de vouloir rejeter cette ressource ? Cette action est irréversible.</p>
                              <div class="d-flex justify-content-end">
                                <button class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                                <form method="POST" class="d-inline">
                                  <input type="hidden" name="resource_id" value="<?= $resource['id_ressource'] ?>">
                                  <button type="submit" name="reject_resource" class="btn btn-danger">Rejeter</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
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
            <a href="./../logout.php" class="btn btn-danger">Se déconnecter</a>
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
