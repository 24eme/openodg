#!/bin/bash

. bin/config.inc
LOCK="/tmp/compte_update_ldap.lock"
SEQ="/tmp/compte_update_ldap.seq"
if test -f $LOCK ; then
    exit 1
fi
touch $LOCK
if ! grep '[0-9]' $SEQ > /dev/null ; then
    echo 0 > $SEQ
fi

curl -s "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_changes?feed=continuous&timeout=590000&since="$(cat $SEQ ) | grep "COMPTE" | while read ligne
do
    echo $ligne | sed 's/.*"seq":"//' | sed 's/".*//' > $SEQ
    php symfony compte:ldap-update $SYMFONYTASKOPTIONS $(echo $ligne | sed 's/.*"id":"//' | sed 's/".*//')
done

rm $LOCK
