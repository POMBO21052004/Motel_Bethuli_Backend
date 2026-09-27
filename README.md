# FEC — Application de Gestion des Attestations et Formations (Backend API)

API REST de la plateforme **Free Engineering Consultancy (FEC)** pour la gestion des formations professionnelles, des sessions, des apprenants, des paiements et la génération d'attestations certifiées (PDF + QR code).

> **Projet associé :** [Frontend React](../FreeIngeneeringConsulting_AGA_Frontend)

---

## Présentation

Backend **REST API** développé avec **Laravel 12**. Il expose des endpoints JSON consommés par le frontend React, gère l'authentification via **Laravel Sanctum**, la persistance des données via **Eloquent ORM**, et les fonctionnalités métier avancées (PDF, QR code, import Excel, e-mails).

### Fonctionnalités principales

- Authentification JWT-like via tokens Sanctum (login, OTP, reset password)
- Gestion CRUD des formations, sessions, participants, entreprises
- Import Excel des participants avec validation et rapport d'erreurs
- Génération d'attestations PDF (4 modèles configurables)
- QR code et vérification publique des attestations
- Envoi d'attestations par e-mail et export ZIP
- Gestion des paiements avec stockage des preuves
- Notifications, recherche globale
- Contrôle d'accès par rôle (`admin` / `gestionnaire`)

---

## Stack technique

| Technologie | Version | Usage |
|-------------|---------|-------|
| PHP | ≥ 8.2 | Langage serveur |
| Laravel | 12 | Framework API |
| Laravel Sanctum | 4 | Authentification par token |
| Eloquent ORM | — | Couche données |
| DomPDF | 3 | Génération PDF |
| Maatwebsite Excel | 3 | Import/export Excel |
| chillerlan/php-qrcode | 6 | QR codes |
| SQLite / MySQL | — | Base de données |

---

## Prérequis

- **PHP** ≥ 8.2 avec extensions : `pdo`, `mbstring`, `xml`, `gd`, `zip`, `bcmath`
- **Composer** ≥ 2
- **Node.js** ≥ 18 (optionnel, pour les assets Vite Laravel)
- **SQLite** (dev) ou **MySQL/PostgreSQL** (production)

---

## Installation

```bash
# Entrer dans le dossier backend
cd FreeIngeneeringConsulting_AGA_Backend

# Installer les dépendances PHP
composer install

# Configurer l'environnement
cp .env.example .env
php artisan key:generate

# Créer la base SQLite (si utilisée)
touch database/database.sqlite

# Exécuter les migrations et seeders
php artisan migrate --seed

# Lier le stockage public
php artisan storage:link
```

### Variables d'environnement essentielles

```env
APP_NAME="FEC Attestations"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=fec_attestations
# DB_USERNAME=root
# DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@fec-consulting.com
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database
```

---

## Démarrage

```bash
# Serveur de développement
php artisan serve
# → http://127.0.0.1:8000

# Mode développement complet (serveur + queue + logs + vite)
composer dev

# File d'attente (e-mails, jobs asynchrones)
php artisan queue:listen
```

**Health check :** `GET /up`

**Base API :** `http://127.0.0.1:8000/api`

---

## Structure du projet

```
app/
├── Http/
│   ├── Controllers/Api/    # Contrôleurs REST
│   └── Middleware/         # CheckRole, etc.
├── Models/                 # Modèles Eloquent
├── Imports/                # Import Excel participants
├── Exports/                # Export Excel
├── Mail/                   # Classes Mailable
└── Notifications/          # Notifications Laravel

database/
├── migrations/             # Schéma de la base de données
└── seeders/                # AdminSeeder, UserSeeder

resources/views/
├── certificates/           # Templates PDF (model1–4)
└── emails/                 # Templates e-mail

routes/
└── api.php                 # Définition de toutes les routes API

storage/
├── app/                    # Fichiers uploadés (PDF, logos, preuves)
└── fonts/                  # Polices pour DomPDF
```

---

## API — Aperçu des routes

### Routes publiques

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/login` | Connexion |
| POST | `/api/auth/verify-otp` | Validation OTP |
| POST | `/api/auth/resend-otp` | Renvoi OTP |
| POST | `/api/auth/forgot-password` | Demande reset |
| POST | `/api/auth/reset-password` | Réinitialisation |
| GET | `/api/attestations/verify` | Vérification publique |

### Routes authentifiées (`auth:sanctum`)

| Ressource | Endpoints | Rôle |
|-----------|-----------|------|
| Formations | `GET/POST/PUT/DELETE /api/formations` | Lecture : tous ; Écriture : admin |
| Sessions | `/api/session-formations` | Tous |
| Participants | `/api/participants` + import | Tous |
| Attestations | `/api/attestations` + PDF/email/ZIP | Tous |
| Paiements | `/api/payements` | Tous |
| Entreprises | `/api/entreprises` | Lecture : tous ; Écriture : admin |
| Types attestation | `/api/type-attestations` | Admin |
| Administrateurs | `/api/administrateurs` | Admin |
| Gestionnaires | `/api/gestionnaires` | Admin |
| Notifications | `/api/notifications` | Tous |
| Recherche | `/api/search` | Tous |

Consultez `routes/api.php` pour la liste complète.

---

## Docker

```bash
# Build
docker build -t fec-backend .

# Run
docker run -p 10000:10000 -e PORT=10000 fec-backend
```

Le script `docker-entrypoint.sh` exécute automatiquement :
1. Génération de la clé d'application
2. Migrations
3. Lien symbolique storage
4. Seeder administrateur
5. Cache config/routes
6. Démarrage du serveur sur le port `10000`

---

## CORS

Les origines autorisées sont configurées dans `config/cors.php` :

- `http://localhost:5173` (dev)
- `https://fec-attestations.vercel.app` (prod)
- Pattern Vercel : `https://fec-attestations.*.vercel.app`

---

## Tests

```bash
php artisan test
```

> Les tests métier sont à compléter. Seuls les tests Laravel par défaut sont présents actuellement.

---

## Documentation complémentaire

- [Cahier des charges](./CAHIER_DES_CHARGES.md) — Spécifications fonctionnelles et techniques du backend
- [Licence](./LICENSE) — Conditions d'utilisation du logiciel

---

## Support

Pour toute question relative à ce projet, contactez **Free Engineering Consultancy (FEC)**.

---

**Copyright © 2026 Free Engineering Consultancy (FEC). Tous droits réservés.**
