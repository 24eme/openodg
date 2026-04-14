#!/bin/bash

. bin/config.inc

DOCTYPE=$1
ANNEE=$2

CAMPAGNE="$ANNEE-$(($ANNEE + 1))"

curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous?startkey=\[\"\",\"$DOCTYPE\",\"$CAMPAGNE\"\]&endkey=\[\"\",\"$DOCTYPE\",\"$CAMPAGNE\",\[\]\]&reduce=false" | grep "Approuvé" | cut -d "," -f 1 | grep id | sed 's/{"id":"//'  | sed 's/"//' | while read id; do
    ETABLISSEMENT_ID=$(echo -n $id | cut -d "-" -f 2);
    if ! "$(curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/ETABLISSEMENT-$ETABLISSEMENT_ID" | jq '.statut == "ACTIF"')"; then
        continue;
    fi

	NEWANNEE=$(($ANNEE + 1))
	NEWCAMPAGNE="$NEWANNEE-$(($NEWANNEE + 1))"
	NEWDOCID=$(echo -n $id | sed -r "s/$ANNEE$/$NEWANNEE/");

    echo "Copie du document $id"
    echo "Nouvelle campagne : $NEWCAMPAGNE"
    echo "Nouveau document : $NEWDOCID"

    if ! (curl -sX COPY "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$id" -H "Destination: $NEWDOCID" | grep '"rev":'); then
        echo "$NEWDOCID existe déjà"
        continue
    else
        php symfony document:setvalue $NEWDOCID campagne "$NEWCAMPAGNE" validation "$(date +%Y-%m-%d)" validation_odg "$(date +%Y-%m-%d)" signataire "Générée depuis la déclaration de $ANNEE" papier "AUTO" $SYMFONYTASKOPTIONS
    fi
done;
