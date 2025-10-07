# 🍃 Configuration MongoDB avec Symfony (Scénario Idéal)

## 📌 Introduction

Ce document explique **comment MongoDB aurait été configuré** si les problèmes de compatibilité TLS avec Heroku n'existaient pas.

> ⚠️ **Note** : Cette configuration ne fonctionne pas actuellement sur Heroku à cause de l'incompatibilité entre l'extension PHP MongoDB et le niveau TLS requis par MongoDB Atlas.

---

## 🎯 Architecture avec MongoDB

```
📦 Application EcoRide
├── 🗄️ MySQL (ClearDB)
│   ├── Users
│   ├── Trips
│   ├── Reservations
│   ├── Payments
│   └── Vehicles
│
└── 🍃 MongoDB (Atlas)
    └── TripReviews (Avis sur les trajets)
```

**Avantage** : Séparation des données relationnelles (MySQL) et documents (MongoDB)

---

## 📦 1. Installation des dépendances

### Composer (PHP)

```bash
# Installer le bundle MongoDB pour Symfony
composer require doctrine/mongodb-odm-bundle

# Installer l'extension PHP MongoDB (si pas déjà installée)
# Sur Heroku : automatique via buildpack PHP
# En local (macOS) :
pecl install mongodb
```

### Vérifier l'extension PHP

```bash
php -m | grep mongodb
# Devrait afficher : mongodb
```

---

## 🔧 2. Configuration de MongoDB Atlas

### 2.1 Créer un cluster gratuit

1. Aller sur [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
2. Créer un compte gratuit
3. Créer un cluster **M0 (Free Tier)**
4. Région : Choisir **AWS / Europe (Ireland)** ou proche de votre serveur Heroku
5. Nom du cluster : `ecoride-cluster`

### 2.2 Créer un utilisateur de base de données

1. Dans Atlas : **Database Access** → **Add New Database User**
2. Username : `ecoride_user`
3. Password : Générer un mot de passe sécurisé (ex: `MySecurePass123!`)
4. Permissions : **Read and write to any database**

### 2.3 Autoriser l'accès réseau

1. Dans Atlas : **Network Access** → **Add IP Address**
2. Pour Heroku : Cliquer sur **Allow Access from Anywhere** (`0.0.0.0/0`)
   - ⚠️ En production : Limiter aux IPs de Heroku

### 2.4 Obtenir la connection string

1. Dans Atlas : **Clusters** → **Connect** → **Connect your application**
2. Driver : **PHP** version **1.13 or later**
3. Connection string :
   ```
   mongodb+srv://ecoride_user:MySecurePass123!@ecoride-cluster.xxxxx.mongodb.net/ecoride_reviews?retryWrites=true&w=majority
   ```

---

## ⚙️ 3. Configuration Symfony

### 3.1 Fichier `.env` (local)

```env
# MySQL (existant)
DATABASE_URL="mysql://user:password@127.0.0.1:3306/ecoride_db"

# MongoDB (nouveau)
MONGODB_URL="mongodb+srv://ecoride_user:MySecurePass123!@ecoride-cluster.xxxxx.mongodb.net/ecoride_reviews?retryWrites=true&w=majority"
MONGODB_DB="ecoride_reviews"
```

### 3.2 Fichier `config/packages/doctrine_mongodb.yaml`

```yaml
doctrine_mongodb:
    connections:
        default:
            server: '%env(MONGODB_URL)%'
            options:
                connectTimeoutMS: 5000      # Timeout de connexion : 5 secondes
                socketTimeoutMS: 30000      # Timeout de requête : 30 secondes
                retryWrites: true           # Réessayer les écritures en cas d'échec
                w: 'majority'               # Niveau de garantie d'écriture
    
    default_database: '%env(MONGODB_DB)%'
    
    document_managers:
        default:
            auto_mapping: true
            mappings:
                App:
                    type: annotation
                    dir: '%kernel.project_dir%/src/Document'
                    prefix: 'App\Document'
                    is_bundle: false
```

**Explications** :
- `server` : URL de connexion MongoDB Atlas
- `connectTimeoutMS` : Temps max pour établir la connexion
- `socketTimeoutMS` : Temps max pour une requête
- `retryWrites: true` : Réessaye automatiquement si échec réseau
- `w: 'majority'` : Garantit que les écritures sont confirmées par la majorité des nœuds
- `auto_mapping: true` : Détecte automatiquement les Documents
- `dir: '%kernel.project_dir%/src/Document'` : Dossier des Documents MongoDB

### 3.3 Enregistrer le bundle dans `config/bundles.php`

```php
<?php

return [
    // ... autres bundles
    Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle::class => ['all' => true],
];
```

---

## 📄 4. Créer le Document MongoDB

### 4.1 Structure du dossier

```
src/
├── Document/              ← Documents MongoDB
│   └── TripReview.php
├── Entity/                ← Entities MySQL
│   ├── User.php
│   ├── Trip.php
│   └── ...
└── Repository/
    └── TripReviewRepository.php
```

### 4.2 Créer `src/Document/TripReview.php`

```php
<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "trip_reviews")]
class TripReview
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: "string")]
    private ?string $tripId = null;

    #[MongoDB\Field(type: "string")]
    private ?string $userId = null;

    #[MongoDB\Field(type: "string")]
    private ?string $userPseudo = null;

    #[MongoDB\Field(type: "string")]
    private ?string $comment = null;

    #[MongoDB\Field(type: "int")]
    private ?int $rating = null;

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // Getters et Setters

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTripId(): ?string
    {
        return $this->tripId;
    }

    public function setTripId(string $tripId): self
    {
        $this->tripId = $tripId;
        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserPseudo(): ?string
    {
        return $this->userPseudo;
    }

    public function setUserPseudo(string $userPseudo): self
    {
        $this->userPseudo = $userPseudo;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
```

**Différences clés avec Entity MySQL** :
- `#[MongoDB\Document]` au lieu de `#[ORM\Entity]`
- `#[MongoDB\Id]` génère un **ObjectId** (string) au lieu d'un INT
- `#[MongoDB\Field(type: "...")]` pour chaque propriété
- Pas besoin de migrations (schéma flexible)
- `src/Document/` au lieu de `src/Entity/`

---

## 🎛️ 5. Créer le Controller

### `src/Controller/TripReviewController.php`

```php
<?php

namespace App\Controller;

use App\Document\TripReview;
use App\Entity\Trip;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/trip/{id}/reviews', name: 'api_trip_reviews_')]
class TripReviewController extends AbstractController
{
    private DocumentManager $dm;              // MongoDB
    private EntityManagerInterface $em;        // MySQL

    public function __construct(DocumentManager $dm, EntityManagerInterface $em)
    {
        $this->dm = $dm;  // DocumentManager pour MongoDB
        $this->em = $em;  // EntityManager pour MySQL
    }

    /**
     * Ajouter un avis sur un trajet
     */
    #[Route('', name: 'add', methods: ['POST'])]
    public function add(int $id, Request $request): JsonResponse
    {
        // 1. Récupérer le trajet depuis MySQL
        $trip = $this->em->getRepository(Trip::class)->find($id);
        
        if (!$trip) {
            return $this->json(['error' => 'Trajet non trouvé'], 404);
        }

        // 2. Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        // 3. Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['comment']) || !isset($data['rating'])) {
            return $this->json(['error' => 'Commentaire et note requis'], 400);
        }

        // 4. Créer le document MongoDB
        $review = new TripReview();
        $review->setTripId((string) $trip->getId())      // Conversion INT → STRING
               ->setUserId((string) $user->getId())
               ->setUserPseudo($user->getPseudo())
               ->setComment($data['comment'])
               ->setRating((int) $data['rating'])
               ->setCreatedAt(new \DateTime());

        // 5. Sauvegarder dans MongoDB
        $this->dm->persist($review);   // DocumentManager (MongoDB)
        $this->dm->flush();

        return $this->json([
            'message' => 'Avis ajouté avec succès',
            'review' => [
                'id' => $review->getId(),
                'tripId' => $review->getTripId(),
                'userPseudo' => $review->getUserPseudo(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ], 201);
    }

    /**
     * Récupérer tous les avis d'un trajet
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function getReviews(int $id): JsonResponse
    {
        // 1. Vérifier que le trajet existe dans MySQL
        $trip = $this->em->getRepository(Trip::class)->find($id);
        
        if (!$trip) {
            return $this->json(['error' => 'Trajet non trouvé'], 404);
        }

        // 2. Récupérer les avis depuis MongoDB
        $reviews = $this->dm->getRepository(TripReview::class)
                           ->findBy(['tripId' => (string) $id]);  // Conversion INT → STRING

        // 3. Formater les résultats
        $data = array_map(function (TripReview $review) {
            return [
                'id' => $review->getId(),
                'userPseudo' => $review->getUserPseudo(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $reviews);

        return $this->json($data);
    }
}
```

**Points clés** :
- Injection de **2 managers** : `DocumentManager` (MongoDB) et `EntityManagerInterface` (MySQL)
- `Trip` vient de MySQL → `$this->em`
- `TripReview` va dans MongoDB → `$this->dm`
- Conversion des IDs **INT → STRING** pour MongoDB
- `persist()` et `flush()` sur `DocumentManager`

---

## 🚀 6. Déploiement sur Heroku

### 6.1 Configurer les variables d'environnement

```bash
# Connection string MongoDB Atlas
heroku config:set MONGODB_URL="mongodb+srv://ecoride_user:MySecurePass123!@ecoride-cluster.xxxxx.mongodb.net/ecoride_reviews?retryWrites=true&w=majority" --app ecoecoride

# Nom de la base de données
heroku config:set MONGODB_DB="ecoride_reviews" --app ecoecoride
```

### 6.2 Vérifier la configuration

```bash
heroku config --app ecoecoride
# Devrait afficher :
# MONGODB_URL: mongodb+srv://...
# MONGODB_DB:  ecoride_reviews
```

### 6.3 Déployer

```bash
git add -A
git commit -m "Add MongoDB for trip reviews"
git push heroku master
```

### 6.4 Tester la connexion MongoDB

```bash
heroku run "php bin/console doctrine:mongodb:query \"db.trip_reviews.find().limit(1)\"" --app ecoecoride
```

---

## 📊 7. Utilisation dans le Frontend

### JavaScript (`tripReview.js`)

```javascript
// Ajouter un avis
async function addReview(tripId, comment, rating) {
    const response = await authFetch(`${API_CONFIG.BASE_URL}/api/trip/${tripId}/reviews`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            comment: comment,
            rating: rating
        })
    });

    if (!response.ok) {
        throw new Error(`Erreur HTTP : ${response.status}`);
    }

    return await response.json();
}

// Récupérer les avis d'un trajet
async function loadReviews(tripId) {
    const response = await authFetch(`${API_CONFIG.BASE_URL}/api/trip/${tripId}/reviews`);
    
    if (!response.ok) {
        throw new Error(`Erreur HTTP : ${response.status}`);
    }

    const reviews = await response.json();
    
    // Afficher les avis
    reviews.forEach(review => {
        console.log(`${review.userPseudo} (${review.rating}/5) : ${review.comment}`);
    });
}
```

---

## 🔍 8. Commandes utiles

### Lister tous les avis

```bash
heroku run "php bin/console doctrine:mongodb:query \"db.trip_reviews.find()\"" --app ecoecoride
```

### Compter les avis d'un trajet

```bash
heroku run "php bin/console doctrine:mongodb:query \"db.trip_reviews.countDocuments({tripId: '4'})\"" --app ecoecoride
```

### Supprimer un avis

```bash
heroku run "php bin/console doctrine:mongodb:query \"db.trip_reviews.deleteOne({_id: ObjectId('...')})\"" --app ecoecoride
```

### Créer un index pour optimiser les recherches

```bash
heroku run "php bin/console doctrine:mongodb:schema:create --index" --app ecoecoride
```

---

## 📈 Avantages de MongoDB pour les avis

| Avantage | Explication |
|----------|-------------|
| **Schéma flexible** | Ajouter des champs sans migration (ex: photos, likes) |
| **Performance** | Optimisé pour les lectures massives de documents |
| **Évolutivité** | Scaling horizontal facile (sharding) |
| **Séparation** | Les avis n'impactent pas la base MySQL |
| **Données JSON** | Structure naturelle pour API REST |

---

## ⚠️ Limitations rencontrées sur Heroku

### Problème TLS

```
Error: TLS handshake failed: error:0A000438:SSL routines::tlsv1 alert internal error
```

**Cause** :
- L'extension PHP `mongodb` (version 2.1.1) sur Heroku n'est pas compatible avec le niveau de sécurité TLS 1.3 requis par MongoDB Atlas
- MongoDB Atlas **impose** TLS pour toutes les connexions (pas de désactivation possible)

**Solutions testées sans succès** :
```yaml
# ❌ Ne fonctionne pas
doctrine_mongodb:
    connections:
        default:
            options:
                tls: false                           # Ignoré par Atlas
                tlsAllowInvalidCertificates: true   # Ne résout pas le handshake
                tlsAllowInvalidHostnames: true      # Ne résout pas le handshake
```

**Conclusion** : Incompatibilité technique irrésoluble → Migration vers MySQL nécessaire.

---

## ✅ Quand utiliser MongoDB ?

MongoDB est idéal si :
- ✅ Vous avez un serveur compatible (pas Heroku PHP)
- ✅ Vous utilisez Node.js (excellente compatibilité)
- ✅ Vous avez un budget pour ObjectRocket ($95/mois)
- ✅ Vous gérez votre propre VPS
- ✅ Vous avez des données très flexibles (schéma changeant)
- ✅ Vous avez de gros volumes de données non-relationnelles

MongoDB n'est **pas** nécessaire si :
- ❌ Structure de données simple et fixe
- ❌ Relations claires entre les tables
- ❌ Petit volume de données
- ❌ MySQL déjà en place et fonctionnel

**Pour EcoRide** : MySQL est suffisant et plus adapté ✅

---

## 📚 Ressources

- [Documentation Doctrine MongoDB ODM](https://www.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/)
- [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
- [Symfony MongoDB Bundle](https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html)
- [MongoDB PHP Driver](https://www.php.net/manual/en/set.mongodb.php)

---

**Date** : 7 octobre 2025  
**Statut** : ⚠️ Configuration théorique (non applicable sur Heroku PHP)  
**Alternative retenue** : Migration vers MySQL ✅

