#!/bin/bash
EXPORTGLOBALDIR=web/exports_igp
EXPORTIVSEDIR=web/exports_ivse

# Génération tableau suivi vip2c
VIP2C_LOTS=$EXPORTGLOBALDIR/lots.csv
VIP2C_DREV_LOTS=$EXPORTGLOBALDIR/drev_lots.csv
VIP2C_ETABLISSEMENTS=$EXPORTGLOBALDIR/etablissements.csv

php bin/export_vip2c.php $VIP2C_DREV_LOTS $VIP2C_LOTS $VIP2C_ETABLISSEMENTS > $EXPORTIVSEDIR/liste_vip2c.csv

head -n 1 $EXPORTGLOBALDIR/engagements.csv > $EXPORTIVSEDIR/engagements_vip2c.new.csv
grep -a VIP2C $EXPORTGLOBALDIR/engagements.csv >> $EXPORTIVSEDIR/engagements_vip2c.new.csv

if [ -e $EXPORTIVSEDIR/engagements_vip2c.csv ]
then
    DIFF=$(diff $EXPORTIVSEDIR/engagements_vip2c.csv $EXPORTIVSEDIR/engagements_vip2c.new.csv)
    if ! test "$DIFF" != "" ;
    then
        rm $EXPORTIVSEDIR/engagements_vip2c.new.csv
        exit 0
    fi
fi
mv $EXPORTIVSEDIR/engagements_vip2c.new.csv $EXPORTIVSEDIR/engagements_vip2c.csv
