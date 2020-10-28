<?php use_helper('Float'); ?>
<?php use_helper('PointsAides');?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation )); ?>

    <div class="page-header"><h2>Modification du Lot IGP</h2></div>

    <div class="alert alert-warning" role="alert">
      <strong>Attention !</strong> Cela créera une modificatrice à la DRev <?php echo $lot->id_document ?>
    </div>

    <form role="form" action="<?php echo url_for("degustation_update_lot", ['id' => $degustation->_id, 'lot' => $lotkey]) ?>" method="post" id="form_degustation_update_lot" class="form-horizontal">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

        <div class="panel panel-default bloc-lot">
            <div class="panel-body" style="padding-bottom: 0;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo $form['numero']->renderLabel("Logement", array('class' => "col-sm-3 control-label")); ?>
                            <div class="col-sm-6">
                                  <?php echo $form['numero']->render(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
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
            </div>
        </div>
    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-offset-8 col-xs-4 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>

