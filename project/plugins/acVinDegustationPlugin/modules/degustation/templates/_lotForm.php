<?php $lotHtmlId =  str_replace('volume', '', $form['volume']->renderId()); ?>
<div class="panel panel-default bloc-lot">
    <div class="panel-body" style="padding-bottom: 0;">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo $form['produit_hash']->renderLabel("Produit", array('class' => "col-sm-3 control-label")); ?>
                    <div class="col-sm-9">
                        <?php echo $form['produit_hash']->render(array("data-placeholder" => "Sélectionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <button type="button" tabindex="-1" class="close lot-delete" title="Supprimer ce lot" aria-hidden="true">×</button>
                <div class="form-group">
                    <div class="col-sm-11">
                        <div class="checkbox checkboxlots">
                            <label>
                                <input type="checkbox" <?php echo (count($lot->cepages->toArray(true, false)))? 'checked="checked"' : '' ?>
                                id="lien_<?php echo $lotHtmlId ?>cepages" data-toggle="modal"
                                data-target="#<?php echo $lotHtmlId ?>cepages" />
                                <span class="checkboxtext_<?php echo $lotHtmlId ?>cepages"><?php echo (count($lot->cepages->toArray(true, false))) ? "Mention : " :  "Sans mention de cépage <a>(Changer)</a>" ?></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo $form['numero_logement_operateur']->renderLabel("Numéro lot", array('class' => "col-sm-3 control-label")); ?>
                    <div class="col-sm-3">
                        <?php echo $form['numero_logement_operateur']->render(); ?>
                    </div>
                    <div class="col-sm-6 text-danger">
                        <?php echo $form['numero_logement_operateur']->renderError(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo $form['millesime']->renderLabel("Millesime", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <?php echo $form['millesime']->render(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo $form['destination_type']->renderLabel("Destination", array('class' => "col-sm-3 control-label")); ?>
                    <div class="col-sm-9">
                        <?php echo $form['destination_type']->render(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo $form['volume']->renderLabel("Volume", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <?php echo $form['volume']->render(); ?>
                            <div class="input-group-addon">hl</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <?php if(DRevConfiguration::getInstance()->hasSpecificiteLot()): ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <?php echo $form['specificite']->renderLabel("Spécificité", array('class' => "col-sm-3 control-label")); ?>
                        <div class="col-sm-5">
                            <?php echo $form['specificite']->render(); ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <div class="col-md-6">
                <div class="form-group">
                    <?php echo $form['destination_date']->renderLabel("Date de transaction / conditionnement", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-5">
                        <div class="input-group date-picker">
                            <?php echo $form['destination_date']->render(array('placeholder' => "Date")); ?>
                            <div class="input-group-addon"><span class="glyphicon-calendar glyphicon"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-offset-8 col-md-3 <?php if(!DRevConfiguration::getInstance()->hasSpecificiteLot()): ?>col-md-offset-6<?php endif ?>">
                <?php echo $form['elevage']->render() ?>
                <?php echo $form['elevage']->renderLabel('Lot prévu en élevage') ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal_lot_cepages" data-inputvolumeid="<?php echo $form['volume']->renderId() ?>" id="<?php echo $lotHtmlId ?>cepages" role="dialog" aria-labelledby="Mention de cépages" aria-hidden="true">
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
                            <div class="form-group ligne_lot_cepage ">
                                <div class="col-sm-8">
                                    <?php echo $form['cepage_'.$i]->render(array("data-placeholder" => "Séléctionnez un cépage", "class" => "form-control selectCepage select2 select2-offscreen select2autocomplete")); ?>
                                </div>
                                <div class="col-sm-4">
                                    <div class="input-group input-group-pc" style='display:none;'>
                                        <input class='form-control text-right input-pc'></input>
                                        <div class="input-group-addon">%</div>
                                    </div>
                                    <div class="input-group input-group-hl" >
                                        <?php echo $form['repartition_hl_'.$i]->render(); ?>
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
