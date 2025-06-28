<?php
include_once 'includes/config.php';

$message_succes = "";
$erreurs = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = array_map('trim', $_POST);

    if (isset($data['nom'])) {
    $nom = $data['nom'];
    } else {
        $nom = '';
    }

    if (isset($data['prenom'])) {
        $prenom = $data['prenom'];
    } else {
        $prenom = '';
    }

    if (isset($data['email'])) {
        $email = $data['email'];
    } else {
        $email = '';
    }

    if (isset($data['mot_de_passe'])) {
        $mot_de_passe = $data['mot_de_passe'];
    } else {
        $mot_de_passe = '';
    }

    if (isset($data['confirm_password'])) {
        $confirmation = $data['confirm_password'];
    } else {
        $confirmation = '';
    }

    if (isset($data['cef_matricule'])) {
        $cef_matricule = $data['cef_matricule'];
    } else {
        $cef_matricule = '';
    }


    if (empty($cef_matricule)) {
        $erreurs[] = "Le champ CEF / Matricule est obligatoire.";
    }

    $email = $email . '@ofppt-edu.ma';
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@ofppt-edu\.ma$/", $email)) {
        $erreurs[] = "L'adresse email doit être valide et se terminer par @ofppt-edu.ma";
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $mot_de_passe)) {
        $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un symbole.";
    }

    if ($mot_de_passe !== $confirmation) {
        $erreurs[] = "Les mots de passe ne correspondent pas.";
    }

    $verifier_email = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $verifier_email->bind_param("s", $email);
    $verifier_email->execute();
    $verifier_email->store_result();
    if ($verifier_email->num_rows > 0) {
        $erreurs[] = "Cette adresse email est déjà utilisée.";
    }
    $verifier_email->close();

    $verifier_cef = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE cef_matricule = ?");
    $verifier_cef->bind_param("s", $cef_matricule);
    $verifier_cef->execute();
    $verifier_cef->store_result();
    if ($verifier_cef->num_rows > 0) {
        $erreurs[] = "Ce CEF / Matricule est déjà utilisé.";
    }
    $verifier_cef->close();

    if (empty($erreurs)) {
        $mot_de_passe_hashé = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $requete = $conn->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, statut, role, cef_matricule) VALUES (?, ?, ?, ?, 'en_attente', NULL, ?)");

        $requete->bind_param("sssss", $nom, $prenom, $email, $mot_de_passe_hashé, $cef_matricule);

        try {
            $requete->execute();
            header("Location: ./../pages/attente-validation.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), "Duplicate entry") !== false) {
                $erreurs[] = "Ce CEF / Matricule est déjà utilisé.";
            } else {
                $erreurs[] = "Une erreur est survenue lors de l'inscription : " . $e->getMessage();
            }
        }
        $requete->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
  <title>ISMOShare | Inscription</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./../assets/css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link rel="shortcut icon" href="./../assets/images/logo.png" type="image/x-icon" />
</head>
<body class="bg-light">

<div class="w-100 text-center py-2 bg-white shadow-sm position-fixed top-0 start-0 z-3">
  <a href="./../index.php" class="text-primary fw-semibold text-decoration-none">
    <i class="fa-solid fa-arrow-rotate-left"></i> Retour à l'accueil
  </a>
</div>

<div class="d-flex align-items-center justify-content-center vh-100">
  <div class="p-4 rounded bg-light w-100" style="max-width: 500px">
    <div class="text-center mb-3">
      <img src="./../assets/images/logo.png" alt="Logo ISMOShare" width="50" />
    </div>
    <h4 class="text-center fw-semibold mb-4">Créer un compte ISMOShare</h4>

    <br>

    <?php if (!empty($message_succes)): ?>
      <div class="alert alert-success"><?= $message_succes ?></div>
    <?php endif; ?>

    <?php if (!empty($erreurs)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Formulaire d'inscription -->
    <form class="m-3" method="POST" action="inscription.php" novalidate>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="prenom" class="form-label fw-semibold">Prénom</label>
          <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Votre prénom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" />
        </div>
        <div class="col-md-6 mb-3">
          <label for="nom" class="form-label fw-semibold">Nom</label>
          <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" />
        </div>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Adresse email</label>
        <div class="input-group">
          <input type="text" class="form-control" id="email" name="email" placeholder="exemple" required value="<?= htmlspecialchars(str_replace('@ofppt-edu.ma', '', $_POST['email'] ?? '')) ?>" />
          <span class="input-group-text">@ofppt-edu.ma</span>
        </div>
      </div>

      <div class="mb-3">
        <label for="cef_matricule" class="form-label fw-semibold">CEF / Matricule</label>
        <input type="text" class="form-control" id="cef_matricule" name="cef_matricule" placeholder="CEF pour stagiaire, Matricule pour formateur" value="<?= htmlspecialchars($_POST['cef_matricule'] ?? '') ?>" required />
      </div>

      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Mot de passe</label>
        <input type="password" class="form-control mb-2" id="password" name="mot_de_passe" placeholder="Votre mot de passe" required />
        <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Confirmez le mot de passe" required />
      </div>

      <div class="d-grid mb-3 mt-4">
        <button type="submit" class="btn btn-primary">S'inscrire</button>
      </div>

      <div class="text-center small">
        Vous avez déjà un compte ?
        <a href="./connexion.php" class="text-primary text-decoration-underlined">Se connecter</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
