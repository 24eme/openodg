#!/bin/bash

. bin/config.inc

cat data/import/extravitis/operateur/EVV.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3,4,5,6,7,8,9,10,11,12 | sort -t ";" -k 1,1 > /tmp/cvi.csv
cat data/import/extravitis/operateur/PPM_EVV_MFV.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 3,4 | sort | uniq | sort -t ";" -k 2,2  > /tmp/id_evv.csv

join -t ";" -1 2 -2 1 /tmp/id_evv.csv /tmp/cvi.csv | sort -t ";" -k 2,2 | cut -d ";" -f 1,2,3 | sort -t ";" -k 2,2 > /tmp/id_evv_cvi.csv

cat data/import/extravitis/operateur/COMMUNICATION.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,3,4,8,9,10,11,12,13,14 | sort -t ";" -k 2,2 > /tmp/communication.csv

join -t ";" -1 2 -2 2 /tmp/id_evv_cvi.csv /tmp/communication.csv | cut -d ";" -f 2,3,4,5,6,7,8,9,10,11,12 | sort -t ";" -k 5,5 > /tmp/communication_cvi.csv








