<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php use_javascript('degustation.js?'.$_ENV['GIT_LAST_COMMIT']); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Sélection des lots <small class="text-muted">Campagne <?php echo $degustation->campagne; ?></small></h2>
</div>
<div class="alert alert-info" role="alert">
  <table class="table table-condensed">
    <tbody>
      <tr class="vertical-center">
        <td class="col-xs-3" >Nombre total de <strong>lots à prélever&nbsp;:</strong></td>
        <td class="col-xs-9"><strong id="nbLotsSelectionnes"><?php echo $infosDegustation["nbLots"]; ?></strong></td>
      </tr>
      <tr class="vertical-center">
        <td class="col-xs-3" >Nombre total <strong>d'adhérents à prélever&nbsp;:</strong></td>
        <td class="col-xs-9"><strong id="nbAdherentsAPrelever"><?php echo $infosDegustation["nbAdherents"]; ?></strong></td>
      </tr>
    </tbody>
  </table>
</div>

<p>Sélectionnez l'ensemble des lots à prélever pour la dégustation</p>
<form action="<?php echo url_for("degustation_selection_lots", $degustation) ?>" method="post" class="form-horizontal degustation prelevements selectionlots">
	<?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <?php include_partial('degustation/tableSelectionLots', ['degustation' => $degustation, 'form' => $form]); ?>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("degustation") ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
