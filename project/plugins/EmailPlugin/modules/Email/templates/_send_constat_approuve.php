<?php use_helper('Date') ?>
Madame, Monsieur, 

Veuillez trouver ci-joint le document attestant du constat VT/SGN portant sur le <?php echo $constat->produit_libelle; ?> de <?php echo $constat->produit_libelle; echo ($constat->denomination_lieu_dit)? " (".$constat->denomination_lieu_dit.")" : ''; ?> effectué le <?php echo ucfirst(format_date($constat->date_signature, "P", "fr_FR")); ?> sur votre exploitation. 

En vous souhaitant bonne réception. 

Cordialement,

Vicky CHAN FOOK TIN