<?php use_helper('Date'); ?>
<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationTransactionRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationTransactionRegion(); ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $transaction->identifiant, 'campagne' => $transaction->campagne)); ?>"><?php echo $transaction->getEtablissementObject()->getNom() ?> (<?php echo $transaction->getEtablissementObject()->identifiant ?> - <?php echo $transaction->getEtablissementObject()->cvi ?>)</a></li>
  <li class="active"><a href="">Vrac export du <?php echo format_date($transaction->getDate(), 'dd/MM/yyyy'); ?></a></li>
</ol>
