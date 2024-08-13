#!/bin/bash

CONFIGFILE=$1

if ! test -f "$CONFIGFILE"; then
    echo "Fichier config requis";
    exit 1;
fi

. $CONFIGFILE

USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" URLSITE="$URLSITE" node scrapping_aoc_chais.js

USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" URLSITE="$URLSITE" node scrapping_aoc_commissions.js

USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" URLSITE="$URLSITE" node scrapping_aoc_fiche_operateur.js
