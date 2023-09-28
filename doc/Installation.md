Installation
============

# Dépendances

```
sudo aptitude install texlive-fonts-recommended texlive-latex-extra texlive-latex-base pdftk texlive-lang-french texlive-lang-greek latexmk curl gawk libjson-perl recode unzip jq xlsx2csv php-common php-curl php-json php-ldap php-readline php-gd php-xml php-mbstring
```

Installer couchdb : https://docs.couchdb.org/en/stable/install/unix.html#enabling-the-apache-couchdb-package-repository

En mode dév : https://sergio.24eme.fr/2020/10/19/demarrer-couchdb3-sans-mot-de-passe-admin/

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

Copier le fichier de configuration bin/config.inc

```
cp bin/config.inc{.example,}
```

Configurer le fichier bin/config.inc si besoin

```
#bin/config.inc
COUCHDBDOMAIN=your_couchdb_host
COUCHDBPORT=your_couchdb_port
COUCHDBBASE=your_database_name
```

Création des dossier cache et log

```
mkdir cache log
```

Test avec un serveur web PHP :

```
php -S localhost:9000 -t web
```

