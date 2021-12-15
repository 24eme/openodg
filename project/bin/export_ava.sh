#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

split_export_by_annee () {
    EXPORTTYPE=$1
    FILEPART=$EXPORTDIR/$EXPORTTYPE.csv.part
    cat $FILEPART | cut -d ";" -f 1 | sort | uniq | grep -E "^[0-9]+" | while read annee; do
        FILEPARTANNEE=$EXPORTDIR/$annee/"$annee"_$EXPORTTYPE.csv.part
        FILEANNEE=$EXPORTDIR/$annee/"$annee"_$EXPORTTYPE.csv
        mkdir $EXPORTDIR/$annee 2> /dev/null;
        head -n 1 $FILEPART > $FILEPARTANNEE
        cat $FILEPART | sort -t ";" -k 1,1 | grep -E "^$annee;" >> $FILEPARTANNEE
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

bash bin/export_docs.sh Parcellaire > $EXPORTDIR/parcellaire.csv.part
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

echo "campagne;categorie;nombre opérateurs;superficie totale" > $EXPORTDIR/facture_stats.csv.part;
cat $EXPORTDIR/facture.csv | iconv -f ISO88591//TRANSLIT -t UTF-8 | cut -d ";" -f 18,19 | sed -r 's/_.+;/;/' | grep "TEMPLATE" | sort | uniq | while read ligne; do
    TEMPLATE=$(echo $ligne | cut -d ";" -f 1);
    CATEGORIE=$(echo $ligne | cut -d ";" -f 2);
    echo -n "$TEMPLATE;$CATEGORIE;" >> $EXPORTDIR/facture_stats.csv.part;
    cat $EXPORTDIR/facture.csv | iconv -f ISO88591//TRANSLIT -t UTF-8 | grep "$TEMPLATE" | cut -d ";" -f 10,11,14,19,21 | sed -r 's/FACTURE-([A-Z0-9]+)-[0-9]+/\1/' | grep "$CATEGORIE" | awk -F ';' '{ coefficient = 1; if($1 == "DEBIT") { coefficient = -1 } totalfacture[$3] += $2*coefficient; totalsuperficie[$3] += $5*coefficient } END { total_superficie = 0; nb = 0; for (key in totalfacture) { if(totalfacture[key] > 0) { total_superficie += totalsuperficie[key]; nb++;  }} printf("%d;%0.2f\n", nb, total_superficie) }' >> $EXPORTDIR/facture_stats.csv.part;
done;
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/facture_stats.csv.part > $EXPORTDIR/facture_stats.csv
rm $EXPORTDIR/facture_stats.csv.part

for ((i=2015 ; $(date +%Y -d "-9 month") ; i++)); do
    mkdir $EXPORTDIR/$i 2> /dev/null;
    curl -s "$HTTP_CIVA_DATA/DR/$i.csv" | iconv -f UTF8 -t ISO88591//TRANSLIT > $EXPORTDIR/$i/"$i"_dr.csv
done

rm $EXPORTDIR/bilan_vci.tmp.csv 2> /dev/null
echo "campagne;Produit;titre;raison_sociale;adresse;commune;code_postal;CVI;siret;stock_vci_n-1;dr_surface;dr_volume;dr_vci;vci_constitue;vci_complement;vci_substitution;vci_rafraichi;vci_desctruction;drev_revendique_n;drev_revendique_n-1;stock_vci_n" > $EXPORTDIR/bilan_vci.tmp.csv
cat $EXPORTDIR/bilan_vci.tmp.csv > $EXPORTDIR/bilan_vci.csv
for ((i=2018 ; $(date +%Y) -i ; i++)); do
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
