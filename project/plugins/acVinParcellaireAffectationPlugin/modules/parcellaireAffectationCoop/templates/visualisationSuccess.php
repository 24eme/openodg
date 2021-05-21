<div class="page-header no-border">
    <h2><?php echo $parcellaireAffectation->declarant->raison_sociale ?> - Déclaration d'affectation parcellaire</h2>
</div>

<?php include_partial('parcellaireAffectation/recap', array('parcellaireAffectation' => $parcellaireAffectation)); ?>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("parcellaireaffectationcoop_liste", array('sf_subject' => $etablissement, 'periode' => $periode)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('parcellaireaffectation_export_pdf', $parcellaireAffectation) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
    <div class="col-xs-4 text-right">
    </div>
</div>