#!/bin/bash

EXPORTGLOBALDIR=web/exports_igp
mkdir $EXPORTGLOBALDIR 2> /dev/null

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then

    rm $EXPORTGLOBALDIR/*.csv

    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | sort -ur | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')export_globalisefichiers.sh $app;
    done

    grep -a FILTERED:DREV- $EXPORTGLOBALDIR/production.csv > $EXPORTGLOBALDIR/production.ligneavecdrev.csv
    grep -va FILTERED:DREV- $EXPORTGLOBALDIR/production.csv > $EXPORTGLOBALDIR/production.lignesansdrev.csv

    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

for file in $(find "$EXPORTDIR" -maxdepth 1 -type f -name "*.csv")
do
	FILENAME=$(basename $file)
	if [ -f "$EXPORTGLOBALDIR/$FILENAME" ]; then
  	cat $file | sed 1,1d >> $EXPORTGLOBALDIR/$FILENAME
	else
		cat $file > $EXPORTGLOBALDIR/$FILENAME
	fi
done
