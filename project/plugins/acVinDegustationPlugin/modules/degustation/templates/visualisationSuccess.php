<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>

<div class="page-header no-border">
  <h2>Dégustation <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h2>
</div>
<div class="row row-condensed">
</div>
