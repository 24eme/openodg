<?php
$app = strtoupper(sfConfig::get('sf_app'));
$infos = sfConfig::get('app_facture_emetteur');

$service = $infos[$app]['service_facturation'];
$email = $infos[$app]['email'];
$adresse = $infos[$app['adresse'];
$code_postal = $infos[$app['code_postal'];
$ville = $infos[$app['ville'];
?>
Bonjour,

Suite à la validation de votre déclaration vous n'avez pas envoyé tous les documents permettant de compléter votre Déclaration de Revendication.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents restant à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?><?php if($document->statut != DRevDocuments::STATUT_EN_ATTENTE): continue; endif; ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>

Le ou les document(s) annexes peuvent nous être envoyés par mail (<<?php echo $email; ?>>) ou par voie postale :

<?php echo $service; ?>
<?php echo $adresse; ?>
<?php echo $code_postal; ?> <?php echo $ville; ?>
<?php endif; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>

La validation définitive de votre déclaration de Revendication ne pourra se faire que lorsque nous aurons réceptionné la ou les pièces manquantes.

Une fois tous les documents réceptionnés, la DREV sera envoyée à l'organisme de contrôle.

Pour toute question, n'hésitez pas à contacter votre syndicat.

Bonne journée.

<?php echo include_partial('Email/footerMail'); ?>
