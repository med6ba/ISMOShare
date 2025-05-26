<?php
session_start();
include_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user data
$stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if user is suspended
if ($user['statut'] === 'suspendu') {
    $_SESSION['error'] = "Votre compte a été suspendu. Veuillez contacter l'administrateur.";
    header("Location: dashboard.php");
    exit();
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $cef_matricule = trim($_POST['cef_matricule']);
    $id_filiere = !empty($_POST['filiere']) ? $_POST['filiere'] : null;
    $bio = trim($_POST['bio']);
    $numero_whatsapp = trim($_POST['numero_whatsapp']);
    $annee_formation = trim($_POST['annee_formation']);

    // Handle profile picture upload
    $photo_profil = $user['photo_profil']; // Keep existing photo by default
    if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
        // Delete old profile picture if exists
        if ($user['photo_profil'] && file_exists('../assets/uploads/profile/' . $user['photo_profil'])) {
            unlink('../assets/uploads/profile/' . $user['photo_profil']);
        }
        $photo_profil = null; // Set to null to remove the photo
    } else if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profilePicture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_dir = '../assets/uploads/profile/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists
                if ($user['photo_profil'] && file_exists($upload_dir . $user['photo_profil'])) {
                    unlink($upload_dir . $user['photo_profil']);
                }
                $photo_profil = $new_filename;
            }
        }
    }

    // Update user profile
    $stmt = $conn->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, cef_matricule = ?, id_filiere = ?, photo_profil = ?, bio = ?, numero_whatsapp = ?, annee_formation = ? WHERE id_utilisateur = ?");
    $stmt->bind_param("ssssissssi", $nom, $prenom, $email, $cef_matricule, $id_filiere, $photo_profil, $bio, $numero_whatsapp, $annee_formation, $user_id);
    
    if ($stmt->execute()) {
        $message = "Profil mis à jour avec succès!";
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Erreur lors de la mise à jour du profil.";
    }
}

// Handle profile deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_profile'])) {
    // Delete profile picture if exists
    if ($user['photo_profil'] && file_exists('../assets/uploads/profile/' . $user['photo_profil'])) {
        unlink('../assets/uploads/profile/' . $user['photo_profil']);
    }
    
    // Delete user from database
    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Destroy session and redirect to login
        session_destroy();
        header("Location: connexion.php");
        exit();
    } else {
        $error = "Erreur lors de la suppression du profil.";
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $old_password = $_POST['oldPassword'];
    $new_password = $_POST['newPassword'];
    $confirm_password = $_POST['confirmPassword'];

    // Verify old password
    if (!password_verify($old_password, $user['mot_de_passe'])) {
        $error = "L'ancien mot de passe est incorrect.";
    }
    // Check if new password matches confirmation
    elseif ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    }
    // Validate new password strength
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $new_password)) {
        $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un symbole.";
    }
    else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $message = "Mot de passe modifié avec succès!";
        } else {
            $error = "Erreur lors de la modification du mot de passe.";
        }
    }
}

// Get all filieres for the dropdown
$filieres = [];
$result = $conn->query("SELECT * FROM filiere ORDER BY nom");
while ($row = $result->fetch_assoc()) {
    $filieres[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Mon profile</title>
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

      :root {
        --primary-color: #3498db;
        --secondary-color: #2c3e50;
        --accent-color: #f39c12;
      }
      body {
        background-color: #f8f9fa;
      }
      .profile-header {
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 2rem;
      }
      .profile-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      }
      .profile-body {
        padding: 2.5rem;
      }
      .info-label {
        font-weight: 600;
        color: #6c757d;
      }
      .info-value {
        font-weight: 500;
      }
      .action-btn {
        border-radius: 50px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s;
      }
      .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }
      .card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
      }
      .modal-content {
        border-radius: 15px;
        border: none;
      }
      .modal-header {
        padding: 1.5rem;
      }
      .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        margin-right: 10px;
        transition: all 0.3s;
      }
      .social-links a:hover {
        background-color: var(--secondary-color);
        transform: translateY(-3px);
      }
      .bio-text {
        font-style: italic;
        color: #6c757d;
        line-height: 1.6;
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
          <a class="nav-link" id="active" href="./profile.php">
            <i class="fas fa-user"></i>
            Mon Profil
          </a>
        </li>
        <li class="nav-item">
          <a
            class="nav-link text-light"
            href="#"
            data-bs-toggle="modal"
            data-bs-target="#logoutModal"
          >
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </a>
        </li>
      </ul>
    </nav>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    <main class="main-content py-4">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <?php if ($message): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>
            <?php if ($error): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <!-- Profile Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
              <div class="card-body text-center p-4">
                <br>
                <img
                  src="<?= $user['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($user['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>"
                  alt="Photo de profil"
                  class="rounded-circle mb-3"
                  style="width: 100px; height: 100px; object-fit: cover;"
                  onerror="this.src='../assets/images/ISMO SHARE.png'"
                />
                <h4 class="mb-1 fw-bold" style="text-transform: uppercase;"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                <span class="badge bg-danger-subtle text-danger mb-3"><?= strtoupper($user['role']) ?></span>

                <br>
                <br>

                <div class="container">
                  <div class="row justify-content-center g-2">
                    <div class="col-lg-4 col-md-12 text-center">
                      <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal" <?php echo $user['statut'] === 'suspendu' ? 'disabled' : ''; ?>>
                        <i class="fas fa-user-edit me-2"></i>Modifier le profil
                      </button>
                    </div>
                    <div class="col-lg-4 col-md-12 text-center">
                      <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal" <?php echo $user['statut'] === 'suspendu' ? 'disabled' : ''; ?>>
                        <i class="fas fa-key me-2"></i>Changer le mot de passe
                      </button>
                    </div>
                    <div class="col-lg-4 col-md-12 text-center">
                      <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteProfileModal" <?php echo $user['statut'] === 'suspendu' ? 'disabled' : ''; ?>>
                        <i class="fas fa-user-times me-2"></i>Supprimer le compte
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- Information Card -->
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body p-4">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light">
                      <div class="mb-1">CEF / Matricule</div>
                      <div class="text-muted"><?= htmlspecialchars($user['cef_matricule']) ?></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light">
                      <div class="mb-1">Filière</div>
                      <div class="text-muted">
                        <?php
                        $stmt = $conn->prepare("SELECT nom FROM filiere WHERE id_filiere = ?");
                        $stmt->bind_param("i", $user['id_filiere']);
                        $stmt->execute();
                        $filiere = $stmt->get_result()->fetch_assoc();
                        echo htmlspecialchars($filiere['nom'] ?? 'Non spécifiée');
                        ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light">
                      <div class="mb-1">Email institutionnel</div>
                      <div class="text-muted"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light">
                      <div class="mb-1">Numéro WhatsApp</div>
                      <div class="text-muted">
                        <?php if (!empty($user['numero_whatsapp'])): ?>
                          <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $user['numero_whatsapp']) ?>" 
                             class="text-decoration-none" 
                             target="_blank">
                            <i class="fab fa-whatsapp text-success me-1"></i>
                            +212 <?= substr(preg_replace('/[^0-9]/', '', $user['numero_whatsapp']), -9) ?>
                          </a>
                        <?php else: ?>
                          <span>Non spécifié</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light">
                      <div class="mb-1">Année de formation</div>
                      <div class="text-muted">
                        <?= !empty($user['annee_formation']) ? htmlspecialchars($user['annee_formation']) : 'Non spécifiée' ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light">
                      <div class="mb-1">Bio</div>
                      <div class="text-muted">
                        <?= !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'Aucune bio disponible' ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-sm">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="editProfileModalLabel">
          <i class="fas fa-user-edit me-2"></i>Modifier le profil
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="row">
            <!-- Photo de profil -->
            <div class="col-md-4 text-center mb-4">
              <label for="profilePicture" class="form-label d-block">Photo de profil</label>
              <br>  
              <img
                src="<?= $user['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($user['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>"
                alt="Photo de profil actuelle"
                class="rounded-circle mb-3"
                style="width: 120px; height: 120px; object-fit: cover"
                id="previewImage"
              />
              <br>
              <br>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('profilePicture').click()">
                <i class="fas fa-camera me-2"></i>Changer
              </button>
              <input type="file" class="d-none" id="profilePicture" name="profilePicture" accept="image/*" onchange="previewProfilePhoto(event)" />
              <?php if ($user['photo_profil']): ?>
              <button type="button" class="btn btn-outline-danger btn-sm ms-2" onclick="deleteProfilePhoto()">
                <i class="fas fa-trash-alt me-2"></i>Supprimer
              </button>
              <?php endif; ?>
            </div>

            <!-- Infos utilisateur -->
            <div class="col-md-8">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nom</label>
                  <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Prénom</label>
                  <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email institutionnel</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="emailPrefix" value="<?= explode('@', $user['email'])[0] ?>" required />
                    <span class="input-group-text">@ofppt-edu.ma</span>
                  </div>
                  <input type="hidden" name="email" id="fullEmail" value="<?= htmlspecialchars($user['email']) ?>" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">CEF / Matricule</label>
                  <input type="text" class="form-control" name="cef_matricule" value="<?= htmlspecialchars($user['cef_matricule']) ?>" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Filière</label>
                  <select class="form-select" name="filiere">
                    <option value="">Sélectionnez une filière</option>
                    <?php foreach ($filieres as $filiere): ?>
                      <option value="<?= $filiere['id_filiere'] ?>" <?= $filiere['id_filiere'] == $user['id_filiere'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($filiere['nom']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Numéro WhatsApp</label>
                  <div class="input-group">
                    <span class="input-group-text">+212</span>
                    <input type="tel" class="form-control" name="numero_whatsapp" 
                           value="<?= !empty($user['numero_whatsapp']) ? substr(preg_replace('/[^0-9]/', '', $user['numero_whatsapp']), -9) : '' ?>" 
                           placeholder="6/7 XX XX XX XX" 
                           pattern="[0-9]{9}"
                           maxlength="9"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Année de formation</label>
                  <select class="form-select" name="annee_formation">
                    <option value="">Sélectionnez une année</option>
                    <option value="1ère année" <?= $user['annee_formation'] == '1ère année' ? 'selected' : '' ?>>1ère année</option>
                    <option value="2ème année" <?= $user['annee_formation'] == '2ème année' ? 'selected' : '' ?>>2ème année</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Bio</label>
                  <textarea class="form-control" name="bio" rows="3" placeholder="Parlez-nous de vous..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" name="update_profile" class="btn btn-primary">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="changePasswordModalLabel">
              <i class="fas fa-key me-2"></i>Modifier le mot de passe
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body p-4">
            <form method="POST" action="">
              <div class="mb-3">
                <label for="oldPassword" class="form-label">Ancien mot de passe</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="oldPassword" name="oldPassword" required />
                  <button class="btn btn-outline-secondary" type="button" id="toggleOldPassword">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
              </div>
              <div class="mb-3">
                <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="newPassword" name="newPassword" required />
                  <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
              </div>
              <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirmer le nouveau mot de passe</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required />
                  <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
              </div>
              <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" name="change_password" class="btn btn-warning">
                  <i class="fas fa-check me-2"></i>Changer
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Profile Modal -->
    <div class="modal fade" id="deleteProfileModal" tabindex="-1" aria-labelledby="deleteProfileModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteProfileModalLabel">
              <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible et toutes vos données seront définitivement supprimées.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <form method="POST" action="" class="d-inline">
              <button type="submit" name="delete_profile" class="btn btn-danger">
                <i class="fas fa-trash-alt me-2"></i>Supprimer définitivement
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="logoutModal"
      tabindex="-1"
      aria-labelledby="logoutModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
          <div class="modal-header bg-danger text-white rounded-top-3">
            <h5 class="modal-title" id="logoutModalLabel">
              <i class="fas fa-sign-out-alt me-2"></i>Confirmer la déconnexion
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Fermer"
            ></button>
          </div>
          <div class="modal-body">
            Êtes-vous sûr de vouloir vous déconnecter ?
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Annuler
            </button>
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

      // Profile picture preview
      function previewProfilePhoto(event) {
        const reader = new FileReader();
        reader.onload = function() {
          const preview = document.getElementById('previewImage');
          preview.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
      }

      // Password toggle functionality
      document.addEventListener("DOMContentLoaded", function() {
        const togglePassword = (buttonId, inputId) => {
          const button = document.getElementById(buttonId);
          const input = document.getElementById(inputId);

          if (button && input) {
            button.addEventListener("click", () => {
              const type = input.getAttribute("type") === "password" ? "text" : "password";
              input.setAttribute("type", type);

              // Toggle icon
              const icon = button.querySelector("i");
              icon.classList.toggle("fa-eye");
              icon.classList.toggle("fa-eye-slash");
            });
          }
        };

        togglePassword("toggleOldPassword", "oldPassword");
        togglePassword("toggleNewPassword", "newPassword");
        togglePassword("toggleConfirmPassword", "confirmPassword");
      });

      // Email validation
      document.getElementById('emailPrefix').addEventListener('input', function(e) {
        const prefix = e.target.value;
        const fullEmail = prefix + '@ofppt-edu.ma';
        document.getElementById('fullEmail').value = fullEmail;
      });

      function deleteProfilePhoto() {
        if (confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?')) {
          // Create a hidden input to indicate photo deletion
          const deleteInput = document.createElement('input');
          deleteInput.type = 'hidden';
          deleteInput.name = 'delete_photo';
          deleteInput.value = '1';
          document.querySelector('form').appendChild(deleteInput);
          
          // Update preview to default image
          document.getElementById('previewImage').src = '../assets/images/ISMO SHARE.png';
        }
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>

