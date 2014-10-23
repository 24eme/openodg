#!/bin/bash

. bin/config.inc

echo "Suppression de la Base"
curl -X DELETE "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE"

echo "Création de la Base"
curl -X PUT "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE"

echo "Import de la Configuration 2013"
php symfony import:Configuration 2013

echo "Import de la Configuration 2014"
php symfony import:Configuration 2014

echo "Import des opérateurs"
bash bin/import/operateur.sh

echo "Import des déclarations de Revendication"
bash bin/import/drev.sh

echo "Import des déclarations de Revendication de Marc de Gewurtz."
bash bin/import/drevmarc.sh