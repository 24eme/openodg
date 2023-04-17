#! /bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

if ! test "$ASSVAS_ETABLISSEMENT_FILE_URL"; then
    echo "La variable ASSVAS_ETABLISSEMENT_FILE_URL contenant l'url du fichier à importé n'est pas défini"
    exit;
fi

XLSX_IMPORT="$TMPDIR"/import_etablissements_assvas.xslx
CSV_IMPORT="$TMPDIR"/import_etablissements_assvas.csv

CHECKSUM=$(md5sum $XLSX_IMPORT)

wget $ASSVAS_ETABLISSEMENT_FILE_URL -O $XLSX_IMPORT
xlsx2csv -d ";" "$XLSX_IMPORT" > "$CSV_IMPORT"

if test "$CHECKSUM" = "$(md5sum $XLSX_IMPORT)"; then
    exit;
fi

php symfony import:etablissements-assvas $SYMFONYTASKOPTIONS "$CSV_IMPORT" --trace
