#!/bin/bash
#
# Migration des auteurs :
#     ls web/statuts/xml/* | grep -v '\-' | while read file ; do LASTCOMMIT=$(echo $file | awk -F '_' '{print $3}') ; AUTHOR=$(git show $LASTCOMMIT | head -n 5 | grep "^Author:" | sed 's/.*: //' | sed 's/ <.*//' | sed 's/[^a-z]//ig') ; if test "$AUTHOR" ; then mv $file $(echo $file | awk -F '_' '{print $1"_"$2"_"$3"-'$AUTHOR'_"$4}' ); fi ; done

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
AUTHOR=$(git show $LASTCOMMIT | head -n 5 | grep "^Author:" | sed 's/.*: //' | sed 's/ <.*//' | sed 's/[^a-z]//ig')

if [ "$( ls $XMLTESTDIR | grep $LASTCOMMIT | grep $APPLICATION"" )" != "" ] && [ "$FORCE" = "" ]
then
    echo "Test déjà effectué sur le commit $LASTCOMMIT"
    rm $PID_PATH
    exit;
fi

XMLFILE=$XMLTESTDIR/"$DATE"_"$APPLICATION"_"$LASTCOMMIT"-"$AUTHOR"_"$BRANCH".xml

bash $(dirname $0)/run_test.sh -x $XMLFILE $APPLICATION

sed -i "s|$WORKINGDIR/||" $XMLFILE

echo "Output XML file : $XMLFILE"

rm $PID_PATH
