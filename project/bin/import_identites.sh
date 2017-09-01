#!/bin/bash

. bin/config.inc

echo "Import des identités"

if ! test "$1"; then
    echo "Paramêtre de récupération des données obligatoire";
    exit 1;
fi

SYMFODIR=$(pwd);

LOGDATE=$SYMFODIR/$(date +%Y%m%d%H%M%S_import_data.log)


scp $1 $TMPDIR/ODGRHONE_IDENTITES_DATA.xml.gz

echo "Dézippage";
rm -rf $TMPDIR/ODGRHONE_IDENTITES_DATA 2>/dev/null
mkdir $TMPDIR/ODGRHONE_IDENTITES_DATA 2> /dev/null
mkdir $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA 2> /dev/null
gunzip $TMPDIR/ODGRHONE_IDENTITES_DATA.xml.gz
mv /tmp/ODGRHONE_IDENTITES_DATA.xml $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.xml




cat $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.xml | sed -e 's|<b:Identite_Identite>|\n<b:Identite_Identite>|g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA_N.xml

## ici retirer le head
## cat $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA_N.xml | grep -E '^<b:Identite_Identite>' | sed -r 's/(.*)<b:CleIdentite>([0-9]+)<\/b:CleIdentite>(.*)/\2###\1<b:CleIdentite>\2<\/b:CleIdentite>\3/g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.tmp.xml

head -n 200 $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA_N.xml | grep -E '^<b:Identite_Identite>' | sed -r 's/(.*)<b:CleIdentite>([0-9]+)<\/b:CleIdentite>(.*)/\2###\1<b:CleIdentite>\2<\/b:CleIdentite>\3/g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.tmp.xml

echo "Création des xml entités";
###
### DANS CETTE BOUCLE IL FAUDRA utiliser un identifiant unique ou répértorier les id en doublon!!!
###
while read xml
do
  IDFIC=$(echo $xml | sed -r 's/([0-9]+)###(.*)/\1/g')
  echo $xml | sed -r 's/([0-9]+)###(.*)/\2/g' > $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/$IDFIC.xml
done < $TMPDIR/ODGRHONE_IDENTITES_DATA/ODGRHONE_IDENTITES_DATA.tmp.xml

echo "Création des entités";
for path in $TMPDIR/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/*.xml ; do
  php symfony import:entite-from-xml $path
done
#nom num coptable et cvi + tag
