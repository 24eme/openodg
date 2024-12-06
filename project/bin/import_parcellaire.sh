#!/bin/bash

. $(echo $0 | sed 's/[^\/]*$//')config.inc

php "$WORKINGDIR/symfony" $SYMFONYTASKOPTIONS import:parcellaire-douanier "$1"
