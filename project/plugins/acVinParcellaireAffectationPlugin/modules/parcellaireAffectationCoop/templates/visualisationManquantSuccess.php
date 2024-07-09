<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'declaration' => $parcellaireManquant)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php include_partial('parcellaireAffectationCoop/headerSaisie', ['declaration' => $parcellaireManquant, 'parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'hasForm' => false]); ?>
    </div>
    <div class="panel-body">
        <div class="page-header no-border mt-0">
            <h3 class="mt-2">Déclaration de pieds manquants <?php echo $parcellaireManquant->getPeriode() ?></h3>
        </div>

<?php include_partial('parcellaireManquant/recap', array('parcellaireManquant' => $parcellaireManquant)); ?>

</div>
<div class="panel-footer">
    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("parcellaireaffectationcoop_liste", $parcellaireAffectationCoop) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour à la liste</a>
        </div>
        <div class="col-xs-4 text-center">
                <a href="<?php echo url_for('parcellaireirrigable_export_pdf', $parcellaireManquant) ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
                </a>
        </div>
        <div class="col-xs-4 text-right">
        </div>
    </div>
</div>
</div>
