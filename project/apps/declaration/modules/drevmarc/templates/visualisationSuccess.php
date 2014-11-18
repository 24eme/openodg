<div class="page-header no-border">
    <h2>Déclaration de Revendication Marc d'Alsace de Gewurztraminer <?php echo $drevmarc->campagne; ?></h2>
</div>

<?php include_partial('drevmarc/recap', array('drevmarc' => $drevmarc)); ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drevmarc_export_pdf", $drevmarc) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
</div>
