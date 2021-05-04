#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')../config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//')../ | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        echo "running generation $app :"
        bash $(echo $0 | sed 's/[^\/]*$//')generation.sh $app
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')../config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')../config_"$1".inc
fi

LOCK_FILE=/tmp/cron_generation_pdf.$COUCHBASE.pid

if test -e $LOCK_FILE ; then
exit 1;
fi

echo $$ > $LOCK_FILE

cd $WORKINGDIR

php symfony generation:generate $SYMFONYTASKOPTIONS

rm $LOCK_FILE
