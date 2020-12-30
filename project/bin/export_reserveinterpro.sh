#!/bin/bash

. bin/config.inc

echo 'DREV,CAMPAGNE,Interloire ID,Raison socilae,CVI,SIRET,Reserve interpro,Date validation ODG,id doc' > $EXPORTDIR/TOURAINE/reserve_interpro.csv
cat web/exports/TOURAINE/drev.csv  | awk -F ';' '{print $41}' | sort -u | grep ^DREV | while read drev ; do
    php symfony document:get $SYMFONYTASKOPTIONS --hash='["type","campagne","identifiant","declarant/raison_sociale","declarant/cvi","declarant/siret","/declaration/certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/TOU/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT","validation_odg","_id"]' $drev 2> /dev/null  | sed 's/^\[//' | sed 's/\]$//' > /tmp/$$.csv && php symfony document:get $SYMFONYTASKOPTIONS $drev > /tmp/$$.drev
    if test -f "/tmp/$$.drev" ; then
        reserve=$( echo $( jq '.declaration."certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/TOU/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT"' < /tmp/$$.drev | grep dont_volume_revendique_reserve_interpro | sed 's/,//g' | awk '{print $2 " + "}' ) | sed 's/$/0/' | bc -l )
        if ! test "$reserve" = "0"; then
            sed 's/{}/'$reserve'/' /tmp/$$.csv
        fi
    fi
    rm /tmp/$$.csv
    rm /tmp/$$.drev
done >> $EXPORTDIR/TOURAINE/reserve_interpro.csv
