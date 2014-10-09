<?php use_helper("Date"); ?>
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
            <?php if(isset($form[DRev::BOUTEILLE_ALSACE])): ?>
            <div class="row-margin">
                <h3>AOC Alsace</h3>
                <div class="col-xs-offset-1">
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                    <div class="form-group <?php if($form[DRev::BOUTEILLE_ALSACE]["date"]->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderError(); ?></span>
                        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                        <div class="col-xs-7">
                            <div class="input-group date-picker">
                                <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <?php if($drev->prelevements->get(DRev::BOUTEILLE_ALSACE)->date_precedente): ?>
                            <small class="col-xs-5 text-right text-muted">Dégustation <?php echo $drev->campagne - 1 ?></small>
                            <small class="col-xs-7 text-center text-muted">Semaine du <?php echo format_date($drev->prelevements->get(DRev::BOUTEILLE_ALSACE)->date_precedente, "D", "fr_FR") ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if(isset($form[DRev::BOUTEILLE_GRDCRU])): ?>
            <div class="row-margin">
                <h3>AOC Alsace Grand Cru</h3>
                <div class="col-xs-offset-1">
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté</p>
                    <div class="form-group <?php if($form[DRev::BOUTEILLE_GRDCRU]["date"]->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderError(); ?></span>
                        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                        <div class="col-xs-7">
                            <div class="input-group date-picker">
                                <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->render(array("class" => "form-control")); ?>
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <?php if($drev->prelevements->get(DRev::BOUTEILLE_GRDCRU)->date_precedente): ?>
                            <small class="col-xs-5 text-right text-muted">Dégustation <?php echo $drev->campagne - 1 ?></small> 
                            <small class="col-xs-7 text-center text-muted">Semaine du <?php echo format_date($drev->prelevements->get(DRev::BOUTEILLE_GRDCRU)->date_precedente, "D", "fr_FR") ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if(isset($form[DRev::BOUTEILLE_VTSGN])): ?>
            <div class="row-margin">
                <h3>VT / SGN</h3>
                <div class="col-xs-offset-1">
                    <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                    <div class="form-group <?php if($form[DRev::BOUTEILLE_VTSGN]["date"]->hasError()): ?>has-error<?php endif; ?>">
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
                        <?php if($drev->prelevements->get(DRev::BOUTEILLE_VTSGN)->date_precedente): ?>
                            <small class="col-xs-5 text-right text-muted">Dégustation <?php echo $drev->campagne - 1 ?></small>
                            <small class="col-xs-7 text-center text-muted">Semaine du <?php echo format_date($drev->prelevements->get(DRev::BOUTEILLE_VTSGN)->date_precedente, "D", "fr_FR") ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?php if($form[DRev::BOUTEILLE_VTSGN]["total_lots"]->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->renderError(); ?>
                        <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->renderLabel("Nombre de lots VT/SGN <small class=\"text-muted\">(toutes appellations confondues)</small>", array("class" => "col-xs-10 control-label")); ?>
                        <div class="col-xs-2">
                            <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->render(array("class" => "form-control input-rounded num_int text-right")); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 col-xs-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h2 class="panel-title">Lieu de prélèvement</h2>
                </div>
                <div class="panel-body form-chai">
                    <?php $chai = $drev->chais->get(DRev::BOUTEILLE) ?>
                    <?php if(!$formPrelevement): ?>
                    <p>
                        <?php echo $chai->adresse ?><br />
                        <?php echo $chai->code_postal ?> <?php echo $chai->commune ?>
                    </p>
                    <?php endif; ?>
                    <?php if(isset($form['chai'])): ?>
                        <div class="form-group <?php if(!$formPrelevement): ?>hidden<?php endif; ?>">
                            <?php echo $form["chai"]->renderError(); ?>
                            <?php echo $form["chai"]->render(array("class" => "form-control")); ?>
                        </div>
                        <?php if(!$formPrelevement): ?>
                        <div class="row-margin text-right">
                            <button type="button" class="btn btn-sm btn-warning">Modifier</button>
                        </div>
                    	<?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la validation</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        </div>
    </div>
</form>