<?php use_helper("Date"); ?>
<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>

<?php include_partial('degustation/organisationTableHeader', array('degustation' => $degustation, 'numero_table' => $numero_table, 'tri' => $tri)); ?>

<div class="row row-condensed">
  <div class="col-xs-12">
    <div class="panel panel-default">
      <div class="panel-body">
          <div class="alert alert-info" role="alert">
          	<h3>Synthèse table <?php echo DegustationClient::getNumeroTableStr($numero_table); ?></h3>
          	<table class="table table-condensed">
          			<thead>
          				<tr>
          					<th class="col-xs-8"><?php echo $tri; ?> (<a data-toggle="modal" data-target="#popupTableTriForm" type="button" href="#">changer</a>)</th>
                            <th class="col-xs-1"></th>
          					<th class="col-xs-2">Nombre d'échantillons</th>
                            <th class="col-xs-1"></th>
          				</tr>
          			</thead>
          			<tbody id="synthese">
                <?php $total = 0; ?>
          			<?php foreach ($syntheseLots as $hash => $lotsProduit): ?>
          				<tr class="vertical-center cursor-pointer" data-hash="<?php echo $hash; ?>" >
          					<td><?php echo preg_replace('/ -(.*)/', '<span class="text-muted">\1</span>', $lotsProduit->libelle) ?></td>
                    <td></td>
                    <td class="nblots text-right"><?php echo count($lotsProduit->lotsTable); $total += count($lotsProduit->lotsTable); ?></td>
                    <td></td>
          				</tr>
          			<?php endforeach; ?>
                  <tr>
                    <td class="text-right"></td>
                    <td><strong>Total</strong> : </td>
                    <td class="nblots text-right" ><span data-total="1"><?php echo $total ?></span></td>
                    <td></td>
                  </tr>
          		</tbody>
          	</table>
          </div>
          <div class="row">
            <div class="col-sm-offset-8 col-sm-4 col-xs-offset-6 col-xs-6">
              <button class="btn btn-block btn-default" id="btn-preleve-all">
                  <i class="glyphicon glyphicon-ok-sign"></i>
                  Tous sur la table <?php echo DegustationClient::getNumeroTableStr($numero_table); ?>
              </button>
              <br/>
            </div>
          </div>
          	<form action="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => $numero_table, 'tri' => $tri)) ?>" method="post" class="form-horizontal degustation table" id="#form-organisation-table">
          		<?php echo $form->renderHiddenFields(); ?>
          		<div class="bg-danger">
          			<?php echo $form->renderGlobalErrors(); ?>
          		</div>

          		<table class="table table-bordered table-condensed table-striped">
          			<thead>
          				<tr>
                      <th class="col-xs-1">&nbsp;</th>
                      <th class="col-xs-9">Échantillons &nbsp; <span class="text-muted">(<?php echo $tri; ?> - <a data-toggle="modal" data-target="#popupTableTriForm" type="button" href="#">changer</a> )</span></th>
                      <th class="col-xs-1">Provenance</th>
          					  <th class="col-xs-1">Table <?php echo DegustationClient::getNumeroTableStr($numero_table); ?></th>
          				</tr>
          			</thead>
          			<tbody>
          				<?php
          				foreach ($form->getTableLots() as $lot):
          					$name = $form->getWidgetNameFromLot($lot);
          					if (isset($form[$name])):
          						?>
          						<tr class="vertical-center cursor-pointer">
                        <td class="edit text-center<?php if ($lot->leurre === true): ?> bg-warning<?php endif ?>">
                          <?php if ($numero_table == $lot->numero_table): ?>
                          <a href="<?php echo url_for('degustation_position_lot_up', array('id' => $degustation->_id, 'index' => $lot->getKey(), 'tri' => $tri, 'numero_table' => $numero_table)) ?>"><span class="glyphicon glyphicon-chevron-up"></span></a>
                          <a href="<?php echo url_for('degustation_position_lot_down', array('id' => $degustation->_id, 'index' => $lot->getKey(), 'tri' => $tri, 'numero_table' => $numero_table)) ?>"><span class="glyphicon glyphicon-chevron-down"></span></a>
                          <?php endif; ?>
                            <br/>
                            <small class="text-muted"><?php echo $lot->position ?></small>
                        </td>
          							<td<?php if ($lot->leurre === true): ?> class="bg-warning"<?php endif ?>>
          								<div class="row">
                                              <div class="col-xs-4 text-right">
                                                  <?php if ($lot->leurre === true): ?><em>Leurre</em> <?php endif ?>
                                                  <?php echo $lot->declarant_nom; echo (!$lot->leurre)? ' ('.$lot->numero_logement_operateur.')' : ''; ?>
                                              </div>
                                              <div class="col-xs-6">
                                                <?= showProduitLot($lot) ?>
                                              </div>
          								</div>
          							</td>
                                    <td><?= $lot->getTypeProvenance() ?></td>
          							<td class="text-center<?php if ($lot->leurre === true): ?> bg-warning<?php endif ?>" data-hash="<?php echo $lot->getTriHash($tri_array->getRawValue()); ?>" data-libelle-produit="<?php echo $lot->produit_libelle.' <small class=\'text-muted\'>'.$lot->details.'</small>'; echo ($lot->millesime)? ' ('.$lot->millesime.')' : ''; ?>">
          								<div style="margin-bottom: 0;" class="form-group <?php if($form[$name]->hasError()): ?>has-error<?php endif; ?>">
          									<?php echo $form[$name]->renderError() ?>
          									<div class="col-xs-12">
                                              <?php echo $form[$name]->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
          									</div>
          								</div>
          							</td>
          						</tr>
          					<?php  endif; ?>
          				<?php endforeach; ?>
          			</tbody>
          		</table>

          		<div class="row row-margin row-button">
          			<div class="col-xs-4">
                        <?php if($numero_table > 1): ?>
                        <a href="<?php echo url_for("degustation_organisation_table", array('id' => $degustation->_id, 'numero_table' => $numero_table - 1, 'tri' => $tri)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Précédent</a>
                        <?php else: ?>
                            <a href="<?php echo url_for("degustation_tables_etape", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
                        <?php endif; ?>
                    </div>
          			<div class="col-xs-4 text-center">
          				<button class="btn btn-sm btn-default ajax" data-toggle="modal" data-target="#popupLeurreForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un leurre</button>
          			</div>
          			<div class="col-xs-4 text-right">
                        <button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
          			</div>
          		</div>
          	</form>

          <?php include_partial('degustation/popupAjoutLeurreForm', array('url' => url_for('degustation_ajout_leurre', $degustation), 'form' => $ajoutLeurreForm, 'table' => $numero_table)); ?>
          <?php include_partial('degustation/popupTableTriForm', array('url' => url_for('degustation_tri_table', array('id' => $degustation->_id, 'numero_table' => $numero_table)), 'form' => $triTableForm, 'table' => $numero_table)); ?>
      </div>
    </div>
  </div>
</div>
