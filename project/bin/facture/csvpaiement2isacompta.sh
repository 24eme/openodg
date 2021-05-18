#!/bin/bash

PAIEMENT_CSV_FILE=$2

echo "date;identifiant analytique;journal;piece;raison sociale;libelle ligne;;quantite;debit;credit;;igp";
cat $PAIEMENT_CSV_FILE  | sed 's/\([0-9]*\)-\([0-9]*\)-\([0-9]*\);/\3\/\2\/\1;/' | awk -F ';' '{
	//compte client
	print $1";411"$3";22;"$4";"$2";"$7";;;;"$6";;";
	//depot
        print $1";58000000;22;"$4";"$2";"$7";;;"$6";;;";
}'
