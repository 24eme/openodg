<div class="page-header no-border">
    <h2>Déclaration de revendication 2013</h2>
</div>

<?php include_partial('drev/recap', array('drev' => $drev)); ?>

<div class="row row-margin">
    <div class="col-xs-4">
        <a href="<?php echo url_for("home") ?>#drev" class="btn btn-primary btn-lg"><span class="eleganticon arrow_carrot-left pull-left"></span>Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-save"></span>
                Visualiser
            </a>
    </div>
</div>
