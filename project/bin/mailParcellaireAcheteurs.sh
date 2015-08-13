#bash bin/export/parecellaire.sh 2015 > /tmp/parecellaire_2015.sh

cat /tmp/parcellaire_2015.csv | grep -v ";AOC Crémant d'Alsace;" | grep "AUTORISÉE" > /tmp/parcellaire_acheteurs_parcelles.csv

cat /tmp/parcellaire_acheteurs_parcelles.csv | cut -d ";" -f 15,16 | sort | uniq > /tmp/parcellaire_acheteurs.csv

mkdir /tmp/export_parcellaire_acheteurs 2> /dev/null

cat /tmp/parcellaire_acheteurs.csv | while read ligne  
do
    CVI=$(echo $ligne | cut -d ";" -f 1)
    NOM=$(echo $ligne | cut -d ";" -f 2 | sed -r 's/[éèê]+/e/g' | sed -r 's/[àâ]+/a/g' | sed -r 's/ç/c/g' | sed -r "s/[' -]+/_/g" | sed -r 's/["()&\.]+//g' | sed -r 's/__/_/g' | tr '[a-z]' '[A-Z]')
    FILE_ACHETEUR=/tmp/export_parcellaire_acheteurs/parcellaire_acheteur_"$CVI"_"$NOM".csv
    echo -ne "\xef\xbb\xbf" > $FILE_ACHETEUR
    head -n 1 /tmp/parcellaire_2015.csv >> $FILE_ACHETEUR
    cat /tmp/parcellaire_acheteurs_parcelles.csv | grep "$CVI" | sort -t ";" -k 9,9 >> $FILE_ACHETEUR
done
