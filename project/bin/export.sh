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

if ! test "$SYMFONYTASKOPTIONS" ; then
    exit
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

cat $EXPORTDIR/declarations_cepages_lots.csv.part | awk -F ';' 'BEGIN{OFS=";"}  {gsub("\\.", " ", $24); print $0; }' | sed 's/ / /g' |  sed 's/CABERNET-SAUVIGNON/CABERNET SAUVIGNON/g' | sed 's/CAB-SAUV-N/CABERNET SAUVIGNON N/' | sed 's/CALADOC"/CALADOC N"/' | sed 's/CAMENèRE N/CAMENÈRE N/' | sed 's/CHARDONAY B/CHARDONNAY B/' | sed 's/CHARDONNAY"/CHARDONNAY B"/g' | sed 's/CHASAN"/CHASAN B"/' | sed 's/GRENACHE"/GRENACHE N"/g' | sed 's/Grenache N/GRENACHE N/g' | sed 's/MARSELAN"/MARSELAN N"/g' | sed 's/MERLOT"/MERLOT N"/' | sed 's/MOURVED N/MOURVEDRE N/' | sed 's/MOURVÈDRE N/MOURVEDRE N/g' | sed 's/MUSCAT A B/MUSCAT A PETITS GRAINS B/' | sed 's/MUSCAT À PETITS GRAINS/MUSCAT A PETITS GRAINS/g' | sed 's/MUSCAT D.HAMBOURG N/MUSCAT DE HAMBOURG N/' | sed 's/MUSCAT H N/MUSCAT DE HAMBOURG N/' | sed 's/MUS HAMB N/MUSCAT DE HAMBOURG N/' | sed 's/MUS P G /MUSCAT A PETITS GRAINS /' | sed 's/MUS PT G /MUSCAT A PETITS GRAINS /' | sed 's/Syrah/SYRAH N/' | sed 's/SYRAH"/SYRAH N"/' | sed 's/VERMENTINO"/VERMENTINO B"/g' | sed 's/VIOGNIER"/VIOGNIER B"/' | sed 's/VIOGNIER"/VIOGNIER B"/g' | sed 's/cinsau[lt]*/CINSAULT/ig' > $EXPORTDIR/declarations_lots.csv.part

head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/drev_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/conditionnement_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/transaction_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/pmc_lots.csv.part
head -1 $EXPORTDIR/declarations_lots.csv.part > $EXPORTDIR/pmcnc_lots.csv.part

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

grep "^PMCNC" $EXPORTDIR/declarations_lots.csv.part >> $EXPORTDIR/pmcnc_lots.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/pmcnc_lots.csv.part > $EXPORTDIR/pmcnc_lots.csv
rm $EXPORTDIR/pmcnc_lots.csv.part

grep "^PMC;" $EXPORTDIR/declarations_lots.csv.part >> $EXPORTDIR/pmc_lots.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/pmc_lots.csv.part > $EXPORTDIR/pmc_lots.csv
rm $EXPORTDIR/pmc_lots.csv.part

rm $EXPORTDIR/declarations_lots.csv.part

bash bin/export_docs.sh Habilitation $EXPORTSLEEP $1 > $EXPORTDIR/habilitation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation.csv.part > $EXPORTDIR/habilitation.csv
rm $EXPORTDIR/habilitation.csv.part

php symfony export:habilitation-demandes $SYMFONYTASKOPTIONS > $EXPORTDIR/habilitation_demandes.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes.csv.part > $EXPORTDIR/habilitation_demandes.csv
php bin/export/export_liste_inao.php $EXPORTDIR/habilitation_demandes.csv.part | grep -E "^(Côtes du Rhône|Libelle Appellation)" > $EXPORTDIR/habilitation_demandes_inao.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation_demandes_inao.csv.part > $EXPORTDIR/habilitation_demandes_inao.csv
rm $EXPORTDIR/habilitation_demandes.csv.part $EXPORTDIR/habilitation_demandes_inao.csv.part

sleep $EXPORTSLEEP

if [ -z $IS_NO_VINIF ]; then
  bash bin/export_docs.sh DR $EXPORTSLEEP $1 > $EXPORTDIR/production.csv.part
  bash bin/export_docs.sh SV11 $EXPORTSLEEP $1 >> $EXPORTDIR/production.csv.part
  bash bin/export_docs.sh SV12 $EXPORTSLEEP $1 >> $EXPORTDIR/production.csv.part
  head -n 1 $EXPORTDIR/production.csv.part | sed 's/$/;Pseudo Production Id/' | iconv -f UTF8 -t ISO88591//TRANSLIT > $EXPORTDIR/production.csv
  cat $EXPORTDIR/production.csv.part | grep -E '^(DR|SV)' |  awk -F ';' '{uniq = $1"-"$2"-"$4 ; pseudoid = $4"-"substr($2,0,4); gsub(/"/, "", pseudoid) ; if ( ! unicite[uniq] || unicite[uniq] == $3 ) { print $0";"pseudoid  ; unicite[uniq] = $3 } }' | sort -t ';' -k 2,2 -r  | iconv -f UTF8 -t ISO88591//TRANSLIT >> $EXPORTDIR/production.csv

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

bash bin/export_docs.sh ParcellaireManquant $EXPORTSLEEP $1 > $EXPORTDIR/parcellairemanquant.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellairemanquant.csv.part > $EXPORTDIR/parcellairemanquant.csv
rm $EXPORTDIR/parcellairemanquant.csv.part

curl -s "http://$COUCHHOST:$COUCHDBPORT/$COUCHDBBASE/_all_docs?startkey=\"PARCELLAIRE-\"&endkey=\"PARCELLAIRE-Z\"" | cut -d '"' -f 4 | grep "PARCELLAIRE" | sort -r | awk -F '-' 'BEGIN { } { if(!identifiant[$2]) { print $0 } identifiant[$2] = $0; }' | while read id;do php symfony declaration:export-csv --header=$(if ! test $header;then echo -n "1"; fi) $SYMFONYTASKOPTIONS $id; header=0; done > $EXPORTDIR/parcellaire.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaire.csv.part > $EXPORTDIR/parcellaire.csv
rm $EXPORTDIR/parcellaire.csv.part

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

php symfony compte:export-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/comptes_simplifies.csv.part
cat $EXPORTDIR/comptes_simplifies.csv.part | head -n 1 > $EXPORTDIR/comptes_simplifies.csv.sorted.part
cat $EXPORTDIR/comptes_simplifies.csv.part | tail -n +2 | sort -t ";" -rk 1,2 >> $EXPORTDIR/comptes_simplifies.csv.sorted.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/comptes_simplifies.csv.sorted.part > $EXPORTDIR/comptes_simplifies.csv
rm $EXPORTDIR/comptes_simplifies.csv.part
rm $EXPORTDIR/comptes_simplifies.csv.sorted.part

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

grep 'Affecté à une dégustation (destination)' $EXPORTDIR/lots-historique.csv.part | sort -t ';' -k 9,9 -r | awk -F ';' '{ uniq = $18 ; if ( ! unicite[uniq] ) { print $18";"$14 ; unicite[uniq] = uniq ; }  }' | sort -t ';' -k 1,1 > $EXPORTDIR/lots-passages.csv
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

php symfony lots:export-suivi-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/lots_suivi.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/lots_suivi.csv.part > $EXPORTDIR/lots_suivi.csv
rm $EXPORTDIR/lots_suivi.csv.part

bash bin/export_docs.sh Degustation $EXPORTSLEEP $1 > $EXPORTDIR/degustations.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/degustations.csv.part > $EXPORTDIR/degustations.csv
rm $EXPORTDIR/degustations.csv.part

php symfony degustations:export-degustateurs-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/degustateurs.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/degustateurs.csv.part > $EXPORTDIR/degustateurs.csv
rm $EXPORTDIR/degustateurs.csv.part

php symfony export:csv-configuration $SYMFONYTASKOPTIONS > $EXPORTDIR/produits.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/produits.csv.part > $EXPORTDIR/produits.csv
rm $EXPORTDIR/produits.csv.part


php symfony declaration:engagements $SYMFONYTASKOPTIONS > $EXPORTDIR/engagements.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/engagements.csv.part > $EXPORTDIR/engagements.csv
rm $EXPORTDIR/engagements.csv.part

php symfony drev:reserve-interpro $SYMFONYTASKOPTIONS > $EXPORTDIR/reserve-interpro.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/reserve-interpro.csv.part > $EXPORTDIR/reserve-interpro.csv
rm $EXPORTDIR/reserve-interpro.csv.part


echo $EXPORT_SUB_HABILITATION | tr '|' '\n' | grep '[A-Z]' | while read subhab; do
    eval 'SUBDIR=$EXPORT_SUB_HABILITATION_'$subhab'_DIR'
    eval 'SUBFILTRE=$EXPORT_SUB_HABILITATION_'$subhab'_FILTRE'
    eval 'SUBMETABASE=$EXPORT_SUB_HABILITATION_'$subhab'_METABASE'
    mkdir -p $SUBDIR
    head -n 1 $EXPORTDIR/habilitation.csv > $SUBDIR/habilitation.csv
    cat $EXPORTDIR/habilitation.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591 >> $SUBDIR/habilitation.csv
    head -n 1 $EXPORTDIR/drev.csv > $SUBDIR/drev.csv
    cat $EXPORTDIR/drev.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/drev.csv
    cat $SUBDIR/habilitation.csv | iconv -f ISO88591 -t UTF8 | cut -d ";" -f 2 | sed 's/"//g' | sed -r 's/.{2}$//' | sort | uniq | grep -E '[0-9]+$' | tr "\n" "|" | sed 's/^/(/' | sed 's/|$/)/' > $SUBDIR/.etablissements.grep
    head -n 1 $EXPORTDIR/etablissements.csv > $SUBDIR/etablissements.csv
    cat $EXPORTDIR/etablissements.csv | iconv -f ISO88591 -t UTF8 | grep -E "^$(cat $SUBDIR/.etablissements.grep)" | iconv -f UTF8 -t ISO88591 >> $SUBDIR/etablissements.csv
    head -n 1 $EXPORTDIR/societe.csv > $SUBDIR/societes.csv
    cat $EXPORTDIR/societe.csv | iconv -f ISO88591 -t UTF8 | grep -E "^$(cat $SUBDIR/.etablissements.grep)" | iconv -f UTF8 -t ISO88591 >> $SUBDIR/societes.csv
    head -n 1 $EXPORTDIR/comptes.csv > $SUBDIR/comptes.csv
    cat $EXPORTDIR/comptes.csv | iconv -f ISO88591 -t UTF8 | grep -E "^$(cat $SUBDIR/.etablissements.grep)" | iconv -f UTF8 -t ISO88591 >> $SUBDIR/comptes.csv
    head -n 1 $EXPORTDIR/chais.csv > $SUBDIR/chais.csv
    cat $EXPORTDIR/chais.csv | iconv -f ISO88591 -t UTF8 | grep -E "^$(cat $SUBDIR/.etablissements.grep)" | iconv -f UTF8 -t ISO88591 >> $SUBDIR/chais.csv
    if test -s $EXPORTDIR/reserve-interpro.csv; then
        head -n 1 $EXPORTDIR/reserve-interpro.csv > $SUBDIR/reserve-interpro.csv
        cat $EXPORTDIR/reserve-interpro.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/reserve-interpro.csv
    fi

    if [ -z $IS_NO_VINIF ]; then
        head -n 1 $EXPORTDIR/dr.csv > $SUBDIR/dr.csv
        cat $EXPORTDIR/dr.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/dr.csv
        head -n 1 $EXPORTDIR/sv11.csv > $SUBDIR/sv11.csv
        cat $EXPORTDIR/sv11.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/sv11.csv
        head -n 1 $EXPORTDIR/sv12.csv > $SUBDIR/sv12.csv
        cat $EXPORTDIR/sv12.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/sv12.csv
        head -n 1 $EXPORTDIR/production.csv > $SUBDIR/production.csv
        cat $EXPORTDIR/production.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/production.csv
        head -n 1 $EXPORTDIR/lots.csv > $SUBDIR/lots.csv
        cat $EXPORTDIR/lots.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/lots.csv
        head -n 1 $EXPORTDIR/lots_suivi.csv > $SUBDIR/lots_suivi.csv
        cat $EXPORTDIR/lots_suivi.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/lots_suivi.csv
        head -n 1 $EXPORTDIR/factures.csv > $SUBDIR/factures.csv
        cat $EXPORTDIR/factures.csv | iconv -f ISO88591 -t UTF8 | grep -E ";$subhab;" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/factures.csv
        head -n 1 $EXPORTDIR/drev_lots.csv > $SUBDIR/drev_lots.csv
        cat $EXPORTDIR/drev_lots.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/drev_lots.csv
        head -n 1 $EXPORTDIR/transaction_lots.csv > $SUBDIR/transaction_lots.csv
        cat $EXPORTDIR/transaction_lots.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/transaction_lots.csv
        head -n 1 $EXPORTDIR/conditionnement_lots.csv > $SUBDIR/conditionnement_lots.csv
        cat $EXPORTDIR/conditionnement_lots.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/conditionnement_lots.csv
        head -n 1 $EXPORTDIR/pmc_lots.csv > $SUBDIR/pmc_lots.csv
        cat $EXPORTDIR/pmc_lots.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/pmc_lots.csv
        head -n 1 $EXPORTDIR/pmcnc_lots.csv> $SUBDIR/pmcnc_lots.csv
        cat $EXPORTDIR/pmcnc_lots.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/pmcnc_lots.csv
        head -n 1 $EXPORTDIR/lots-historique.csv > $SUBDIR/lots-historique.csv
        cat $EXPORTDIR/lots-historique.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/lots-historique.csv
        head -n 1 $EXPORTDIR/paiements.csv > $SUBDIR/paiements.csv
        cat $EXPORTDIR/paiements.csv | iconv -f ISO88591 -t UTF8 | grep -E ";$subhab;" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/paiements.csv
        head -n 1 $EXPORTDIR/degustations.csv > $SUBDIR/degustations.csv
        cat $EXPORTDIR/degustations.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/degustations.csv
        head -n 1 $EXPORTDIR/parcellaire.csv > $SUBDIR/parcellaire.csv
        cat $EXPORTDIR/parcellaire.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/parcellaire.csv
        head -n 1 $EXPORTDIR/parcellairemanquant.csv > $SUBDIR/parcellairemanquant.csv
        cat $EXPORTDIR/parcellairemanquant.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/parcellairemanquant.csv
        cat $EXPORTDIR/parcellairemanquant.csv | iconv -f ISO88591 -t UTF8 | grep -vE ";(AOC|IGP|AOP);" | grep -E "[\|;]{1}$subhab[\|;]{1}" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/parcellairemanquant.csv
        head -n 1 $EXPORTDIR/parcellaireirrigue.csv > $SUBDIR/parcellaireirrigue.csv
        cat $EXPORTDIR/parcellaireirrigue.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/parcellaireirrigue.csv
        head -n 1 $EXPORTDIR/parcellaireirrigable.csv > $SUBDIR/parcellaireirrigable.csv
        cat $EXPORTDIR/parcellaireirrigable.csv | iconv -f ISO88591 -t UTF8 | grep -E "$SUBFILTRE" | iconv -f UTF8 -t ISO88591  >> $SUBDIR/parcellaireirrigable.csv
    fi
    if test "$SUBMETABASE"; then
        python3 bin/csv2sql.py $SUBMETABASE".tmp" $SUBDIR
        mv $SUBMETABASE".tmp" $SUBMETABASE
    fi
done

mkdir -p $EXPORTDIR/stats

cd bin/notebook/

if ! test "$APPLICATION_PYTHON_EXPORT"; then
    APPLICATION_PYTHON_EXPORT=$APPLICATION
fi

if [[ $APPLICATION_PYTHON_EXPORT == igp* ]];then
  ls igp_*.py | while read script; do python3 $script $APPLICATION_PYTHON_EXPORT;done
else
  ls "$APPLICATION_PYTHON_EXPORT"_*.py | while read script; do python3 $script;done
fi

cd -

find $EXPORTDIR -type f -empty -delete

if test "$METABASE_SQLITE"; then
    python3 bin/csv2sql.py $METABASE_SQLITE".tmp" $EXPORTDIR
    mv $METABASE_SQLITE".tmp" $METABASE_SQLITE
fi
