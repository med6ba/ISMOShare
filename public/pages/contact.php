<?php
include_once 'includes/config.php';

$message_succes = "";
$erreurs = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données
    $nom_complet = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation des données
    if (empty($nom_complet)) {
        $erreurs[] = "Le nom complet est obligatoire.";
    }

    if (empty($email)) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }

    if (empty($message)) {
        $erreurs[] = "Le message est obligatoire.";
    }

    // Si pas d'erreurs, enregistrer le message
    if (empty($erreurs)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (nom_complet, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nom_complet, $email, $message);
        
        if ($stmt->execute()) {
            $message_succes = "Votre message a été envoyé avec succès.";
        } else {
            $erreurs[] = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <!-- Title of the website -->
    <title>ISMOShare | Contact</title>
    <!-- Required Meta's -->
    <meta charset="UTF-8" />
    <meta
      name="descreption"
      content="plateforme collaborative pour les stagiaires 
    de l'ISMO tétouan"
    />
    <meta name="KEYWORDS" content="ismo share" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Required links -->
    <!-- Custom stylesheet file -->
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <!-- Bootstrap -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7"
      crossorigin="anonymous"
    />
    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <!-- Favicon -->
    <link
      rel="shortcut icon"
      href="./../assets/images/logo.png"
      type="image/x-icon"
    />
  </head>
  <body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg bg-light sticky-top shadow" id="nav">
      <div class="container">
        <!-- Mobile layout: login - logo - toggler -->
        <div
          class="d-flex d-lg-none justify-content-between align-items-center w-100"
        >
          <!-- Mobile login icon -->
          <a href="./connexion.php" class="text-decoration-none text-black">
            <i class="fa-solid fa-arrow-right-to-bracket" id="login-icon"></i>
          </a>

          <!-- Centered logo -->
          <a class="navbar-brand mx-lg-0 mx-auto fw-bold" href="./../index.php">
            <img
              src="./../assets/images/logo.png"
              width="30"
              alt="logo ismo share"
            /><span id="logo">&nbsp;ISMOShare</span>
          </a>

          <!-- Navbar toggler -->
          <button
            class="navbar-toggler text-dark"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>

        <div
          class="collapse navbar-collapse justify-content-between align-items-center"
          id="navbarNav"
        >
          <a
            class="navbar-brand fw-bold d-none d-lg-flex"
            href="./../index.php"
          >
            <img
              src="./../assets/images/logo.png"
              width="30"
              alt="logo ismo share"
            />
            <span class="pe-2" id="logo">&nbsp;ISMOShare</span>
          </a>

          <ul class="navbar-nav mx-auto text-center">
            <li class="nav-item">
              <a class="nav-link fw-semibold" href="./../index.php">Accueil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link fw-semibold" href="./apropos.php"
                >À&nbsp;propos</a
              >
            </li>
            <li class="nav-item">
              <a class="nav-link fw-semibold" href="./contact.php" id="active"
                >Contact</a
              >
            </li>
          </ul>

          <div class="d-none d-lg-flex">
            <a
              href="./../pages/connexion.php"
              class="btn btn-primary text-decoration-none"
              id="login-btn"
            >
              Connexion
            </a>
          </div>
        </div>
      </div>
    </nav>

    <main class="container">
      <section class="py-5 bg-light">
        <div class="container">
          <h2 class="text-center fw-bold">Contactez-nous</h2>
          <div class="line mx-auto mb-5"></div>

          <h5 class="text-center mb-5 mx-5">
            Une question? Une suggestion? Nous sommes là pour vous aider
          </h5>

          <!-- Section Contact -->
          <div class="container my-5">
            <div class="row g-4 align-items-stretch">
              <!-- Colonne 1 : Formulaire -->
              <div class="col-md-6">
                <div class="p-4 shadow rounded bg-white h-100">
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

                  <form action="contact.php" method="POST">
                    <div class="mb-3">
                      <label for="nom" class="form-label fw-bold">Nom complet</label>
                      <input
                        type="text"
                        class="form-control rounded"
                        id="nom"
                        name="nom"
                        placeholder="Votre nom"
                        required
                        value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                      />
                    </div>
                    <div class="mb-3">
                      <label for="email" class="form-label fw-bold">Email</label>
                      <input
                        type="email"
                        class="form-control rounded"
                        id="email"
                        name="email"
                        placeholder="Votre email"
                        required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                      />
                    </div>
                    <div class="mb-3">
                      <label for="message" class="form-label fw-bold">Message</label>
                      <textarea
                        class="form-control rounded py-2"
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="Votre message..."
                        required
                      ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <input
                      type="submit"
                      value="Envoyer"
                      class="btn btn-primary rounded px-4"
                    />
                  </form>
                </div>
              </div>

              <!-- Colonne 2 : Google Maps -->
              <div class="col-md-6">
                <div class="h-100 rounded shadow overflow-hidden">
                  <div class="ratio ratio-4x3 h-100">
                    <iframe
                      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3242.047887729344!2d-5.307937623598776!3d35.651191772596924!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0b5b615148fea3%3A0x37b6f3842f42dcbe!2sInstitute%20specialized%20in%20offshoring%20professions!5e0!3m2!1sen!2sma!4v1746028212054!5m2!1sen!2sma"
                      style="border: 0"
                      allowfullscreen=""
                      loading="lazy"
                      referrerpolicy="no-referrer-when-downgrade"
                    >
                    </iframe>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer mt-auto py-3 bg-dark text-white">
      <div
        class="container d-flex flex-column-reverse flex-md-row justify-content-between align-items-center"
      >
        <!-- Left (on large) / Bottom (on small): Copyrights -->
        <div class="text-start mt-3 mt-md-0">
          <h6 class="mb-0">&copy; 2025 ISMOShare. Tous droits réservés</h6>
        </div>

        <!-- Right (on large) / Top (on small): Icons -->
        <div class="text-end">
          <a
            href="https://ismo.ma"
            class="text-white text-decoration-none me-3"
            target="_blank"
          >
            <i class="fa-solid fa-link"></i>
          </a>
          <a
            href="https://t.me/ismotc"
            class="text-white text-decoration-none me-3"
            target="_blank"
          >
            <i class="fa-solid fa-paper-plane"></i>
          </a>
          <a
            href="https://maps.app.goo.gl/YQNPPmqGWHYhJLB89"
            class="text-white text-decoration-none me-3"
            target="_blank"
          >
            <i class="fa-solid fa-location-dot"></i>
          </a>
          <a
            href="mailto:infos@ismo.ma"
            class="text-white text-decoration-none"
          >
            <i class="fa-solid fa-envelope"></i>
          </a>
        </div>
      </div>
    </footer>

    <!-- Required scripts -->
    <!-- Bootstrap -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
      crossorigin="anonymous"
    ></script>
    <!-- Custom Script file -->
    <script src="./assets/js/script.js"></script>
  </body>
</html>
