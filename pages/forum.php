<?php
session_start();
extract($_POST);
include_once 'includes/config.php';
include_once 'includes/notification_functions.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();//get_result() pour recuperer toutes les reponses
$user = $result->fetch_assoc();
if ($user['statut'] === 'suspendu') {
    $_SESSION['error'] = "Votre compte a été suspendu. Veuillez contacter l'administrateur.";
    header("Location: dashboard.php");
    exit();
}
$is_admin = ($user['role'] === 'admin');
$is_formateur = ($user['role'] === 'formateur');
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_like'])) {
    $post_id = intval($post_id);
    // Vérifier si l'utilisateur a déjà liké ce post
    $stmt = $conn->prepare("SELECT id_like FROM likes_forum WHERE id_sujet = ? AND id_utilisateur = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Si l'utilisateur a déjà liké, supprimer le like
        $stmt = $conn->prepare("DELETE FROM likes_forum WHERE id_sujet = ? AND id_utilisateur = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
    } else {
        // Si l'utilisateur n'a pas fait like on ajoute le like dans la bd
        $stmt = $conn->prepare("INSERT INTO likes_forum (id_sujet, id_utilisateur) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_post'])) {
    $titre = trim($titre);
    $contenu = trim($contenu);
    if (!empty($titre) && !empty($contenu)) {
        if ($is_admin || $is_formateur) {
            $est_valide = 1;
        } else {
            $est_valide = 0;
        }
        //Une transaction c'est l'ensemlble des opérations SQL qui sont exécutés ensemble soit tout réussit sinon une erreur sera produite
        $conn->begin_transaction();
        try {//insertion du post
            $stmt = $conn->prepare("INSERT INTO reponseforum (titre, contenu, date_creation, est_valide) VALUES (?, ?, NOW(), ?)");
            $stmt->bind_param("ssi", $titre, $contenu, $est_valide);
            if ($stmt->execute()) {
                $post_id = $conn->insert_id;
                // Insérer la relation dans la table reponse
                $stmt = $conn->prepare("INSERT INTO reponse (id_sujet, id_utilisateur) VALUES (?, ?)");
                $stmt->bind_param("ii", $post_id, $user_id);
                if ($stmt->execute()) {
                  //commit()pour valider et enregistrer les changements
                    $conn->commit();
                    //$can_manage_all variable déclarée en haut soit admin soit formateur
                    if ($is_admin || $is_formateur) {
                      $message = "Post publié avec succès!";
                  } else {
                      $message = "Post soumis avec succès! Il sera visible après validation.";
                  }
                } else {
                    throw new Exception("Erreur lors de la création de la relation utilisateur-post.");
                }
            } else {
                throw new Exception("Erreur lors de la publication du post.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            //rollback()est une fonction qui permet d'annuler tout et de revenir à l'état initial
            $error = $e->getMessage();
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
//La modification du post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_post'])) {
    $post_id = intval($post_id);
    $titre = trim($titre);
    $contenu = trim($contenu);
    if (!empty($categorie)) {
      $id_categorie = $categorie;
  } else {
      $id_categorie = null;
  }
    // Vérifier si l'utilisateur est admin/formateur ou propriétaire du post
    $stmt = $conn->prepare("SELECT r.id_utilisateur FROM reponseforum rf  INNER JOIN reponse r ON rf.id_sujet = r.id_sujet WHERE rf.id_sujet = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    if (($is_admin||$is_formateur) || $post['id_utilisateur'] == $user_id) {
        $stmt = $conn->prepare("UPDATE reponseforum SET titre = ?, contenu = ?, id_forum = ? WHERE id_sujet = ?");
        $stmt->bind_param("ssii", $titre, $contenu, $id_categorie, $post_id);
        if ($stmt->execute()) {
            $message = "Post modifié avec succès!";
        } else {
            $error = "Erreur lors de la modification du post.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour modifier ce post.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
//pour supprimer le post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_post'])) {
  $post_id = intval($post_id);
    // Vérifier si l'utilisateur est admin/formateur ou celui qui a écrit le post
    $stmt = $conn->prepare("SELECT r.id_utilisateur, rf.est_valide FROM reponseforum rf INNER JOIN reponse r ON rf.id_sujet = r.id_sujet  WHERE rf.id_sujet = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    if (($is_admin||$is_formateur) || $post['id_utilisateur'] == $user_id) {
        $stmt = $conn->prepare("DELETE FROM reponseforum WHERE id_sujet = ?");
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute()) {
            $message = "Post supprimé avec succès!";
        } else {
            $error = "Erreur lors de la suppression du post.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour supprimer ce post.";
    }
    header("Location:".$_SERVER['PHP_SELF']);
    exit();
}
//pour les réponses au post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['validate_post'])) {
  $post_id = intval($post_id);
    $action = $_POST['action']; // 'approuve' ou 'rejete'
    if ($is_admin||$is_formateur) {
      if ($action === 'approuve') {
        $est_valide = 1;
    } else {
        $est_valide = 0;
    }
        $stmt = $conn->prepare("UPDATE reponseforum SET est_valide = ? WHERE id_sujet = ?");
        $stmt->bind_param("ii", $est_valide, $post_id);
        if ($stmt->execute()) {
          if ($action === 'approuve') {
              $message = "Post approuvé avec succès!";
          } else {
              $message = "Post rejeté avec succès!";
          }
      } else {
          $error = "Erreur lors de la modification du statut du post.";
      }
    } else {
        $error = "Vous n'avez pas les droits pour effectuer cette action.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $post_id = intval($post_id);
    $comment = trim($comment);
    if (!empty($comment)) {
        // Si l'utilisateur est admin ou formateur le commentaire est approuvé 
        if ($is_admin || $is_formateur) {
          $statut = 'approuve';
      } else {
          $statut = 'en_attente';
      }
        $stmt = $conn->prepare("INSERT INTO commentaire_forum (id_sujet, id_utilisateur, contenu, date_commentaire, statut) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("iiss", $post_id, $user_id, $comment, $statut);
        if ($stmt->execute()) {
            // Récupérer l'ID de l'utilisateur qui a créé le post
            $stmt = $conn->prepare("SELECT r.id_utilisateur FROM reponseforum rf INNER JOIN reponse r ON rf.id_sujet = r.id_sujet 
                                  WHERE rf.id_sujet = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $post = $result->fetch_assoc();
            // Si le commentateur n'est pas le créateur du post, créer une notification elle est déja déclaré dans la page notifaction_functions.php
            if ($post['id_utilisateur'] != $user_id) {
                $message = "a commenté votre post";
                addNotification($conn, $post['id_utilisateur'], 'forum_comment', $post_id, $message);
            }
            if ($is_admin || $is_formateur) {
              $message = "Commentaire ajouté avec succès!";
          } else {
              $message = "Commentaire soumis avec succès! Il sera visible après validation.";
          }
        } else {
            $error = "Erreur lors de l'ajout du commentaire.";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
//supprimer un commentaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_comment'])) {
    $comment_id = intval($comment_id);
    // Vérifier si l'utilisateur est admin/formateur ou propriétaire du commentaire
    $stmt = $conn->prepare("SELECT id_utilisateur FROM commentaire_forum WHERE id_commentaire = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    if (($is_admin||$is_formateur) || $comment['id_utilisateur'] == $user_id) {
        $stmt = $conn->prepare("DELETE FROM commentaire_forum WHERE id_commentaire = ?");
        $stmt->bind_param("i", $comment_id);
        if ($stmt->execute()) {
            $message = "Commentaire supprimé avec succès!";
        } else {
            $error = "Erreur lors de la suppression du commentaire.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour supprimer ce commentaire.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['validate_comment'])) {
  $comment_id = intval($comment_id);
    $action = $_POST['action']; // 'approuve' ou 'rejete'
    if (($is_admin||$is_formateur)) {
      if ($action == 'approuve') {
        $statut = 'approuve';
    } else {
        $statut = 'rejete';
    }
        $stmt = $conn->prepare("UPDATE commentaire_forum SET statut = ? WHERE id_commentaire = ?");
        $stmt->bind_param("si", $statut, $comment_id);
        if ($stmt->execute()) {
          if ($action === 'approuve') {
            $message = "Commentaire approuvé avec succès!";
        } else {
            $message = "Commentaire rejeté avec succès!";
        }
        } else {
            $error = "Erreur lors de la modification du statut du commentaire.";
        }
    } else {
        $error = "Vous n'avez pas les droits pour effectuer cette action.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_comment_like'])) {
  $comment_id = intval($comment_id);
    // Vérifier si l'utilisateur a déjà liké ce commentaire
    $stmt = $conn->prepare("SELECT id_like FROM likes_commentaire_forum WHERE id_commentaire = ? AND id_utilisateur = ?");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Si l'utilisateur a déjà liké supprimer le like
        $stmt = $conn->prepare("DELETE FROM likes_commentaire_forum WHERE id_commentaire = ? AND id_utilisateur = ?");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
    } else {
        // Si l'utilisateur n'a pas encore like ajouter le like
        $stmt = $conn->prepare("INSERT INTO likes_commentaire_forum (id_commentaire, id_utilisateur) VALUES (?, ?)");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
// Gestion de la meilleure réponse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_best_answer'])) {
  if ($user['role'] === 'admin' || $user['role'] === 'formateur') {
    $commentId = intval($mark_best_answer);    
    // Vérifier si le commentaire est déjà marqué comme meilleure réponse
    $stmt = $conn->prepare("SELECT est_meilleure_reponse FROM commentaire_forum WHERE id_commentaire = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    if ($comment['est_meilleure_reponse']) {
      // Si c'est déjà la meilleure réponse retirer
      $stmt = $conn->prepare("UPDATE commentaire_forum SET est_meilleure_reponse = 0 WHERE id_commentaire = ?");
      $stmt->bind_param("i", $commentId);
      if ($stmt->execute()) {
        echo "removed";
      } else {
        echo "error";
      }
    } else {
      // Sinon, mettre à jour le statut de tous les commentaires et marquer celui-ci comme meilleure réponse
      $stmt = $conn->prepare("UPDATE commentaire_forum SET est_meilleure_reponse = 0 WHERE id_sujet = ?");
      $stmt->bind_param("i", $subjectId);
      $stmt->execute();
      
      $stmt = $conn->prepare("UPDATE commentaire_forum SET est_meilleure_reponse = 1 WHERE id_commentaire = ?");
      $stmt->bind_param("i", $commentId);
      
      if ($stmt->execute()) {
        echo "success";
      } else {
        echo "error";
      }
    }
    exit;
  }
}
// Récupérer les catégories pour le dropdown
$categories = [];
$result = $conn->query("SELECT id_forum, titre_categorie FROM forum ORDER BY titre_categorie");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
// Récupérer le nombre de notifications non lues
$unread_count = getUnreadNotificationsCount($conn, $user_id);
// Construire la requête SQL de base
$sql = "SELECT rf.*, u.nom, u.prenom, u.photo_profil, u.numero_whatsapp, u.role, r.id_utilisateur FROM reponseforum rf INNER JOIN reponse r ON rf.id_sujet = r.id_sujet
        INNER JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur WHERE 1=1";
$where_conditions = [];
$params = [];
$types = "";
// Filtrer par recherche
if (!empty($_GET['search'])) {
    //pour récupérer la valeur envoyée par l'utilisateur % n'importe quoi avant et n'importe quoi aprè
    $search = "%" . $_GET['search'] . "%";
    $where_conditions[] = "(rf.titre LIKE ? OR rf.contenu LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}
// Si l'utilisateur n'est pas admin ou formateur, ne montrer que les posts approuvés
if (!($is_admin||$is_formateur)) {
    $where_conditions[] = "(rf.est_valide = 1 OR r.id_utilisateur = ?)";
    $params[] = $user_id;
    $types .= "i";
}
// Ajouter les conditions WHERE à la requête
if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}
// Trier par date de création décroissante
$sql .= " ORDER BY rf.date_creation DESC";
try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête: " . $conn->error);
    }
    if (!empty($params)) {
        if (!$stmt->bind_param($types, ...$params)) {
            throw new Exception("Erreur de liaison des paramètres: " . $stmt->error);
        }
    }
    if (!$stmt->execute()) {
        throw new Exception("Erreur d'exécution de la requête: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Erreur lors de la récupération des résultats: " . $stmt->error);
    }
    $posts = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Une erreur est survenue lors de la récupération des posts: " . $e->getMessage();
    error_log("Erreur SQL dans forum.php: " . $e->getMessage());
    $posts = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISMOShare | Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="./../assets/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
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

      .post-card {
        background: #ffffff;
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
      }

      .post-card .card-body {
        padding: 0;
      }

      .post-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
      }

      .post-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 1rem;
        object-fit: cover;
      }

      .post-info {
        flex: 1;
      }

      .post-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 1rem;
      }

      .post-meta {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
      }

      .post-meta-item {
        display: flex;
        align-items: center;
        color: #6c757d;
        font-size: 0.875rem;
      }

      .post-meta-item i {
        margin-right: 0.5rem;
      }

      .post-actions {
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

      .filter-section {
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      }

      .user-avatar-link {
        text-decoration: none;
        cursor: pointer;
        display: block;
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
        <button class="navbar-toggler text-dark d-md-none" type="button" id="sidebarToggle" style="outline: none; box-shadow: none">
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
            <?php
              if ($unread_count > 0) {
                  echo '<span class="badge bg-danger ms-2">' . $unread_count . '</span>';
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
    <div id="overlay" class="overlay"></div>
    <main class="main-content">
    <div class="container-fluid mt-2">
      <?php
      if ($message) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
      }
      ?>
    </div>
      <?php
        if ($error) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($error);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
      ?>
        <div class="page-header d-flex justify-content-between align-items-center">
          <div>
            <h1 class="page-title">Partagez vos questions et discutez avec la communauté</h1>
                  </div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPostModal">
            <i class="fas fa-plus"></i>
          </button>
                </div>
        <div class="filters mb-4">
            <form method="GET" class="row g-3">
                <div class="col-12">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher dans les posts..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Rechercher
                        </button>
                        <?php
                            if (!empty($_GET['search'])) {
                                echo '<a href="?page=forum" class="btn btn-outline-secondary">';
                                echo '<i class="fas fa-times me-2"></i>Effacer les filtres';
                                echo '</a>';
                            }
                        ?>
                  </div>
                  </div>
            </form>
                </div>
        <div class="row">
          <?php if (empty($posts)): ?>
            <div class="col-12">
              <div class="text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Aucun post trouvé</h4>
                <p class="text-muted">Soyez le premier à créer un post !</p>
                </div>
              </div>
          <?php else: ?>
            <?php foreach ($posts as $post): ?>
              <div class="col-md-6">
                <div class="post-card">
                  <div class="post-header">
                    <div class="d-flex align-items-center">
                        <img src="<?= $post['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($post['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>" 
                             alt="Photo de profil" 
                             class="post-avatar"
                             style="cursor: pointer;"
                             onclick="showUserInfo(<?= htmlspecialchars(json_encode([
                                 'id' => $post['id_utilisateur'],
                                 'nom' => $post['nom'],
                                 'prenom' => $post['prenom'],
                                 'photo' => $post['photo_profil'],
                                 'role' => $post['role'],
                                 'whatsapp' => $post['numero_whatsapp'] ? '0' . $post['numero_whatsapp'] : '0'
                             ])) ?>)">
                        <div class="post-info">
                            <h6 class="mb-0"><?= htmlspecialchars($post['prenom'] . ' ' . $post['nom']) ?></h6>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($post['date_creation'])) ?></small>
            </div>
          </div>
        </div>

                  <h5 class="post-title"><?= htmlspecialchars($post['titre']) ?></h5>
                  <p class="text-muted"><?= nl2br(htmlspecialchars($post['contenu'])) ?></p>

                  <div class="post-meta">
                    <?php
                      if ($post['est_valide'] === 0) {
                          echo '<div class="post-meta-item">';
                          echo '<i class="fas fa-clock me-2"></i>';
                          echo '<span class="badge bg-warning">En attente</span>';
                          echo '</div>';
                      }
                    ?>
                </div>
                  <div class="post-actions">
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="post_id" value="<?= $post['id_sujet'] ?>">
                      <?php
                      // Vérifier si l'utilisateur a liké ce post
                      $stmt = $conn->prepare("SELECT id_like FROM likes_forum WHERE id_sujet = ? AND id_utilisateur = ?");
                      $stmt->bind_param("ii", $post['id_sujet'], $user_id);
                      $stmt->execute();
                      $has_liked = $stmt->get_result()->num_rows > 0;
                      // Compter le nombre total des likes
                      $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes_forum WHERE id_sujet = ?");
                      $stmt->bind_param("i", $post['id_sujet']);
                      $stmt->execute();
                      $likes_count = $stmt->get_result()->fetch_assoc()['count'];
                      ?>
                     <?php
                          if ($has_liked) {
                              $btn_class = 'btn-danger';
                          } else {
                              $btn_class = 'btn-outline-danger';
                          }
                    ?>
                    <button type="submit" name="toggle_like" class="btn <?= $btn_class ?> btn-sm">
                        <i class="fas fa-heart me-2"></i>J'aime
                    <?php
                        if ($has_liked && $likes_count > 0) {
                            echo "<span>($likes_count)</span>";
                        }
                    ?>
                        </button>
                    </form>
                    <?php
                    // Compter le nombre de commentaires pour ce post
                    $stmt = $conn->prepare("SELECT COUNT(*) as count  FROM commentaire_forum WHERE id_sujet = ? AND (statut = 'approuve' OR id_utilisateur = ? OR ? = 1) ");
                    if ($is_admin || $is_formateur) {
                      $can_see_all = 1;
                  } else {
                      $can_see_all = 0;
                  }
                    $stmt->bind_param("iii", $post['id_sujet'], $user_id, $can_see_all);
                    $stmt->execute();
                    $comments_count = $stmt->get_result()->fetch_assoc()['count'];
                    ?>
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#commentsModal<?= $post['id_sujet'] ?>">
                        <i class="fas fa-comments me-2"></i>Commentaires
                        <?php
                            if ($comments_count > 0) {
                                echo "<span>($comments_count)</span>";
                            }
                          ?>
                        </button>
                        <?php 
                            if (($is_admin || $is_formateur) && $post['est_valide'] === 0) { ?>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="post_id" value="<?= $post['id_sujet'] ?>">
                                <input type="hidden" name="action" value="approuve">
                                <button type="submit" name="validate_post" class="btn btn-success btn-sm">
                                  <i class="fas fa-check me-2"></i>Approuver
                                </button>
                              </form>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="post_id" value="<?= $post['id_sujet'] ?>">
                                <input type="hidden" name="action" value="rejete">
                                <button type="submit" name="validate_post" class="btn btn-danger btn-sm">
                                  <i class="fas fa-times me-2"></i>Rejeter
                                </button>
                              </form>
                            <?php 
                            } 
                        ?>

              <?php 
                  if (($is_admin || $is_formateur) || $post['id_utilisateur'] == $user_id) { ?>
                    <button class="btn btn-warning btn-sm" 
                        data-bs-toggle="modal"
                        data-bs-target="#editPostModal<?= $post['id_sujet'] ?>">
                      <i class="fas fa-edit me-2"></i>Modifier
                    </button>
                    <button class="btn btn-danger btn-sm" 
                        data-bs-toggle="modal"
                        data-bs-target="#deletePostModal<?= $post['id_sujet'] ?>">
                      <i class="fas fa-trash-alt me-2"></i>Supprimer
                    </button>
                  <?php 
                  } 
              ?>

                      </div>
                  <?php if (($is_admin||$is_formateur)|| $post['id_utilisateur'] == $user_id): ?>
                    <div class="modal fade" id="editPostModal<?= $post['id_sujet'] ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">
                              <i class="fas fa-edit me-2"></i>Modifier le post
                            </h5>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                    </div>
                          <div class="modal-body">
                            <form method="POST">
                              <input type="hidden" name="post_id" value="<?= $post['id_sujet'] ?>">
                              <div class="mb-3">
                                <label for="titre" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="titre" name="titre" 
                                       value="<?= htmlspecialchars($post['titre']) ?>" required>
                  </div>
                              <div class="mb-3">
                                <label for="contenu" class="form-label">Contenu</label>
                                <textarea class="form-control" id="contenu" name="contenu" 
                                          rows="3" required><?= htmlspecialchars($post['contenu']) ?></textarea>
                </div>
                              <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" name="edit_post" class="btn btn-warning">
                                  <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                  </div>
                            </form>
              </div>
            </div>
          </div>
        </div>
                    <div class="modal fade" id="deletePostModal<?= $post['id_sujet'] ?>" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                              <i class="fas fa-trash-alt me-2"></i>Confirmer la suppression
                </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                            <p>Êtes-vous sûr de vouloir supprimer ce post ? Cette action est irréversible.</p>
              </div>
              <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <form method="POST" class="d-inline">
                              <input type="hidden" name="post_id" value="<?= $post['id_sujet'] ?>">
                              <button type="submit" name="delete_post" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                </button>
                            </form>
              </div>
            </div>
          </div>
        </div>
                  <?php endif; ?>
                  <div class="modal fade" id="commentsModal<?= $post['id_sujet'] ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered modal-lg">
                          <div class="modal-content">
                              <div class="modal-header bg-info text-dark">
                                  <h5 class="modal-title">
                                      <i class="fas fa-comments me-2"></i>Commentaires
                </h5>
                                  <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                                  <div class="comments-list mb-4">
                                      <?php
                                      $stmt = $conn->prepare("
                                          SELECT c.*, u.nom, u.prenom, u.photo_profil, u.role, u.numero_whatsapp,
                                                 c.est_meilleure_reponse,
                                                 (SELECT COUNT(*) FROM likes_commentaire_forum WHERE id_commentaire = c.id_commentaire) as likes_count,
                                                 (SELECT COUNT(*) FROM likes_commentaire_forum WHERE id_commentaire = c.id_commentaire AND id_utilisateur = ?) as user_liked
                                          FROM commentaire_forum c
                                          JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
                                          WHERE c.id_sujet = ? AND (c.statut = 'approuve' OR c.id_utilisateur = ? OR ? = 1)
                                          ORDER BY c.est_meilleure_reponse DESC, c.date_commentaire DESC
                                      ");
                                      if ($is_admin || $is_formateur) {
                                        $can_see_all = 1;
                                    } else {
                                        $can_see_all = 0;
                                    }                                    
                                      $stmt->bind_param("iiii", $user_id, $post['id_sujet'], $user_id, $can_see_all);
                                      $stmt->execute();
                                      $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                      
                                      if (empty($comments)): ?>
                                          <div class="text-center text-muted py-4">
                                              <i class="fas fa-comments fa-3x mb-3"></i>
                                              <p>Aucun commentaire pour le moment</p>
              </div>
                                      <?php else:
                                          foreach ($comments as $index => $comment): ?>
                                              <div class="comment-item mb-3">
                                                  <div class="d-flex">
                                                      <img src="<?= $comment['photo_profil'] ? '../assets/uploads/profile/' . htmlspecialchars($comment['photo_profil']) : '../assets/images/ISMO SHARE.png' ?>" 
                                                           alt="Photo de profil" 
                                                           class="rounded-circle me-2"
                                                           style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                                           onclick="showUserInfo(<?= htmlspecialchars(json_encode([
                                                               'id' => $comment['id_utilisateur'],
                                                               'nom' => $comment['nom'],
                                                               'prenom' => $comment['prenom'],
                                                               'photo' => $comment['photo_profil'],
                                                               'role' => $comment['role'],
                                                               'whatsapp' => $comment['numero_whatsapp'] ? '0' . $comment['numero_whatsapp'] : '0'
                                                           ])) ?>)">
                                                      <div class="flex-grow-1">
                                                          <div class="d-flex justify-content-between align-items-center">
                                                              <h6 class="mb-0">
                                                                  <?= htmlspecialchars($comment['prenom'] . ' ' . $comment['nom']) ?>
                                                                  <small class="text-muted ms-2"><?= date('d/m/Y H:i', strtotime($comment['date_commentaire'])) ?></small>
                                                                  <?php 
                                                                      if ($comment['statut'] === 'en_attente') { ?>
                                                                          <span class="badge bg-warning ms-2">En attente</span>
                                                                      <?php 
                                                                      } 
                                                                      ?>

                                                                      <?php 
                                                                      if ($comment['est_meilleure_reponse']) { ?>
                                                                          <span class="badge text-dark bg-warning ms-2">
                                                                              <i class="fas fa-star"></i>
                                                                          </span>
                                                                      <?php 
                                                                      } 
                                                                      ?>
                                                              </h6>
                                                              <div class="d-flex gap-2">
                                                                  <!-- Bouton Like -->
                                                                  <form method="POST" class="d-inline">
                                                                      <input type="hidden" name="comment_id" value="<?= $comment['id_commentaire'] ?>">
                                                                      <button type="submit" name="toggle_comment_like" class="btn <?= $comment['user_liked'] ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm">
                                                                          <i class="fas fa-heart"></i>
                                                                          <?php if ($comment['likes_count'] > 0): ?>
                                                                              <span>(<?= $comment['likes_count'] ?>)</span>
                                                                          <?php endif; ?>
                                                                      </button>
                                                                  </form>
                                                                  
                                                                  <?php
                                                                      if (($is_formateur||$is_admin) && $comment['statut'] === 'en_attente') {
                                                                          echo '
                                                                          <form method="POST" class="d-inline">
                                                                              <input type="hidden" name="comment_id" value="' . $comment['id_commentaire'] . '">
                                                                              <input type="hidden" name="action" value="approuve">
                                                                              <button type="submit" name="validate_comment" class="btn btn-sm btn-success">
                                                                                  <i class="fas fa-check"></i>
                                                                              </button>
                                                                          </form>
                                                                          <form method="POST" class="d-inline">
                                                                              <input type="hidden" name="comment_id" value="' . $comment['id_commentaire'] . '">
                                                                              <input type="hidden" name="action" value="rejete">
                                                                              <button type="submit" name="validate_comment" class="btn btn-sm btn-danger">
                                                                                  <i class="fas fa-times"></i>
                                                                              </button>
                                                                          </form>
                                                                          ';
                                                                      }
                                                                   ?>

                                                                    <?php
                                                                    if ($user['role'] === 'admin' || $user['role'] === 'formateur') {
                                                                        $btnClass = $comment['est_meilleure_reponse'] ? 'btn-warning' : 'btn-outline-warning';
                                                                        $id = $comment['id_commentaire'];
                                                                        echo '<button class="btn ' . $btnClass . ' btn-sm" 
                                                                                    onclick="markAsBestAnswer(' . $id . ', this)" 
                                                                                    id="bestAnswerBtn_' . $id . '">
                                                                                  <i class="fas fa-star"></i>
                                                                              </button>';
                                                                    }
                                                                    ?>

                                                                  <?php
                                                                    if (($is_admin || $is_formateur) || $comment['id_utilisateur'] == $user_id) {
                                                                        $id = $comment['id_commentaire'];
                                                                        echo '
                                                                        <form method="POST" class="d-inline">
                                                                            <input type="hidden" name="comment_id" value="' . $id . '">
                                                                            <button type="submit" name="delete_comment" class="btn btn-sm btn-danger">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                        </form>';
                                                                    }
                                                                  ?>

              </div>
            </div>
                                                          <p class="mb-0"><?= nl2br(htmlspecialchars($comment['contenu'])) ?></p>
          </div>
        </div>
                                            <?php
                                                if ($index < count($comments) - 1) {
                                                    echo '<hr class="my-3">';
                                                }
                                             ?>

              </div>
                                          <?php endforeach;
                                      endif; ?>
                  </div>
                                  <form method="POST" class="mt-4">
                                      <input type="hidden" name="post_id" value="<?= $post['id_sujet'] ?>">
                  <div class="mb-3">
                                          <textarea class="form-control" name="comment" rows="3" placeholder="Ajouter un commentaire..." required></textarea>
                  </div>
                                      <div class="text-end">
                                          <button type="submit" name="add_comment" class="btn btn-primary">
                      <i class="fas fa-paper-plane me-2"></i>Publier
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </main>
    <div class="modal fade" id="addPostModal" tabindex="-1">
          <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-plus me-2"></i>Nouveau post
                </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
          <div class="modal-body">
            <form method="POST">
                  <div class="mb-3">
                <label for="titre" class="form-label">Titre</label>
                <input type="text" class="form-control" id="titre" name="titre" required>
                  </div>

                  <div class="mb-3">
                <label for="contenu" class="form-label">Contenu</label>
                <textarea class="form-control" id="contenu" name="contenu" rows="5" required></textarea>
                  </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" name="add_post" class="btn btn-primary">
                  <i class="fas fa-paper-plane me-2"></i>Publier
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
    <div class="modal fade" id="logoutModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">
              <i class="fas fa-sign-out-alt me-2"></i>Confirmer la déconnexion
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

      function showUserInfo(userData) {
          const modal = new bootstrap.Modal(document.getElementById('userModal'));
          document.getElementById('userModal').querySelector('.modal-title').textContent = 'Informations de l\'utilisateur';
          document.getElementById('userModal').querySelector('.modal-body').innerHTML = `
              <div class="text-center mb-4">
                  <img src="${userData.photo ? '../assets/uploads/profile/' + userData.photo : '../assets/images/ISMO SHARE.png'}" 
                       alt="Photo de profil" 
                       class="rounded-circle mb-3" 
                       style="width: 150px; height: 150px; object-fit: cover;">
                  <h4>${userData.prenom} ${userData.nom}</h4>
                  <p class="text-muted mb-3">${userData.role.charAt(0).toUpperCase() + userData.role.slice(1)}</p>
              </div>
              <div class="user-details">
                  <div class="row">
                      <div class="col-12 mb-3">
                          <div class="d-flex align-items-center justify-content-center">
                              <i class="fab fa-whatsapp text-success me-2" style="font-size: 1.5rem;"></i>
                              ${userData.whatsapp && userData.whatsapp !== '0' ? 
                                  `<a href="https://wa.me/${userData.whatsapp.slice(1)}" class="text-decoration-none text-success" style="font-size: 1.2rem;">
                                      ${userData.whatsapp}
                                  </a>` : 
                                  `<span class="text-muted" style="font-size: 1.2rem;">
                                      Aucun numéro WhatsApp
                                  </span>`
                              }
                          </div>
                      </div>
                  </div>
              </div>
          `;
          modal.show();
      }

      function markAsBestAnswer(commentId, button) {
        fetch('forum.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `mark_best_answer=${commentId}`
        })
        .then(response => response.text())
        .then(data => {
          if (data.includes('success')) {
            // Réinitialiser tous les boutons
            document.querySelectorAll('[id^="bestAnswerBtn_"]').forEach(btn => {
              btn.className = 'btn btn-outline-warning btn-sm';
            });
            // Mettre à jour le bouton cliqué
            button.className = 'btn btn-warning btn-sm';
            // Recharger la page pour mettre à jour les badges
            location.reload();
          } else if (data.includes('removed')) {
            // Réinitialiser le bouton
            button.className = 'btn btn-outline-warning btn-sm';
            // Recharger la page pour mettre à jour les badges
            location.reload();
          } else {
            alert('Erreur lors de la mise à jour');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Erreur lors de la mise à jour');
        });
      }
    </script>

    <!-- User Info Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>Informations de l'utilisateur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Le contenu sera injecté dynamiquement par JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>