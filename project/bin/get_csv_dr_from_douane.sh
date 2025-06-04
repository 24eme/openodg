#!/bin/bash

cd $(dirname $0)/.. > /dev/null 2>&1

. bin/config.inc

if ! test "$SYMFONYTASKOPTIONS"; then
    exit
fi

echo "type;année;id interne;cvi;raison sociale;;commune;tiers;tiers id;categorie;genre;denomination;mention;lieu;couleur;cepage;inao;libelle;denomination complementaire;ligne numero;ligne libelle;ligne valeur;acheteur id;acheteur raison sociale;;"
php symfony $SYMFONYTASKOPTIONS douaneRecolte:convert2csv  $1
