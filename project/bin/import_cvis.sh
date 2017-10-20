#!/bin/bash

. bin/config.inc

echo "Ajout des cvis manquants"

if ! test "$1"; then
    echo "Paramêtre de récupération des données obligatoire";
    exit 1;
fi


SYMFODIR=$(pwd);

LOGDATE=$SYMFODIR/$(date +%Y%m%d%H%M%S_import_metayers.log)

if [ ! -d "$TMPDIR/ODGRHONE_CVI_DATA" ]; then
rm -rf $TMPDIR/ODGRHONE_CVI_DATA
mkdir $TMPDIR/ODGRHONE_CVI_DATA 2> /dev/null
scp $1"/cvi.csv" $TMPDIR/ODGRHONE_CVI_DATA/cvi.csv
fi


php symfony import:etablissements-cvis $TMPDIR/ODGRHONE_CVI_DATA/cvi.csv --trace  --application="declaration"
