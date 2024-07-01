# BILEMO : Créer un web service exposant une API

**Formation OpenClassrooms**
Projet n°7 : Créer un web service exposant une API

## Présentation

**BileMo** est une entreprise offrant une sélection de téléphones mobiles haut de gamme. Son modèle économique repose sur la vente en B2B (business to business), en fournissant aux plateformes un accès à son catalogue via une API (Application Programming Interface).

## Installation

Pour installer le projet, exécutez les commandes suivantes dans votre terminal :

```bash
git clone https://github.com/lisavincent31/Vincent_Lisa_1_repository_git_052024.git
cd Vincent_Lisa_1_repository_git_052024
composer install
```

### Configuration de la base de donnée
Configurez votre base de données dans le fichier **.env** à la racine du dossier. Assurez-vous d'avoir configuré les paramètres appropriés pour votre environnement de développement.

Une fois votre fichier configuré vous pouvez initialiser la base de données avec les commandes suivantes :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load
```

### Configuration des Fixtures

Si vous rencontrez une erreur concernant les commandes de fixtures, 
- vérifier dans votre fichier **.env** d'avoir la clé **APP_ENV=dev** ;
- si l'erreur persiste, installez le bundle nécessaire avec :

```bash
composer require doctrine/doctrine-fixtures-bundle --dev
```

### Configurer le JWT Token

Pour utiliser l'authentification JWT, générez les clés nécessaires dans le répertoire **config/jwt**. Assurez-vous de sécuriser votre clé privée avec une passphrase.

```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Modifier votre fichier **.env** pour spécifier les chemins vers vos clés JWT et la passphrase utilisée :

``` .env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=mot_de_passe
```

## Lancer le serveur

```bash
symfony serve:start
```

## Documentation

Retrouvez toute la documentation du projet BileMo en suivant ce lien :
[Documentation](http://127.0.0.1:8000/api/doc)

