#!/bin/bash
EXPORTGLOBALDIR=web/exports_igp
EXPORTIVSEDIR=web/exports_ivse

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
