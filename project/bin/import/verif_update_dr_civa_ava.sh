#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

rm -rf /tmp/comparaison_dr_ava_civa_$CAMPAGNE
mkdir /tmp/comparaison_dr_ava_civa_$CAMPAGNE

curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous?reduce=false" | cut -d '"' -f 4 | grep "DREV-" | grep "\-$CAMPAGNE" | sort | cut -d "-" -f 2 | while read CVI; do
	if ! test -f data/dr/$1/DR_"$CVI"_"$CAMPAGNE".csv; then
		continue;
	fi
	cat data/dr/$1/DR_"$CVI"_"$CAMPAGNE".csv | grep -v '^"CVI' | sed -r 's/[0-9]{4}-[0-9]{2}-[0-9]{2}//g' | awk -F ';' '{ $1=""; $2=""; print $0}' > /tmp/DR_"$CVI"_"$CAMPAGNE"_CIVA.csv
	curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/DREV-$CVI-$CAMPAGNE/DR.csv" | grep -v '^"CVI' | sed -r 's/[0-9]{4}-[0-9]{2}-[0-9]{2}//g' | awk -F ';' '{ $1=""; $2=""; print $0}' > /tmp/DR_"$CVI"_"$CAMPAGNE"_AVA.csv
	nbdiff=$(diff -y /tmp/DR_"$CVI"_"$CAMPAGNE"_AVA.csv /tmp/DR_"$CVI"_"$CAMPAGNE"_CIVA.csv | grep "|" | wc -l)
	if test $(echo $nbdiff | grep -Ev "^0$"); then
		echo "$CVI;$nbdiff"
		cat /tmp/DR_"$CVI"_"$CAMPAGNE"_AVA.csv >> /tmp/comparaison_dr_ava_civa_$CAMPAGNE/AVA.csv
		cat /tmp/DR_"$CVI"_"$CAMPAGNE"_CIVA.csv >> /tmp/comparaison_dr_ava_civa_$CAMPAGNE/CIVA.csv
		cp /tmp/DR_"$CVI"_"$CAMPAGNE"_AVA.csv /tmp/comparaison_dr_ava_civa_$CAMPAGNE/
		cp /tmp/DR_"$CVI"_"$CAMPAGNE"_CIVA.csv /tmp/comparaison_dr_ava_civa_$CAMPAGNE/
	fi
	rm /tmp/DR_"$CVI"_"$CAMPAGNE"_AVA.csv /tmp/DR_"$CVI"_"$CAMPAGNE"_CIVA.csv
done

echo /tmp/comparaison_dr_ava_civa_$CAMPAGNE
