Bonjour,

Suite à la validation de votre déclaration vous n'avez pas envoyé tous les documents permettant de compléter votre Déclaration de Revendication.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents restant à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?><?php if($document->statut != DRevDocuments::STATUT_EN_ATTENTE): continue; endif; ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>
Le ou les document(s) annexes peuvent nous être envoyés par mail ou par voie postale.
<?php endif; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>

La validation définitive de votre déclaration de Revendication ne pourra se faire que lorsque nous aurons réceptionné la ou les pièces manquantes.

Une fois tous les documents réceptionnés, la DREV sera envoyée à l'organisme de contrôle.

Pour toute question, n'hésitez pas à contacter votre syndicat.

Bonne journée.

<?php echo include_partial('Email/footerMail'); ?>
