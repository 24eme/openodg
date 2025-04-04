#! /bin/bash
#
# Title: Import des DR
# Description: Récupère et importe les DR depuis le CIVA pour la campagne courante

APPLICATION=$1
CAMPAGNE=$(date '+%Y' -d '-9 month')

if test -f "$(dirname $0)/../../config_$APPLICATION.inc"; then
    source "$(dirname $0)/../../config_$APPLICATION.inc"
else
    source "$(dirname $0)/../../config.inc"
fi

cd "$(dirname $0)/../../.."

bash bin/import/get_dr_from_civa.sh $CAMPAGNE
bash bin/import/dr_in_drev.sh $CAMPAGNE
