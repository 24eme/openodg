<?php use_helper("Date") ?>
<ol class="breadcrumb">
  <?php if (!isset($degustation)): ?>
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <?php else: ?>
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li class="active"><a href=""><?php echo $degustation->getLieuNom(); ?>  le <?php echo ucfirst(format_datetime($degustation->date, "F H:i", "fr_FR")) ?></a></li>
  <?php endif; ?>
</ol>
