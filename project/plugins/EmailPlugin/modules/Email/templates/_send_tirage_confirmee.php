Bonjour,

Après vérification, votre déclaration de tirage de crémant d'Alsace <?php echo $tirage->campagne; ?> a été définitivement validée par notre service.

Vous trouverez en pièce jointe le document PDF de votre déclaration.

Vous pouvez aussi à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('tirage_visualisation', $tirage,true); ?>


Bien Cordialement,

<?php echo include_partial('Email/footerMail'); ?>
