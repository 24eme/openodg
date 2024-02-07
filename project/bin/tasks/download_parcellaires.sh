#! /bin/bash
#
# Title: Téléchargement des parcellaires
# Description: Télécharge l'ensemble des parcellaires

source "$(dirname $0)/../config.inc"

IDS=$(mktemp)

curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/habilitation/_view/activites?reduce=false" | grep -- '"HABILITE"' | cut -d'-' -f2 | sort | uniq > "$IDS"

while read -r id; do
    echo "Import de l'opérateur $id"
    php "$WORKINGDIR/symfony" $SYMFONYTASKOPTIONS import:parcellaire-douanier "$cvi"
done < "$IDS"

rm "$IDS"
