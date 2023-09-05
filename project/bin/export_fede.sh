#!/bin/bash
campagne="$1"
if !test "$campagne"; then
    campagne=$(date '+%Y')
fi

cd ~/prodouane_scrapy

bash bin/download_all.sh

mkdir documents/fede
for dep in  37 41 44 49 79 86; do
    cp "documents/dr-"$campagne"-""$dep"*  documents/fede/
    cp "documents/production-"$campagne"-""$dep"*  documents/fede/
done

cd -
ls documents/fede/dr-$campagne-*.xls | while read file; do
    csvfile=$(echo $echo | sed 's/.xls/.csv/')
    if ! test -f $csvfile; then
        php symfony douaneRecolte:convert2csv $file --application=igploire > $csvfile".csv";
    fi
done
ls documents/fede/production-$campagne-*.csv | while read file; do
    csvfile=$(echo $echo | sed 's/production-/sv-/') ;
    if ! test -f $csvfile; then
        php symfony douaneRecolte:convert2csv $file --application=igploire > $csvfile".csv";
    fi
done
rename 's/production-/sv-/' ~/prodouane_scrapy/documents/fede/production-$campagne-*.pdf

cd ~/prodouane_scrapy/documents/fede
mkdir final
cp $(grep -lE ';ANJCDL;|;BON;|;SAVCDS;|;SAV;|;SAVRAM;|;CAJ;|;ANJ;|;AJV;|;RLO;|;SAU;|;CLO;|;SAUCHA;|;COB;|;COL;|;COS;|;QDC;|;RAJ;|;AJVBRI;' dr*csv sv*csv  | sed 's/.csv/*/') final/
