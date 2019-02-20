Bonjour,

Votre déclaration de tirage de Crémant d'Alsace <?php echo $tirage->campagne; ?> a bien été enregistré à l'AVA.

Vous pouvez à tout moment revenir sur votre compte pour consulter ou imprimer votre document : <?php echo sfContext::getInstance()->getRouting()->generate('tirage_visualisation', $tirage,true); ?>


Bien cordialement,

<?php echo include_partial('Email/footerMail'); ?>
