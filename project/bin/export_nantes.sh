#!/bin/bash

. bin/config.inc

#bash bin/export.sh

php symfony export:facture $SYMFONYTASKOPTIONS >  $EXPORTDIR/factures.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/factures.csv.part > $EXPORTDIR/factures.csv
rm $EXPORTDIR/factures.csv.part

cd bin/notebook/ ; python nantes_factures_linemorgane.py ; cd -

php symfony export:facture-paiements $SYMFONYTASKOPTIONS >  $EXPORTDIR/factures_paiements.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/factures_paiements.csv.part > $EXPORTDIR/factures_paiements.csv
rm $EXPORTDIR/factures_paiements.csv.part
