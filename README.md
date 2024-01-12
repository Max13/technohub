# ITIC Paris – Intranet

## Intro
Bienvenue sur l'intranet de ITIC Paris, il est Open-Source dans le but d'inciter les étudiants à proposer des modifications, fonctionnalités ou des corrections d'erreurs.

## Démarrage
```bash
$ git checkout https://github.com/<dépôt>
$ cd <dépôt>
$ composer start
```

Pensez ensuite à remplir correctement le fichier `.env`, et vous pouvez utiliser `php artisan serve` pour tester le projet en local.

## Bonus
Voir `composer.json` pour les compatibilités. Le projet tourne sur `SQLite` et les textes sont écrits en Anglais puis traduits.

## Prod (Private repository)
```bash
$ composer config repositories.ypareo-auth vcs https://github.com/Max13/php-ypareauth.git
$ composer require max13/php-ypareauth:^1.0
```
