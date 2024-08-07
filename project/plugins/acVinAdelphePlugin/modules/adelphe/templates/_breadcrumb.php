<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getRegion(); ?></a></li>
  <?php endif; ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $adelphe->identifiant, 'campagne' => $adelphe->campagne)); ?>"><?php echo $adelphe->declarant->nom ?> (<?php echo $adelphe->identifiant ?> - <?php echo $adelphe->declarant->cvi ?>)</a></li>
  <li class="active"><a href="">Adelphe <?php echo $adelphe->getPeriode(); ?></a></li>
</ol>
