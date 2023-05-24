<?php use_helper('Date'); ?>
<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationPMCRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationPMCRegion(); ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $pmc->identifiant, 'campagne' => $pmc->campagne)); ?>"><?php echo $pmc->getEtablissementObject()->getNom() ?> (<?php echo $pmc->getEtablissementObject()->identifiant ?> - <?php echo $pmc->getEtablissementObject()->cvi ?>)</a></li>
  <li class="active"><a href="">Mise en circulation du <?php echo format_date($pmc->getDate(), 'dd/MM/yyyy'); ?></a></li>
</ol>
