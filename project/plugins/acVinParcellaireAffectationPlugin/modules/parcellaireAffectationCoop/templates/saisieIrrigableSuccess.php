<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'declaration' => $parcellaireIrrigable)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<form id="validation-form" action="" method="post" >
<div class="panel panel-default">
    <div class="panel-heading">
        <?php include_partial('parcellaireAffectationCoop/headerSaisie', ['declaration' => $parcellaireIrrigable, 'parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'hasForm' => true]); ?>
    </div>
    <div class="panel-body">
        <div class="page-header no-border mt-0">
            <h3 class="mt-2">Déclaration d'irrigation <?php echo $parcellaireIrrigable->getPeriode() ?></h3>
        </div>

        <?php include_partial("parcellaireIrrigable/formParcelles", ['parcellaireIrrigable' => $parcellaireIrrigable]); ?>
    </div>
    <div class="panel-footer">
        <div class="row row-margin row-button">
        <div class="col-xs-4"><button type="submit" name="retour" value="1" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour à la liste</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button class="btn btn-primary">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</div>
</div>
</div>
