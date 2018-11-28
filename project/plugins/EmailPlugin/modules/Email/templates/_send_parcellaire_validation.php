Bonjour,

Votre déclaration d'<?php echo ($parcellaire->isIntentionCremant())? 'intention de production' : 'affectation parcellaire'; ?><?php if($parcellaire->isParcellaireCremant()): ?><?php if($parcellaire->isIntentionCremant()): ?> AOC Crémant d'Alsace<?php else: ?> Crémant<?php endif; ?><?php endif; ?> <?php echo $parcellaire->campagne; ?> a bien été validée et envoyée au service Appui technique de l'AVA.

Vous pouvez à tout moment revenir sur votre compte pour consulter ou imprimer votre document : <?php echo sfContext::getInstance()->getRouting()->generate('parcellaire_visualisation', $parcellaire,true); ?>

Vous trouverez votre document en pièce jointe de ce mail aux formats PDF et CSV.

Bien cordialement,

<?php echo include_partial('Email/footerMail'); ?>
