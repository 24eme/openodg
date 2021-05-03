#!/bin/bash

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')../config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')../config_"$1".inc
fi

if test -e /tmp/cron_generation_pdf.$PROJET.pid ; then
exit 1;
fi

echo $$ > /tmp/cron_generation_pdf.$PROJET.pid

cd $WORKINGDIR

php symfony generation:generate $SYMFONYTASKOPTIONS

rm /tmp/cron_generation_pdf.$PROJET.pid
