<?php use_helper('Float'); ?>
<?php use_helper('PointsAides');?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation )); ?>
<?php include_partial('degustation/step', array('step' => DegustationEtapes::ETAPE_LOTS, 'degustation' => $degustation)) ?>

    <div class="page-header"><h2>Modification du Lot IGP</h2></div>

    <form role="form" action="<?php echo url_for("degustation_update_lot", ['id' => $degustation->_id, 'lot' => $lotkey]) ?>" method="post" id="form_degustation_update_lot" class="form-horizontal">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

        <?php $lotItem = $degustation->lots->get($lotkey); ?>
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
                        <div class="form-group">
                          <div class="col-sm-7">
                            <div class="checkbox checkboxlots">
                              <label>
                                <input type="checkbox" <?php echo ($lotItem->exist('cepages') && count($lotItem->cepages->toArray(true, false)))? 'checked="checked"' : '' ?>
                                       id="lien_<?php echo $lotkey ?>_cepages" data-toggle="modal"
                                       data-target="#<?php echo $lotkey ?>_cepages" />
                                <span class="checkboxtext_<?php echo $lotkey ?>_cepages"><?php echo ($lotItem->exist('cepages') && count($lotItem->cepages->toArray(true, false))) ? "Assemblages : " :  "Assemblage" ?></span></label>
                              </div>

                            </div>
                            <div class="col-sm-2">
                                  <?php echo $form['millesime']->render(array('data-default-value' => $degustation->getCampagne())); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form['numero']->renderLabel("Numéro / Cuve(s)", array('class' => "col-sm-3 control-label")); ?>
                            <div class="col-sm-6">
                                  <?php echo $form['numero']->render(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button type="button" tabindex="-1" class="close lot-delete" title="Supprimer ce lot" aria-hidden="true">×</button>
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
                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                      <label>
                        <?php echo $form['elevage']->render() ?>
                        <?php echo $form['elevage']->renderLabel('Lot prévu en élevage') ?>
                      </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade modal_lot_cepages" data-lot=<?php echo $key ?> id="<?php echo $lotkey ?>_cepages" role="dialog" aria-labelledby="Répartition des cépages" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="myModalLabel">Répartition des cépages</h4>
                    </div>
                    <div class="modal-body">
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
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-offset-8 col-xs-4 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>

