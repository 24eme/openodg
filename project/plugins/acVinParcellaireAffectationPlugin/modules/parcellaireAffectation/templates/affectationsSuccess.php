<?php use_helper('Date') ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php else: ?>
    <?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php endif; ?>

<?php include_partial('parcellaireAffectation/step', array('step' => 'affectations', 'parcellaireAffectation' => $parcellaireAffectation)) ?>
<div class="page-header no-border">
    <h2>Affectation de vos parcelles</h2>
    <h3 style="font-size: 14px;">Les parcelles listées ci-dessous sont reprises depuis le parcellaire douanier</h3>
</div>
<form id="validation-form" action="" method="post" class="form-horizontal">
    <?php include_partial("parcellaireAffectation/formAffectations", array('parcellaireAffectation' => $parcellaireAffectation, 'form' => $form)); ?>
    <div class="row row-margin row-button"  style="display:flex; justify-content: space-evenly;">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectation_exploitation", ['id' => $parcellaireAffectation->_id]) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>

        <div class="col-xs-4" style="display:flex; justify-content:center;"> <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>

        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>

        </div>


    </div>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php endif; ?>
