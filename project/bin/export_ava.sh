#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

bash bin/export_docs.sh DRev > $EXPORTDIR/drev.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev.csv.part > $EXPORTDIR/drev.csv
rm $EXPORTDIR/drev.csv.part

bash bin/export_docs.sh DRevMarc > $EXPORTDIR/drev_marc.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev_marc.csv.part > $EXPORTDIR/drev_marc.csv
rm $EXPORTDIR/drev_marc.csv.part

bash bin/export_docs.sh Facture > $EXPORTDIR/facture.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/facture.csv.part > $EXPORTDIR/facture.csv
rm $EXPORTDIR/facture.csv.part

bash bin/export_docs.sh Parcellaire > $EXPORTDIR/parcellaire.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaire.csv.part > $EXPORTDIR/parcellaire.csv
rm $EXPORTDIR/parcellaire.csv.part

bash bin/export_docs.sh TravauxMarc > $EXPORTDIR/travaux_marc.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/travauxmarc.csv.part > $EXPORTDIR/travaux_marc.csv
rm $EXPORTDIR/travaux_marc.csv.part

bash bin/export_docs.sh Tirage > $EXPORTDIR/tirage.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/tirage.csv.part > $EXPORTDIR/tirage.csv
rm $EXPORTDIR/tirage.csv.part

bash bin/export_docs.sh RegistreVCI > $EXPORTDIR/registre_vci.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/registre_vci.csv.part > $EXPORTDIR/registre_vci.csv
rm $EXPORTDIR/registre_vci.csv.part

bash bin/export_docs.sh Constats > $EXPORTDIR/constats.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/constats.csv.part > $EXPORTDIR/constats.csv
rm $EXPORTDIR/constats.csv.part
