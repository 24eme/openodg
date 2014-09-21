<div class="frame">
    <?php include_partial('drev/recap', array('drev' => $drev)); ?>
</div>

<div class="row row-margin">
    <div class="col-xs-4">
        <a href="<?php echo url_for("home") ?>#drev" class="btn btn-primary btn-lg btn-block"><span class="eleganticon arrow_carrot-left pull-left"></span>Retourner à mon espace</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-save"></span>
                Visualiser
            </a>
    </div>
</div>
