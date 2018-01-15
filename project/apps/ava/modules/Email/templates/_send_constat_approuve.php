<?php use_helper('Date') ?>
<?php use_helper('Orthographe') ?>
Madame, Monsieur, 

Veuillez trouver ci-joint le document attestant du constat VT/SGN portant sur <?php echo elision("le", $constat->produit_libelle); ?><?php echo ($constat->denomination_lieu_dit)? " (".$constat->denomination_lieu_dit.")" : ''; ?> effectué le <?php echo ucfirst(format_date($constat->date_signature, "P", "fr_FR")); ?> sur votre exploitation. 

En vous souhaitant bonne réception. 

Cordialement,

Vicky CHAN FOOK TIN