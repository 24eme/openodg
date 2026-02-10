#/bin/bash

if test "$CONFIG_FILE"; then
	if test -f "$CONFIG_FILE"; then
		source "$CONFIG_FILE";
	else
		echo $CONFIG_FILE not found;
		exit 1;
	fi
else
	source $(dirname $0)/../bin/config.inc
fi

campagne=$1

if ! test "$campagne" ; then
    echo "$0 <campagne>";
    exit 1;
fi

millesime=$(echo $campagne | sed 's/-.*//')

if ! test "$SCRAPY_DIR" ; then
    echo "SCRAPY_DIR missing in config.inc"
    exit 2
fi

awk -F ';' '{print $12}'  $EXPORTDIR/produits.csv  | sed 's/^\(.....\).*/\1/' | strings | sort -u > /tmp/inao.list

grep -a ";"$campagne";"  $EXPORTDIR/production.csv | awk -F ';' '{print $1";"$2";"$3";"$4";"$4}'| uniq > /tmp/production.$millesime.csv

mkdir -p data/douane_diff

cat /tmp/inao.list | sed 's/^\(.....\).*/\1/'  | sort -u | grep ^[13] | while read inao ; do
    rgrep -l $inao $SCRAPY_DIR/documents/*json ;
done | grep $millesime'-' | sort -u | awk -F '-' '{print $3" "$1"-"$2"-"$3}'  | sed 's/.json//' | while read cvi file; do
    csv=$(echo $file | sed 's/json/csv/') ;
    if test -f "$csv"; then
        file=$csv;
    fi ;
    grep -a $cvi /tmp/production.$millesime.csv | awk -F ";" '{print "'$file' "$3}'  | grep ' ' || echo $cvi" missing" ;
done | sort -u > data/douane_diff/production.files

rm /tmp/production.$millesime.csv

grep ' missing' data/douane_diff/production.files

cat data/douane_diff/production.files | grep -v ' missing' | while read file id ; do
    php symfony douaneRecolte:convert2csv $SYMFONYTASKOPTIONS $file 2> /dev/null | awk -F ';' '{print $1";"$2";"$3";"$4";"$5";"$6";"$7";"$8";"$9";"$10";"$11";"$12";"$13";"$14";"$15";"$16";;;"$19";"$20";"$21";"$22";"$23";;"$25";"$26";;"$28";"$29}' | sed 's/ô/o/g' | sed 's/[éèêë]/e/g' | grep -v '^#' | grep -a ';\(AOC\|IGP\);' | sed 's/"//g' > /tmp/douane_file.$id.$$.csv
    cat /tmp/douane_file.$id.$$.csv  | sed 's/[,\.0]//g' | sed 's/  */ /g' | sed 's/ *;/;/g' > /tmp/douane_file.clean.$id.$$.csv
    php symfony declaration:export-csv $SYMFONYTASKOPTIONS "DOUANE-"$id"-"2025 2> /dev/null | awk -F ';' '{print $1";"$2";"$3";"$4";"$5";"$6";"$7";"$8";"$9";"$10";"$11";"$12";"$13";"$14";"$15";"$16";;;"$19";"$20";"$21";"$22";"$23";;"$25";"$26";;"$28";"$29}' | sed 's/ô/o/g' | sed 's/[éèêë]/e/g' | grep -v '^#' | grep -a ';\(AOC\|IGP\);' | sed 's/"//g' > /tmp/douane_db.$id.$$.csv
    cat /tmp/douane_db.$id.$$.csv  | sed 's/[,\.0]//g' | sed 's/  */ /g' | sed 's/ *;/;/g' > /tmp/douane_db.clean.$id.$$.csv

    echo "COMPARE $file $id"
    if diff /tmp/douane_file.$id.$$.csv /tmp/douane_db.clean.$id.$$.csv | grep ';' > /dev/null ; then
        mkdir -p "data/douane_diff/"$id
        mv /tmp/douane_file.$id.$$.csv "data/douane_diff/"$id/douane_file_douane.csv
        mv /tmp/douane_db.$id.$$.csv "data/douane_diff/"$id/douane_in_db.csv
        mv /tmp/douane_file.clean.$id.$$.csv "data/douane_diff/"$id/douane_file_douane.clean.csv
        mv /tmp/douane_db.clean.$id.$$.csv "data/douane_diff/"$id/douane_in_db.clean.csv
        echo "diff result: $file $id DIFF (versions: data/douane_diff/"$id")"
    else
        echo "diff result: $file $id OK (identical)"
        rm /tmp/douane_file.$id.$$.csv /tmp/douane_db.$id.$$.csv /tmp/douane_file.clean.$id.$$.csv /tmp/douane_db.clean.$id.$$.csv
    fi
done
