<?php use_helper('Float'); ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php else: ?>
    <?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php endif; ?>

<?php include_partial('parcellaireAffectation/step', array('step' => $etape, 'parcellaireAffectation' => $parcellaireAffectation)) ?>

<div class="page-header">
    <h2>Parcelles irrigables sur votre exploitation <br/><small>Merci d'indiquer le type de matériel et de ressource utilisés sur chaque parcelle irrigable</small></h2>
</div>

<?php include_partial('parcellaireAffectation/destinataires', ['destinataires' => $destinataires, 'produits' => $produits, 'parcellaireAffectation' => $parcellaireAffectation, 'destinataire' => $destinataire, 'etape' => $etape]); ?>

<form id="validation-form" action="" method="post" class="form-horizontal">
    <?php include_partial('parcellaireIrrigable/formIrrigations', ['parcellaireIrrigable' => $parcellaireAffectation, 'form' => $form]); ?>

    <?php include_partial('parcellaireAffectation/buttons'); ?>
</form>

<?php if(isset($coop)): ?>
<?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php endif; ?>
