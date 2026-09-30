# 🛠️ MAINT&O — Plateforme GMAO & PWA Intelligente

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)
![AI Powered](https://img.shields.io/badge/AI-Groq%20%7C%20Gemini-10B981?style=for-the-badge&logo=openai&logoColor=white)

**Application Web Progressive (PWA) de Gestion de Maintenance Assistée par Ordinateur (GMAO)**  
*Centralisation, pilotage et traçabilité des interventions, maintenances préventives, dépannages et installations d'équipements industriels et frigorifiques entre **Soutarah Group** et ses clients partenaires *

</div>

---

## 📌 Sommaire

- [À propos du projet](#-à-propos-du-projet)
- [Fonctionnalités Principales](#-fonctionnalités-principales)
  - [Module 1 : Référentiel Équipements & Cartographie](#1-module-1--référentiel-équipements--cartographie)
  - [Module 2 : Gestion des Demandes & Dépannages](#2-module-2--gestion-des-demandes--dépannages)
  - [Module 3 : Maintenance Préventive & Planification](#3-module-3--maintenance-préventive--planification)
  - [Module 4 : Assistant IA & Aide au Diagnostic (RAG)](#4-module-4--assistant-ia--aide-au-diagnostic-rag)
  - [Module 5 : Interface Mobile Terrain (PWA)](#5-module-5--interface-mobile-terrain-pwa)
- [Architecture & Modélisation UML](#-architecture--modélisation-uml)
- [Rôles & Contrôle d'Accès (RBAC)](#-rôles--contrôle-daccès-rbac)
- [Stack Technique](#-stack-technique)
- [Installation & Démarrage Rapide](#-installation--démarrage-rapide)
- [Sécurité & Confidentialité](#-sécurité--confidentialité)
- [Arborescence du Projet](#-arborescence-du-projet)
- [Auteur & Licence](#-auteur--licence)

---

## 📖 À propos du projet

Dans les environnements multi-sites (télécoms, banques, industries), le suivi des équipements critiques (climatisation industrielle, groupes électrogènes, onduleurs, froid commercial) requiert une réactivité maximale et une traçabilité rigoureuse.

**MAINT&O** répond à ces exigences en offrant :
- Une **centralisation complète** des actifs matériels répartis par Client $\to$ Base $\to$ Site $\to$ Zone / Emplacement.
- Un **suivi en temps réel** des pannes, de la demande initiale jusqu'à la signature du compte-rendu terrain.
- Un **algorithme intelligent de planification** des maintenances préventives tenant compte des rythmes de traitement et des jours non ouvrables (repos, chômés, fériés).
- Un **Assistant IA contextuel (RAG)** pour assister les techniciens sur site et guider les diagnostics de panne.

---

## 🚀 Fonctionnalités Principales

### 1. Module 1 : Référentiel Équipements & Cartographie
* **Arborescence multiniveaux** : Gestion hiérarchique Client $\to$ Base $\to$ Site $\to$ Zone / Emplacement.
* **Fiche d'identité équipement** : Numéro d'inventaire unique, numéro de série, marque, modèle, puissance, fluide frigorigène, criticité, statut opérationnel.
* **Système VIP & Priorité** : Marquage et filtrage spécifique des équipements et sites stratégiques.
* **Fiches Froid (F-Gaz)** : Suivi réglementaire des manipulations de fluides frigorigènes (charge, récupération, étanchéité).
* **Import / Export en masse** : Gabarits Excel pour intégrer rapidement de grands parcs de matériel.

### 2. Module 2 : Gestion des Demandes & Dépannages
* **Création simplifiée de demandes** par les demandeurs et superviseurs de sites.
* **Circuit de validation hiérarchique** : Validation par le superviseur client avant déclenchement de l'ordre de réparation.
* **Affectation d'équipe & Technicien référent** : Assignation d'équipes internes ou de sous-traitants qualifiés.
* **Confirmation de passage** : Double validation avec le demandeur sur place.
* **Rapport d'intervention numérique** : Saisie des pièces remplacées, temps passé, causes racines et photos avant/après.

### 3. Module 3 : Maintenance Préventive & Planification
* **Calculateur automatique de durée** : Estimation automatique de la date de fin selon le ratio d'équipements à traiter par jour.
* **Gestion des jours non ouvrables** : Saut automatique des week-ends, jours fériés ou jours chômés cochés dans le calendrier sans fausser les ratios statistiques.
* **Gestion multi-équipes** : Définition d'une équipe principale et d'équipes de support.
* **Périodicité mensuelle récurrente** : Possibilité de reproduire instantanément une maintenance planifiée sur les mois choisis de l'année en cours (génération de maintenances autonomes avec leur propre suivi).
* **Planning interactif** : Visualisation globale en calendrier avec étiquettes de statut et sauts de jours de repos visibles.

### 4. Module 4 : Assistant IA & Aide au Diagnostic (RAG)
* **Architecture RAG (Retrieval-Augmented Generation)** : L'IA ne formule pas de réponses génériques, elle interroge la base de données locale pour connaître l'historique précis de l'équipement ou du site de l'utilisateur.
* **Multi-LLM orchestré** :
  1. *Groq API* : Inférence ultra-rapide sur modèles ouverts (LLaMA 3 / GPT-OSS 120B).
  2. *Google Gemini API* : Modèle multimodal en renfort.
  3. *Moteur expert local (Fallback)* : Mode hors-ligne autonome garantissant une réponse même en cas de coupure Internet.
* **Assistance terrain** : Arbre de diagnostic guidé (codes défauts, surchauffe compresseur, défaut d'isolement, etc.).

### 5. Module 5 : Interface Mobile Terrain (PWA)
* Interface dédiée et optimisée pour smartphone et tablette.
* Vue synthétique pour le technicien : interventions du jour, adresses des sites, contacts d'urgence.
* Saisie rapide des comptes-rendus et fiches de passage.

---

## 📐 Architecture & Modélisation UML

Tous les diagrammes de conception sont disponibles dans le dossier [`diagrammes_uml/`](./diagrammes_uml) :

| Fichier | Type de Diagramme | Description |
| :--- | :--- | :--- |
| `module1_referentiel_equipements.puml` | Classes | Modélisation des clients, sites, zones, équipements et fiches froid |
| `module2_interventions_depannages.puml` | Classes | Modélisation des demandes, dépannages et rapports |
| `module3_maintenance_preventive.puml` | Classes | Modélisation des plannings, pivots d'équipes et périodicité |
| `module4_assistant_ia.puml` | Classes | Modélisation des conversations et messages de l'IA |
| `sequence_1_authentification_roles.puml` | Séquence | Flux de connexion et contrôle des habilitations RBAC |
| `sequence_2_creation_demande_depannage.puml`| Séquence | Workflow d'initiation et de validation d'un dépannage |
| `sequence_3_planification_depannage.puml` | Séquence | Planification et assignation d'une intervention |
| `sequence_4_maintenance_preventive_suivi.puml`| Séquence | Cycle de vie d'une maintenance préventive |
| `sequence_7_interaction_assistant_ia.puml` | Séquence | Pipeline complet RAG $\to$ Inférence LLM $\to$ Restitution |
| `cas_utilisation_*.puml` | Use Cases | Cas d'utilisation par profil métier |

---

## 👥 Rôles & Contrôle d'Accès (RBAC)

Le système intègre un cloisonnement étanche des données selon le rôle de l'utilisateur :

- 👑 **Administrateur** : Accès global à l'ensemble du système, configuration des utilisateurs, des clients et des rôles.
- 👔 **Superviseur Soutarah** : Responsable d'une base ou de l'exploitation globale, affectation des équipes et validation des plannings.
- 🏢 **Superviseur Client** : Vue restreinte sur le parc et les sites de son entreprise, validation des demandes d'intervention.
- 👤 **Demandeur de Site** : Déclaration rapide d'incidents sur son ou ses sites affectés et confirmation de passage.
- 🔧 **Technicien / Chef d'Équipe** : Consultation de son planning, réalisation des tâches et saisie des rapports d'intervention.

---

## 💻 Stack Technique

* **Framework Backend** : [Laravel 12](https://laravel.com/) (PHP 8.2+)
* **Base de données** : MySQL 8.0+
* **Frontend** : Blade Templates, Vanilla CSS moderne (design responsive épuré), JavaScript ES6+
* **Iconographie & Typographie** : FontAwesome 6 Pro / Free, Inter font
* **Temps Réel & WebSockets** : Laravel Reverb / Pusher
* **Exportations** : Maatwebsite Excel (PhpSpreadsheet), Barryvdh DomPDF
* **IA & LLM** : Groq API (OpenAI Compatible SDK), Google Gemini API (v1beta)
* **Serveur Web cible** : Apache / Nginx (compatible hébergement mutualisé Hostinger et VPS)

---

## ⚙️ Installation & Démarrage Rapide

### Prérequis
- PHP $\ge$ 8.2 (extensions requises : `pdo_mysql`, `mbstring`, `openssl`, `curl`, `zip`, `gd`)
- Composer $\ge$ 2.x
- Serveur MySQL ou MariaDB
- Node.js & NPM (pour la compilation des assets Vite)

### 1. Cloner le projet
```bash
git clone https://github.com/Dimarco124/mainteo-app.git
cd mainteo-app
```

### 2. Installer les dépendances
```bash
composer install
npm install && npm run build
```

### 3. Configurer l'environnement
Copiez le modèle de configuration et générez la clé applicative :
```bash
cp .env.example .env
php artisan key:generate
```

Configurez vos identifiants dans le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=votre_base_de_donnees
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe

# Clés IA (Optionnelles mais recommandées)
GROQ_API_KEY=votre_cle_groq
GEMINI_API_KEY=votre_cle_gemini
```

### 4. Exécuter les migrations et seeders
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Démarrer le serveur local
```bash
php artisan serve
```
L'application est accessible sur : `http://127.0.0.1:8000`

---

## 🔒 Sécurité & Confidentialité

- **Secrets & Clés protégés** : Aucune clé d'API, mot de passe ou jeton sensible n'est committé dans le code source. Toutes les données d'authentification passent exclusivement par les variables d'environnement (`.env`).
- **Protection des données (Storage)** : Les fichiers temporaires, sessions, photos d'intervention et logs sont exclus du versionnement via le fichier [`.gitignore`](./.gitignore).
- **Protection CSRF & En-têtes sécurisés** : Middleware de protection CSRF, assainissement des entrées et en-têtes HTTP de sécurité activés.

---

## 📂 Arborescence du Projet

```text
mainteo-app/
├── app/
│   ├── Http/Controllers/     # Contrôleurs web et API (Demandes, Maintenances, IA, etc.)
│   ├── Models/               # Modèles Eloquent (Client, Equipement, Maintenance, etc.)
│   └── Services/             # Orchestrateur GeminiService (RAG & Multi-LLM)
├── bootstrap/                # Configuration du cycle de vie et gestionnaire d'exceptions
├── config/                   # Paramètres de l'application (services, database, etc.)
├── database/
│   ├── migrations/           # Schémas de base de données relationnelle
│   └── seeders/              # Jeux de données d'initialisation et démonstration
├── diagrammes_uml/           # Spécifications et diagrammes PlantUML complets
├── public/                   # Point d'entrée web (index.php), polices et assets publics
├── resources/
│   ├── views/                # Vues Blade modulaires (admin, planning, mobile, ai)
│   └── css/                  # Styles CSS
├── routes/
│   └── web.php               # Définition des routes applicatives avec middlewares RBAC
└── storage/                  # Logs, caches et fichiers privés uploadés
```

---

## 👨‍💻 Auteur & Soutenance

- **Développeur Principal** : [Dimarco124](https://github.com/Dimarco124)  
- **Projet** : Application GMAO MAINT&O — Soutarah Group  
- **Année** : 2026

<div align="center">
  <sub>Développé avec passion pour l'excellence opérationnelle et la maintenance prédictive moderne.</sub>
</div>
