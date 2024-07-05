<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2><?php echo $parcellaireIrrigable->declarant->raison_sociale ?> - Déclaration d'irrigation</h2>
</div>

<?php include_partial('parcellaireIrrigable/recap', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("parcellaireaffectationcoop_liste", $parcellaireAffectationCoop) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('parcellaireirrigable_export_pdf', $parcellaireIrrigable) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
    <div class="col-xs-4 text-right">
    </div>
</div>
