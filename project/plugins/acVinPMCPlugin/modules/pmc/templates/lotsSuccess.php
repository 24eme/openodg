<?php use_helper('Float'); ?>
<?php use_helper('PointsAides');?>

<?php include_partial('pmc/breadcrumb', array('pmc' => $pmc )); ?>
<?php include_partial('pmc/step', array('step' => ConditionnementEtapes::ETAPE_LOTS, 'pmc' => $pmc, 'ajax' => true)) ?>

    <div class="page-header"><h2>Mise en circulation des Lots</h2></div>

    <?php echo include_partial('global/flash'); ?>
    <form role="form" action="<?php echo url_for("pmc_lots", $pmc) ?>" method="post" id="form_pmc_lots" class="form-horizontal">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php include_component('degustation', 'syntheseCommercialise', ['identifiant' => $pmc->identifiant, 'campagnes' => [ConfigurationClient::getInstance()->getPreviousCampagne($pmc->campagne), $pmc->campagne], 'region' => $sf_user->getRegion()]) ?>

    <?php foreach($pmc->lots as $lot): ?>
      <?php if(!$lot->hasBeenEdited()){ continue; } ?>
      <div class="panel panel-default" style="border-color:rgba(130, 147, 69, 0.4);">
          <div class="panel-body panel-body-success">
            <div class="row">
              <div class="col-md-2"><?php echo Date::francizeDate($lot->date); ?></div>
              <div class="col-md-6"><strong><?php echo $lot->produit_libelle; ?></strong>
                <?php if(count($lot->cepages)): ?>
                  &nbsp;<small>
                    <?php echo $lot->getCepagesLibelle(); ?>
                  </small>
                <?php endif; ?>
              </div>
              <div class="col-md-3"><?php echo $lot->millesime; ?></div>
              <div class="col-md-1 text-right">
                <?php if($isAdmin): ?>
                  <a href="<?php echo url_for("pmc_lots_delete", array('id' => $pmc->_id, 'numArchive' => $lot->numero_archive)) ?>" onclick='return confirm("Étes vous sûr de vouloir supprimer ce lot ?");' class="close" title="Supprimer ce lot" aria-hidden="true">×</a>
                <?php endif; ?>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2"></div>
              <div class="col-md-3">Numéro lot : <?php echo $lot->numero_archive; ?></div>
              <div class="col-md-3"><strong>Volume : <?php echo $lot->volume; ?><small class="text-muted">&nbsp;hl</small></strong></div>
              <div class="col-md-3"><?php echo ($lot->destination_type)? PMCClient::$lotDestinationsType[$lot->destination_type] : ''; echo ($lot->destination_date)? " (".Date::francizeDate($lot->destination_date).")" : ""; ?></div>
              <div class="col-md-1" >
              </div>
            </div>
          </div>
          <div class="row"></div>
      </div>
    <?php endforeach; ?>
    <?php foreach($form['lots'] as $key => $lot): ?>
        <?php $lotItem = $pmc->lots->get($key); ?>
        <?php if($key == count($form['lots']) - 1): ?>
          <a name="dernier"></a>
        <?php endif; ?>
        <div class="panel panel-default bloc-lot">
            <div class="panel-body" style="padding-bottom: 0;">
              <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['produit_hash']->renderLabel("Produit", array('class' => "col-sm-5 control-label")); ?>
                            <div class="col-sm-7">
                                  <?php echo $lot['produit_hash']->render(array("data-placeholder" => "Sélectionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                            </div>
                            <div class="col-sm-12 text-danger">
                            <?php echo $lot['produit_hash']->renderError(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                      <div class="form-group">
                        <div class="col-md-offset-3 checkbox">
                            <?php echo $lot['engagement_8515']->render(); ?>
                            <?php echo $lot['engagement_8515']->renderLabel("Est en 85/15"); ?>
                        </div>
                        <div class="col-sm-12 text-danger">
                          <?php echo $lot['engagement_8515']->renderError(); ?>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-1">
                      <button type="button" tabindex="-1" class="close lot-delete" title="Supprimer ce lot" aria-hidden="true">×</button>
                    </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $lot['numero_logement_operateur']->renderLabel("Lot / Cuve", array('class' => "col-sm-5 control-label")); ?>
                      <div class="col-sm-6">
                            <?php echo $lot['numero_logement_operateur']->render(); ?>
                      </div>
                      <div class="col-sm-6 text-danger">
                            <?php echo $lot['numero_logement_operateur']->renderError(); ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
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
        <button type="submit" name="submit" value="add" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter un lot</button>
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
