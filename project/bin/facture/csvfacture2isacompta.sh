#!/bin/bash

FACTURE_CSV_FILE=$1

#echo "date;identifiant analytique;journal;piece;raison sociale;libelle mouvement;;quantite;debit;credit;code tva;igp";
cat $FACTURE_CSV_FILE  | iconv -f iso88591 -t utf8 | tail -n +2 | sed 's/\([0-9]*\)-\([0-9]*\)-\([0-9]*\);/\3\/\2\/\1;/' | sed 's/ - N° / - /' | awk -F ';' '{
    tva = ""
    tva_prix = $17
    sub(",", ".", tva_prix) * 1.0
    if (tva_prix * 1 != 0.00) {
        tva = "r5"
    }
	if ($14 && $15) {
		//Credit ligne
		print $1";"$12";"71";"$11";"$4";"$13";0;"$15";;"$16";"tva";"$13;
	} else {
		//export debit client
        print $1";411"$3";"71";"$11";"$4";REV "$4";0;"$15";"$18";;;"$13;
        if (tva_prix * 1 != 0.00) {
			print $1";44571251;"71";"$11";"$4";TVA "$13";0;"$15";;"$17";"tva";"$13;
		}
	}
}' | iconv -f utf8 -t iso88591
