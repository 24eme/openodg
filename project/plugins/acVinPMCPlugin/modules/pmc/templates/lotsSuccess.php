<?php use_helper('Float'); ?>
<?php use_helper('PointsAides');?>

<?php include_partial('pmc/breadcrumb', array('pmc' => $pmc )); ?>
<?php include_partial('pmc/step', array('step' => ConditionnementEtapes::ETAPE_LOTS, 'pmc' => $pmc, 'ajax' => true)) ?>

    <div class="page-header"><h2>Mise en circulation de Lots</h2></div>

    <?php echo include_partial('global/flash'); ?>
    <form role="form" action="<?php echo url_for("pmc_lots", $pmc) ?>" method="post" id="form_pmc_lots" class="form-horizontal">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if ($pmc->type == PMCNCClient::TYPE_MODEL): ?>
    <h2>Votre lot non conforme</h2>
    <?php include_partial('chgtdenom/infoLotOrigine', array('lot' => $pmc->getLotOrigine(), 'opacity' => false)); ?>
    <h2>Ce que vous souhaitez faire recontroler : </h2>
    <p>Merci de confirmer ou corriger les informations du lot à recontroler</p>
    <?php else: ?>
    <?php include_component('degustation', 'syntheseCommercialise', ['identifiant' => $pmc->identifiant, 'campagnes' => [ConfigurationClient::getInstance()->getPreviousCampagne($pmc->campagne), $pmc->campagne], 'region' => $sf_user->getRegion()]) ?>
    <?php endif; ?>

    <?php foreach($form['lots'] as $key => $lot): ?>
        <?php $lotItem = $pmc->lots->get($key); ?>
        <?php if($key == count($form['lots']) - 1): ?>
          <a name="dernier"></a>
        <?php endif; ?>
        <div class="panel panel-default bloc-lot">
            <div class="panel-body" style="padding-bottom: 0;">
              <div class="row">
                    <div class="col-md-6">
                        <?php if (isset($lot['produit_hash'])): ?>
                        <div class="form-group">
                            <?php echo $lot['produit_hash']->renderLabel("Produit", array('class' => "col-sm-5 control-label")); ?>
                            <div class="col-sm-7">
                                  <?php echo $lot['produit_hash']->render(array("data-placeholder" => "Sélectionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                            </div>
                            <div class="col-sm-12 text-danger">
                            <?php echo $lot['produit_hash']->renderError(); ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label class="col-sm-5 pt-3 text-right">
                                Produit
                            </label>
                            <div class="col-sm-7 pt-3">
                                <p><?php echo $pmc->getLotOrigine()->getProduitLibelle(); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5">
                    <?php if (isset($lot['engagement_8515'])): ?>
                      <div class="form-group">
                        <div class="col-md-offset-3 checkbox">
                            <?php echo $lot['engagement_8515']->render(); ?>
                            <?php echo $lot['engagement_8515']->renderLabel("Est en 85/15"); ?>
                        </div>
                        <div class="col-sm-12 text-danger">
                          <?php echo $lot['engagement_8515']->renderError(); ?>
                        </div>
                      </div>
                    <?php endif; ?>
                    </div>
                    <div class="col-md-1">
                      <button type="button" tabindex="-1" class="close lot-delete" title="Supprimer ce lot" aria-hidden="true">×</button>
                    </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $lot['numero_logement_operateur']->renderLabel("Identifiant Lot / Cuve", array('class' => "col-sm-5 control-label")); ?>
                      <div class="col-sm-6">
                            <?php echo $lot['numero_logement_operateur']->render(); ?>
                      </div>
                      <div class="col-sm-6 text-danger">
                            <?php echo $lot['numero_logement_operateur']->renderError(); ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                      <?php if (isset($lot['millesime'])): ?>
                      <div class="form-group">
                          <?php echo $lot['millesime']->renderLabel("Millesime", array('class' => "col-sm-4 control-label")); ?>
                          <div class="col-sm-3">
                              <div class="input-group">
                                  <?php echo $lot['millesime']->render(array('class' => "form-control text-right", 'maxlength' => "4")); ?>
                              </div>
                          </div>
                          <div class="col-sm-6 text-danger">
                              <?php echo $lot['millesime']->renderError(); ?>
                          </div>
                      </div>
                    <?php else: ?>
                      <div class="form-group">
                          <label class="col-sm-4 pt-3 text-right">
                              Millesime
                          </label>
                          <div class="col-sm-3 pt-3">
                              <p><?php echo $pmc->getLotOrigine()->getMillesime(); ?></p>
                          </div>
                      </div>
                      <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['date_degustation_voulue']->renderLabel("Lot prélevable à partir du", array('class' => "col-sm-5 control-label")); ?>
                            <div class="col-sm-5">
                                <div class="input-group date-picker">
                                    <?php echo $lot['date_degustation_voulue']->render(array("class" => "form-control", 'placeholder' => "Date")); ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon-calendar glyphicon"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 text-danger">
                            <?php echo $lot['date_degustation_voulue']->renderError(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['volume']->renderLabel("Volume", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-5">
                                <div class="input-group">
                                    <?php echo $lot['volume']->render(); ?>
                                    <div class="input-group-addon">hl</div>
                                </div>
                            </div>
                            <div class="col-sm-6 text-danger">
                                  <?php echo $lot['volume']->renderError(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php if(DRevConfiguration::getInstance()->hasSpecificiteLot()): ?>
                      <div class="col-md-6">
                          <div class="form-group">
                              <?php echo $lot['specificite']->renderLabel("Spécificité", array('class' => "col-sm-3 control-label")); ?>
                              <div class="col-sm-4">
                                    <?php echo $lot['specificite']->render(); ?>
                              </div>
                              <div class="col-sm-5"></div>
                              <div class="col-sm-6 text-danger">
                                    <?php echo $lot['specificite']->renderError(); ?>
                              </div>
                          </div>
                      </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="text-right">
        <?php if ($pmc->type == PMCNCClient::TYPE_MODEL): ?>
            <button type="submit" name="submit" value="add" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign"></span> Diviser le lot</button>
        <?php else: ?>
            <button type="submit" name="submit" value="add" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter un lot</button>
        <?php endif; ?>
    </div>
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-4">
            <a tabindex="-1" href="<?php echo  url_for('pmc_exploitation', $pmc)  ?>?prec=1" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>
