#! /bin/bash
#
# Title: Téléchargement des parcellaires
# Description: Télécharge l'ensemble des parcellaires

APPLICATION=$1

source "$(dirname $0)/../config.inc"

if test -f "$(dirname $0)/../config.$APPLICATION.inc"; then
    source "$(dirname $0)/../config.$APPLICATION.inc"
else
    source "$(dirname $0)/../config.inc"
fi

IDS=$(mktemp)

curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/habilitation/_view/activites?reduce=false" | grep "PRODUCTEUR" | grep '"HABILITE"' | cut -d'-' -f2 | sort | uniq > "$IDS"

if ! grep -E '[0-9]+' "$IDS"; then
    curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE//_design/etablissement/_view/all?reduce=false" | grep "PRODUCTEUR" | grep -v "SUSPENDU" | cut -d '"' -f 4 | cut -d "-" -f 2 | sort | uniq > "$IDS"
fi

while read -r id; do
    echo "Import de l'opérateur $id"
    if [ "$(curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/ETABLISSEMENT-$id" | jq . | grep "statut" | cut -d'"' -f4)" = "ACTIF" ]; then
        sudo -u www-data "$WORKINGDIR/bin/import_parcellaire.sh" "$id"
    else
        echo "ETABLISSEMENT-$id;WARNING;Opérateur archivé, parcellaire non mis à jour"
    fi
done < "$IDS"

rm "$IDS"
