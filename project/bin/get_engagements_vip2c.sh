#!/bin/bash
EXPORTGLOBALDIR=web/exports_igp
EXPORTIVSEDIR=web/exports_ivse

head -n 1 $EXPORTGLOBALDIR/engagements.csv > $EXPORTIVSEDIR/engagements_vip2c.new.csv
grep -a VIP $EXPORTGLOBALDIR/engagements.csv >> $EXPORTIVSEDIR/engagements_vip2c.new.csv

if [ -e $EXPORTIVSEDIR/engagements_vip2c.csv ]
then
    DIFF=$(diff $EXPORTIVSEDIR/engagements_vip2c.csv $EXPORTIVSEDIR/engagements_vip2c.new.csv)
    if [ "$DIFF" != "" ]
    then
        mv $EXPORTIVSEDIR/engagements_vip2c.new.csv $EXPORTIVSEDIR/engagements_vip2c.csv
    else
        rm $EXPORTIVSEDIR/engagements_vip2c.new.csv
    fi
else
    mv $EXPORTIVSEDIR/engagements_vip2c.new.csv $EXPORTIVSEDIR/engagements_vip2c.csv
fi
