<?php
include_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Forum</title>
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
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
      }

      .btn-icon {
        width: auto;
        min-width: 40px;
        height: 40px;
        padding: 0 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
        gap: 0.5rem;
      }

      .btn-icon .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        background-color: #f8f9fa;
        color: #343a40;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

      @media (max-width: 576px) {
        .resource-actions {
          justify-content: center;
        }
        
        .btn-icon {
          flex: 1;
          min-width: 0;
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
          <a class="nav-link" href="./dashboard.php">
            <i class="fa-solid fa-chart-line"></i>
            Tableau de bord
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="active" href="./forum.php">
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
        <!-- Create Forum Link -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="mb-0 fw-bold">
            Partagez vos questions, idées et discussions
          </h3>
          <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#createForumModal"
          >
            <i class="fas fa-plus-circle"></i>
          </button>
        </div>

        <!-- Forum Posts Grid -->
        <div class="row g-4">
          <!-- Forum Post 1 -->
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
                <h5 class="resource-title">Comment bien débuter avec Bootstrap 5 ?</h5>
                <div class="resource-meta">
                  <div class="resource-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>Publié le 15 Mars 2024</span>
                  </div>
                  <div class="resource-meta-item">
                    <i class="fas fa-comments"></i>
                    <span>8 réponses</span>
                  </div>
                </div>
                <p class="card-text text-muted mb-3">
                  Je débute avec Bootstrap 5 et j'aimerais avoir des conseils pour bien commencer. Quelles sont les bonnes pratiques à suivre ?
                </p>
                <div class="resource-actions">
                  <button class="btn btn-primary btn-icon" title="Likes">
                    <i class="fas fa-thumbs-up"></i>
                    <span class="ms-1">24</span>
                  </button>
                  <button class="btn btn-warning btn-icon" title="Commentaires" data-bs-toggle="modal" data-bs-target="#commentsModal">
                    <i class="fas fa-comments"></i>
                    <span class="ms-1">8</span>
                  </button>
                  <button class="btn btn-success btn-icon" title="Modifier" data-bs-toggle="modal" data-bs-target="#editForumModal">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-danger btn-icon" title="Supprimer" data-bs-toggle="modal" data-bs-target="#deleteForumModal">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Comments Modal -->
        <div
          class="modal fade"
          id="commentsModal"
          tabindex="-1"
          aria-labelledby="commentsModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
              <div class="modal-header bg-warning text-dark rounded-top-4">
                <h5 class="modal-title" id="commentsModalLabel">
                  <i class="fas fa-comments me-2"></i>Commentaires
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-dark"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body bg-light">
                <!-- Existing Comments -->
                <div class="mb-4">
                  <!-- Comment 1 -->
                  <div class="d-flex align-items-start mb-3">
                    <img
                      src="./../assets/images/ISMO SHARE.png"
                      width="40"
                      class="rounded-circle me-3"
                      alt="User"
                    />
                    <div class="w-100 position-relative">
                      <div class="bg-white rounded shadow-sm p-3">
                        <strong>Ahmed</strong>
                        <p class="mb-1">
                          Très bonne question, j'utilise aussi Bootstrap 5.
                        </p>
                        <small class="text-muted">Il y a 2 heures</small>
                      </div>
                      <div
                        class="position-absolute top-0 end-0 m-2 d-flex gap-1"
                      >
                        <button class="btn btn-outline-warning rounded">
                          <i class="fas fa-star"></i>
                        </button>
                        <button class="btn btn-primary btn-sm">
                          <i class="fas fa-thumbs-up me-1"></i>24 Likes
                        </button>
                        <button
                          class="btn btn-success rounded"
                          data-bs-toggle="modal"
                          data-bs-target="#replyModal"
                        >
                          <i class="fas fa-reply"></i>
                        </button>
                        <button
                          class="btn btn-danger rounded"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteCommentModal"
                        >
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Comment 2 -->
                  <div class="d-flex align-items-start mb-3">
                    <img
                      src="./../assets/images/ISMO SHARE.png"
                      width="40"
                      class="rounded-circle me-3"
                      alt="User"
                    />
                    <div class="w-100 position-relative">
                      <div class="bg-white rounded shadow-sm p-3">
                        <strong>Fatima</strong>
                        <p class="mb-1">
                          Essaye de lire la doc officielle, elle est bien faite.
                        </p>
                        <small class="text-muted">Il y a 1 heure</small>
                      </div>
                      <div
                        class="position-absolute top-0 end-0 m-2 d-flex gap-1"
                      >
                        <button class="btn btn-outline-warning rounded">
                          <i class="fas fa-star"></i>
                        </button>
                        <button class="btn btn-primary btn-sm">
                          <i class="fas fa-thumbs-up me-1"></i>24 Likes
                        </button>
                        <button
                          class="btn btn-success rounded"
                          data-bs-toggle="modal"
                          data-bs-target="#replyModal"
                        >
                          <i class="fas fa-reply"></i>
                        </button>
                        <button
                          class="btn btn-danger rounded"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteCommentModal"
                        >
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Add New Comment -->
                <div class="bg-white rounded p-3 shadow-sm">
                  <h6 class="mb-3">Ajouter un commentaire</h6>
                  <textarea
                    class="form-control rounded-3 border-1"
                    id="newComment"
                    rows="3"
                    placeholder="Votre commentaire..."
                  ></textarea>
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
        <div
          class="modal fade"
          id="replyModal"
          tabindex="-1"
          aria-labelledby="replyModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="replyModalLabel">
                  <i class="fas fa-reply me-2"></i>Répondre au commentaire
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body">
                <textarea
                  class="form-control rounded-3 border-1"
                  rows="3"
                  placeholder="Votre réponse..."
                ></textarea>
              </div>
              <div class="modal-footer">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Annuler
                </button>
                <button type="button" class="btn btn-success">Répondre</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Delete Comment Modal -->
        <div
          class="modal fade"
          id="deleteCommentModal"
          tabindex="-1"
          aria-labelledby="deleteCommentModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCommentModalLabel">
                  <i class="fas fa-trash-alt me-2"></i>Supprimer le commentaire
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body">
                <p class="mb-0">
                  Êtes-vous sûr de vouloir supprimer ce commentaire ?
                </p>
              </div>
              <div class="modal-footer">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Annuler
                </button>
                <button type="button" class="btn btn-danger">Supprimer</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Create Forum Modal -->
        <div
          class="modal fade"
          id="createForumModal"
          tabindex="-1"
          aria-labelledby="createForumModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
              <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title" id="createForumModalLabel">
                  <i class="fas fa-plus-circle me-2"></i>Créer une nouvelle
                  discussion
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body bg-light">
                <form>
                  <div class="mb-3">
                    <label for="forumTitle" class="form-label">Titre</label>
                    <input
                      type="text"
                      class="form-control rounded-3"
                      id="forumTitle"
                      placeholder="Entrez le titre de la discussion"
                      required
                    />
                  </div>
                  <div class="mb-3">
                    <label for="forumContent" class="form-label">Contenu</label>
                    <textarea
                      class="form-control rounded-3"
                      id="forumContent"
                      rows="5"
                      placeholder="Posez votre question, partagez une idée ou un retour d'expérience..."
                      required
                    ></textarea>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button
                      type="button"
                      class="btn btn-secondary me-2"
                      data-bs-dismiss="modal"
                    >
                      Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-paper-plane me-2"></i>Publier
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Forum Modal -->
        <div
          class="modal fade"
          id="editForumModal"
          tabindex="-1"
          aria-labelledby="editForumModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
              <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title" id="editForumModalLabel">
                  <i class="fas fa-edit me-2"></i>Modifier la discussion
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body bg-light">
                <form>
                  <div class="mb-3">
                    <label for="editForumTitle" class="form-label">Titre</label>
                    <input
                      type="text"
                      class="form-control rounded-3"
                      id="editForumTitle"
                      placeholder="Modifier le titre de la discussion"
                      required
                    />
                  </div>
                  <div class="mb-3">
                    <label for="editForumContent" class="form-label"
                      >Contenu</label
                    >
                    <textarea
                      class="form-control rounded-3"
                      id="editForumContent"
                      rows="5"
                      placeholder="Modifier le contenu de la discussion"
                      required
                    ></textarea>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button
                      type="button"
                      class="btn btn-secondary me-2"
                      data-bs-dismiss="modal"
                    >
                      Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                      <i class="fas fa-check me-2"></i>Enregistrer
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Delete Forum Modal -->
        <div
          class="modal fade"
          id="deleteForumModal"
          tabindex="-1"
          aria-labelledby="deleteForumModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
              <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title" id="deleteForumModalLabel">
                  <i class="fas fa-trash-alt me-2"></i>Confirmer la suppression
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Fermer"
                ></button>
              </div>
              <div class="modal-body bg-light">
                <p class="fs-5 mb-4">
                  Êtes-vous sûr de vouloir supprimer cette discussion ? Cette
                  action est irréversible.
                </p>
                <div class="d-flex justify-content-end">
                  <button
                    type="button"
                    class="btn btn-secondary me-2"
                    data-bs-dismiss="modal"
                  >
                    Annuler
                  </button>
                  <button type="button" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Supprimer
                  </button>
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
