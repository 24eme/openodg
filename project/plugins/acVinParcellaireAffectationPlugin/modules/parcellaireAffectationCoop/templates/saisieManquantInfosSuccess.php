<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2>Déclaration de pieds manquants de <?php echo $parcellaireManquant->declarant->raison_sociale ?></h2>
</div>
<form id="validation-form" action="" method="post" >
    <?php include_partial('parcellaireManquant/formManquants', ['parcellaireManquant' => $parcellaireManquant, 'form' => $form]); ?>

    <div class="row row-margin row-button">
        <div class="col-xs-4"><button type="submit" name="retour" value="1" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button id="submit-confirmation-validation" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span> Valider la déclaration</button></div>
    </div>
</form>
