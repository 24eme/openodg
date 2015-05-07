Installation
============

# Dépendances

Pour Symfony :

$ sudo aptitude install couchdb libapache2-mod-php5 php5-cli php5-curl

Pour la génération des pdf en latex :

$ sudo aptitude install texlive-fonts-recommended texlive-latex-extra pdflatex pdftk texlive-lang-french texlive-lang-greek

# Récupération

Récupération du projet

 > git clone https://github.com/24eme/ava.git

Aller dans le dossier ava/project/

 > cd ava/project

# Configuration

Copier le fichier de configuration bin/config.inc

 > cp bin/config.inc{.example,}

Configurer le fichier bin/config.inc si besoin

        #bin/config.inc
        COUCHDBDOMAIN=your_couchdb_host
        COUCHDBPORT=your_couchdb_port
        COUCHDBBASE=your_database_name
 
Lancer le script d'installation :

 > bash bin/install.sh

Test avec un serveur web PHP :

 > php -S localhost:9000 -t web

Droit d'écriture apache sur les des dossiers cache et log

 > mkdir cache log

 > sudo chown www-data:your_user cache log

 > sudo chmod g+w cache log

Apache Virtual host :

        #ava.conf
        <VirtualHost *:80>
            ServerName declaration.dev.ava-aoc.fr
            DocumentRoot "/path_to/ava/project/web"
            DirectoryIndex index.php

            <Directory "/path_to/ava/project/web">
                AllowOverride All
                Require all granted
            </Directory>

            Alias /sf /path_to/ava/project/lib/vendor/symfony/data/web/sf

            <Directory "/path_to/ava/project/lib/vendor/symfony/data/web/sf">
                AllowOverride All
                Require all granted
            </Directory>
        </VirtualHost>

Récupération des données :

> git submodule init;
> git submodule update;

Import de la configuration :

> php symfony import:Configuration
