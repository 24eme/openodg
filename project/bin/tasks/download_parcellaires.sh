#! /bin/bash
#
# Title: Téléchargement des parcellaires
# Description: Télécharge l'ensemble des parcellaires

source "$(dirname $0)/../config.inc"

IDS=$(mktemp)

curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/habilitation/_view/activites?reduce=false" | grep "PRODUCTEUR" | grep '"HABILITE"' | cut -d'-' -f2 | sort | uniq > "$IDS"

while read -r id; do
    echo "Import de l'opérateur $id"
    sudo -u www-data php "$WORKINGDIR/symfony" $SYMFONYTASKOPTIONS import:parcellaire-douanier "$id"
done < "$IDS"

rm "$IDS"
