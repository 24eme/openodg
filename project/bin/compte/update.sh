#!/bin/bash

. bin/config.inc

EXPRESSION=$1

if ! test "$EXPRESSION"; then
    echo "Une expression est requise avec \\\$id pour utiliser le paramètre"
    exit;
fi


curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_all_docs?startkey_docid=COMPTE-&endkey_docid=COMPTE-ZZZZZZ | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' | grep -E "^COMPTE" | while read id  
do
    eval "$EXPRESSION"
done
