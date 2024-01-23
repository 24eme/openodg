#!/bin/bash

cd $(dirname $0)/..

. bin/config.inc

curl 'http://127.0.0.1:9200/'$COUCHDBBASE'/_search' -X POST -H 'Content-Type: application/json'  --data-raw '{"query":{"bool":{"must":[{"match":{"doc.tags.activite":"producteur_de_raisins"}},{"match":{"doc.statut":"ACTIF"}},{"match_all":{}}],"must_not":[],"should":[]}},"from":0,"size":5000,"sort":[],"aggs":{}}' > /tmp/search.$$.json
cat /tmp/search.$$.json  | jq .hits[] | grep _id | awk -F '"' '{print $4}' | sort -u | sed 's/COMPTE-//'  | while read id ; do
    echo php symfony import:parcellaire-douanier $SYMFONYTASKOPTIONS $id ;
done  | sudo -u www-data bash
rm /tmp/search.$$.json
