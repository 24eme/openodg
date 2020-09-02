<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_PRESENCE)); ?>


<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
    <h2>Présence à la dégustation</h2>
</div>

<form action="<?php echo url_for("degustation_presence", $degustation) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

	<?php include_partial('degustation/recap', array('degustation' => $degustation)); ?>


<div class="row row-margin row-button">
      <div class="col-xs-4"><a href="<?php echo url_for("degustation_selection_degustateurs", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
      <div class="col-xs-4 text-center">
      </div>
      <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider</button></div>
  </div>
</form>
</div>
