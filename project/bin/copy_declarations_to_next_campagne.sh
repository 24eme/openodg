#!/bin/bash

. bin/config.inc

DOCTYPE=$1
CAMPAGNE=$2

curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous?startkey=\[\"$DOCTYPE\",\"$CAMPAGNE\"\]&endkey=\[\"$DOCTYPE\",\"$CAMPAGNE\",\[\]\]&reduce=false" | cut -d "," -f 1 | grep id | sed 's/{"id":"//'  | sed 's/"//' | while read id; do 
	NEWCAMPAGNE=$(($CAMPAGNE + 1)) 
	NEWDOCID=$(echo -n $id | sed -r "s/$CAMPAGNE$/$NEWCAMPAGNE/");
	curl -sX COPY "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$id" -H "Destination: $NEWDOCID" | grep '"rev":' || continue;
	php symfony document:setvalue $NEWDOCID campagne "$NEWCAMPAGNE" validation "$(date +%Y-%m-%d)" validation_odg "$(date +%Y-%m-%d)" signataire "Générée depuis la déclaration de $(($NEWCAMPAGNE - 1))" papier +1 $SYMFONYTASKOPTIONS
done;

