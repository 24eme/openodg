<?php use_helper('Date') ?>
Bonjour,

Vos vins ont été dégustés lors de la dégustation conseil du <?php echo ucfirst(format_date($degustation->date_degustation, "P", "fr_FR")) ?>.

Vous trouverez en pièce jointe les résultats de cette dégustation.

Bien cordialement,

<?php echo include_partial('Email/footerMail'); ?>
