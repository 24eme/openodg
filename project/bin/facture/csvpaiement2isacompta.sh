#!/bin/bash

PAIEMENT_CSV_FILE=$1

#echo "date;identifiant analytique;journal;piece;raison sociale;libelle ligne;;quantite;debit;credit;;igp";
cat $PAIEMENT_CSV_FILE | sed 's/_REV_/R/' | tail -n +2 | awk -F ';' '{
	//compte client
	print $5";411"$3";22;"$4";"$2";"$7";;;;"$6";;";
	//depot
    print $5";58000000;22;"$4";"$2";"$7";;;"$6";;;";
}' | grep -v '^;' | sed 's/\([0-9]*\)-\([0-9]*\)-\([0-9]*\);/\3\/\2\/\1;/'
