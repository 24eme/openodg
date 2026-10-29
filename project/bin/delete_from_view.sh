. bin/config.inc

curl -s $1 | grep '"id":' | sed 's/.*"id":"//' | sed 's/".*//' | while read OBJ; do
	OBJREV=$(curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$OBJ | sed 's/{"_id":"//' | sed 's/","_rev":"/?rev=/' | sed 's/".*//')
    if test "$OBJREV" ; then
	   curl -s -X DELETE http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$OBJREV
   fi
done
