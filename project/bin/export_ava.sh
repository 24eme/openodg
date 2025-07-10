#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

split_export_by_annee () {
    EXPORTTYPE=$1
    FILEPART=$EXPORTDIR/$EXPORTTYPE.csv.part
    cat $FILEPART | cut -d ";" -f 1 | sort -u | grep -E "^[0-9]+" | while read annee; do
        FILEPARTANNEE=$EXPORTDIR/$annee/"$annee"_$EXPORTTYPE.csv.part
        FILEANNEE=$EXPORTDIR/$annee/"$annee"_$EXPORTTYPE.csv
        mkdir $EXPORTDIR/$annee 2> /dev/null;
        head -n 1 $FILEPART > $FILEPARTANNEE
        cat $FILEPART | grep -E "^$annee;" >> $FILEPARTANNEE
        iconv -f UTF8 -t ISO88591//TRANSLIT $FILEPARTANNEE > $FILEANNEE
        rm $FILEPARTANNEE
    done;
}

bash bin/export_docs.sh DRev > $EXPORTDIR/drev.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev.csv.part > $EXPORTDIR/drev.csv
split_export_by_annee "drev"
rm $EXPORTDIR/drev.csv.part

bash bin/export_docs.sh DRevMarc > $EXPORTDIR/drev_marc.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/drev_marc.csv.part > $EXPORTDIR/drev_marc.csv
split_export_by_annee "drev_marc"
rm $EXPORTDIR/drev_marc.csv.part

bash bin/export_docs.sh Facture > $EXPORTDIR/facture.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/facture.csv.part > $EXPORTDIR/facture.csv
rm $EXPORTDIR/facture.csv.part

bash bin/export_docs.sh ParcellaireAffectation > $EXPORTDIR/parcellaire_affectation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaire_affectation.csv.part > $EXPORTDIR/parcellaire_affectation.csv
split_export_by_annee "parcellaire_affectation"
rm $EXPORTDIR/parcellaire_affectation.csv.part

bash bin/export_docs.sh ParcellaireIrrigable > $EXPORTDIR/parcellaire_irrigable.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaire_irrigable.csv.part > $EXPORTDIR/parcellaire_irrigable.csv
split_export_by_annee "parcellaire_irrigable"
rm $EXPORTDIR/parcellaire_irrigable.csv.part

bash bin/export_docs.sh ParcellaireIrrigue > $EXPORTDIR/parcellaire_irrigue.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaire_irrigue.csv.part > $EXPORTDIR/parcellaire_irrigue.csv
split_export_by_annee "parcellaire_irrigue"
rm $EXPORTDIR/parcellaire_irrigue.csv.part

curl -s "http://$COUCHHOST:$COUCHDBPORT/$COUCHDBBASE/_all_docs?startkey=\"PARCELLAIRE-\"&endkey=\"PARCELLAIRE-Z\"" | cut -d '"' -f 4 | grep "PARCELLAIRE" | sort -r | awk -F '-' 'BEGIN { } { if(!identifiant[$2]) { print $0 } identifiant[$2] = $0; }' | while read id;do php symfony declaration:export-csv --header=$(if ! test $header;then echo -n "1"; fi) $SYMFONYTASKOPTIONS $id; header=0; done > $EXPORTDIR/parcellaire.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/parcellaire.csv.part > $EXPORTDIR/parcellaire.csv
rm $EXPORTDIR/parcellaire.csv.part

bash bin/export_docs.sh TravauxMarc > $EXPORTDIR/travaux_marc.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/travaux_marc.csv.part > $EXPORTDIR/travaux_marc.csv
split_export_by_annee "travaux_marc"
rm $EXPORTDIR/travaux_marc.csv.part

bash bin/export_docs.sh Tirage > $EXPORTDIR/tirage.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/tirage.csv.part > $EXPORTDIR/tirage.csv
split_export_by_annee "tirage"
rm $EXPORTDIR/tirage.csv.part

bash bin/export_docs.sh RegistreVCI > $EXPORTDIR/registre_vci.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/registre_vci.csv.part > $EXPORTDIR/registre_vci.csv
split_export_by_annee "registre_vci"
rm $EXPORTDIR/registre_vci.csv.part

bash bin/export_docs.sh Constats 5 > $EXPORTDIR/constats.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/constats.csv.part > $EXPORTDIR/constats.csv
split_export_by_annee "constats"
rm $EXPORTDIR/constats.csv.part

bash bin/export_docs.sh Habilitation > $EXPORTDIR/habilitation.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/habilitation.csv.part > $EXPORTDIR/habilitation.csv
rm $EXPORTDIR/habilitation.csv.part

echo "campagne;categorie;nombre opérateurs;superficie totale" > $EXPORTDIR/facture_stats.csv.part;
cat $EXPORTDIR/facture.csv | iconv -f ISO88591//TRANSLIT -t UTF-8 | cut -d ";" -f 18,19 | sed -r 's/_.+;/;/' | grep "TEMPLATE" | sort | uniq | while read ligne; do
    TEMPLATE=$(echo $ligne | cut -d ";" -f 1);
    CATEGORIE=$(echo $ligne | cut -d ";" -f 2);
    echo -n "$TEMPLATE;$CATEGORIE;" >> $EXPORTDIR/facture_stats.csv.part;
    cat $EXPORTDIR/facture.csv | iconv -f ISO88591//TRANSLIT -t UTF-8 | grep "$TEMPLATE" | cut -d ";" -f 10,11,14,19,21 | sed -r 's/FACTURE-([A-Z0-9]+)-[0-9]+/\1/' | grep "$CATEGORIE" | awk -F ';' '{ coefficient = 1; if($1 == "DEBIT") { coefficient = -1 } totalfacture[$3] += $2*coefficient; totalsuperficie[$3] += $5*coefficient } END { total_superficie = 0; nb = 0; for (key in totalfacture) { if(totalfacture[key] > 0) { total_superficie += totalsuperficie[key]; nb++;  }} printf("%d;%0.2f\n", nb, total_superficie) }' >> $EXPORTDIR/facture_stats.csv.part;
done;
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/facture_stats.csv.part > $EXPORTDIR/facture_stats.csv
rm $EXPORTDIR/facture_stats.csv.part

for ((i=2015 ; i <= $(date +%Y -d "-9 month") ; i++)); do
    mkdir $EXPORTDIR/$i 2> /dev/null;
    curl -s "$HTTP_CIVA_DATA/DR/$i.csv" | iconv -f UTF8 -t ISO88591//TRANSLIT > $EXPORTDIR/$i/"$i"_dr.csv
    curl -s "$HTTP_CIVA_DATA/Production/$i.csv" | iconv -f UTF8 -t ISO88591//TRANSLIT > $EXPORTDIR/$i/"$i"_sv.csv
done

rm $EXPORTDIR/bilan_vci.tmp.csv 2> /dev/null
echo "campagne;Produit;titre;raison_sociale;adresse;commune;code_postal;CVI Operateur;siret;stock_vci_n-1;dr_surface;dr_volume;dr_vci;vci_constitue;vci_complement;vci_substitution;vci_rafraichi;vci_desctruction;drev_revendique_n;drev_revendique_n-1;stock_vci_n;rendement_vci_ha_hl" > $EXPORTDIR/bilan_vci.tmp.csv
cat $EXPORTDIR/bilan_vci.tmp.csv > $EXPORTDIR/bilan_vci.csv
for ((i=2018 ; i <= $(date +%Y -d "-9 month") ; i++)); do
    rm $EXPORTDIR/bilan_vci.tmp.csv 2> /dev/null
    python3 bin/notebook/bilan_vci_ava.py "$i" "AOC Crémant d'Alsace" $EXPORTDIR $EXPORTDIR/bilan_vci.tmp.csv
    cat $EXPORTDIR/bilan_vci.tmp.csv | tail -n +2 >> $EXPORTDIR/bilan_vci.csv
    rm $EXPORTDIR/bilan_vci.tmp.csv 2> /dev/null
    python3 bin/notebook/bilan_vci_ava.py "$i" "AOC Alsace blanc" $EXPORTDIR $EXPORTDIR/bilan_vci.tmp.csv
    cat $EXPORTDIR/bilan_vci.tmp.csv | tail -n +2 >> $EXPORTDIR/bilan_vci.csv
done
rm $EXPORTDIR/bilan_vci.tmp.csv 2> /dev/null
iconv -f ISO88591//TRANSLIT -t UTF8 $EXPORTDIR/bilan_vci.csv > $EXPORTDIR/bilan_vci.csv.part
split_export_by_annee "bilan_vci"
rm $EXPORTDIR/bilan_vci.csv.part

php symfony declaration:engagements $SYMFONYTASKOPTIONS > $EXPORTDIR/engagements.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/engagements.csv.part > $EXPORTDIR/engagements.csv
rm $EXPORTDIR/engagements.csv.part

mkdir $EXPORTDIR/archives 2> /dev/null
zip $EXPORTDIR/archives/"$(date +%Y%m%d).daily.zip" $EXPORTDIR/*.csv
cp $EXPORTDIR/archives/"$(date +%Y%m%d).daily.zip" $EXPORTDIR/archives/"$(date +%Y%m.sem%W).weekly.zip"
cp $EXPORTDIR/archives/"$(date +%Y%m%d).daily.zip" $EXPORTDIR/archives/"$(date +%Y%m).monthly.zip"
find $EXPORTDIR/archives/ -name "*.daily.zip" -type f -mtime +7 -delete
find $EXPORTDIR/archives/ -name "*.weekly.zip" -type f -mtime +29 -delete
find $EXPORTDIR/archives/ -name "*.monthly.zip" -type f -mtime +94 -delete
