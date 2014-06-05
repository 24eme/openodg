Installation
============

Récupération du projet

 > git clone git@gitorious.org:ava/ava.git

Aller dans le dossier ava/project/

 > cd ava/project

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
 > cd web
 > php -S localhost:9000

Droit d'écriture apache sur les des dossiers cache et log

 > sudo chown www-data:your_user cache log
 > sudo chmod g+w cache log

Apache Virtual host:

 #ava.conf
 <VirtualHost *:80>
    ServerName declaration.dev.ava-aoc.fr
    DocumentRoot "/home/vince/www/ava/project/web"
    DirectoryIndex index.php

    <Directory "/home/vince/www/ava/project/web">
        AllowOverride All
        Require all granted
    </Directory>

    Alias /sf /home/vince/www/ava/project/lib/vendor/symfony/data/web/sf
  
    <Directory "/home/vince/www/ava/project/lib/vendor/symfony/data/web/sf">
        AllowOverride All
        Require all granted
    </Directory>
 </VirtualHost>

