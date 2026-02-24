#!/bin/bash

. bin/config.inc

if ! test "$SYMFONYTASKOPTIONS" ; then
    exit;
fi

sed -i 's/ 20[23][0-9] / /' $EXPORTDIR/factures.csv

cd bin/notebook/ ;
if test -d pyenv ; then
    source pyenv/bin/activate
fi
python3 nantes_factures_synthese.py ;
cd -
