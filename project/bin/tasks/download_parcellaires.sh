#! /bin/bash
#
# Title: Téléchargement des parcellaires
# Description: Télécharge l'ensemble des parcellaires

source "$(dirname $0)/../config.inc"

IDS=$(mktemp)

curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/habilitation/_view/activites?reduce=false" | grep "PRODUCTEUR" | grep '"HABILITE"' | cut -d'-' -f2 | sort | uniq > "$IDS"

while read -r id; do
    echo "Import de l'opérateur $id"
    if [ "$(curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/ETABLISSEMENT-$id" | jq . | grep "statut" | cut -d'"' -f4)" = "ACTIF" ]; then
        sudo -u www-data "$WORKINGDIR/bin/import_parcellaire.sh" "$id"
    else
        echo "ETABLISSEMENT-$id;WARNING;Opérateur archivé, parcellaire non mis à jour"
    fi
done < "$IDS"

rm "$IDS"
