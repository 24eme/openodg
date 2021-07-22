#!/bin/bash

. bin/config.inc

doc=$1
url=$doc
if ! echo $url | grep 'http://' > /dev/null; then
    url="http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$doc"
else
    doc=$(echo $doc | sed 's|http://[^/]*/[^/]*/||')
fi

i=0
mkdir  -p couchdbdoc2git && cd couchdbdoc2git && git init .
curl "$url?open_revs=all&revs=true" | grep '^\{' | jq ._revisions.ids  | tac | grep ' "' | awk -F '"' '{print $2}'  | while read rev ; do
	i=$(( $i + 1 )) ;
	echo $i"-"$rev ;
	curl -s $url"?rev="$i"-"$rev | jq . > $doc".json" ;
	git add $doc".json" ;
	git commit -m "version "$i"-"$rev ;
done

git log -p $doc".json"
