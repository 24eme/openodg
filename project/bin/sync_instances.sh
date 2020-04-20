#!/bin/bash

. $(dirname $0)/config.inc

rsync -aO $WORKINGDIR"/web/generation/" $COUCHDISTANTHOST":"$WORKINGDIR"/web/generation"
rsync -aO $WORKINGDIR"/"$EXPORTDIR"/" $COUCHDISTANTHOST":"$WORKINGDIR"/"$EXPORTDIR
