<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationDrevRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationDrevRegion(); ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $chgtDenom->identifiant, 'campagne' => $chgtDenom->campagne)); ?>"><?php echo $chgtDenom->getEtablissementObject()->getNom() ?> (<?php echo $chgtDenom->getEtablissementObject()->identifiant ?> - <?php echo $chgtDenom->getEtablissementObject()->cvi ?>)</a></li>
  <li class="active"><a href="">Changement de dénomination de <?php echo $chgtDenom->getDate(); ?></a></li>
</ol>
