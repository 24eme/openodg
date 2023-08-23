. "$(dirname "$0")/config.ds.inc"

ANNEE=$1
DOCUMENTS_SUFFIXE=$(expr "${ANNEE: -2}" - 1)${ANNEE: -2}
DOCUMENTS_FOLDER=$PATH_PRODOUANE/documents/
TMP_CSV_FILE=/tmp/ds_$ANNEE.csv

if ! test "$ANNEE" ; then
	echo "PARAMETRE ANNEE MANQUANT"
	exit 1;
fi

cd $PATH_PRODOUANE

bash bin/download_stock.sh $ANNEE > $TMP_FILE_LIST_CVI

cat $TMP_FILE_LIST_CVI | while read cvi; do
    bash bin/download_stock.sh $ANNEE $cvi #DEBUG=TRUE DEBUG_WITH_BROWSER="true"
done

ls $DOCUMENTS_FOLDER | grep 'ds-' | grep $DOCUMENTS_SUFFIXE.xls | while read xls ; do xls2csv $DOCUMENTS_FOLDER/$xls > $DOCUMENTS_FOLDER/"${xls//.xls/.csv}"  ; done

header=true

if test -f $TMP_CSV_FILE; then
    rm $TMP_CSV_FILE
fi

touch $TMP_CSV_FILE

cd -

ls $DOCUMENTS_FOLDER | grep 'ds-' |  grep $DOCUMENTS_SUFFIXE.csv | while read csv ; do php symfony import:ds-csv --application=provence --header=$header ../../prodouane_scrapy/documents/$csv;header=null; done >> $TMP_CSV_FILE

mv $TMP_CVS_FILE $EXPORT_FOLDER

rm $TMP_FILE_LIST_CVI
rm $TMP_CSV_FILE