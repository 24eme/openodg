<?php use_helper('Degustation') ?>
<?php use_helper('Date') ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li class="active"><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->getIdentifiant() ?>)</a></li>
</ol>

DEGUSTATIONS