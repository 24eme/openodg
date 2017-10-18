#!/bin/bash

. bin/config.inc

echo "Import des identités"

if ! test "$1"; then
    echo "Paramêtre de récupération des données obligatoire";
    exit 1;
fi

SYMFODIR=$(pwd);

LOGDATE=$SYMFODIR/$(date +%Y%m%d%H%M%S_import_data.log)

if [ ! -d "$TMPDIR/ODGRHONE_IDENTITES_DATA" ]; then

scp $1 $TMPDIR/ODGRHONE_IDENTITES_DATA.xml.gz

echo "Dézippage";
rm -rf $TMPDIR/ODGRHONE_IDENTITES_DATA 2>/dev/null
mkdir $TMPDIR/ODGRHONE_IDENTITES_DATA 2> /dev/null
mkdir $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA 2> /dev/null
gunzip $TMPDIR/ODGRHONE_IDENTITES_DATA.xml.gz
mv $TMPDIR/ODGRHONE_IDENTITES_DATA.xml $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.xml

fi

 cat $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.xml | sed -e 's|<b:Identite_Identite>|\n<b:Identite_Identite>|g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA_N.xml

## ici retirer le head

# cat $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA_N.xml | grep -E '^<b:Identite_Identite>' | sed -r 's/(.*)<b:CleIdentite>([0-9]+)<\/b:CleIdentite>(.*)/\2###\1<b:CleIdentite>\2<\/b:CleIdentite>\3/g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.tmp.xml

cat $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA_N.xml | grep -E '^<b:Identite_Identite>' | sed -r 's/(.*)<b:CleIdentite>([0-9]+)<\/b:CleIdentite>(.*)/\2###\1<b:CleIdentite>\2<\/b:CleIdentite>\3/g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.tmp.xml

echo "Création des xml entités";
###
### DANS CETTE BOUCLE ON CHERCHE LES DEFINITIONS DE GROUPES
###
cat $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.tmp.xml | grep -E "(<b:Siret>.+</b:Siret>|<b:Evv>.+</b:Evv>)" | while read xml
do
  IDFIC=$(echo $xml | sed -r 's/([0-9]+)###(.*)/\1/g')
  echo $xml | sed -r 's/([0-9]+)###(.*)/\2/g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/evvSiret_$IDFIC.xml
done


cat $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.tmp.xml  | grep -v "<b:Siret>" | grep -v "<b:Evv>" | while read xml
do
  IDFIC=$(echo $xml | sed -r 's/([0-9]+)###(.*)/\1/g')
  echo $xml | sed -r 's/([0-9]+)###(.*)/\2/g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/autres_$IDFIC.xml
done

echo "Création des entités de type Sociétés (Présence d'un Evv ou d'un Siret)"
for path in $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/evvSiret_*.xml ; do
  php symfony import:entite-from-xml --trace $path
done

echo "Autres entités étant des sociétés autre ou des INTERLOCUTEURS";
for path in $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/autres_*.xml ; do
  php symfony import:entite-from-xml --trace $path
done
