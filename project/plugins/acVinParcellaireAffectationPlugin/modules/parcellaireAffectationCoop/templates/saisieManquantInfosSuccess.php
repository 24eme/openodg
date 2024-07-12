<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'declaration' => $parcellaireManquant)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<form id="validation-form" action="" method="post" >
<div class="panel panel-default">
    <div class="panel-heading">
        <?php include_partial('parcellaireAffectationCoop/headerSaisie', ['declaration' => $parcellaireManquant, 'parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'hasForm' => true]); ?>
    </div>
    <div class="panel-body">
        <div class="page-header no-border mt-0">
            <h3 class="mt-2">Déclaration de pieds manquants <?php echo $parcellaireManquant->getPeriode() ?></h3>
        </div>

    <?php include_partial('parcellaireManquant/formManquants', ['parcellaireManquant' => $parcellaireManquant, 'form' => $form]); ?>
</div>
<div class="panel-footer">
    <div class="row row-margin row-button" style="display:flex; justify-content: center;">
        <div class="col-xs-4"><button type="submit" name="retour" value="1" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour à l'étape précédente</button></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-6 text-right"><button id="submit-confirmation-validation" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span>  Valider la déclaration et revenir à la liste des apporteurs</button>

        <button id="submit-confirmation-validation" class="btn btn-default mt-3" name="decla-manquant-a-irrigable"><span class="glyphicon glyphicon-arrow-right"></span> Valider la déclaration et déclarer les irriguables</button></div>
    </div>
    </div>
</div>
</div>
</div>
