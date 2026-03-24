<?php use_helper('Float') ?>
<?php use_helper('Date') ?>

<?php include_partial('prisedemousse/breadcrumb', array('prisedemousse' => $prisedemousse )); ?>
<?php include_partial('prisedemousse/step', array('step' => 'edition', 'prisedemousse' => $prisedemousse)) ?>


    <div class="page-header no-border">
      <h2>Déclaration de prise de mousse <?php echo $periode; ?></h2>
      <h3><small></small></h3>
    </div>

    <?php if ($lotOrigine = $prisedemousse
        ->getLotOrigine()) : ?>
        <?php include_partial('infoLotOrigine', array('lot' => $prisedemousse
            ->getLotOrigine(), 'opacity' => false)); ?>
    <?php else : ?>
        <div class="well">
            Déclare posséder un lot de <strong><?php echo $prisedemousse
            ->getOrigineProduitLibelleAndCepages() ?></strong> de <strong><?php echoFloat($prisedemousse
                ->getOrigineVolume()) ?></strong> <span class="text-muted">hl</span>
        </div>
    <?php endif; ?>

    <form role="form" action="<?php echo url_for("prisedemousse_edition", array("sf_subject" => $prisedemousse
    )) ?>" method="post" class="form-horizontal" id="form_drev_lots">

        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

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
                      <input type="checkbox" <?php echo (count($prisedemousse
                          ->changement_cepages->toArray(true, false)))? 'checked="checked"' : '' ?>
                             id="lien_prisedemousse_changement_cepages" data-toggle="modal"
                             data-target="#prisedemousse_changement_cepages" />
                      <span class="checkboxtext_prisedemousse_changement_cepages"><?php echo (count($prisedemousse
                          ->changement_cepages->toArray(true, false))) ? "Mention : " :  "Sans mention de cépage <a>(Changer)</a>" ?></span></label>
                    </div>
                  </div>
              </div>
          </div>
        </div>

        <div class="row">
              <div class="col-md-8">
                  <div class="form-group">
                      <?php echo $form['changement_volume']->renderLabel("Volume final après prise de mousse", array('class' => "col-sm-4 control-label")); ?>
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
                          <span class="error text-danger"><?php echo $form['prisedemousse_specificite']->renderError() ?></span>
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
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_delete', $prisedemousse
                ) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-remove"></span> Annuler</a>
            </div>
            <div class="col-xs-4 col-xs-offset-4 text-right">
                <button type="submit" class="btn btn-primary btn-upper">Suivant <span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>


        <div class="modal fade modal_lot_cepages" id="changement_changement_cepages" data-inputvolumeid="<?php echo $form['changement_volume']->renderId() ?>" role="dialog" aria-labelledby="Mention de cépages" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="myModalLabel">Mention de cépages</h4>
                        <h5>Déclarer seulement les cépages qui figureront sur l'étiquette.</h5>
                    </div>
                    <div class="modal-body">
                      <div class="form-group">
                        <div class="col-sm-2 col-sm-offset-10">
                          <div class="form-group">
                              <label class="checkbox-inline checbox-switch">
                                  hl
                                  <input class="form-check-input switch_hl_to_pc" type="checkbox" name="" />
                                  <span></span>
                                  %
                              </label>
                          </div>
                        </div>
                      </div>
                        <?php for($i=0; $i < DRevLotForm::NBCEPAGES; $i++): ?>
                            <div class="form-group ligne_lot_cepage">
                                <div class="col-sm-8">
                                    <?php echo $form['cepage_'.$i]->render(array("data-placeholder" => "Séléctionnez un cépage", "class" => "form-control selectCepage select2 select2-offscreen select2autocomplete")); ?>
                                </div>
                                <div class="col-sm-4">
                                  <div class="input-group input-group-pc" style='display:none;'>
                                      <input class='form-control text-right input-pc'></input>
                                      <div class="input-group-addon">%</div>
                                  </div>
                                  <div class="input-group input-group-hl" >
                                      <?php echo $form['repartition_'.$i]->render(); ?>
                                      <div class="input-group-addon">hl</div>
                                  </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                        <div class="form-group ligne_volume_total">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 text-right">
                              <label class='control-label'> Volume total</label>
                            </div>
                            <div class="col-sm-4">
                                <div class="input-group volume-total">
                                  <input class='form-control text-right input-total'></input>
                                  <div class="input-group-addon">hl</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-default btn pull-left" data-dismiss="modal">Fermer</a>
                        <a class="btn btn-success btn pull-right" data-dismiss="modal">Valider</a>
                    </div>
                </div>
            </div>
        </div>

    </form>
