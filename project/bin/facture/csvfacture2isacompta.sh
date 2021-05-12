#!/bin/bash

FACTURE_CSV_FILE=$2

echo "date;identifiant analytique;journal;piece;raison sociale;libelle ligne;;quantite;debit;credit;v4?;igp";
cat $FACTURE_CSV_FILE | awk -F ';' '{
	if ($14 && $15) {
		//Credit ligne
		print $1";"$12";"70";"$11";"$4";"$13";0;"$15";;"$16";v4;"$13;
		if ($17 * 1 > 0) {
			print $1";445710;"70";"$11";"$4";TVA "$13";0;"$15";;"$17";v4;"$13;
		}
	} else {
		//export debit client
                print $1";411"$3";"70";"$11";"$4";REV "$4";0;"$15";"$18";;;"$13;
	}
}'
