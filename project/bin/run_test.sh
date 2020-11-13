. $(echo $0 | sed 's/[^\/]*$//')config.inc

if ! test "$WORKINGDIR"; then
    WORKINGDIR=$(dirname $0)"/../"
fi

if [ "$(echo $COUCHTEST | grep -E _test$)" == "" ]
then
    echo "La base COUCHTEST ($COUCHTEST) ne semble pas être une base de test ( doit se terminer par \"_test\", par exemple http://localhost:5984/giilda_app_test )"
    exit;
fi

APPLICATION=$1

if [ ! $APPLICATION ]
then
    echo "Vous devez définir une application en argument :"
    echo ;
    echo "$0 <APPLICATION> (<NOM_TEST>) (unit|functional)";
    exit;
fi

NOM_TEST=$2
TYPE_TEST="unit"

if [ $3 ]
then
    TYPE_TEST=$3
fi

if [ $NOM_TEST ] && [ $TYPE_TEST == "unit" ]
then
    APPLICATION=$APPLICATION COUCHURL=$COUCHTEST php symfony test:unit $NOM_TEST --trace
    exit;
fi

if [ $NOM_TEST ] && [ $TYPE_TEST == "functional" ]
then
    APPLICATION=$APPLICATION COUCHURL=$COUCHTEST php symfony test:functional $APPLICATION $NOM_TEST --trace
    exit;
fi

if [ $NOM_TEST ]
then
    exit;
fi

curl -s -X DELETE $COUCHTEST
curl -s -X PUT $COUCHTEST  || ( echo "connexion à $COUCHTEST impossible"  ;  exit 2 )

cd ..
make clean
make couchurl=$COUCHTEST
cd -

ls $WORKINGDIR"/data/configuration/"$APPLICATION | while read jsonFile
do
    curl -s -X POST -d @data/configuration/$APPLICATION/$jsonFile -H "content-type: application/json" $COUCHTEST
done

rm -rf cache/*
php symfony cc

APPLICATION=$APPLICATION COUCHURL=$COUCHTEST NODELETE=1 php symfony test:all
