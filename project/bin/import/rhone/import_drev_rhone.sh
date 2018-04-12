


WORKDIROPERATEUR=$WORKDIR
WORKDIR=.
DATADIR=..

echo "#dossierid;id rhone;millesime" > $WORKDIR/dossier_cvi.csv
cat $DATADIR/SGV_DREV_drev_dossier.txt | iconv -f iso88591 -t utf8 | tr -d "\r" | awk -F ';' '{print $1";"$2";"$3}' | sort -t ";" -k 1,1 > $WORKDIR/dossier_cvi.csv


echo "#ligneid;dossierid" > $WORKDIR/ligne.csv
cat $DATADIR/SGV_DREV_drev_ligne.txt | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,2 | sort -t ";" -k 2,2 > $WORKDIR/ligne.csv
echo "#id rhone;millesime;id ligne" > $WORKDIR/ligne_cvi.csv
join -t ";" -1 1 -2 2 $WORKDIR/dossier_cvi.csv $WORKDIR/ligne.csv | cut -d ";" -f 2,3,4 | sort -t ";" -k 3,3 > $WORKDIR/ligne_cvi.csv

echo "#CLE;valeur;id colonne;id ligne" > $WORKDIR/valeur.csv
cat $DATADIR/SGV_DREV_drev_valeur.txt | iconv -f iso88591 -t utf8 | tr -d "\r" | sort -t ";" -k 4,4 > $WORKDIR/valeur.csv

echo "#id ligne;id rhone;millesime;id valeur;valeur;id colonne"  >  $WORKDIR/valeur_cvi.csv
join -t ";" -1 3 -2 4 $WORKDIR/ligne_cvi.csv $WORKDIR/valeur.csv | awk -F ";" '{print $2";"$3";"$1";"$6";"$5}' | sort -t ";" -k 4,4 > $WORKDIR/valeur_cvi.csv

echo "#id param;LIBELLE" > $WORKDIR/valeur_param.csv
cat $DATADIR/SGV_DREV_drev_param_colonne.txt | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 1,4 | sort -t ";" -k 1,1 > $WORKDIR/valeur_param.csv

echo "#id valeur;id ligne;id rhone;millesime;valeur;id colonne;LIBELLE param" > $WORKDIR/valeur_full_cvi_param.csv
join -t ";" -1 4 -2 1 $WORKDIR/valeur_cvi.csv $WORKDIR/valeur_param.csv | awk -F ';' '{print $3";"$2";"$4";"sprintf("%05d", $1)";"$5";"$6";"$7}' | sort -t ';' -k 1,4 > $WORKDIR/valeur_full_cvi_param.csv

echo "id vin;libelle vin" > $WORKDIR/vins.csv
cat $DATADIR/SGV_BASE_VIN.txt | awk -F ';' '{print $2";"$17}' | sort -t ';' -k 1,1 > $WORKDIR/vins.csv
cat valeur_full_cvi_param.csv | awk -F ';' 'BEGIN { tab[0] = ""; } { if ( $4 == "00050" ) { for (i = 0 ; i < length( tab ) ; i++ ) { printf tab[i]";" ; tab[i] = "" ;}   print  "" ; tab[0]=$0 ; } tab[$4*1] = $5 } END { for (i = 0 ; i < length( tab ) ; i++ ) { printf tab[i]";" } ; print ""} ' | sort -t ';' -k 57,57 > $WORKDIR/full_drev.csv

join -1 1 -2 57 -t ';' $WORKDIR/vins.csv $WORKDIR/full_drev.csv | sort -t ';' -k 3,8 > $WORKDIR/full_vins_drev.csv

echo "#millesime;id rhone;AOC;libelle produit;superficie;volume revendique;dont vci;rafraichi;complement;detruire" > $WORKDIR/drev.csv
cat $WORKDIR/full_vins_drev.csv | awk -F ';' '{print $3";"$4";"$8";"$2";"$118+$128*0.01+$138*0.0001";"$168+$178*0.01";"$818+$828*0.01";"$248+$258*0.01";"$278+$288*0.01";"$304+$305*0.01";"}' | sed 's/;0;/;;/g' | sed 's/;0;/;;/g' >> $WORKDIR/drev.csv
