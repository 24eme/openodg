#!/bin/bash

APPLICATION=$2

if test -f "$(echo $0 | sed 's/[^\/]*$//')config_$APPLICATION.inc"; then
    . "$(echo $0 | sed 's/[^\/]*$//')config_$APPLICATION.inc"
else
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
fi



php "$WORKINGDIR/symfony" $SYMFONYTASKOPTIONS import:parcellaire-douanier "$1"
