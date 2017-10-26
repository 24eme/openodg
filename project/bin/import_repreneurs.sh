#!/bin/bash

. bin/config.inc

echo "Import des repris et repreneurs"

if ! test "$1"; then
    echo "Paramêtre de récupération des données obligatoire";
    exit 1;
fi


SYMFODIR=$(pwd);

LOGDATE=$SYMFODIR/$(date +%Y%m%d%H%M%S_import_repreneurs.log)

if [ ! -d "$TMPDIR/ODGRHONE_REPRENEURS_DATA" ]; then
rm -rf $TMPDIR/ODGRHONE_REPRENEURS_DATA
mkdir $TMPDIR/ODGRHONE_REPRENEURS_DATA 2> /dev/null
scp $1"/reprise_repreneur.csv" $TMPDIR/ODGRHONE_REPRENEURS_DATA/reprise_repreneur.csv
fi


php symfony import:repreneurs $TMPDIR/ODGRHONE_REPRENEURS_DATA/reprise_repreneur.csv  --application="declaration"
