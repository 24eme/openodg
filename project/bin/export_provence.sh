#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

php symfony export:etablissements-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/etablissements.csv.part
php symfony export:chais-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/chais.csv.part

mv $EXPORTDIR/etablissements.csv{.part,}
mv $EXPORTDIR/chais.csv{.part,}
