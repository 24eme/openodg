#!/bin/bash
. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

DREVPATH=$1

cat $DREVPATH | grep -E "      [A-Z_]+\:" | sed -r 's|^([\ ]+)([A-Z_]+)\:|\2|' | while read odg ; do
  mkdir $EXPORTDIR"/"$odg 2> /dev/null

  bash bin/export_docs.sh DRev 10 $odg > $EXPORTDIR"/"$odg"/drev.csv.part"
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR"/"$odg"/drev.csv.part" > $EXPORTDIR"/"$odg"/drev.csv"
done

sleep 10

bash bin/export_docs.sh DR 30 > $EXPORTDIR/dr.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/dr.csv.part > $EXPORTDIR/dr.csv

bash bin/export_docs.sh SV12 30 > $EXPORTDIR/sv12.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv12.csv.part > $EXPORTDIR/sv12.csv

bash bin/export_docs.sh SV11 30 > $EXPORTDIR/sv11.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv11.csv.part > $EXPORTDIR/sv11.csv

php symfony pieces:export-csv $SYMFONYTASKOPTIONS >  $EXPORTDIR/pieces.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/pieces.csv.part > $EXPORTDIR/pieces.csv

sleep 10


cat $DREVPATH | grep -E "      [A-Z_]+\:" | sed -r 's|^([\ ]+)([A-Z_]+)\:|\2|' | while read odg ; do
  head -n 1 $EXPORTDIR/dr.csv.part > $EXPORTDIR"/"$odg"/dr.csv.part"
  head -n 1 $EXPORTDIR/sv12.csv.part > $EXPORTDIR"/"$odg"/sv12.csv.part"
  head -n 1 $EXPORTDIR/sv11.csv.part > $EXPORTDIR"/"$odg"/sv11.csv.part"
  head -n 1 $EXPORTDIR/pieces.csv.part > $EXPORTDIR"/"$odg"/pieces.csv.part"
  cat $EXPORTDIR"/"$odg"/drev.csv.part" | cut -d ';' -f 2 | sort | uniq | grep -vi 'Identifiant' | while read identifiant ; do
    cat $EXPORTDIR/dr.csv.part | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/dr.csv.part"
    done
    cat $EXPORTDIR/sv12.csv.part | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/sv12.csv.part"
    done
    cat $EXPORTDIR/sv11.csv.part | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/sv11.csv.part"
    done
    cat $EXPORTDIR/pieces.csv.part | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/pieces.csv.part"
    done
  done
  ruby -ryaml -e "puts YAML::load(open(ARGV.first).read)['all']['configuration']['drev']['odg']['"$odg"']['produits']" apps/loire/config/drev.yml 2> /dev/null > "/tmp/"$odg".produits"
  head -n 1 web/exports/dr_igploire_2019.csv > "web/exports/"$odg"/2019_dr_douane.csv"
  cat "/tmp/"$odg".produits"  | sed 's/ * - "//' | sed 's/"//'  | sed 's|/|;|g' | sed 's/\(appellations\|mentions\|certifications\|genres\);//g'  | sed 's/^;*/;/' | sed 's/;*$/;/'  | while read produit ; do grep $produit web/exports/dr_igploire_2019.csv | awk -F ';' '{print $3";"$4}' | sort -u ; done | sort -u  | while read cvi ; do grep $cvi web/exports/dr_igploire_2019.csv ; done >> "web/exports/"$odg"/2019_dr_douane.csv"
  rm "/tmp/"$odg".produits"

  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR"/"$odg"/dr.csv.part" > $EXPORTDIR"/"$odg"/dr.csv"
  rm $EXPORTDIR"/"$odg"/dr.csv.part"
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR"/"$odg"/sv12.csv.part" > $EXPORTDIR"/"$odg"/sv12.csv"
  rm $EXPORTDIR"/"$odg"/sv12.csv.part"
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR"/"$odg"/sv11.csv.part" > $EXPORTDIR"/"$odg"/sv11.csv"
  rm $EXPORTDIR"/"$odg"/sv11.csv.part"
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR"/"$odg"/pieces.csv.part" > $EXPORTDIR"/"$odg"/pieces.csv"
  rm $EXPORTDIR"/"$odg"/pieces.csv.part"
  rm $EXPORTDIR"/"$odg"/drev.csv.part"
done

rm $EXPORTDIR/dr.csv.part
rm $EXPORTDIR/sv12.csv.part
rm $EXPORTDIR/sv11.csv.part
rm $EXPORTDIR/pieces.csv.part

echo '{cvi=$4 ; insee=substr(cvi,0,5); print "php symfony dr:pdf '$SYMFONYTASKOPTIONS' "$1"-"$3"-"$2" '$EXPORTDIR'/DR/"insee"/"$1"-"cvi"-"$2".pdf"}' > /tmp/awk.$$
cat $EXPORTDIR/NANTES/dr.csv | grep -v '^#' | awk -F ';' -f /tmp/awk.$$  | sort -u | sh
echo '{cvi=$4 ; insee=substr(cvi,0,5); pdf="'$EXPORTDIR'/NANTES/DREV/"insee"/DREV-"cvi"-"$2".pdf" ; print "mkdir -p $(dirname "pdf") ; cp "$6" "pdf" ;" }' > /tmp/awk.$$
cat $EXPORTDIR/NANTES/drev.csv | awk -F ';' '{print $41}'   | grep ^DREV | sort -u | while read drev ; do echo php symfony drev:pdf $SYMFONYTASKOPTIONS $drev --trace ; done | sh | awk -F ';' -f /tmp/awk.$$ | sh
rm /tmp/awk.$$
