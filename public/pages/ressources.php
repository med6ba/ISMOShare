<?php
include_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Ressources</title>
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
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./rechercher.php">
            <i class="fas fa-search"></i>
            Rechercher
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

    <main class="main-content bg-light py-4">
      <div class="container-fluid py-2">
        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
          <h3 class="mb-0 fw-bold">Consultez et partagez des ressources utiles</h3>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-upload"></i>
          </button>
        </div>

        <!-- Resources Grid -->
        <div class="row g-4">
          <!-- Resource Card -->
          <div class="col-md-6 col-lg-4">
            <div class="resource-card shadow-sm">
              <div class="card-body">
                <div class="resource-header">
                  <img src="./../assets/images/ISMO SHARE.png" alt="User" class="resource-avatar">
                  <div class="resource-info">
                    <h5>Ahmadi Ahmad</h5>
                    <div class="d-flex gap-2">
                      <span class="badge bg-primary">Formateur</span>
                      <span class="badge bg-primary">Informatique</span>
                    </div>
                  </div>
                </div>
                <h5 class="resource-title">Bien démarrer avec Bootstrap 5 : Astuces et Bonnes Pratiques</h5>
                <div class="resource-meta">
                  <div class="resource-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>Publié le 15 Mars 2024</span>
                  </div>
                  <div class="resource-meta-item">
                    <i class="fas fa-download"></i>
                    <span>245 téléchargements</span>
                  </div>
                </div>
                <p class="card-text text-muted mb-3">
                  Guide complet pour maîtriser Bootstrap 5, incluant les composants essentiels et les bonnes pratiques de développement.
                </p>
                <div class="resource-actions">
                  <button class="btn btn-info btn-icon" title="Télécharger">
                    <i class="fas fa-download"></i>
                    <span class="ms-1">245</span>
                  </button>
                  <button class="btn btn-primary btn-icon" title="Likes">
                    <i class="fas fa-thumbs-up"></i>
                    <span class="ms-1">24</span>
                  </button>
                  <button class="btn btn-warning btn-icon" title="Commentaires" data-bs-toggle="modal" data-bs-target="#commentsModal">
                    <i class="fas fa-comments"></i>
                    <span class="ms-1">8</span>
                  </button>
                  <button class="btn btn-success btn-icon" title="Modifier" data-bs-toggle="modal" data-bs-target="#editModal">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-danger btn-icon" title="Supprimer" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header bg-primary text-white rounded-top-4">
            <h5 class="modal-title" id="addModalLabel">
              <i class="fas fa-plus me-2"></i>Ajouter une ressource
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="titreAdd" class="form-label">Titre</label>
                <input type="text" class="form-control" id="titreAdd" placeholder="Titre de la ressource">
              </div>
              <div class="mb-3">
                <label for="descriptionAdd" class="form-label">Description</label>
                <textarea class="form-control" id="descriptionAdd" rows="3" placeholder="Description..."></textarea>
              </div>
              <div class="mb-3">
                <label for="anneeSelect" class="form-label">Année</label>
                <select class="form-select" id="anneeSelect" required>
                  <option value="" disabled selected>Choisir une année</option>
                  <option value="1ere">1ère année</option>
                  <option value="2eme">2ème année</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="filiereSelect" class="form-label">Filière</label>
                <select class="form-select" id="filiereSelect" required>
                  <option value="" disabled selected>Choisir une filière</option>
                  <option value="informatique">Informatique</option>
                  <option value="gestion">Gestion</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="fileUpload" class="form-label">Fichier</label>
                <input type="file" class="form-control" id="fileUpload">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-primary">Ajouter</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header bg-success text-white rounded-top-4">
            <h5 class="modal-title" id="editModalLabel">
              <i class="fas fa-edit me-2"></i>Modifier la ressource
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label for="titreEdit" class="form-label">Titre</label>
                <input type="text" class="form-control" id="titreEdit" placeholder="Titre de la ressource">
              </div>
              <div class="mb-3">
                <label for="descriptionEdit" class="form-label">Description</label>
                <textarea class="form-control" id="descriptionEdit" rows="3" placeholder="Description..."></textarea>
              </div>
              <div class="mb-3">
                <label for="anneeEdit" class="form-label">Année</label>
                <select class="form-select" id="anneeEdit" required>
                  <option value="" disabled selected>Choisir une année</option>
                  <option value="1ere">1ère année</option>
                  <option value="2eme">2ème année</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="filiereEdit" class="form-label">Filière</label>
                <select class="form-select" id="filiereEdit" required>
                  <option value="" disabled selected>Choisir une filière</option>
                  <option value="informatique">Informatique</option>
                  <option value="gestion">Gestion</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-success">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header bg-danger text-white rounded-top-4">
            <h5 class="modal-title" id="deleteModalLabel">
              <i class="fas fa-trash me-2"></i>Confirmer la suppression
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">Êtes-vous sûr de vouloir supprimer cette ressource ? Cette action est irréversible.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger">Supprimer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Comments Modal -->
    <div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
          <div class="modal-header bg-warning text-dark rounded-top-4">
            <h5 class="modal-title" id="commentsModalLabel">
              <i class="fas fa-comments me-2"></i>Commentaires
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body bg-light">
            <!-- Existing Comments -->
            <div class="mb-4">
              <!-- Comment 1 -->
              <div class="d-flex align-items-start mb-3">
                <img src="./../assets/images/ISMO SHARE.png" width="40" class="rounded-circle me-3" alt="User" />
                <div class="w-100 position-relative">
                  <div class="bg-white rounded shadow-sm p-3">
                    <strong>Ahmed</strong>
                    <p class="mb-1">Très bonne question, j'utilise aussi Bootstrap 5.</p>
                    <small class="text-muted">Il y a 2 heures</small>
                  </div>
                  <div class="position-absolute top-0 end-0 m-2 d-flex gap-1">
                    <button class="btn btn-outline-warning rounded">
                      <i class="fas fa-star"></i>
                    </button>
                    <button class="btn btn-success rounded" data-bs-toggle="modal" data-bs-target="#replyModal">
                      <i class="fas fa-reply"></i>
                    </button>
                    <button class="btn btn-danger rounded" data-bs-toggle="modal" data-bs-target="#deleteCommentModal">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Comment 2 -->
              <div class="d-flex align-items-start mb-3">
                <img src="./../assets/images/ISMO SHARE.png" width="40" class="rounded-circle me-3" alt="User" />
                <div class="w-100 position-relative">
                  <div class="bg-white rounded shadow-sm p-3">
                    <strong>Fatima</strong>
                    <p class="mb-1">Essaye de lire la doc officielle, elle est bien faite.</p>
                    <small class="text-muted">Il y a 1 heure</small>
                  </div>
                  <div class="position-absolute top-0 end-0 m-2 d-flex gap-1">
                    <button class="btn btn-outline-warning rounded">
                      <i class="fas fa-star"></i>
                    </button>
                    <button class="btn btn-success rounded" data-bs-toggle="modal" data-bs-target="#replyModal">
                      <i class="fas fa-reply"></i>
                    </button>
                    <button class="btn btn-danger rounded" data-bs-toggle="modal" data-bs-target="#deleteCommentModal">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add New Comment -->
            <div class="bg-white rounded p-3 shadow-sm">
              <h6 class="mb-3">Ajouter un commentaire</h6>
              <textarea class="form-control rounded-3 border-1" id="newComment" rows="3" placeholder="Votre commentaire..."></textarea>
              <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-warning rounded px-4">
                  <i class="fas fa-paper-plane me-2"></i>Envoyer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="replyModalLabel">
              <i class="fas fa-reply me-2"></i>Répondre au commentaire
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <textarea class="form-control rounded-3 border-1" rows="3" placeholder="Votre réponse..."></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-success">Répondre</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Comment Modal -->
    <div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-labelledby="deleteCommentModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteCommentModalLabel">
              <i class="fas fa-trash-alt me-2"></i>Supprimer le commentaire
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">Êtes-vous sûr de vouloir supprimer ce commentaire ?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger">Supprimer</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Logout Modal -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
  </body>
</html>
