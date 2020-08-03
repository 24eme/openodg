#!/bin/bash

cd $(dirname $0)/..

. bin/config.inc

DOC=$1
WAITSLEEP=$2
REGION=$3

if ! test "$DOC" ; then
	echo "USAGE: $0 DOC_TYPE";
	exit 1
fi

OPTIONS=""
if test "$REGION" ; then
	OPTIONS="--region=$REGION";
fi

header=1
curl -s http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE"/_design/declaration/_view/export?reduce=true&group_level=2" | awk -F '"' '{print $4 " " $6}' | grep "^$DOC " > /tmp/$$.docs
cat /tmp/$$.docs | while read doc ; do
	php symfony declarations:export-csv $SYMFONYTASKOPTIONS --header=$header $OPTIONS $doc
	header=0
	if test "$WAITSLEEP" ; then
		sleep $WAITSLEEP
	fi
done
rm /tmp/$$.docs
