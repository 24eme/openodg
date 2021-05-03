#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')reset_replication.sh $app;
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

if ! test "$REPLICATIONDOC"; then
    echo "Le doc de replication n'est pas spécifié"
    exit;
fi

rev=$(curl -s http://$COUCHHOST:$COUCHPORT/_replicator/$REPLICATIONDOC | sed 's/.*rev":"//' | sed 's/".*//' )
curl -s -X DELETE http://$COUCHHOST:$COUCHPORT/_replicator/$REPLICATIONDOC?rev=$rev  > /dev/null
curl -s -X PUT -d '{"_id":"$REPLICATIONDOC","target":"'http://$COUCHHOST:$COUCHPORT/$COUCHBASE'","source":"'http://$COUCHDISTANTHOST:$COUCHPORT/$COUCHBASE'","continuous":true,"user_ctx": {"roles": ["_admin"]}}' http://$COUCHHOST:$COUCHPORT/_replicator/$REPLICATIONDOC  > /dev/null
