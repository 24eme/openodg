<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'declaration' => $parcellaireAffectation)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<form id="validation-form" action="" method="post" >
<div class="panel panel-default">
    <div class="panel-heading">
        <?php include_partial('parcellaireAffectationCoop/headerSaisie', ['declaration' => $parcellaireAffectation, 'hasForm' => true]); ?>
    </div>
    <div class="panel-body">
        <div class="page-header no-border mt-0">
            <h3 class="mt-2">Déclaration d'affectation parcellaire <?php echo $parcellaireAffectation->getPeriode() ?></h3>
        </div>
        <?php if($parcellaireAffectation->isMultiApporteur()): ?>
        <p style="margin-top: 20px; margin-bottom: 20px;">Exploitant multi-apporteur pour les caves coopératives :
        <?php $i = 1; ?>
        <?php $caveCooperatives = $parcellaireAffectation->getCaveCooperatives(); ?>
        <?php foreach ($caveCooperatives as $liaison): ?>
                <strong><?php echo $liaison->libelle_etablissement ?></strong><?php if($i < count($caveCooperatives)): ?>,<?php endif; ?>
                <?php $i++; ?>
         <?php endforeach; ?>
        <br /><br />
        Merci d'agir uniquement sur les parcelles concernés par votre coopérative.</p>
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
    </div>
    <div class="panel-footer">
        <div class="row row-margin row-button">
            <div class="col-xs-4"><button type="submit" name="retour" value="1" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour à la liste</button></div>
            <div class="col-xs-4 text-center">
            </div>
            <div class="col-xs-4 text-right"><button id="submit-confirmation-validation" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span> Valider la déclaration</button></div>
        </div>
    </div>
</div>
</form>
