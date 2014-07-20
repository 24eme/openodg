<?php include_partial('drev/step', array('step' => 'controle_externe', 'drev' => $drev)) ?>

<ul class="nav nav-tabs" role="tablist">
    <li class="active">
        <a href="#" role="tab">Prélèvement en bouteille</a>
    </li>
</ul>

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
                        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderError(); ?>
                        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                        <div class="col-xs-7">
                            <div class="input-group date-picker">
                                <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="h2-border">AOC Alsace Grand Cru</h2>
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                    <div class="form-group">
                        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderError(); ?>
                        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                        <div class="col-xs-7">
                            <div class="input-group date-picker">
                                <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="h2-border">VT / SGN</h2>
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                    <div class="form-group">
                        <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->renderError(); ?>
                        <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                        <div class="col-xs-7">
                            <div class="input-group date-picker">
                                <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->renderError(); ?>
                        <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->renderLabel("Nombre de lots VT/SGN <small class=\"text-muted\">(toutes appellations confondues)</small>", array("class" => "col-xs-10 control-label")); ?>
                        <div class="col-xs-2">
                            <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->render(array("class" => "form-control")); ?>
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
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-4"><a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg btn-block btn-prev">Étape précendente</a></div>
        <div class="col-xs-4 col-xs-offset-4"><button type="submit" class="btn btn-primary btn-lg btn-block btn-next">Étape suivante</a></div>
    </div>
</form>