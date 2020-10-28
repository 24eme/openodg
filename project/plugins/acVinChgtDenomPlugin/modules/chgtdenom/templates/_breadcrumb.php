<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationDrevRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationDrevRegion(); ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $chgtDenom->identifiant)); ?>"><?php echo $chgtDenom->declarant->getNom() ?> (<?php echo $chgtDenom->identifiant ?> - <?php echo $chgtDenom->declarant->cvi ?>)</a></li>
  <li class="active"><a href="">Changement de dénomination du <?php echo date('Y-m-d'); ?></a></li>
</ol>
