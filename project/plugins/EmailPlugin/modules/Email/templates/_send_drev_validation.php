<?php echo include_partial('Email/headerMail') ?>
Madame, Monsieur,<br /><br />
Votre déclaration de revendication 2014 a bien été validée et envoyée au service Appui technique de l'AVA.<br />
Cette validation sera définitive lorsque votre déclaration aura été vérifiée et que les éventuelles pièces à joindre nous serons parvenues.<br /><br />
<?php if (count($drev->getOrAdd('documents')) > 0): ?>
Pour rappel :<br />
<ul>
<?php foreach($drev->getOrAdd('documents') as $document): ?>
	<li><?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?> (<?php echo DRevDocuments::getStatutLibelle($document->statut) ?>)</li>
<?php endforeach; ?>
</ul><br />
<?php endif; ?>
Bien cordialement,<br /><br />
Le service Appui technique (via l'application de télédéclaration)
<?php echo include_partial('Email/footerMail') ?>