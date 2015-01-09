#!/bin/bash

. bin/config.inc

#creation du filtre couchdb

echo '{
"_id": "_design/app",
"filters": {
"type": "function(doc, req) { if(doc.type == req.query.type) { return true; } if(doc._id.replace(/-.*/, '"''"') == req.query.type.toUpperCase()) { return true; } return false;}"
}
}
' > "$TMPDIR/filter.json"

curl -s -X PUT -d "@$TMPDIR/filter.json" "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/app" > /dev/null

#creation des index

curl -X DELETE "http://$ESDOMAIN:$ESPORT/$ESINDEXRIVER/"

curl -X DELETE "http://$ESDOMAIN:$ESPORT/$ESINDEXGLOBAL/"

curl -X PUT "http://$ESDOMAIN:$ESPORT/$ESINDEXRIVER/" -d '{
    "settings" : {
        "number_of_shards" : 5,
        "number_of_replicas" : 0
    }
}'

curl -X PUT "http://$ESDOMAIN:$ESPORT/$ESINDEXGLOBAL/" -d '{
    "settings" : {
        "number_of_shards" : 5,
        "number_of_replicas" : 0,
        "index":{
          "analysis":{
            "analyzer":{
              "francais":{
                "type":"custom",
                "tokenizer":"standard",
                "filter":["lowercase", "fr_stemmer", "stop_francais", "asciifolding", "elision"]
              }
            },
            "filter":{
              "stop_francais":{
                "type":"stop",
                "stopwords":["_french_"]
              },
              "fr_stemmer" : {
                "type" : "stemmer",
                "name" : "french"
              },
              "elision" : {
                "type" : "elision",
                "articles" : ["l", "m", "t", "qu", "n", "s", "j", "d"]
              }
            }
          }
        } 
    },
    "mappings" : {
        "compte" : {
            "_all" : { "analyzer":"francais" },
            "properties" : {
                "tags" : { 
                    "properties" : {
                        "automatiques" : {
                            "type" : "multi_field",
                            "fields" : {
                                "automatiques": { "type": "string", "index" : "analyzed", "analyzer": "keyword" },
                                "untouched": {"type": "string", "index": "not_analyzed"}
                            }
                        },
                        "attributs" : {
                            "type" : "multi_field",
                            "fields" : {
                                "attributs": { "type": "string", "index" : "analyzed", "analyzer": "keyword" },
                                "untouched": {"type": "string", "index": "not_analyzed"}
                            }
                        },
                        "produits" : {
                            "type" : "multi_field",
                            "fields" : {
                                "produits": { "type": "string", "index" : "analyzed", "analyzer": "keyword" },
                                "untouched": {"type": "string", "index": "not_analyzed"}
                            }
                        },
                        "manuels" : {
                            "type" : "multi_field",
                            "fields" : {
                                "manuels": { "type": "string", "index" : "analyzed", "analyzer": "keyword" },
                                "untouched": {"type": "string", "index": "not_analyzed"}
                            }
                        },
                        "syndicats" : {
                            "type" : "multi_field",
                            "fields" : {
                                "syndicats": { "type": "string", "index" : "analyzed", "analyzer": "keyword" },
                                "untouched": {"type": "string", "index": "not_analyzed"}
                            }
                        }
                    }
                }
            }
        }
    }
}'

#creation de la river

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

curl -X PUT "http://$ESDOMAIN:$ESPORT/$ESINDEXRIVER/compte/_meta" -d "@$TMPDIR/esriver.compte.json"
