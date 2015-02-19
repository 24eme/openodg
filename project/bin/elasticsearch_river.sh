. bin/config.inc

echo "{
\"type\" : \"couchdb\",
\"couchdb\" : {
\"host\" : \"$COUCHDBDOMAIN\",
\"port\" : \"$COUCHDBPORT\",
\"db\" : \"$COUCHDBBASE\",
\"filter\" : \"app/type\",
\"filter_params\" : {
\"type\" : \"Compte\"
}
},
\"index\" : {
\"index\" : \"$ESINDEXGLOBAL\",
\"type\" : \"compte\",
\"bulk_size\" : \"100\",
\"bulk_timeout\" : \"10ms\"
}
}" > "$TMPDIR/esriver.compte.json"

curl -X PUT "http://$ESDOMAIN:$ESPORT/$ESINDEXRIVER/$ESINDEXTYPE/_meta" -d "@$TMPDIR/esriver.compte.json"