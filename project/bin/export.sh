#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

php symfony export:etablissements-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/etablissements.csv.part
sort $EXPORTDIR/etablissements.csv.part > $EXPORTDIR/etablissements.csv.part.sorted
mv $EXPORTDIR/etablissements.csv.part{.sorted,}
cat $EXPORTDIR/etablissements.csv.part | grep -E "^IdOp" > $EXPORTDIR/etablissements.csv
cat $EXPORTDIR/etablissements.csv.part | grep -Ev "^IdOp" >> $EXPORTDIR/etablissements.csv
cat $EXPORTDIR/etablissements.csv | iconv -f UTF8 -t ISO88591//TRANSLIT > $EXPORTDIR/etablissements.iso8859.csv

php symfony export:chais-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/chais.csv.part
mv $EXPORTDIR/chais.csv{.part,}
cat $EXPORTDIR/chais.csv | iconv -f UTF8 -t ISO88591//TRANSLIT >$EXPORTDIR/chais.iso8859.csv

bash bin/export_docs.sh DRev > $EXPORTDIR/drev.csv.part
mv $EXPORTDIR/drev.csv{.part,}

bash bin/export_docs.sh Habilitation > $EXPORTDIR/habilitation.csv.part
mv $EXPORTDIR/habilitation.csv{.part,}
php symfony export:habilitation-demandes $SYMFONYTASKOPTIONS > $EXPORTDIR/habilitation_demandes.csv.part
mv $EXPORTDIR/habilitation_demandes.csv{.part,}
php bin/export/export_liste_inao.php $EXPORTDIR/habilitation_demandes.csv > $EXPORTDIR/habilitation_demandes_inao.csv.part
mv $EXPORTDIR/habilitation_demandes_inao.csv{.part,}
php symfony export:habilitation-demandes-publipostage $SYMFONYTASKOPTIONS > web/exports/habilitation_demandes_publipostage.csv.part
mv $EXPORTDIR/habilitation_demandes_publipostage.csv{.part,}

bash bin/export_docs.sh DR > $EXPORTDIR/dr.csv.part
mv $EXPORTDIR/dr.csv{.part,}

bash bin/export_docs.sh SV12 > $EXPORTDIR/sv12.csv.part
mv $EXPORTDIR/sv12.csv{.part,}

bash bin/export_docs.sh SV11 > $EXPORTDIR/sv11.csv.part
mv $EXPORTDIR/sv11.csv{.part,}
