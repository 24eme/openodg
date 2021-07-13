<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationDrevRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationDrevRegion(); ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $drev->identifiant, 'campagne' => $drev->campagne)); ?>"><?php echo $drev->getEtablissementObject()->getNom() ?> (<?php echo $drev->getEtablissementObject()->identifiant ?> - <?php echo $drev->getEtablissementObject()->cvi ?>)</a></li>
  <li class="active"><a href="">DRev de <?php echo $drev->getperiode(); ?></a></li>
</ol>
