#!/bin/bash

cd $(dirname $0)/.. > /dev/null 2>&1

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test "$2" ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')/generate_export_dr_from_douane.sh $1 $app;
    done
    exit 0;
fi

if ! test "$2" ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
    confname='config.inc'
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$2".inc
    confname="config."$2".inc"
fi

if ! test "$SYMFONYTASKOPTIONS" ; then
    exit;
fi

cd ../../prodouane_scrapy/

if test "$1"; then
  ANNEE=$1 ;
else
  ANNEE=$(date '+%Y') ;
fi

PRODOUANE_CONFIG_FILENAME="$confname" bash bin/download_all.sh $ANNEE dr
PRODOUANE_CONFIG_FILENAME="$confname" bash bin/download_all.sh $ANNEE sv11
PRODOUANE_CONFIG_FILENAME="$confname" bash bin/download_all.sh $ANNEE sv12

cd -

echo "type;année;id interne;cvi;raison sociale;;commune;tiers;tiers id;categorie;genre;denomination;mention;lieu;couleur;cepage;inao;libelle;denomination complementaire;ligne numero;ligne libelle;ligne valeur;acheteur id;acheteur raison sociale;;" > $EXPORTDIR"/dr_"$ANNEE".csv.tmp"
find ../../prodouane_scrapy/documents/ -name '[ds][rv]*-'$ANNEE'*.xls' | while read xls ; do
  php symfony $SYMFONYTASKOPTIONS douaneRecolte:convert2csv  $xls
done >> $EXPORTDIR"/dr_"$ANNEE".csv.tmp"
find ../../prodouane_scrapy/documents/ -name 'prod*-'$ANNEE'*.csv' | while read csv ; do
  php symfony $SYMFONYTASKOPTIONS douaneRecolte:convert2csv  $csv
done >> $EXPORTDIR"/dr_"$ANNEE".csv.tmp"

head -n 1 $EXPORTDIR"/dr_"$ANNEE".csv.tmp" > $EXPORTDIR"/dr_"$(echo $SYMFONYTASKOPTIONS | sed 's/.*--application=//' | sed 's/ .*//')"_"$ANNEE".csv.tmp"
grep "$HASHPRODUIT" $EXPORTDIR"/dr_"$ANNEE".csv.tmp" | awk -F ';' '{print $4}' | uniq | while read cvi ; do
	grep "$cvi" $EXPORTDIR"/dr_"$ANNEE".csv.tmp"
done >> $EXPORTDIR"/dr_"$(echo $SYMFONYTASKOPTIONS | sed 's/.*--application=//' | sed 's/ .*//')"_"$ANNEE".csv.tmp"
mv $EXPORTDIR"/dr_"$(echo $SYMFONYTASKOPTIONS | sed 's/.*--application=//' | sed 's/ .*//')"_"$ANNEE".csv.tmp" $EXPORTDIR"/dr_"$(echo $SYMFONYTASKOPTIONS | sed 's/.*--application=//' | sed 's/ .*//')"_"$ANNEE".csv"
rm $EXPORTDIR"/dr_"$ANNEE".csv.tmp"
