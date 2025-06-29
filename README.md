# ISMOShare 📚

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

## 🖼️ Aperçu

![Aperçu ISMOShare](assets/images/ISMO%20SHARE.png)

---

## 🧱 Technologies utilisées

- **Front-end** : HTML, CSS, BOOTSTRAP, JavaScript
- **Back-end** : PHP
- **Base de données** : MySQL

---

## 🛠 Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/votre-nom-utilisateur/ismoshare.git
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

