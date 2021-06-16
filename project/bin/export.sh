#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')export.sh $app;
    done
    rm -f web/exports_igp/*.csv
    bash $(echo $0 | sed 's/[^\/]*$//')export_globalisefichiers.sh;
    bash $(echo $0 | sed 's/[^\/]*$//')export_distribueparproduits.sh;
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

mkdir $EXPORTDIR 2> /dev/null

php symfony export:etablissements-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/etablissements.csv.part
cat $EXPORTDIR/etablissements.csv.part | sort | grep -E "^(Login)" > $EXPORTDIR/etablissements.csv.sorted.part
cat $EXPORTDIR/etablissements.csv.part | sort | grep -Ev "^(Login)" >> $EXPORTDIR/etablissements.csv.sorted.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/etablissements.csv.sorted.part > $EXPORTDIR/etablissements.en.csv
cat $EXPORTDIR/etablissements.en.csv | sed 's/;/ø/g' | awk -F ',' 'BEGIN { OFS=";" }{ $1=$1; print $0 }' | sed 's/ø/,/g' > $EXPORTDIR/etablissements.csv
rm $EXPORTDIR/etablissements.csv.part $EXPORTDIR/etablissements.csv.sorted.part
ln -s etablissements.en.csv $EXPORTDIR/etablissements.iso8859.csv 2> /dev/null # Pour l'AVPI en provence

sleep 60

php symfony export:chais-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/chais.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/chais.csv.part > $EXPORTDIR/chais.en.csv
cat $EXPORTDIR/chais.en.csv | sed 's/;/ø/g' | awk -F ',' 'BEGIN { OFS=";" }{ $1=$1; print $0 }' | sed 's/ø/,/g' > $EXPORTDIR/chais.csv
rm $EXPORTDIR/chais.csv.part
ln -s chais.en.csv $EXPORTDIR/chais.iso8859.csv 2> /dev/null # Pour l'AVPI en provence

sleep 60

php symfony export:societe $SYMFONYTASKOPTIONS > $EXPORTDIR/societe.csv.part
head -n 1 $EXPORTDIR/societe.csv.part > $EXPORTDIR/societe.csv.part.head
tail -n +2 $EXPORTDIR/societe.csv.part | sort > $EXPORTDIR/societe.csv.part.body
cat  $EXPORTDIR/societe.csv.part.head $EXPORTDIR/societe.csv.part.body > $EXPORTDIR/societe.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/societe.csv.part > $EXPORTDIR/societe.iso.csv
rm $EXPORTDIR/societe.csv.part $EXPORTDIR/societe.csv.part.head $EXPORTDIR/societe.csv.part.body
mv -f $EXPORTDIR/societe.iso.csv $EXPORTDIR/societe.csv

sleep 60

bash bin/export_docs.sh DRev 30 $1 > $EXPORTDIR/drev.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev.csv.part > $EXPORTDIR/drev.csv
rm $EXPORTDIR/drev.csv.part

bash bin/export_docs.sh ChgtDenom 30 $1 > $EXPORTDIR/changement_denomination.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/changement_denomination.csv.part > $EXPORTDIR/changement_denomination.csv
rm $EXPORTDIR/changement_denomination.csv.part

sleep 60

php symfony declarations:lots-export-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/declarations_lots.csv.part

head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/drev_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/conditionnement_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/transaction_lots.csv.part

grep "^DRev" $EXPORTDIR/declarations_lots.csv.part >> $EXPORTDIR/drev_lots.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev_lots.csv.part > $EXPORTDIR/drev_lots.csv
rm $EXPORTDIR/drev_lots.csv.part

grep "^Conditionnement" $EXPORTDIR/declarations_lots.csv.part >> $EXPORTDIR/conditionnement_lots.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/conditionnement_lots.csv.part > $EXPORTDIR/conditionnement_lots.csv
rm $EXPORTDIR/conditionnement_lots.csv.part

grep "^Transaction" $EXPORTDIR/declarations_lots.csv.part >> $EXPORTDIR/transaction_lots.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/transaction_lots.csv.part > $EXPORTDIR/transaction_lots.csv
rm $EXPORTDIR/transaction_lots.csv.part

rm $EXPORTDIR/declarations_lots.csv.part

bash bin/export_docs.sh Habilitation 30 $1 > $EXPORTDIR/habilitation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation.csv.part > $EXPORTDIR/habilitation.csv
rm $EXPORTDIR/habilitation.csv.part

php symfony export:habilitation-demandes $SYMFONYTASKOPTIONS > $EXPORTDIR/habilitation_demandes.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes.csv.part > $EXPORTDIR/habilitation_demandes.csv
php bin/export/export_liste_inao.php $EXPORTDIR/habilitation_demandes.csv.part | grep -E "^(Côtes du Rhône|Libelle Appellation)" > $EXPORTDIR/habilitation_demandes_inao.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes_inao.csv.part > $EXPORTDIR/habilitation_demandes_inao.csv
rm $EXPORTDIR/habilitation_demandes.csv.part $EXPORTDIR/habilitation_demandes_inao.csv.part

sleep 60

bash bin/export_docs.sh DR 30 $1 > $EXPORTDIR/dr.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/dr.csv.part > $EXPORTDIR/dr.csv
rm $EXPORTDIR/dr.csv.part

bash bin/export_docs.sh SV12 30 $1 > $EXPORTDIR/sv12.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv12.csv.part > $EXPORTDIR/sv12.csv
rm $EXPORTDIR/sv12.csv.part

bash bin/export_docs.sh SV11 30 $1 > $EXPORTDIR/sv11.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv11.csv.part > $EXPORTDIR/sv11.csv
rm $EXPORTDIR/sv11.csv.part

bash bin/export_docs.sh ParcellaireIrrigable 30 $1 > $EXPORTDIR/parcellaireirrigable.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireirrigable.csv.part > $EXPORTDIR/parcellaireirrigable.csv
rm $EXPORTDIR/parcellaireirrigable.csv.part

bash bin/export_docs.sh ParcellaireIrrigue 30 $1 > $EXPORTDIR/parcellaireirrigue.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireirrigue.csv.part > $EXPORTDIR/parcellaireirrigue.csv
rm $EXPORTDIR/parcellaireirrigue.csv.part

bash bin/export_docs.sh ParcellaireIntentionAffectation 30 $1 > $EXPORTDIR/parcellaireintentionaffectation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireintentionaffectation.csv.part > $EXPORTDIR/parcellaireintentionaffectation.csv
rm $EXPORTDIR/parcellaireintentionaffectation.csv.part

bash bin/export_docs.sh ParcellaireAffectation 30 $1 > $EXPORTDIR/parcellaireaffectation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireaffectation.csv.part > $EXPORTDIR/parcellaireaffectation.csv
rm $EXPORTDIR/parcellaireaffectation.csv.part

#sleep 60

php symfony pieces:export-csv $SYMFONYTASKOPTIONS >  $EXPORTDIR/pieces.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/pieces.csv.part > $EXPORTDIR/pieces.csv
rm $EXPORTDIR/pieces.csv.part

#sleep 60

php symfony liaisons:export-csv $SYMFONYTASKOPTIONS >  $EXPORTDIR/liaisons.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/liaisons.csv.part > $EXPORTDIR/liaisons.csv
rm $EXPORTDIR/liaisons.csv.part

#sleep 60

php symfony compte:export-all-csv $SYMFONYTASKOPTIONS >  $EXPORTDIR/comptes.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/comptes.csv.part > $EXPORTDIR/comptes.csv
rm $EXPORTDIR/comptes.csv.part


php symfony export:facture $SYMFONYTASKOPTIONS >  $EXPORTDIR/factures.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/factures.csv.part > $EXPORTDIR/factures.csv
rm $EXPORTDIR/factures.csv.part

php symfony lots:export-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/lots.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/lots.csv.part > $EXPORTDIR/lots.csv

php symfony lots:export-historique-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/lots-historique.csv.part

# Ajouter la hash produit à la fin du fichier lots-historique
cat $EXPORTDIR/lots.csv.part | cut -d ";" -f 33,34 | sort -t ";" -k 1,1 > $EXPORTDIR/lots_hash.csv
tail -n +2 $EXPORTDIR/lots-historique.csv.part | sort -t ";" -k 15,15 > $EXPORTDIR/lots-historique.csv.sorted
head -n 1 $EXPORTDIR/lots-historique.csv.part | sed 's/$/;Hash produit/' > $EXPORTDIR/lots-historique.csv.sorted.join
join -t ";" -a 1 -1 15 -2 1 $EXPORTDIR/lots-historique.csv.sorted $EXPORTDIR/lots_hash.csv | awk -F ';' 'BEGIN{ OFS=";" }{ unique_id=$1; hash_produit=$16; $16=unique_id; $17=hash_produit; $1=""; print $0 }' | sed 's/^;//' >> $EXPORTDIR/lots-historique.csv.sorted.join

iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/lots-historique.csv.sorted.join > $EXPORTDIR/lots-historique.csv

rm $EXPORTDIR/lots-historique.csv.part
rm $EXPORTDIR/lots-historique.csv.sorted
rm $EXPORTDIR/lots-historique.csv.sorted.join
rm $EXPORTDIR/lots_hash.csv
rm $EXPORTDIR/lots.csv.part

bash bin/export_docs.sh Degustation 30 $1 > $EXPORTDIR/degustations.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/degustations.csv.part > $EXPORTDIR/degustations.csv
rm $EXPORTDIR/degustations.csv.part

php symfony degustations:export-degustateurs-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/degustateurs.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/degustateurs.csv.part > $EXPORTDIR/degustateurs.csv
rm $EXPORTDIR/degustateurs.csv.part

find $EXPORTDIR -type f -empty -delete

if test "$METABASE_SQLITE"; then
    python3 bin/csv2sql.py $METABASE_SQLITE".tmp" $EXPORTDIR
    mv $METABASE_SQLITE".tmp" $METABASE_SQLITE
fi
