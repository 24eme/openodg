<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_DEGUSTATEURS)); ?>

<div class="page-header no-border">
    <h2>Confirmation des lots et dégustateurs <small class="text-muted">Campagne <?php echo $degustation->campagne; ?></small></h2>
</div>

<?php if($validation->hasPoints()): ?>
    <?php include_partial('degustation/pointsAttentions', array('degustation' => $degustation, 'validation' => $validation)); ?>
<?php endif; ?>

<p>Vérifier ici les lots et les dégustateurs selectionnés</p>
<form action="<?php echo url_for("degustation_validation", $degustation) ?>" method="post" class="form-horizontal" onsubmit="return confirm('Etes-vous sûr de Confirmer ces lots à déguster ? ');">
	<?php echo $form->renderHiddenFields(); ?>
    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

	<?php include_partial('degustation/recap', array('degustation' => $degustation)); ?>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation_selection_degustateurs", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-success btn-upper">Confirmer cette dégustation</button></div>
    </div>
</form>
