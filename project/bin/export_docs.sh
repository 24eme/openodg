#!/bin/bash

cd $(dirname $0)/..

if test $3 && test -f $(echo $0 | sed 's/[^\/]*$//')config_"$3".inc ; then
    . $(echo $0 | sed 's/[^\/]*$//')config_"$3".inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
fi

DOC_TYPE=$1
WAITSLEEP=$2
REGION=$3

if ! test "$DOC_TYPE" ; then
	echo "USAGE: $0 DOC_TYPE_TYPE";
	exit 1
fi

OPTIONS=""
if test "$REGION" ; then
	OPTIONS="--region=$REGION";
fi

header=1
curl -s http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE"/_design/declaration/_view/export?reduce=true&group_level=2" | awk -F '"' '{print $4 " " $6}' | grep "^$DOC_TYPE " > /tmp/$$.docs
cat /tmp/$$.docs | while read doctype ; do
	php symfony declarations:export-csv $SYMFONYTASKOPTIONS --header=$header $OPTIONS $doctype
	header=0
	if test "$WAITSLEEP" ; then
		sleep $WAITSLEEP
	fi
done
rm /tmp/$$.docs
