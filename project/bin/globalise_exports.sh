#!/bin/bash

EXPORTGLOBALDIR=web/exports_igp

if [ $# -eq 0 ]; then
    echo 'Parametres manquants'
    exit 1
fi


mkdir $EXPORTGLOBALDIR 2> /dev/null

rm -rf $EXPORTGLOBALDIR/*.csv

for rep in $*
do
	for file in $(find "$rep" -maxdepth 1 -type f -name "*.csv")
	do
		FILENAME=$(basename $file)
		if [ -f "$EXPORTGLOBALDIR/$FILENAME" ]; then
    			cat $file | sed 1,1d >> $EXPORTGLOBALDIR/$FILENAME
    		else
    			cat $file > $EXPORTGLOBALDIR/$FILENAME
    		fi
    	done
done
