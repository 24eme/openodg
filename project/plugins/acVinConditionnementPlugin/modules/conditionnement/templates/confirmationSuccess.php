<?php include_partial('conditionnement/breadcrumb', array('conditionnement' => $conditionnement )); ?>

<div class="page-header">
    <h2>Confirmation de validation de votre déclaration</h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <p>
            Merci d'avoir validé votre déclaration.<br /><br />
            Cette validation sera définitive lorsque votre déclaration aura été vérifiée et que les éventuelles pièces à joindre seront parvenues à notre service.
        </p>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $conditionnement->identifiant, 'campagne' => $conditionnement->campagne)); ?>" class="btn btn-default"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
</div>
