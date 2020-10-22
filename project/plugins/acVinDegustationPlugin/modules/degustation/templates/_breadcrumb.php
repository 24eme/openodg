<?php use_helper("Date") ?>
<ol class="breadcrumb">
  <?php if (!isset($degustation)): ?>
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <?php else: ?>
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li <?php echo (isset($options))? 'class="active"' : ''; ?> ><a href="<?php echo url_for('degustation_visualisation', $degustation); ?>"><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?>&nbsp;<small><?php echo $degustation->getLieuNom(); ?></small></a></li>
  <?php if (isset($options)): ?>
    <li class="active"><a href=""><?php echo $options['nom']; ?></a></li>
  <?php endif; ?>
  <?php endif; ?>
</ol>
