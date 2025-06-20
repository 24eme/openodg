#!/bin/bash

CONFIGFILE=$1

if ! test -f "$CONFIGFILE"; then
    echo "Fichier config requis";
    exit 1;
fi

. $CONFIGFILE

#USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" URLSITE="$URLSITE" node scrapping_aoc_chais.js
#
#USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" URLSITE="$URLSITE" node scrapping_aoc_commissions.js
#
#USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" URLSITE="$URLSITE" node scrapping_aoc_fiche_operateur.js
mkdir -p $DOSSIER"/01_operateurs/fiches"
xlsx2csv $DOSSIER"/operateurs.xlsx" | grep -v 'NumOp,RaisonSociale' | awk -F ',' '{print $1";"$2";"$13";"$14}' > $DOSSIER"/operateurs.csv"
PATHOPERATOR=$DOSSIER"/operateurs.csv" RAISON_SOCIALE="oui" USER="$USER" PASSWORD="$PASSWORD" DOSSIER="$DOSSIER" URLSITE="$URLSITE" node scrapping_aoc_fiche_operateur.js
