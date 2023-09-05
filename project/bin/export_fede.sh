#!/bin/bash
campagne="$1"
if ! test "$campagne"; then
    campagne=$(date '+%Y')
fi

cd ~/prodouane_scrapy

bash bin/download_all.sh

mkdir -p documents/fede
for dep in  37 41 44 49 79 86; do
    rsync "documents/dr-"$campagne"-""$dep"*  documents/fede/
    rsync "documents/production-"$campagne"-""$dep"*  documents/fede/
done

cd -
ls documents/fede/dr-$campagne-*.xls | while read file; do
    csvfile=$(echo $echo | sed 's/.xls/.csv/')
    if ! test -f $csvfile; then
        php symfony douaneRecolte:convert2csv $file --application=igploire > $csvfile".csv";
    fi
done
ls documents/fede/production-$campagne-*.csv | while read file; do
    csvfile=$(echo $echo | sed 's/production-/sv-/') ;
    if ! test -f $csvfile; then
        php symfony douaneRecolte:convert2csv $file --application=igploire > $csvfile".csv";
    fi
done
rename 's/production-/sv-/' ~/prodouane_scrapy/documents/fede/production-$campagne-*.pdf

cd ~/prodouane_scrapy/documents/fede
mkdir -p final/documents
cp $(grep -lE ';ANJCDL;|;BON;|;SAVCDS;|;SAV;|;SAVRAM;|;CAJ;|;ANJ;|;AJV;|;RLO;|;SAU;|;CLO;|;SAUCHA;|;COB;|;COL;|;COS;|;QDC;|;RAJ;|;AJVBRI;' "dr-"$campagne"-"*csv "sv-"$campagne"-"*csv  | sed 's/.csv/*/') final/documents/
echo '#Type;Campagne;Identifiant;CVI;Raison Sociale;Code Commune;Commune;Bailleur Nom;Bailleur PPM;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;INAO;Produit;Complement;Code;Categorie;Valeur;CVI Tiers;Valeur Motif / Raison Sociale Tiers;Code Commune Tiers;Commune Tiers;Id Colonne;Organisme;Hash produit;Last DRev id if exist;Last DRev id with produit filter if exist;Doc Id;Famille calculee;Millesime;Famille ligne calculee;label calculee' > "final/dr-"$campagne".csv"
cat final/documents/*csv >> "final/dr-"$campagne".csv"
