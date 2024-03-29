Installation
============

# Dépendances

```
sudo aptitude install texlive-fonts-recommended texlive-latex-extra texlive-latex-base pdftk texlive-lang-french texlive-lang-greek latexmk curl gawk libjson-perl recode unzip jq xlsx2csv php-common php-curl php-json php-ldap php-readline php-gd php-xml php-mbstring
```

Installer couchdb : https://docs.couchdb.org/en/stable/install/unix.html#enabling-the-apache-couchdb-package-repository

En mode dév il est préférable d'appliquer cette procédure : https://sergio.24eme.fr/2020/10/19/demarrer-couchdb3-sans-mot-de-passe-admin/

Une fois installé couchdb est accessible ici : http://localhost:5984

# Récupération

Récupération du projet

```
git clone https://github.com/24eme/openodg.git
```

Aller dans le dossier openodg/project/

```
cd openodg/project
```

# Configuration

Copier et configurer le fichier de configuration config/databases.yml

```
cp config/databases.yml{.example,}
```

Copier le fichier de configuration config/app.yml

```
cp config/app.yml{.example,}
```

Création des dossier cache et log

```
mkdir cache log
```

Test avec un serveur web PHP :

```
php -S localhost:9000 -t web
```

