<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => DrevEtapes::ETAPE_LOTS, 'drev' => $drev, 'ajax' => true)) ?>

    <div class="page-header"><h2>Revendication des Lots</h2></div>

    <?php echo include_partial('global/flash'); ?>

    <form role="form" action="<?php echo url_for("drev_lots", $drev) ?>" method="post" id="form_drev_lots" class="form-horizontal">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach($form['lots'] as $key => $lot): ?>
        <?php if($key == count($form['lots']) - 1): ?>
        <a name="dernier"></a>
        <?php endif; ?>
        <div class="panel panel-default bloc-lot">
            <div class="panel-body" style="padding-bottom: 0;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['numero']->renderLabel("Numéro", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-3">
                                  <?php echo $lot['numero']->render(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['date']->renderLabel("Date", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-6">
                                <div class="input-group date-picker">
                                    <?php echo $lot['date']->render(array('placeholder' => "Date")); ?>
                                    <div class="input-group-addon"><span class="glyphicon-calendar glyphicon"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['produit_hash']->renderLabel("Produit", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-8">
                                  <?php echo $lot['produit_hash']->render(array("data-placeholder" => "Séléctionnez un produit", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['millesime']->renderLabel("Millésime", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-3">
                                  <?php echo $lot['millesime']->render(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['volume']->renderLabel("Volume", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <?php echo $lot['volume']->render(); ?>
                                    <div class="input-group-addon">hl</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $lot['destination']->renderLabel("Destination", array('class' => "col-sm-4 control-label")); ?>
                            <div class="col-sm-8">
                                  <?php echo $lot['destination']->render(); ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
    <div class="text-center">
        <button type="submit" name="submit" value="add" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter un lot</button>
    </div>
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo (count($drev->getProduitsVci())) ? url_for('drev_vci', $drev) : url_for('drev_revendication_superficie', $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">

        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>
