#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')export_globalisefichiers.sh $app;
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

EXPORTGLOBALDIR=web/exports_igp
mkdir $EXPORTGLOBALDIR 2> /dev/null

for file in $(find "$EXPORTDIR" -maxdepth 1 -type f -name "*.csv")
do
	FILENAME=$(basename $file)
	if [ -f "$EXPORTGLOBALDIR/$FILENAME" ]; then
  	cat $file | sed 1,1d >> $EXPORTGLOBALDIR/$FILENAME
	else
		cat $file > $EXPORTGLOBALDIR/$FILENAME
	fi
done

head -n 1 $EXPORTGLOBALDIR/production.csv | iconv -f ISO88591 -t UTF8 > $EXPORTGLOBALDIR/production.csv.part
tail -n +2 $EXPORTGLOBALDIR/production.csv | iconv -f ISO88591 -t UTF8 | awk -F ';' '{uniq = $1"-"$2"-"$4"-"$17"-"$18"-"$19"-"$20"-"$29 ; if ( ! unicite[uniq] ) { print uniq";"$0  ; unicite[uniq] = 1 } }'  >> $EXPORTGLOBALDIR/production.csv.part
cat $EXPORTGLOBALDIR/production.csv.part | iconv -f UTF8 -t ISO88591 > $EXPORTGLOBALDIR/production.csv

for type in dr sv11 sv12 ; do
    head -n 1 $EXPORTGLOBALDIR/production.csv.part  > $EXPORTGLOBALDIR/$type".csv.part"
    cat $EXPORTGLOBALDIR/production.csv.part | grep -i "^"$type";" >> $EXPORTGLOBALDIR/$type".csv.part"
    cat $EXPORTGLOBALDIR/$type".csv.part" | iconv -f UTF8 -t ISO88591 > $EXPORTGLOBALDIR/$type".csv"
done
