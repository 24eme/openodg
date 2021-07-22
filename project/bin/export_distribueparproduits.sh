#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')export_distribueparproduits.sh $app;
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

EXPORTGLOBALDIR=web/exports_igp
EXPORTAPPDIR=web/exports_"$1"
EXPORTDIRFORGLOBAL=$EXPORTAPPDIR/GLOBAL

if [ -d "$EXPORTDIRFORGLOBAL" ]; then
  rm -rf $EXPORTDIRFORGLOBAL
fi

mkdir $EXPORTDIRFORGLOBAL 2> /dev/null

for file in $(find "$EXPORTGLOBALDIR" -maxdepth 1 -type f -name "*.csv")
do
  FILENAME="$(basename $file)"
  if [ ! -f "$EXPORTDIRFORGLOBAL/$FILENAME" ]; then
    head -n 1 $file > $EXPORTDIRFORGLOBAL/$FILENAME
  fi
  cat $file | grep -E $HASHPRODUIT --binary-files=text > $EXPORTDIRFORGLOBAL/"tmp_$FILENAME"
  cat $EXPORTAPPDIR/$FILENAME | sed 1,1d >> $EXPORTDIRFORGLOBAL/"tmp_$FILENAME"
  cat $EXPORTDIRFORGLOBAL/"tmp_$FILENAME" | sort | uniq >> $EXPORTDIRFORGLOBAL/$FILENAME
  rm $EXPORTDIRFORGLOBAL/"tmp_$FILENAME"
done
