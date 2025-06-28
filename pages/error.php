<?php
// error.php
$error_code = $_GET['code'] ?? 500;
$message = 'Une erreur inconnue est survenue.';

switch ($error_code) {
    case 403:
        $message = "Vous n'avez pas l'autorisation d'accéder à cette page.";
        break;
    case 404:
        $message = "La page que vous cherchez n'existe pas.";
        break;
    case 500:
    default:
        $message = "Une erreur interne du serveur est survenue.";
        break;
}
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMOShare | Erreur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="./../assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Andika:wght@400;700&display=swap');
        
        body {
            font-family: 'Andika', sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .error-container {
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        
        .error-title {
            color: #343a40;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .btn-home {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1 class="error-title">Oups !</h1>
        <p class="error-message">
            <?php
            $error_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : "Veuillez réessayer plus tard.";
            echo $error_message;
            ?>
        </p>
        <a href="./../index.php" class="btn btn-primary btn-home">
        <i class="fa-solid fa-arrow-rotate-left me-2"></i>Retour à l'accueil
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>