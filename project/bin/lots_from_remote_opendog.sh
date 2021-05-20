#!/bin/bash

. bin/config.inc

if test $1 && test -f $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc ; then
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

if ! test "$URL_EXPORT_REMOTE_OPENDOG"
then
    echo "Aucune instance distante d'openodg définie, set URL_EXPORT_REMOTE_OPENDOG in config_extra.inc"
    exit;
fi


DATA_DIR=$TMPDIR"/"$1;
mkdir $DATA_DIR 2> /dev/null

YESTERDAY=$(date --date '1 days ago' '+%Y-%m-%d');

cat $URL_EXPORT_REMOTE_OPENDOG/drev.csv | awk -F ';' -v y=$YESTERDAY '$40 ~ y { print $0 } ' > $DATA_DIR"/drev_"$YESTERDAY".csv"
LOGFILE=$DATA_DIR"/drev_"$YESTERDAY".log"

php symfony drev:import $DATA_DIR/drev_$YESTERDAY.csv --byLots=1 $SYMFONYTASKOPTIONS > $LOGFILE
echo "Log du jour dans $LOGFILE"
