<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>


<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<h2>Confirmation dégustation</h2>
<h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>

<?php include_partial('degustation/recap', array('degustation' => $degustation)); ?>


<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("degustation"); ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-4 text-center">
		<a href="#" class="btn btn-default"><span class="glyphicon glyphicon-file"></span>&nbsp;Etiquettes</a>
    </div>
    <div class="col-xs-1 text-right">
        <a class="btn btn-xs btn-default" href="<?php echo url_for('degustation_devalidation', $degustation) ?>" onclick="return confirm('Êtes-vous sûr de vouloir dévalider cette dégustation ?');"><span class="glyphicon glyphicon-remove-sign"></span>&nbsp;&nbsp;Dévalider</a>
	  </div>
    <div class="col-xs-3 text-right">
      <a class="btn btn-success" href="<?php echo url_for('degustation_visualisation', $degustation) ?>" >&nbsp;Visualisation et organisation&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
    </div>
</div>
