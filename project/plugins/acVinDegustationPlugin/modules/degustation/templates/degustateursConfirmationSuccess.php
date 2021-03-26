<?php use_helper('Float') ?>
<?php use_helper("Date") ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, 'options' => array('route' => 'degustation_degustateurs_confirmation', 'nom' => 'Confirmation des dégustateurs'))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_PRELEVEMENTS)); ?>

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<div class="page-header no-border">
  <h2>Confirmation de la venue des dégustateurs</h2>
	<h3><?php echo ucfirst(format_date($degustation->date, "P", "fr_FR"))." à ".format_date($degustation->date, "H")."h".format_date($degustation->date, "mm") ?> <small><?php echo $degustation->getLieuNom(); ?></small></h3>
</div>
<p>Sélectionner les degustateurs qui ont confirmer leur présence à la dégustation</p>
<form action="<?php echo url_for("degustation_degustateurs_confirmation", $degustation) ?>" method="post" class="form-horizontal degustateurs-confirmation">
	<?php echo $form->renderHiddenFields(); ?>

		<div class="bg-danger">
		<?php echo $form->renderGlobalErrors(); ?>
		</div>
<div class="row row-condensed">
	<div class="col-xs-12">
		<h3>Dégustateurs</h3>
        <table class="table table-bordered table-condensed table-striped">
        	<thead>
            	<tr>
            		<th class="col-xs-3">Collège</th>
        				<th class="col-xs-7">Membre</th>
                <th class="col-xs-2">Confirmation présence</th>
              </tr>
        	</thead>
        	<tbody>
        		<?php foreach ($degustation->degustateurs as $college => $degustateurs): ?>
	        		<?php foreach ($degustateurs as $id => $degustateur):
								$name = $form->getWidgetNameFromDegustateur($degustateur);
								?>
	        		<tr <?php if($degustateur->exist('confirmation') && ($degustateur->confirmation === false)): ?>class="disabled text-muted" disabled="disabled" style="text-decoration:line-through;"<?php endif; ?> >
								<td><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></td>
	        			<td><a href="<?php echo url_for('compte_visualisation', array('identifiant' => $id)) ?>" target="_blank"><?php echo $degustateur->get('libelle','') ?></a></td>
	              <td class="text-center edit">
									<div style="margin-bottom: 0;" class="form-group <?php if($form[$name]->hasError()): ?>has-error<?php endif; ?>">
										<?php echo $form[$name]->renderError() ?>
										<div class="col-xs-12" >
											<?php echo $form[$name]->render(array('class' => "bsswitch ajax", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                      <?php if(!$degustateur->exist('confirmation') || ($degustateur->confirmation === true)): ?>
                      <a onclick='return confirm("Êtes vous sûr de marquer absent ce dégustateur ?");' class="pull-right" href="<?php echo url_for('degustation_degustateur_absence', array('id' => $degustation->_id, 'college' => $college, 'degustateurId' => $id)); ?>">
                        <?php if(!$degustateur->exist('confirmation') || $degustateur->confirmation != false): ?><span style="position:absolute;right:30px;"class="glyphicon glyphicon-remove-sign text-danger"></span><?php endif; ?></a>
                    <?php endif; ?>
                    </div>
									</div>
	  						</td>
							</tr>
        		<?php endforeach;?>
        		<?php endforeach; ?>
        	</tbody>
        </table>
	</div>
</div>
	<div class="row row-button">
				<div class="col-xs-4"><a href="<?php echo url_for("degustation_prelevements_etape", $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
				<div class="col-xs-4 text-center">
				</div>
				<div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider</button></div>
		</div>
</form>
