#!/bin/bash

if ! test -f "$1"; then
    echo "Fichier config requis";
    exit 1;
fi

CONFIGFILE=$1
ODG=$(cat $CONFIGFILE | jq '.file_name' | sed s/\"//g)
DATADIR="imports/$ODG"

if ! test "$ODG"; then
    echo "Nom du dossier de l'ODG non trouvé";
    exit 1;
fi

if ! test -f $DATADIR/operateurs.xlsx || ! file -i $DATADIR/operateurs.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/operateurs.xlsx manquant ou corrompu"
fi

if ! test -f $DATADIR/apporteurs_de_raisins.xlsx || ! file -i $DATADIR/apporteurs_de_raisins.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/apporteurs_de_raisins.xlsx manquant ou corrompu"
fi

if ! test -f $DATADIR/operateurs_inactifs.xlsx || ! file -i $DATADIR/operateurs_inactifs.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/operateurs_inactifs.xlsx manquant ou corrompu"
fi

if ! test -f $DATADIR/contacts.xlsx || ! file -i $DATADIR/contacts.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/contacts.xlsx manquant ou corrompu"
fi

if ! test -f $DATADIR/membres.xlsx || ! file -i $DATADIR/membres.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/membres.xlsx manquant ou corrompu"
fi

if ! test -f $DATADIR/habilitations.xlsx || ! file -i $DATADIR/habilitations.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/habilitations.xlsx manquant ou corrompu"
fi

if ! test -f $DATADIR/lots.xlsx || ! file -i $DATADIR/lots.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/lots.xlsx manquant ou corrompu"
fi

if ! test -f $DATADIR/changement_denom.xls || ! file -i $DATADIR/changement_denom.xls | grep "text/xml" > /dev/null; then
    echo "/!\ Fichier $DATADIR/changement_denom.xls manquant ou corrompu"
fi

if ! test -f $DATADIR/gestion_nc.xlsx || ! file -i $DATADIR/gestion_nc.xlsx | grep "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" > /dev/null; then
    echo "/!\ Fichier $DATADIR/gestion_nc.xls manquant ou corrompu"
fi


