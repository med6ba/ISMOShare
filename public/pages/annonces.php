<?php
include_once 'includes/config.php';
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

    <!-- Main Content -->
    <main class="main-content bg-light py-4">
      <div class="container-fluid py-2">
        <!-- Add Announcement Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="mb-0 fw-bold">
            Restez informé des dernières mises à jour
          </h3>
          <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalAdd"
          >
            <i class="fa-solid fa-calendar-plus"></i>
          </button>
        </div>

        <!-- Announcement Card Example -->
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
              <h5 class="resource-title">Séance de révision ce vendredi</h5>
              <div class="resource-meta">
                <div class="resource-meta-item">
                  <i class="fas fa-calendar"></i>
                  <span>Publié le 2 mai 2025</span>
                </div>
                <div class="resource-meta-item">
                  <i class="fas fa-clock"></i>
                  <span>Expire le 5 mai 2025</span>
                </div>
              </div>
              <p class="card-text text-muted mb-3">
                Une séance de révision aura lieu ce vendredi à 14h au labo 2 pour le module d'intégration web. Soyez à l'heure !
              </p>
              <div class="resource-actions">
                <button class="btn btn-primary btn-icon position-relative" title="Archiver" data-bs-toggle="modal" data-bs-target="#modalArchive">
                  <i class="fas fa-archive"></i>
                </button>
                <button class="btn btn-success btn-icon" title="Modifier" data-bs-toggle="modal" data-bs-target="#modalEdit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-icon" title="Supprimer" data-bs-toggle="modal" data-bs-target="#modalDelete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Modal -->
      <div class="modal fade" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Ajouter une annonce</h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
              ></button>
            </div>
            <div class="modal-body">
              <form>
                <div class="mb-3">
                  <label class="form-label">Titre</label>
                  <input type="text" class="form-control" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Contenu</label>
                  <textarea class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Date d'expiration</label>
                  <input type="date" class="form-control" />
                </div>
                <button type="submit" class="btn btn-primary w-100">
                  Publier
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Archive Modal -->
      <div class="modal fade" id="modalArchive" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Archiver l'annonce</h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
              ></button>
            </div>
            <div class="modal-body">
              Voulez-vous vraiment archiver cette annonce ?
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Annuler
              </button>
              <button class="btn btn-primary">Archiver</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">Modifier l'annonce</h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
              ></button>
            </div>
            <div class="modal-body">
              <form>
                <div class="mb-3">
                  <label class="form-label">Titre</label>
                  <input
                    type="text"
                    class="form-control"
                    value="Séance de révision ce vendredi"
                  />
                </div>
                <div class="mb-3">
                  <label class="form-label">Contenu</label>
                  <textarea class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Date d'expiration</label>
                  <input type="date" class="form-control" value="2025-05-05" />
                </div>
                <button type="submit" class="btn btn-success w-100">
                  Enregistrer
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title">Supprimer l'annonce</h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
              ></button>
            </div>
            <div class="modal-body">
              Cette action est irréversible. Voulez-vous continuer ?
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Annuler
              </button>
              <button class="btn btn-danger">Supprimer</button>
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
