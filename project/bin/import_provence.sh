#!/bin/bash

. bin/config.inc

curl -X PUT http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE

cd ..
git pull
make clean
make
cd -

curl -X POST -d @data/configuration/provence/config.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/provence/current.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
