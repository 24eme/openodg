#!/bin/bash

. bin/config.inc

bash bin/export.sh

php symfony export:facture $SYMFONYTASKOPTIONS >  $EXPORTDIR/factures.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/factures.csv.part > $EXPORTDIR/factures.csv
rm $EXPORTDIR/factures.csv.part
