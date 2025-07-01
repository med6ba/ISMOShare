<?php
include_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <!-- Title of the website -->
    <title>ISMOShare | À propos</title>
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
              <a class="nav-link fw-semibold" href="./apropos.php" id="active"
                >À&nbsp;propos</a
              >
            </li>
            <li class="nav-item">
              <a class="nav-link fw-semibold" href="./contact.php">Contact</a>
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
          <h2 class="text-center fw-bold">Qui sommes-nous ?</h2>
          <div class="line mx-auto mb-5"></div>

          <h5 class="text-center mx-5 mb-5">
            Nous sommes une équipe de 5 stagiaires poursuivant notre formation
            en Développement Digital à l'ISMO Tétouan.
          </h5>

          <div
            class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4"
          >
            <!-- Card Template (Repeat for 5 people) -->
            <div class="col">
              <div
                class="card h-100 text-center border-0 shadow-sm transition-hover"
              >
                <img
                  src="./../assets/images/img1.jpg"
                  class="rounded-circle mx-auto mt-4"
                  alt="medba photo"
                  width="100"
                  height="100"
                />
                <div class="card-body">
                  <h4 class="card-title mb-1 fw-bold">Medba</h4>
                </div>
                <div
                  class="card-footer bg-transparent border-0 d-flex justify-content-center gap-3 pb-4"
                >
                  <a href="https://www.linkedin.com/in/med6ba" target="_blank" class="text-black"
                    ><i class="fa-brands fa-linkedin fa-lg"></i
                  ></a>
                  <a href="https://github.com/med6ba" target="_blank" class="text-black"
                    ><i class="fa-brands fa-github fa-lg"></i
                  ></a>
                </div>
              </div>
            </div>
            <div class="col">
              <div
                class="card h-100 text-center border-0 shadow-sm transition-hover"
              >
                <img
                  src="./../assets/images/img2.jpg"
                  class="rounded-circle mx-auto mt-4"
                  alt="John Doe"
                  width="100"
                  height="100"
                />
                <div class="card-body">
                  <h4 class="card-title mb-1 fw-bold">Fatima Ezzahraa Hmodo</h4>
                </div>
                <div
                  class="card-footer bg-transparent border-0 d-flex justify-content-center gap-3 pb-4"
                >
                  <a href="http://www.linkedin.com/in/fatima-ezzahraa-hmodo-531923276" target="_blank" class="text-black"
                    ><i class="fa-brands fa-linkedin fa-lg"></i
                  ></a>
                  <a href="https://github.com/Fatimaezzah2" target="_blank" class="text-black"
                    ><i class="fa-brands fa-github fa-lg"></i
                  ></a>
                </div>
              </div>
            </div>
            <div class="col">
              <div
                class="card h-100 text-center border-0 shadow-sm transition-hover"
              >
                <img
                  src="./../assets/images/img3.jpg"
                  class="rounded-circle mx-auto mt-4"
                  alt="John Doe"
                  width="100"
                  height="100"
                />
                <div class="card-body">
                  <h4 class="card-title mb-1 fw-bold">Nihad Afilal</h4>
                </div>
                <div
                  class="card-footer bg-transparent border-0 d-flex justify-content-center gap-3 pb-4"
                >
                  <a href="https://www.linkedin.com/in/nihad-afilal-b40a96316" class="text-black" target="_blank"
                    ><i class="fa-brands fa-linkedin fa-lg"></i
                  ></a>
                  <a href="https://github.com/Nih17ad" class="text-black" target="_blank"
                    ><i class="fa-brands fa-github fa-lg"></i
                  ></a>
                </div>
              </div>
            </div>
            <div class="col">
              <div
                class="card h-100 text-center border-0 shadow-sm transition-hover"
              >
                <img
                  src="./../assets/images/img4.jpg"
                  class="rounded-circle mx-auto mt-4"
                  alt="John Doe"
                  width="100"
                  height="100"
                />
                <div class="card-body">
                  <h4 class="card-title mb-1 fw-bold">Dina Actaou</h4>
                </div>
                <div
                  class="card-footer bg-transparent border-0 d-flex justify-content-center gap-3 pb-4"
                >
                  <a href="https://www.linkedin.com/in/aktaou-dina-b7a8342aa/" class="text-black" target="_blank"
                    ><i class="fa-brands fa-linkedin fa-lg"></i
                  ></a>
                  <a href="https://github.com/DinaActaou" class="text-black"  target="_blank"
                    ><i class="fa-brands fa-github fa-lg"></i
                  ></a>
                </div>
              </div>
            </div>
            <div class="col">
              <div
                class="card h-100 text-center border-0 shadow-sm transition-hover"
              >
                <img
                  src="./../assets/images/img5.jpg"
                  class="rounded-circle mx-auto mt-4"
                  alt="John Doe"
                  width="100"
                  height="100"
                />
                <div class="card-body">
                  <h4 class="card-title mb-1 fw-bold">Sara Hidouri</h4>
                </div>
                <div
                  class="card-footer bg-transparent border-0 d-flex justify-content-center gap-3 pb-4"
                >
                  <a href="https://www.linkedin.com/in/sara-hidouri-02353531a" class="text-black" target="_blank"
                    ><i class="fa-brands fa-linkedin fa-lg"></i
                  ></a>
                  <a href="https://github.com/sarahidouri" class="text-black" target="_blank"
                    ><i class="fa-brands fa-github fa-lg"></i
                  ></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="bg-light">
        <div class="container">
          <h2 class="text-center fw-bold">Notre mission</h2>
          <div class="line mx-auto mb-5"></div>
          <h5 class="text-center mx-5 mb-5">
            Notre mission est de créer une plateforme interactive pour les
            stagiaires de l'ISMO Tétouan, facilitant l'accès aux ressources
            pédagogiques tout en favorisant les échanges entre stagiaires. Cet
            outil vise à encourager la collaboration, l'entraide et le partage
            de connaissances au sein de la communauté des stagiaires.
          </h5>
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
