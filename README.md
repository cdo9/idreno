# IDReno

Application Laravel 12 (Livewire 3) de gestion commerciale d'IDReno.

## Installation locale (Laravel Sail)

Prérequis : Docker Desktop, et une entrée dans `/etc/hosts` :

```bash
echo "127.0.0.1 idreno.local\n::1 idreno.local" | sudo tee -a /etc/hosts
```

Copie le fichier d'environnement :

```bash
cp .env.example .env
```

Installe les dépendances PHP (sans PHP local, via l'image Sail) :

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

Démarre les conteneurs, génère la clé et installe le front :

```bash
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail npm install
vendor/bin/sail npm run build
```

Importe la base : place le dump de production dans `db-dump.sql` (ignoré par git), puis :

```bash
sh reset-database.sh
```

Le script recrée la base, importe le dump, lance les migrations, met le mot de passe `password` sur tous les comptes (en local uniquement) et clone la base dans `idreno_testing` pour les tests.

L'application est alors disponible sur http://idreno.local:8081.

Les ports (8081 / 3307 / 5174) sont décalés par rapport à Homiso (80 / 3306 / 5173) pour pouvoir lancer les deux projets en même temps.

## Maintenance (CI/CD local)

Avec Sail démarré (`vendor/bin/sail up -d`) :

```bash
sh ci-cd.sh
```

Le script met à jour les dépendances Composer et npm, propose de monter les versions majeures, rafraîchit Laravel Boost, régénère les PHPDocs des modèles (ide-helper), formate le code (Pint), puis lance le build Vite, PHPStan, Pest et les audits de sécurité. Si une mise à jour de dépendances échoue, tout est restauré.

Pour que `git blame` ignore les commits de formatage Pint :

```bash
git config blame.ignoreRevsFile .git-blame-ignore-revs
```
