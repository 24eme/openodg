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
  xvfb-run -a --server-args="-screen 0 1400x1800x24" node scrapping.js $CONFIGFILE
fi

bash commission2csv.sh $DATADIR/04_controles_produits
bash cepage2csv.sh $DATADIR/06_administration
