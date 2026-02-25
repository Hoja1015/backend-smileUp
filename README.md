# Résid'Up — Backend Symfony 7.3

## Prérequis
- PHP 8.3+
- Composer
- MySQL 8.0 (ou PostgreSQL 16)
- Symfony CLI

## Installation complète

### 1. Créer le projet Symfony

```bash
symfony new residup --webapp --version="7.3"
cd residup
```

### 2. Installer les dépendances supplémentaires

```bash
# JWT Authentication
composer require lexik/jwt-authentication-bundle

# CORS
composer require nelmio/cors-bundle

# Fixtures (données de test)
composer require --dev doctrine/doctrine-fixtures-bundle
```

### 3. Copier les fichiers de ce dossier

Copie tous les fichiers de ce dossier dans ton projet Symfony :
- `src/Entity/` → dans `src/Entity/`
- `src/Controller/` → dans `src/Controller/`
- `src/Repository/` → dans `src/Repository/`
- `src/DataFixtures/` → dans `src/DataFixtures/`
- `config/packages/` → dans `config/packages/`
- `.env` → à la racine (ou adapte le `.env` existant)

### 4. Configurer `.env`

Édite le fichier `.env` et adapte :

```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/residup?serverVersion=8.0&charset=utf8mb4"
JWT_PASSPHRASE=change_me_with_a_secure_passphrase
```

### 5. Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

### 6. Créer la base de données et les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate --no-interaction
```

### 7. Charger les données initiales (activités, badges, événements)

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

### 8. Lancer le serveur

```bash
symfony server:start --port=8000
```

L'API sera accessible sur http://localhost:8000/api

---

## Routes API

| Méthode | Route                         | Auth | Description                                 |
|---------|-------------------------------|------|---------------------------------------------|
| POST    | /api/login                    | Non  | Connexion → JWT token                       |
| POST    | /api/register                 | Non  | Inscription → JWT + user (XP=0 garanti)     |
| GET     | /api/me                       | Oui  | Profil utilisateur connecté                 |
| PATCH   | /api/me                       | Oui  | Modifier son profil                         |
| GET     | /api/activities               | Oui  | Liste + état de progression par utilisateur |
| POST    | /api/activities/{id}/start    | Oui  | Lancer une activité                         |
| PATCH   | /api/activities/{id}/progress | Oui  | Mettre à jour progression (→ XP si 100%)    |
| GET     | /api/events                   | Oui  | Événements à venir                          |
| GET     | /api/leaderboard              | Oui  | Classement par XP décroissant               |
| GET     | /api/badges                   | Oui  | Tous les badges (unlocked/locked)           |

---

## Exemples d'appels API

### Inscription
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"alex@univ.fr","password":"motdepasse","firstName":"Alex","code":"RES-COLBERT-2025"}'
```

Réponse : `{ "token": "eyJ...", "user": { "id": 1, "xp": 0, "badges": [], "level": "Bronze", ... } }`

### Connexion
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"alex@univ.fr","password":"motdepasse"}'
```

### Récupérer les activités
```bash
curl http://localhost:8000/api/activities \
  -H "Authorization: Bearer TOKEN"
```

---

## Logique de gamification

### Niveaux (calculés automatiquement par l'entité User)
| Niveau | XP requis | XP Max affiché |
|--------|-----------|----------------|
| Bronze | 0         | 500            |
| Silver | 500       | 2000           |
| Gold   | 2000      | 5000           |
| Legend | 5000      | 9999           |

### Attribution XP
- Quand `PATCH /api/activities/{id}/progress` est appelé avec `progress: 100`
- L'XP est attribué **une seule fois** (vérification `status !== 'completed'`)
- Le niveau est recalculé automatiquement

### Attribution badges
- Après chaque complétion d'activité, les badges sont vérifiés automatiquement
- Un badge ne peut être obtenu qu'une seule fois
- Conditions : XP minimum OU nombre d'activités complétées

### Nouvel utilisateur
Un utilisateur inscrit a **garantis** :
- `xp = 0`
- `badges = []`
- `level = 'Bronze'`
- Aucune activité démarrée

Ces valeurs ne viennent **jamais** du localStorage mais toujours de l'API.
