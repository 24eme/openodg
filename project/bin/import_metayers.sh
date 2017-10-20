#!/bin/bash

. bin/config.inc

echo "Match des bailleurs et metayers"

if ! test "$1"; then
    echo "Paramêtre de récupération des données obligatoire";
    exit 1;
fi


SYMFODIR=$(pwd);

LOGDATE=$SYMFODIR/$(date +%Y%m%d%H%M%S_import_metayers.log)

if [ ! -d "$TMPDIR/ODGRHONE_METAYERS_DATA" ]; then
rm -rf $TMPDIR/ODGRHONE_METAYERS_DATA
mkdir $TMPDIR/ODGRHONE_METAYERS_DATA 2> /dev/null
scp $1"/metayers.csv" $TMPDIR/ODGRHONE_METAYERS_DATA/metayers.csv
fi


php symfony import:relations-etablissements $TMPDIR/ODGRHONE_METAYERS_DATA/metayers.csv  --application="declaration"
