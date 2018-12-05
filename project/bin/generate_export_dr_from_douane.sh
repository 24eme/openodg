#!/bin/bash

cd $(dirname $0)/.. > /dev/null 2>&1

. bin/config.inc

cd ~/prodouane_scrapy/

if test "$1"; then
  ANNEE=$1 ;
else
  ANNEE=$(date '+%Y') ;
fi

bash bin/download_all.sh $ANNEE dr
bash bin/download_all.sh $ANNEE sv11
bash bin/download_all.sh $ANNEE sv12

cd -

find ~/prodouane_scrapy/documents/ -name '[ds][rv]*-'$ANNEE'*.xls' | while read xls ; do
  php symfony --application=provence douaneRecolte:convert2csv  $xls
done > $EXPORTDIR"/dr_"$ANNEE".csv.tmp"

mv $EXPORTDIR"/dr_"$ANNEE".csv.tmp" $EXPORTDIR"/dr_"$ANNEE".csv"
