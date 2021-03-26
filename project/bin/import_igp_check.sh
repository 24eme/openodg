#!/bin/bash

if ! test "$1"; then
    echo "Nom du dossier/de l'ODG";
    exit 1;
fi

ODG=$1

. bin/config_$ODG.inc

if test $ODG = "igp13"
then

    if test $(curl -s localhost:5984/openodg_import/_design/mouvement/_view/lot | grep "03_PRELEVE" | grep "DEGUSTATION-20210308" | wc -l) != 18
    then
        echo "IGP13;ERROR;La Dégustation 20-16 n'a pas 18 lots"
    fi

fi