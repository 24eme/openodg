#!/bin/bash

. $(echo $0 | sed 's/[^\/]*$//')config.inc

APPLICATION=$1
FORCE=$2

if [ ! $APPLICATION ]
then
    echo "Vous devez définir une application en argument :"
    echo ;
    echo "$0 <APPLICATION> [FORCE]";
    exit;
fi

PID_PATH=/tmp/$APPLICATION".integrationcontinue.pid"

if test -e $PID_PATH; then
    echo "Une instance tourne déjà $PID_PATH"
exit 2;
fi

echo $$ > $PID_PATH

if ! test "$WORKINGDIR"; then
    WORKINGDIR=$(dirname $0)"/../"
fi

mkdir -p $XMLTESTDIR 2> /dev/null

BRANCH=$(cat ../.git/HEAD | sed -r 's|^ref: refs/heads/||')
LASTCOMMIT=$(cat $WORKINGDIR"/../.git/refs/heads/"$BRANCH)
DATE=$(date +%Y%m%d%H%M%S)
BRANCH=$(echo $BRANCH | tr '/' '-')

if [ "$( ls $XMLTESTDIR | grep $LASTCOMMIT | grep $APPLICATION"" )" != "" ] && [ "$FORCE" = "" ]
then
    echo "Test déjà effectué sur le commit $LASTCOMMIT"
    rm $PID_PATH
    exit;
fi

XMLFILE=$XMLTESTDIR/"$DATE"_"$APPLICATION"_"$LASTCOMMIT"_"$BRANCH".xml

bash $(dirname $0)/run_test.sh -x $XMLFILE $APPLICATION

sed -i "s|$WORKINGDIR/||" $XMLFILE

echo "Output XML file : $XMLFILE"

rm $PID_PATH
