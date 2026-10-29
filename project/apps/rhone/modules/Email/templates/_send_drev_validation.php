Bonjour,

Votre déclaration de Revendication <?php echo $drev->campagne; ?> a bien été transmise au syndicat via le site <?php echo sfConfig::get('app_routing_context_production_host'); ?>.

Cette validation sera définitive lorsque votre déclaration aura été vérifiée et que les éventuelles pièces à joindre seront parvenues à notre service.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>

Le ou les document(s) annexes peuvent être envoyés par mail (<<?php echo $organisme->getEmail(); ?>>) ou par voie postale :

Syndicat Général des Côtes du Rhône
<?php $organisme->getAdresse(); ?><br />
<?php echo $organisme->getCodePostal(); ?> <?php echo $organisme->getCommune() ?>
<?php endif; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>


Votre déclaration sera transmise à l’organisme de contrôle.

Pour toute question, n'hésitez pas à contacter votre syndicat.

Bonne journée.

<?php echo include_partial('Email/footerMail'); ?>
