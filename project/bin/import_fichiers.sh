#!/bin/bash

. bin/config.inc

echo "Import des fichiers"

if ! test "$1"; then
    echo "Paramêtre de récupération des données obligatoire";
    exit 1;
fi

SYMFODIR=$(pwd);

LOGDATE=$SYMFODIR/$(date +%Y%m%d%H%M%S_import_data.log)

mkdir $TMPDIR/ODGRHONE_FICHIERS 2>/dev/null
scp $1 $TMPDIR/ODGRHONE_FICHIERS/


echo "Création des fichiers";

TARGET=$(echo $TMPDIR/ODGRHONE_FICHIERS/ | sed 's,/,\\/,g')

for path in $TMPDIR/ODGRHONE_FICHIERS/* ; do
	
	TYPE=$(echo $path | sed 's/'$TARGET'//g' | cut -d '-' -f 1)
	TYPE=$(echo $TYPE | tr 'a-z' 'A-Z')
	IDENTIFIANT=$(echo $path | sed 's/\.[a-zA-Z0-9]*$//g' | cut -d '-' -f 3)
	ANNEE=$(echo $path | cut -d '-' -f 2)
	
	php symfony import:fichier $IDENTIFIANT $path --libelle="$TYPE $ANNEE issue de Prodouane" --papier="1" --date_depot="$ANNEE-01-01" --type="$TYPE" --annee="$ANNEE" --lien_symbolique=true --trace
	
done

