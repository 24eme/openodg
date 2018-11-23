Bonjour,

Nous sommes toujours en attente du/des document(s) permettant de compléter votre déclaration de Revendication dématérialisée.

Suivant ce qui est notifié dans le cahier des charges, nous vous rappelons que votre déclaration de Revendication doit nous parvenir « au minimum 15 jours avant la première sortie de produits du chai de vinification et au plus tard 60 jours après la date de mise en marché à destination du consommateur », soit le 15 février.

Nous vous encourageons à nous faire parvenir au plus vite le(s) élément(s) manquant(s), afin que nous puissions valider définitivement votre déclaration.
<?php if (count($drev->getOrAdd('documents')) > 0): ?>

Rappel des documents restant à envoyer :

<?php foreach($drev->getOrAdd('documents') as $document): ?><?php if($document->statut != DRevDocuments::STATUT_EN_ATTENTE): continue; endif; ?>
- <?php echo DRevDocuments::getDocumentLibelle($document->getKey()) ?>

<?php endforeach; ?>
Le ou les document(s) annexes peuvent nous être envoyés par mail (<<?php echo sfConfig::get('app_email_plugin_reply_to_adresse'); ?>>) ou par voie postale.
<?php endif; ?>

Vous pouvez à tout moment revenir sur votre compte pour consulter votre document : <?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>


La validation définitive de votre déclaration de Revendication ne pourra se faire que lorsque nous aurons réceptionné la ou les pièces manquantes.

Bien cordialement,

<?php echo include_partial('Email/footerMail'); ?>
