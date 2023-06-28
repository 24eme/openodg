#!/bin/bash

fichier=$(dirname $0)/configs/$1

if ! test -f "$fichier"; then
    echo "Fichier config requis";
    exit 1;
fi

CONFIGFILE=$1

. $fichier

mkdir -p "$DOSSIER"/07_chais

echo $DEBUG_WITH_BROWSER
USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" node import/igp/scrapping_chais.js