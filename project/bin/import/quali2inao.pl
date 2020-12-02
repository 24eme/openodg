#!/bin/perl

print "Produit;raison sociale de l'operateur;Nom ou Raison sociale de l'établissement;N CVI;N SIRET;Adresse du siege 1;Adresse du siege 2;Boite Postale;Adresse du siege 3;Code postal;Adresse du siege commune;Nom du responsable;Qualite du Responsable;Telephone;Portable;Telecopie;Email;Date depot DI;Date Enregistrement DI;Producteur de raisins;Producteur de mouts;Vinificateur;vide;Achat et vente de vins en vrac (entre operateurs);Elaborateur;vide;DISTILLATEUR;conditionneur;METTEUR_EN_MARCHE;ELEVEUR;PRESTATAIRE_DE_SERVICE\n";

while(<STDIN>) {
    @l = split(/;/);
    @d = [];
    $d[0] = $l[0];  #Produits
    $d[3] = $l[13]; #CVI
    $d[4] = $l[12]; #SIRET

    $d[17] = $l[5]; #Date enregistrement
    $d[18] = $l[6]; #date dépot
    $d[17] =~ s/\///g;#Format permettant le traitement plus rapide du tri des dates par un tableur
    $d[18] =~ s/\///g;#

    $d[19] = 'x' if ($l[3] =~ /A/); #Producteur de raison
    $d[20] = 'x' if ($l[3] =~ /P/); #Producteur de mouts
    $d[21] = 'x' if ($l[3] =~ /B/); #vinificateur
    $d[27] = 'x' if ($l[3] =~ /D/); #conditionneur
    $d[24] = 'x' if ($l[3] =~ /L/); #Elaboration
    $d[40] = '';
    print join(';', @d);
    print "\n";
}
