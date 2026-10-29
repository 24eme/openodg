#! /bin/bash
#
# Title: Import des DR et VCI constitué
# Description: Récupère et importe les DR depuis le CIVA et importe le VCI constitué dans les registres VCI pour la campagne courante

APPLICATION=$1
CAMPAGNE=$(date '+%Y' -d '-9 month')

if test -f "$(dirname $0)/../../config_$APPLICATION.inc"; then
    source "$(dirname $0)/../../config_$APPLICATION.inc"
else
    source "$(dirname $0)/../../config.inc"
fi

cd "$(dirname $0)/../../.."

echo "Récupération des $CAMPAGNE chez le CIVA"
bash bin/import/get_dr_from_civa.sh $CAMPAGNE
echo "Import des DR $CAMPAGNE"
bash bin/import/dr_in_drev.sh $CAMPAGNE
echo "Création des registres VCI $CAMPAGNE"
php symfony import:VCIFromDR $CAMPAGNE data/dr/$CAMPAGNE.csv $SYMFONYTASKOPTIONS
