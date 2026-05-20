#!/bin/bash

ODG=igpatlantique

. bin/config.inc


xlsx2csv -s 4 -d ';' $TMPDIR/FICHIER_IGP_ATLANTIQUE_2025-2026-1.xlsx $TMPDIR/f4.csv
xlsx2csv -s 5 -d ';' $TMPDIR/FICHIER_IGP_ATLANTIQUE_2025-2026-1.xlsx $TMPDIR/f5.csv
xlsx2csv -s 6 -d ';' $TMPDIR/FICHIER_IGP_ATLANTIQUE_2025-2026-1.xlsx $TMPDIR/f6.csv
xlsx2csv -s 7 -d ';' $TMPDIR/FICHIER_IGP_ATLANTIQUE_2025-2026-1.xlsx $TMPDIR/f7.csv
grep -E '^[0-9]{2}-[0-9]{2}-[0-9]{2}' $TMPDIR/f4.csv > $TMPDIR/PVC.csv
grep -E '^[0-9]{2}-[0-9]{2}-[0-9]{2}' $TMPDIR/f5.csv > $TMPDIR/VC.csv
grep -E '^[0-9]{2}-[0-9]{2}-[0-9]{2}' $TMPDIR/f6.csv > $TMPDIR/C.csv
grep -E '^[0-9]{2}-[0-9]{2}-[0-9]{2}' $TMPDIR/f7.csv > $TMPDIR/P.csv
rm $TMPDIR/f4.csv
rm $TMPDIR/f5.csv
rm $TMPDIR/f6.csv
rm $TMPDIR/f7.csv

echo "Import des Opérateurs et Habilitations PVC"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/PVC.csv  --application="$ODG" --trace

echo "Import des Opérateurs et Habilitations VC"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/VC.csv  --application="$ODG" --trace

echo "Import des Opérateurs et Habilitations C"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/C.csv  --application="$ODG" --trace

echo "Import des Opérateurs et Habilitations P"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/P.csv  --application="$ODG" --trace
