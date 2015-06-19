Type de déclaration;Campagne;Identifiant (CVI);Raison sociale;Email;Etape;Date de validation;Date de validation par l'ODG;Nombre de pièces jointes manquantes;Mode de déclaration;Id du document
<?php foreach ($lists[$current_key_list]['statuts'][$statut] as $doc): ?>
<?php echo $doc->key[0] ?>;<?php echo $doc->key[1] ?>;<?php echo $doc->key[5] ?>;"<?php echo $doc->key[8] ?>";"<?php echo $doc->key[9] ?>";<?php if(!$doc->key[2]): ?><?php echo $doc->key[4] ?><?php endif; ?>;<?php echo $doc->key[2] ?>;<?php echo $doc->key[3] ?>;<?php echo $doc->key[6] ?>;<?php if($doc->key[7]): ?>PAPIER<?php else: ?>TÉLÉDÉCLARÉ<?php endif; ?>;<?php echo $doc->id ?>

<?php endforeach; ?>