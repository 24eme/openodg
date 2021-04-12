<?php use_helper('Float') ?>
<?php use_helper('Date') ?>

<?php use_javascript("chgtDenom.js", "last") ?>
<?php include_partial('chgtdenom/breadcrumb', array('chgtDenom' => $chgtDenom )); ?>


    <div class="page-header no-border">
      <h2>Changement de dénomination / Déclassement</h2>
      <h3><small></small></h3>
    </div>

    <?php include_partial('infoLotOrigine', array('lot' => $chgtDenom->getLotOrigine(), 'opacity' => false)); ?>

    <form role="form" action="<?php echo url_for("chgtdenom_edition", array("sf_subject" => $chgtDenom, 'key' => $chgtDenom->getLotKey())) ?>" method="post" class="form-horizontal" id="form_drev_lots">

        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <div class="row">
              <div class="col-md-8">
                  <div class="form-group">
                      <?php echo $form['changement_type']->renderLabel("Type de modification", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-8 bloc_condition" data-condition-cible="#bloc_changement_produit_hash">
                            <span class="error text-danger"><?php echo $form['changement_type']->renderError() ?></span>
                            <?php echo $form['changement_type']->render(); ?>
                      </div>
                  </div>
              </div>
        </div>

        <div class="row" id="bloc_changement_produit_hash" data-condition-value="<?php echo ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT; ?>">
          <div class="col-md-8">
              <div class="form-group">
                  <?php echo $form['changement_produit_hash']->renderLabel("Nouveau produit", array('class' => "col-sm-4 control-label")); ?>
                  <div class="col-sm-8">
                      <span class="error text-danger"><?php echo $form['changement_produit_hash']->renderError() ?></span>
                      <?php echo $form['changement_produit_hash']->render(array("data-placeholder" => "Sélectionnez un nouveau produit", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                  </div>
              </div>
          </div>
          <div class="col-md-4">
              <div class="form-group">
                <div class="col-sm-12">
                  <div class="checkbox checkboxlots">
                    <label>
                      <input type="checkbox" <?php echo (count($chgtDenom->changement_cepages->toArray(true, false)))? 'checked="checked"' : '' ?>
                             id="lien_changement_cepages" data-toggle="modal"
                             data-target="#changement_cepages" />
                      <span class="checkboxtext_changement_cepages"><?php echo (count($chgtDenom->changement_cepages->toArray(true, false))) ? "Mention : " :  "Sans mention de cépage <a>(Changer)</a>" ?></span></label>
                    </div>
                  </div>
              </div>
          </div>
        </div>

        <div class="row">
              <div class="col-md-8">
                  <div class="form-group">
                      <?php echo $form['changement_volume']->renderLabel("Volume concerné par cette modification", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-5">
                          <span class="error text-danger"><?php echo $form['changement_volume']->renderError() ?></span>
                          <div class="input-group">
                              <?php echo $form['changement_volume']->render(array("placeholder" => "Précisez un volume")); ?>
                              <div class="input-group-addon">hl</div>
                          </div>
                      </div>
                  </div>
              </div>
        </div>
        <?php if(ChgtDenomConfiguration::getInstance()->hasSpecificiteLot()): ?>
        <div class="row">
              <div class="col-md-8">
                  <div class="form-group">
                      <?php echo $form['changement_specificite']->renderLabel("Spécificité", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-5">
                          <span class="error text-danger"><?php echo $form['changement_specificite']->renderError() ?></span>
                          <div class="input-group">
                              <?php echo $form['changement_specificite']->render(array("placeholder" => "Précisez une spécificité")); ?>
                          </div>
                      </div>
                  </div>
              </div>
        </div>
      <?php endif; ?>

        <div style="margin-top: 20px;" class="row row-margin row-button">
            <div class="col-xs-4">
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_lots', ['id' => $chgtDenom->_id, 'campagne' => $chgtDenom->campagne]) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
            </div>
            <div class="col-xs-4 text-center">
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_delete', $chgtDenom) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-remove"></span> Supprimer la déclaration</a>
            </div>
            <div class="col-xs-4 text-right">
                <button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>


        <div class="modal fade modal_lot_cepages" id="changement_cepages" role="dialog" aria-labelledby="Mention de cépages" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="myModalLabel">Mention de cépages</h4>
                        <h5>Déclarer seulement les cépages qui figureront sur l'étiquette.</h5>
                    </div>
                    <div class="modal-body form-horizontal">
                        <?php for($i=0; $i < DRevLotForm::NBCEPAGES; $i++): ?>
                            <div class="form-group ligne_lot_cepage">
                                <div class="col-sm-1"></div>
                                <div class="col-sm-7">
                                    <?php echo $form['cepage_'.$i]->render(array("data-placeholder" => "Séléctionnez un cépage", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                                </div>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <?php echo $form['repartition_'.$i]->render(); ?>
                                        <div class="input-group-addon">hl</div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-default btn pull-left" data-dismiss="modal">Fermer</a>
                        <a class="btn btn-success btn pull-right" data-dismiss="modal">Valider</a>
                    </div>
                </div>
            </div>
        </div>

    </form>
