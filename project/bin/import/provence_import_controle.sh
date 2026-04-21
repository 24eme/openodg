#!/bin/bash

. $(dirname $0)/../config.inc

awk -F ';' '{print $1" "$2" "$28}' "$1"  | grep CDP0 | sed 's/  / /g' | while read cdp type extra ; do
    controledate=20260331
    controlecampagne="2026-2027"
    if echo $extra | grep 2025 > /dev/null; then
        controledate=20250331
        controlecampagne="2025-2026"
    fi
    type_tournee="Conditions de production"
    if test $type = "SUIVI"; then
    type_tournee="Suivi de manquements"
    elif test $type = "CONTRÔLE"; then
    type_tournee="Documentaire"
    elif test $type = "HABILITATION"; then
    type_tournee="Habilitation"
    fi

    #echo "$cdp;$type;$extra"
    echo '{"_id":"CONTROLE-CDPIDENTIFIANT-'$controledate'","type":"Controle","date":"'$controledate'","identifiant":"CDPIDENTIFIANT","campagne":"'$controlecampagne'","type_tournee":"'$type_tournee'","mouvements_statuts":[["CONTROLE","Controle","A_PLANIFIER","CDPIDENTIFIANT"]]}' | sed 's/CDPIDENTIFIANT/'$cdp'01/g' | curl -X PUT -d @/dev/stdin "http://"$COUCHHOST":5984/"$COUCHDBBASE"/CONTROLE-"$cdp"01-"$controledate
    php symfony document:save $SYMFONYTASKOPTIONS "CONTROLE-"$cdp"01-"$controledate
done
