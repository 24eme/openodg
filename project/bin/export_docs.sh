#!/bin/bash

cd $(dirname $0)/..

. bin/config.inc

DOC=$1

if ! test "$DOC" ; then
	echo "USAGE: $0 DOC_TYPE";
	exit 1
fi

header=1
curl -s http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE"/_design/declaration/_view/export?reduce=false&startkey_docid="$DOC"-&endkey_docid="$DOC"-ZZZZZZ" | awk -F '"' '{print $4}' | grep $DOC | while read doc ; do
	php symfony declaration:export-csv $SYMFONYTASKOPTIONS --header=$header $doc
	header=0
done
