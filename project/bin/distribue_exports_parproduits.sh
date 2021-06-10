#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')distribue_exports_parproduits.sh $app;
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

EXPORTGLOBALDIR=web/exports_igp

rm -rf $EXPORTDIR/global_*.csv

for file in $(find "$EXPORTGLOBALDIR" -maxdepth 1 -type f -name "*.csv")
do
  FILENAME="global_$(basename $file)"
  for hash in $(echo $HASHPRODUIT | tr "," "\n")
  do
    if [ ! -f "$EXPORTDIR/$FILENAME" ]; then
      head -n 1 $file > $EXPORTDIR/$FILENAME
    fi
    cat $file | grep $hash --binary-files=text >> $EXPORTDIR/$FILENAME
  done
done
