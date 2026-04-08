<?php use_helper('Float'); ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php else: ?>
    <?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php endif; ?>

<?php include_partial('parcellaireAffectation/step', array('step' => $etape, 'parcellaireAffectation' => $parcellaireAffectation)) ?>
<?php $manquants_by_commune = $parcellaireAffectation->declaration->getParcellesByCommune(); ?>
<div>
    <h2>Pieds morts ou manquants sur votre exploitation</h2>
    <?php if (count($manquants_by_commune ) == 0): ?>
    <p class="py-5 alert alert-warning">Aucune parcelle du parcellaire actuellement connu est éligible aux manquants.</p>
    <p class="pb-3"><i>Pas de parcelles éligibles</i></p>
    <?php else: ?>
    <p class="pt-3">Merci d'indiquer la densité et le % de pied manquant</p>
    <div class="alert alert-info">
        <div style="display: inline-block; margin-right: 1rem;">
            <span class="glyphicon glyphicon-info-sign"></span>
        </div>
        <div style="display: inline-block; vertical-align: middle">
            Il n'est pas nécessaire d'indiquer les parcelles avec moins de <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>% de pieds manquants.
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_partial('parcellaireAffectation/destinataires', ['destinataires' => $destinataires, 'produits' => $produits, 'parcellaireAffectation' => $parcellaireAffectation, 'destinataire' => $destinataire, 'etape' => $etape, 'coop' => $coop]); ?>

<form id="validation-form" action="" method="post" class="form-inline">
    <?php include_partial('parcellaireManquant/formManquants', ['form' => $form, 'manquants_by_commune' => $manquants_by_commune]); ?>

    <?php include_partial('parcellaireAffectation/buttons'); ?>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php endif; ?>
