# 📚 Documentation : Migration MongoDB → MySQL pour les Avis (Reviews)

## 🔍 Pourquoi MongoDB n'a pas fonctionné avec Heroku ?

### ❌ Problèmes rencontrés avec MongoDB

#### 1. **MongoDB Atlas - Incompatibilité TLS**
- **Problème** : MongoDB Atlas impose l'utilisation de TLS/SSL pour toutes les connexions
- **Erreur** : `TLS handshake failed: error:0A000438:SSL routines::tlsv1 alert internal error`
- **Cause** : L'extension PHP MongoDB sur Heroku (version 2.1.1) n'est pas compatible avec le niveau de sécurité TLS exigé par MongoDB Atlas
- **Solutions tentées** :
  - ✗ Configuration `tls: false` dans `doctrine_mongodb.yaml`
  - ✗ Paramètres `tlsAllowInvalidCertificates: true`
  - ✗ Utilisation de `mongodb+srv://` (connection string DNS)
  - ✗ Utilisation de `mongodb://` (connection string standard)
  - **Résultat** : Toutes les tentatives ont échoué avec des erreurs de handshake TLS

#### 2. **Clever Cloud MongoDB - Plus de plan gratuit**
- **Problème** : Clever Cloud a supprimé son plan MongoDB gratuit (DEV)
- **Observation** : Tous les plans disponibles sont payants
- **Coût minimum** : Impossible à utiliser gratuitement

#### 3. **ObjectRocket MongoDB (Heroku Addon)**
- **Problème** : Le seul addon MongoDB disponible sur Heroku
- **Coût** : **~$95/mois** (plan minimum)
- **Conclusion** : Trop cher pour un projet étudiant/pédagogique

#### 4. **Autres alternatives envisagées**
- **Oracle Cloud Free Tier** : Nécessite une carte bancaire et configuration VPS complexe
- **MongoDB Community sur VPS** : Nécessite gestion d'infrastructure et maintenance

### 📊 Tableau comparatif des solutions

| Solution | Gratuit ? | Compatible Heroku ? | Complexité | Verdict |
|----------|-----------|---------------------|------------|---------|
| MongoDB Atlas | ✅ Oui | ❌ Non (TLS incompatible) | Moyenne | ❌ Impossible |
| Clever Cloud | ❌ Non | ✅ Oui | Faible | ❌ Payant |
| ObjectRocket | ❌ Non ($95/mois) | ✅ Oui | Faible | ❌ Trop cher |
| Oracle Cloud VPS | ✅ Oui | ⚠️ Externe | Élevée | ⚠️ Complexe |
| **MySQL (Heroku)** | ✅ Oui | ✅ Oui | Très faible | ✅ **SOLUTION RETENUE** |

---

## ✅ Solution adoptée : Migration vers MySQL

### 💡 Pourquoi MySQL ?

1. ✅ **Déjà configuré** : MySQL est déjà installé et fonctionnel sur Heroku (ClearDB addon)
2. ✅ **100% gratuit** : Inclus dans le plan gratuit Heroku
3. ✅ **Aucune configuration supplémentaire** : Pas besoin d'addon externe
4. ✅ **Performant** : Suffisant pour stocker des avis (reviews)
5. ✅ **Fiable** : Pas de problèmes de connexion TLS
6. ✅ **Bien supporté** : Doctrine ORM parfaitement intégré avec Symfony

---

## 🔄 Étapes de la migration MongoDB → MySQL

### 1. Créer l'entité MySQL `TripReview`

**Fichier créé** : `src/Entity/TripReview.php`

```php
<?php

namespace App\Entity;

use App\Repository\TripReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TripReviewRepository::class)]
#[ORM\Table(name: 'trip_review')]
class TripReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $tripId = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $userId = null;

    #[ORM\Column(length: 255)]
    private ?string $userPseudo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $rating = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    // Getters et Setters...
}
```

**Différences avec MongoDB** :
- MongoDB : `#[MongoDB\Document(collection: "trip_reviews")]`
- MySQL : `#[ORM\Entity(repositoryClass: TripReviewRepository::class)]`
- MongoDB : `#[MongoDB\Id]` (génère un ObjectId)
- MySQL : `#[ORM\Id]` + `#[ORM\GeneratedValue]` (génère un INT AUTO_INCREMENT)
- MongoDB : `#[MongoDB\Field(type: "string")]`
- MySQL : `#[ORM\Column(type: Types::INTEGER)]` ou `#[ORM\Column(length: 255)]`

---

### 2. Créer le Repository

**Fichier créé** : `src/Repository/TripReviewRepository.php`

```php
<?php

namespace App\Repository;

use App\Entity\TripReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TripReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripReview::class);
    }
}
```

---

### 3. Créer la migration de base de données

**Fichier créé** : `migrations/Version20251007200000.php`

```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251007200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create trip_review table for storing trip reviews in MySQL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trip_review (
            id INT AUTO_INCREMENT NOT NULL,
            trip_id INT NOT NULL,
            user_id INT NOT NULL,
            user_pseudo VARCHAR(255) NOT NULL,
            comment LONGTEXT NOT NULL,
            rating INT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_trip_id (trip_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE trip_review');
    }
}
```

**Commande pour exécuter la migration sur Heroku** :
```bash
heroku run "php bin/console doctrine:query:sql 'CREATE TABLE IF NOT EXISTS trip_review (...)'" --app ecoecoride
```

---

### 4. Mettre à jour le contrôleur `TripReviewController`

**Fichier modifié** : `src/Controller/TripReviewController.php`

**Avant (MongoDB)** :
```php
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\TripReview;

private DocumentManager $dm;

public function __construct(DocumentManager $dm, ...)
{
    $this->dm = $dm;
}

// Dans la méthode add()
$review = new TripReview();
$review->setTripId((string) $trip->getId()) // String pour MongoDB
       ->setUserId((string) $user->getId())
       // ...

$this->dm->persist($review);
$this->dm->flush();

// Dans la méthode getReviews()
$reviews = $this->dm->getRepository(TripReview::class)
                    ->findBy(['tripId' => (string) $tripId]);
```

**Après (MySQL)** :
```php
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\TripReview;

private EntityManagerInterface $em;

public function __construct(EntityManagerInterface $em, ...)
{
    $this->em = $em;
}

// Dans la méthode add()
$review = new TripReview();
$review->setTripId($trip->getId()) // INT pour MySQL
       ->setUserId($user->getId())
       // ...

$this->em->persist($review);
$this->em->flush();

// Dans la méthode getReviews()
$reviews = $this->em->getRepository(TripReview::class)
                    ->findBy(['tripId' => $tripId]);
```

**Changements principaux** :
- `DocumentManager` → `EntityManagerInterface`
- `App\Document\TripReview` → `App\Entity\TripReview`
- IDs en `string` → IDs en `int`
- `$this->dm` → `$this->em`

---

### 5. Supprimer les fichiers et configurations MongoDB

#### Fichiers supprimés :
1. ✅ `config/packages/doctrine_mongodb.yaml` (configuration MongoDB)
2. ✅ `src/Document/TripReview.php` (document MongoDB)
3. ✅ `src/Controller/MongoTestController.php` (contrôleur de test)
4. ✅ `src/Command/TripReviewPreviewCommand.php` (commande utilisant MongoDB)

#### Configuration modifiée :

**Fichier** : `config/bundles.php`
```php
// AVANT
Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle::class => ['all' => true],

// APRÈS (commenté)
// Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle::class => ['all' => true], // Désactivé - migration vers MySQL
```

**Fichier** : `config/services.yaml`
```yaml
# AVANT
bind:
    $profilesDirectory: '%profiles_directory%'

App\Command\:
    resource: '../src/Command'
    tags: ['console.command']

# APRÈS (commenté car dossier Command supprimé)
# bind:
#     $profilesDirectory: '%profiles_directory%'

# App\Command\:
#     resource: '../src/Command'
#     tags: ['console.command']
```

---

### 6. Déployer sur Heroku

```bash
# 1. Ajouter tous les changements
git add -A

# 2. Commit
git commit -m "Migrate reviews from MongoDB to MySQL"

# 3. Push vers Heroku
git push heroku master

# 4. Créer la table trip_review
heroku run "php bin/console doctrine:query:sql 'CREATE TABLE IF NOT EXISTS trip_review (...)'" --app ecoecoride
```

---

## 📈 Structure de la table MySQL

```sql
CREATE TABLE trip_review (
    id INT AUTO_INCREMENT NOT NULL,          -- ID auto-incrémenté
    trip_id INT NOT NULL,                    -- ID du trajet
    user_id INT NOT NULL,                    -- ID de l'utilisateur
    user_pseudo VARCHAR(255) NOT NULL,       -- Pseudo de l'utilisateur
    comment LONGTEXT NOT NULL,               -- Commentaire de l'avis
    rating INT NOT NULL,                     -- Note (1-5)
    created_at DATETIME NOT NULL,            -- Date de création
    PRIMARY KEY(id),                         -- Clé primaire
    INDEX IDX_trip_id (trip_id)              -- Index pour optimiser les requêtes par trip_id
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
```

---

## 🔄 Comparaison MongoDB vs MySQL pour les avis

| Critère | MongoDB | MySQL |
|---------|---------|-------|
| **Type de stockage** | NoSQL (Document) | SQL (Relationnel) |
| **Structure** | Flexible (JSON-like) | Rigide (schéma fixe) |
| **ID** | ObjectId (string) | INT AUTO_INCREMENT |
| **Requêtes** | Queries MongoDB | SQL standard |
| **Compatibilité Heroku** | ❌ Problèmes TLS | ✅ Parfait |
| **Coût sur Heroku** | 💰 $95/mois minimum | ✅ Gratuit |
| **Performance** | ⚡ Excellent (gros volumes) | ✅ Suffisant (notre cas) |
| **Maintenance** | ⚠️ Externe à gérer | ✅ Intégré Heroku |

**Pour notre cas d'usage** (avis de trajets) :
- Volume de données : ✅ Faible à moyen → MySQL parfait
- Besoin de flexibilité NoSQL : ❌ Non → Structure fixe suffit
- Coût : ✅ Gratuit avec MySQL vs $95/mois MongoDB
- Complexité : ✅ Plus simple avec MySQL (déjà configuré)

---

## ✅ Résultat final

### Avantages de la solution MySQL :
1. ✅ **Fonctionne parfaitement** : Aucune erreur de connexion
2. ✅ **100% gratuit** : Inclus dans le plan Heroku
3. ✅ **Aucune configuration externe** : Tout est dans Heroku
4. ✅ **Performance suffisante** : Pour le volume d'avis attendu
5. ✅ **Maintenance simplifiée** : Une seule base de données (MySQL) pour tout
6. ✅ **Cohérence** : Toutes les données au même endroit

### Fonctionnalités opérationnelles :
- ✅ Ajouter un avis sur un trajet
- ✅ Lister les avis d'un trajet
- ✅ Affichage du pseudo, commentaire, note et date
- ✅ Persistance des données

---

## 📝 Conclusion

**MongoDB était la solution initiale prévue** pour stocker les avis car :
- Flexibilité NoSQL
- Structure de données simple (document JSON)
- Pas de relations complexes

**MAIS** les contraintes techniques et financières sur Heroku ont rendu cette solution **impossible** :
- ❌ Incompatibilité TLS avec MongoDB Atlas
- ❌ Pas d'addon MongoDB gratuit sur Heroku
- ❌ Solutions externes complexes ou payantes

**La migration vers MySQL s'est révélée être la meilleure solution** :
- ✅ Simple à mettre en œuvre
- ✅ Gratuit
- ✅ Performant
- ✅ Déjà configuré sur Heroku

**Résultat** : Application 100% fonctionnelle avec une base de données unique (MySQL) pour toutes les fonctionnalités ! 🎉

---

## 🔗 Liens utiles

- [Documentation Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)
- [Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/latest/index.html)
- [Heroku MySQL (ClearDB)](https://devcenter.heroku.com/articles/cleardb)
- [MongoDB Atlas TLS Requirements](https://www.mongodb.com/docs/atlas/security/tls/)

---

**Date de migration** : 7 octobre 2025  
**Version déployée** : v42  
**Statut** : ✅ Migration réussie - Application fonctionnelle


