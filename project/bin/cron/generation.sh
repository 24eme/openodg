#!/bin/bash
. $(echo $0 | sed 's/[^\/]*$//')../config.inc

if test -e /tmp/cron_generation_pdf.$PROJET.pid ; then
exit 1;
fi

echo $$ > /tmp/cron_generation_pdf.$PROJET.pid

cd $WORKINGDIR

php -d memory_limit=512M symfony generation:generate

rm /tmp/cron_generation_pdf.$PROJET.pid
