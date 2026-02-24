#!/bin/bash

. bin/config.inc

sed -i 's/ 20[23][0-9] / /' $EXPORTDIR/factures.csv
