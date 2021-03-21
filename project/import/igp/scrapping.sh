#!/bin/bash

if ! test -f "$1"; then
    echo "Fichier config requis";
    exit 1;
fi

CONFIGFILE=$1
ODG=$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)
DATADIR="imports/$ODG"

if ! test "$ODG"; then
    echo "Nom du dossier de l'ODG non trouvé";
    exit 1;
fi

if [ -d $DATADIR ]; then
  rm -r $DATADIR;
fi
mkdir -p $DATADIR


if test "$DISPLAY"; then
  node scrapping.js $CONFIGFILE
else
  xvfb-run -a --server-args="-screen 0 1366x768x24" node scrapping.js $CONFIGFILE
fi

#bash script_verify.sh $CONFIGFILE

echo "date;code;campagne;millesime;responsable;lieu_nom;lieu_adresse;lieu_code_postal;lieu_ville;type_ligne;operateur;appellation;couleur;cepage;volume;logement;type_lot;passage;degre;doc;numero_anonymat;conformite;motif_refus;commentaire" > $DATADIR/commissions.csv
ls $DATADIR/commission_*.html | while read file; do nodejs parse_commisson.js $file; done >> $DATADIR/commissions.csv
