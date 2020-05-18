#!/bin/bash

. bin/config.inc

if ! test "$URL_EXPORT_REMOTE_OPENDOG"
then
    "Aucune instance distance d'openodg défini, set URL_EXPORT_REMOTE_OPENDOG in config.inc"

fi

php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/dr.csv $SYMFONYTASKOPTIONS
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/sv12.csv $SYMFONYTASKOPTIONS
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/sv11.csv $SYMFONYTASKOPTIONS
php symfony drev:import $URL_EXPORT_REMOTE_OPENDOG/drev.csv $SYMFONYTASKOPTIONS
