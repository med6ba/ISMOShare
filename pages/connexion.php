<?php
session_start();

include_once 'includes/config.php';

$message_error = "";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    extract($_POST); 

    $email = trim($email ?? '') . '@ofppt-edu.ma';
    $password = $password ?? '';

    if (empty($email) || empty($password)) {
        $message_error = "Veuillez remplir tous les champs.";
    }

    elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@ofppt-edu\.ma$/", $email)) {
        $message_error = "L'adresse email doit être valide et se terminer par @ofppt-edu.ma";
    }
    else {
        $stmt = $conn->prepare("SELECT id_utilisateur, mot_de_passe, statut, role FROM utilisateur WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message_error = "Identifiants incorrects.";
        } else {
            $user = $result->fetch_assoc();

            if (!password_verify($password, $user['mot_de_passe'])) {
                $message_error = "Identifiants incorrects.";
            } else {
                switch ($user['statut']) {
                    case 'rejeté':
                        $message_error = "Votre compte a été rejeté. Veuillez créer un nouveau compte.";
                        break;

                    case 'en_attente':
                        $message_error = "Votre compte est en attente de validation. Merci de patienter.";
                        break;

                    case 'suspendu':
                        $message_error = "Votre compte a été suspendu. Veuillez contacter l'administrateur.";
                        break;

                    case 'approuvé':
                        $_SESSION['user_id'] = $user['id_utilisateur'];
                        $_SESSION['user_role'] = $user['role'];
                        header("Location: dashboard.php");
                        exit();

                    default:
                        $message_error = "Statut du compte inconnu. Contactez l'administrateur.";
                }
            }
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>ISMOShare | Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="./../assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="shortcut icon" href="./../assets/images/logo.png" type="image/x-icon">
</head>
<body class="bg-light">
<div class="w-100 text-center py-2 bg-white shadow-sm position-fixed top-0 start-0 z-3">
  <a href="./../index.php" class="text-primary fw-semibold text-decoration-none">
    <i class="fa-solid fa-arrow-rotate-left"></i> Retour à l'accueil
  </a>
</div>

<div class="d-flex align-items-center justify-content-center vh-100">
    <div class="bg-light p-4 rounded w-100" style="max-width: 470px;">
        <div class="text-center mb-3">
            <img src="./../assets/images/logo.png" alt="Logo ISMOShare" width="50">
        </div>
        <h4 class="text-center fw-semibold mb-4">Connexion à ISMOShare</h4>

        <br>

        <?php if (!empty($message_error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message_error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email institutionnel</label>
                <div class="input-group">
                    <input
                        type="text"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="exemple"
                        required
                        value="<?= htmlspecialchars(str_replace('@ofppt-edu.ma', '', $email ?? '')) ?>"
                    >
                    <span class="input-group-text">@ofppt-edu.ma</span>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Mot de passe</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Votre mot de passe"
                    required
                >
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </div>

            <div class="text-center small">
                Pas encore de compte ?
                <a href="./inscription.php" class="text-primary text-decoration-underline">Créer un compte</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
