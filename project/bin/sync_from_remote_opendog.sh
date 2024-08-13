#!/bin/bash

. bin/config.inc

if ! test "$SYMFONYTASKOPTIONS" ; then
    exit
fi

if ! test "$URL_EXPORT_REMOTE_OPENDOG"
then
    echo "Aucune instance distante d'openodg définie, set URL_EXPORT_REMOTE_OPENDOG in config_extra.inc"
    exit;
fi

php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/dr.csv $SYMFONYTASKOPTIONS
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/sv12.csv $SYMFONYTASKOPTIONS
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/sv11.csv $SYMFONYTASKOPTIONS
php symfony drev:import $URL_EXPORT_REMOTE_OPENDOG/drev.csv $SYMFONYTASKOPTIONS
