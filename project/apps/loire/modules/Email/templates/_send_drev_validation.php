Bonjour,

Votre déclaration de Revendication <?php echo $drev->campagne; ?> a bien été transmise à vos différents syndicats.

Cette validation sera définitive lorsque votre déclaration aura été vérifiée et que les éventuelles pièces à joindre seront parvenues à notre service.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>

<?php endif; ?>

Contacts de vos Syndicats :

<?php foreach ($drev->declaration->getSyndicats() as $syndicat) :
    $nom = (isset($odgs['odg'][$syndicat]['nom']))? $odgs['odg'][$syndicat]['nom'] : false;
    $adresse = (isset($odgs['odg'][$syndicat]['adresse']))? $odgs['odg'][$syndicat]['adresse'] : false;
    $email = (isset($odgs['odg'][$syndicat]['email']))? $odgs['odg'][$syndicat]['email'] : false;
    $telephone = (isset($odgs['odg'][$syndicat]['telephone']))? $odgs['odg'][$syndicat]['telephone'] : false;
?>
<?php if($nom): ?>
  - <?php echo $nom; ?>
<?php endif; ?>
<?php if($adresse): ?>
      <?php echo $adresse; ?>
<?php endif; ?>
<?php if($email): ?>
      Email : <?php echo $email; ?>
<?php endif; ?>
<?php if($telephone): ?>
      Téléphone : <?php echo $telephone; ?>
<?php endif; ?>

<?php endforeach; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>

Pour toute question, n'hésitez pas à contacter votre syndicat.

Bonne journée.

<?php echo include_partial('Email/footerMail'); ?>
