Bonjour,

Votre déclaration d'affectation parcellaire<?php echo ($parcellaire->isParcellaireCremant())? ' crémant' : ''; ?> <?php echo $parcellaire->campagne; ?> a bien été validée et envoyée au service Appui technique de l'AVA.

Vous pouvez à tout moment revenir sur votre compte pour consulter ou imprimer votre document : <?php echo sfContext::getInstance()->getRouting()->generate('parcellaire_visualisation', $parcellaire,true); ?>

Vous trouverez votre document en pièce jointe de ce mail aux formats PDF et CSV.

Bien cordialement,

Le service Appui technique (via l'application de télédéclaration)