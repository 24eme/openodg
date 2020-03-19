. bin/config.inc

if ! test "$1"; then
    echo "Campagne de la DR Requise";
    exit 1;
fi

CAMPAGNE=$1
CAMPAGNE_FACTURATION=$(($CAMPAGNE+1))

echo "Récupération des DR en CSV et en pdf"
bash bin/import/get_dr_from_civa.sh "$CAMPAGNE"

echo "Génération des DREV à partir des DR"
bash bin/import/dr_in_drev.sh "$CAMPAGNE" data/dr/$CAMPAGNE

echo "Génération des abonnements"
bash bin/compte/update.sh "php symfony abonnement:generate \$id $CAMPAGNE_FACTURATION --application=ava"

echo "Génération des registres VCI ayant consituté du VCI à partir des DR"
php symfony import:VCIFromDR $CAMPAGNE data/dr/$CAMPAGNE.csv --application=ava
