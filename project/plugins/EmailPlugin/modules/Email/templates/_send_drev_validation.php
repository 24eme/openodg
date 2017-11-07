Bonjour,

Votre déclaration de Revendication <?php echo $drev->campagne; ?> a bien été transmise au syndicat via le site syndicat-cotesdurhone.com.

Cette validation sera définitive lorsque votre déclaration aura été vérifiée et que les éventuelles pièces à joindre seront parvenues à notre service.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>

Le ou les document(s) annexes peuvent être envoyés par mail (gestiondesdonnees@syndicat-cotesdurhone.com) ou par voie postale :

Syndicat Général des Côtes du Rhône
6 rue des trois faucons
CS 60093
84918 AVIGNON Cedex 9
<?php endif; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>

Votre déclaration sera transmise à l’organisme de contrôle.

Pour toute question, n'hésitez pas à contacter votre syndicat.

Bonne journée.

Le Syndicat Général des Vignerons réunis des Côtes du Rhône
Gestiondesdonnees@syndicat-cotesdurhone.com
