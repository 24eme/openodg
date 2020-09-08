<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>


<form action="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id)) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>


    <table class="table table-bordered table-condensed table-striped">
    <thead>
          <tr>
            <th class="col-xs-3">Ressortissant</th>
            <th class="col-xs-1">Lot</th>
            <th class="col-xs-3">Produit (millésime)</th>
            <th class="col-xs-1">Volume</th>
            <th class="col-xs-2">Destination (date)</th>
            <th class="col-xs-2">Tables</th>
          </tr>
    </thead>
    <tbody>
    <?php
      foreach ($degustation->getLots() as $lot):
      $name = $form->getWidgetNameFromLot($lot);
      if (isset($form[$name])):
    ?>
      <tr class="vertical-center cursor-pointer" <?php if($lot): ?>disabled="disabled"<?php endif; ?>>
        <td><?php echo $lot->declarant_nom; ?></td>
        <td><?php echo $lot->numero; ?></td>
        <td><?php echo $lot->produit_libelle; ?><?php if ($lot->millesime): ?>&nbsp;(<?php echo $lot->millesime; ?>)<?php endif; ?></td>
        <td class="text-right"><?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small></td>
        <td><?php echo MouvementLotView::getDestinationLibelle($lot); ?><?php if ($lot->destination_date): ?>&nbsp;(<?php echo ucfirst(format_date($lot->destination_date, "dd/MM/yyyy", "fr_FR")); ?>)<?php endif; ?></td>
              <td class="text-center">
                  <div style="margin-bottom: 0;" class="form-group <?php if($form[$name]->hasError()): ?>has-error<?php endif; ?>">
                      <?php echo $form[$name]->renderError() ?>
                        <div class="col-xs-12">
                    <?php echo $form[$name]->render(array("class" => "form-control select2", "placeholder" => "Séléctionner une table")); ?>
                        </div>
                    </div>
              </td>
            </tr>
        <?php  endif; ?>
      <?php endforeach; ?>
      </tbody>
    </table>

<div class="row row-margin row-button">
      <div class="col-xs-4"><a href="<?php echo url_for("degustation_validation", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
      <div class="col-xs-4 text-center">
				<button class="btn btn-sm btn-default ajax" data-toggle="modal" data-target="#popupLeurreForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un leurre</button>
      </div>
      <div class="col-xs-4 text-right">
				<button type="submit" class="btn btn-primary btn-upper">Valider</button>
				&nbsp;<a href="<?php echo url_for("degustation_resultats", array('id' => $degustation->_id)) ?>" class="btn btn-success btn-upper">Suivant</a>
			</div>
  </div>
</form>


<?php include_partial('degustation/popupAjoutLeurreForm', array('url' => url_for('degustation_ajout_leurre', $degustation), 'form' => $ajoutLeurreForm)); ?>
