#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

php symfony export:etablissements-csv $SYMFONYTASKOPTIONS | sort > $EXPORTDIR/etablissements.csv.part
cat $EXPORTDIR/etablissements.csv.part | grep -E "^IdOp" > $EXPORTDIR/etablissements.csv.sorted.part
cat $EXPORTDIR/etablissements.csv.part | grep -Ev "^IdOp" >> $EXPORTDIR/etablissements.csv.sorted.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/etablissements.csv.sorted.part > $EXPORTDIR/etablissements.en.csv
cat $EXPORTDIR/etablissements.en.csv | sed 's/;/ø/g' | awk -F ',' 'BEGIN { OFS=";" }{ $1=$1; print $0 }' | sed 's/ø/,/g' > $EXPORTDIR/etablissements.csv
rm $EXPORTDIR/etablissements.csv.part $EXPORTDIR/etablissements.csv.sorted.part
ln -s etablissements.en.csv $EXPORTDIR/etablissements.iso8859.csv # Pour l'AVPI en provence

#php symfony export:chais-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/chais.csv.part
echo "test"
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/chais.csv.part > $EXPORTDIR/chais.en.csv
cat $EXPORTDIR/chais.en.csv | sed 's/;/ø/g' | awk -F ',' 'BEGIN { OFS=";" }{ $1=$1; print $0 }' | sed 's/ø/,/g' > $EXPORTDIR/chais.csv
rm $EXPORTDIR/chais.csv.part
ln -s chais.en.csv $EXPORTDIR/chais.iso8859.csv # Pour l'AVPI en provence

bash bin/export_docs.sh DRev > $EXPORTDIR/drev.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev.csv.part > $EXPORTDIR/drev.csv
rm $EXPORTDIR/drev.csv.part

bash bin/export_docs.sh Habilitation > $EXPORTDIR/habilitation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation.csv.part > $EXPORTDIR/habilitation.csv
rm $EXPORTDIR/habilitation.csv.part

php symfony export:habilitation-demandes $SYMFONYTASKOPTIONS > $EXPORTDIR/habilitation_demandes.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes.csv.part > $EXPORTDIR/habilitation_demandes.csv
php bin/export/export_liste_inao.php $EXPORTDIR/habilitation_demandes.csv.part > $EXPORTDIR/habilitation_demandes_inao.csv.part
mv $EXPORTDIR/habilitation_demandes_inao.csv{.part,}
rm $EXPORTDIR/habilitation_demandes.csv.part

bash bin/export_docs.sh DR > $EXPORTDIR/dr.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/dr.csv.part > $EXPORTDIR/dr.csv
rm $EXPORTDIR/dr.csv.part

bash bin/export_docs.sh SV12 > $EXPORTDIR/sv12.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv12.csv.part > $EXPORTDIR/sv12.csv
rm $EXPORTDIR/sv12.csv.part

bash bin/export_docs.sh SV11 > $EXPORTDIR/sv11.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv11.csv.part > $EXPORTDIR/sv11.csv
rm $EXPORTDIR/sv11.csv.part

php symfony export:habilitation-demandes-publipostage $SYMFONYTASKOPTIONS > web/exports/habilitation_demandes_publipostage.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes_publipostage.csv.part > $EXPORTDIR/habilitation_demandes_publipostage.csv
rm $EXPORTDIR/habilitation_demandes_publipostage.csv.part
