# 📚 ISMOShare
<img src="assets/images/ISMO%20SHARE.png" alt="Aperçu ISMOShare" width="200" style="border-radius: 500px;" />

ISMOShare est une plateforme collaborative destinée aux étudiants et enseignants de l’ISMO (Institut Spécialisé de Technologie Appliquée). Elle permet de partager des ressources pédagogiques, publier des annonces, et gérer les profils utilisateurs.

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
Notre mission est de créer une plateforme interactive pour les stagiaires de l'ISMO Tétouan, facilitant l'accès aux ressources pédagogiques tout en favorisant les échanges entre stagiaires.  
Cet outil vise à encourager la collaboration, l'entraide et le partage de connaissances au sein de la communauté des stagiaires.

## 🗓️ Planification du Projet

| **Étape**               | **Tâches**                                         | **Durée**   | **Période**     |
|-------------------------|---------------------------------------------------|-------------|-----------------|
| **1. Conception**        | Modélisation MERISE (MCD et MLD)                   | 3 jours     | Semaine 1       |
|                         | Maquettage UI/UX (interfaces web)                  | 2 jours     | Semaine 1       |
| **2. Architecture technique** | Conception base de données (MySQL)             | 3 jours     | Semaine 1       |
| **4. Développement Frontend Web** | Intégration des maquettes (HTML, CSS, JS)  | 1 semaine   | Semaine 2       |
| **5. Développement Backend (API)** | Authentification, gestion des rôles         | 1 semaine   | Semaine 3       |
|                         | Fonctionnalités de base partie stagiaire et formateur | 1 semaine | Semaine 4       |
|                         | Fonctionnalités de base partie administration      | 2 semaines  | Semaines 5 et 6 |
| **6. Fonctionnalités avancées** | Chat, notifications, recherches filtrées      | 1 semaine   | Semaine 7       |
| **7. Tests & validation** | Tests unitaires, fonctionnels                      | 1 semaine   | Semaine 8       |
|                         | Retours utilisateurs (phase pilote avec une filière) | 1 semaine   | Semaine 8       |
| **8. Présentation finale** | Rapport final + diaporama de soutenance            | 1 jour      | Semaine 9       |

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

**Institut spécialisé dans les métiers de l'offshoring Tétouan (ISMO) – Tétouan, 2025**

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.
