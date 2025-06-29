# 📚 ISMOShare

ISMOShare est une plateforme collaborative destinée aux étudiants et enseignants de l’ISMO (Institut Spécialisé de Technologie Appliquée). Elle permet de partager des ressources pédagogiques, publier des annonces, et gérer les profils utilisateurs.

<img src="assets/images/logo.png" alt="Logo ISMOShare" width="200" />

---

## 🚀 Fonctionnalités

- 📥 Téléversement et partage de ressources  
- 📢 Gestion des annonces  
- 👥 Authentification et profils utilisateurs  
- 📄 Tableau d’administration avec file d’attente de validation  
- 📚 Visualisation intégrée des fichiers  
- 🔐 Accès basé sur les rôles  

---

## 🧱 Technologies utilisées

- **Front-end** : HTML, CSS, BOOTSTRAP, JavaScript
- **Back-end** : PHP
- **Base de données** : MySQL

---

## 🛠 Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/med6ba/ismoshare.git
```

### 2. Importer la base de données

- Ouvrir phpMyAdmin ou un autre client MySQL.
- Importer le fichier suivant :

```bash
database/ismoshare.sql
```

### 3. Configurer le serveur local

Copier le dossier du projet dans votre répertoire serveur (ex: htdocs/ si vous utilisez XAMPP).

Démarrer Apache et MySQL.

### 4. Accéder au site

```bash
http://localhost/ISMOShare/index.php
```

### 5. Structure du projet

```bash
ISMOShare/
├── index.php
├── database/
│   └── ismoshare.sql
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   └── logo.png, hero.png, ...
│   ├── uploads/
│   │   └── profile/, resources/
│   └── documents/
│       └── cahier des charges.pdf
├── pages/
│   ├── includes/
│   │   ├── config.php
│   │   └── notification_functions.php
│   ├── subpages/
│   │   ├── contact-messages.php
│   │   ├── liste-users.php
│   │   ├── valider-inscriptions.php
│   │   └── valider-ressources.php
│   ├── uploads/
│   │   └── profiles/
│   ├── annonces.php
│   ├── apropos.php
│   ├── attente-validation.php
│   ├── connexion.php
│   ├── contact.php
│   ├── dashboard.php
│   ├── error.php
│   ├── forum.php
│   ├── inscription.php
│   ├── logout.php
│   ├── notifications.php
│   ├── profile.php
│   ├── ressources.php
└── └── update_download_count.php
```

## 👥 Travail d’équipe

Nous sommes une équipe de 5 stagiaires poursuivant notre formation en Développement Digital à l'ISMO Tétouan.  
Notre mission est de créer une plateforme interactive, facilitant l'accès aux ressources pédagogiques tout en favorisant les échanges entre stagiaires.  
Cet outil vise à encourager la collaboration, l'entraide et le partage de connaissances au sein de la communauté des stagiaires.

## 🗓️ Planification du Projet

| **Semaine**        | **Tâches réalisées**                                                                 |
|--------------------|--------------------------------------------------------------------------------------|
| Semaine 1          | Modélisation MERISE (MCD/MLD) + Maquettage UI/UX                                    |
| Semaine 2          | Intégration des maquettes (HTML, CSS, JavaScript)                                   |
| Semaines 3 - 4     | Développement Backend : authentification, gestion des rôles, modules stagiaires & formateurs |
| Semaines 5 - 6     | Développement des modules d’administration                                           |
| Semaine 7          | Mise en place des notifications et de la recherche filtrée                 |
| Semaine 8          | Tests techniques & validation avec utilisateurs pilotes                             |
| Semaine 9          | Présentation finale & rédaction du rapport    

---

## 📂 Livrables

1. Projet PHP compressé  
2. Rapport en PDF contenant les principaux axes :  
   - Description des fonctionnalités du projet  
   - Planification du projet  
   - Gestion de l’équipe et distribution des tâches  
   - La réalisation (capture d’écran des interfaces)  
   - Les difficultés rencontrées  
   - Les extensions possibles  
3. Présentation numérique contenant les mêmes axes que le rapport

---

## 🤝 Contribution

Les contributions sont les bienvenues !  
Merci de suivre les étapes suivantes :

1. Fork le dépôt
2. Crée une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Commit tes changements (`git commit -m 'Ajout d'une fonctionnalité'`)
4. Push vers ta branche (`git push origin feature/ma-fonctionnalite`)
5. Crée une Pull Request

---

## 📬 Contact

### Réalisé par

- **Mohamed Ben Abdessadak** - <a href="https://www.linkedin.com/in/med6ba/">LinkedIn</a> - <a href="https://github.com/med6ba">GitHub</a>
- **Fatima Ezzahraa Hmodo** - <a href="#">LinkedIn</a>
- **Nihad Afilal** - <a href="#">LinkedIn</a>
- **Dina Actaou** - <a href="#">LinkedIn</a>
- **Sara Hidouri** - <a href="#">LinkedIn</a>

### Sous la supervision de

- **Mme Joairia Lafhal** - <a href="#">LinkedIn</a>

**Institut spécialisé dans les métiers de l'offshoring (ISMO) – Tétouan, 2025**

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.
