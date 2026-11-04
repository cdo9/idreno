# Homiso

Homiso est une application web développée avec [Laravel](https://laravel.com), un framework PHP moderne et robuste.

## Installation

```bash
Cloner le projet
```

Installe les dépendances PHP :

```bash
composer install
```

Installe les dépendances front-end :

```bash
npm install
```

Copie le fichier d'environnement et configure-le :

```bash
cp .env.example .env
```

Génère la clé d'application :

```bash
php artisan key:generate
```

Configure ta base de données dans `.env`, puis lance les migrations :

```bash
php artisan migrate
```

Pour avoir le hot reload :

```bash
npm run dev
```

Pour compiler les assets pour la production :

```bash
npm run build
```

Lance le serveur de développement :

```bash
php artisan serve
```

## Licence

Ce projet est sous licence [MIT](LICENSE).