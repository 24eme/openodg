<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationDrevRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationDrevRegion(); ?></a></li>
  <?php endif; ?>
  <?php if($chgtDenom->exist('declarant')): ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $chgtDenom->identifiant)); ?>"><?php echo $chgtDenom->declarant->getNom() ?> (<?php echo $chgtDenom->identifiant ?> - <?php echo $chgtDenom->declarant->cvi ?>)</a></li>
  <li class="active"><a href="">
<?php if ($chgtDenom->isDeclassement()): ?>
      Déclassement
<?php else: ?>
      Changement de dénomination
<?php endif; ?>
<?php if ($chgtDenom->numero_archive): ?>
      n° <?php echo $chgtDenom->numero_archive; ?>
<?php endif; ?>
      du <?php echo $chgtDenom->getDateFormat(); ?>
  </a></li>
<?php else: ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $chgtDenom->identifiant)); ?>"><?php echo $chgtDenom->getNom() ?> (<?php echo $chgtDenom->identifiant ?> - <?php echo $chgtDenom->cvi ?>)</a></li>
  <li class="active"><a href="">Changement de dénomination (Historique des lots)</a></li>
<?php endif; ?>

</ol>
