<?php include_partial('drev/step', array('step' => 'controle_externe', 'drev' => $drev)) ?>

<div class="page-header">
    <h2>Contrôle externe <small>Prélèvement en bouteille</small></h2>
</div>

<form method="post" action="" role="form" class="form-horizontal ajaxForm">
    
    <div class="row">
        <div class="col-xs-7">
            <p>Vin prêt à être dégusté ou plus proche de la commercialisation...</p>
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="row-margin">
                <h3>AOC Alsace</h3>
                <div class="col-xs-offset-1">
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                    <div class="form-group">
                        <span class="text-danger"><?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderError(); ?></span>
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
                </div>
            </div>
            <div class="row-margin">
                <h3>AOC Alsace Grand Cru</h3>
                <div class="col-xs-offset-1">
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté</p>
                    <div class="form-group">
                        <span class="text-danger"><?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderError(); ?></span>
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
                </div>
            </div>

            <div class="row-margin">
                <h3>VT / SGN</h3>
                <div class="col-xs-offset-1">
                    <div class="form-group">
                        <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                        <span class="text-danger"><?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->renderError(); ?></span>
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
                        <span class="text-danger"><?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->renderError(); ?></span>
                        <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->renderLabel("Nombre de lots VT/SGN <small class=\"text-muted\">(toutes appellations confondues)</small>", array("class" => "col-xs-10 control-label")); ?>
                        <div class="col-xs-2">
                            <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->render(array("class" => "form-control")); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-xs-4 col-xs-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h2 class="panel-title">Lieu de prélèvement</h2>
                </div>
                <div class="panel-body">
                    <span>1, rue Garnier Neuilly, 92110</span> <br />
                    <div class="row-margin text-right">
                        <a href="#" class="btn btn-warning">Modifier</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider <small>et étape suivante</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        </div>
    </div>
</form>