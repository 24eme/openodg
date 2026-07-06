<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getRegion(); ?></a></li>
  <?php endif; ?>
  <?php if($prisedemousse->exist('declarant')): ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $prisedemousse->identifiant)); ?>"><?php echo $prisedemousse->declarant->getNom() ?> (<?php echo $prisedemousse->identifiant ?> - <?php echo $prisedemousse->declarant->cvi ?>)</a></li>
  <li class="active"><a href="">
      Prise de Mousse
<?php if ($prisedemousse->numero_archive): ?>
      n° <?php echo $prisedemousse->numero_archive; ?>
<?php endif; ?>
      du <?php echo $prisedemousse->getDateFormat(); ?>
  </a></li>
<?php else: ?>
  <li><a href="<?php echo url_for('declaration_etablissement', array('identifiant' => $prisedemousse->identifiant)); ?>"><?php echo $prisedemousse->getNom() ?> (<?php echo $prisedemousse->identifiant ?> - <?php echo $prisedemousse->cvi ?>)</a></li>
  <li class="active"><a href="">Prise de mousse</a></li>
<?php endif; ?>

</ol>
