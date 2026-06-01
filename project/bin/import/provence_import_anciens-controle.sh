#!/bin/bash

. $(dirname $0)/../config.inc

awk -F ';' '{print $1" "$7}' "$1"  | grep CDP0 | sed 's/  / /g' | while read cdp annee ; do
    if ! test "$annee"; then
        continue;
    fi
    anneemoinsun=$annee
    (( anneemoinsun-- ))
    controledate=$annee"-03-31"
    notificationdate=$annee"-06-31"
    controleiddate=$annee"0331"
    controlecampagne=$anneemoinsun"-"$annee
    type_tournee="Contrôle Importé"

    #echo "$cdp;$type;$extra"
    echo '{"_id":"CONTROLE-CDPIDENTIFIANT-'$controleiddate'","type":"Controle","date_tournee":"'$controledate'","parcelles":{"IMPORT": {}},"date":"'$controledate'","identifiant":"CDPIDENTIFIANT","campagne":"'$controlecampagne'","type_tournee":"'$type_tournee'","notification_date":"'$notificationdate'","mouvements_statuts":[["CONTROLE","Controle","CLOTURE","CDPIDENTIFIANT"]]}' | sed 's/CDPIDENTIFIANT/'$cdp'01/g' | curl -X PUT -d @/dev/stdin "http://"$COUCHHOST":5984/"$COUCHDBBASE"/CONTROLE-"$cdp"01-"$controleiddate
    php symfony document:save $SYMFONYTASKOPTIONS "CONTROLE-"$cdp"01-"$controleiddate
done
