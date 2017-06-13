<?php use_helper("Date") ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li class="active"><a href="">Tournée <?php echo $tournee->getLibelle(); ?>  le <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></a></li>
</ol>
