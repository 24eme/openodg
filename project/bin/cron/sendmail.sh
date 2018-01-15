#!/bin/bash
. $(echo $0 | sed 's/[^\/]*$//')../config.inc

if test -e /tmp/sendmail.$PROJET.pid ; then
exit 1;
fi

echo $$ > /tmp/sendmail.$PROJET.pid

cd $WORKINGDIR

for i in {1..30}
do
    php symfony project:send-emails $SYMFONYTASKOPTIONS --message-limit=1
    sleep 1
done

rm /tmp/sendmail.$PROJET.pid
