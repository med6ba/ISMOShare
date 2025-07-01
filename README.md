# ISMOShare
<br>
<img src="assets/images/ISMO SHARE.png" alt="Logo ISMOShare" width="100" />

ISMOShare est une plateforme collaborative destinée aux étudiants et enseignants de l’ISMO (Institut spécialisé dans les métiers de l'offshoring). Elle permet de centraliser les ressources pédagogiques de l’ISMO et fluidifier la communication entre stagiaires, formateurs et administration.
<br>

![GitHub repo size](https://img.shields.io/github/repo-size/med6ba/ismoshare)
![GitHub license](https://img.shields.io/github/license/med6ba/ismoshare)

---

## 🚀 Fonctionnalités

- Téléversement et partage de ressources  
- Gestion des annonces  
- Authentification et profils utilisateurs  
- Tableau d’administration avec file d’attente de validation  
- Visualisation intégrée des fichiers  
- Accès basé sur les rôles  

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
├── LICENSE
├── README.md
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/...
│   └── uploads/
│       ├── profile/...
│       └── resources/...
│
├── database/
│   └── ismoshare.sql
│
├── documents/
│   ├── cahier des charges.pdf
│   ├── MCD.docx
│   ├── MLD.docx
│   ├── Présentation de Projet.pptx
│   └── Rapport de Projet.pdf
│
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
│   ├── update_download_count.php
│   │
│   ├── includes/
│   │   ├── config.php
│   │   └── notification_functions.php
│   │
│   └── subpages/
│       ├── contact-messages.php
│       ├── liste-users.php
│       ├── valider-inscriptions.php
└────── └── valider-ressources.php

```

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

## 🤝 Contribution

Les contributions sont les bienvenues !  
Merci de suivre les étapes suivantes :

1. Fork le dépôt
2. Crée une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Commit tes changements (`git commit -m 'Ajout d'une fonctionnalité'`)
4. Push vers ta branche (`git push origin feature/ma-fonctionnalite`)
5. Crée une Pull Request

---

## ❗ Remarque importante
ℹ️ Le projet ISMOShare n’a pas été hébergé en ligne.
<br>
Il est fourni uniquement sous forme de fichiers locaux afin que les utilisateurs puissent faire une simulation en local.

---

## 📬 Contact

### Réalisé par:

- **Mohamed Ben Abdessadak** - <a href="https://www.linkedin.com/in/med6ba/">LinkedIn</a> - <a href="https://github.com/med6ba">GitHub</a>
- **Fatima Ezzahraa Hmodo** - <a href="http://www.linkedin.com/in/fatima-ezzahraa-hmodo-531923276">LinkedIn</a> - <a href="https://github.com/Fatimaezzah2">GitHub</a>
- **Nihad Afilal** - <a href="https://www.linkedin.com/in/nihad-afilal-b40a96316">LinkedIn</a> - <a href="https://github.com/Nih17ad">GitHub</a>
- **Dina Actaou** - <a href="https://www.linkedin.com/in/aktaou-dina-b7a8342aa">LinkedIn</a> - <a href="https://github.com/DinaActaou">GitHub</a>
- **Sara Hidouri** - <a href="https://www.linkedin.com/in/sara-hidouri-02353531a">LinkedIn</a> - <a href="https://github.com/sarahidouri">GitHub</a>

### Sous la supervision de:

- **Mme Joairia Lafhal** - <a href="https://www.linkedin.com/in/joairia-lafhal-231454271/">LinkedIn</a> - <a href="https://github.com/joairia">GitHub</a>

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.
