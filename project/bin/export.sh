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

if ! test $EXPORTSLEEP ; then
EXPORTSLEEP=30
fi

php symfony export:etablissements-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/etablissements.csv.part
cat $EXPORTDIR/etablissements.csv.part | sort | grep -E "^(Login)" > $EXPORTDIR/etablissements.csv.sorted.part
cat $EXPORTDIR/etablissements.csv.part | sort | grep -Ev "^(Login)" >> $EXPORTDIR/etablissements.csv.sorted.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/etablissements.csv.sorted.part > $EXPORTDIR/etablissements.en.csv
cat $EXPORTDIR/etablissements.en.csv | sed 's/;/ø/g' | awk -F ',' 'BEGIN { OFS=";" }{ $1=$1; print $0 }' | sed 's/ø/,/g' > $EXPORTDIR/etablissements.csv
rm $EXPORTDIR/etablissements.csv.part $EXPORTDIR/etablissements.csv.sorted.part
ln -s etablissements.en.csv $EXPORTDIR/etablissements.iso8859.csv 2> /dev/null # Pour l'AVPI en provence

sleep $EXPORTSLEEP

php symfony export:chais-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/chais.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/chais.csv.part > $EXPORTDIR/chais.en.csv
cat $EXPORTDIR/chais.en.csv | sed 's/;/ø/g' | awk -F ',' 'BEGIN { OFS=";" }{ $1=$1; print $0 }' | sed 's/ø/,/g' > $EXPORTDIR/chais.csv
rm $EXPORTDIR/chais.csv.part
ln -s chais.en.csv $EXPORTDIR/chais.iso8859.csv 2> /dev/null # Pour l'AVPI en provence

sleep $EXPORTSLEEP

php symfony export:societe $SYMFONYTASKOPTIONS > $EXPORTDIR/societe.csv.part
head -n 1 $EXPORTDIR/societe.csv.part > $EXPORTDIR/societe.csv.part.head
tail -n +2 $EXPORTDIR/societe.csv.part | sort > $EXPORTDIR/societe.csv.part.body
cat  $EXPORTDIR/societe.csv.part.head $EXPORTDIR/societe.csv.part.body > $EXPORTDIR/societe.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/societe.csv.part > $EXPORTDIR/societe.iso.csv
rm $EXPORTDIR/societe.csv.part $EXPORTDIR/societe.csv.part.head $EXPORTDIR/societe.csv.part.body
mv -f $EXPORTDIR/societe.iso.csv $EXPORTDIR/societe.csv

sleep $EXPORTSLEEP

bash bin/export_docs.sh DRev $EXPORTSLEEP $1 > $EXPORTDIR/drev.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev.csv.part > $EXPORTDIR/drev.csv
rm $EXPORTDIR/drev.csv.part

bash bin/export_docs.sh ChgtDenom $EXPORTSLEEP $1 > $EXPORTDIR/changement_denomination.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/changement_denomination.csv.part > $EXPORTDIR/changement_denomination.csv
rm $EXPORTDIR/changement_denomination.csv.part

sleep $EXPORTSLEEP

php symfony declarations:lots-export-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/declarations_cepages_lots.csv.part

cat $EXPORTDIR/declarations_cepages_lots.csv.part | awk -F ';' 'BEGIN{OFS=";"}  {gsub("\\.", " ", $24); print $0; }' | sed 's/ / /g' |  sed 's/CABERNET-SAUVIGNON/CABERNET SAUVIGNON/g' | sed 's/CAB-SAUV-N/CABERNET SAUVIGNON N/' | sed 's/CALADOC"/CALADOC N"/' | sed 's/CAMENèRE N/CAMENÈRE N/' | sed 's/CHARDONAY B/CHARDONNAY B/' | sed 's/CHARDONNAY"/CHARDONNAY B"/g' | sed 's/CHASAN"/CHASAN B"/' | sed 's/GRENACHE"/GRENACHE N"/g' | sed 's/Grenache N/GRENACHE N/g' | sed 's/MARSELAN"/MARSELAN N"/g' | sed 's/MERLOT"/MERLOT N"/' | sed 's/MOURVED N/MOURVEDRE N/' | sed 's/MOURVÈDRE N/MOURVEDRE N/g' | sed 's/MUSCAT A B/MUSCAT A PETITS GRAINS B/' | sed 's/MUSCAT À PETITS GRAINS/MUSCAT A PETITS GRAINS/g' | sed 's/MUSCAT D.HAMBOURG N/MUSCAT DE HAMBOURG N/' | sed 's/MUSCAT H N/MUSCAT DE HAMBOURG N/' | sed 's/MUS HAMB N/MUSCAT DE HAMBOURG N/' | sed 's/MUS P G /MUSCAT A PETITS GRAINS /' | sed 's/MUS PT G /MUSCAT A PETITS GRAINS /' | sed 's/Syrah/SYRAH N/' | sed 's/SYRAH"/SYRAH N"/' | sed 's/VERMENTINO"/VERMENTINO B"/g' | sed 's/VIOGNIER"/VIOGNIER B"/' | sed 's/VIOGNIER"/VIOGNIER B"/g' > $EXPORTDIR/declarations_lots.csv.part

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

bash bin/export_docs.sh Habilitation $EXPORTSLEEP $1 > $EXPORTDIR/habilitation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation.csv.part > $EXPORTDIR/habilitation.csv
rm $EXPORTDIR/habilitation.csv.part

php symfony export:habilitation-demandes $SYMFONYTASKOPTIONS > $EXPORTDIR/habilitation_demandes.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes.csv.part > $EXPORTDIR/habilitation_demandes.csv
php bin/export/export_liste_inao.php $EXPORTDIR/habilitation_demandes.csv.part | grep -E "^(Côtes du Rhône|Libelle Appellation)" > $EXPORTDIR/habilitation_demandes_inao.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes_inao.csv.part > $EXPORTDIR/habilitation_demandes_inao.csv
rm $EXPORTDIR/habilitation_demandes.csv.part $EXPORTDIR/habilitation_demandes_inao.csv.part

echo $EXPORT_SUB_HABILITATION | tr '|' '\n' | grep '/' | while read subhab; do
    eval 'SUBDIR=$EXPORT_SUB_HABILITATION_'$subhab'_DIR'
    eval 'SUBFILTRE=$EXPORT_SUB_HABILITATION_'$subhab'_FILTRE'
    mkdir -p $SUBDIR
    head -n 1 $EXPORTDIR/habilitation.csv > $SUBDIR/habilitation.csv
    cat $EXPORTDIR/habilitation.csv | grep "$SUBFILTRE" >> $SUBDIR/habilitation.csv
    head -n 1 $EXPORTDIR/drev.csv > $SUBDIR/drev.csv
    cat $EXPORTDIR/drev.csv | grep "$SUBFILTRE" >> $SUBDIR/drev.csv
done

sleep $EXPORTSLEEP

if [ -z $IS_NO_VINIF ]; then
  bash bin/export_docs.sh DR $EXPORTSLEEP $1 > $EXPORTDIR/production.csv.part
  bash bin/export_docs.sh SV11 $EXPORTSLEEP $1 >> $EXPORTDIR/production.csv.part
  bash bin/export_docs.sh SV12 $EXPORTSLEEP $1 >> $EXPORTDIR/production.csv.part
  head -n 1 $EXPORTDIR/production.csv.part | iconv -f UTF8 -t ISO88591//TRANSLIT > $EXPORTDIR/production.csv
  cat $EXPORTDIR/production.csv.part | grep -E '^(DR|SV)' | awk -F ';' '{uniq = $1"-"$2"-"$4 ; if ( ! unicite[uniq] || unicite[uniq] == $3 ) { print $0  ; unicite[uniq] = $3 } }' | iconv -f UTF8 -t ISO88591//TRANSLIT >> $EXPORTDIR/production.csv

  head -n 1  $EXPORTDIR/production.csv > $EXPORTDIR/dr.csv
  cat $EXPORTDIR/production.csv | grep -a '^DR' >> $EXPORTDIR/dr.csv
  head -n 1  $EXPORTDIR/production.csv > $EXPORTDIR/sv11.csv
  cat $EXPORTDIR/production.csv | grep -a '^SV11' >> $EXPORTDIR/sv11.csv
  head -n 1  $EXPORTDIR/production.csv > $EXPORTDIR/sv12.csv
  cat $EXPORTDIR/production.csv | grep -a '^SV12' >> $EXPORTDIR/sv12.csv

  rm $EXPORTDIR/production.csv.part
fi

bash bin/export_docs.sh ParcellaireIrrigable $EXPORTSLEEP $1 > $EXPORTDIR/parcellaireirrigable.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireirrigable.csv.part > $EXPORTDIR/parcellaireirrigable.csv
rm $EXPORTDIR/parcellaireirrigable.csv.part

bash bin/export_docs.sh ParcellaireIrrigue $EXPORTSLEEP $1 > $EXPORTDIR/parcellaireirrigue.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireirrigue.csv.part > $EXPORTDIR/parcellaireirrigue.csv
rm $EXPORTDIR/parcellaireirrigue.csv.part

bash bin/export_docs.sh ParcellaireIntentionAffectation $EXPORTSLEEP $1 > $EXPORTDIR/parcellaireintentionaffectation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireintentionaffectation.csv.part > $EXPORTDIR/parcellaireintentionaffectation.csv
rm $EXPORTDIR/parcellaireintentionaffectation.csv.part

bash bin/export_docs.sh ParcellaireAffectation $EXPORTSLEEP $1 > $EXPORTDIR/parcellaireaffectation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaireaffectation.csv.part > $EXPORTDIR/parcellaireaffectation.csv
rm $EXPORTDIR/parcellaireaffectation.csv.part

#sleep $EXPORTSLEEP

php symfony pieces:export-csv $SYMFONYTASKOPTIONS >  $EXPORTDIR/pieces.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/pieces.csv.part > $EXPORTDIR/pieces.csv
rm $EXPORTDIR/pieces.csv.part

#sleep $EXPORTSLEEP

php symfony liaisons:export-csv $SYMFONYTASKOPTIONS >  $EXPORTDIR/liaisons.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/liaisons.csv.part > $EXPORTDIR/liaisons.csv
rm $EXPORTDIR/liaisons.csv.part

#sleep $EXPORTSLEEP

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
cat $EXPORTDIR/lots.csv.part | awk -F ';' 'BEGIN{OFS=";"}  {gsub("\\.", " ", $20); print $0; }' | sed 's/ / /g' |  sed 's/CABERNET-SAUVIGNON/CABERNET SAUVIGNON/g' | sed 's/CAB-SAUV-N/CABERNET SAUVIGNON N/' | sed 's/CALADOC"/CALADOC N"/' | sed 's/CAMENèRE N/CAMENÈRE N/' | sed 's/CHARDONAY B/CHARDONNAY B/' | sed 's/CHARDONNAY"/CHARDONNAY B"/g' | sed 's/CHASAN"/CHASAN B"/' | sed 's/GRENACHE"/GRENACHE N"/g' | sed 's/Grenache N/GRENACHE N/g' | sed 's/MARSELAN"/MARSELAN N"/g' | sed 's/MERLOT"/MERLOT N"/' | sed 's/MOURVED N/MOURVEDRE N/' | sed 's/MOURVÈDRE N/MOURVEDRE N/g' | sed 's/MUSCAT A B/MUSCAT A PETITS GRAINS B/' | sed 's/MUSCAT À PETITS GRAINS/MUSCAT A PETITS GRAINS/g' | sed 's/MUSCAT D.HAMBOURG N/MUSCAT DE HAMBOURG N/' | sed 's/MUSCAT H N/MUSCAT DE HAMBOURG N/' | sed 's/MUS HAMB N/MUSCAT DE HAMBOURG N/' | sed 's/MUS P G /MUSCAT A PETITS GRAINS /' | sed 's/MUS PT G /MUSCAT A PETITS GRAINS /' | sed 's/Syrah/SYRAH N/' | sed 's/SYRAH"/SYRAH N"/' | sed 's/VERMENTINO"/VERMENTINO B"/g' | sed 's/VIOGNIER"/VIOGNIER B"/' | sed 's/VIOGNIER"/VIOGNIER B"/g' > $EXPORTDIR/lots_cleancepages.csv.part

php symfony lots:export-historique-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/lots-historique.csv.part

# Ajouter la hash produit à la fin du fichier lots-historique
tail -n +2 $EXPORTDIR/lots.csv.part | sort -t ";" -k 37,37 > $EXPORTDIR/lots_hash.csv
tail -n +2 $EXPORTDIR/lots-historique.csv.part | sort -t ";" -k 18,18 > $EXPORTDIR/lots-historique.csv.sorted
echo "Origine;Id Opérateur;Nom Opérateur;Campagne;Date commission;Date lot;Num Dossier;Num Lot;Doc Ordre;Doc Type;Libellé du lot;Volume;Statut;Details;Organisme;Doc Id;Lot unique Id;Lot Origine;Lot Id Opérateur;Lot Nom Opérateur;Lot Adresse Opérateur;Lot Code postal Opérateur;Lot Commune Opérateur;Lot Campagne;Lot Date Commission;Lot Date lot;Lot Num dossier;Lot Num lot;Lot Num logement Opérateur;Lot Certification;Lot Genre;Lot Appellation;Lot Mention;Lot Lieu;Lot Couleur;Lot Cepage;Lot Produit;Lot Cépages;Lot Millésime;Lot Spécificités;Lot Volume;Lot Statut de lot;Lot Destination;Lot Date de destination;Lot Pays de destination;Lot Elevage;Lot Centilisation;Lot Date prélévement;Lot Conformité;Lot Date de conformité en appel;Lot Organisme;Lot Doc Id;Lot Lot unique Id;Hash produit;Lot Passage" > $EXPORTDIR/lots-historique.csv.sorted.join
join -t ";" -a 1 -1 18 -2 37 $EXPORTDIR/lots-historique.csv.sorted $EXPORTDIR/lots_hash.csv | awk -F ';' 'BEGIN{ OFS=";" }{ $1=""; print $0 }' | sed 's/^;//' >> $EXPORTDIR/lots-historique.csv.sorted.join
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/lots-historique.csv.sorted.join > $EXPORTDIR/lots-historique.csv

grep 'Affecté à une dégustation (destination)' $EXPORTDIR/lots-historique.csv.part | sort -t ';' -k 8,8 -r | awk -F ';' '{ uniq = $17 ; if ( ! unicite[uniq] ) { print $17";"$13 ; unicite[uniq] = uniq ; }  }' | sort -t ';' -k 1,1 > $EXPORTDIR/lots-passages.csv
tail -n +2 $EXPORTDIR/lots_cleancepages.csv.part | sort -t ';' -k 37,37 > $EXPORTDIR/lots_cleancepages.csv.part.sorted
echo "Origine;Id Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Campagne;Date commission;Date lot;Num dossier;Num lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume;Statut de lot;Destination;Date de destination;Pays de destination;Elevage;Centilisation;Date prélévement;Conformité;Date de conformité en appel;Organisme;Doc Id;Lot unique Id;Hash produit;Passage" > $EXPORTDIR/lots_cleancepages_passages.csv.part
join -t ';' -a 1 -1 37 -2 1 $EXPORTDIR/lots_cleancepages.csv.part.sorted  $EXPORTDIR/lots-passages.csv | awk -F ';' 'BEGIN{OFS=";"}  {$1=""; print $0}' | sed 's/^;//'  >> $EXPORTDIR/lots_cleancepages_passages.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/lots_cleancepages_passages.csv.part > $EXPORTDIR/lots.csv

rm $EXPORTDIR/lots-historique.csv.part
rm $EXPORTDIR/lots-historique.csv.sorted
rm $EXPORTDIR/lots-historique.csv.sorted.join
rm $EXPORTDIR/lots_hash.csv
rm $EXPORTDIR/lots.csv.part
rm $EXPORTDIR/lots-passages.csv $EXPORTDIR/lots_cleancepages_passages.csv.part $EXPORTDIR/lots_cleancepages.csv.part.sorted $EXPORTDIR/lots_cleancepages.csv.part

bash bin/export_docs.sh Degustation $EXPORTSLEEP $1 > $EXPORTDIR/degustations.csv.part
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
