#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')updateviews.sh $app;
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

if test -e $TMPDIR/$COUCHBASE".updateviews.pid"; then
exit 2;
fi

echo $$ > $TMPDIR/$COUCHBASE".updateviews.pid"


curl -s "http://$COUCHHOST:$COUCHDBPORT/$COUCHBASE/_all_docs?startkey=%22_design%2F%22&endkey=%22_design0%22&include_docs=true" | sed -r 's/key.+views//' | cut -d "," -f 1,2 | cut -d ":" -f 2,3 | sed 's|","":{"|/_view/|' | sed 's/"//g' | grep "_design" | sed "s|^|curl -s http://$COUCHHOST:$COUCHDBPORT/$COUCHBASE/|" | bash > /dev/null

rm $TMPDIR/$COUCHBASE".updateviews.pid"
