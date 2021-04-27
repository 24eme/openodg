<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, 'options' => array('route' => 'degustation_preleve', 'nom' => 'Prélevements réalisés'))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_PRELEVEMENTS)); ?>

<div>
  <div class="modal fade modal-page modal-demande" aria-labelledby="Créer une demande" aria-hidden="true">
  	<div class="modal-dialog">
  		<div class="modal-content">
  			<form method="post" action="<?php echo url_for("degustation_ajout_degustateurPresence", array('id' => $degustation->_id)) ?>" role="form" class="form-horizontal" novalidate>
  				<div class="modal-header">
  					<h4 class="modal-title" id="myModalLabel">Ajouter un degustateur</h4>
  				</div>
  				<div class="modal-body">
            <?php echo $form->renderHiddenFields(); ?>
            <div class="bg-danger">
            <?php echo $form->renderGlobalErrors(); ?>
            </div>


            <div class="panel panel-default bloc-lot">
                <div class="panel-body" style="padding-bottom: 0;">
                  <div class="row">
                        <div class="col-md-10">
                            <div class="form-group">
                                <?php echo $form['nom']->renderLabel("Nom", array('class' => "col-sm-3 control-label")); ?>
                                <div class="col-sm-9">
                                      <?php echo $form['nom']->render(array("data-placeholder" => "Saisir un nom", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                      <div class="col-md-10">
                          <div class="form-group">
                              <?php echo $form['college']->renderLabel("Collège", array('class' => "col-sm-3 control-label")); ?>
                              <div class="col-sm-4">
                                    <?php echo $form['college']->render(); ?>
                              </div>
                              <div class="col-sm-5"></div>
                          </div>
                      </div>
                    </div>
                    <?php if (isset($form['table'])): ?>
                    <div class="row">
                      <div class="col-md-10">
                          <div class="form-group">
                              <?php echo $form['table']->renderLabel("Table", array('class' => "col-sm-3 control-label")); ?>
                              <div class="col-sm-4">
                                    <?php echo $form['table']->render(); ?>
                              </div>
                              <div class="col-sm-5"></div>
                          </div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
            </div>
  				</div>
  				<div class="modal-footer">
            <?php if (isset($form['table'])): ?>
  					  <a href="<?php echo url_for('degustation_presences', array('id' => $degustation->_id, 'numero_table' => 1)) ?>" class="pull-left btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
            <?php else: ?>
              <a href="<?php echo url_for('degustation_degustateurs_confirmation', array('id' => $degustation->_id)) ?>" class="pull-left btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
            <?php endif; ?>
  					<button type="submit" class="btn btn-success btn pull-right">Valider</button>
  				</div>
  			</form>
  		</div>
  	</div>
  </div>

</div>
