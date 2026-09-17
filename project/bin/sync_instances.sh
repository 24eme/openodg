#!/bin/bash

# Mode multi app
if ! test -f "$(echo "$0" | sed 's/[^\/]*$//')config.inc" && ! test "$1" ; then
    find . "$( dirname -- "$0" )" -maxdepth 1 -name "config_*.inc" -not -path "*config_extra.inc" -exec basename {} \; | while read -r app; do
        bash "$( dirname -- "$0" )/sync_instances.sh" "${app:7:-4}" # suppr substring offset 7 (config_) et length -4 (.inc)
    done

    exit 0
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

rsync -aO $WORKINGDIR"/web/generation/" $COUCHDISTANTHOST":"$WORKINGDIR"/web/generation"
rsync -aO $WORKINGDIR"/"$EXPORTDIR"/" $COUCHDISTANTHOST":"$WORKINGDIR"/"$EXPORTDIR
if test "$EXTRA_SYNC" && test -d "$EXTRA_SYNC" ; then
    rsync -aO $EXTRA_SYNC"/" $COUCHDISTANTHOST":"$EXTRA_SYNC
fi
