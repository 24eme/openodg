#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')export.sh $app;
    done
    PROJECTDIR=$(echo $0 | sed 's/[^\/]*$//')..;
    METABASE_SQLITE=$PROJECTDIR/../../metabaseigp/db/igp.sqlite
    EXPORTDIR=$PROJECTDIR/web/exports_igp
    rm -f $EXPORTDIR/*.csv
    bash $PROJECTDIR/bin/export_globalisefichiers.sh;
    bash $PROJECTDIR/bin/export_distribueparproduits.sh;
    mkdir $EXPORTDIR/stats
    ls igp_*.py | while read script; do python3 $script igp;done
    python3 bin/csv2sql.py $METABASE_SQLITE".tmp" $EXPORTDIR
    mv $METABASE_SQLITE".tmp" $METABASE_SQLITE

    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        . $(echo $0 | sed 's/[^\/]*$//')config_"$app".inc
        if test -d $EXPORTDIR"/GLOBAL" ; then
            python3 bin/csv2sql.py $METABASE_SQLITE".global.tmp" $EXPORTDIR"/GLOBAL"
            mv $METABASE_SQLITE".global.tmp" $METABASE_SQLITE".global"
        fi
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

mkdir $EXPORTDIR 2> /dev/null

APPLICATION=$(echo -n $SYMFONYTASKOPTIONS | sed -r 's/.*--application=([^ ]+).*/\1/')

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

php symfony declarations:lots-export-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/declarations_cepages_lots.csv.part

cat $EXPORTDIR/declarations_cepages_lots.csv.part | sed 's/ALICANTE.HENRI/ALICANTE HENRI/g' | sed 's/CABERNET FRANC N/CABERNET FRANC N/g' | sed 's/CABERNET.FRANC.N/CABERNET FRANC N/g' | sed 's/CABERNET SAUVIGNON N/CABERNET SAUVIGNON N/g' | sed 's/CABERNET-SAUVIGNON N/CABERNET SAUVIGNON N/g' | sed 's/CALADOC N/CALADOC N/g' | sed 's/CALADOC.N/CALADOC N/g' | sed 's/CARIGNAN N/CARIGNAN N/g' | sed 's/CARIGNAN.N/CARIGNAN N/g' | sed 's/CHARDONNAY"/CHARDONNAY B"/g' | sed 's/CHARDONNAY B/CHARDONNAY B/g' | sed 's/CHARDONN.B/CHARDONNAY B/g' | sed 's/CHASAN B/CHASAN B/g' | sed 's/CHASAN.B/CHASAN B/g' | sed 's/CINSAULT N/CINSAULT N/g' | sed 's/CINSAUT N/CINSAULT N/g' | sed 's/CLAIRET.B/CLAIRETTE B/g' | sed 's/CLAIRETTE B/CLAIRETTE B/g' | sed 's/COLOMBARD.B/COLOMBARD B/g' | sed 's/COT.N/COT N/g' | sed 's/COUNOISE N/COUNOISE N/g' | sed 's/COUNOISE.N/COUNOISE N/g' | sed 's/GRENACHE"/GRENACHE N"/g' | sed 's/GRENACHE.BLANC.B/GRENACHE BLANC B/g' | sed 's/Grenache N/GRENACHE N/g' | sed 's/GRENACHE.N/GRENACHE N/g' | sed 's/MARSANNE.B/MARSANNE B/g' | sed 's/MARSELAN"/MARSELAN N"/g' | sed 's/MARSELAN N/MARSELAN N/g' | sed 's/MARSELAN.N/MARSELAN N/g' | sed 's/MERLOT N/MERLOT N/g' | sed 's/MERLOT.N/MERLOT N/g' | sed 's/MOURVED.N/MOURVED N/g' | sed 's/MOURVÈDRE N/MOURVEDRE N/g' | sed 's/MUSCAT À PETITS GRAINS B/MUSCAT À PETITS GRAINS B/g' | sed 's/MUSCAT.à.PETITS.GRAINS.RG/MUSCAT À PETITS GRAINS B/g' | sed 's/MUSCAT À PETITS GRAINS RS/MUSCAT À PETITS GRAINS B/g' | sed 's/MUS.HAMB.N/MUS HAMB N/g' | sed 's/MUS.P.G.RS/MUS P G RS/g' | sed 's/MUS.PT.G.B/MUS PT G B/g' | sed 's/PINOT NOIR N/PINOT NOIR N/g' | sed 's/PINOT.NOIR.N/PINOT NOIR N/g' | sed 's/PIQUEPOUL.BLANC.B/PIQUEPOUL BLANC B/g' | sed 's/ROUSSANNE B/ROUSSANNE B/g' | sed 's/ROUSSANNE.B/ROUSSANNE B/g' | sed 's/SAUVIGN.B/SAUVIGNON B/g' | sed 's/SAUVIGNON B/SAUVIGNON B/g' | sed 's/SAUVIGNON B,VIOGNIER B/SAUVIGNON B/g' | sed 's/SAVAGN.B/SAUVIGNON B/g' | sed 's/SYRAH.N/SYRAH N/g' | sed 's/TEMPRANILLO.N/TEMPRANILLO N/g' | sed 's/UGNI BLANC B/UGNI BLANC B/g' | sed 's/UGNI.BLANC.B/UGNI BLANC B/g' | sed 's/VERMENT.B/VERMENTINO B/g' | sed 's/VERMENTINO"/VERMENTINO B"/g' | sed 's/VERMENTINO B/VERMENTINO B/g' | sed 's/VIOGNIER"/VIOGNIER B"/g' | sed 's/VIOGNIER B/VIOGNIER B/g' | sed 's/VIOGNIER.B/VIOGNIER B/g' > $EXPORTDIR/declarations_lots.csv.part

head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/drev_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/conditionnement_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/transaction_lots.csv.part

if [ -z $IS_NO_VINIF ]; then
  grep "^DRev" $EXPORTDIR/declarations_lots.csv.part >> $EXPORTDIR/drev_lots.csv.part
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev_lots.csv.part > $EXPORTDIR/drev_lots.csv
  rm $EXPORTDIR/drev_lots.csv.part
fi

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

if [ -z $IS_NO_VINIF ]; then
  bash bin/export_docs.sh DR 30 $1 > $EXPORTDIR/dr.csv.part
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/dr.csv.part > $EXPORTDIR/dr.csv
  rm $EXPORTDIR/dr.csv.part

  bash bin/export_docs.sh SV12 30 $1 > $EXPORTDIR/sv12.csv.part
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv12.csv.part > $EXPORTDIR/sv12.csv
  rm $EXPORTDIR/sv12.csv.part

  bash bin/export_docs.sh SV11 30 $1 > $EXPORTDIR/sv11.csv.part
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv11.csv.part > $EXPORTDIR/sv11.csv
  rm $EXPORTDIR/sv11.csv.part
fi

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

php symfony export:facture-paiements $SYMFONYTASKOPTIONS >  $EXPORTDIR/paiements.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/paiements.csv.part > $EXPORTDIR/paiements.csv
rm $EXPORTDIR/paiements.csv.part

php symfony lots:export-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/lots.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/lots.csv.part > $EXPORTDIR/lots.csv

php symfony lots:export-historique-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/lots-historique.csv.part

# Ajouter la hash produit à la fin du fichier lots-historique
cat $EXPORTDIR/lots.csv.part | cut -d ";" -f 34,35,36 | sort -t ";" -k 1,1 > $EXPORTDIR/lots_hash.csv
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

php symfony export:csv-configuration $SYMFONYTASKOPTIONS > $EXPORTDIR/produits.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/produits.csv.part > $EXPORTDIR/produits.csv
rm $EXPORTDIR/produits.csv.part

mkdir -p $EXPORTDIR/stats

cd bin/notebook/

if [[ $APPLICATION == igp* ]];then
  ls igp_*.py | while read script; do python3 $script $APPLICATION;done
else
  ls "$APPLICATION"_*.py | while read script; do python3 $script;done
fi

cd -

find $EXPORTDIR -type f -empty -delete

if test "$METABASE_SQLITE"; then
    python3 bin/csv2sql.py $METABASE_SQLITE".tmp" $EXPORTDIR
    mv $METABASE_SQLITE".tmp" $METABASE_SQLITE
fi
