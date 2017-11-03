#!/bin/bash

csv=$1
repertoire=$2

if ! test "$repertoire"; then
  echo "ERROR: besoin d'un csv et d'un repertoire en arguments";
  exit 1;
fi

cat $csv | sed 's/ *\([0-9][0-9][0-9][0-9]\) *$/;\1/' | sed 's/ *\.pdf//' | sed 's/id=/;/' | awk -F ";" '{print "php symfony import:fichier --application=declaration  --libelle=\""$2"\" --type=\""$6"\" --date_depot=\""$5"\" --visibilite=0 --papier=1 --annee=\""$7"\" "sprintf("%06d01", $1)" '$repertoire'"$4".pdf"}' | sh
