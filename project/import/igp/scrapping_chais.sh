#!/bin/bash

CONFIGFILE=$1

if ! test -f "$CONFIGFILE"; then
    echo "Fichier config requis";
    exit 1;
fi

. $CONFIGFILE

mkdir -p "$DOSSIER"/07_chais

echo $DEBUG_WITH_BROWSER
USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" node import/igp/scrapping_chais.js