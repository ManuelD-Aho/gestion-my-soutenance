# Gestion My Soutenance

Ce projet est une application de gestion des soutenances, développée avec Laravel et Filament. Elle permet de gérer les étudiants, les rapports, les jurys, les sessions de commission, et bien plus.

## Sommaire
1. [Présentation](#1-présentation)
2. [Prérequis](#2-prérequis)
3. [Installation](#3-installation)
4. [Configuration](#4-configuration)
5. [Lancement du projet](#5-lancement-du-projet)
6. [Structure du projet](#6-structure-du-projet)
7. [Commandes utiles](#7-commandes-utiles)
8. [Tests](#8-tests)
9. [Déploiement](#9-déploiement)
10. [Contribuer](#10-contribuer)

---

## 1. Présentation

**Gestion My Soutenance** est une plateforme permettant de gérer l’ensemble du processus de soutenance académique : inscriptions, rapports, jurys, décisions, documents, etc. Elle s’adresse aux administrateurs, enseignants, étudiants et membres du jury.

## 2. Prérequis

- PHP >= 8.1
- Composer
- MySQL ou MariaDB
- Node.js >= 16
- npm ou yarn
- Serveur web (Apache, Nginx, ou Laravel Valet)

## 3. Installation

1. **Cloner le dépôt**
   ```bash
   git clone <url-du-repo>
   cd gestion-my-soutenance
   ```
2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```
3. **Installer les dépendances front-end**
   ```bash
   npm install
   # ou
   yarn install
   ```

## 4. Configuration

1. **Copier le fichier d’exemple d’environnement**
   ```bash
   cp .env.example .env
   ```
2. **Générer la clé d’application**
   ```bash
   php artisan key:generate
   ```
3. **Configurer la base de données**
   - Modifier les variables `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` dans `.env`.

4. **(Optionnel) Configurer les mails, stockage, etc.**

## 5. Lancement du projet

1. **Lancer les migrations et les seeders**
   ```bash
   php artisan migrate --seed
   ```
2. **Compiler les assets front-end**
   ```bash
   npm run dev
   # ou
   yarn dev
   ```
3. **Démarrer le serveur de développement**
   ```bash
   php artisan serve
   ```
4. Accéder à l’application via [http://localhost:8000](http://localhost:8000)

## 6. Structure du projet

- `app/` : Code principal (contrôleurs, modèles, services, policies, etc.)
- `config/` : Fichiers de configuration
- `database/` : Migrations, seeders, factories
- `public/` : Fichiers accessibles publiquement (index.php, assets)
- `resources/` : Vues Blade, assets front-end
- `routes/` : Fichiers de routes (web, api, console)
- `tests/` : Tests unitaires et fonctionnels
- `vendor/` : Dépendances PHP (gérées par Composer)

## 7. Commandes utiles

- `php artisan migrate:fresh --seed` : Réinitialiser la base de données
- `php artisan db:seed` : Rejouer les seeders
- `php artisan make:model Nom` : Générer un modèle
- `npm run build` : Compiler les assets pour la production

## 8. Tests

Lancer les tests avec :
```bash
php artisan test
```

## 9. Déploiement

- Compiler les assets : `npm run build`
- Configurer le `.env` pour la production
- Utiliser un serveur web (Apache/Nginx) pointant vers `public/`
- Sécuriser les permissions des dossiers `storage/` et `bootstrap/cache/`

## 10. Contribuer

1. Forkez le projet
2. Créez une branche (`git checkout -b feature/ma-nouvelle-fonctionnalite`)
3. Commitez vos modifications (`git commit -am 'Ajout d’une fonctionnalité'`)
4. Poussez la branche (`git push origin feature/ma-nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request

---

Pour toute question, contactez l’équipe de développement.
