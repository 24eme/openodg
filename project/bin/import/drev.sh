#!/bin/bash

. bin/config.inc

WORKDIR=$TMPDIR/import_drev
WORKDIROPERATEUR=$TMPDIR/import_operateurs
DATADIR=data/import/extravitis/revendication
mkdir $WORKDIR 2> /dev/null

bash bin/import/operateur.sh

#===PRODUITS===

cat $DATADIR/AVA_GROUPE_VIN.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3 | sort -t ";" -k 1,1 > $WORKDIR/cepage.csv
cat $DATADIR/AVA_GRANDCRU.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 1,1 > $WORKDIR/grdcru.csv

#===REVENDICATION===

#---DOSSIER---

cat $DATADIR/drev_dossier.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3,4 | sort -t ";" -k 3,3 > $WORKDIR/dossier.csv

join -t ";" -1 1 -2 3 $WORKDIROPERATEUR/id_evv_cvi.sort_evv.csv $WORKDIR/dossier.csv | cut -d ";" -f 4,3,5 | sort -t ";" -k 2,2 > $WORKDIR/dossier_cvi.csv

#---LIGNE----

cat $DATADIR/drev_ligne.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,2 | sort -t ";" -k 2,2 > $WORKDIR/ligne.csv

join -t ";" -1 2 -2 2 $WORKDIR/dossier_cvi.csv $WORKDIR/ligne.csv | cut -d ";" -f 2,3,4 | sort -t ";" -k 3,3 > $WORKDIR/ligne_cvi.csv

#---VALEUR---

cat $DATADIR/drev_valeur.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 4,4 > $WORKDIR/valeur.csv

join -t ";" -1 3 -2 4 $WORKDIR/ligne_cvi.csv $WORKDIR/valeur.csv | cut -d ";" -f 1,2,3,5,6 | sed -r 's/^([0-9]+);([0-9]+);([0-9]+);/\2;\3;\1;/' | sed -r 's/;([0-9,]+);([0-9]+)$/;\2;\1/' | sort -t ";" -k 4,4 > $WORKDIR/valeur_cvi.csv

cat $DATADIR/drev_param_colonne.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,4 | sort -t ";" -k 1,1 > $WORKDIR/valeur_param.csv

join -t ";" -1 4 -2 1 $WORKDIR/valeur_cvi.csv $WORKDIR/valeur_param.csv | awk -F ";" '{ print $2 ";" $3 ";1.REVE;" $4 ";" $1 ";" $6 ";" $5 }' | sed 's/;30;Aoc Couleur;/;030;Aoc Couleur;/' | sort > $WORKDIR/valeur_cvi_param.csv

#===LOTS===

cat $DATADIR/AVA_NOMBRE_LOTS.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 2,3,4,5,6,7 | sort -t ";" -k 4,4 > $WORKDIR/lot.csv

join -t ";" -1 4 -2 1 $WORKDIR/lot.csv $WORKDIR/cepage.csv | sort -t ";" -k 1,1 | cut -d ";" -f 2,3,4,5,6,7 | sort -t ";" -k 1,1 > $WORKDIR/lot_cepage.csv

join -t ";" -1 2 -2 1 $WORKDIROPERATEUR/id_evv_cvi.csv $WORKDIR/lot_cepage.csv | cut -d ";" -f 3,4,5,6,7,8 | awk -F ";" '{ print $1 ";" $2 ";2.LOT ;;;;;" $5 ";" $3 ";" $6 ";" $4 }' > $WORKDIR/lot_cvi.csv

#===PRELEVEMENTS===

cat $DATADIR/AVA_DREV_PRELEVEMENT.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 1,1 > $WORKDIR/prelevement.csv

join -t ";" -1 2 -2 1 $WORKDIR/dossier_cvi.csv $WORKDIR/prelevement.csv | cut -d ";" -f 2,3,4,6,7,8 | awk -F ";" '{ print $1 ";" $2 ";3.PREL;;;;;" $3 ";;;" $6 ";" $4 ";" $5 }' > $WORKDIR/prelevement_cvi.csv

#===FINAL===

echo "cvi;annee;type ligne;rev num ligne;rev type id;rev type libelle;rev valeur;aoc;grdcru;cepage;nb lot;annee prelevement;semaine prelevement" > $WORKDIR/drev.csv

cat $WORKDIR/valeur_cvi_param.csv $WORKDIR/lot_cvi.csv $WORKDIR/prelevement_cvi.csv | sort | grep -E "^[0-9]+;2013;" >> $WORKDIR/drev.csv


