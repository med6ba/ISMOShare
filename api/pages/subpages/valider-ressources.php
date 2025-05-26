<?php
include_once '../includes/config.php';
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
        <!-- Titre et bouton -->
        <div
          class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3"
        >
          <div>
            <h3 class="fw-bold mb-2 mt-3">Valider les Ressources</h3>
            <p class="text-muted mb-0"></p>
          </div>
        </div>

        <!-- Table des ressources -->
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Liste des ressources en attente</h5>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
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
                  <tr>
                    <td>Guide HTML</td>
                    <td>Introduction aux balises HTML.</td>
                    <td>1ére année</td>
                    <td>Informatique</td>
                    <td>Développement Web</td>
                    <td>
                      <a href="#" class="btn btn-sm btn-dark"
                        ><i class="fas fa-file-pdf me-1"></i> guide-html.pdf</a
                      >
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <button
                          class="btn btn-success btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#approveModal"
                        >
                          <i class="fas fa-check"></i>
                        </button>
                        <button
                          class="btn btn-danger btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#rejectModal"
                        >
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Cours CSS</td>
                    <td>Feuilles de style en cascade.</td>
                    <td>1ére année</td>
                    <td>Informatique</td>
                    <td>Développement Web</td>
                    <td>
                      <a href="#" class="btn btn-sm btn-dark"
                        ><i class="fas fa-file-pdf me-1"></i> css-cours.pdf</a
                      >
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <button
                          class="btn btn-success btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#approveModal"
                        >
                          <i class="fas fa-check"></i>
                        </button>
                        <button
                          class="btn btn-danger btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#rejectModal"
                        >
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Modale Approve -->
        <div
          class="modal fade"
          id="approveModal"
          tabindex="-1"
          aria-labelledby="approveModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
              <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title">
                  <i class="fas fa-check-circle me-2"></i> Confirmer
                  l'approbation
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body">
                <p class="fs-5">Voulez-vous approuver cette ressource ?</p>
                <div class="d-flex justify-content-end">
                  <button
                    class="btn btn-secondary me-2"
                    data-bs-dismiss="modal"
                  >
                    Annuler
                  </button>
                  <button class="btn btn-success">Approuver</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modale Reject -->
        <div
          class="modal fade"
          id="rejectModal"
          tabindex="-1"
          aria-labelledby="rejectModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
              <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title">
                  <i class="fas fa-times-circle me-2"></i> Confirmer le rejet
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body">
                <p class="fs-5">
                  Êtes-vous sûr de vouloir rejeter cette ressource ? Cette
                  action est irréversible.
                </p>
                <div class="d-flex justify-content-end">
                  <button
                    class="btn btn-secondary me-2"
                    data-bs-dismiss="modal"
                  >
                    Annuler
                  </button>
                  <button class="btn btn-danger">Rejeter</button>
                </div>
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
