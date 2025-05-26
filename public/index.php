<?php
include_once 'pages/includes/config.php';
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <!-- Title of the website -->
    <title>ISMOShare | Accueil</title>
    <!-- Required Meta's -->
    <meta charset="UTF-8" />
    <meta
      name="descreption"
      content="plateforme collaborative pour les stagiaires 
    de l’ISMO tétouan"
    />
    <meta name="KEYWORDS" content="ismo share" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Required links -->
    <!-- Custom stylesheet file -->
    <link rel="stylesheet" href="./assets/css/style.css" />
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
      href="./assets/images/logo.png"
      type="image/x-icon"
    />
  </head>

  <body class="bg-light d-flex flex-column min-vh-100">
    <!-- Info Badge at the top before Navbar -->
    <div class="alert alert-info text-center mb-0" role="alert">
      🎉 La plateforme vient d'être lancée !<!-- Nous sommes impatients de vous donner l'accès à votre espace personnel. -->
    </div>

    <nav class="navbar navbar-expand-lg bg-light sticky-top shadow" id="nav">
      <div class="container">
        <!-- Mobile layout: login - logo - toggler -->
        <div
          class="d-flex d-lg-none justify-content-between align-items-center w-100"
        >
          <!-- Mobile login icon -->
          <a
            href="./pages/connexion.php"
            class="text-decoration-none text-black"
          >
            <i class="fa-solid fa-arrow-right-to-bracket" id="login-icon"></i>
          </a>

          <!-- Centered logo -->
          <a class="navbar-brand mx-lg-0 mx-auto fw-bold" href="index.php">
            <img
              src="./assets/images/logo.png"
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
          <a class="navbar-brand fw-bold d-none d-lg-flex" href="index.php">
            <img
              src="./assets/images/logo.png"
              width="30"
              alt="logo ismo share"
            />
            <span class="pe-2" id="logo">&nbsp;ISMOShare</span>
          </a>

          <ul class="navbar-nav mx-auto text-center">
            <li class="nav-item">
              <a class="nav-link fw-semibold" href="./index.php" id="active"
                >Accueil</a
              >
            </li>
            <li class="nav-item">
              <a class="nav-link fw-semibold" href="./pages/apropos.php"
                >À&nbsp;propos</a
              >
            </li>
            <li class="nav-item">
              <a class="nav-link fw-semibold" href="./pages/contact.php"
                >Contact</a
              >
            </li>
          </ul>

          <div class="d-none d-lg-flex">
            <a
              href="./pages/connexion.php"
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
      <section class="pt-5 bg-light" id="hero">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-lg-0">
              <h1 class="display-5 fw-bold">
                Échanger pour progresser, partager pour réussir
              </h1>
              <br />
              <p class="lead text-muted">
                ISMOShare - Plateforme Collaborative pour les Stagiaires de
                l’ISMO Tétouan
              </p>
              <a
                href="./pages/inscription.php"
                class="btn btn-primary btn-lg mt-2"
                >Créer un compte</a
              >
            </div>

            <div class="col-lg-6 text-center">
              <img
                src="./assets/images/hero.png"
                alt="Illustration"
                class="img-fluid rounded"
              />
            </div>
          </div>
        </div>
      </section>

      <section class="pb-5">
        <div class="container">
          <h2 class="text-center fw-bold">Comment ça marche ?</h2>
          <div class="line mx-auto mb-5"></div>
          <div class="row text-center g-4">
            <div class="col-12 col-md-3 gy-4">
              <div class="p-4 bg-white rounded shadow h-100">
                <i class="fas fa-user-plus text-primary fs-2 mb-3"></i>
                <h5 class="mb-0 fw-semibold">Créer votre compte</h5>
              </div>
            </div>

            <div class="col-12 col-md-3 gy-4">
              <div class="p-4 bg-white rounded shadow h-100">
                <i class="fas fa-comments text-primary fs-2 mb-3"></i>
                <h5 class="mb-0 fw-semibold">Répondez aux forums</h5>
              </div>
            </div>

            <div class="col-12 col-md-3 gy-4">
              <div class="p-4 bg-white rounded shadow h-100">
                <i class="fas fa-folder-open text-primary fs-2 mb-3"></i>
                <h5 class="mb-0 fw-semibold">Consultez les ressources</h5>
              </div>
            </div>

            <div class="col-12 col-md-3 gy-4">
              <div class="p-4 bg-white rounded shadow h-100">
                <i class="fas fa-bullhorn text-primary fs-2 mb-3"></i>
                <h5 class="mb-0 fw-semibold">Explorez les annonces</h5>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="py-5 bg-light">
        <div class="container">
          <h2 class="text-center fw-bold">Questions fréquentes</h2>
          <div class="line mx-auto mb-5"></div>

          <div class="accordion accordion-flush" id="faqAccordion">
            <div class="accordion-item mb-4 shadow-sm border rounded">
              <h2 class="accordion-header" id="q1">
                <button
                  class="accordion-button collapsed bg-transparent fw-bold"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#a1"
                  aria-expanded="false"
                  aria-controls="a1"
                >
                  <i class="fas fa-question-circle me-3 fs-3 text-primary"></i>
                  <h5 class="mb-0">C'est quoi ISMOShare ?</h5>
                </button>
              </h2>
              <div
                id="a1"
                class="accordion-collapse collapse"
                aria-labelledby="q1"
                data-bs-parent="#faqAccordion"
              >
                <div class="accordion-body">
                  <p>
                    ISMOShare est une plateforme collaborative conçue pour les
                    stagiaires de l’ISMO Tétouan. Elle vise à :
                  </p>
                  <ul class="mb-0">
                    <li>
                      Centraliser le partage, la consultation et le classement
                      des documents pédagogiques.
                    </li>
                    <li>
                      Fournir des outils d'entraide et des notifications adaptés
                      à l’environnement de formation.
                    </li>
                    <li>
                      Faciliter l’accès à l’information et renforcer la
                      collaboration entre stagiaires et formateurs.
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="accordion-item mb-4 shadow-sm border rounded">
              <h2 class="accordion-header" id="q2">
                <button
                  class="accordion-button collapsed bg-transparent fw-bold"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#a2"
                  aria-expanded="false"
                  aria-controls="a2"
                >
                  <i class="fas fa-envelope me-3 fs-3 text-primary"></i>
                  <h5 class="mb-0">
                    Puis-je m'inscrire avec un email personnel ?
                  </h5>
                </button>
              </h2>
              <div
                id="a2"
                class="accordion-collapse collapse"
                aria-labelledby="q2"
                data-bs-parent="#faqAccordion"
              >
                <div class="accordion-body">
                  Non, l'inscription est uniquement possible avec un email
                  institutionnel pour garantir la sécurité et l'authenticité des
                  membres.
                </div>
              </div>
            </div>

            <div class="accordion-item mb-4 shadow-sm border rounded">
              <h2 class="accordion-header" id="q3">
                <button
                  class="accordion-button collapsed bg-transparent fw-bold"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#a3"
                  aria-expanded="false"
                  aria-controls="a3"
                >
                  <i class="fas fa-lock me-3 fs-3 text-primary"></i>
                  <h5 class="mb-0">
                    Comment protéger mes données personnelles ?
                  </h5>
                </button>
              </h2>
              <div
                id="a3"
                class="accordion-collapse collapse"
                aria-labelledby="q3"
                data-bs-parent="#faqAccordion"
              >
                <div class="accordion-body">
                  Vos données sont sécurisées et ne seront jamais partagées sans
                  votre consentement explicite.
                </div>
              </div>
            </div>

            <div class="accordion-item shadow-sm border rounded">
              <h2 class="accordion-header" id="q4">
                <button
                  class="accordion-button collapsed bg-transparent fw-bold"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#a4"
                  aria-expanded="false"
                  aria-controls="a4"
                >
                  <i class="fas fa-mobile-alt me-3 fs-3 text-primary"></i>
                  <h5 class="mb-0">
                    Puis-je accéder à la plateforme depuis mon téléphone ?
                  </h5>
                </button>
              </h2>
              <div
                id="a4"
                class="accordion-collapse collapse"
                aria-labelledby="q4"
                data-bs-parent="#faqAccordion"
              >
                <div class="accordion-body">
                  Oui, ISMOShare est entièrement responsive et accessible depuis
                  tous les appareils : smartphones, tablettes et ordinateurs.
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
