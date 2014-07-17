#!/bin/bash

. bin/config.inc

cat data/import/extravitis/operateur/EVV.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3,4,5,6,7,8,9,10,11 | sort -t ";" -k 1,1 | sed 's/ ;/;/g' | grep -E ";6[0-9]{7,12};"  > /tmp/evv.csv

cat data/import/extravitis/operateur/PPM_EVV_MFV.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 3,4 | sort | uniq | sort -t ";" -k 2,2  > /tmp/id_evv.csv

join -t ";" -1 2 -2 1 /tmp/id_evv.csv /tmp/evv.csv | sort -t ";" -k 2,2 | cut -d ";" -f 1,2,3 | sort -t ";" -k 2,2  > /tmp/id_evv_cvi.csv

cat /tmp/evv.csv | cut -d ";" -f 2,3,4,5,6,7,8,9,10,11 | sed -r 's/^([0-9]+);/\1;1.CVI;/' > /tmp/cvi.csv

cat data/import/extravitis/operateur/COMMUNICATION.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3,4,8,9,10,11,12,13,14 | sort -t ";" -k 2,2 > /tmp/communication.csv

join -t ";" -1 2 -2 2 /tmp/id_evv_cvi.csv /tmp/communication.csv | cut -d ";" -f 3,7,8,9,10,11,12 | sed -r 's/^([0-9]+);/\1;2.COM;/' > /tmp/communication_cvi.csv

cat data/import/extravitis/operateur/COORDONNEES.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3,4,5,6,7,8,10,11,12,13,14,15,16,17 | sort -t ";" -k 2,2 > /tmp/coordonnees.csv

join -t ";" -1 2 -2 2 /tmp/id_evv_cvi.csv /tmp/coordonnees.csv | cut -d ";" -f 3,7,8,9,10,11,12,13,14,15,16,17 | sed -r 's/^([0-9]+);/\1;3.COO;/' > /tmp/coordonnees_cvi.csv

cat data/import/extravitis/operateur/PPM_ATTRIBUTS.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 3,3 > /tmp/attributs.csv

cat data/import/extravitis/operateur/PPM_ATTRIBUT_REF.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 1,1 > /tmp/attributs_ref.csv

join -t ";" -1 3 -2 1 /tmp/attributs.csv /tmp/attributs_ref.csv | sort -t ";" -k 3,3 > /tmp/attributs.join.csv

join -t ";" -1 2 -2 3 /tmp/id_evv_cvi.csv /tmp/attributs.join.csv | cut -d ";" -f 3,7 | sed -r 's/^([0-9]+);/\1;4.ATT;/' > /tmp/attributs_cvi.csv

cat /tmp/cvi.csv /tmp/communication_cvi.csv /tmp/coordonnees_cvi.csv /tmp/attributs_cvi.csv | sort



