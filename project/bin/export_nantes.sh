#!/bin/bash

. bin/config.inc

bash bin/export.sh

cd bin/notebook/ ;
if test -d pyenv ; then
    source pyenv/bin/activate
fi
python nantes_factures_linemorgane.py ;
cd -

php symfony export:facture-paiements $SYMFONYTASKOPTIONS >  $EXPORTDIR/factures_paiements.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/factures_paiements.csv.part > $EXPORTDIR/factures_paiements.csv
rm $EXPORTDIR/factures_paiements.csv.part
