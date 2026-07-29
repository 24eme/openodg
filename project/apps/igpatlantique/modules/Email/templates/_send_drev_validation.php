Bonjour,

Votre déclaration de Revendication <?php echo $drev->campagne; ?> a bien été transmise au syndicat via le site syndicat-cotesdeprovence.com.

Cette validation sera définitive lorsque votre déclaration aura été vérifiée et que les éventuelles pièces à joindre seront parvenues à notre service.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>

Le ou les document(s) annexes peuvent nous être envoyés par mail (<<?php echo $email; ?>>) ou par voie postale :

<?php echo $service; ?>
<?php echo $adresse; ?>
<?php echo $code_postal; ?> <?php echo $ville; ?>
<?php endif; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>


Votre déclaration sera transmise à l’organisme de contrôle.

Pour toute question, n'hésitez pas à contacter votre syndicat.

Bonne journée.

<?php echo include_partial('Email/footerMail'); ?>
