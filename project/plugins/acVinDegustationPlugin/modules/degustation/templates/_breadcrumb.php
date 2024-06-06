<?php use_helper("Date") ?>
<ol class="breadcrumb hidden-print">

  <li <?php if (!isset($degustation)): ?>class="active"<?php endif; ?>><a href="<?php echo url_for('degustation'); ?>"><?php if(isset($degustation) && $degustation->type == TourneeClient::TYPE_MODEL): ?>Tournée<?php else: ?>Dégustation<?php endif ?></a></li>
  <?php if(isset($degustation)): ?>
  <li <?php echo (isset($options))? 'class="active"' : ''; ?> ><a href="<?php echo url_for('degustation_visualisation', $degustation); ?>">
      <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")); if ($degustation->type == DegustationClient::TYPE_MODEL) { echo " à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm"); } ?>&nbsp;<small><?php echo $degustation->getLieuNom(); ?></small></a></li>
  <?php if (isset($options)): ?>
    <li class="active"><a href=""><?php echo $options['nom']; ?></a></li>
  <?php endif; ?>
  <?php endif; ?>
</ol>
