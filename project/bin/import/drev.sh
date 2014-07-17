#!/bin/bash

. bin/config.inc

WORKDIR=$TMPDIR/import_drev
WORKDIROPERATEUR=$TMPDIR/import_operateurs
DATADIR=data/import/extravitis/revendication
mkdir $WORKDIR 2> /dev/null

bash bin/import/operateur.sh

#---DOSSIER---

cat $DATADIR/drev_dossier.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3,4 | sort -t ";" -k 3,3 > $WORKDIR/dossier.csv

join -t ";" -1 1 -2 3 $WORKDIROPERATEUR/id_evv_cvi.sort_evv.csv $WORKDIR/dossier.csv | cut -d ";" -f 4,3,5 | sort -t ";" -k 2,2 > $WORKDIR/dossier_cvi.csv

#---LIGNE----

cat $DATADIR/drev_ligne.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,2 | sort -t ";" -k 2,2 > $WORKDIR/ligne.csv

join -t ";" -1 2 -2 2 $WORKDIR/dossier_cvi.csv $WORKDIR/ligne.csv | cut -d ";" -f 2,3,4 | sort -t ";" -k 2,2 > $WORKDIR/ligne_cvi.csv

#---VALEUR---

cat $DATADIR/drev_valeur.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 4,4 > $WORKDIR/valeur.csv

join -t ";" -1 2 -2 4 $WORKDIR/ligne_cvi.csv $WORKDIR/valeur.csv | cut -d ";" -f 1,2,3,5,6 | sed -r 's/^([0-9]+);([0-9]+);/\2;\1;/' | sed -r 's/;([0-9,]+);([0-9]+)$/;\2;\1/' | sort -t ";" -k 4,4 > $WORKDIR/valeur_cvi.csv

cat $DATADIR/drev_param_colonne.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,4 | sort -t ";" -k 1,1 > $WORKDIR/valeur_param.csv

join -t ";" -1 4 -2 1 $WORKDIR/valeur_cvi.csv $WORKDIR/valeur_param.csv | awk -F ";" '{ print $2 ";" $3 ";" $4 ";" $1 ";" $6 ";" $5 }' | sort > $WORKDIR/valeur_cvi_param.csv