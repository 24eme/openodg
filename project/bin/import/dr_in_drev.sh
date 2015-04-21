#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

PATH_DR=$2

if ! test "$PATH_DR"; then
    echo "Vous devez spécifier le chemin vers les documents csv et pdf de la DR"
    exit;
fi

curl -s localhost:59681/declaration/_design/declaration/_view/tous | grep "DREV\-" | grep "\-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' | while read id  
do
    DR=$(echo $id | sed 's/DREV/DR/')
    php symfony drev:import-dr $id $PATH_DR/$DR.csv $PATH_DR/$DR.pdf
done

