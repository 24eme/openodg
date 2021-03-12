<?php use_helper('Date'); ?>
<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationConditionnementRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationConditionnementRegion(); ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $conditionnement->identifiant, 'campagne' => $conditionnement->campagne)); ?>"><?php echo $conditionnement->getEtablissementObject()->getNom() ?> (<?php echo $conditionnement->getEtablissementObject()->identifiant ?> - <?php echo $conditionnement->getEtablissementObject()->cvi ?>)</a></li>
  <li class="active"><a href="">Conditionnement du <?php echo format_date($conditionnement->getDate(), 'dd/MM/yyyy'); ?></a></li>
</ol>
