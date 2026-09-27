# Cahier des charges — Backend API

## Application de Gestion des Attestations et Formations

**Client :** Free Engineering Consultancy (FEC)  
**Projet :** FEC Attestations — API REST (Backend)  
**Version du document :** 1.0  
**Date :** Juillet 2026  
**Statut :** Validé / En production

---

## Table des matières

1. [Contexte et objectifs](#1-contexte-et-objectifs)
2. [Périmètre du projet](#2-périmètre-du-projet)
3. [Acteurs et rôles](#3-acteurs-et-rôles)
4. [Exigences fonctionnelles](#4-exigences-fonctionnelles)
5. [Modèle de données](#5-modèle-de-données)
6. [Exigences non fonctionnelles](#6-exigences-non-fonctionnelles)
7. [Architecture technique](#7-architecture-technique)
8. [Spécification API](#8-spécification-api)
9. [Contraintes et dépendances](#9-contraintes-et-dépendances)
10. [Livrables](#10-livrables)
11. [Critères d'acceptation](#11-critères-dacceptation)

---

## 1. Contexte et objectifs

### 1.1 Contexte

Free Engineering Consultancy (FEC) délivre des formations professionnelles et émet des attestations certifiées. Le backend constitue le cœur métier de la plateforme : il centralise les données, applique les règles de gestion, génère les documents officiels et expose une API REST consommée par le frontend React.

### 1.2 Problématique

- Données éparpillées (fichiers Excel, documents Word, e-mails)
- Absence de numérotation standardisée des attestations
- Impossibilité de vérifier l'authenticité d'un certificat en ligne
- Pas de contrôle d'accès granulaire entre administrateurs et gestionnaires
- Processus d'import de participants manuel et sujet aux erreurs

### 1.3 Objectifs du backend

| Objectif | Description |
|----------|-------------|
| **API unifiée** | Exposer une API REST JSON documentée et versionnée |
| **Sécurité** | Authentification Sanctum, OTP, contrôle par rôle |
| **Automatisation** | Génération PDF, QR code, numérotation, e-mails automatiques |
| **Intégrité** | Schéma de données normalisé avec migrations versionnées |
| **Scalabilité** | Architecture prête pour MySQL/PostgreSQL en production |
| **Traçabilité** | Historique des générations, envois, et modifications |

### 1.4 Indicateurs de succès

- Temps de génération PDF < 5 secondes
- Import Excel de 500 participants < 30 secondes
- API response time médian < 200 ms
- Zéro faille d'authentification (routes protégées)

---

## 2. Périmètre du projet

### 2.1 Inclus dans le périmètre

- API REST Laravel 12 avec Sanctum
- Authentification (login, OTP, reset password)
- CRUD complet : formations, sessions, participants, entreprises, attestations, paiements
- Import/export Excel participants
- Génération PDF attestations (4 modèles)
- QR code et endpoint de vérification publique
- Envoi e-mail (OTP, reset password, attestation)
- Export ZIP des attestations par session
- Gestion des utilisateurs (admin, gestionnaire)
- Notifications en base de données
- Recherche globale
- Docker + script d'entrée automatisé
- Migrations et seeders

### 2.2 Hors périmètre

- Interface utilisateur (frontend React)
- Paiement en ligne (gateway)
- Signature électronique qualifiée
- API publique tierce (webhooks, OAuth clients)
- Multi-tenant (plusieurs organismes)

---

## 3. Acteurs et rôles

### 3.1 Acteurs système

| Acteur | Description |
|--------|-------------|
| **Frontend React** | Client principal consommant l'API |
| **Visiteur public** | Accède à `/api/attestations/verify` sans authentification |
| **Administrateur** | Rôle `admin` — accès complet |
| **Gestionnaire** | Rôle `gestionnaire` — accès opérationnel |
| **Worker queue** | Traite les jobs asynchrones (e-mails) |

### 3.2 Matrice des permissions (API)

| Ressource | Admin | Gestionnaire | Public |
|-----------|:-----:|:------------:|:------:|
| Auth (login, OTP) | ✓ | ✓ | ✓ |
| Formations (lecture) | ✓ | ✓ | ✗ |
| Formations (écriture) | ✓ | ✗ | ✗ |
| Sessions | ✓ | ✓ | ✗ |
| Participants + Import | ✓ | ✓ | ✗ |
| Attestations (CRUD) | ✓ | ✓ | ✗ |
| Attestations (verify) | ✓ | ✓ | ✓ |
| Paiements | ✓ | ✓ | ✗ |
| Entreprises (lecture) | ✓ | ✓ | ✗ |
| Entreprises (écriture) | ✓ | ✗ | ✗ |
| Types attestation | ✓ | ✗ | ✗ |
| Objectifs pédagogiques (écriture) | ✓ | ✗ | ✗ |
| Administrateurs / Gestionnaires | ✓ | ✗ | ✗ |
| Notifications / Recherche | ✓ | ✓ | ✗ |

---

## 4. Exigences fonctionnelles

### 4.1 Authentification et sécurité (AUTH)

| ID | Exigence | Priorité |
|----|----------|----------|
| AUTH-01 | Login e-mail/mot de passe, retour token Sanctum ou flag `require_otp` | Haute |
| AUTH-02 | Génération et envoi OTP par e-mail (table `otps`) | Haute |
| AUTH-03 | Vérification OTP → émission token Sanctum | Haute |
| AUTH-04 | Renvoi OTP avec rate limiting | Haute |
| AUTH-05 | Reset password via token e-mail | Haute |
| AUTH-06 | Endpoint `/auth/me` — profil utilisateur connecté | Haute |
| AUTH-07 | Mise à jour profil et mot de passe | Moyenne |
| AUTH-08 | Logout — révocation du token courant | Haute |
| AUTH-09 | Middleware `role:admin` sur routes sensibles | Haute |
| AUTH-10 | Détection statut en ligne (`is_online`) via tokens Sanctum | Basse |

### 4.2 Formations (FORM)

| ID | Exigence | Priorité |
|----|----------|----------|
| FORM-01 | CRUD formations (UUID, intitulé, domaine, prix, image, statut) | Haute |
| FORM-02 | Association à un type d'attestation | Haute |
| FORM-03 | Association aux objectifs pédagogiques | Haute |
| FORM-04 | Champ `categorie_permis` pour formations permis de conduire | Moyenne |
| FORM-05 | Traçabilité `created_by` (utilisateur créateur) | Moyenne |

### 4.3 Sessions de formation (SES)

| ID | Exigence | Priorité |
|----|----------|----------|
| SES-01 | CRUD sessions (nom, dates, lieu, statut) | Haute |
| SES-02 | Liaison obligatoire à une formation | Haute |
| SES-03 | Inscription participants (attach/detach) via table pivot | Haute |
| SES-04 | Champ pivot `attestation_disponible` (boolean) | Haute |
| SES-05 | Compteur `nb_participants` | Moyenne |
| SES-06 | Liste des paiements par session | Moyenne |

### 4.4 Participants / Apprenants (PART)

| ID | Exigence | Priorité |
|----|----------|----------|
| PART-01 | CRUD participants (UUID, nom, prénom, e-mail, téléphone, fonction) | Haute |
| PART-02 | Association optionnelle à une entreprise | Haute |
| PART-03 | Champ `numero_permis` (permis de conduire) | Moyenne |
| PART-04 | Import Excel — téléchargement modèle vierge | Haute |
| PART-05 | Import Excel — phase de vérification (validation sans insertion) | Haute |
| PART-06 | Import Excel — insertion après validation | Haute |
| PART-07 | Import Excel — export rapport d'erreurs | Haute |
| PART-08 | Relations : sessions (many-to-many), attestations, paiements | Haute |

### 4.5 Attestations (ATT)

| ID | Exigence | Priorité |
|----|----------|----------|
| ATT-01 | CRUD attestations | Haute |
| ATT-02 | Numérotation automatique : `FEC/{ACRONYME}/{ANNÉE}/{ID}` | Haute |
| ATT-03 | Génération UUID QR code unique par attestation | Haute |
| ATT-04 | Génération PDF via DomPDF (4 modèles Blade) | Haute |
| ATT-05 | Sélection modèle PDF selon `TypeAttestation.model_choice` | Haute |
| ATT-06 | Calcul date d'expiration selon durée du type attestation | Haute |
| ATT-07 | Envoi attestation par e-mail (PDF en pièce jointe) | Haute |
| ATT-08 | Export ZIP de toutes les attestations d'une session | Haute |
| ATT-09 | Endpoint public `/attestations/verify?identifier=` | Haute |
| ATT-10 | Toggle disponibilité attestation (admin) | Moyenne |
| ATT-11 | Stockage chemin PDF (`fichier_pdf_path`) | Haute |
| ATT-12 | Traçabilité `generated_by`, `sent_at` | Moyenne |

### 4.6 Types d'attestation (TYPE)

| ID | Exigence | Priorité |
|----|----------|----------|
| TYPE-01 | CRUD types d'attestation (admin) | Haute |
| TYPE-02 | Configuration durée validité (valeur + unité : jours/mois/ans) | Haute |
| TYPE-03 | Choix modèle PDF (model1, model2, model3, model4) | Haute |
| TYPE-04 | Upload fichiers (signature, logo, images template) | Haute |

### 4.7 Paiements (PAY)

| ID | Exigence | Priorité |
|----|----------|----------|
| PAY-01 | CRUD paiements | Haute |
| PAY-02 | Liaison participant + session + attestation | Haute |
| PAY-03 | Upload preuve de paiement (fichier) | Haute |
| PAY-04 | Téléchargement preuve de paiement | Haute |

### 4.8 Entreprises (ENT)

| ID | Exigence | Priorité |
|----|----------|----------|
| ENT-01 | CRUD entreprises (admin pour écriture) | Haute |
| ENT-02 | Champs : raison sociale, adresse, contacts | Haute |

### 4.9 Objectifs pédagogiques (OBJ)

| ID | Exigence | Priorité |
|----|----------|----------|
| OBJ-01 | CRUD catégories d'objectifs (admin) | Moyenne |
| OBJ-02 | CRUD objectifs liés à une formation (admin) | Moyenne |
| OBJ-03 | Réordonnancement par champ `ordre` | Moyenne |

### 4.10 Utilisateurs (USER)

| ID | Exigence | Priorité |
|----|----------|----------|
| USER-01 | CRUD administrateurs (admin) | Haute |
| USER-02 | CRUD gestionnaires (admin) | Haute |
| USER-03 | Actions groupées (bulk activate/deactivate/delete) | Moyenne |
| USER-04 | Forcer vérification e-mail (`force-verify`) | Moyenne |
| USER-05 | Terminer sessions actives (`terminate-sessions`) | Moyenne |
| USER-06 | Rôles : `admin`, `gestionnaire` | Haute |
| USER-07 | Statut actif/inactif | Haute |

### 4.11 Transversal (TRANS)

| ID | Exigence | Priorité |
|----|----------|----------|
| TRANS-01 | Notifications en base (création, lecture, mark-as-read) | Moyenne |
| TRANS-02 | Recherche globale multi-entités | Moyenne |
| TRANS-03 | Health check `/up` | Haute |
| TRANS-04 | CORS configuré pour frontend Vercel + localhost | Haute |
| TRANS-05 | Réponses JSON standardisées (success, message, data, errors) | Haute |

---

## 5. Modèle de données

### 5.1 Diagramme entité-relation

```
┌──────────────┐     ┌──────────────────┐     ┌───────────────┐
│    User      │     │    Formation     │     │TypeAttestation│
│──────────────│     │──────────────────│     │───────────────│
│ id (UUID)    │────<│ created_by       │>────│ id (UUID)     │
│ email        │     │ type_attestation │     │ model_choice  │
│ role         │     │ intitule, price  │     │ duration_*    │
│ actif        │     └────────┬─────────┘     └───────────────┘
└──────────────┘              │
                              │ 1:N
                              ▼
                   ┌──────────────────┐
                   │ SessionFormation │
                   │──────────────────│
                   │ id (UUID)        │
                   │ formation_id     │
                   │ date_debut/fin   │
                   │ lieu, statut     │
                   └────────┬─────────┘
                            │ N:M (pivot)
                            ▼
┌──────────────┐   ┌──────────────────┐   ┌──────────────┐
│  Entreprise  │   │   Participant    │   │ Attestation  │
│──────────────│   │──────────────────│   │──────────────│
│ id (UUID)    │<──│ entreprise_id    │──>│ participant  │
│ raison_soc.  │   │ nom, prenom      │   │ session_id   │
└──────────────┘   │ email, tel       │   │ numero       │
                   └──────────────────┘   │ qr_code_uuid │
                                          │ fichier_pdf  │
                                          └──────┬───────┘
                                                 │ 1:1
                                                 ▼
                                          ┌──────────────┐
                                          │   Payement   │
                                          │──────────────│
                                          │ montant      │
                                          │ preuve_path  │
                                          └──────────────┘
```

### 5.2 Tables principales

| Table | Clé primaire | Description |
|-------|-------------|-------------|
| `users` | UUID | Administrateurs et gestionnaires |
| `formations` | UUID | Catalogue de formations |
| `type_attestations` | UUID | Modèles et durées de validité |
| `session_formations` | UUID | Sessions planifiées |
| `participants` | UUID | Apprenants |
| `participant_session_formation` | — | Pivot inscription + `attestation_disponible` |
| `attestations` | auto-increment | Certificats générés |
| `payements` | auto-increment | Règlements |
| `entreprises` | UUID | Entreprises clientes |
| `objectif_categories` | auto-increment | Catégories d'objectifs |
| `objectifs` | auto-increment | Objectifs pédagogiques |
| `otps` | auto-increment | Codes OTP temporaires |
| `notifications` | UUID | Notifications utilisateur |
| `personal_access_tokens` | auto-increment | Tokens Sanctum |

### 5.3 Règles métier — Numérotation attestations

Format : `FEC/{ACRONYME}/{ANNÉE}/{ID}`

- **FEC** : préfixe fixe
- **ACRONYME** : 3 premières lettres des mots significatifs (> 4 caractères) de l'intitulé formation
- **ANNÉE** : année de création
- **ID** : identifiant attestation paddé sur 4 chiffres

Exemple : `FEC/INC/2026/0042` pour une formation "Conduite Incendie"

### 5.4 Règles métier — Expiration attestation

```
date_expiration = date_fin_session + duration_value × duration_unit
```

Unités supportées : `jours`, `mois`, `ans`

---

## 6. Exigences non fonctionnelles

### 6.1 Performance

- Response time API médian < 200 ms (hors génération PDF)
- Génération PDF < 5 s par attestation
- Import Excel 500 lignes < 30 s
- Support de 50 requêtes concurrentes

### 6.2 Sécurité

- Authentification Bearer token (Sanctum)
- OTP à usage unique avec expiration
- Mots de passe hashés (bcrypt, rounds=12)
- Middleware `auth:sanctum` sur toutes les routes privées
- Middleware `role:admin` sur routes admin
- Validation des entrées (Form Requests Laravel)
- CORS restrictif (origines whitelistées)
- Pas de données sensibles dans les réponses JSON (password hidden)

### 6.3 Fiabilité

- Migrations versionnées et réversibles
- Transactions DB pour opérations critiques (import, génération)
- Queue database pour envoi e-mails asynchrones
- Logs Laravel (channel stack)
- Health check endpoint

### 6.4 Déploiement

- Docker (PHP 8.3-cli, port 10000)
- Script entrypoint : migrate + seed + cache + serve
- Compatible SQLite (dev) et PostgreSQL/MySQL (prod)
- Variables d'environnement via `.env`

### 6.5 Maintenabilité

- Architecture MVC Laravel standard
- Contrôleurs API dans `app/Http/Controllers/Api/`
- Modèles Eloquent avec relations explicites
- Migrations datées et nommées
- Seeders pour données initiales

---

## 7. Architecture technique

### 7.1 Stack

```
PHP 8.2+ / Laravel 12
Laravel Sanctum (auth)
Eloquent ORM
DomPDF (PDF)
Maatwebsite Excel (import/export)
chillerlan/php-qrcode (QR)
SQLite / MySQL / PostgreSQL
```

### 7.2 Schéma d'architecture

```
┌──────────────┐         ┌─────────────────────────────────────┐
│ Frontend     │  HTTPS  │         Laravel Backend              │
│ React SPA    │────────>│  ┌─────────┐  ┌──────────────────┐  │
└──────────────┘  JSON   │  │ Routes  │→ │ Controllers/Api  │  │
                          │  └─────────┘  └────────┬─────────┘  │
                          │                        │             │
                          │  ┌─────────┐  ┌───────▼─────────┐   │
                          │  │Middleware│  │ Models/Eloquent │   │
                          │  │Sanctum   │  └───────┬─────────┘   │
                          │  │CheckRole │          │             │
                          │  └─────────┘  ┌───────▼─────────┐   │
                          │               │   Base de données  │   │
                          │               └─────────────────┘   │
                          │  ┌─────────┐  ┌─────────────────┐   │
                          │  │ DomPDF  │  │ Storage (files)  │   │
                          │  │ QR Code │  │ PDF, logos, etc. │   │
                          │  │ Mail    │  └─────────────────┘   │
                          │  └─────────┘                         │
                          └─────────────────────────────────────┘
```

### 7.3 Contrôleurs API

| Contrôleur | Responsabilité |
|------------|----------------|
| `AuthController` | Login, OTP, profil, logout |
| `ForgotPasswordController` | Reset password |
| `FormationController` | CRUD formations |
| `SessionFormationController` | CRUD sessions + participants |
| `ParticipantController` | CRUD + import Excel |
| `AttestationController` | CRUD + PDF + email + ZIP + verify |
| `TypeAttestationController` | CRUD types attestation |
| `PayementController` | CRUD + preuves |
| `EntrepriseController` | CRUD entreprises |
| `ObjectifController` | CRUD objectifs |
| `ObjectifCategorieController` | CRUD catégories |
| `AdministrateurController` | Gestion admins |
| `GestionnaireController` | Gestion gestionnaires |
| `NotificationController` | Notifications |
| `SearchController` | Recherche globale |

### 7.4 Templates PDF

Quatre modèles Blade dans `resources/views/certificates/` :

| Modèle | Fichier | Usage |
|--------|---------|-------|
| model1 | `model1.blade.php` | Attestation standard |
| model2 | `model2.blade.php` | Variante design 2 |
| model3 | `model3.blade.php` | Variante design 3 |
| model4 | `model4.blade.php` | Variante design 4 |

Chaque template intègre : logo FEC, informations participant, formation, dates, QR code, signature.

---

## 8. Spécification API

### 8.1 Conventions

- **Base URL :** `/api`
- **Format :** JSON (`Content-Type: application/json`)
- **Authentification :** `Authorization: Bearer {token}`
- **Codes HTTP :** 200 (OK), 201 (Created), 401 (Unauthorized), 403 (Forbidden), 404 (Not Found), 422 (Validation Error), 500 (Server Error)

### 8.2 Format de réponse standard

```json
{
  "success": true,
  "message": "Description de l'opération",
  "data": { }
}
```

Erreur de validation :

```json
{
  "success": false,
  "message": "Erreur de validation",
  "errors": {
    "email": ["Le champ email est obligatoire."]
  }
}
```

### 8.3 Routes publiques

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/login` | `{ email, password }` → token ou `require_otp` |
| POST | `/api/auth/verify-otp` | `{ email, otp }` → token |
| POST | `/api/auth/resend-otp` | `{ email }` |
| POST | `/api/auth/forgot-password` | `{ email }` |
| POST | `/api/auth/reset-password` | `{ email, token, password }` |
| GET | `/api/attestations/verify` | `?identifier={uuid\|numero}` |

### 8.4 Routes authentifiées (extrait)

| Méthode | Endpoint | Rôle |
|---------|----------|------|
| GET | `/api/auth/me` | Tous |
| POST | `/api/auth/logout` | Tous |
| GET/POST/PUT/DELETE | `/api/formations` | Lecture: tous, Écriture: admin |
| GET/POST/PUT/DELETE | `/api/session-formations` | Tous |
| POST | `/api/session-formations/{id}/participants/attach` | Tous |
| POST | `/api/session-formations/{id}/participants/detach` | Tous |
| GET/POST/PUT/DELETE | `/api/participants` | Tous |
| GET | `/api/participants/import/template` | Tous |
| POST | `/api/participants/import/verify` | Tous |
| POST | `/api/participants/import` | Tous |
| GET | `/api/attestations/generate/{session_id}/{participant_id}` | Tous |
| POST | `/api/attestations/send-email` | Tous |
| GET | `/api/attestations/export-zip/{session_id}` | Tous |
| GET/POST/PUT/DELETE | `/api/attestations` | Tous |
| GET/POST/PUT/DELETE | `/api/payements` | Tous |
| GET | `/api/payements/{id}/download-preuve` | Tous |
| GET/POST/PUT/DELETE | `/api/type-attestations` | Admin |
| GET/POST/PUT/DELETE | `/api/administrateurs` | Admin |
| GET/POST/PUT/DELETE | `/api/gestionnaires` | Admin |
| GET | `/api/notifications` | Tous |
| GET | `/api/search?q=` | Tous |

---

## 9. Contraintes et dépendances

### 9.1 Contraintes techniques

- PHP ≥ 8.2 avec extensions GD, ZIP, BCMath, PDO
- Composer pour gestion des dépendances
- Stockage fichiers accessible en écriture (`storage/app/`)
- Serveur SMTP configuré pour envoi e-mails (OTP, attestations)
- Queue worker actif pour jobs asynchrones

### 9.2 Contraintes réglementaires

- Données personnelles des participants — conformité légale
- Attestations = documents officiels — numérotation unique, QR code inviolable
- Conservation des PDF générés

### 9.3 Dépendances

| Composant | Version | Rôle |
|-----------|---------|------|
| laravel/framework | ^12.0 | Framework |
| laravel/sanctum | ^4.0 | Auth tokens |
| barryvdh/laravel-dompdf | ^3.1 | PDF |
| maatwebsite/excel | ^3.1 | Excel |
| chillerlan/php-qrcode | ^6.0 | QR codes |

---

## 10. Livrables

| Livrable | Description |
|----------|-------------|
| Code source Laravel | Dépôt Git backend |
| Migrations | 19 fichiers de schéma |
| Seeders | AdminSeeder, UserSeeder |
| Templates PDF | 4 modèles Blade |
| Templates e-mail | OTP, reset password, attestation |
| Dockerfile + entrypoint | Conteneurisation |
| Documentation | README.md, CAHIER_DES_CHARGES.md, LICENSE |
| Configuration | `.env.example`, `config/cors.php` |

---

## 11. Critères d'acceptation

### 11.1 Authentification

- [ ] Login retourne token ou demande OTP
- [ ] OTP vérifié émet un token Sanctum valide
- [ ] Routes protégées retournent 401 sans token
- [ ] Routes admin retournent 403 pour gestionnaire
- [ ] Reset password fonctionne de bout en bout

### 11.2 Métier

- [ ] CRUD complet sur toutes les entités
- [ ] Import Excel avec validation et rapport d'erreurs
- [ ] PDF généré avec QR code et numérotation correcte
- [ ] Vérification publique retourne statut valide/expiré/introuvable
- [ ] Export ZIP contient tous les PDF de la session
- [ ] E-mail d'attestation envoyé avec PDF joint

### 11.3 Technique

- [ ] Migrations s'exécutent sans erreur (`php artisan migrate`)
- [ ] Docker build et run fonctionnels
- [ ] Health check `/up` retourne 200
- [ ] CORS autorise le frontend Vercel
- [ ] Aucune fuite de données sensibles dans les réponses JSON

---

**Document rédigé par :** Free Engineering Consultancy (FEC)  
**Prochaine révision prévue :** Selon évolution fonctionnelle
