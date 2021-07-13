DATADIR=$1

echo "appellation;cepage" > $DATADIR/cepages.csv
ls $DATADIR/cepages/cepages_*.html | while read file; do nodejs parse_cepages.js $file; done | sort -t ";" -k1.7,1.10 -k1.4,1.5 -k1.1,1.2 -k2,2 >> $DATADIR/cepages.csv