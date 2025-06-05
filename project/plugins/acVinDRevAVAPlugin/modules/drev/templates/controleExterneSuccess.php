<?php use_helper("Date"); ?>
<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'controle_externe', 'drev' => $drev)) ?>

<div class="page-header">
    <h2>Contrôle externe <small>Effectué par Qualisud</small></h2>
</div>

<form method="post" action="<?php echo url_for("drev_controle_externe", $drev) ?>" role="form" class="form-horizontal ajaxForm">
    <div class="row">
        <div class="col-xs-7">
            <p>Les prélèvements se font uniquement sur des <strong>vins mis en bouteilles</strong>, au plus proche de la commercialisation</p>

            <?php if($drev->getEtablissementObject()->hasFamille(EtablissementClient::FAMILLE_CONDITIONNEUR)): ?>
            <div class="checkbox">
              <label>
                <input id="checkbox_non_conditionneur" name="non_conditionneur" <?php if($drev->isNonConditionneur()): ?>checked="checked"<?php endif; ?> type="checkbox" value="1">
                Je ne conditionne pas de volume pour ce millésime
              </label>
            </div>
            <?php endif; ?>

            <div id="bloc-form-control-externe" class="<?php if($drev->isNonConditionneur()): ?>opacity-lg<?php endif; ?>">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <?php if (isset($form[DRev::BOUTEILLE_ALSACE])): ?>
                <div class="row-margin">
                    <h3>AOC Alsace</h3>
                    <div class="col-xs-offset-1">
                        <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                        <div class="form-group <?php if ($form[DRev::BOUTEILLE_ALSACE]["date"]->hasError() || $focus == "aoc_alsace"): ?>has-error<?php endif; ?>">
                            <?php if ($form[DRev::BOUTEILLE_ALSACE]["date"]->hasError()): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->getError(); ?></div>
                            <?php endif; ?>
                            <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                            <div class="col-xs-7">
                                <div class="input-group date-picker-week">
                                    <?php if ($focus == "aoc_alsace"): ?>
                                        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->render(array("class" => "form-control", "autofocus" => "autofocus")); ?>
                                    <?php else: ?>
                                        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->render(array("class" => "form-control")); ?>
                                    <?php endif; ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($form[DRev::BOUTEILLE_GRDCRU])): ?>
                <div class="row-margin">
                    <h3>AOC Alsace Grand Cru</h3>
                    <div class="col-xs-offset-1">
                        <p>Semaine à partir de laquelle le vin est prêt à être dégusté</p>
                        <div class="form-group <?php if ($form[DRev::BOUTEILLE_GRDCRU]["date"]->hasError() || $focus == "aoc_grdcru"): ?>has-error<?php endif; ?>">
                            <?php if ($form[DRev::BOUTEILLE_GRDCRU]["date"]->hasError()): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->getError(); ?></div>
                            <?php endif; ?>
                            <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                            <div class="col-xs-7">
                                <div class="input-group date-picker-week">
                                    <?php if ($focus == "aoc_grdcru"): ?>
                                        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->render(array("class" => "form-control", "autofocus" => "autofocus")); ?>
                                    <?php else: ?>
                                        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->render(array("class" => "form-control")); ?>
                                    <?php endif; ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($form[DRev::BOUTEILLE_VTSGN])): ?>
                <div class="row-margin">
                    <h3>VT / SGN</h3>
                    <div class="col-xs-offset-1">
                        <p>Semaine à partir de laquelle le vin est prêt à être dégusté :</p>
                        <div class="form-group <?php if ($form[DRev::BOUTEILLE_VTSGN]["date"]->hasError()): ?>has-error<?php endif; ?>">
                            <?php if ($form[DRev::BOUTEILLE_VTSGN]["date"]->hasError()): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->getError(); ?></div>
                            <?php endif; ?>
                            <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                            <div class="col-xs-7">
                                <div class="input-group date-picker-week">
                                    <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->render(array("class" => "form-control")); ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group <?php if ($form[DRev::BOUTEILLE_VTSGN]["total_lots"]->hasError()): ?>has-error<?php endif; ?>">
                            <?php if ($form[DRev::BOUTEILLE_VTSGN]["total_lots"]->hasError()): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->getError(); ?>
                                </div>
                            <?php endif; ?>
                            <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->renderLabel("Nombre de lots VT/SGN <small class=\"text-muted\">(toutes appellations confondues)</small>", array("class" => "col-xs-10 control-label")); ?>
                            <div class="col-xs-2">
                                <?php echo $form[DRev::BOUTEILLE_VTSGN]["total_lots"]->render(array("class" => "form-control input-rounded num_int text-right")); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
        <div id="bloc-lieu-prelevement" class="col-xs-4 col-xs-offset-1 <?php if($drev->isNonConditionneur()): ?>opacity-lg<?php endif; ?>">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h2 class="panel-title">
                        Lieu de prélèvement
                        <a title="L'adresse à préciser ici est celle du lieu de stockage de vos bouteilles" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a>
                    </h2>
                </div>
                <div class="panel-body form-chai">
                    <?php if (!$formPrelevement && $drev->chais->exist(DRev::BOUTEILLE)): ?>
                        <p>
                            <?php echo $drev->chais->get(DRev::BOUTEILLE)->adresse ?><br />
                            <?php echo $drev->chais->get(DRev::BOUTEILLE)->code_postal ?> <?php echo $drev->chais->get(DRev::BOUTEILLE)->commune ?>
                        </p>
                    <?php endif; ?>
                    <?php if (isset($form['chai'])): ?>
                        <div class="form-group <?php if (!$formPrelevement): ?>hidden<?php endif; ?>">
                            <?php echo $form["chai"]->renderError(); ?>
                            <?php echo $form["chai"]->render(array("class" => "form-control")); ?>
                        </div>
                        <?php if (!$formPrelevement): ?>
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
            <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>vers la validation</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
                <?php endif; ?>
        </div>
    </div>
</form>
