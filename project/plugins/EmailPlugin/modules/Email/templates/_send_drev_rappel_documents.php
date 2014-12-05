Bonjour,

Suite à la validation de votre déclaration vous n'avez pas envoyer tous les documents permettant de compléter votre Déclaration de Revendication.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents restant à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?><?php if($document->statut != DRevDocuments::STATUT_EN_ATTENTE): continue; endif; ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>

Le ou les document(s) annexes peuvent nous être envoyés par mail (m.parisot@ava-aoc.fr) ou par voie postale :

Association des Viticulteurs d'Alsace
12 avenue de la Foire Aux Vins
BP 91 225
68012 Colmar Cedex
<?php endif; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>


Bien cordialement,

Le service Appui technique (via l'application de télédéclaration)