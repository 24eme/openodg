<?php include_partial('parcellaire/breadcrumb', array('parcellaire' => $parcellaire )); ?>

<div class="page-header">
    <h2>Confirmation de la validation de votre déclaration d'affectation parcellaire</h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <p>
            Merci d'avoir validé votre parcellaire. <br />
            <br />
            Vous allez recevoir un mail de confirmation.
        </p>
    </div>
</div>

<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("declaration_etablissement", $parcellaire->getEtablissementObject()) ?>" class="btn btn-default btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
    </div>
</div>
