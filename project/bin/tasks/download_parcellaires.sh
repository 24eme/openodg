#! /bin/bash
#
# Title: Téléchargement des parcellaires
# Description: Télécharge l'ensemble des parcellaires

source "$(dirname $0)/../config.inc"

bash ~/Code/prodouane_scrapy/bin/download_parcellaires.sh

for csv in ~/Code/prodouane_scrapy/documents/parcellaire-*.csv; do
    [ -e "$csv" ] || continue
    cvi=$(basename "$csv" ".csv" | cut -d'-' -f2)
    echo "Import du parcellaire du cvi $cvi"
    php "$WORKINGDIR/symfony" $SYMFONYTASKOPTIONS import:parcellaire-douanier "$cvi"
done
