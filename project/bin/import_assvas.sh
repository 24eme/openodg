#! /bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

XLSX_IMPORT=$1
CSV_IMPORT="$TMPDIR"/import_etablissements_assvas.csv

xlsx2csv -d ";" "$XLSX_IMPORT" > "$CSV_IMPORT"

cd ..
make clean
make
cd - || exit

php symfony import:etablissements-assvas $SYMFONYTASKOPTIONS "$CSV_IMPORT" --trace
