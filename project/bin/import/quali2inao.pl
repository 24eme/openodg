while(<STDIN>) {
    @l = split(/;/);
    @d = [];
    $d[0] = $l[0];  #Produits
    $d[3] = $l[13]; #CVI
    $d[4] = $l[12]; #SIRET
    $d[17] = $l[5]; #Date enregistrement
    $d[18] = $l[6]; #date dépot
    $d[19] = 'x' if ($l[3] =~ /A/); #Producteur de raison
    $d[20] = 'x' if ($l[3] =~ /P/); #Producteur de mouts
    $d[21] = 'x' if ($l[3] =~ /B/); #vinificateur
    $d[27] = 'x' if ($l[3] =~ /D/); #conditionneur
    $d[29] = 'x' if ($l[3] =~ /L/); #Elaboration
    $d[40] = '';
    print join(';', @d);
    print "\n";
}
