#!/bin/bash

cd $(dirname $0)/.. > /dev/null 2>&1

. bin/config.inc

cd ~/prodouane_scrapy/


bash bin/download_all.sh $(date '+%Y') dr
bash bin/download_all.sh $(date '+%Y') sv11
bash bin/download_all.sh $(date '+%Y') sv12

cd -

find ~/prodouane_scrapy/documents/ -name '[ds][rv]*-2018*xls' | while read xls ; do
  php symfony --application=provence douaneRecolte:convert2csv  $xls
done > $EXPORTDIR"/dr_"$(date '+%Y')".csv"
