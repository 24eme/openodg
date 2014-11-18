<?php echo include_partial('Email/headerMail') ?>
Madame, Monsieur,

Votre déclaration de revendication <?php echo $drev->campagne; ?> a bien été validée et envoyée au service Appui technique de l'AVA.

Cette validation sera définitive lorsque votre déclaration aura été vérifiée et que les éventuelles pièces à joindre nous serons parvenues.

<?php if (count($drev->getOrAdd('documents')) > 0): ?>
Pour rappel :

<?php foreach($drev->getOrAdd('documents') as $document): ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?> (<?php echo DRevDocuments::getStatutLibelle($document->statut) ?>)
<?php endforeach; ?>

<?php endif; ?>
Bien cordialement,

Le service Appui technique (via l'application de télédéclaration)
<?php echo include_partial('Email/footerMail') ?>