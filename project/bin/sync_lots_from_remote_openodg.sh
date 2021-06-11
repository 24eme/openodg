#!/bin/bash

. bin/config_$1.inc

DATE=$(date +%Y%m%d%H%M%S)

DATEFR=$(date +%d/%m/%Y)
TIMEFR=$(date +%Hh%M)

if ! test "$URL_EXPORT_REMOTE_OPENDOG"
then
    echo "Aucune instance distante d'openodg définie, set URL_EXPORT_REMOTE_OPENDOG in config_extra.inc"
    exit;
fi

php symfony drev:import $URL_EXPORT_REMOTE_OPENDOG/drev.csv --application=$1 --byLots=1 > $TMPDIR"/import_lots_from_openodg_$DATE.log"

RAPPORTBODY=$TMPDIR"/import_lots_from_openodg_$DATE.mail"

echo -e "Voici le rapport d'import des lots provenant de la plateforme https://teledeclaration.vinsvaldeloire.pro/ \n\n" > $RAPPORTBODY
echo -e "Cet import a été effectué le $DATEFR à $TIMEFR \n\n" >> $RAPPORTBODY
echo -e "Ci dessous, veuillez trouver la liste des DREV qui ont été modifiées ou créées : \n\n" >> $RAPPORTBODY

grep "IMPORTE;" $TMPDIR"/import_lots_from_openodg_$DATE.log" | cut -d ';' -f 3 >> $RAPPORTBODY

cat $RAPPORTBODY | mail -s "[RAPPORT IMPORT DREV depuis https://teledeclaration.vinsvaldeloire.pro/ du $DATEFR à $TIMEFR]" $EMAILS_RAPPORT_IMPORT;
