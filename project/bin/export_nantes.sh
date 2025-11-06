#!/bin/bash

. bin/config.inc

if ! test "$SYMFONYTASKOPTIONS" ; then
    exit;
fi

bash bin/export.sh

sed -i 's/ 20[23][0-9] / /' $EXPORTDIR/factures.csv

cd bin/notebook/ ;
if test -d pyenv ; then
    source pyenv/bin/activate
fi
python3 nantes_factures_synthese.py ;
cd -

php symfony export:facture-paiements $SYMFONYTASKOPTIONS >  $EXPORTDIR/factures_paiements.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/factures_paiements.csv.part > $EXPORTDIR/factures_paiements.csv
rm $EXPORTDIR/factures_paiements.csv.part
