#!/bin/bash

. bin/config.inc

echo "Suppression de la Base"
curl -X DELETE "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE"

echo "Création de la Base"
curl -X PUT "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE"

echo "Import de la Configuration"
php symfony import:Configuration

echo "Import des opérateurs"
bash bin/import/operateur.sh

echo "Import des déclarations de Revendication"
bash bin/import/drev.sh