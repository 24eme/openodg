#!/bin/bash

. bin/config.inc

echo "Import des fichiers"

if ! test "$1"; then
    echo "Paramêtre de récupération des données obligatoire";
    exit 1;
fi

SYMFODIR=$(pwd);

LOGDATE=$SYMFODIR/$(date +%Y%m%d%H%M%S_import_data.log)
ODGRHONE_FICHIERS=$1


echo "Création des fichiers";

TARGET=$(echo $ODGRHONE_FICHIERS/ | sed 's,/,\\/,g')

ls $ODGRHONE_FICHIERS/* | while read path ; do

	TYPE=$(echo $path | sed 's/'$TARGET'//g' | cut -d '-' -f 1)
	TYPE=$(echo $TYPE | tr 'a-z' 'A-Z')
	IDENTIFIANT=$(echo $path | sed 's/\.[a-zA-Z0-9]*$//g' | cut -d '-' -f 3)
	ANNEE=$(echo $path | cut -d '-' -f 2)

	php symfony import:fichier $SYMFONYTASKOPTIONS $IDENTIFIANT $path --libelle="$TYPE $ANNEE issue de Prodouane" --papier="1" --type="$TYPE" --annee="$ANNEE" --lien_symbolique=true --trace

done
