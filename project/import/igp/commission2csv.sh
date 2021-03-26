DATADIR=$1

echo "date;code;campagne;millesime;responsable;lieu_nom;lieu_adresse;lieu_code_postal;lieu_ville;type_ligne;operateur;appellation;couleur;cepage;volume;logement;type_lot;passage;degre;doc;numero_anonymat;conformite;motif_refus;commentaire" > $DATADIR/commissions.csv
ls $DATADIR/commissions/*.html | while read file; do nodejs parse_commisson.js $file; done | sort -t ";" -k1.7,1.10 -k1.4,1.5 -k1.1,1.2 -k2,2 >> $DATADIR/commissions.csv