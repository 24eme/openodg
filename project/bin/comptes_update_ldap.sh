#!/bin/bash

LOCK="/tmp/compte_update_ldap.lock"
SEQ="/tmp/compte_update_ldap.seq"

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')comptes_update_ldap.sh $app;
    done
    exit
fi
if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

if test -f $LOCK ; then
    exit 1
fi
touch $LOCK
if ! test -s $SEQ; then
    echo 0 > $SEQ
fi

curl -s "http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_changes?feed=continuous&timeout=590000&since="$(cat $SEQ | sed 's/[^0-9]//g') | grep "COMPTE" | while read ligne
do
    echo $ligne | awk -F '"' '{print $3}' | sed 's/[^0-9]//g' > $SEQ
    php symfony compte:ldap-update $SYMFONYTASKOPTIONS $(echo $ligne | awk -F '"' '{print $6}')
done

rm $LOCK
