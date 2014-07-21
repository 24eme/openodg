<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'prelevement', 'drev' => $drev)) ?>

<form method="post" action="" role="form" class="form-horizontal">

    <div class="tab-content">
        <div class="tab-pane active">
            <div class="row">
                <div class="col-xs-7">
                    <p>Vin prêt à être dégusté ou plus proche de la commercialisation...</p>
                    <?php echo $form->renderHiddenFields(); ?>
                    <?php echo $form->renderGlobalErrors(); ?>

                    <h2 class="h2-border">AOC Alsace</h2>
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                    <div class="form-group">
                        <?php echo $form[DRev::CUVE_ALSACE]["date"]->renderError(); ?>
                        <?php echo $form[DRev::CUVE_ALSACE]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                        <div class="col-xs-7">
                            <div class="input-group date-picker">
                                <?php echo $form[DRev::CUVE_ALSACE]["date"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="h2-border">VT / SGN</h2>
                    <div class="form-group">
                      <div class="checkbox">
                        <label>
                          <input name="<?php echo $form["vtsgn_demande"]->renderName() ?>" value="<?php echo $form["vtsgn_demande"]->getValue() ?>" type="checkbox" <?php if($form[DRev::CUVE_VTSGN]["date"]->getValue()): ?>checked="checked"<?php endif; ?> class="checkbox-relation" data-relation="#degustation_conseil_cuve_vtsgn_date_form_group" /> Demande de prélévement volontaire des VT / SGN
                        </label>
                      </div>
                    </div>
                    <div id="degustation_conseil_cuve_vtsgn_date_form_group" class="form-group <?php if(!$form[DRev::CUVE_VTSGN]["date"]->getValue()): ?>hidden<?php endif; ?>">
                        <?php echo $form[DRev::CUVE_VTSGN]["date"]->renderError(); ?>
                        <?php echo $form[DRev::CUVE_VTSGN]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                        <div class="col-xs-7">
                            <?php echo $form[DRev::CUVE_VTSGN]["date"]->render(array("class" => "form-control")); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4 col-xs-offset-1">
                    <h2>Lieu de prélèvement :</h2>
                                    
                    <span>Nom du responsable : Gwenael Chichery</span> <br />
                    <span>Adresse : 1, rue Garnier Neuilly, 92110</span> <br />
                    <span>Tél : 06 82 87 68 92</span><br />
                                    
                    <div class="row-margin text-right">
                        <a href="#" class="btn btn-default">Modifier</a>
                    </div>
                </div>
            </div>

            <div class="row row-margin">
                <div class="col-xs-6">
                </div>
                <div class="col-xs-6 text-right">
                    <button type="submit" class="btn btn-default btn-next">Valider et répartir les lots</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-4"><a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg btn-block btn-prev">Étape précendente</a></div>
    </div>
</form>



