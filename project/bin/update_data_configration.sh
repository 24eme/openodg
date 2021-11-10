#!/bin/bash

. bin/config.inc

TMPFILE=/tmp/.update_data_configuration.$$

curl -s http://$COUCHDBDOMAIN:5984/_all_dbs | jq . | grep '_prod"' | awk -F '"' '{print $2}' | sed 's/_prod//' | while read instance ; do
    if ! test -d data/configuration/$instance ; then
        continue;
    fi
    find data/configuration/$instance -type f | sed 's|.*/||g' | sed 's/.json//' > $TMPFILE
    curl -s "http://"$COUCHDBDOMAIN":5984/"$instance"_prod/_all_docs?startkey=%22CONFIGURATION-%22&endkey=%22CONFIGURATION-%E9%A6%99%22" | jq .  | grep '"id"' | awk -F '"' '{print $4}' >> $TMPFILE
    curl -s "http://"$COUCHDBDOMAIN":5984/"$instance"_prod/_all_docs?startkey=%22TEMPLATE-%22&endkey=%22TEMPLATE-%E9%A6%99%22" | jq .  | grep '"id"' | awk -F '"' '{print $4}' >> $TMPFILE
    sort -u $TMPFILE | while read couchdbobj ; do
        curl -s http://$COUCHDBDOMAIN:5984/$instance"_prod/"$couchdbobj | jq . | sed '3d' > data/configuration/$instance/$couchdbobj".json"
    done
done
rm $TMPFILE