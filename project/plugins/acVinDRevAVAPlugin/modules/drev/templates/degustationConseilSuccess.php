<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<div class="page-header">
    <h2>Dégustation conseil <small>Réalisée par l'ODG - AVA</small></h2>
</div>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'prelevement', 'drev' => $drev)) ?>

<form method="post" action="<?php echo url_for("drev_degustation_conseil", $drev) ?>" role="form" class="form-horizontal ajaxForm">
    <div class="row">
        <div class="col-xs-7">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <?php if (isset($form[DRev::CUVE_ALSACE])): ?>
                <div class="row-margin">
                    <h3>AOC Alsace <small>(hors VT/SGN)</small></h3>
                    <div class="col-xs-offset-1">
                        <p>
                            Semaine à partir de laquelle le vin est prêt à être dégusté :
                            <a title="Les vins sont à présenter fermentation terminée, stabilisés et clarifiés (filtration non obligatoire)" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a>
                        </p>
                        <div class="form-group <?php if ($form[DRev::CUVE_ALSACE]["date"]->hasError()): ?>has-error<?php endif; ?>">
                            <?php if ($form[DRev::CUVE_ALSACE]["date"]->hasError()): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $form[DRev::CUVE_ALSACE]["date"]->getError(); ?></div>
                            <?php endif; ?>
                            <?php echo $form[DRev::CUVE_ALSACE]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                            <div class="col-xs-7">
                                <div class="input-group date-picker-week">
                                    <?php echo $form[DRev::CUVE_ALSACE]["date"]->render(array("class" => "form-control")); ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($form[DRev::CUVE_CREMANT])): ?>
                <div class="row-margin">
                    <h3>AOC Crémant</h3>
                    <div class="col-xs-offset-1">
                        <p>
                            Semaine à partir de laquelle le vin de base est prêt à être dégusté :
                            <a title="Les vins sont à présenter fermentation terminée, stabilisés et clarifiés (filtration non obligatoire)" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a>
                        </p>
                        <div class="form-group <?php if ($form[DRev::CUVE_CREMANT]["date"]->hasError()): ?>has-error<?php endif; ?>">
                            <?php if ($form[DRev::CUVE_CREMANT]["date"]->hasError()): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $form[DRev::CUVE_CREMANT]["date"]->getError(); ?></div>
                            <?php endif; ?>
                            <?php echo $form[DRev::CUVE_CREMANT]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                            <div class="col-xs-7">
                                <div class="input-group date-picker-week">
                                    <?php echo $form[DRev::CUVE_CREMANT]["date"]->render(array("class" => "form-control")); ?>
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($form[DRev::CUVE_VTSGN])): ?>
                <div class="row-margin">
                    <h3>VT / SGN</h3>
                    <div class="col-xs-offset-1">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input name="<?php echo $form["vtsgn_demande"]->renderName() ?>" value="<?php echo $form["vtsgn_demande"]->getValue() ?>" type="checkbox" <?php if ($form[DRev::CUVE_VTSGN]["date"]->getValue() || $form["vtsgn_demande"]->hasError()): ?>checked="checked"<?php endif; ?> class="checkbox-relation" data-relation="#degustation_conseil_cuve_vtsgn_date_form_group" /> Demande de prélèvement volontaire des VT / SGN <a title="Le prélèvement se fera de préférence sur des vins encore en cuve ou en fût" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a>
                                </label>
                            </div>
                        </div>
                        <div id="degustation_conseil_cuve_vtsgn_date_form_group" class="form-group <?php if (!$form[DRev::CUVE_VTSGN]["date"]->getValue() && !$form["vtsgn_demande"]->hasError()): ?>hidden<?php endif; ?> <?php if ($form[DRev::CUVE_VTSGN]["date"]->hasError()): ?>has-error<?php endif; ?>">
                            <div class="<?php if ($form["vtsgn_demande"]->hasError()): ?>has-error<?php endif; ?>">
                                <?php if ($form["vtsgn_demande"]->hasError()): ?>
                                    <div class="alert alert-danger" role="alert"><?php echo $form["vtsgn_demande"]->getError(); ?></div>
                                <?php endif; ?>

                                <?php if ($form[DRev::CUVE_VTSGN]["date"]->hasError()): ?>
                                    <div class="alert alert-danger" role="alert"><?php echo $form[DRev::CUVE_ALSACE]["date"]->getError(); ?></div>
                                <?php endif; ?>

                                <?php echo $form[DRev::CUVE_VTSGN]["date"]->renderLabel(null, array("class" => "col-xs-5 control-label")); ?>
                            </div>
                            <div class="col-xs-7<?php if ($form["vtsgn_demande"]->hasError()): ?> has-error<?php endif; ?>">
                                <?php echo $form[DRev::CUVE_VTSGN]["date"]->render(array("class" => "form-control")); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 col-xs-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h2 class="panel-title">
                        Lieu de prélèvement
                        <a title="L'adresse à préciser ici est celle de votre lieu de vinification" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a>
                    </h2>
                </div>
                <div class="panel-body form-chai">
                    <?php $chai = $drev->chais->getOrAdd(DRev::CUVE) ?>
                    <?php if (!$formPrelevement): ?>
                        <p>
                            <?php echo $chai->adresse ?><br />
                            <?php echo $chai->code_postal ?> <?php echo $chai->commune ?>
                        </p>
                    <?php endif; ?>
                    <?php if (isset($form['chai'])): ?>
                        <div class="form-group <?php if (!$formPrelevement): ?>hidden<?php endif; ?>">
                            <?php if ($form["chai"]->hasError()): ?>
                                <div class="alert alert-danger" role="alert"><?php echo $form["chai"]->getError(); ?></div>
                            <?php endif; ?>
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
            <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer <small>en répartissant les lots</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
                <?php endif; ?>
        </div>
    </div>
</form>
