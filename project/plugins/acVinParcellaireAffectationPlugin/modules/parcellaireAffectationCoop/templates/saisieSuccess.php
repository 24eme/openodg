<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2><?php echo $parcellaireAffectation->declarant->raison_sociale ?> - Saisie de la déclaration d'affectation parcellaire</h2>
</div>
<form id="validation-form" action="" method="post" >
    <?php if($parcellaireAffectation->isMultiApporteur()): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">
                    Exploitant multi-apporteur pour les caves coopératives :
                    <br/><br/>
                </div>
                <div class="col-xs-12">
                    <ul class="list-unstyled">
                    <?php foreach ($parcellaireAffectation->getCaveCooperatives() as $liaison): ?>
                            <li><?php echo $liaison->libelle_etablissement." (".$liaison->cvi.")"; ?></li>
                     <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-xs-12">
                    Merci d'agir uniquement sur les parcelles concernés par votre coopérative.
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
    <?php include_partial("parcellaireAffectation/formAffectations", array('parcellaireAffectation' => $parcellaireAffectation, 'form' => $form)); ?>
    <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="form-group <?php if ($form["observations"]->hasError()): ?>has-error<?php endif; ?>">
                        <div class="col-xs-3">
                            <h3>Observations :</h3>
                        </div>
                         <div class="col-xs-9">
                            <?php echo $form['observations']->renderError(); ?>
                            <?php echo $form['observations']->render(); ?>
                         </div>
                     </div>
                 </div>
            </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><button type="submit" name="retour" value="1" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button id="btn-validation-document" data-toggle="modal" data-target="#parcellaireaffectation-confirmation-validation" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span> Valider</button></div>
    </div>

    <?php include_partial('parcellaireAffectationCoop/popupConfirmationValidation', array('form' => $form, 'parcellaireAffectation' => $parcellaireAffectation)); ?>
</form>
