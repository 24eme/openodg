#!/bin/bash

. bin/config.inc

echo "Création et configuration du fichier de configuration config/databases.yml"
cat config/databases.yml.example | sed "s|dsn: http://localhost:5984/|dsn: http://$COUCHDBDOMAIN:$COUCHDBPORT/|" | sed "s|dbname: ava|dbname: $COUCHDBBASE|" > config/databases.yml

echo "Création du fichier de configuration apps/declaration/config/factories.yml"
cp apps/declaration/config/factories.yml{.example,}

echo "Création de la base de données"
curl -X PUT "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE"