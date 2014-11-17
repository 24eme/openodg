<div class="page-header no-border">
    <h2>Déclaration de revendication <?php echo $drev->campagne ?></h2>
</div>

<?php include_partial('drev/recap', array('drev' => $drev, 'form' => $form)); ?>

<?php if(!$drev->validation): ?>
    <div class="alert alert-warning">
        Cette déclaration est en cours d'édition&nbsp;&nbsp;
        <a href="<?php echo url_for("drev_edit", $drev) ?>" class="btn btn-warning">Continuer</a>
    </div>
<?php endif; ?>


<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php if(isset($service)): ?><?php echo $service ?><?php else: ?><?php echo url_for("home") ?><?php endif; ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
</div>
