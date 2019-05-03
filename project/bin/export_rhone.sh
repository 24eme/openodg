#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

bash bin/export_docs.sh DREV > $EXPORTDIR/drev.csv
bash bin/export_docs.sh HABILITATION > $EXPORTDIR/habilitation.csv
bash bin/export_docs.sh DR > $EXPORTDIR/dr.csv
bash bin/export_docs.sh SV12 > $EXPORTDIR/sv12.csv
bash bin/export_docs.sh SV11 > $EXPORTDIR/sv11.csv
