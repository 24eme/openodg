Bonjour,
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Nous avons bien reçu les éléments qui complètent votre déclaration de Revendication.
<?php endif; ?>

Après vérification, votre déclaration de Revendication <?php echo $drev->campagne; ?> a été définitivement validée par notre service.

Vous trouverez en pièce jointe le document PDF de votre déclaration.

Vous pouvez aussi à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>


Merci de bien vouloir nous signaler les éventuelles modifications apportées à votre déclaration de Récolte qui pourraient impacter votre déclaration de Revendication.

Bonne journée.

Le Syndicat Général des Vignerons réunis des Côtes du Rhône
Gestiondesdonnees@syndicat-cotesdurhone.com
